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
 * User state machine
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/advanced_job_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/group/lib.php');

/**
 * Class mod_groupformation_user_state_machine
 *
 * @package     mod_groupformation
 * @author      Johannes Konert, Rene Roepke
 * @copyright   2018 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_user_state_machine {

    /** @var int ID of module instance */
    private $groupformationid;

    /** @var array Transitions of state machine */
    private $transitions = array(
            0 => array("consent" => 1, "p_code" => 2, "consent+p_code" => 3, "answer" => 3, "submit" => 4),
            1 => array("p_code" => 3, "answer" => 3),
            2 => array("consent" => 3, "answer" => 3),
            3 => array("submit" => 4, "remove_consent" => 0),
            4 => array("revert" => 3, "remove_consent" => 0)
    );

    /** @var array States of state machine */
    private $states = array(
            0 => "started",
            1 => "consent_given",
            2 => "p_code_given",
            3 => "answering",
            4 => "submitted"
    );

    /**
     * Constructs storage manager for a specific groupformation
     *
     * @param int $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
    }

    /**
     * Returns current state
     *
     * @param bool $internal
     * @return mixed
     */
    public function get_state($userid, $internal = false) {
        global $DB;

        $field = $DB->get_field(
                'groupformation_users',
                'state',
                array(
                        'groupformation' => $this->groupformationid,
                        'userid' => $userid
                )
        );

        return $internal ? $field % 10 : $this->states[$field % 10];
    }

    /**
     * Sets state
     *
     * @param $state
     */
    public function set_state($userid, $state) {
        global $DB;

        $DB->set_field('groupformation_users',
                'state',
                $state,
                array(
                        'groupformation' => $this->groupformationid,
                        'userid' => $userid
                )
        );
    }

    /**
     * Changes state
     *
     * @param $userid
     * @param $action
     */
    public function change_state($userid, $action) {
        $transitions = $this->transitions;
        $state = $this->get_state($userid, true);
        $state = $state % 10;

        if (array_key_exists($state, $transitions) && array_key_exists($action, $transitions[$state])) {
            $newstate = $transitions[$state][$action];
            $this->set_state($userid, $newstate);
        }
    }
}