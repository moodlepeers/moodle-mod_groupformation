<?php
namespace mod_groupformation\task;

require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');

class build_groups extends \core\task\scheduled_task {
	public function get_name() {
		// Shown in admin screens
		return "test_groupformation_task";
	}
	 
	public function execute() {
		groupformation_log(1,1,"<update_instance>");
		sleep(180);
		groupformation_log(1,1,"<view_settings>");
	}
}