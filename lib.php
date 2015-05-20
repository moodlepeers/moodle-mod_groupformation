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
 * Library of interface functions and constants for module groupformation
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the newmodule specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once (dirname ( __FILE__ ) . '/classes/moodle_interface/storage_manager.php');
require_once (dirname ( __FILE__ ) . '/classes/util/xml_loader.php');

/**
 * Moodle core API
 */
/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature
 *        	FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function groupformation_supports($feature) {
	switch ($feature) {
		case FEATURE_MOD_INTRO :
			return true;
		case FEATURE_SHOW_DESCRIPTION :
			return true;
		case FEATURE_GRADE_HAS_GRADE :
			return true;
		case FEATURE_BACKUP_MOODLE2 :
			return true;
		default :
			return null;
	}
}
/**
 * Saves a new instance of the groupformation into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $groupformation
 *        	An object from the form in mod_form.php
 * @param mod_groupformation_mod_form $mform        	
 * @return int The id of the newly inserted groupformation record
 */
function groupformation_add_instance(stdClass $groupformation, mod_groupformation_mod_form $mform = null) {
	global $DB;
	
	$groupformation->timecreated = time ();
	
	// checks all fields and sets them properly
	$groupformation = groupformation_set_fields ( $groupformation );
	
	// You may have to add extra stuff in here.
	$groupformation->id = $DB->insert_record ( 'groupformation', $groupformation );
	groupformation_grade_item_update ( $groupformation );
	
	groupformation_save_more_infos ( $groupformation, TRUE );
	
	return $groupformation->id;
}

/**
 * Updates an instance of the groupformation in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $groupformation
 *        	An object from the form in mod_form.php
 * @param mod_groupformation_mod_form $mform        	
 * @return boolean Success/Fail
 */
function groupformation_update_instance(stdClass $groupformation, mod_groupformation_mod_form $mform = null) {
	global $DB;
	
	// TODO Kommentar in Wiki - zu XML Fragebögen
	
	// checks all fields and sets them properly
	$groupformation = groupformation_set_fields ( $groupformation );
	
	$groupformation->timemodified = time ();
	$groupformation->id = $groupformation->instance;
	/*
	 * //man kann nur solange etwas verändern bis die erste antwort gespeichert wurde
	 * if($DB->count_records('groupformation_answer', array('groupformation' => $groupformation->id)) == 0){
	 * // You may have to add extra stuff in here.
	 * $result = $DB->update_record('groupformation', $groupformation);
	 *
	 * groupformation_grade_item_update($groupformation);
	 * groupformation_save_more_infos($groupformation, FALSE);
	 * }else{
	 * // TODO @Eduard,Nora Wir brauchen die Möglichkeit die Anzahl an Gruppen bzw. die Gruppengröße zu ändern
	 * // das wird aber bei anzeigen der Settings geregelt. Workaround für jetzt, ich kümmere mich mit Eduard drum
	 * // LG René
	 * return true;
	 * }
	 */
	// You may have to add extra stuff in here.
	$result = $DB->update_record ( 'groupformation', $groupformation );
	
	groupformation_grade_item_update ( $groupformation );
	
	groupformation_save_more_infos ( $groupformation, FALSE );
	
	return $result;
}

/**
 * Removes an instance of the groupformation from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id
 *        	Id of the module instance
 * @return boolean Success/Failure
 */
function groupformation_delete_instance($id) {
	global $DB;
	
	// TODO kaskadierendes Löschen der Antworten zur passenden groupforamtion id
	
	if (! $groupformation = $DB->get_record ( 'groupformation', array (
			'id' => $id 
	) )) {
		return false;
	}
	
	// Delete any dependent records here.
	$DB->delete_records ( 'groupformation', array (
			'id' => $groupformation->id 
	) );
	
	groupformation_grade_item_delete ( $groupformation );
	
	return true;
}

/**
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function groupformation_user_outline($course, $user, $mod, $groupformation) {
	$return = new stdClass ();
	$return->time = 0;
	$return->info = '';
	return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course
 *        	the current course record
 * @param stdClass $user
 *        	the record of the user we are generating report for
 * @param cm_info $mod
 *        	course module info
 * @param stdClass $groupformation
 *        	the module instance record
 * @return void, is supposed to echp directly
 */
