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
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_groupformation
 *
 */
defined('MOODLE_INTERNAL') or die ('not allowed');

$tabs = array();
$row = array();
$inactive = array();
$activated = array();
$store = new mod_groupformation_storage_manager ($groupformation->id);
$groups_store = new mod_groupformation_groups_manager ($groupformation->id);
$user_manager = new mod_groupformation_user_manager ($groupformation->id);

// Some pages deliver the cmid instead the id.
if (isset ($cmid) and intval($cmid) and $cmid > 0) {
    $usedid = $cmid;
} else {
    $usedid = $id;
}

$context = context_module::instance($usedid);

$courseid = optional_param('courseid', false, PARAM_INT);

if (!isset ($current_tab)) {
    $current_tab = '';
}

// Has editing rights -> course manager or higher.
if (has_capability('mod/groupformation:editsettings', $context)) {
    // Analysis_view
    $analyseurl = new moodle_url ('/mod/groupformation/analysis_view.php', array(
        'id' => $usedid, 'do_show' => 'analysis'));
    $row [] = new tabobject ('analysis', $analyseurl->out(), get_string('tab_overview', 'groupformation'));

    // The grouping_view.
    $groupingurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
        'id' => $usedid, 'do_show' => 'grouping'));
    $row [] = new tabobject ('grouping', $groupingurl->out(), get_string('tab_grouping', 'groupformation'));

    // The questionnaire_view -> preview mode .
    $questionnaire_viewiewurl = new moodle_url ('/mod/groupformation/questionnaire_view.php', array(
        'id' => $usedid));
    $row [] = new tabobject ('view', $questionnaire_viewiewurl->out(), get_string('tab_preview', 'groupformation'));

    // The import/export view.
    $exporturl = new moodle_url ('/mod/groupformation/export_view.php', array(
        'id' => $usedid, 'do_show' => 'export'));
    $row [] = new tabobject ('import_export', $exporturl->out(), 'Export');
} elseif (!has_capability('mod/groupformation:editsettings', $context) &&
    has_capability('mod/groupformation:onlystudent', $context)
) {

    // The view -> student mode.
    $viewurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $usedid, 'do_show' => 'view'));
    $row [] = new tabobject ('view', $viewurl->out(), get_string('tab_overview', 'groupformation'));

    // If questionaire is available for students.
    if ($store->is_questionnaire_available() || ($store->is_questionnaire_accessible())) {
        // The questionaire view.
        $questionnaire_viewiewurl = new moodle_url ('/mod/groupformation/questionnaire_view.php', array(
            'id' => $usedid));
        $row [] = new tabobject ('answering', $questionnaire_viewiewurl->out(),
            get_string('tab_questionaire', 'groupformation'));
    }

    // TODO @Rene : auskommentierter Code!
    // always if student meets conditions
    // || $user_manager->is_completed ( $userid ) || $groups_store->groups_created()) {


    // evaluation view -> later TODO
    $evaluationurl = new moodle_url ('/mod/groupformation/evaluation_view.php', array(
        'id' => $usedid,
        'do_show' => 'evaluation'
    ));
    $row [] = new tabobject ('evaluation', $evaluationurl->out(), get_string('tab_evaluation', 'groupformation'));

    // The group view.
    $groupurl = new moodle_url ('/mod/groupformation/group_view.php', array(
        'id' => $usedid, 'do_show' => 'group'));
    $row [] = new tabobject ('group', $groupurl->out(), get_string('tab_group', 'groupformation'));

    // The import/export view.
    $groupurl = new moodle_url ('/mod/groupformation/import_export_view.php', array(
        'id' => $usedid, 'do_show' => 'import_export'));
    $row [] = new tabobject ('import_export', $groupurl->out(), 'Import/Export');

}

if (count($row) >= 1) {
    $tabs [] = $row;

    print_tabs($tabs, $current_tab, $inactive, $activated);
}

