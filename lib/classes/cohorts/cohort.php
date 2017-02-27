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

class mod_groupformation_cohort {

    /** @var mod_groupformation_ievaluator This is the evaluator instance for all cohorts */
    public static $evaluator;

    /** @var array This array contains all computed groups */
    public $groups;

    /** @var mod_groupformation_statistics This contains all statistics */
    public $results;

    /** @var string This is the class name of the used matcher */
    public $whichmatcherused = "";

    /** @var int This is the number of groups */
    public $countofgroups = 0;

    /** @var number This is the cohort performance index */
    public $cpi = null;

    /**
     * mod_groupformation_cohort constructor.
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
        for ($i = count($this->groups); $i < $numberofgroups; $i++) {
            $g = new mod_groupformation_group();
            $this->add_group($g);
        }

    }

    /**
     * Adds a Group to this Cohort if not already a member
     * @param $g mod_groupformation_group
     * @return boolean
     */
    public function add_group(mod_groupformation_group $g) {
        if (in_array($g, $this->groups, TRUE)) {
            return false;
        }

        $this->groups[] = $g;
        $this->calculate_cpi();

        return true;
    }

    /**
     * evaluates the Performance of this Cohort using the evaluator GroupALEvaluator
     * @return float with CohortPerformanceIndex
     * @throws Exception
     */
    public function calculate_cpi() {
        if (static::$evaluator == null) {
            throw new Exception("No evaluator set");
        }
        $this->cpi = static::$evaluator->evaluate_cpi($this);

        return $this->cpi;
    }

    /**
     * adds empty Group
     */
    public function add_empty_group() {
        $this->add_group(new mod_groupformation_group());
    }

    /**
     * helper function for cron-job, deliver results as a copy structure
     *
     * @return stdClass
     */
    public function get_result() {
        $result = new stdClass();
        $result->groups = array();
        $result->users = array();
        foreach ($this->groups as $g) {
            $groupid = $g->get_id();
            $gpi = $g->get_gpi();
            $participantsids = $g->get_participants_ids();
            $result->groups[$groupid] = array('gpi' => $gpi, 'users' => $participantsids);
            foreach ($participantsids as $participantid) {
                $result->users[$participantid] = $groupid;
            }
        }
        return $result;
    }
}