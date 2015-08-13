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

require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/userid_filter.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/lib.php');
require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');

require_once ($CFG->dirroot . '/lib/groupal/classes/Criteria/SpecificCriterion.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/Participant.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/Cohort.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/Matcher/GroupALGroupCentricMatcher.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/GroupFormationAlgorithm.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/GroupFormationRandomAlgorithm.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/Optimizer/GroupALOptimizer.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/ParticipantWriter.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/CohortWriter.php');
class mod_groupformation_job_manager {
	
	/**
	 * Selects next job and sets it on "started"
	 *
	 * @return Ambigous <>
	 */
	public static function get_next_job() {
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
				ORDER BY timecreated ASC";
		$jobs = $DB->get_records ( 'groupformation_jobs', array (
				'waiting' => 1,
				'started' => 0,
				'aborted' => 0,
				'done' => 0 
		) );
		
		if (count ( $jobs ) == 0)
			return null;
		$next = null;
		foreach ( $jobs as $id => $job ) {
			if ($job->timecreated != null && ($next == null || $job->timecreated < $next->timecreated))
				$next = $job;
		}
		self::set_job ( $next, "started", true );
		
		groupformation_info ( null, $next->groupformationid, 'groupal job with groupformation id="' . $next->groupformationid . '" selected' );
		
		return $next;
	}
	
	/**
	 * Selects aborted but not started jobs and sets it on "started"
	 *
	 * @return Ambigous <>
	 */
	public static function get_aborted_jobs() {
		global $DB;
		$jobs = $DB->get_records ( 'groupformation_jobs', array (
				'waiting' => 0,
				'started' => 0,
				'aborted' => 1,
				'done' => 0,
				'timestarted' => 0 
		) );
		
		return $jobs;
	}
	
	/**
	 *
	 * Resets job to "ready"
	 *
	 * @param stdClass $job        	
	 */
	public static function reset_job($job) {
		self::set_job ( $job, "ready", false, true );
		groupformation_info ( null, $job->groupformationid, 'groupal job with groupformation id="' . $job->groupformationid . '" resetted' );
	}
	
	/**
	 *
	 * Sets job to state e.g. 1000
	 *
	 * @param stdClass $job        	
	 * @param string $state        	
	 */
	public static function set_job($job, $state = "ready", $settime = false, $resettime = false) {
		global $DB;
		$status_options = self::get_status_options ();
		
		if (array_key_exists ( $state, $status_options ))
			$status = $status_options [$state];
		else
			$status = $state;
		if (! (preg_match ( "/[0-1]{4}/", $status ) && strlen ( $status ) == 4))
			return false;
		
		$job->waiting = $status [0];
		$job->started = $status [1];
		$job->aborted = $status [2];
		$job->done = $status [3];
		
		if ($job->waiting == 1 && $settime) {
			$job->timecreated = time ();
			groupformation_info ( null, $job->groupformationid, 'groupal job set to waiting' );
		}
		if ($job->done == 1 && $settime) {
			$job->timefinished = time ();
			groupformation_info ( null, $job->groupformationid, 'groupal job set to done' );
		}
		if ($job->started == 1 && $settime) {
			$job->timestarted = time ();
			groupformation_info ( null, $job->groupformationid, 'groupal job set to started' );
		}
		if ($job->aborted == 1) {
			groupformation_info ( null, $job->groupformationid, 'groupal job set to aborted' );
		}
		if ($job->waiting == 0 && $resettime) {
			$job->timecreated = 0;
		}
		if ($job->done == 0 && $resettime) {
			$job->timefinished = 0;
		}
		if ($job->started == 0 && $resettime) {
			$job->timestarted = 0;
		}
		if ($resettime) {
			$job->matcher_used = null;
			$job->count_groups = null;
			$job->performance_index = null;
			$job->stats_avg_variance = null;
			$job->stats_variance = null;
			$job->stats_n = null;
			$job->stats_avg = null;
			$job->stats_st_dev = null;
			$job->stats_norm_st_dev = null;
			$job->stats_performance_index = null;
		}
		
		return $DB->update_record ( 'groupformation_jobs', $job );
	}
	
	/**
	 *
	 * Checks whether job is aborted or not
	 *
	 * @param stdClass $job        	
	 * @return boolean
	 */
	public static function is_job_aborted($job) {
		global $DB;
		
		return $DB->get_field ( 'groupformation_jobs', 'aborted', array (
				'id' => $job->id 
		) ) == '1';
	}
	
