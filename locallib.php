<?php
use mod_groupformation\task\build_groups_task;
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
 * Internal library of functions for module newmodule
 *
 * All the newmodule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_groupformation
 * @copyright 2015 MoodlePeers
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once ($CFG->dirroot . '/mod/groupformation/classes/controller/logging_controller.php');

/**
 * Adds jQuery
 *
 * @param unknown $PAGE        	
 * @param string $filename        	
 */
function groupformation_add_jquery($PAGE, $filename = null) {
	$PAGE->requires->jquery ();
	$PAGE->requires->jquery_plugin ( 'ui' );
	$PAGE->requires->jquery_plugin ( 'ui-css' );
	
	if (! is_null ( $filename )) {
		$PAGE->requires->js ( '/mod/groupformation/js/' . $filename );
	}
}

/**
 * Logs message
 *
 * @param integer $userid        	
 * @param integer $groupformationid        	
 * @param string $message        	
 * @param string $level        	
 * @return boolean
 */
function groupformation_log($userid, $groupformationid, $message, $level = 'info') {
	return false;
	// $logging_controller = new mod_groupformation_logging_controller ();
	// return $logging_controller->handle ( $userid, $groupformationid, $message, $level );
}

/**
 * Logs debug message
 *
 * @param integer $userid        	
 * @param integer $groupformationid        	
 * @param string $message        	
 * @param string $level        	
 * @return boolean
 */
function groupformation_debug($userid, $groupformationid, $message) {
	return groupformation_log ( $userid, $groupformationid, $message, $level = 'debug' );
}

/**
 * Logs info message
 *
 * @param integer $userid        	
 * @param integer $groupformationid        	
 * @param string $message        	
 * @param string $level        	
 * @return boolean
 */
function groupformation_info($userid, $groupformationid, $message) {
	return groupformation_log ( $userid, $groupformationid, $message, $level = 'info' );
}

/**
 * Logs warn message
 *
 * @param integer $userid        	
 * @param integer $groupformationid        	
 * @param string $message        	
 * @param string $level        	
 * @return boolean
 */
function groupformation_warn($userid, $groupformationid, $message) {
	return groupformation_log ( $userid, $groupformationid, $message, $level = 'warn' );
}

/**
 * Logs error message
 *
 * @param integer $userid        	
 * @param integer $groupformationid        	
 * @param string $message        	
 * @param string $level        	
 * @return boolean
 */
function groupformation_error($userid, $groupformationid, $message) {
	return groupformation_log ( $userid, $groupformationid, $message, $level = 'error' );
}

/**
 * Logs fatal message
 *
 * @param integer $userid        	
 * @param integer $groupformationid        	
 * @param string $message        	
 * @param string $level        	
 * @return boolean
 */
function groupformation_fatal($userid, $groupformationid, $message) {
	return groupformation_log ( $userid, $groupformationid, $message, $level = 'fatal' );
}

/**
 * Triggers event
 *
 * @param stdClass $cm        	
 * @param stdClass $course        	
 * @param stdClass $groupformation        	
 * @param stdClass $context        	
 */
function groupformation_trigger_event($cm, $course, $groupformation, $context) {
	
	// TODO Logging - We need to implement events to trigger
	
	// $event = \mod_groupformation\event\job_queued::create ( array (
	// 'objectid' => $groupformation->id,
	// 'context' => $context
	// ) );
	// $event->add_record_snapshot ( 'course', $course );
	// $event->add_record_snapshot ( $cm->modname, $groupformation );
	// $event->trigger ();
}

/**
 * Determines instances of course module, course and groupformation by id
 *
 * @param int $id        	
 * @param stdClass $cm        	
 * @param stdClass $course        	
 * @param stdClass $groupformation        	
 */
function groupformation_determine_instance($id, &$cm, &$course, &$groupformation) {
	global $DB;
	if ($id) {
		$cm = get_coursemodule_from_id ( 'groupformation', $id, 0, false, MUST_EXIST );
		$course = $DB->get_record ( 'course', array (
				'id' => $cm->course 
		), '*', MUST_EXIST );
		$groupformation = $DB->get_record ( 'groupformation', array (
				'id' => $cm->instance 
		), '*', MUST_EXIST );
		// } else if ($g) {
		// $groupformation = $DB->get_record ( 'groupformation', array ('id' => $g ), '*', MUST_EXIST );
		// $course = $DB->get_record ( 'course', array ('id' => $groupformation->course ), '*', MUST_EXIST );
		// $cm = get_coursemodule_from_instance ( 'groupformation', $groupformation->id, $course->id, false, MUST_EXIST );
	} else {
		error ( 'You must specify a course_module ID or an instance ID' );
	}
}

/**
 * Returns context for groupformation id
 *
 * @param int $groupformationid        	
 * @return context_course
 */
function groupformation_get_context($groupformationid) {
	$store = new mod_groupformation_storage_manager ( $groupformationid );
	
	$courseid = $store->getCourseID ();
	
	$context = context_course::instance ( $courseid );
	
	return $context;
}

/**
 *
 * @param stdClass $course        	
 * @param stdClass $cm        	
 * @param int $userid        	
 */
function groupformation_set_activity_completion($course, $cm, $userid) {
	$completion = new completion_info ( $course );
	$completion->set_module_viewed ( $cm, $userid );
}

/**
 * send confirmation for finishing group formation
 *
 * @param stdClass $recipient        	
 * @param string $subject        	
 * @param string $message        	
 *
 */
function groupformation_send_message($recipient, $subject, $messagetext, $contexturl = null, $contexturlname = null) {
	global $DB;
	
	// get admin user for setting as "userfrom"
	$admin = array_pop ( $DB->get_records ( 'user', array (
			'username' => 'admin' 
	) ) );
	
	// Prepare the message.
	$message = new \core\message\message ();
	$message->component = 'moodle';
	$message->name = 'instantmessage';
	$message->userfrom = $admin;
	$message->userto = $recipient;
	$message->subject = $subject;
	$message->fullmessage = $messagetext;
	$message->fullmessageformat = FORMAT_MARKDOWN;
	$message->fullmessagehtml = '<p>' . $messagetext . '</p>';
	$message->smallmessage = $messagetext;
	$message->notification = '0';
	$message->contexturl = $contexturl;
	$message->contexturlname = $contexturlname;
	$message->replyto = "noreply@moodle.com";
	$content = array (
			'*' => array (
					'header' => ' test ',
					'footer' => ' test ' 
			) 
	); // Extra content for specific processor
	$message->set_additional_content ( 'email', $content );
	
	// send message
	$messageid = message_send ( $message );
}
function groupformation_check_for_cron_job() {
	global $DB;
	
	$record = $DB->get_record ( 'task_scheduled', array (
			'component' => 'mod_groupformation' 
	) );
	$now = time ();
	$lastruntime = $record->lastruntime;
	
	if (($now - intval ( $lastruntime )) > 60 * 60 * 24) {
		echo '<div class="alert">' . get_string ( 'cron_job_not_running', 'groupformation' ) . '</div>';
	}
}
