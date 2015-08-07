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
class mod_groupformation_logging_controller {
	const LOGGING_LEVEL = 4; // <= Sperre
	const FATAL = 0;
	const ERROR = 1;
	const WARNING = 2;
	const INFO = 3;
	const DEBUG = 4;
	private $LOGGING_LEVELS;
	const LOGGING_TABLE_NAME = "groupformation_logging";
	private $MESSAGES = array (
			'<index>' => 3,
			
			'<create_instance>' => 3,
			'<update_instance>' => 3,
			'<delete_instance>' => 3,
			
			'<view_settings>' => 3,
			'<save_settings>' => 3,
			
			'<view_student_overview>' => 3,
			'<view_student_questionaire>' => 3,
			'<view_student_evaluation>' => 3,
			'<view_student_group_assignment>' => 3,
			
			'<view_teacher_overview>' => 3,
			'<view_teacher_grouping>' => 3,
			'<view_teacher_questionaire_preview>' => 3,
			
			'<view_questionaire_category_topic>' => 3,
			'<view_questionaire_category_knowledge>' => 3,
			'<view_questionaire_category_team>' => 3,
			'<view_questionaire_category_character>' => 3,
			'<view_questionaire_category_motivation>' => 3,
			'<view_questionaire_category_learning>' => 3,
			'<view_questionaire_final_page>' => 3,
			
// 			'<begin_questionaire>' => 3,
// 			'<view_category_questionaire>' => 3,
// 			'<continue_questionaire>' => 3,
// 			'<submit_questionaire>' => 3,
// 			'<start_groupal>' => 3,
// 			'<cancel_groupal>' => 3,
// 			'<restart_groupal>' => 3,
// 			'<view_results_groupal>' => 3,
// 			'<accept_results_groupal>' => 3,
// 			'<delete_results_groupal>' => 3,
// 			'<unknown>' => 3 
	);
	
	/**
	 * Creates logging controller instance
	 */
	public function __construct() {
		$this->LOGGING_LEVELS = array (
				self::FATAL => 'fatal',
				self::ERROR => 'error',
				self::WARNING => 'warning',
				self::INFO => 'info',
				self::DEBUG => 'debug' 
		);
	}
	
	/**
	 * Handles data and tries logging it
	 *
	 * @param int $userid        	
	 * @param int $groupformationid        	
	 * @param string $message        	
	 * @return boolean
	 */
	public function handle($userid, $groupformationid, $message, $level) {
		if (!is_null($message) && is_string($message))
			$this->create_log_entry ( $userid, $groupformationid, $message );
		else
			return false;
	}
	
	/**
	 * Creates log entry in database
	 *
	 * @param int $userid        	
	 * @param int $groupformationid        	
	 * @param string $message        	
	 */
	private function create_log_entry($userid, $groupformationid, $message) {
		global $DB;
		$timestamp = microtime (true);
		
		$log_entry = new stdClass ();
		$log_entry->timestamp = $timestamp;
		$log_entry->userid = $userid;
		$log_entry->groupformationid = $groupformationid;
		$log_entry->message = $message;
		
		$DB->insert_record ( self::LOGGING_TABLE_NAME, $log_entry );
	}
	
	/**
	 * Checks if message is valid
	 *
	 * @param string $message        	
	 * @return boolean
	 */
	private function isValidMessage($message) {
		return key_exists ( $message, $this->MESSAGES ) && $this->MESSAGES [$message] <= self::LOGGING_LEVEL;
	}
}
