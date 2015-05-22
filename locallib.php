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

/**
 * add jquery to view
 *
 * @param unknown $PAGE        	
 * @param string $filename        	
 */
function addjQuery($PAGE, $filename = null) {
	$PAGE->requires->jquery ();
	
	if (! is_null ( $filename )) {
		$PAGE->requires->js ( '/mod/groupformation/js/' . $filename );
	}
}

/**
 * sets language depended on moodle config and course config
 *
 * @return string - language for showing questions
 */
function get_language() {
	global $COURSE, $CFG;
	return ($CFG->lang != $COURSE->lang) ? (($COURSE->lang != '' && $COURSE->lang != null) ? $COURSE->lang : $CFG->lang) : $CFG->lang;
}