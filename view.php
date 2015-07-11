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
	require_once(dirname(__FILE__).'/locallib.php');
	require_once(dirname(__FILE__).'/classes/moodle_interface/storage_manager.php');
	require_once(dirname(__FILE__).'/classes/question_manager/info_text.php');

	// Read URL params
	$id = optional_param('id', 0, PARAM_INT);   // Course Module ID
// 	$g = optional_param('g', 0, PARAM_INT);		// groupformation instance ID
	$do_show = optional_param('do_show', 'view', PARAM_TEXT);
	$back = optional_param('back', 0, PARAM_INT);
	
	// Import jQuery and js file
	groupformation_add_jquery ( $PAGE, 'survey_functions.js' );
	
	// Determine instances of course module, course, groupformation
	groupformation_determine_instance($id, $cm, $course, $groupformation);

	// Require user login if not already logged in
	require_login($course, true, $cm);

	// Get useful stuff
	$context = $PAGE->context;
	$userid = $USER->id;
	
	if (has_capability('mod/groupformation:editsettings', $context)){
		$returnurl = new moodle_url('/mod/groupformation/analysis_view.php', array('id' => $id, 'do_show' => 'analysis'));
		redirect($returnurl);
	}else{
		$current_tab = $do_show;
	}
	
	// Log access to page
	groupformation_log($USER->id,$groupformation->id,'<view_student_overview>');
	
	$store = new mod_groupformation_storage_manager($groupformation->id);
	$info = new mod_groupformation_info_text ($cm->id, $groupformation->id, $userid );

	// Trigger event TODO @Nora why?
	groupformation_trigger_event($cm,$course,$groupformation,$context);

	// Set PAGE config
	$PAGE->set_url('/mod/groupformation/view.php', array('id' => $cm->id, 'do_show' => $do_show));
	$PAGE->set_title(format_string($groupformation->name));
	$PAGE->set_heading(format_string($course->fullname));
	
  	$begin = 1;		
	if (isset($_POST["begin"])){
  		$begin = $_POST["begin"];
  	}else{
  		$begin = 1;
  	}  	
  	  	
  	if($begin == 1){
  		if (isset($_POST["questions"])){
  			
  			if($_POST["questions"] == 1 && !$back){
  			
  				$returnurl = new moodle_url('/mod/groupformation/questionaire_view.php', array('id' => $id));
  			
  				redirect($returnurl);
  			}
  		}
  	}else{
  		$store->statusChanged($userid, 1);
  	}
  	
  	echo $OUTPUT->header();
  	
  	// Print the tabs.
  	require('tabs.php');
  	
  	// Conditions to show the intro can change to look for own settings or whatever.
  	if ($groupformation->intro) {
  		echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox', 'groupformationintro');
  	}

	if (has_capability('mod/groupformation:onlystudent', $context)){
		if(!mod_groupformation_groups_manager::isNotBuild($groupformation->id)){
			$info->__groupsAvailable();
		}else{
	 		if ($store->isQuestionaireAvailable()){	
				$status = $store->answeringStatus($userid);
				if($status ==  -1){
					$info->__printAvailabilityInfo();
	 				$info->__printStatusA();
	 			}
	 			if($status == 0){
					$info->__printAvailabilityInfo();
	 				$info->__printStatusB();
	 			}
	 			if($status == 1){
					$info->__printAvailabilityInfo();
	 				$info->__printStatusC();
	 			}
	 		}else{
	 			$info->__printAvailabilityInfo(false);
	 		}
		}
	}else{
		print_error('This activity is not accessible for you');
	}
	
	echo $OUTPUT->footer();
	

