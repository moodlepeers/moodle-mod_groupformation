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
 * This class contains the results of a group formation as a cohort consisting
 * of groups filled with participants
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/lib/groupal/classes/evaluators/groupal_evaluator.php");
require_once($CFG->dirroot . "/lib/groupal/classes/group.php");

class lib_groupal_topic_cohort {

    /** @var lib_groupal_ievaluator This is the evaluator instance for all cohorts */
    public static $evaluator;

    /** @var array This array contains all computed groups */
    public $groups = array();

    /** @var lib_groupal_statistics This contains all statistics */
    public $results = null;

    /** @var string This is the class name of the used matcher */
    public $whichMatcherUsed = "";

    /** @var int This is the number of groups */
    public $numberofgroups = 0;

    /**
     * lib_groupal_cohort constructor.
     * @param $numberofgroups
     * @param null $groups
     * @param bool|false $random
     */
    public function __construct($numberofgroups, $groups = null) {
        $this->groups = array();
        if ($groups != null) {
            for ($i = 0; $i < count($groups); $i++) {
                $this->addGroup($groups[$i]);
            }
        }
        $this->numberofgroups = $numberofgroups;
    }

    /**
     * Adds a Group to this Cohort if not already a member
     * @param $g lib_groupal_group
     * @return boolean
     */
    public function addGroup(lib_groupal_group $g) {
        if (in_array($g, $this->groups)) {
            return false;
        }

        $this->groups[] = $g;
        return true;
    }

    /**
     * remove one group from this Cohort
     * @param $g lib_groupal_group
     * @return boolean
     */
    public function removeGroup(lib_groupal_group $g) {
        $index = array_search($g, $this->groups);
        if ($index == false) {
            return false;
        }

        array_splice($this->groups, index, 1);
        $this->numberofgroups--;
        return true;
    }

    /**
     * Remove Participant from this Cohort (from all groups that are member of this Cohort)
     * @param lib_groupal_participant $p
     * @return bool true if any change happend
     */
    public function removeParticipant(lib_groupal_participant $p) {
        $result = false;
        foreach ($this->groups as $g) {
            $i = array_search($p, $g);
            if ($i != false) {
                array_splice($g, $i, 1);
                $result = true;
            }
        }
        if (result) {
            $this->removeEmptyGroups();
            $this->calculateCohortPerformanceIndex();
        }
        return $result;
    }

    /**
     * Removes all empty groups in this Cohort
     * @return bool  true if any change happened
     */
    public function removeEmptyGroups() {
        $result = false;
        $removeCandidates = array(); // Remember indices of groups to delete.
        for ($i = count($this->groups) - 1; $i >= 0; $i--) {
            if (count($this->groups[$i]) == 0) {
                $removeCandidates[] = $i;
            }
        }

        if (!$result) {
            return false;
        }

        // Remove now groups in extra loop due to concurrent modification exception.
        // Do it from 0-n because highest indices are at the beginning in $removeCandidates.
        for ($i = 0; $i < count($removeCandidates); $i++) {
            array_splice($this->groups, $removeCandidates[$i], 1);
            $this->numberofgroups--;
        }
        return true;
    }

    /**
     * adds empty Group
     */
    public function addEmptyGroup() {
        $this->addGroup(new lib_groupal_group());
    }

    /**
     * evaluates the Performance of this Cohort using the evaluator GroupALEvaluator
     * @return float with CohortPerformanceIndex
     * @throws Exception
     */
    public function calculateCohortPerformanceIndex() {
        if (static::$evaluator == null) {
            throw new Exception("Cohort.evaluateCohortPerformanceIndex(): setEvaluator() before execute evaluateCohortPerformanceIndex()");
        }
        $this->cohortPerformanceIndex = static::$evaluator->evaluateCohortPerformanceIndex($this);
        return $this->cohortPerformanceIndex;
    }

    /**
     * Hilfsfunktion für cron-job, Ergebnisse werden ausgelesen
     *
     * @return stdClass
     */
    public function getResult() {
        $result = new stdClass();
        $result->groups = array();
        $result->users = array();

        // Get groupids.
        foreach ($this->groups as $g) {
            // Groupids
            $g_id = $g->getID();
            $g_gpi = $g->getGroupPerformanceIndex();
            $p_ids = $g->get_participants_ids();
            $result->groups[$g_id] = array('gpi' => $g_gpi, 'users' => $p_ids);
            foreach ($p_ids as $p_id) {
                $result->users[$p_id] = $g_id;
            }

        }

        return $result;
    }

}