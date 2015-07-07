<?php
namespace mod_groupformation\task;

require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');

class build_groups extends \core\task\scheduled_task {
	public function get_name() {
		// Shown in admin screens
		return "groupformation_job_build_groups";
	}
	 
	public function execute() {
		sleep(180);
// 		$jm = new mod_groupformation_job_manager();
		/* 
		$job = get_next_job()
		 
		$result = do_groupal($job)
		 
		if (!is_aborted($job)) {
			save_results($job,$results)
			set_job($job,"done");
		} else {
			set_job($job, "ready");
		}
		
		// DONE
		 */
	}
}