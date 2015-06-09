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


	require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
	require_once (dirname ( __FILE__ ) . '/lib.php');
	require_once (dirname ( __FILE__ ) . '/locallib.php');
// 	require_once ($CFG->dirroot.'/mod/feedback/lib.php');
	require_once (dirname ( __FILE__ ) . '/classes/util/define_file.php');

	require_once(dirname(__FILE__).'/classes/moodle_interface/storage_manager.php');
	require_once(dirname(__FILE__).'/classes/question_manager/questionaire.php');
	require_once(dirname(__FILE__).'/classes/question_manager/Save.php');

	// 	Import jQuery and js file
	addJQuery ( $PAGE, 'survey_functions.js' );
	
	$id = optional_param('id', 0, PARAM_INT);   // Course Module ID
	$g = optional_param('g', 0, PARAM_INT);		// groupformation instance ID
	$url_category = optional_param('category','',PARAM_TEXT); 	// category name
	
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
	$userId = $USER->id;
	
	require_login($course, true, $cm);
		
	$data = new mod_groupformation_data();
	$store = new mod_groupformation_storage_manager($groupformation->id);
	
	$names = $data->getNames();
	$scenario = $store->getScenario();
	
	$category = "";

	if (isset($_POST["category"])){
		$category = $_POST['category'];
	}elseif (!(strcmp($url_category, '')==0)){
		$category = $data->getPreviousCategory($scenario, $url_category);
	}

	$number = $store->getNumber($category);
	
	
	$PAGE->set_url('/mod/groupformation/answeringView.php', array('id' => $cm->id));
	$PAGE->set_title(format_string($groupformation->name));
	$PAGE->set_heading(format_string($course->fullname));

// 	echo $OUTPUT->header();


// 	// Conditions to show the intro can change to look for own settings or whatever.
// 	if ($groupformation->intro) {
// 		echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox', 'groupformationintro');
// 	}
 
// 	$xmlLoader = new mod_groupformation_xml_loader();

	
	$direction = 1;
	if(isset($_POST["direction"])){
		$direction = $_POST["direction"];
	}

	//--- Mathevorkurs
	$go = true;
	//---
	
	$inArray = in_array($category, $names);
	if(has_capability('mod/groupformation:onlystudent', $context)){
		if($inArray){
			
			$save = new mod_groupformation_save($groupformation->id, $userId, $category);
			for($i = 1; $i<=$number; $i++){
				$temp = $category . $i;
				if(isset($_POST[$temp])){
					$save->save($_POST[$temp], $i);
					
				}
			}
			
			// --- Mathevorkurs
			if($store->answerNumberForUser($userId, $category) != $number){
				$go = false;
			}
			// ---
		}
	}
	
	if($direction == 0 && $_POST["percent"] == 0){
		$returnurl = new moodle_url('/mod/groupformation/view.php', array(
				'id' => $cm->id, 
				'do_show' => 'view', 
				'back' => '1'));
		redirect($returnurl);
	}
	
	if($category == '' || $inArray){
		echo $OUTPUT->header();
		
		
		// Conditions to show the intro can change to look for own settings or whatever.
		if ($groupformation->intro) {
			echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox', 'groupformationintro');
		}
		
		$onlystudent = has_capability('mod/groupformation:onlystudent', $context);
		
		$questionManager = new mod_groupformation_questionaire($cm->id,$groupformation->id, get_string('language','groupformation'), $userId, $category, $onlystudent);
		
		if($direction == 0){
			$questionManager->goBack();
		}else{
			if(!$go){
				$questionManager->goNotOn();
			}
		}
		$questionManager->printQuestionairePage();
		
	}else if($category == 'no'){
		if(isset($_POST["action"]) && $_POST["action"] == 1){
			$store->statusChanged($userId);
		}
		$returnurl = new moodle_url('/mod/groupformation/view.php', array(
				'id' => $cm->id, 
				'do_show' => 'view', 
				'back' => '1'));
		redirect($returnurl);
	}else{
		echo $OUTPUT->header();
		
		
		// Conditions to show the intro can change to look for own settings or whatever.
		if ($groupformation->intro) {
			echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox', 'groupformationintro');
		}
		
		echo $OUTPUT->heading('category has been manipulated');
	}
	
	



	echo $OUTPUT->footer();

