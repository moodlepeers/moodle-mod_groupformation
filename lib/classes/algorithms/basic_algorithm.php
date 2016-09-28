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
 * main class to be used for group formations. get an instance of this and run your
 * groupformations using the provided API of this class.
 *
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/algorithms/ialgorithm.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/cohorts/cohort.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/ievaluator.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/matchers/imatcher.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/statistics.php");

class lib_groupal_basic_algorithm implements lib_groupal_ialgorithm {

    /** @var array This array contains all participants which need to be matched to groups */
    public $participants = array();

    /** @var array This array contains all non-matched participants */
    public $notmatchedparticipants = array();

    /** @var lib_groupal_cohort This object contains the final cohort with all computed groups and some stats */
    public $cohort;

    /** @var lib_groupal_evaluator This is the evaluator which is needed to compute performance indices */
    public $evaluator;

    /** @var lib_groupal_imatcher This is the matcher which is used to match participants to groups */
    public $matcher;

    /** @var lib_groupal_optimizer This is the optimizer which is used to optimize the computed groups */
    public $optimizer;

    /** @var int This is the number of participants which need to be matched */
    public $numberofparticipants = 0;

    /** @var int This is the maximum group size */
    public $groupsize = 0;

    /** @var int This is the number of groups */
    public $numberofgroups = 0;

    /**
     * lib_groupal_basic_algorithm constructor.
     * @param $participants
     * @param lib_groupal_imatcher $matcher
     * @param $groupsize
     */
    public function __construct($participants, lib_groupal_imatcher $matcher, $groupsize) {

        foreach ($participants as $p) {
            $this->participants[] = clone($p);
        }

        $this->matcher = $matcher;

        $this->evaluator = new lib_groupal_evaluator();

        $this->groupsize = $groupsize;

        $this->init();
    }

    /**
     * Init of algorithm class
     */
    private function init() {
        $this->numberofparticipants = count($this->participants);

        lib_groupal_group::set_group_members_max_size($this->groupsize);

        lib_groupal_group::$evaluator = $this->evaluator;

        lib_groupal_cohort::$evaluator = $this->evaluator;

        // Set cohort: generate empty groups in cohort to fill with participants.
        $this->cohort = new lib_groupal_cohort(ceil($this->numberofparticipants / $this->groupsize));

        // Set the list of not yet matched participants; the array is automatically copied in PHP.
        $this->notmatchedparticipants = $this->participants;

        $this->numberofgroups = 0;
    }

    /**
     * Adds a participant to the participants which need to be matched
     *
     * @param lib_groupal_participant $participant
     * @return bool
     */
    public function add_new_participant(lib_groupal_participant $participant) {
        if ($this->participants == null || in_array($participant, $this->participants)) {
            return false;
        }

        // Increase count of participants.
        $this->numberofparticipants++;
        $tmp = ceil($this->numberofparticipants / $this->groupsize);
        // If count of groups changed, then new empty Group.
        if ($tmp != $this->numberofgroups) {
            $this->numberofgroups = $tmp;
            $this->cohort->add_empty_group();
        }

        // Add the new participant to entries.
        $this->participants[] = $participant;
        // Add new participant to the set of not yet matched entries.
        $this->notmatchedparticipants[] = $participant;
        return true;
    }

    /**
     * Removes a participant from the participants which need to be matched
     *
     * @param lib_groupal_participant $participant
     * @return bool
     */
    public function remove_participant(lib_groupal_participant $participant) {
        $index = array_search($participant, $this->participants);
        if ($this->participants == null || $index == false) {
            return false;
        }
        // Decrease count of Participants.
        $this->numberofparticipants--;
        $this->cohort->remove_participant($participant);

        // Remove participant.
        array_splice($this->participants, $index);

        // If in non-matched, remove there as well.
        $index = array_search($participant, $this->notmatchedparticipants);
        if ($index != false) {
            array_splice($this->notmatchedparticipants, $index);
        }

    }

    /**
     *  The main method to call for getting a formation "run" (this takes a while)
     *  Uses the global set matcher to assign evry not yet matched participant to a group
     *
     * @return lib_groupal_cohort
     * @throws Exception
     */
    public function do_one_formation() {
        $this->matcher->match_to_groups($this->notmatchedparticipants, $this->cohort->groups);
        $this->cohort->countofgroups = count($this->cohort->groups);
        $this->cohort->whichmatcherused = get_class($this);
        $this->cohort->calculate_cpi();
        return $this->cohort;
    }
}