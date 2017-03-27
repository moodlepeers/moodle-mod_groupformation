<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Controller for grouping view
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/test_user_generator.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_writer.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');

class mod_groupformation_grouping_controller {
    private $groupformationid;
    private $cmid;
    private $viewstate = 0;
    private $groups = array();
    private $incompletegroups = array();
    private $store = null;
    private $groupsmanager = null;
    private $usermanager;
    private $job = null;
    private $view = null;
    private $groupscreated;
    private $maxgroupssize;

    /**
     * Creates an instance of grouping_controller for groupformation
     *
     * @param int $groupformationid
     */
    public function __construct($groupformationid, $cm = null) {
        $this->groupformationid = $groupformationid;
        if (!is_null($cm)) {
            $this->cmid = $cm->id;
        }

        $this->store = new mod_groupformation_storage_manager ($groupformationid);

        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);

        $this->usermanager = new mod_groupformation_user_manager($this->groupformationid);

        $this->view = new mod_groupformation_template_builder ();

        $this->groups = $this->groupsmanager->get_generated_groups('id, groupname,performance_index,moodlegroupid');

        $this->job = mod_groupformation_job_manager::get_job($this->groupformationid);
        if (is_null($this->job)) {
            $groupingid = ($cm->groupmode != 0) ? $cm->groupingid : 0;
            mod_groupformation_job_manager::create_job($groupformationid, $groupingid);
            $this->job = mod_groupformation_job_manager::get_job($this->groupformationid);
        } else {
            $groupingid = ($cm->groupmode != 0) ? $cm->groupingid : 0;
            mod_groupformation_job_manager::update_job($groupformationid, $groupingid);
            $this->job = mod_groupformation_job_manager::get_job($this->groupformationid);
        }

