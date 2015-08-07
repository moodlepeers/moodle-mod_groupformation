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
 * Task - Cron
 *
 * @package mod_groupformation
 * @author Rene & Ahmed
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_groupformation\task;

require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once ($CFG->dirroot . '/mod/groupformation/lib.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');
class build_groups_task extends \core\task\scheduled_task {
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \core\task\scheduled_task::get_name()
	 */
	public function get_name() {
		// Shown in admin screens
		return "groupformation_job_build_groups";
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \core\task\task_base::execute()
	 */
	public function execute() {
// 		groupformation_info ( null, null, 'cron job started' );
		
		// First reset aborted jobs; user might wanna use it soon
// 		$this->reset_aborted_jobs ();
		
		// Look for jobs; select a job; get it done
// 		$this->do_job ();
		
// 		groupformation_info ( null, null, 'cron job terminated' );
		
		return true;
	}
	
	/**
	 * Selects a waiting job, runs it and saves results
	 *
	 * @return boolean
	 */
	private function do_job() {
		$saved = false;
		
		$job = null;
		$groupal_cohort = null;
		$random_cohort = null;
		$incomplete_cohort = null;
		
		$job = \mod_groupformation_job_manager::get_next_job ();
		
		if (! is_null ( $job )) {
			$result = \mod_groupformation_job_manager::do_groupal ( $job, $groupal_cohort, $random_cohort, $incomplete_cohort );
			$aborted = \mod_groupformation_job_manager::is_job_aborted ( $job );
			if (! $aborted) {
				
				$saved = \mod_groupformation_job_manager::save_result ( $job, $groupal_cohort, $result [1], $result [2] );
			}
		}
		return $saved;
	}
	
	/**
	 * Resets all aborted jobs which are not currently running
	 */
	private function reset_aborted_jobs() {
		$jobs = \mod_groupformation_job_manager::get_aborted_jobs ();
		foreach ( $jobs as $key => $job ) {
			\mod_groupformation_job_manager::reset_job ( $job );
		}
	}
}