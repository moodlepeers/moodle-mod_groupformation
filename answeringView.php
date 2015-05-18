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
 * Prints a particular instance of groupformation
 *
 * @package mod_groupformation
 * @author  Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


	require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
	require_once(dirname(__FILE__).'/lib.php');
// 	require_once ($CFG->dirroot.'/mod/feedback/lib.php');



// 	$PAGE->requires->js($CFG->dirroot.'/mod/groupformation/test_js.js');

// 	$PAGE->requires->jquery_plugin('survey-jquerfunctions', 'mod_groupformation');
	$names = array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation');
	$category = "";

	if (isset($_POST["category"])){
		$category = $_POST['category'];
	}

//$id = required_param('id', PARAM_INT);    // Course Module ID
	$id = optional_param('id', 0, PARAM_INT);   // Course Module ID
	$g = optional_param('g', 0, PARAM_INT);		// groupformation instance ID


// if(!$cm = get_coursemodule_from_id('groupformation', $id, 0, false, MUST_EXIST)) {
// //if (!$cm = get_coursemodule_from_id('groupformation', $id)) {
// 	print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
// }

// if(!$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST)) {
// //if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
// 	print_error('course is misconfigured');  // NOTE As above
// }

// if (!$groupformation = $DB->get_record('groupformation', array('id'=> $cm->instance), '*', MUST_EXIST)) {
// 	print_error('course module is incorrect'); // NOTE As above
// }

// jQuery functions
	$PAGE->requires->jquery();
	$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/groupformation/js/survey_functions.js'));

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

	$context = context_module::instance($cm->id);
	
//	require_login($course);
	require_login($course, true, $cm);
//	require_capability('mod/groupformation:editparam', $context);
//	$context = context_course::instance($courseid);
	
	
	$PAGE->set_url('/mod/groupformation/answeringView.php', array('id' => $cm->id));
	$PAGE->set_title(format_string($groupformation->name));
	$PAGE->set_heading(format_string($course->fullname));

	echo $OUTPUT->header();


	// Conditions to show the intro can change to look for own settings or whatever.
	if ($groupformation->intro) {
		echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox', 'groupformationintro');
	}



	// Replace the following lines with you own code.
	echo $OUTPUT->heading('Yay! It works!');

	require_once(dirname(__FILE__).'/classes/moodle_interface/storage_manager.php');
//	require_once(dirname(__FILE__).'/classes/question_manager/question_manager.php');
	require_once(dirname(__FILE__).'/classes/question_manager/questionaire.php');
	require_once(dirname(__FILE__).'/classes/question_manager/Save.php');
//  	$a = array();
// 	var_dump(count($a));


 
// //  	$xmlLoader = new mod_groupformation_xml_loader();
	$userId = $USER->id;
	$store = new mod_groupformation_storage_manager($groupformation->id);
	$number = $store->getNumber($category);
	
	$direction = 1;
	if(isset($_POST["direction"])){
		$direction = $_POST["direction"];
	}

	$inArray = in_array($category, $names);
	if($inArray){
		$save = new mod_groupformation_save($groupformation->id, $userId, $category);
		for($i = 1; $i<=$number; $i++){
			$temp = $category . $i;
			if(isset($_POST[$temp])){
				$save->save($_POST[$temp], $i);
			}
		}
	}
	
	if($direction == 0 && $_POST["percent"] == 0){
		$returnurl = new moodle_url('/mod/groupformation/view.php', array('id' => $cm->id, 'do_show' => 'view', 'back' => '1'));
		redirect($returnurl);
	}
	
	if($category == '' || $inArray){
		$questionManager = new mod_groupformation_questionaire($groupformation->id, 'en', $userId, $category);
		
		if($direction == 0){
			$questionManager->goback();
		}
		
		$questionManager->getQuestions();
		
	}else if($category == 'no'){
		if(isset($_POST["action"]) && $_POST["action"] == 1){
			$store->statusChanged($userId);
		}
		$returnurl = new moodle_url('/mod/groupformation/view.php', array('id' => $cm->id, 'do_show' => 'view', 'back' => '1'));
		redirect($returnurl);
	}else{
		echo $OUTPUT->heading('category has been manipulated');
	}
	
	



	echo $OUTPUT->footer();