	/**
	 * Returns status options placed in define file
	 */
	public static function get_status_options() {
		$data = new mod_groupformation_data ();
		return $data->get_job_status_options ();
	}
	
	/**
	 * Generates participants with ids within interval
	 *
	 * @deprecated not in use?
	 * @param unknown $id_begin        	
	 * @param unknown $id_end        	
	 * @return multitype:Participant
	 */
	private static function get_testing_data($id_begin, $id_end) {
		
		// Dummy Criterions
		$c_vorwissen = new SpecificCriterion ( "vorwissen", array (
				0.4,
				0.8,
				0.8,
				0.4,
				0.4,
				0.4,
				0.4 
		), 0, 1, true, 1 );
		$c_note = new SpecificCriterion ( "note", array (
				0.4 
		), 0, 1, true, 1 );
		$c_persoenlichkeit = new SpecificCriterion ( "persoenlichkeit", array (
				0.4,
				0.4,
				0.4,
				0.4,
				0.4 
		), 0, 1, true, 1 );
		$c_motivation = new SpecificCriterion ( "motivation", array (
				0.4,
				0.4,
				0.4,
				0.4 
		), 0, 1, true, 1 );
		$c_lernstil = new SpecificCriterion ( "lernstil", array (
				0.4,
				0.4,
				0.4,
				0.4 
		), 0, 1, true, 1 );
		$c_teamorientierung = new SpecificCriterion ( "teamorientierung", array (
				0.4,
				0.4,
				0.4,
				0.4,
				0.4,
				0.4 
		), 0, 1, true, 1 );
		
		$criterion_types = array (
				$c_vorwissen,
				$c_motivation,
				$c_note,
				$c_persoenlichkeit,
				$c_lernstil,
				$c_teamorientierung 
		);
		$participants = array ();
		for($i = $id_begin; $i <= $id_end; $i ++) {
			$participants [] = new Participant ( array (
					$c_vorwissen,
					$c_motivation,
					$c_note,
					$c_persoenlichkeit,
					$c_lernstil,
					$c_teamorientierung 
			), $i );
		}
		
		return $participants;
	}
	