function groupformation_user_complete($course, $user, $mod, $groupformation) {
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in newmodule activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function groupformation_print_recent_activity($course, $viewfullnames, $timestart) {
	return false; // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link groupformation_print_recent_mod_activity()}.
 *
 * @param array $activities
 *        	sequentially indexed array of objects with the 'cmid' property
 * @param int $index
 *        	the index in the $activities to use for the next record
 * @param int $timestart
 *        	append activity since this time
 * @param int $courseid
 *        	the id of the course we produce the report for
 * @param int $cmid
 *        	course module id
 * @param int $userid
 *        	check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid
 *        	check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function groupformation_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0, $groupid = 0) {
}

/**
 * Prints single activity item prepared by {@see groupformation_get_recent_mod_activity()}
 *
 * @return void
 */
function groupformation_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc .
 *
 * ..
 *
 * @return boolean
 * @todo Finish documenting this function
 *      
 */
function groupformation_cron() {
	return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function groupformation_get_extra_capabilities() {
	return array ();
}

/**
 * Gradebook API //
 */
/**
 * Is a given scale used by the instance of groupformation?
 *
 * This function returns if a scale is being used by one groupformation
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $groupformationid
 *        	ID of an instance of this module
 * @return bool true if the scale is used by the given groupformation instance
 */
function groupformation_scale_used($groupformationid, $scaleid) {
	global $DB;
	/* @example */
	if ($scaleid and $DB->record_exists ( 'groupformation', array (
			'id' => $groupformationid,
			'grade' => - $scaleid 
	) )) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if scale is being used by any instance of groupformation.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int        	
 * @return boolean true if the scale is used by any groupformation instance
 */
function groupformation_scale_used_anywhere($scaleid) {
	global $DB;
	/* @example */
	if ($scaleid and $DB->record_exists ( 'groupformation', array (
			'grade' => - $scaleid 
	) )) {
		return true;
	} else {
		return false;
	}
}

/**
 * Creates or updates grade item for the give groupformation instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $groupformation
 *        	instance object with extra cmidnumber and modname property
 * @param
 *        	mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return void
 */
function groupformation_grade_item_update(stdClass $groupformation, $reset = false) {
	global $CFG;
	require_once ($CFG->libdir . '/gradelib.php');
	
	$item = array ();
	$item ['itemname'] = clean_param ( $groupformation->name, PARAM_NOTAGS );
	$item ['gradetype'] = GRADE_TYPE_VALUE;
	
	if ($groupformation->grade > 0) {
		$item ['gradetype'] = GRADE_TYPE_VALUE;
		$item ['grademax'] = $groupformation->grade;
		$item ['grademin'] = 0;
	} else if ($groupformation->grade < 0) {
		$item ['gradetype'] = GRADE_TYPE_SCALE;
		$item ['scaleid'] = - $groupformation->grade;
	} else {
		$item ['gradetype'] = GRADE_TYPE_NONE;
	}
	
	if ($reset) {
		$item ['reset'] = true;
	}
	
	grade_update ( 'mod/groupformation', $groupformation->course, 'mod', 'groupformation', $groupformation->id, 0, null, $item );
}

/**
 * Delete grade item for given groupformation instance
 *
 * @param stdClass $groupformation
 *        	instance object
 * @return grade_item
 */
function groupformation_grade_item_delete($groupformation) {
	global $CFG;
	require_once ($CFG->libdir . '/gradelib.php');
	return grade_update ( 'mod/groupformation', $groupformation->course, 'mod', 'groupformation', $groupformation->id, 0, null, array (
			'deleted' => 1 
	) );
}

/**
 * Update groupformation grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $groupformation
 *        	instance object with extra cmidnumber and modname property
 * @param int $userid
 *        	update grade of specific user only, 0 means all participants
 * @return void
 */
function groupformation_update_grades(stdClass $groupformation, $userid = 0) {
	global $CFG, $DB;
	require_once ($CFG->libdir . '/gradelib.php');
	$grades = array (); // Populate array of grade objects indexed by userid. @example .
	grade_update ( 'mod/groupformation', $groupformation->course, 'mod', 'groupformation', $groupformation->id, 0, $grades );
}

/**
 * File API //
 */
/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course        	
 * @param stdClass $cm        	
 * @param stdClass $context        	
 * @return array of [(string)filearea] => (string)description
 */
function groupformation_get_file_areas($course, $cm, $context) {
	return array ();
}

/**
 * File browsing support for groupformation file areas
 *
 * @package mod_groupformation
 * @category files
 *          
 * @param file_browser $browser        	
 * @param array $areas        	
 * @param stdClass $course        	
 * @param stdClass $cm        	
 * @param stdClass $context        	
 * @param string $filearea        	
 * @param int $itemid        	
 * @param string $filepath        	
 * @param string $filename        	
 * @return file_info instance or null if not found
 */
function groupformation_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
	return null;
}

/**
 * Serves the files from the groupformation file areas
 *
 * @package mod_groupformation
 * @category files
 *          
 * @param stdClass $course
 *        	the course object
 * @param stdClass $cm
 *        	the course module object
 * @param stdClass $context
 *        	the newmodule's context
 * @param string $filearea
 *        	the name of the file area
 * @param array $args
 *        	extra arguments (itemid, path)
 * @param bool $forcedownload
 *        	whether or not force download
 * @param array $options
 *        	additional options affecting the file serving
 */
function groupformation_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options = array()) {
	global $DB, $CFG;
	if ($context->contextlevel != CONTEXT_MODULE) {
		send_file_not_found ();
	}
	require_login ( $course, true, $cm );
	send_file_not_found ();
}

/**
 * Navigation API //
 */
/**
 * Extends the global navigation tree by adding groupformation nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref
 *        	An object representing the navigation tree node of the newmodule module instance
 * @param stdClass $course        	
 * @param stdClass $module        	
 * @param cm_info $cm        	
 */
function groupformation_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, cm_info $cm) {
}

