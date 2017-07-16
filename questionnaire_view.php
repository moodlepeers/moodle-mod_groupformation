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
 * displays the questionnaire page with the categories relevant for the configured scenario
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('header.php');

require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/questionnaire_controller.php');

$filename = substr(__FILE__, strrpos(__FILE__, '\\') + 1);
$url = new moodle_url('/mod/groupformation/' . $filename, $urlparams);

// Set PAGE config.
$PAGE->set_url($url);
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'survey_functions.js');

// TODO: after fixing db issue, change param to url?
$urlcategory = optional_param('category', '', PARAM_TEXT);

$store = new mod_groupformation_storage_manager ($groupformation->id);
$usermanager = new mod_groupformation_user_manager ($groupformation->id);
$groupsmanager = new mod_groupformation_groups_manager ($groupformation->id);

$scenario = $store->get_scenario();
$names = $store->get_categories();

if (!has_capability('mod/groupformation:editsettings', $context)) {
    $currenttab = 'answering';
} else {
    $currenttab = 'view';
}

$consent = $usermanager->get_consent($userid);
$askforparticipantcode = mod_groupformation_data::ask_for_participant_code();
$participantcode = $usermanager->has_participant_code($userid) || !$askforparticipantcode;

if (((!$consent || !$participantcode) && !$groupsmanager->groups_created()) &&
        !has_capability('mod/groupformation:editsettings', $context)) {
    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
            'id' => $cm->id, 'giveconsent' => !$consent, 'giveparticipantcode' => !$participantcode));
    redirect($returnurl);
}

$category = $store->get_previous_category($urlcategory);
$direction = 1;
$percent = 0;
$action = 0;

if (data_submitted() && confirm_sesskey()) {
    $category = optional_param('category', null, PARAM_ALPHA);
    $direction = optional_param('direction', null, PARAM_BOOL);
    $percent = optional_param('percent', null, PARAM_INT);
    $action = optional_param('action', null, PARAM_BOOL);
}

$controller = new mod_groupformation_questionnaire_controller($groupformation->id,
        $userid, $category, $cm->id);
$inarray = in_array($category, $names);
$go = true;

if (has_capability('mod/groupformation:onlystudent', $context) &&
        !has_capability('mod/groupformation:editsettings', $context) &&
        (data_submitted() && confirm_sesskey())) {
    $status = $usermanager->get_answering_status($userid);
    if ($status == 0 || $status == -1) {
        if ($inarray) {
            $go = $controller->save_answers($category);
        }
    }
}

if ($direction == 0 && $percent == 0) {
    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
            'id' => $cm->id, 'back' => '1'));
    redirect($returnurl);
}

$next = $controller->has_next();

$available = $store->is_questionnaire_available() || $store->is_questionnaire_accessible();
$isteacher = has_capability('mod/groupformation:editsettings', $context);

if ($next && ($available || $isteacher) && ($category == '' || $inarray)) {

    echo $OUTPUT->header();

    // Print the tabs.
    require('tabs.php');

    if ($store->is_archived() && !has_capability('mod/groupformation:editsettings', $context)) {
        echo '<div class="alert" id="commited_view">';
        $tmp = has_capability('mod/groupformation:editsettings', $context) ? "admin" : "answers";
        echo get_string('archived_activity_' . $tmp, 'groupformation');
        echo '</div>';
    } else {
        if ($direction == 0) {
            $controller->go_back();
        } else if (!$go) {
            $controller->not_go_on();
        }
        $controller->render();
    }
} else if (!$next ||!$available || $category == 'no') {

    if (isset ($action) && $action == 1) {
        $usermanager->change_status($userid);
    }

    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $cm->id, 'do_show' => 'view', 'back' => '1'));
    redirect($returnurl);
} else {
    echo $OUTPUT->notification('Category has been manipulated');
}

echo $OUTPUT->footer();

