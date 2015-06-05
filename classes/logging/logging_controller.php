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
 * Logging controller
 *
 * @package mod_groupformation
 * @author Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

// define('MOTIVATION', 7);
// define('TEAM', 4);
// define('LEARNING', 6);
// define('CHARACTER', 5);
// define('GENERAL', 2);
// define('KNOWLEDGE', 1);
// define('TOPIC', 0);
// define('GRADE', 3);
class mod_groupformation_logging_controller {
	
	const LOGGING_TABLE_NAME = "groupformation_logging";
	private $MESSAGES = array('<index>', '<settings>', '<begin_questionaire>', '<category_questionaire>', 
				'<continue_questionaire>', '<submit_questionaire>', '<start_groupal>', 
				'<cancel_groupal>', '<restart_groupal>', '<view_results_groupal>', '<accept_results_groupal>', 
				'<delete_results_groupal>', '<delete>', '<unknown>');
	
	/**
	 * Handles data and tries logging it
	 * 
	 * @param int $userid
	 * @param int $groupformationid
	 * @param string $message
	 * @return boolean
	 */
	public function handle($userid,$groupformationid,$message){
		if (!$this->isValidMessage($message))
			return false;
		else
			$this->create_log_entry($userid, $groupformationid, $message);
	}
	
	/**
	 * Creates log entry in database 
	 * 
	 * @param int $userid
	 * @param int $groupformationid
	 * @param string $message
	 */
	public function create_log_entry($userid,$groupformationid,$message){
		global $DB;
		$timestamp = time();
		
		$log_entry = new stdClass();
		$log_entry->timestamp = $timestamp;
		$log_entry->userid = $userid;
		$log_entry->groupformationid = $groupformationid;
		$log_entry->message = $message;
		
		$DB->insert_record(self::LOGGING_TABLE_NAME, $log_entry);
	}
	
	/**
	 * Checks if message is valid
	 * 
	 * @param string $message
	 * @return boolean
	 */
	private function isValidMessage($message){
		return in_array($message, $this->MESSAGES);
	}
}