/**
 * Extends the settings navigation with the groupformation settings
 *
 * This function is called when the context for the page is a groupformation module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav
 *        	{@link settings_navigation}
 * @param navigation_node $groupformationnode
 *        	{@link navigation_node}
 */
function groupformation_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $groupformationnode = null) {
}

/**
 * Sets all the importants fields and clears fields which are supposed to be empty or back on default.
 *
 * @param
 *        	$groupformation
 * @return $groupformation
 */
function groupformation_set_fields(stdClass $groupformation) {
	if (isset ( $groupformation->knowledge ) && $groupformation->knowledge == 0) {
		$groupformation->knowledge = 0;
		$groupformation->knowledgelines = "";
	} elseif (! isset ( $groupformation->knowledge )) {
		$groupformation->knowledge = 0;
		$groupformation->knowledgelines = "";
	} elseif (isset ( $groupformation->knowledge ) 
			&& $groupformation->knowledge == 1 
			&& isset ( $groupformation->knowledgelines ) 
			&& $groupformation->knowledgelines == "") {
		$groupformation->knowledge = 0;
		$groupformation->knowledgelines = "";
	}
	
	if (isset ( $groupformation->topics ) && $groupformation->topics == 0) {
		$groupformation->topics = 0;
		$groupformation->topiclines = "";
	} elseif (! isset ( $groupformation->topics )) {
		$groupformation->topics = 0;
		$groupformation->topiclines = "";
	} elseif (isset ( $groupformation->topics ) 
			&& $groupformation->topics == 1 
			&& isset ( $groupformation->topiclines ) 
			&& $groupformation->topiclines == "") {
		$groupformation->topics = 0;
		$groupformation->topiclines = "";
	}
	
	if (isset ( $groupformation->groupoption ) && $groupformation->groupoption == 1) {
		$groupformation->maxmembers = 0;
	} elseif (isset ( $groupformation->groupoption ) && $groupformation->groupoption == 0) {
		$groupformation->maxgroups = 0;
	}
	
	if (isset ( $groupformation->evaluationmethod) && $groupformation->evaluationmethod != 2){
		$groupformation->maxpoints = 100;
	}
	
	return $groupformation;
}
function groupformation_save_more_infos($groupformation, $init) {
	
	// speicher mir zusätzliche Daten ab
	$store = new mod_groupformation_storage_manager ( $groupformation->id );
	
	$knowledgearray = array ();
	if ($groupformation->knowledge != 0) {
		$knowledgearray = explode ( "\n", $groupformation->knowledgelines );
	}
	
	$topicsarray = array ();
	if ($groupformation->topics != 0) {
		$topicsarray = explode ( "\n", $groupformation->topiclines );
	}
	
	$names = array (
			'general',
			'grade',
			'team',
			'character',
			'learning',
			'motivation' 
	);
	
	if ($init) {
		
		$xmlLoader = new mod_groupformation_xml_loader ();
		$xmlLoader->setStore ( $store );
		
		if ($store->catalogTableNotSet ()) {
			
			foreach ( $names as $category ) {
				
				$array = $xmlLoader->saveData ( $category );
				$version = $array [0] [0];
				$numbers = $array [0] [1];
				$store->add_catalog_version ( $category, $numbers, $version, TRUE );
			}
		} else {
			// TODO @ALL Wenn man die Fragen ändert, ändern sie sich auch in den alten groupformation Instanzen
			// da gibt es dann unter umständen konsistenzprobleme
			// da müssen wir nochmal drüber reden
			foreach ( $names as $category ) {
				$xmlLoader->latestVersion ( $category );
			}
		}
	}
	
	if ($store->generalAnswerNotExist ()) {
		$store->add_setting_question ( $knowledgearray, $topicsarray, $init );
	}
}
	