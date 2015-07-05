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
        require_once($CFG->dirroot.'/lib/groupal/classes/GroupFormationAlgorithm.php');
        require_once($CFG->dirroot.'/lib/groupal/classes/Optimizer/GroupALOptimizer.php');


class mod_groupformation_job_manager {		
	
	/**
	 * Selects next job and sets it on "started"
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
		
		/**
         * <Testdaten>------------------------------------------
         */

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
		//$gcm = new GroupALGroupCentricMatcher();
        $matcher = new GroupALGroupCentricMatcher();
        $gal = new GroupFormationAlgorithm($users, $matcher, new GroupALOptimizer($matcher), 3);

        $cohort = $gal->doOneFormation();

        return $cohort->getResult();
	}
	
	/**
	 * Saves results
	 * 
	 * @param stdClass $job
	 * @param stdClass $result
	 * @return boolean
	 */
	public function save_result($job, $result){
		global $DB;
		
		$flags = array("groupal"=>1,"random"=>0,"mrandom"=>0,"created"=>0);
		$idmap = $this->create_groups($job, $result->groups,$flags);
		
		$this->assign_users_to_groups($job, $result->users, $idmap);
		
		return true;
	}
	
	/**
	 * Creates groups generated by GroupAL
	 * 
	 * @param stdClass $job
	 * @param unknown $groupids
	 * @return boolean
	 */	
	public function create_groups($job, $groups, $flags){
		
		$groupformationid = $job->groupformationid;
		
		$store = new mod_groupformation_storage_manager($groupformationid);
		
		$groupname_prefix = $store->getGroupName();
		$groupformationname = $store->getName();
		
		$groupname = "";
	
		if (strlen($groupname_prefix)<1){
			$groupname = "G_".substr($groupformationname, 0, 8)."_";
		}else{
			$groupname = "G_".$groupname_prefix."_";
		}
		
		$ids = array();
		foreach ($groups as $group){+
			$groupalid = $group->getID();
			$name = $groupname.strval($groupalid);
			$id = $this->create_group($groupalid, $name, $groupformationid,$flags);
			$ids[$groupalid] = $id;
		}
		
		return $ids;
	}
	
	/**
	 * Creates group instance in DB
	 * 
	 * @param integer $groupalid
	 * @param unknown $name
	 * @param integer $groupformationid
	 * @return Ambigous <boolean, number>
	 */
	public function create_group($groupalid, $name, $groupformationid,$flags){
		global $DB;
		
		$record = new stdClass();
		$record->groupformation = $groupformationid;
		$record->moodlegroupid = null;
		$record->groupname = $name;
		$record->groupal = $flags['groupal'];
		$record->random = $flags['random'];
		$record->mrandom = $flags['random'];
		$record->created = $flags['created'];
		
		$id = $DB->insert_record('groupformation_groups', $record);
		
		return $id;
	}
	
	
	/**
	 * 
	 * Assign users to groups
	 * 
	 * @param stdClass $job
	 * @param unknown $users
	 * @param unknown $idmap
	 */
	public function assign_users_to_groups($job, $users, $idmap){
		
		$groupformationid = $job->groupformationid;
		
		foreach($users as $key => $user){
			$this->assign_user_to_group($groupformationid,$user['id'],$user['group'],$idmap);
		}
		
	}
	
	/**
	 * Creats user-group instance in DB
	 * 
	 * @param integer $groupformationid
	 * @param integer $userid
	 * @param unknown $usergroup
	 * @param unknown $idmap
	 */
	public function assign_user_to_group($groupformationid,$userid,$usergroup,$idmap){
		global $DB;
		
		$record = new stdClass();
		$record->groupformation = $groupformationid;
		$record->userid = $userid;
		$record->groupid = $idmap[$usergroup];
		
		return $DB->insert_record('groupformation_group_users', $record);
	}
	
	/**
	 * Creates job for groupformation instance
	 * 
	 * @param integer $groupformationid
	 */
	public static function create_job($groupformationid){
		global $DB;
		
		$job = new stdClass();
		$job->groupformationid = $groupformationid;
		$job->waiting = 0;
		$job->started = 0;
		$job->aborted = 0;
		$job->done = 0;
		$job->timecreated = null;
		$job->timestarted = null;
		$job->timefinished = null;
		
		$DB->insert_record('groupformation_jobs', $job);				
	}
	
	/**
	 * Returns job for groupformation
	 * 
	 * @param integer $groupformationid
	 * @return stdClass
	 */
	public static function get_job($groupformationid){
		global $DB;
		return $DB->get_record('groupformation_jobs', array('groupformationid'=>$groupformationid));
	}
	
	/**
	 * Returns job status -> to compare use $data->get_job_status_options()
	 * 
	 * @param stdClass $job
	 * @return String
	 */
	public static function get_status($job){
		$data = new mod_groupformation_data();
		$status_options = $data->get_job_status_options();
		if ($job->waiting){
			return $status_options[1];
		}elseif ($job->started) {
			return $status_options[2];
		}elseif ($job->aborted) {
			return $status_options[3];
		}elseif ($job->done) {
			return $status_options[4];
		}else{
			return $status_options[0];
		}
	}
}