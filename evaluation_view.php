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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require('header.php');

require_once($CFG->dirroot . '/mod/groupformation/classes/controller/evaluation_controller.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/evaluation_view_controller.php');

$filename = substr(__FILE__, strrpos(__FILE__, '\\') + 1);
$filename = substr($filename, strpos($filename, '/mod'));

$url = new moodle_url($filename, $urlparams);
// Set PAGE config.
$PAGE->set_url('/mod/groupformation/evaluation_view.php', $urlparams);
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_cm($cm);

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'startcarousel.js');

$controller = new mod_groupformation_evaluation_controller($groupformation->id, $cm->id);
$viewcontroller = new mod_groupformation_evaluation_view_controller($groupformation->id, $controller);

$viewcontroller->handle_access();

echo '<link rel="stylesheet" href="fonts/fontawesome/css/font-awesome.min.css">';

echo $OUTPUT->header();

$currenttab = $doshow;

// Print the tabs.
require('tabs.php');

echo $viewcontroller->render();

echo $OUTPUT->footer();