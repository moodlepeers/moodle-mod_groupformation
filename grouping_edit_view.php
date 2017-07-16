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
require('header.php');

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/grouping_controller.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/grouping_edit_view_controller.php');

$filename = substr(__FILE__, strrpos(__FILE__, '\\') + 1);
$url = new moodle_url('/mod/groupformation/' . $filename, $urlparams);

// Set PAGE config.
$PAGE->set_url($url);
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'edit_groups.js');

if (!has_capability('mod/groupformation:editsettings', $context)) {
    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $id, 'do_show' => 'view'));
    redirect($returnurl);
} else {
    $currenttab = $doshow;
}

$store = new mod_groupformation_storage_manager ($groupformation->id);
$controller = new mod_groupformation_grouping_controller ($groupformation->id, $cm);

if ((data_submitted()) && confirm_sesskey()) {
    $saveedit = optional_param('save_edit', null, PARAM_BOOL);
    if (true || (isset ($saveedit) && $saveedit == 1)) {
        $groupsstring = optional_param('groups_string', null, PARAM_TEXT);
        $controller->save_edit($groupsstring);
    }
    $returnurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
        'id' => $id, 'do_show' => 'grouping'));
    redirect($returnurl);
}

echo $OUTPUT->header();

// Print the tabs.
require('tabs.php');

if (groupformation_get_current_questionnaire_version() > $store->get_version()) {
    echo '<div class="alert">' . get_string('questionnaire_outdated', 'groupformation') . '</div>';
}
if ($store->is_archived() && has_capability('mod/groupformation:editsettings', $context)) {
    echo '<div class="alert" id="commited_view">' . get_string('archived_activity_admin', 'groupformation') . '</div>';
} else {
    echo '<form id="edit_groups_form" action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off">';
    echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
    echo '<input type="hidden" name="id" value="' . $id . '"/>';

    $viewcontroller = new mod_groupformation_grouping_edit_view_controller($groupformation->id, $controller);
    echo $viewcontroller->render();

    echo '</form>';
}
echo $OUTPUT->footer();
