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

	require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
	require_once(dirname(__FILE__).'/edit_param_form.php');
	
// 	global $CFG;
// 	require_once $CFG->dirroot.'/mod/groupformation/classes/lecturer_settings/setting_manager.php';
	require_once(dirname(__FILE__).'/setting_manager.php');

	
//	$courseid = required_param('courseid', PARAM_INT);

	$id = optional_param('id', 0, PARAM_INT);   // Course Module ID
	$g = optional_param('g', 0, PARAM_INT);		// groupformation instance ID
	
	
	$current_tab = 'edit_param';

	if($id) {
		$cm = get_coursemodule_from_id('groupformation', $id, 0, false, MUST_EXIST);
		$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
		$groupformation = $DB->get_record('groupformation', array('id' => $cm->instance), '*', MUST_EXIST);
	} else if($g) {
		$groupformation = $DB->get_record('groupformation', array('id' => $g), '*', MUST_EXIST);
		$course = $DB->get_record('course', array('id' => $groupformation->course), '*', MUST_EXIST);
		$cm = get_coursemodule_from_instance('groupformation', $groupformation->id, $course->id, false, MUST_EXIST);
	} else {
		error('You must specify a course_module ID or an instance ID');
	}
	
// 	if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
// 		print_error('invalidcourseid');
// 	}

	//wichtig!
	$context = context_module::instance($cm->id);
	
//	require_login($course);
	require_login($course, true, $cm);
//	require_capability('mod/groupformation:editparam', $context);
//	$context = context_course::instance($courseid);
	
	
	$PAGE->set_url('/mod/groupformation/edit_param.php', array('id' => $cm->id, 'do_show' => 'edit_param'));
	$PAGE->set_title(format_string($groupformation->name));
	$PAGE->set_heading(format_string($course->fullname));
	
	//$returnurl = $CFG->wwwroot.'/groupformation/index.php?id='.$course->id;
	$returnurl = new moodle_url('/mod/groupformation/view.php', array('id' => $cm->id, 'do_show' => 'view'));
	
//	$PAGE->set_title('edit_param');
// 	$PAGE->set_heading($course->fullname. ': '.'edit_param');
// 	$PAGE->set_pagelayout('admin');
// 	navigation_node::override_active_url(new moodle_url('/groupformation/index.php', array('id' => $id)));
	
	// 	// Print the page and form
	// 	$preview = '';
	$error = '';
	
	// 	/// Get applicable roles - used in menus etc later on
	// 	$rolenames = role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);
	
	$mform = new mod_groupformation_edit_param_form();
	
	if ($mform->is_cancelled()) {
		redirect($returnurl);
	
	} elseif ($data = $mform->get_data()) {
	
	
		// 		$coursename = $course->name;
		// 		$intro = "<p>In der Lehrveranstaltung $coursename wird Gruppenarbeit
		// 					eingesetzt. Um eine möglichst optimale Zusammensetzung aller Gruppen zu erzielen,
		// 					sollen mit diesem Fragebogen Ihr Vorwissen, Ihre Interessen und Ihre Motivation
		// 					erfasst werden. Die Software bildet dann die Gruppen so, dass die zu erwartende
		// 					Zufriedenheit aller Teilnehmer und Leistungen aller Gruppen möglichst hoch sind.
		// 					Ihre Eingaben sind von niemandem (auch nicht dem Dozenten) einsehbar <br></p>";
	
	
		$knowledges = $data->knowledgeValues;
		$knowledgearray = explode("\n", $knowledges);
	
	
		$topics = $data->topicValues;
		$topicsarray = explode("\n", $topics);
		
		var_dump($topicsarray);
	
		$settings = new mod_groupformation_setting_manager($groupformation->id, $data->szenario, $topicsarray, $knowledgearray);
	
		$settings->create_Questions(TRUE);
		$settings->save_settings();
	
		redirect($returnurl);
	}
	
	echo $OUTPUT->header();
	
	require('tabs.php');
	//TODO Do we need a form for generating the groups?
	/// Create the form
	echo $OUTPUT->box_start('generalbox errorboxcontent boxaligncenter boxwidthnormal');

	//$mform = new mod_groupformation_edit_param_form();
	$mform->display();
	
	echo $OUTPUT->box_end();
	
	
	echo $OUTPUT->footer();