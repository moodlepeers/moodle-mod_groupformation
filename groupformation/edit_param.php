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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * 
 *
 * @package    mod_groupformation
 * @copyright  Nora Wester
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

	require_once('../config.php');
	require_once('lib.php');
	
	$courseid = required_param('courseid', PARAM_INT);
	$PAGE->set_url('/groupformation/edit_param.php', array('courseid' => $courseid));
	
	if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
		print_error('invalidcourseid');
	}
	
	// Make sure that the user has permissions to manage groups.
	require_login($course);
	
	$context = context_course::instance($courseid);
	require_capability('moodle/course:managegroups', $context);
	
	$returnurl = $CFG->wwwroot.'/groupformation/index.php?id='.$course->id;
	
	
	$PAGE->set_title('edit_param');
	$PAGE->set_heading($course->fullname. ': '.'edit_param');
	$PAGE->set_pagelayout('admin');
	navigation_node::override_active_url(new moodle_url('/groupformation/index.php', array('id' => $courseid)));
	
	// 	// Print the page and form
	// 	$preview = '';
	$error = '';
	
	// 	/// Get applicable roles - used in menus etc later on
	// 	$rolenames = role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);
	
	//TODO Do we need a form for generating the groups?
	/// Create the form
	$paramform = new edit_param_form();
	$paramform->set_data(array('courseid' => $courseid, 'seed' => time()));
	
	
	/// Handle form submission
	if ($paramform->is_cancelled()) {
		redirect($returnurl);
	
	} elseif ($data = $paramform->get_data()) {
		
		// manipulate feedback 
	}