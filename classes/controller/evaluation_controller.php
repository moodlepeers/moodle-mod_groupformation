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

    /** @var mod_groupformation_storage_manager */
    private $store;

    /** @var mod_groupformation_groups_manager */
    private $groupsmanager;

    /** @var mod_groupformation_user_manager */
    private $usermanager;

    /** @var int This is the id of the activity */
    private $groupformationid;

    /** @var mod_groupformation_template_builder|null */
    private $view = null;

    /**
     * mod_groupformation_evaluation_controller constructor.
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;

        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
        $this->view = new mod_groupformation_template_builder ();
        $this->view->set_template('wrapper_student_evaluation');
    }

    /**
     * Renders for no evaluation
     */
    public function no_evaluation($caption = 'no_evaluation_text') {
        $this->view->assign('eval_show_text', true);
        $this->view->assign('eval_text', get_string($caption, 'groupformation'));
        $json = json_encode(null);
        $this->view->assign('json_content', $json);
    }

    /**
     * Renders eval values
     *
     * @param $userid
     * @return string
     */
    public function render($userid) {
        if ($this->store->ask_for_topics()) {
            $this->no_evaluation();
        } else if (!$this->usermanager->has_answered_everything($userid)) {
            $this->no_evaluation('no_evaluation_ready');
        } else {
            $courseusers = $this->store->get_users();

            if (!count($courseusers) >= 2) {
                $courseusers = array();
            }

            $groupusers = array();
            $hasgroup = $this->groupsmanager->has_group($userid);
            if ($hasgroup) {
                $groupusers = $this->groupsmanager->get_group_members($userid);
            }

            if (!count($groupusers) >= 2 || !$this->groupsmanager->groups_created()) {
                $groupusers = array();
            }

            $cc = new mod_groupformation_criterion_calculator($this->groupformationid);

            if (!$this->usermanager->has_evaluation_values($userid)){
                $this->usermanager->set_evaluation_values($userid);
            }

            $eval = $cc->get_eval($userid, $groupusers, $courseusers);

            if (is_null($eval) || count($eval) == 0) {
                $this->no_evaluation();
            } else {
                $this->view->assign('eval_text', false);
                $this->view->assign('eval_show_text', false);
                $json = json_encode($eval);
                $this->view->assign('json_content', $json);
            }
        }

        return $this->view->load_template();
    }
}