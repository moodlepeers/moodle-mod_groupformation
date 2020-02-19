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
 * This class contains an implementation of an ListItem as a Group.
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/statistics.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/groupal_evaluator.php");

/**
 * Class mod_groupformation_group
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_group {

    /** @var int */
    public static $groupcount = 0; // Int.

    /** @var mod_groupformation_ievaluator */
    public static $evaluator;  // IEvaluator.

    /** @var int  */
    public static $groupmembersmaxsize = 0;

    /** @var int  */
    public $groupid = 0; // Int.

    /** @var mod_groupformation_stats */
    public $statistics;

    /** @var int  */
    public $gpi = 0;

    /** @var array  */
    private $participants; // Generic array: Participant .

    /**
     * mod_groupformation_group constructor.
     *
     * @param null $id
     */
    public function __construct($id = null) {
        static::$groupcount++;
        $this->groupid = static::$groupcount;
        if (!is_null($id)) {
            $this->groupid = $id;
        }
        $this->participants = array();

    }

    /**
     * Returns the id of the group
     *
     * @return int
     */
    public function get_id() {
        return $this->groupid;
    }

    /**
     * Returns the participants of the group
     *
     * @return array
     */
    public function get_participants() {
        return $this->participants;
    }

    /**
     * Returns participant IDs
     *
     * @return array of integers, the participant IDs
     */
    public function get_participants_ids() {
        $result = array();
        foreach ($this->participants as $p) {
            $result[] = $p->get_id();
        }

        return $result;
    }

    /**
     * Removes an Participant from this Group and calculates the new GroupPerformanceIndex
     *
     * @param mod_groupformation_participant $p
     * @return bool  true if successfull
     */
    public function remove_participant(mod_groupformation_participant $p) {
        $index = array_search($p, $this->participants);
        if ($index == false) {
            return false;
        }
        $this->participants[$index]->actualGroup = null;
        array_splice($this->participants, $index, 1);
        $this->calculate_gpi();
        return true;
    }

    public function remove_participant_by_id($id) {
        $position = null;
        foreach($this->participants as $index => $participant) {
            if ($participant->get_id() == $id){
                $position = $index;
                break;
            }
        }
        $participant = $this->participants[$position];
        $participant->actualGroup = null;
        unset($this->participants[$position]);
        $this->participants = array_values($this->participants);
    }

    /**
     * Adds an Participant to this Group and calculates the new GroupPerformanceIndex
     *
     * @param mod_groupformation_participant $p
     * @param bool $random
     * @return bool, true: if was succesful, otherwise false
     */
    public function add_participant(mod_groupformation_participant $p, $random = false) {

        if (count($this->participants) >= static::$groupmembersmaxsize) {
            return false;
        }
        if (in_array($p, $this->participants, true)) {
            return false;
        }
        $this->participants[] = $p;
        $p->actualgroup = $this->get_id();

        // XXX this should not be extra case; better make Calculation of CPI/GPI more robust..
        if (!$random) {
            $this->calculate_gpi();
        }
    }

    /**
     * Returns GPI
     *
     * @return array
     */
    public function get_gpi() {
        return $this->gpi;
    }

    /**
     * Sets GPI
     *
     * @param float $index
     */
    public function set_gpi($index) {
        $this->gpi = $index;
    }

    /**
     * Returns group members max size
     *
     * @return int
     */
    public static function get_group_members_max_size() {
        return static::$groupmembersmaxsize;
    }

    /**
     * Sets group members max size
     *
     * @param int $size
     */
    public static function set_group_members_max_size($size) {
        static::$groupmembersmaxsize = $size;
    }

    /**
     * Calculates the GroupPerformanceIndex using the _evaluator
     * void
     */
    public function calculate_gpi() {
        $this->gpi = static::$evaluator->evaluate_gpi($this);
    }

    // Extra methods.
    /**
     * clears this group -> removes alle entries and sets the GPI to 0
     * void TODO einzelne Participants müssen bearbeitet werden (next werte)
     */
    public function clear() {
        // Empty participants list.
        $this->participants = array();
        $this->gpi = 0;
    }
}