	/**
	 * Runs groupal with job
	 *
	 * @param stdClass $job        	
	 * @return array with 3 elements: groupal cohorts, random cohort and incomplete random cohort
	 */
	public static function do_groupal($job, &$groupal_cohort, &$random_cohort, &$incomplete_cohort) {
		global $CFG;
		
		$groupformationid = $job->groupformationid;
		
		$path = $CFG->dirroot . '/mod/groupformation/xml_participants/' . "php_" . $groupformationid;
		
		$store = new mod_groupformation_storage_manager ( $groupformationid );
		$groupsize = intval ( $store->getGroupSize () );
		
		$userfilter = new mod_groupformation_userid_filter ( $groupformationid );
		
		$completed_users = $userfilter->getCompletedIDs ();
		$nonecomplete_users = $userfilter->getNoneCompletedIds ();
		
		$store = new mod_groupformation_storage_manager ( $groupformationid );
		
		$courseid = $store->getCourseID ();
		
		$context = context_course::instance ( $courseid );
		
		$enrolled_students = array_keys ( get_enrolled_users ( $context, 'mod/groupformation:onlystudent' ) );
		
		$diff = array_diff ( $enrolled_students, $completed_users );
		$merge = array_unique ( array_merge ( $diff, $nonecomplete_users ) );
		
		$incomplete_users = $merge;
		
		$pp = new mod_groupformation_participant_parser ( $groupformationid );
		
		if (count ( $completed_users ) > 2) {
			$divided_userlist = array_chunk ( $completed_users, ceil ( count ( $completed_users ) / 2.0 ) );
			
			if (! is_null ( $divided_userlist [0] )) {
				$groupal_users = $divided_userlist [0];
			} else {
				$groupal_users = array ();
			}
			if (! is_null ( $divided_userlist [1] )) {
				$random_users = $divided_userlist [1];
			} else {
				$random_users = array ();
			}
		} else {
			$groupal_users = $completed_users;
			$random_users = array ();
		}
		
		$starttime = microtime ( true );
		
		// Generate participants for Groupal
		$participants = $pp->build_participants ( $groupal_users );
		// $participants = self::get_testing_data ( 1, 20 );
		$groupal_participants = $participants;
		
		// TODO XML WRITER : einkommentieren falls benötigt
		// $participant_writer = new participant_writer ( $path . "_participants.xml" );
		// $participant_writer->write ( $groupal_participants );
		
		// Generate empty participants
		$participants = $pp->build_empty_participants ( $random_users );
		$random_participants = $participants;
		
		// Generate empty participants
		$participants = $pp->build_empty_participants ( $incomplete_users );
		$incomplete_participants = $participants;
		
		$endtime = microtime ( true );
		$comptime = $endtime - $starttime;
		
		groupformation_info ( null, $job->groupformationid, 'building participants for groupal needed ' . $comptime . 'ms' );
		
		// var_dump ($random_participants,$incomplete_participants);
		
		$store = new mod_groupformation_storage_manager ( $groupformationid );
		$groupsize = intval ( $store->getGroupSize () );
		
		if (count ( $groupal_participants ) > 0) {
			
			// Matcher (einer von beiden)
			$matcher = new GroupALGroupCentricMatcher ();
			
			$starttime = microtime ( true );
			
			$gfa = new GroupFormationAlgorithm ( $groupal_participants, $matcher, $groupsize );
			$groupal_cohort = $gfa->doOneFormation (); // this call takes time...
			
			$endtime = microtime ( true );
			$comptime = $endtime - $starttime;
			
			groupformation_info ( null, $job->groupformationid, 'groupal needed ' . $comptime . 'ms' );
		}
		
		if (count ( $random_participants ) > 0) {
			$gfra = new GroupFormationRandomAlgorithm ( $random_participants, $groupsize );
			$random_cohort = $gfra->doOneFormation ();
		}
		
		if (count ( $incomplete_participants ) > 0) {
			$gfra = new GroupFormationRandomAlgorithm ( $incomplete_participants, $groupsize );
			$incomplete_cohort = $gfra->doOneFormation ();
		}
		
		// var_dump ( $groupal_cohort );
		// var_dump ( $random_cohort );
		// var_dump ( $incomplete_cohort );
		
		// TODO XML WRITER : einkommentieren falls benötigt
		// $cohort_writer = new cohort_writer($path."_cohort.xml");
		// $cohort_writer->write($groupal_cohort);
		
		$cohorts = array (
				$groupal_cohort,
				$random_cohort,
				$incomplete_cohort 
		);
		
		return $cohorts;
	}
	
	/**
	 * Saves results
	 *
	 * @param stdClass $job        	
	 * @param stdClass $result        	
	 * @return boolean
	 */
	public static function save_result($job, &$groupal_cohort = null, &$random_cohort = null, &$incomplete_cohort = null) {
		global $DB;
		
		if (! is_null ( $groupal_cohort )) {
			
			$result = $groupal_cohort->getResult ();
			
			$flags = array (
					"groupal" => 1,
					"random" => 0,
					"mrandom" => 0,
					"created" => 0 
			);
			
			$idmap = self::create_groups ( $job, $result->groups, $flags );
			
			self::assign_users_to_groups ( $job, $result->users, $idmap );
			
			self::save_stats ( $job, $groupal_cohort );
		}
		
		if (! is_null ( $random_cohort )) {
			$result = $random_cohort->getResult ();
			
			$flags = array (
					"groupal" => 0,
					"random" => 0,
					"mrandom" => 1,
					"created" => 0 
			);
			
			$idmap = self::create_groups ( $job, $result->groups, $flags );
			
			self::assign_users_to_groups ( $job, $result->users, $idmap );
		}
		
		if (! is_null ( $incomplete_cohort )) {
			$result = $incomplete_cohort->getResult ();
			
			$flags = array (
					"groupal" => 0,
					"random" => 1,
					"mrandom" => 0,
					"created" => 0 
			);
			
			$idmap = self::create_groups ( $job, $result->groups, $flags );
			
			self::assign_users_to_groups ( $job, $result->users, $idmap );
		}
		
		self::set_job ( $job, 'done', true );
		
		groupformation_info ( null, $job->groupformationid, 'groupal results saved' );
		
		return true;
	}
	
