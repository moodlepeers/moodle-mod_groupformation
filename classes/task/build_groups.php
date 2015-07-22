<?php

namespace mod_groupformation\task;

require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once ($CFG->dirroot . '/mod/groupformation/lib.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');
class build_groups extends \core\task\scheduled_task {
	
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
		
		// First reset aborted jobs; user might wanna use it soon
		$this->reset_aborted_jobs ();
		
		// Look for jobs; select a job; get it done
		$this->do_job ();
		
		return true;
	}
	
	/**
	 * Selects a waiting job, runs it and saves results
	 *
	 * @return boolean
	 */
	private function do_job() {
		$saved = false;
		$job = \mod_groupformation_job_manager::get_next_job ();
		if (! is_null ( $job )) {
			$result = \mod_groupformation_job_manager::do_groupal ( $job, $groupal_cohort, $random_cohort, $incomplete_cohort);
			$aborted = \mod_groupformation_job_manager::is_job_aborted ( $job );
			if (! $aborted) {
				$saved = \mod_groupformation_job_manager::save_result ( $job, $groupal_cohort );
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