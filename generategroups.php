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
 * Create and allocate users to groups
 * This code is extracted out of /group/autogroup.php
 *
 * @package    mod_groupformation
 * @copyright  Nora Wester
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

	require_once('../config.php');
	require_once('lib.php');
	require_once($CFG->dirroot.'/group/lib.php');
	require_once($CFG->dirroot.'/lib/groupal/groupal.php');
	
	$courseid = required_param('courseid', PARAM_INT);
	$PAGE->set_url('/groupformation/generategroups.php', array('courseid' => $courseid));
	
	if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
		print_error('invalidcourseid');
	}
	
	// Make sure that the user has permissions to manage groups.
	require_login($course);
	
	$context = context_course::instance($courseid);
	require_capability('moodle/course:managegroups', $context);
	
	$returnurl = $CFG->wwwroot.'/groupformation/index.php?id='.$course->id;

	$strgroups           = get_string('groups');
	$strparticipants     = get_string('participants');
	$strautocreategroups = get_string('generategroups', 'groupformation');
	
	$PAGE->set_title($strgroups);
	$PAGE->set_heading($course->fullname. ': '.$strgroups);
	$PAGE->set_pagelayout('admin');
	navigation_node::override_active_url(new moodle_url('/groupformation/index.php', array('id' => $courseid)));
	
// 	// Print the page and form
// 	$preview = '';
 	$error = '';
	
// 	/// Get applicable roles - used in menus etc later on
// 	$rolenames = role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);
	
	//TODO Do we need a form for generating the groups?
	/// Create the form
	$generateform = new generategroups_form();
	$generateform->set_data(array('courseid' => $courseid, 'seed' => time()));
	
	
	/// Handle form submission
	if ($generateform->is_cancelled()) {
		redirect($returnurl);
	
	} elseif ($data = $generateform->get_data()) {
	
// 		//do something with the data
// 		$data->elementname; 
		
		$userpergrp = $data->userpergroup;
		$groupal = new groupal();
		$users = $groupal->getUser($userpergrp);
		$usercnt = count($users);
		$numgrps = $groupal->numberOfGroups();
	
		$groups = array();
		
		// allocate the users 
		for ($i=0; $i<$numgrps; $i++) {
			$groups[$i] = array();
			$groups[$i]['name']    = groups_parse_name(trim($data->namingscheme), $i);
			$groups[$i]['members'] = array();
			
			for ($j=0; $j<$userpergrp; $j++) {
				if (empty($users)) {
					break 2;
				}
				$user = array_shift($users);
				$groups[$i]['members'][$user->id] = $user;
			}
		}
	
// 		if (isset($data->preview)) {
// 			$table = new html_table();
// 			$table->head  = array(get_string('groupscount', 'groupformation', $numgrps));
// 			$table->size  = array('100%');
// 			$table->align = array('left');
// 			$table->width = '40%';
// 			$table->data  = array();
	
// 			foreach ($groups as $group) {
// 				$line = array();
// 				if (groups_get_group_by_name($courseid, $group['name'])) {
// 					$line[] = '<span class="notifyproblem">'.get_string('groupnameexists', 'groupformation', $group['name']).'</span>';
// 					$error = get_string('groupnameexists', 'groupformation', $group['name']);
// 				} else {
// 					$line[] = $group['name'];
// 				}
// 				$table->data[] = $line;
// 			}
	
// 			$preview .= html_writer::table($table);
	
// 		} 
			
		/**
		 * NO GROUPING
		 */
	
			$createdgroups = array();
			$failed = false;
	
			// Save the groups data
			foreach ($groups as $key=>$group) {
				if (groups_get_group_by_name($courseid, $group['name'])) {
					$error = get_string('groupnameexists', 'groupformation', $group['name']);
					$failed = true;
					break;
				}
				$newgroup = new stdClass();
				$newgroup->courseid = $data->courseid;
				$newgroup->name     = $group['name'];
				$groupid = groups_create_group($newgroup);
				$createdgroups[] = $groupid;
				foreach($group['members'] as $user) {
					groups_add_member($groupid, $user->id);
				}
			}
	
			// Invalidate the course groups cache seeing as we've changed it.
			cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($courseid));
	
			if ($failed) {
				foreach ($createdgroups as $groupid) {
					groups_delete_group($groupid);
				}
				if ($createdgrouping) {
					groups_delete_grouping($createdgrouping);
				}
			} else {
				redirect($returnurl);
			}
		}
	
	
	$PAGE->navbar->add($strparticipants, new moodle_url('/user/index.php', array('id'=>$courseid)));
	$PAGE->navbar->add($strgroups, new moodle_url('/groupformation/index.php', array('id'=>$courseid)));
	$PAGE->navbar->add($strautocreategroups);
	
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strautocreategroups);
	
	if ($error != '') {
		echo $OUTPUT->notification($error);
	}
	
	/// Display the form
	$generateform->display();
	
// 	if($preview !== '') {
// 		echo $OUTPUT->heading(get_string('groupspreview', 'groupformation'));
	
// 		echo $preview;
// 	}
	
	echo $OUTPUT->footer();
	


