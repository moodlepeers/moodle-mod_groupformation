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
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
require_once (dirname ( __FILE__ ) . '/lib.php');
require_once (dirname ( __FILE__ ) . '/locallib.php');

// $id = required_param('id', PARAM_INT); // Course Module ID
$id = optional_param ( 'id', 0, PARAM_INT ); // Course Module ID
$g = optional_param ( 'g', 0, PARAM_INT ); // groupformation instance ID
$back = optional_param ( 'back', 0, PARAM_INT );

$current_tab = 'view';

// Import jQuery and js file
addJQuery ( $PAGE, 'survey_functions.js' );
if ($id) {
	$cm = get_coursemodule_from_id ( 'groupformation', $id, 0, false, MUST_EXIST );
	$course = $DB->get_record ( 'course', array (
			'id' => $cm->course 
	), '*', MUST_EXIST );
	$groupformation = $DB->get_record ( 'groupformation', array (
			'id' => $cm->instance 
	), '*', MUST_EXIST );
} else if ($g) {
	$groupformation = $DB->get_record ( 'groupformation', array (
			'id' => $g 
	), '*', MUST_EXIST );
	$course = $DB->get_record ( 'course', array (
			'id' => $groupformation->course 
	), '*', MUST_EXIST );
	$cm = get_coursemodule_from_instance ( 'groupformation', $groupformation->id, $course->id, false, MUST_EXIST );
} else {
	error ( 'You must specify a course_module ID or an instance ID' );
}

require_login ( $course, true, $cm );
// $context = context_module::instance($cm->id);

$event = \mod_groupformation\event\course_module_viewed::create ( array (
		'objectid' => $PAGE->cm->instance,
		'context' => $PAGE->context 
) );

$context = $PAGE->context;

$event->add_record_snapshot ( 'course', $PAGE->course );
$event->add_record_snapshot ( $PAGE->cm->modname, $groupformation );
$event->trigger ();

$PAGE->set_url ( '/mod/groupformation/view.php', array (
		'id' => $cm->id,
		'do_show' => 'view' 
) );
$PAGE->set_title ( format_string ( $groupformation->name ) );
$PAGE->set_heading ( format_string ( $course->fullname ) );
// $PAGE->set_context($context);

echo $OUTPUT->header ();

// Print the tabs.
require ('tabs.php');

// Conditions to show the intro can change to look for own settings or whatever.
if ($groupformation->intro) {
	echo $OUTPUT->box ( format_module_intro ( 'groupformation', $groupformation, $cm->id ), 'generalbox mod_introbox', 'groupformationintro' );
}

// Replace the following lines with you own code.
echo $OUTPUT->heading ( $groupformation->name );

require_once (dirname ( __FILE__ ) . '/classes/moodle_interface/storage_manager.php');
// require_once(dirname(__FILE__).'/classes/question_manager/question_manager.php');
require_once (dirname ( __FILE__ ) . '/classes/question_manager/infoText.php');
$userId = $USER->id;

$store = new mod_groupformation_storage_manager ( $groupformation->id );
$val;
if ($id) {
	$val = $id;
} else {
	$val = $groupformation->id;
}
$truegroupformationId = $groupformation->id;
$info = new mod_groupformation_infoText ( $val , $userId , $truegroupformationId );

$begin = 1;
if (isset ( $_POST ["begin"] )) {
	$begin = $_POST ["begin"];
}

if ($begin == 1) {
	if (isset ( $_POST ["questions"] )) {
		
		if ($_POST ["questions"] == 1 && ! $back) {
			
			$returnurl = new moodle_url ( '/mod/groupformation/answeringView.php', array (
					'id' => $val 
			) );
			
			redirect ( $returnurl );
		}
	}
} else {
	$store->statusChanged ( $userId, 1 );
}

// $xmlLoader = new mod_groupformation_xml_loader();
if (has_capability ( 'mod/groupformation:onlystudent', $context )) {
	$status = $store->answeringStatus ( $userId );
	if ($status == - 1) {
		$info->statusA ();
	}
	if ($status == 0) {
		$info->statusB ();
	}
	if ($status == 1) {
		$info->statusC ();
	}
} else {
	$info->Dozent ();
}

echo $OUTPUT->footer ();
	

