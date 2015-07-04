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
	require_once (dirname ( __FILE__ ) . '/classes/util/define_file.php');
	require_once(dirname(__FILE__).'/classes/moodle_interface/storage_manager.php');
	require_once(dirname(__FILE__).'/classes/question_manager/questionaire.php');
	require_once(dirname(__FILE__).'/classes/question_manager/Save.php');

	// Read URL params
	$id = optional_param('id', 0, PARAM_INT);   // Course Module ID
// 	$g = optional_param('g', 0, PARAM_INT);		// groupformation instance ID
	$url_category = optional_param('category','',PARAM_TEXT); 	// category name
	
	// 	Import jQuery and js file
	groupformation_add_jquery ( $PAGE, 'survey_functions.js' );
	
	// Determine instances of course module, course, groupformation
	groupformation_determine_instance($id, $cm, $course, $groupformation);
	
	// Require user login if not already logged in
	require_login($course, true, $cm);

	// Get useful stuff
	$context = $PAGE->context;
	$userid = $USER->id;
		
	$data = new mod_groupformation_data();
	$store = new mod_groupformation_storage_manager($groupformation->id);
	
	//$names = $data->getNames();
	$scenario = $store->getScenario();
	$names = $store->getCategories();
	
	$category = "";

	if (!has_capability('mod/groupformation:editsettings', $context)) {
		$current_tab = 'answering';
		// Log access to page
		groupformation_log($USER->id,$groupformation->id,'<view_student_questionaire>');
		
	} else {
		$current_tab = 'view';
		// Log access to page
		groupformation_log($USER->id,$groupformation->id,'<view_teacher_questionaire_preview>');
	}
	
	if (isset($_POST["category"])){
		$category = $_POST['category'];
	}elseif (!(strcmp($url_category, '')==0)){
		var_dump($url_category);
		$category = $store->getPreviousCategory($url_category);
	}

	$number = $store->getNumber($category);
	var_dump($number);
	// Set PAGE config
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
	var_dump($inArray);
	if(has_capability('mod/groupformation:onlystudent', $context) && !has_capability('mod/groupformation:editsettings', $context)){
		if($inArray){
			
			$save = new mod_groupformation_save($groupformation->id, $userid, $category);
            if($category == 'knowledge'){
                for($i = 1; $i<=$number; $i++){
                    $tempValidateRangeValue = $category . $i . '_valid';
                    $temp = $category . $i;
                    if(isset($_POST[$temp]) && $_POST[$tempValidateRangeValue] == '1'){
                        $save->save($_POST[$temp], $i);
                    }
                }
                /*}else if($category == 'grade'){
                    for($i = 1; $i<=$number; $i++){
                        $temp = $category . $i;
                        if(isset($_POST[$temp]) && $_POST[$temp] != 0){
                            $save->save($_POST[$temp], $i);
                        }
                    }*/
            }else{
                for($i = 1; $i<=$number; $i++){
                    $temp = $category . $i;
                    if(isset($_POST[$temp])){
                        $save->save($_POST[$temp], $i);
                    }
                }
            }
			
			// --- Mathevorkurs
			if($store->answerNumberForUser($userid, $category) != $number){
				$go = false;
			}
			// ---
		}
	}
	
	if($direction == 0 && $_POST["percent"] == 0){
		$returnurl = new moodle_url('/mod/groupformation/view.php', array(
				'id' => $cm->id,
				'back' => '1'));
		redirect($returnurl);
	}
	
	$available = $store->isQuestionaireAvailable();
	
	if(($available || has_capability('mod/groupformation:editsettings', $context)) && ($category == '' || $inArray)){
		echo $OUTPUT->header();
		
		// Print the tabs.
		require ('tabs.php');
		$questionManager = new mod_groupformation_questionaire($cm->id,$groupformation->id, get_string('language','groupformation'), $userid, $category, $context);
		
		if($direction == 0){
			$questionManager->goBack();
		}else{
			if(!$go){
				$questionManager->goNotOn();
			}
		}
		
		$questionManager->printQuestionairePage();
		
	}else if(!$available || $category == 'no'){
		if(isset($_POST["action"]) && $_POST["action"] == 1){
			$store->statusChanged($userid);
		}
		$returnurl = new moodle_url('/mod/groupformation/view.php', array(
				'id' => $cm->id, 
				'do_show' => 'view', 
				'back' => '1'));
		redirect($returnurl);
	}else{
		
		echo $OUTPUT->heading('Category has been manipulated');
	}
	
	



	echo $OUTPUT->footer();

