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
 * Internal library of functions for module newmodule
 *
 * All the newmodule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_groupformation
 * @copyright 2014 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
/*
 * Does something really useful with the passed things
 *
 * @param array $things
 * @return object
 * function newmodule_do_something_useful(array $things) {
 * return new stdClass();
 * }
 */

require_once ($CFG->dirroot . '/mod/groupformation/classes/logging/logging_controller.php');

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
 * Logs action
 *
 * @param unknown $PAGE        	
 * @param string $filename        	
 */
function groupformation_log($userid, $groupformationid, $message) {
	$logging_controller = new mod_groupformation_logging_controller ();
	
	return $logging_controller->handle ( $userid, $groupformationid, $message );
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
	$event = \mod_groupformation\event\course_module_viewed::create ( array (
			'objectid' => $groupformation->id,
			'context' => $context 
	) );
	$event->add_record_snapshot ( 'course', $course );
	$event->add_record_snapshot ( $cm->modname, $groupformation );
	$event->trigger ();
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