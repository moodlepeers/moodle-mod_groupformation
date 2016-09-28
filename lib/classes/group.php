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
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/statistics.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/groupal_evaluator.php");

class lib_groupal_group {

    public static $groupcount = 0; // Int.
    public static $evaluator;  // IEvaluator.

    public static $groupmembersmaxsize = 0;

    public $groupid = 0; // Int.

    public $statistics;

    public $gpi = 0;


    private $participants; // Generic array: Participant .


    /**
     * lib_groupal_group constructor.
     */
    public function __construct() {
        static::$groupcount++;
        $this->groupid = static::$groupcount;
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
     * @param lib_groupal_participant $p
     * @return bool  true if successfull
     */
    public function remove_participant(lib_groupal_participant $p) {
        $index = array_search($p, $this->participants);
        if ($index == false) {
            return false;
        }
        $this->participants[$index]->actualGroup = null;
        array_splice($this->participants, $index, 1);
        $this->calculate_gpi();
        return true;
    }


    /**
     * Adds an Participant to this Group and calculates the new GroupPerformanceIndex
     *
     * @param lib_groupal_participant $p
     * @return bool, true: if was succesful, otherwise false
     */
    public function add_participant(lib_groupal_participant $p, $random = false) {

        if (count($this->participants) >= static::$groupmembersmaxsize) {
            return false;
        }
        if (in_array($p, $this->participants)) {
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
     * @return float[]
     */
    public function get_gpi() {
        return $this->gpi;
    }

    /**
     *
     * @param float $index
     */
    public function set_gpi($index) {
        $this->gpi = $index;
    }


    /**
     *
     * @return int
     */
    public static function get_group_members_max_size() {
        return static::$groupmembersmaxsize;
    }

    /**
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