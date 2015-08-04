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

require_once (dirname ( __FILE__ ) . '/classes/util/test_user_generator.php');
require_once (dirname ( __FILE__ ) . '/classes/moodle_interface/storage_manager.php');

// Read URL params
$id = optional_param ( 'id', 0, PARAM_INT ); // Course Module ID
                                             // $g = optional_param ( 'g', 0, PARAM_INT ); // groupformation instance ID
$do_show = optional_param ( 'do_show', 'analysis', PARAM_TEXT );

$create_users = optional_param ( 'create_users', 0, PARAM_INT );
$create_answers = optional_param ( 'create_answers', false, PARAM_BOOL );
$delete_users = optional_param ( 'delete_users', false, PARAM_BOOL );

// Import jQuery and js file
groupformation_add_jquery ( $PAGE, 'survey_functions.js' );

// Determine instances of course module, course, groupformation
groupformation_determine_instance ( $id, $cm, $course, $groupformation );

// Require user login if not already logged in
require_login ( $course, true, $cm );

// Get useful stuff
$context = $PAGE->context;
$userid = $USER->id;

if (! has_capability ( 'mod/groupformation:editsettings', $context )) {
	$returnurl = new moodle_url ( '/mod/groupformation/view.php', array (
			'id' => $id,
			'do_show' => 'view' 
	) );
	redirect ( $returnurl );
} else {
	$current_tab = $do_show;
}

// Log access to page
groupformation_info ( $USER->id, $groupformation->id, '<view_teacher_overview>' );

// Trigger event TODO @Nora why?
// groupformation_trigger_event($cm, $course, $groupformation, $context);

// Set PAGE config
$PAGE->set_url ( '/mod/groupformation/analysis_view.php', array (
		'id' => $cm->id,
		'do_show' => $do_show 
) );
$PAGE->set_title ( format_string ( $groupformation->name ) );
$PAGE->set_heading ( format_string ( $course->fullname ) );

echo $OUTPUT->header ();

// Print the tabs.
require ('tabs.php');

// Conditions to show the intro can change to look for own settings or whatever.
// if ($groupformation->intro) {
// echo $OUTPUT->box ( format_module_intro ( 'groupformation', $groupformation, $cm->id ), 'generalbox mod_introbox', 'groupformationintro' );
// }

// Replace the following lines with you own code.
// echo $OUTPUT->heading ( $groupformation->name );

// ---------------------------------------------
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/controller/analysis_controller.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');

$controller = new mod_groupformation_analysis_controller ( $groupformation->id );

if ($_POST) {
	if (isset ( $_POST ['start_questionnaire'] )) {
		$controller->startQuestionnaire ();
	} elseif (isset ( $_POST ['stop_questionnaire'] )) {
		$controller->stopQuestionnaire ();
	}
}

/* ---------- Automated test user generation ---------- */

$cqt = new mod_groupformation_test_user_generator ();

if ($delete_users){
	$cqt->delete_test_users ( $groupformation->id );
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis'
	) );
	redirect ( $returnurl );
	
}
if ($create_users > 0){
	$cqt->create_test_users ( $create_users, $groupformation->id,$create_answers);
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis'
	) );
	redirect ( $returnurl );
}
	
/* ---------- / Automated test user generation ---------- */

$jm = new mod_groupformation_job_manager ();

$job = null;

$groupal_cohort = null;
$random_cohort = null;
$incomplete_cohort = null;

$job = $jm::get_job ( $groupformation->id );

if (! is_null ( $job )) {
	$result = $jm::do_groupal ( $job, $groupal_cohort, $random_cohort, $incomplete_cohort);
// 	var_dump($result);
}
// -----------------------------------------------

echo '<div style="color:red;">Diese Seite ist soweit fertig; Rückmeldung, wenn es etwas fehlt oder unverständlich ist, wäre super.</div>';

// TODO : form in das template packen?
echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';

echo '<input type="hidden" name="id" value="' . $id . '"/>';

echo $controller->display ();

echo '</form>';

echo $OUTPUT->footer ();
