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

require_once($CFG->dirroot . '/mod/groupformation/classes/util/test_user_generator.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/analysis_controller.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/scientific_grouping_2.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/analysis_view_controller.php');

$filename = substr(__FILE__, strrpos(__FILE__, '\\') + 1);
$url = new moodle_url('/mod/groupformation/' . $filename, $urlparams);

// Set PAGE config.
$PAGE->set_url($url);
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'survey_functions.js');

// Update questionnaire config if necessary.
groupformation_import_questionnaire_configuration();

$store = new mod_groupformation_storage_manager($groupformation->id);
$controller = new mod_groupformation_analysis_controller ($groupformation->id, $cm);
$viewcontroller = new mod_groupformation_analysis_view_controller($groupformation->id, $controller);

$viewcontroller->handle_access();

$viewcontroller->handle_actions();

require('debug_actions.php');

echo $OUTPUT->header();

// Print the tabs.
require('tabs.php');

echo $viewcontroller->render();

echo $debugbuttons;

echo $OUTPUT->footer();
