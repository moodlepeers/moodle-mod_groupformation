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
 *
 * @package mod_groupformation
 * @@author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

class mod_groupformation_group_controller {

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var int The id of the groupformation activity */
    private $groupformationid = null;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store = null;

    /**
     * mod_groupformation_student_group_view_controller constructor.
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
    }

    /**
     * Returns infos for template
     *
     * @return array
     */
    public function load_info() {
        global $CFG, $COURSE, $USER;

        $assigns = array();

        $userid = $USER->id;
        $array = array();
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
            $othermembers = $this->groupsmanager->get_group_members($userid);

            $pos = strrpos($groupname, "_");
            $number = substr($groupname, $pos + 1, strlen($groupname) - $pos);

            if ($topics) {
                $topicinfo = get_string("topic_group_info", "groupformation") . ": <b>" . $options[$number - 1] . "</b>";
            }

            if (count($othermembers) > 0) {
                $groupinfo = get_string('membersAre', 'groupformation');

                foreach ($othermembers as $memberid) {

                    $member = get_complete_user_data('id', $memberid);

                    $url = $CFG->wwwroot . '/user/view.php?id=' . $memberid . '&course=' . $COURSE->id;

                    if (!$member) {
                        $array[] = get_string('noUser', 'groupformation');
                    }

                    $array[] = '<a href="' . $url . '">' . fullname($member) . '</a>';
                }

            } else {
                $groupinfo = get_string('oneManGroup', 'groupformation');
            }
            $assigns['topic_info'] = $topicinfo;
            $assigns['group_name'] = $groupname;
            $assigns['members'] = $array;
            $assigns['group_info'] = $groupinfo;
        } else {
            if ($this->groupsmanager->groups_created()) {
                $groupinfo = get_string('noGroup', 'groupformation');
                $assigns['group_info'] = $groupinfo;
            }else{
                $groupinfo = get_string('groupingNotReady', 'groupformation');
                $assigns['group_info'] = $groupinfo;
            }
        }

        return $assigns;
    }
}