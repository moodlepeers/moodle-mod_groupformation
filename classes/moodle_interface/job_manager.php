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
		
		$result = new stdClass();
		$result->groupids = array(1, 2);
		$result->groups = array(1=>array(2,3),2=>array(1,4,5));
		$result->users = array(1=>2,2=>1,3=>1,4=>2,5=>2);
		return $result;	
	}
	
	public function save_result($result){
		global $DB;
		
		// TODO @Rene
		
	}
	
	
	
}