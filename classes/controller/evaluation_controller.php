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
 * Controller for evaluation view
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');

/**
 * Class mod_groupformation_evaluation_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_evaluation_controller {

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store = null;

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager = null;

    /** @var int ID of module instance */
    private $groupformationid = null;

    /** @var int ID of course module */
    public $cmid = null;

    /**
     * mod_groupformation_evaluation_controller constructor.
     *
     * @param int $groupformationid
     * @param int $cmid
     */
    public function __construct($groupformationid, $cmid) {
        $this->groupformationid = $groupformationid;
        $this->cmid = $cmid;

        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
    }

    /**
     * Renders for no evaluation
     *
     * @param string $caption
     * @return array
     */
    public function no_evaluation($caption = 'no_evaluation_text') {

        $assigns = array();

        $assigns['eval_show_text'] = true;
        $assigns['eval_text'] = get_string($caption, 'groupformation');
        $json = json_encode(null);
        $assigns['json_content'] = $json;

        return $assigns;
    }

    /**
     * Returns eval array for user.
     *
     * @param int $userid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_eval($userid) {
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

        $this->usermanager->set_evaluation_values($userid);

        return $cc->get_eval($userid, $groupusers, $courseusers);
    }

    /**
     * Load info
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function load_info() {
        global $USER;

        $assigns = array();

        $userid = $USER->id;

        if ($this->store->ask_for_topics()) {

            $assigns = array_merge($this->no_evaluation(), $assigns);

        } else if (!$this->usermanager->has_answered_everything($userid)) {

            $assigns = array_merge($this->no_evaluation('no_evaluation_ready'), $assigns);

        } else {

            $eval = $this->get_eval($userid);

            if (is_null($eval) || count($eval) == 0) {

                $assigns = array_merge($this->no_evaluation(), $assigns);

            } else {

                $assigns['eval_text'] = false;
                $assigns['eval_show_text'] = false;
                $json = json_encode($eval);
                $assigns['json_content'] = $json;

            }
        }

        return $assigns;
    }

}