	/**
	 * Saves stats for computed job
	 *
	 * @param unknown $job        	
	 * @param unknown $cohort        	
	 */
	private static function save_stats($job, &$groupal_cohort = null) {
		global $DB;
		
		$job->matcher_used = strval ( $groupal_cohort->whichMatcherUsed );
		$job->count_groups = floatval ( $groupal_cohort->countOfGroups );
		$job->performance_index = floatval ( $groupal_cohort->cohortPerformanceIndex );
		
		groupformation_info ( null, null, $job->matcher_used . "yay" );
		
		$stats = $groupal_cohort->results;
		
		$job->stats_avg_variance = $stats->averageVariance;
		$job->stats_variance = $stats->variance;
		$job->stats_n = $stats->n;
		$job->stats_avg = $stats->avg;
		$job->stats_st_dev = $stats->stDev;
		$job->stats_norm_st_dev = $stats->normStDev;
		$job->stats_performance_index = $stats->performanceIndex;
		
		$DB->update_record ( 'groupformation_jobs', $job );
	}
	
	/**
	 * Creates groups generated by GroupAL
	 *
	 * @param stdClass $job        	
	 * @param unknown $groupids        	
	 * @return boolean
	 */
	private static function create_groups($job, $groups, $flags) {
		$groupformationid = $job->groupformationid;
		
		$groups_store = new mod_groupformation_groups_manager ( $groupformationid );
		
		$store = new mod_groupformation_storage_manager ( $groupformationid );
		
		$groupname_prefix = $store->get_group_name_setting ();
		$groupformationname = $store->getName ();
		
		$groupname = "";
		$i = $store->getInstanceNumber ();
		
		if (strlen ( $groupname_prefix ) < 1) {
			$groupname = "G" . $i . "_" . substr ( $groupformationname, 0, 8 ) . "_";
		} else {
			$groupname = "G" . $i . "_" . $groupname_prefix . "_";
		}
		
		$ids = array ();
		foreach ( $groups as $groupalid => $group ) {
			+ $name = $groupname . strval ( $groupalid );
			$db_id = $groups_store->create_group ( $groupalid, $group, $name, $groupformationid, $flags );
			$ids [$groupalid] = $db_id;
		}
		
		return $ids;
	}
	
	/**
	 *
	 * Assign users to groups
	 *
	 * @param stdClass $job        	
	 * @param unknown $users        	
	 * @param unknown $idmap        	
	 */
	private static function assign_users_to_groups($job, $users, $idmap) {
		$groupformationid = $job->groupformationid;
		
		$groups_store = new mod_groupformation_groups_manager ( $groupformationid );
		
		foreach ( $users as $userid => $groupalid ) {
			$groups_store->assign_user_to_group ( $groupformationid, $userid, $groupalid, $idmap );
		}
	}
	
	/**
	 * Creates job for groupformation instance
	 *
	 * @param integer $groupformationid        	
	 */
	public static function create_job($groupformationid) {
		global $DB;
		
		$job = new stdClass ();
		$job->groupformationid = $groupformationid;
		$job->waiting = 0;
		$job->started = 0;
		$job->aborted = 0;
		$job->done = 0;
		$job->timecreated = 0;
		$job->timestarted = 0;
		$job->timefinished = 0;
		
		$DB->insert_record ( 'groupformation_jobs', $job );
	}
	
	/**
	 * Returns job for groupformation
	 *
	 * @param integer $groupformationid        	
	 * @return stdClass
	 */
	public static function get_job($groupformationid) {
		global $DB;
		if ($DB->record_exists ( 'groupformation_jobs', array (
				'groupformationid' => $groupformationid 
		) )) {
			return $DB->get_record ( 'groupformation_jobs', array (
					'groupformationid' => $groupformationid 
			) );
		} else {
			$record = new stdClass ();
			$record->groupformationid = $groupformationid;
			$DB->insert_record ( 'groupformation_jobs', $record );
			return $DB->get_record ( 'groupformation_jobs', array (
					'groupformationid' => $groupformationid 
			) );
		}
	}
	
	/**
	 * Returns job status -> to compare use $data->get_job_status_options()
	 *
	 * @param stdClass $job        	
	 * @return String
	 */
	public static function get_status($job) {
		$data = new mod_groupformation_data ();
		$status_options = array_keys ( $data->get_job_status_options () );
		if ($job->waiting) {
			return $status_options [1];
		} elseif ($job->started) {
			return $status_options [2];
		} elseif ($job->aborted) {
			return $status_options [3];
		} elseif ($job->done) {
			return $status_options [4];
		} else {
			return $status_options [0];
		}
	}
}