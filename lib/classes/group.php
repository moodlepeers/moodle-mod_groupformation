<?php
// This file is part of PHP implementation of GroupAL
// http://sourceforge.net/projects/groupal/
//
// GroupAL is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// GroupAL implementations are distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with GroupAL. If not, see <http://www.gnu.org/licenses/>.
//
//  This code CAN be used as a code-base in Moodle
// (e.g. for moodle-mod_groupformation). Then put this code in a folder
// <moodle>\lib\groupal
/**
 * This class contains an implementation of an ListItem as a Group
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/statistics.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/groupal_evaluator.php");

class lib_groupal_group {

    public static $groupCount = 0; // Int.
    public static $evaluator;  // IEvaluator.

    public static $groupMembersMaxSize = 0;

    public $groupID = 0; // Int.

    public $statistics;

    public $gpi = 0;


    private $participants; // Generic array: Participant .


    /**
     * lib_groupal_group constructor.
     */
    public function __construct() {
        static::$groupCount++;
        $this->groupID = static::$groupCount;
        $this->participants = array();

    }

    /**
     * Returns the id of the group
     *
     * @return int
     */
    public function getID() {
        return $this->groupID;
    }

    /**
     * Returns the participants of the group
     *
     * @return array
     */
    public function getParticipants() {
        return $this->participants;
    }

    /**
     *
     * @return array of integers, the participant IDs
     */
    public function get_participants_ids() {
        $result = array();
        foreach ($this->participants as $p) {
            $result[] = $p->getID();
        }

        return $result;
    }

    /**
     * Removes an Participant from this Group and calculates the new GroupPerformanceIndex
     *
     * @param lib_groupal_participant $p
     * @return bool  true if successfull
     */
    public function removeParticipant(lib_groupal_participant $p) {
        $index = array_search($p, $this->participants);
        if ($index == false) {
            return false;
        }
        $this->participants[$index]->actualGroup = null;  // What for??
        array_splice($this->participants, $index, 1);
        $this->calculateGroupPerformanceIndex();
        return true;
    }


    /**
     * Adds an Participant to this Group and calculates the new GroupPerformanceIndex
     *
     * @param lib_groupal_participant $p
     * @return bool, true: if was succesful, otherwise false
     */
    public function addParticipant(lib_groupal_participant $p, $random = false) {

        if (count($this->participants) >= static::$groupMembersMaxSize) {
            return false;
        }
        if (in_array($p, $this->participants)) {
            return false;
        }
        $this->participants[] = $p;
        $p->actualGroup = $this->getID();

        // XXX this should not be extra case; better make Calculation of CPI/GPI more robust..
        if (!$random) {
            $this->CalculateGroupPerformanceIndex();
        }
        // TODO GroupALEvaluator Fehler, es würde NULL statt Criterion uebergeben werden.
    }


    /**
     * @return float[]
     */
    public function getGroupPerformanceIndex() {
        return $this->gpi;
    }

    /**
     *
     * @param float $index
     */
    public function setGroupPerformanceIndex($index) {
        $this->gpi = $index;
    }


    /**
     *
     * @return int
     */
    public static function getGroupMembersMaxSize() {
        return static::$groupMembersMaxSize;
    }

    /**
     *
     * @param int $size
     */
    public static function setGroupMembersMaxSize($size) {
        static::$groupMembersMaxSize = $size;
    }


    /**
     * Calculates the GroupPerformanceIndex using the _evaluator
     * void
     */
    public function calculateGroupPerformanceIndex() {
        $this->gpi = static::$evaluator->evaluateGroupPerformanceIndex($this);
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


    public function toString() {
        $s = "This group contains:";
        foreach ($this->participants as $p) {
            $s .= " " . $p->getID();
        }
        return $s;
    }


}