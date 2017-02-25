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
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/import_export_controller.php');

// Read URL params.
$id = optional_param('id', 0, PARAM_INT);
$doshow = optional_param('do_show', 'import_export', PARAM_TEXT);
$manualid = optional_param('gid', null, PARAM_INT);

// Determine instances of course module, course.
groupformation_determine_instance($id, $cm, $course, $groupformation);

// Require user login if not already logged in.
require_login($course, true, $cm);

// Get useful stuff.
$context = $PAGE->context;
$userid = $USER->id;

if (is_null($manualid)) {
    $manualid = $groupformation->id;
}

// Redirect if no access is granted for user.
if (!has_capability('mod/groupformation:editsettings', $context)) {
    $returnurl = new moodle_url('/mod/groupformation/view.php', array('id' => $id, 'do_show' => 'view'));
    redirect($returnurl);
} else {
    $currenttab = $doshow;
}

// Set PAGE config.
$PAGE->set_url('/mod/groupformation/import_export_view.php', array('id' => $cm->id, 'do_show' => $doshow));
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

// Print the tabs.
require('tabs.php');

$controller = new mod_groupformation_import_export_controller($manualid, $cm);
echo $controller->render_export();

echo $OUTPUT->footer();