        $this->determine_status();
    }

    /**
     * Determines status of grouping_view
     */
    public function determine_status() {
        $activitystate = $this->store->is_questionnaire_available();

        $jobstatus = mod_groupformation_job_manager::get_status($this->job);

        $this->groupscreated = $this->groupsmanager->groups_created();

        if ($activitystate) {
            /* Questionnaire is still on */
            $this->viewstate = 0;
        } else if ($jobstatus == 'ready') {
            /* Questionnaire closed, but no groups are generated yet. */
            $this->viewstate = 1;
        } else if ($jobstatus == 'waiting' || $jobstatus == 'started') {
            /* Groupbuilding is in progress */
            $this->viewstate = 2;
        } else if ($jobstatus == 'aborted') {
            /* Groupbuilding is done, but not integrated to moodle-groups */
            $this->viewstate = 3;
        } else if ($jobstatus == 'done' && !$this->groupscreated) {
            /* Moodlegroups are created */
            $this->viewstate = 4;
        } else if ($jobstatus == 'done' && $this->groupscreated) {
            /* currently everything block til job is aborted and reset by cron */
            $this->viewstate = 5;
        }
    }

    /**
     * POST action to start job, sets it to 'waiting'
     */
    public function start($course, $cm) {
        $users = $this->usermanager->handle_complete_questionnaires();
        $this->job->groupingid = $cm->groupingid;
        mod_groupformation_job_manager::set_job($this->job, "waiting", true);
        $this->determine_status();

        $context = groupformation_get_context($this->groupformationid);
        $enrolledusers = get_enrolled_users($context, 'mod/groupformation:onlystudent');

        foreach (array_values($enrolledusers) as $user) {
            groupformation_set_activity_completion($course, $cm, $user->id);
        }

        return $users;
    }

    /**
     * POST action to abort current waiting or running job
     */
    public function abort() {
        mod_groupformation_job_manager::set_job($this->job, "aborted", false, false);
        $this->determine_status();
    }

    /**
     * POST action to adopt groups to moodle
     */
    public function adopt() {
        mod_groupformation_group_generator::generate_moodle_groups($this->groupformationid);
        $this->determine_status();
    }

    /**
     * POST action to adopt groups to moodle
     */
    public function edit($cm) {
        $returnurl = new moodle_url ('/mod/groupformation/grouping_edit_view.php', array(
            'id' => $cm->id, 'do_show' => 'grouping'));
        redirect($returnurl);
    }

    /**
     * POST action to delete generated and/or adopted groups (moodle groups)
     */
    public function delete() {
        mod_groupformation_job_manager::set_job($this->job, "ready", false, true);
        $this->groupsmanager->delete_generated_groups();
        $this->determine_status();
    }

    public function save_edit($groupsstring) {
        $groupsarrayafter = json_decode($groupsstring, true);
        $groupskeysafter = array_keys($groupsarrayafter);
        $useridsafter = array();
        foreach ($groupsarrayafter as $array) {
            $useridsafter = array_merge($useridsafter, $array);
        }

        $groupsarraybefore = array();
        $useridsbefore = array();

        foreach (array_keys($this->groups) as $key) {
            $groupmembers = array_keys($this->get_group_members($key));

            $groupsarraybefore["" . $key] = $groupmembers;
            $useridsbefore = array_merge($useridsbefore, $groupmembers);
        }

        $groupskeysbefore = array_keys($groupsarraybefore);

        $samegroupids = count(
            array_intersect($groupskeysbefore, $groupskeysafter)) == count(
            $groupsarrayafter) && count($groupsarraybefore) == count($groupsarrayafter);
        $nousertwice = (count(array_unique($useridsafter)) == count($useridsbefore));
        $samenumberofusers = count($useridsafter) == count($useridsbefore);
        $nousermissing = count(array_intersect($useridsbefore, $useridsafter)) == count($useridsafter);
        if ($samegroupids && $nousertwice && $nousermissing && $samenumberofusers) {
            $this->groupsmanager->update_groups($groupsarrayafter, $groupsarraybefore);
        }
    }

    /**
     * Generate and return the HTMl Page with templates and data
     *
     * @return string
     */
    public function display() {
        $this->determine_status();
        $this->view->set_template('wrapper_grouping');
        $this->view->assign('grouping_title', $this->store->get_name());
        $this->view->assign('grouping_settings', $this->load_settings());
        $this->view->assign('grouping_statistics', $this->load_statistics());
        // $this->view->assign('grouping_incomplete_groups', $this->load_incomplete_groups());
        $this->view->assign('grouping_generated_groups', $this->load_generated_groups());

        return $this->view->load_template();
    }

    /**
     * sets the buttons of grouping settings
     *
     * @return string
     */
    private function load_settings() {
        $settingsgroupview = new mod_groupformation_template_builder ();
        $settingsgroupview->set_template('grouping_settings');

        $array = array(
            'button1' => array(
                'type' => 'submit', 'name' => 'start', 'value' => '0', 'state' => 'disabled',
                'text' => get_string('grouping_start', 'groupformation')),
            'button2' => array(
                'type' => 'submit', 'name' => 'delete', 'value' => '0', 'state' => 'disabled',
                'text' => get_string('grouping_delete', 'groupformation')),
            'button3' => array(
                'type' => 'submit', 'name' => 'adopt', 'value' => '0', 'state' => 'disabled',
                'text' => get_string('grouping_adopt', 'groupformation')),
            'button4' => array(
                'type' => 'submit', 'name' => 'edit', 'value' => '0', 'state' => 'disabled',
                'text' => get_string('grouping_edit', 'groupformation'))
        );

        switch ($this->viewstate) {
            case 0 :
                $settingsgroupview->assign('status', array(
                    get_string('grouping_status_0', 'groupformation'), 0));
                $settingsgroupview->assign('buttons', $array);

                break;

            case 1 :
                $settingsgroupview->assign('status', array(
                    get_string('grouping_status_1', 'groupformation'), 0));
                $array['button1']['value'] = 1;
                $array['button1']['state'] = '';
                $settingsgroupview->assign('buttons', $array);

                break;

            case 2 :
                $settingsgroupview->assign('status', array(
                    get_string('grouping_status_2', 'groupformation'), 1));
                $array['button1']['name'] = 'abort';
                $array['button1']['text'] = get_string('grouping_abort', 'groupformation');
                $array['button1']['value'] = 1;
                $array['button1']['state'] = '';
                $settingsgroupview->assign('buttons', $array);

                $settingsgroupview->assign('emailnotifications', $this->store->get_email_setting());
                break;

            case 3 :
                $settingsgroupview->assign('status', array(
                    get_string('grouping_status_3', 'groupformation'), 1));
                $settingsgroupview->assign('buttons', $array);
                break;

            case 4 :

                $settingsgroupview->assign('status', array(
                    get_string('grouping_status_4', 'groupformation'), 0));

                $array['button2']['value'] = 1;
                $array['button2']['state'] = '';
                $array['button3']['value'] = 1;
                $array['button3']['state'] = '';
                $array['button4']['value'] = 1;
                $array['button4']['state'] = '';
                $settingsgroupview->assign('buttons', $array);

                break;

            case 5 :
                $settingsgroupview->assign('status', array(
                    get_string('grouping_status_5', 'groupformation'), 0));
                $array['button2']['value'] = 1;
                $array['button2']['state'] = '';
                $array['button2']['text'] = get_string('grouping_delete_moodle_groups', 'groupformation');

                $settingsgroupview->assign('buttons', $array);
                break;

            case 'default' :
            default :

                break;
        }

        $users = mod_groupformation_job_manager::get_users($this->groupformationid);

        $count = count($users[0]) + count($users[1]);

        $settingsgroupview->assign('student_count', $count);
        $settingsgroupview->assign('cmid', $this->cmid);
        $settingsgroupview->assign('onlyactivestudents', $this->store->get_grouping_setting());

        return $settingsgroupview->load_template();
    }

    /**
     * Loads statistics
     *
     * @return string
     */
    private function load_statistics() {
        $statisticsview = new mod_groupformation_template_builder ();

        if ($this->viewstate == 4 || $this->viewstate == 5) {

            $statisticsview->set_template('grouping_statistics');
            $this->maxgroupssize = $this->groupsmanager->get_max_groups_size();
            $statisticsview->assign('performance', $this->job->performance_index);
            $statisticsview->assign('numbOfGroups', count($this->groupsmanager->get_generated_groups()));
            $statisticsview->assign('maxSize', $this->maxgroupssize);
        } else {
            $statisticsview->set_template('grouping_no_data');
            $statisticsview->assign('title','evaluation');
            $statisticsview->assign('grouping_no_data', get_string('no_data_to_display', 'groupformation'));
        }

        return $statisticsview->load_template();
    }

    /**
     * Assigns data about incomplete groups to template
     *
     * @return string
     */
    private function load_incomplete_groups() {
        $incompletegroupsview = new mod_groupformation_template_builder ();

        if ($this->viewstate == 4 || $this->viewstate == 5) {
            $this->set_incomplete_groups();

            $incompletegroupsview->set_template('grouping_incomplete_groups');

            foreach ($this->incompletegroups as $key => $value) {

                $incompletegroupsview->assign($key, array(
                    'groupname' => $value->groupname, 'scrollTo_group' => $this->get_scroll_to_link($key),
                    'grouplink' => $this->get_group_link($value->moodlegroupid), 'groupsize' => $value->groupsize));
            }
        } else {
            $incompletegroupsview->set_template('grouping_no_data');
            $incompletegroupsview->assign('title','incomplete_groups');
            $incompletegroupsview->assign('grouping_no_data', get_string('no_data_to_display', 'groupformation'));
        }

        return $incompletegroupsview->load_template();
    }

    /**
     * Returns link for scrollTo function
     *
     * @param
     *            $groupID
     * @return string
     */
    private function get_scroll_to_link($groupid) {
        return '#' . $groupid;
    }

    /**
     * Sets the array with incompleted groups
     */
    private function set_incomplete_groups() {
        $maxsize = $this->maxgroupssize;
        foreach (array_keys($this->groups) as $key) {
            $userids = $this->groupsmanager->get_users_for_generated_group($key);
            $size = count($userids);
            if ($size < $maxsize) {
                $a = ( array )$this->groups [$key];
                $a ['groupsize'] = $size;
                $this->incompletegroups [$key] = ( object )$a;
            }
        }
    }

    /**
     * Assign groups-data to template
     *
     * @return string
     */
    private function load_generated_groups() {
        $generatedgroupsview = new mod_groupformation_template_builder ();

        $topics = $this->store->ask_for_topics();
        $options = null;
        if ($topics) {
            $xmlcontent = $this->store->get_knowledge_or_topic_values('topic');
            $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $xmlcontent . ' </OPTIONS>';
            $options = mod_groupformation_util::xml_to_array($xmlcontent);
        }

        if ($this->viewstate == 4 || $this->viewstate == 5) {

            $generatedgroupsview->set_template('grouping_generated_groups');

            foreach ($this->groups as $key => $value) {

                $gpi = (is_null($value->performance_index)) ? '-' : $value->performance_index;

                $pos = strrpos($value->groupname, "_");
                $number = substr($value->groupname, $pos + 1, strlen($value->groupname) - $pos);
                $title = "";
                if ($topics) {
                    $title = $options[$number - 1];
                }

                $generatedgroupsview->assign($key, array(
                    'topic' => $title,
                    'groupname' => $value->groupname, 'groupquallity' => $gpi,
                    'grouplink' => $this->get_group_link($value->moodlegroupid),
                    'group_members' => $this->get_group_members($key)));
            }
        } else {
            $generatedgroupsview->set_template('grouping_no_data');
            $generatedgroupsview->assign('title','group_overview');
            $generatedgroupsview->assign('grouping_no_data', get_string('no_data_to_display', 'groupformation'));
        }

        return $generatedgroupsview->load_template();
    }

    /**
     * Generate and return the HTMl Page with templates and data
     *
     * @return string
     */
    public function display_edit() {
        $this->determine_status();
        $this->view->set_template('wrapper_grouping_edit');
        $this->view->assign('grouping_title', $this->store->get_name());
        $this->view->assign('grouping_edit_header', $this->load_settings_edit());
        $this->view->assign('grouping_generated_groups', $this->load_generated_groups_edit());

        return $this->view->load_template();
    }

    /**
     * sets the buttons of grouping settings
     *
     * @return string
     */
    private function load_settings_edit() {
        global $PAGE;
        $settingsgroupview = new mod_groupformation_template_builder ();
        $settingsgroupview->set_template('grouping_edit_header');
        $url = new moodle_url ('/mod/groupformation/grouping_view.php', array(
            'id' => $this->cmid));
        $settingsgroupview->assign('buttons', array(
            'button1' => array(
                'id' => 'submit_groups', 'type' => 'submit', 'name' => 'save_edit', 'value' => '1', 'state' => '',
                'text' => get_string('submit')),
            'button2' => array(
                'id' => 'cancel_groups', 'type' => 'cancel', 'name' => 'cancel_edit', 'value' => $url->out(), 'state' => '',
                'text' => get_string('cancel'))
        ));
        $context = $PAGE->context;
        $count = count(get_enrolled_users($context, 'mod/groupformation:onlystudent'));

        $settingsgroupview->assign('student_count', $count);
        $settingsgroupview->assign('cmid', $this->cmid);
        $settingsgroupview->assign('onlyactivestudents', $this->store->get_grouping_setting());

        return $settingsgroupview->load_template();
    }

    /**
     * Assign groups-data to template
     *
     * @return string
     */
    private function load_generated_groups_edit() {
        $generatedgroupsview = new mod_groupformation_template_builder ();

        if ($this->viewstate == 4 || $this->viewstate == 5) {

            $generatedgroupsview->set_template('grouping_edit_groups');

            $groupsstring = "";
            $groupsarray = array();
            $generatedgroups = array();
            foreach ($this->groups as $key => $value) {

                $gpi = (is_null($value->performance_index)) ? '-' : $value->performance_index;

                $groupmembers = $this->get_group_members($key);
                $groupsarray[$key] = array_keys($groupmembers);

                $gids = implode(',', array_keys($groupmembers));
                $groupsstring .= $gids . "\n";

                $generatedgroups[$key] = array(
                    'id' => 'group_id_' . $key,
                    'groupname' => $value->groupname,
                    'groupquallity' => $gpi,
                    'grouplink' => $this->get_group_link($value->moodlegroupid),
                    'group_members' => $groupmembers);
            }
            $generatedgroupsview->assign('generated_groups', $generatedgroups);

            $v = array();
            foreach ($groupsarray as $array) {
                $v = array_merge($v, $array);
            }

            $groupsstring = json_encode($groupsarray);

            $generatedgroupsview->assign('groups_string', $groupsstring);

        } else {
            $generatedgroupsview->set_template('grouping_no_data');
            $generatedgroupsview->assign('grouping_no_data', get_string('no_data_to_display', 'groupformation'));
        }

        return $generatedgroupsview->load_template();
    }

    /**
     * Gets the name and moodle link of group members
     *
     * @param
     *            $groupID
     * @return array
     */
    private function get_group_members($groupid) {
        global $CFG, $COURSE;
        $userids = $this->groupsmanager->get_users_for_generated_group($groupid);
        $groupmembers = array();
        global $DB;
        $userrecords = $DB->get_records('user');
        foreach ($userids as $user) {
            $url = $CFG->wwwroot . '/user/view.php?id=' . $user->userid . '&course=' . $COURSE->id;

            $username = $user->userid;
            $userrecord = $userrecords[$username];

            if (!is_null($userrecord)) {
                $username = fullname($userrecord);
            }

            if (!(strlen($username) > 2)) {
                $username = $user->userid;
            }
            $userlink = $url;

            $groupmembers [$user->userid] = [
                'name' => $username, 'link' => $userlink, 'id' => $user->userid];
        }

        return $groupmembers;
    }

    /**
     * Get the moodle-link to group and set state of the link(enabled || disabled)
     *
     * @param int $groupid
     * @return array
     */
    private function get_group_link($groupid) {
        $link = array();
        if ($this->groupscreated) {
            $url = new moodle_url ('/group/members.php', array(
                'group' => $groupid));
            $link [] = $url;
            $link [] = '';
        } else {

            $link [] = '';
            $link [] = 'disabled';
        }

        return $link;
    }
}

