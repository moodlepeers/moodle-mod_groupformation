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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require('header.php');

require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/questionnaire_controller.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/questionnaire_view_controller.php');

$filename = substr(__FILE__, strrpos(__FILE__, '\\') + 1);
$url = new moodle_url('/mod/groupformation/' . $filename, $urlparams);

// Set PAGE config.
$PAGE->set_url($url);
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_cm($cm);

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'survey_functions.js');

$currentcategory = optional_param('category', "", PARAM_TEXT);
$direction = optional_param('direction', 0, PARAM_INT);

$store = new mod_groupformation_storage_manager ($groupformation->id);
$usermanager = new mod_groupformation_user_manager ($groupformation->id);

$controller = new mod_groupformation_questionnaire_controller(
        $groupformation->id,
        $userid,
        $cm->id,
        $currentcategory,
        $direction
);

$viewcontroller = new mod_groupformation_questionnaire_view_controller(
        $groupformation->id,
        $controller
);

$viewcontroller->handle_access();

$viewcontroller->handle_actions();

echo $OUTPUT->header();

$currenttab = 'questionnaire';

// Print the tabs.
require('tabs.php');

$viewcontroller->render();
echo $OUTPUT->footer();
