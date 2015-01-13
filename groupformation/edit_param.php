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
	require_once ($CFG->dirroot.'/mod/feedback/lib.php');
	require_once('lib.php');
	
//	$courseid = required_param('courseid', PARAM_INT);

	$id = optional_param('id', 0, PARAM_INT);   // Course Module ID
	$g = optional_param('g', 0, PARAM_INT);		// groupformation instance ID
	
	$do_show = optional_param('do_show', 'edit_param', PARAM_ALPHA);
	$current_tab = $do_show;
	
	$PAGE->set_url('/groupformation/edit_param.php', array('id' => $id, 'do_show' => $do_show));
	
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
	
	$context = context_module::instance($cm->id);
	
//	require_login($course);
	require_login($course, true, $cm);
	require_capability('mod/groupformation:editparam', $context);
//	$context = context_course::instance($courseid);
	
	
	$returnurl = $CFG->wwwroot.'/groupformation/index.php?id='.$course->id;
	
	
	$PAGE->set_title('edit_param');
	$PAGE->set_heading($course->fullname. ': '.'edit_param');
	$PAGE->set_pagelayout('admin');
	navigation_node::override_active_url(new moodle_url('/groupformation/index.php', array('id' => $id, 'do_show' => $do_show)));
	
	// 	// Print the page and form
	// 	$preview = '';
	$error = '';
	
	// 	/// Get applicable roles - used in menus etc later on
	// 	$rolenames = role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);
	
	require('tabs.php');
	//TODO Do we need a form for generating the groups?
	/// Create the form
	$paramform = new edit_param_form();
	$paramform->set_data(array('id' => $id, 'seed' => time()));
	
	
	/// Handle form submission
	if ($paramform->is_cancelled()) {
		redirect($returnurl);
	
	} elseif ($data = $paramform->get_data()) {
		
		// manipulate feedback 
		//feedbackattribut soll die Daten aus mod_feedback_mod_form imitieren
		$feedbackattribut = 
			  array('id' => '', //ist so gewollt
					'course' => $courseid,
					'name' => get_string('groupformationname', 'groupformation'),
					'intro' => 'some intro',
					'introformat' => '', //nur Platzhalter
			  		'anonymous' => 1,
			  		'email_notification' => 0,
			  		'multiple_submit' => 0,
			  		'autonumbering' => 0,
			  		'site_after_submit' => '', //ist so gewollt
			  		'page_after_submit' => 'page_after_submit',
			  		'page_after_submitformat' => '', //nur Platzhalter
			  		'publish_stats' => 0,
			  		'timeopen' => $data->assesstimestart,
			  		'timeclose' => $data->assesstimeend,
			  		'timemodified' => time(),
			  		'completionsubmit' => 0 //hier bin ich mir nicht sicher ob 0 oder 1
			);
		$feedbackid = feedback_add_instance($feedbackattribut);
		
//		feedback_init_feedback_session(); // nötig?
		
// 		if (! $feedback = $DB->get_record("feedback", array("id"=>$cm->instance))) {
// 			print_error('invalidcoursemodule');
// 		}
		/**
		 * typs: captcha info label multichoice multichoicerated numeric textarea textfield
		 */
		
		/**
		 * intro
		 * dataarray soll die Daten aus edit_item_form imitieren
		 */
		$dataarray = array(
				'id' => '',
				'feedback' => $feedbackid,
				'template' => 0,
				'name' => 'intro',
				'label' => '',
				'presentation' => 'Dies ist der Introtext', // TODO
				'type' => 'label',
				'position' => 0,
				'hasvalue' => 0,
				'required' => 0,
				'dependitem' => 0,
				'dependvalue' => "",
				'options' => 'options',
				
				'feedbackid' => $feedbackid,
				'templatedid' => 0,
				'itemname' => 'intro',
				'itemlabel' => '',
		);
		
		feedback_create_item($dataarray);
		//make a pagebreak
		feedback_create_pagebreak($feedbackid);
		
		/**
		 * motivation
		 */
		if($data->motivation == 'checked'){
			
		}
		
		if($data->lernstil == 'checked'){
			
		}
		
		//und soweiter
		groupformation_create_feedback($data, $feedbackid, $groupformation->id);
	}