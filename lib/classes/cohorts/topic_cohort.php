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
 * This class contains the results of a group formation as a cohort consisting
 * of groups filled with participants
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/groupal_evaluator.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");

class lib_groupal_topic_cohort {

    /** @var lib_groupal_ievaluator This is the evaluator instance for all cohorts */
    public static $evaluator;

    /** @var array This array contains all computed groups */
    public $groups = array();

    /** @var lib_groupal_statistics This contains all statistics */
    public $results = null;

    /** @var string This is the class name of the used matcher */
    public $whichmatcherused = "";

    /** @var int This is the number of groups */
    public $numberofgroups = 0;

    /**
     * lib_groupal_topic_cohort constructor.
     * @param $numberofgroups
     * @param null $groups
     */
    public function __construct($numberofgroups, $groups = null) {
        $this->groups = array();
        if ($groups != null) {
            for ($i = 0; $i < count($groups); $i++) {
                $this->add_group($groups[$i]);
            }
        }
        $this->numberofgroups = $numberofgroups;
    }

    /**
     * Adds a Group to this Cohort if not already a member.
     *
     * @param $g lib_groupal_group
     * @return boolean
     */
    public function add_group(lib_groupal_group $g) {
        if (in_array($g, $this->groups)) {
            return false;
        }

        $this->groups[] = $g;
        return true;
    }

    /**
     * remove one group from this Cohort.
     *
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
     * Remove Participant from this Cohort (from all groups that are member of this Cohort).
     *
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
            $this->remove_empty_groups();
            $this->calculate_cpi();
        }
        return $result;
    }

    /**
     * Removes all empty groups in this Cohort.
     * @return bool  true if any change happened
     */
    public function remove_empty_groups() {
        $result = false;
        $removecandidates = array(); // Remember indices of groups to delete.
        for ($i = count($this->groups) - 1; $i >= 0; $i--) {
            if (count($this->groups[$i]) == 0) {
                $removecandidates[] = $i;
            }
        }

        if (!$result) {
            return false;
        }

        // Remove now groups in extra loop due to concurrent modification exception.
        // Do it from 0-n because highest indices are at the beginning in $removecandidates.
        for ($i = 0; $i < count($removecandidates); $i++) {
            array_splice($this->groups, $removecandidates[$i], 1);
            $this->numberofgroups--;
        }
        return true;
    }

    /**
     * adds empty Group.
     */
    public function add_empty_group() {
        $this->add_group(new lib_groupal_group());
    }

    /**
     * evaluates the Performance of this Cohort using the evaluator GroupALEvaluator
     * @return float with CohortPerformanceIndex
     * @throws Exception
     */
    public function calculate_cpi() {
        if (static::$evaluator == null) {
            throw new Exception("Cohort.evaluateCohortPerformanceIndex(): setEvaluator() before execute evaluateCohortPerformanceIndex()");
        }
        $this->cohortPerformanceIndex = static::$evaluator->evaluate_cpi($this);
        return $this->cohortPerformanceIndex;
    }

    /**
     * Hilfsfunktion für cron-job, Ergebnisse werden ausgelesen.
     *
     * @return stdClass
     */
    public function get_result() {
        $result = new stdClass();
        $result->groups = array();
        $result->users = array();
        foreach ($this->groups as $g) {
            $groupid = $g->get_id();
            $gpi = $g->getGroupPerformanceIndex();
            $participantsids = $g->get_participants_ids();
            $result->groups[$groupid] = array('gpi' => $gpi, 'users' => $participantsids);
            foreach ($participantsids as $participantid) {
                $result->users[$participantid] = $groupid;
            }

        }

        return $result;
    }

}