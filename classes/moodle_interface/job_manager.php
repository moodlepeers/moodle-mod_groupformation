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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * interface betweeen DB and Plugin
 *
 * @package mod_groupformation
 * @author Rene & Ahmed
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// defined('MOODLE_INTERNAL') || die(); -> template
// namespace mod_groupformation\moodle_interface;
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

// DUMMY
// require_once ...
        require_once($CFG->dirroot.'/lib/groupal/classes/Criteria/SpecificCriterion.php');
		require_once($CFG->dirroot.'/lib/groupal/classes/Participant.php');
		require_once($CFG->dirroot.'/lib/groupal/classes/Cohort.php');
		require_once($CFG->dirroot.'/lib/groupal/classes/Matcher/GroupALGroupCentricMatcher.php');


class mod_groupformation_job_manager {	
	
	/**
	 *
	 * @return Ambigous <>
	 */
	public function get_next_job() {
		global $DB;
		$sql = "SELECT * 
				FROM {groupformation_jobs} 
				WHERE 
					waiting = 1
					AND
					started = 0
					AND
					aborted = 0
					AND 
					done = 0 
				ORDER BY timecreated ASC 
				LIMIT 1";
		$jobs = $DB->get_records_sql ( $sql );
		
		if (count ( $jobs ) == 1) {
			$id = array_keys ( $jobs )[0];
			$job = $jobs [$id];
			$this->set_job($job,"1000");
			return $job;
		}elseif (count ($jobs) == 0){
			return null;
		}
	}
	
	/**
	 * 
	 * Resets job to 0000
	 * 
	 * @param stdClass $job
	 */
	public function reset_job($job){
		$this->set_job($job);
	}
	
	/**
	 * 
	 * Sets job to state e.g. 1000
	 * 
	 * @param stdClass $job
	 * @param string $state
	 */
	public function set_job($job,$state="0000"){
		global $DB;
		
		$job->waiting = $state[0];
		$job->started = $state[1];
		$job->aborted = $state[2];
		$job->done = $state[3];
		
		$DB->update_record('groupformation_jobs', $job);
	}
	
	/**
	 * 
	 * Checks whether job is aborted or not
	 * 
	 * @param stdClass $job
	 * @return boolean
	 */
	public function is_job_aborted($job){
		global $DB;
		
		return $DB->get_field('groupformation_jobs','aborted',array('id'=>$job->id)) == '1';
		
	}
	
	/**
	 * Runs groupal with job
	 * 
	 * @param stdClass $job
	 * @return stdClass
	 */
	public function do_groupal($job){
		// TODO @Nora @Ahmed
		// get groupformation for this job
		
		$store = new mod_groupformation_storage_manager($job->groupformationid);
		
		$groupsize = intval($store->getGroupSize());
		
		var_dump($groupsize);
		
		// get users for this job
		// for each user get the criterions and create participant
		// USER+ANSWERS => CRITERIONS => PARTICIPANT WITH CRITERIONS => LIST OF PARTICIPANTS
		// hand-over participants to groupAL lib 
		// get back the results
		
		

		//----- DUMMY Criterions
		// ...
		
		//...
		
		//groupal
		
		//lib/groupal/run->run(Participants, groupsize, ..)
		
		//... var_dump("ergebnis")
		
		
		// DUMMY 
		/**
         * <Testdaten>------------------------------------------
         */
        // init CriterionWeight and set group members max size
        CriterionWeight::init(new HashMap);
        Group::setGroupMembersMaxSize(2);

        // Dummy Criterions
        $c_vorwissen = new SpecificCriterion("vorwissen", array(0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $c_note = new SpecificCriterion("note", array(0.4), 0, 1, true, 1);
        $c_persoenlichkeit = new SpecificCriterion("persoenlichkeit", array(0.4, 0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $c_motivation = new SpecificCriterion("motivation", array(0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $c_lernstil = new SpecificCriterion("lernstil", array(0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $c_teamorientierung = new SpecificCriterion("teamorientierung", array(0.4, 0.4, 0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        // Dummy Participants
        $users = array();
        for ($i = 0; $i < 8; $i++) {
            $users[] = new Participant(array($c_vorwissen, $c_motivation,
                $c_note, $c_persoenlichkeit, $c_lernstil, $c_teamorientierung), $i);
        }
        /**
         * </Testdaten>-----------------------------------------
         */

        // Matcher (einer von beiden)
		$gcm = new GroupALGroupCentricMatcher();
        // TODO Cohort dynamisch gestalten
        $cohort = new Cohort(4, null); // null, weil noch keine Gruppen vorhanden. Bereits vorhandene könnten aber übergeben werden.

        $gcm->matchToGroups($users, $cohort->groups);


		$result = new stdClass();
        // groupsIDs und Gruppen sammeln
		$result->groupids = array();
        $result->groups = array();
        $result->users = array();

        foreach($cohort->groups as $g) {
            // gruppen mit GruppenIDs als array-Index
            $result->groups[$g->getID()] = $g->getID();
        }

        foreach($cohort->groups as $g) {
            // groupIDs
            
        	// TODO @Ahmed Kannst du in Group eine Methode get_participants_ids() schreiben die die get_participants in ein array mit nur den IDs umwandelt?
            $result->groupids[$g->getID()] = array('id'=>$g->getID(),'users'=>$g->get_participants()); //->get_participants_ids());
        }

        // get all matched users
        foreach($cohort->groups as $g) {
            $p = $g->get_participants(); // Participants as  LinkedList
            for ($z = $p->first(); $z != null; $z = $z->next()) {
                $result->users[] = array('id'=>$z->getID(), 'group'=>$z->actualGroup);
            }
        }
        
        //-----------------------------------------------------------
        
		return $result;	
	}
	
	public function save_result($result){
		global $DB;
		
		// TODO @Rene
		
	}
	
	
	
}