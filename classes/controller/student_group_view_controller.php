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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// TODO noch nicht getestet
// defined('MOODLE_INTERNAL') || die(); -> template
// namespace mod_groupformation\classes\lecturer_settings;
if (! defined ( 'MOODLE_INTERNAL' )) {
    die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

// require_once 'storage_manager.php';
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');


class mod_groupformation_student_group_view_controller {
    private $groups_store;
    private $groupformationid;

    private $view = NULL;

    /**
     * Constructs instance of groupInfos
     *
     * @param integer $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->groups_store = new mod_groupformation_groups_manager ( $groupformationid );

        $this->view = new mod_groupformation_template_builder ();
        $this->view->setTemplate('wrapper_student_groupview');
    }

    /**
     * Outputs group with its members
     *
     * @param integer $userid
     */
    public function render($userid) {
        global $CFG, $COURSE;
        if ($this->groups_store->has_group ( $userid ) && $this->groups_store->groups_created()) {
            $id = $this->groups_store->get_group_id ( $userid );

            $name = $this->groups_store->get_group_name ( $userid );

            //echo 'Der Name deiner Gruppe ist ' . $name . ' (ID #' . $id . ')<br>';
            $string = $name . ' (ID #' . $id . ')';

            $this->view->assign('group_name', $string);

            // echo 'Deine Gruppennummer ist ' . $id . '<br>';

            $otherMembers = $this->groups_store->get_group_members ( $userid );

            if (count ( $otherMembers ) > 0) {
                //echo 'Deine Arbeitskollegen sind: <br>';

                $this->view->assign('group_info', 'Deine Arbeitskollegen sind:');
                $this->view->assign('group_info_contact', 'Um deine Gruppenmitglieder zu kontaktieren, klicke auf deren Profilnamen.');

                $array = array();
                foreach ( $otherMembers as $memberid ) {

                    $member = get_complete_user_data ( 'id', $memberid );

                    $url = $CFG->wwwroot . '/user/view.php?id=' . $memberid . '&course=' . $COURSE->id;

                    if (! $member) {
                        echo 'user does not exist!';
                    }

                    $array[] = '<a href="' . $url . '">' . fullname ( $member ) . '</a>';
                    //echo '<a href="' . $url . '">' . fullname ( $member ) . '</a>';
                }
                $this->view->assign('members', $array);
            }else{
                $this->view->assign('group_info', 'Du bist allein in dieser Gruppe.');
                //echo 'Du bist allein in dieser Gruppe.';
            }
        } else {
            $this->view->assign('group_info', 'Die Gruppenbildung ist noch nicht abgeschlossen.');
            //echo '<h5> Die Gruppenbildung ist noch nicht abgeschlossen. </h>';
        }
        return $this->view->loadTemplate();
    }
}