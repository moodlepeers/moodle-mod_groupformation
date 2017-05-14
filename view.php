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
 * Prints a particular instance of groupformation
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/overview_controller.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/overview_view_controller.php');

// Read URL params.
$id = optional_param('id', 0, PARAM_INT); // Course Module ID.

$doshow = optional_param('do_show', 'view', PARAM_TEXT);
$back = optional_param('back', 0, PARAM_INT);
$giveconsent = optional_param('giveconsent', false, PARAM_BOOL);
$giveparticipantcode = optional_param('giveparticipantcode', false, PARAM_BOOL);

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'survey_functions.js');

// Determine instances of course module, course, groupformation.
groupformation_determine_instance($id, $cm, $course, $groupformation);

// Require user login if not already logged in.
require_login($course, true, $cm);

// Get useful stuff.
$context = $PAGE->context;
$userid = $USER->id;

if (has_capability('mod/groupformation:editsettings', $context)) {
    $returnurl = new moodle_url ('/mod/groupformation/analysis_view.php', array(
        'id' => $id, 'do_show' => 'analysis'));
    redirect($returnurl);
} else {
    $currenttab = $doshow;
}

// Log access to page.
groupformation_info($USER->id, $groupformation->id, '<view_student_overview>');

$store = new mod_groupformation_storage_manager ($groupformation->id);
$groupsmanager = new mod_groupformation_groups_manager ($groupformation->id);
$usermanager = new mod_groupformation_user_manager ($groupformation->id);

if ($usermanager->is_completed($userid)) {
    groupformation_set_activity_completion($course, $cm, $userid);
}

// Set PAGE config.
$PAGE->set_url('/mod/groupformation/view.php', array(
    'id' => $cm->id, 'do_show' => $doshow));
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

if (data_submitted() && confirm_sesskey()) {
    $consent = optional_param('consent', null, PARAM_BOOL);
    $begin = optional_param('begin', null, PARAM_INT);
    $questions = optional_param('questions', null, PARAM_BOOL);
    $participantcode = optional_param('participantcode', '', PARAM_TEXT);
}
if (!isset ($begin)) {
    $begin = 1;
}

if ($begin == 1) {
    if (isset($questions) && $questions == 1 && !$back) {
        if (isset($consent)) {
            $dbconsent = $usermanager->get_consent($userid);
            $usermanager->set_consent($userid, true);
        }
        if (isset($participantcode) && $participantcode !== '') {
            if ($usermanager->validate_participant_code($participantcode)) {
                $usermanager->register_participant_code($userid, $participantcode);
            }
        }
        $returnurl = new moodle_url ('/mod/groupformation/questionnaire_view.php', array(
            'id' => $id));
        redirect($returnurl);
    }
} else if ($begin == -1) {
    $usermanager->delete_answers($userid);
    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $id));
    redirect($returnurl);
} else if ($usermanager->is_completed($userid)) {
    $usermanager->set_complete($userid, 0);
} else {
    $usermanager->change_status($userid, 1);
    // XXX: scientific studies A/B sampling $groupsmanager->assign_to_groups_a_and_b($userid);
    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $id));

    redirect($returnurl);
}

echo $OUTPUT->header();

if ($usermanager->get_consent($userid) || $groupsmanager->groups_created()) {
    // Print the tabs.
    require('tabs.php');
}

if ($giveconsent) {
    echo '<div class="alert alert-danger">' . get_string('consent_alert_message', 'groupformation') .
        '</div>';
}

if ($giveparticipantcode) {
    echo '<div class="alert alert-danger">' . get_string('participant_code_alert_message', 'groupformation') .
        '</div>';
}
if (groupformation_get_current_questionnaire_version() > $store->get_version()) {
    echo '<div class="alert">' . get_string('questionnaire_outdated', 'groupformation') . '</div>';
}
if ($store->is_archived()) {
    echo '<div class="alert" id="commited_view">' . get_string('archived_activity_answers', 'groupformation') .
        '</div>';
} else {
    // Conditions to show the intro can change to look for own settings or whatever.
    if ($groupformation->intro) {
        echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox',
            'groupformationintro');
    }

    echo '<form action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off">';
    echo '<input type="hidden" name="questions" value="1"/>';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

    $controller = new mod_groupformation_student_overview_controller ($cm->id, $groupformation->id, $userid);

    $viewcontroller = new mod_groupformation_overview_view_controller($groupformation->id, $controller);
    echo $viewcontroller->render();

    echo '</form>';


}
echo $OUTPUT->footer();


