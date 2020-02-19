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
 * prints the tabbed bar
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

$tabs = array();
$row = array();
$inactive = array();
$activated = array();

$store = new mod_groupformation_storage_manager ($groupformation->id);
$groupsmanager = new mod_groupformation_groups_manager ($groupformation->id);
$usermanager = new mod_groupformation_user_manager ($groupformation->id);

// Some pages deliver the cmid instead the id.
if (isset ($cmid) and intval($cmid) and $cmid > 0) {
    $usedid = $cmid;
} else {
    $usedid = $id;
}

if (!isset ($currenttab)) {
    $currenttab = 'view';
}

$editsettings = has_capability('mod/groupformation:editsettings', $context);
$onlystudent = has_capability('mod/groupformation:onlystudent', $context);

// Has editing rights -> course manager or higher.
if ($editsettings) {

    // Analysis_view.
    $analyseurl = new moodle_url ('/mod/groupformation/analysis_view.php', array(
        'id' => $usedid, 'do_show' => 'analysis'));
    $row [] = new tabobject ('analysis', $analyseurl->out(), get_string('tab_overview', 'groupformation'));

    // The grouping_view.
    $groupingurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
        'id' => $usedid, 'do_show' => 'grouping'));
    $row [] = new tabobject ('grouping', $groupingurl->out(), get_string('tab_grouping', 'groupformation'));

    // The questionnaire_view -> preview mode .
    $questionnaireviewurl = new moodle_url ('/mod/groupformation/questionnaire_view.php', array(
        'id' => $usedid, 'direction' => 1));
    $row [] = new tabobject ('questionnaire', $questionnaireviewurl->out(), get_string('tab_preview', 'groupformation'));

    // The import/export view.
    // TODO Only activate if export of study is needed.
    if (mod_groupformation_data::is_math_prep_course_mode() || mod_groupformation_data::is_amigo_mode()) {
        $exporturl = new moodle_url ('/mod/groupformation/export_view.php', array(
            'id' => $usedid, 'do_show' => 'export'));
        $row [] = new tabobject ('export', $exporturl->out(), 'Export');
    }

} else if (!$editsettings && $onlystudent) {
    // The view -> student mode.
    $viewurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $usedid, 'do_show' => 'view'));
    $row [] = new tabobject ('view', $viewurl->out(), get_string('tab_overview', 'groupformation'));

    // If questionnaire is available for students.
    $state = $store->statemachine->get_state();
    if (true || in_array($state, array('q_open', 'q_reopened')) || $usermanager->already_answered($userid)) {
        // The questionnaire view.
        $questionnaireviewurl = new moodle_url ('/mod/groupformation/questionnaire_view.php', array(
            'id' => $usedid, 'direction' => 1));
        $row [] = new tabobject ('questionnaire', $questionnaireviewurl->out(),
            get_string('tab_questionnaire', 'groupformation'));
    }

    // Evaluation view.
    if (!$store->ask_for_topics()) {
        $evaluationurl = new moodle_url ('/mod/groupformation/evaluation_view.php', array(
                'id' => $usedid,
                'do_show' => 'evaluation'
        ));
        $row [] = new tabobject ('evaluation', $evaluationurl->out(), get_string('tab_evaluation', 'groupformation'));
    }

    // The group view.
    $groupurl = new moodle_url ('/mod/groupformation/group_view.php', array(
        'id' => $usedid, 'do_show' => 'group'));
    $row [] = new tabobject ('group', $groupurl->out(), get_string('tab_group', 'groupformation'));

    // The import/export view.
    if (mod_groupformation_data::import_export_enabled()) {

        $groupurl = new moodle_url ('/mod/groupformation/import_export_view.php', array(
            'id' => $usedid, 'do_show' => 'import_export'));
        $row [] = new tabobject ('import_export', $groupurl->out(), 'Import/Export');

    }
}

if (count($row) >= 1 && ($editsettings || $usermanager->get_consent($userid) || $groupsmanager->groups_created())) {
    $tabs [] = $row;

    print_tabs($tabs, $currenttab, $inactive, $activated);
}

