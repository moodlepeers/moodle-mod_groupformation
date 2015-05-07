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
	

	//$id = required_param('id', PARAM_INT);    // Course Module ID
	$id = optional_param('id', 0, PARAM_INT);   // Course Module ID
	$g = optional_param('g', 0, PARAM_INT);		// groupformation instance ID
	
	$current_tab = 'view';
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

	require_login($course, true, $cm);
//	$context = context_module::instance($cm->id);

	$event = \mod_groupformation\event\course_module_viewed::create(array(
			'objectid' => $PAGE->cm->instance,
			'context' => $PAGE->context,
	));

	$context = $PAGE->context;
	
	$event->add_record_snapshot('course', $PAGE->course);
	$event->add_record_snapshot($PAGE->cm->modname, $groupformation);
	$event->trigger();
	
	$PAGE->set_url('/mod/groupformation/view.php', array('id' => $cm->id, 'do_show' => 'view'));
// 	$PAGE->set_title(get_string('title', 'groupformation'));
// 	$PAGE->set_heading(get_string('header', 'groupformation'));
	$PAGE->set_title(format_string($groupformation->name));
	$PAGE->set_heading(format_string($course->fullname));
//	$PAGE->set_context($context);

	
	
	echo $OUTPUT->header();

	
	
	
	// Print the tabs.
	require('tabs.php');
	
	// $mform = new form_dummy();
	// //Form processing and displaying is done here
	// if ($mform->is_cancelled()) {
	// 	//Handle form cancel operation, if cancel button is present on form
	// } else if ($fromform = $mform->get_data()) {
	// 	//In this case you process validated data. $mform->get_data() returns data posted in form.
	// } else {
	// 	// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
	// 	// or on the first display of the form.

	// 	//Set default data (if any)
	// 	$mform->set_data($toform);
	// 	//displays the form
	// 	$mform->display();
	// }

	// Conditions to show the intro can change to look for own settings or whatever.
	if ($groupformation->intro) {
		echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox', 'groupformationintro');
	}
	
	
	
	// Replace the following lines with you own code.
	echo $OUTPUT->heading('Yay! It works!');
	
 	require_once(dirname(__FILE__).'/classes/moodle_interface/storage_manager.php');
  //	require_once(dirname(__FILE__).'/classes/question_manager/question_manager.php');
  	require_once(dirname(__FILE__).'/classes/question_manager/questionaire.php');
//  	$a = array();
// 	var_dump(count($a));
	

  	
// //  	$xmlLoader = new mod_groupformation_xml_loader();
  	$userId = $USER->id;
	$store = new mod_groupformation_storage_manager($groupformation->id);
	
	
 		if (has_capability('mod/groupformation:onlystudent', $context)){
			$questionManager = new mod_groupformation_questionaire($groupformation->id, 'en', $userId);
			$questionManager->getQuestions();
		}else{
       		 echo $OUTPUT->heading('no access');
		}
// 		$hasNext = $questionManager->hasNext();
// 		if($questionManager->questionsToAnswer($userId)){
// 			while($hasNext){
// 				$category = $questionManager->getCurrentCategory();
// 				var_dump($category);
// 				$question = $questionManager->getNextQuestion();
			
// 				var_dump($question);
// 				$hasAnswer = $questionManager->hasAnswers($userId);
// 				var_dump($hasAnswer);
// 				if($hasAnswer){
// 					var_dump($questionManager->getAnswers($userId));
// 				}
// 				$hasNext = $questionManager->hasNext();
// 				$answers = array('0');
// 				$questionManager->saveAnswers($userId, $answers);
// 			}
		
// 			if($hasNext){
// 				$category = $questionManager->getCurrentCategory();
// 				var_dump($category);
// 				$question = $questionManager->getNextQuestion();
				
// 				var_dump($question);
// 				$hasAnswer = $questionManager->hasAnswers($userId);
// 				var_dump($hasAnswer);
// 				if($hasAnswer){
// 					var_dump($questionManager->getAnswers($userId));
// 				}
// 				$hasNext = $questionManager->hasNext();
// 			}
		
// 			if($hasNext){
// 				$category = $questionManager->getCurrentCategory();
// 				var_dump($category);
// 				$question = $questionManager->getNextQuestion();
		
// 				var_dump($question);
// 				$hasAnswer = $questionManager->hasAnswers($userId);
// 				var_dump($hasAnswer);
// 				if($hasAnswer){
// 					var_dump($questionManager->getAnswers($userId));
// 				}
// 				$hasNext = $questionManager->hasNext();
// 			}
		
// 			if($hasNext){
// 				$category = $questionManager->getCurrentCategory();
// 				var_dump($category);
// 				$question = $questionManager->getNextQuestion();
		
// 				var_dump($question);
// 				$hasAnswer = $questionManager->hasAnswers($userId);
// 				var_dump($hasAnswer);
// 				if($hasAnswer){
// 					var_dump($questionManager->getAnswers($userId));
// 				}
// 				$hasNext = $questionManager->hasNext();
// 			}
		
// 			if($hasNext){
// 				$category = $questionManager->getCurrentCategory();
// 				var_dump($category);
// 				$question = $questionManager->getNextQuestion();
		
// 				var_dump($question);
// 				$hasAnswer = $questionManager->hasAnswers($userId);
// 				var_dump($hasAnswer);
// 				var_dump('hasAnswer');
// 				if($hasAnswer){
// 					var_dump($questionManager->getAnswers($userId));
// 				}
// 				$hasNext = $questionManager->hasNext();
			
// 				var_dump($hasNext);
// 			}
		
//		}
	
// 	$questions = $questionManager->getFirstQuestion($USER->id);
// 	var_dump($questions);
	//var_dump($USER->id);
// 	$xmlLoader->saveData('team', FALSE, 0);
	//$feedbackid = groupformation_get_feedback_id($groupformation->id);
		
		

		
	echo $OUTPUT->footer();
	

