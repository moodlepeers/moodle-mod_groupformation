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


class mod_groupformation_student_group_view_controller {
    private $groupsmanager;
    private $groupformationid;

    private $view = null;

    /**
     * mod_groupformation_student_group_view_controller constructor.
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);

        $this->view = new mod_groupformation_template_builder ();
        $this->view->set_template('wrapper_student_groupview');
    }

    /**
     * Outputs group with its members
     *
     * @param $userid
     * @return string
     * @throws coding_exception
     */
    public function render($userid) {
        global $CFG, $COURSE;
        $array = array();
        $groupinfocontact = '';
        $groupname = '';
        if ($this->groupsmanager->has_group($userid) && $this->groupsmanager->groups_created()) {
            $id = $this->groupsmanager->get_group_id($userid);

            $name = $this->groupsmanager->get_group_name($userid);

            $groupname = $name . ' (ID #' . $id . ')';
            $othermembers = $this->groupsmanager->get_group_members($userid);

            if (count($othermembers) > 0) {
                $groupinfo = get_string('membersAre', 'groupformation');
                $groupinfocontact = get_string('contact_members', 'groupformation');

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
        } else {
            $groupinfo = get_string('groupingNotReady', 'groupformation');
        }

        $this->view->assign('group_name', $groupname);
        $this->view->assign('members', $array);
        $this->view->assign('group_info', $groupinfo);
        $this->view->assign('group_info_contact', $groupinfocontact);
        return $this->view->load_template();
    }
}