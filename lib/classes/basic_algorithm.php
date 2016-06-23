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
 * main class to be used for group formations. get an instance of this and run your
 * groupformations using the provided API of this class.
 * 
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once(__DIR__ . "/group.php");
require_once(__DIR__ . "/cohort.php");
require_once(__DIR__ . "/ievaluator.php");
require_once(__DIR__ . "/imatcher.php");
//require_once(__DIR__."/ioptimizer.php");
require_once(__DIR__ . "/participant.php");
require_once(__DIR__ . "/statistics.php");

class lib_groupal_basic_algorithm {
	
	// gematchte Teilnehmer
	public $participants = array(); // generic List: Participant
	// non matched participants
	public $nmp = array(); // generic List: Participant
	public $cohort; // Object: Cohort (result)
	
    public $evaluator;  // IEvaluator
    public $matcher; // IMatcher
    public $optimizer; // IOptimizer
 
    public $participantsCount = 0; //count of entries(participants) in the system
    public $groupSize = 0;  // group size(how many people has a Group at most)
    public $X = 0; //count of groups



    /**
     * Constructor
     *
     * @param $_participants
     * @param lib_groupal_imatcher $_matcher
     * @param lib_groupal_ievaluator $_evaluator // not needed FIXME Ahemd: why?? (JK)
     * @param lib_groupal_optimizer $_optimizer
     * @param $groupsize
     */

    public function __construct($_participants, lib_groupal_imatcher $matcher, $groupsize) {
        foreach($_participants as $p) {
            $this->participants[] = clone($p);          
        }        
        $this->matcher = $matcher;
        $this->evaluator = new lib_groupal_evaluator(); // this SHOULD be a parameter!
        // $this->optimizer = $_optimizer;
        $this->groupSize = $groupsize;        
        $this->init();
    }

    /**
     * initialize
     */
    public function init() {
        $this->participantsCount = count($this->participants);
        lib_groupal_group::setGroupMembersMaxSize($this->groupSize);
		
        lib_groupal_group::$evaluator = $this->evaluator;  // XXX needs to be made unstatic once.
        lib_groupal_cohort::$evaluator = $this->evaluator;
        
        // set cohort: generate empty groups in cohort to fill with participants
        $this->cohort = new lib_groupal_cohort(ceil($this->participantsCount / $this->groupSize));

        // set the list of not yet matched participants; the array is automatically copied in PHP
        $this->nmp = $this->participants;

        $this->X = 0;
    }

    /**
     * @param lib_groupal_participant $p
     * @return bool
     */
    public function addNewParticipant(lib_groupal_participant $p) {
        if ($this->participants == null || in_array($p, $this->participants)) {
            return false;
        }

        // increase count of participants
        $this->participantsCount++;
        $tmpX = ceil($this->participantsCount / $this->groupSize);
        // if count of groups changed, then new empty Group
        if ($tmpX != $this->X) {
            $this->X = $tmpX;
            $this->cohort->addEmptyGroup();
        }

        // add the new participant to entries
        $this->participants[] = $p;
        // add new participant to the set of not yet matched entries
        $this->nmp[] = $p;
        return true;
    }

    /**
     * @param lib_groupal_participant $p
     * @return bool
     */
    public function removeParticipant(lib_groupal_participant $p) {
        $index = array_search($p, $this->participants);
    	if ($this->participants == null || $index == false) {
            return false;
        }
        // decrease count of Participants
        $this->participantsCount--;
        $tmpX = ceil($this->participantsCount / $this->groupSize);
        $this->cohort->removeParticipant($p);

        // remove participant 
        array_splice($this->participants, $index);
        
        // if in non-matched, remove there as well
        $index = array_search($p, $this->nmp);
        if ($index != false) {
        	array_splice($this->nmp, $index);
        }
        
    }

    /**
     * @param $nmp list of not matched participants
     * @param $groups groups to add the person(s) to
     */
    public function matchToGroups($nmp, $groups) {
        // math the new added participant to one of the groups
        $this->matcher->matchToGroups($nmp, $groups);
    }

    /**
     *
     */
    public function optimizeCohort() {
        $this->optimizer->optimizeCohort($this->cohort);
    }

    /** The main method to call for getting a formation "run" (this takes a while)
     *  Uses the global set matcher to assign evry not yet matched participant to a group
     * @return $cohort Cohort
     */
    public function doOneFormation() {
        $this->matchToGroups($this->nmp, $this->cohort->groups);
        $this->cohort->countOfGroups = count($this->cohort->groups);
        $this->cohort->whichMatcherUsed = get_class($this->matcher);
        $this->cohort->calculateCohortPerformanceIndex();
        return $this->cohort;
    }


}