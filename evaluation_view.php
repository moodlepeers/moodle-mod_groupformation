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
require_once($CFG->dirroot . "/mod/groupformation/classes/controller/evaluation_controller.php");
require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/evaluation_view_controller.php');

// Read URL params.
$id = optional_param('id', 0, PARAM_INT);
$doshow = optional_param('do_show', 'evaluation', PARAM_TEXT);

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'startCarousel.js');

// Determine instances of course module, course, groupformation.
groupformation_determine_instance($id, $cm, $course, $groupformation);

// Require user login if not already logged in.
require_login($course, true, $cm);

// Get useful stuff.
$context = $PAGE->context;
$userid = $USER->id;

if (has_capability('mod/groupformation:editsettings', $context)) {
    $returnurl = new moodle_url('/mod/groupformation/analysis_view.php', array('id' => $id, 'do_show' => 'analysis'));
    redirect($returnurl);
} else {
    $currenttab = $doshow;
}

// Log access to page.
groupformation_info($USER->id, $groupformation->id, '<view_student_evaluation>');

// Set PAGE config.
$PAGE->set_url('/mod/groupformation/evaluation_view.php', array('id' => $cm->id, 'do_show' => $doshow));
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

echo '<link rel="stylesheet" href="fonts/fontawesome/css/font-awesome.min.css">';

echo $OUTPUT->header();

// Print the tabs.
require('tabs.php');

// Conditions to show the intro can change to look for own settings or whatever.
if ($groupformation->intro) {
    echo $OUTPUT->box(format_module_intro('groupformation', $groupformation, $cm->id), 'generalbox mod_introbox',
            'groupformationintro');
}

if (groupformation_get_current_questionnaire_version() > $store->get_version()) {
    echo '<div class="alert">'.get_string('questionnaire_outdated', 'groupformation') . '</div>';
}
if ($store->is_archived()) {
    echo '<div class="alert" id="commited_view">'.get_string('archived_activity_answers', 'groupformation') . '</div>';
} else {
    $controller = new mod_groupformation_evaluation_controller($groupformation->id);

    $viewcontroller = new mod_groupformation_evaluation_view_controller($groupformation->id, $controller);
    echo $viewcontroller->render();
}

echo $OUTPUT->footer();