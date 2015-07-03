<?php
namespace mod_groupformation\task;

require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');

class build_groups extends \core\task\scheduled_task {
	public function get_name() {
		// Shown in admin screens
		return "groupformation_job_build_groups";
	}
	 
	public function execute() {
		sleep(180);
	}
}