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
 * State machine
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
 * Class mod_groupformation_state_machine
 *
 * @package     mod_groupformation
 * @author      Johannes Konert, Rene Roepke
 * @copyright   2018 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_state_machine {

    /** @var int ID of module instance */
    private $groupformationid;

    /** @var array Transitions of state machine */
    private $transitions = array(
            0 => array(1, 0),   // Closing questionnaire.
            1 => array(2, 0),   // Starting groupformation, opening questionnaire.
            2 => array(4, 3),   // Groupformation terminates, aborting groupformation.
            3 => array(3, 1),   // Job abortion terminates.
            4 => array(1, 5),   // Starting groupadoption, reset groupformation.
            5 => array(6, 5),   // Job terminates.
            6 => array(1, 7),   // Deleting moodlegroups, reopens questionnaire.
            7 => array(6, 7)
    );

    /** @var array States of state machine */
    private $states = array(
            0 => "q_open",
            1 => "q_closed",
            2 => "gf_started",
            3 => "gf_aborted",
            4 => "gf_done",
            5 => "ga_started",
            6 => "ga_done",
            7 => "q_reopened"
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
     * @throws dml_exception
     */
    public function get_state($internal = false) {
        global $DB;

        $field = $DB->get_field('groupformation', 'state', array('id' => $this->groupformationid));

        return ($internal) ? $field : $this->states[$field];
    }

    /**
     * Sets state
     *
     * @param int $state The state of the machine
     */
    public function set_state($state) {
        global $DB;

        $DB->set_field('groupformation', 'state', $state, array('id' => $this->groupformationid));
    }

    /**
     * Switches to previous state
     *
     * @throws dml_exception
     */
    public function prev() {

        $state = $this->get_state(true);

        $nextstate = $this->transitions[$state][1];

        if (!is_null($nextstate)) {
            $this->set_state($nextstate);
        }
    }

    /**
     * Switches to next state
     *
     * @throws dml_exception
     */
    public function next() {

        $state = $this->get_state(true);

        $nextstate = $this->transitions[$state][0];

        $this->set_state($nextstate);
    }
}