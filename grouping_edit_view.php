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

// Read URL params.
$id = optional_param('id', 0, PARAM_INT);
$doshow = optional_param('do_show', 'grouping', PARAM_TEXT);

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'edit_groups.js');

// Determine instances of course module, course, groupformation.
groupformation_determine_instance($id, $cm, $course, $groupformation);

// Require user login if not already logged in.
require_login($course, true, $cm);

// Get useful stuff.
$context = $PAGE->context;
$userid = $USER->id;

if (!has_capability('mod/groupformation:editsettings', $context)) {
    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $id, 'do_show' => 'view'));
    redirect($returnurl);
} else {
    $currenttab = $doshow;
}

// Get data for HTML output.
require_once(dirname(__FILE__) . '/classes/moodle_interface/storage_manager.php');
require_once(dirname(__FILE__) . '/classes/controller/grouping_controller.php');
$store = new mod_groupformation_storage_manager ($groupformation->id);

// Set data and viewStatus of groupingView, after possible db update.
$controller = new mod_groupformation_grouping_controller ($groupformation->id, $cm);
//
//if ($_POST) {
//    if (isset ($_POST ['save_edit'])) {
//        if (isset ($_POST['groups_string'])) {
//            $controller->save_edit($_POST['groups_string']);
//        }
//        unset ($_POST ['save_edit']);
//    }
//    $returnurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
//        'id' => $id, 'do_show' => 'grouping'));
//    redirect($returnurl);
//}
if ( (data_submitted()) && confirm_sesskey()){
    $save_edit = optional_param('save_edit', null, PARAM_BOOL);
    if (true || (isset ($save_edit) && $save_edit == 1)) {
        $groups_string = optional_param('groups_string', null, PARAM_TEXT);
        $controller->save_edit($groups_string);
    }
    $returnurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
        'id' => $id, 'do_show' => 'grouping'));
    redirect($returnurl);
}

// Log access to page.
groupformation_info($USER->id, $groupformation->id, '<view_teacher_grouping>');

// Set PAGE config.
$PAGE->set_url('/mod/groupformation/grouping_edit_view.php', array(
    'id' => $cm->id, 'do_show' => $doshow));
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

// Print the tabs.
require('tabs.php');
if ($store->is_archived() && has_capability('mod/groupformation:editsettings', $context)) {
    echo '<div class="alert" id="commited_view">' . get_string('archived_activity_admin', 'groupformation') . '</div>';
} else {
    echo '<form id="edit_groups_form" action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off">';
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
    echo '<input type="hidden" name="id" value="' . $id . '"/>';

    echo $controller->display_edit();

    echo '</form>';
}
echo $OUTPUT->footer();
