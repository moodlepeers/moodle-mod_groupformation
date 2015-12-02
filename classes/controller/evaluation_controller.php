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


class mod_groupformation_evaluation_controller {
    private $store;
    private $groups_store;
    private $groupformationid;

    private $view = null;

    /**
     * Constructs instance of groupInfos
     *
     * @param integer $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;

        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->groups_store = new mod_groupformation_groups_manager ($groupformationid);

        $this->view = new mod_groupformation_template_builder ();
        $this->view->set_template('wrapper_student_evaluation');
    }

    /**
     * @param $userid
     * @return string
     */
    public function render($userid) {
        $pp = new mod_groupformation_participant_parser($this->groupformationid);

        $participants = $pp->build_participants(array(3));
//      var_dump($participants[0]->criteria);

        $course_users = $this->store->get_users();
//      var_dump($course_users);

        $group_users = array();
        $has_group = $this->groups_store->has_group($userid, true);
        if ($has_group) {
            $group_users = $this->groups_store->get_group_members($userid);
        }

//        if (!count($course_users) >= 3) {
//            $course_users = array();
//        }
//
//        if (!count($group_users) >= 3) {
//            $group_users = array();
//        }

        $cc = new mod_groupformation_criterion_calculator($this->groupformationid);

        $eval = $cc->get_eval($userid,$group_users,$course_users);//array(3,59,60), array(3, 59,60,61));

        $json = json_encode($eval);

        $this->view->assign('json_content', $json);

        return $this->view->load_template();
    }
}