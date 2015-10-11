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
$random_answers = optional_param ( 'random_answers', false, PARAM_BOOL );
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

// Set PAGE config
$PAGE->set_url ( '/mod/groupformation/analysis_view.php', array (
		'id' => $cm->id,
		'do_show' => $do_show 
) );
$PAGE->set_title ( format_string ( $groupformation->name ) );
$PAGE->set_heading ( format_string ( $course->fullname ) );

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/controller/analysis_controller.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');

$controller = new mod_groupformation_analysis_controller ( $groupformation->id );

if ($_POST) {
	if (isset ( $_POST ['start_questionnaire'] )) {
		$controller->start_questionnaire ();
	} elseif (isset ( $_POST ['stop_questionnaire'] )) {
		$controller->stop_questionnaire ();
	}
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis' 
	) );
	redirect ( $returnurl );
}

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

// require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/answer_manager.php');
// require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/question_manager.php');
// require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/version_manager.php');
// require_once ($CFG->dirroot . '/mod/groupformation/classes/model/range_question.php');

// $vm = new mod_groupformation_version_manager($groupformation->id);

// $filename = 'definition_2015100100.xml';

// $vm->read_file($filename);

// $b = $qm->add_setting_question(array('V1','V2'), array('T1','T2','T3'));
// var_dump($b);

// var_dump($question->is_valid());
// $question->save();

// var_dump($question->get_knowledge_values());
// $radio_question = new mod_groupformation_radio_question('motivation',19);

/* ---------- Automated test user generation ---------- */

$cqt = new mod_groupformation_test_user_generator ();

if ($delete_users) {
	$cqt->delete_test_users ( $groupformation->id );
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis' 
	) );
	redirect ( $returnurl );
}
if ($create_users > 0) {
	$cqt->create_test_users ( $create_users, $groupformation->id, $create_answers, $random_answers );
	$returnurl = new moodle_url ( '/mod/groupformation/analysis_view.php', array (
			'id' => $id,
			'do_show' => 'analysis' 
	) );
	redirect ( $returnurl );
}

/* ---------- / Automated test user generation ---------- */

// TODO Ahmed - Einkommentieren und die notify_admin-Function testen
/* -----  function for sending confirmations when group formation algorithm finished */
//groupformation_send_confirmation($USER);
/* ----- / function for sending confirmations when group formation algorithm finished */

// $jm = new mod_groupformation_job_manager ();

// $job = null;

// $job = $jm::get_job ( $groupformation->id );

// if (! is_null ( $job )) {
// 	$result = $jm::do_groupal($job);
// 	var_dump ( $result );
//  	$saved = $jm::save_result($job,$result);

// }

// $admin = array_pop($DB->get_records('user', array('username' => 'admin')));
// var_dump($admin);
// -----------------------------------------------

// echo '<div style="color:red;">Diese Seite ist soweit fertig; Rückmeldung, wenn es etwas fehlt oder unverständlich ist, wäre super.</div>';

echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';

echo '<input type="hidden" name="id" value="' . $id . '"/>';

echo $controller->display ();

echo '</form>';

echo $OUTPUT->footer ();
