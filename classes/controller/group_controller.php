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
 * Controller for group view
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

/**
 * Class mod_groupformation_group_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_group_controller {

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var int The id of the groupformation activity */
    private $groupformationid = null;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store = null;

    /** @var int ID of course module */
    public $cmid = null;

    /**
     * mod_groupformation_student_group_view_controller constructor.
     *
     * @param int $groupformationid
     * @param int $cmid
     */
    public function __construct($groupformationid, $cmid) {
        $this->groupformationid = $groupformationid;
        $this->cmid = $cmid;

        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
    }

    /**
     * Returns infos for template
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function load_info() {
        global $CFG, $COURSE, $USER;

        $assigns = array();

        $userid = $USER->id;
        $activemembers = array();
        $inactivemembers = array();
        $topicinfo = '';

        $options = null;
        $topics = $this->store->ask_for_topics();
        if ($topics) {
            $xmlcontent = $this->store->get_knowledge_or_topic_values('topic');
            $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $xmlcontent . ' </OPTIONS>';
            $options = mod_groupformation_util::xml_to_array($xmlcontent);
        }

        if ($this->groupsmanager->has_group($userid) && $this->groupsmanager->groups_created()) {

            $name = $this->groupsmanager->get_group_name($userid);

            $groupname = $name;
            $groupid = $this->groupsmanager->get_group_id($userid);
            $groupid = $this->groupsmanager->get_moodle_group_id($groupid);
            $othermembers = $this->groupsmanager->get_group_members($userid);

            $pos = strrpos($groupname, "_");
            $number = substr($groupname, $pos + 1, strlen($groupname) - $pos);
            $groupleftinfo = null;
            if ($topics) {
                $topicinfo = get_string("topic_group_info", "groupformation") . ": <b>" . $options[$number - 1] . "</b>";
            }

            if (count($othermembers) > 0) {
                $groupinfo = get_string('members_are', 'groupformation');

                foreach ($othermembers as $memberid) {

                    $member = get_complete_user_data('id', $memberid);

                    $url = $CFG->wwwroot . '/user/view.php?id=' . $memberid . '&course=' . $COURSE->id;

                    if (!$member) {
                        $activemembers[] = get_string('noUser', 'groupformation');
                    }

                    if (!groups_is_member($groupid, $member->id)) {
                        $groupleftinfo = get_string('inactive_members_are', 'groupformation');
                        $inactivemembers[] = fullname($member);
                    } else {
                        $activemembers[] = '<a href="' . $url . '">' . fullname($member) . '</a>' . ((mod_groupformation_data::participant_email_enabled())?(' - '. "<a href=\"mailto:".$member->email."\">". $member->email. '</a>'):'');
                    }
                }

            } else {
                $groupinfo = get_string('oneManGroup', 'groupformation');
            }
            $assigns['topic_info'] = $topicinfo;
            $assigns['group_name'] = str_replace("G1_", "", $groupname);
            $assigns['members'] = $activemembers;
            $assigns['group_info'] = $groupinfo;
            $assigns['group_left_info'] = $groupleftinfo;
            $assigns['inactivemembers'] = $inactivemembers;
        } else {
            if ($this->groupsmanager->groups_created()) {
                $groupinfo = get_string('noGroup', 'groupformation');
                $assigns['group_info'] = $groupinfo;
            } else {
                $groupinfo = get_string('groupingNotReady', 'groupformation');
                $assigns['group_info'] = $groupinfo;
            }
        }

        return $assigns;
    }
}