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
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/classes/util/define_file.php');
require_once(dirname(__FILE__) . '/classes/moodle_interface/storage_manager.php');
require_once(dirname(__FILE__) . '/classes/moodle_interface/user_manager.php');
require_once(dirname(__FILE__) . '/classes/controller/questionnaire_controller.php');

// Read URL params.
$id = optional_param('id', 0, PARAM_INT);
$urlcategory = optional_param('category', '', PARAM_TEXT);

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'survey_functions.js');

// Determine instances of course module, course, groupformation.
groupformation_determine_instance($id, $cm, $course, $groupformation);

// Require user login if not already logged in.
require_login($course, true, $cm);

// Get useful stuff.
$context = $PAGE->context;
$userid = $USER->id;

$data = new mod_groupformation_data ();
$store = new mod_groupformation_storage_manager ($groupformation->id);
$usermanager = new mod_groupformation_user_manager ($groupformation->id);

$scenario = $store->get_scenario();
$names = $store->get_categories();

$category = "";

if (!has_capability('mod/groupformation:editsettings', $context)) {
    $currenttab = 'answering';
    // Log access to page.
    groupformation_info($USER->id, $groupformation->id, '<view_student_questionnaire>');
} else {
    $currenttab = 'view';
    // Log access to page.
    groupformation_info($USER->id, $groupformation->id, '<view_teacher_questionnaire_preview>');
}

if (isset ($_POST ["category"])) {
    $category = $_POST ['category'];
} else if (!(strcmp($urlcategory, '') == 0)) {
    $category = $store->get_previous_category($urlcategory);
}

$number = $store->get_number($category);

// Set PAGE config.
$PAGE->set_url('/mod/groupformation/questionnaire_view.php', array(
    'id' => $cm->id));
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

$direction = 1;
if (isset ($_POST ["direction"])) {
    $direction = $_POST ["direction"];
}


$inarray = in_array($category, $names);

if (has_capability('mod/groupformation:onlystudent', $context) &&
    !has_capability('mod/groupformation:editsettings', $context)
) {
    $status = $usermanager->get_answering_status($userid);
    if ($status == 0 || $status == -1) {
        if ($inarray) {

            if ($category == 'knowledge') {
                for ($i = 1; $i <= $number; $i++) {
                    $tempvalidaterangevalue = $category . $i . '_valid';
                    $temp = $category . $i;
                    if (isset ($_POST [$temp]) && $_POST [$tempvalidaterangevalue] == '1') {
                        $usermanager->save_answer($userid, $category, $_POST [$temp], $i);
                    }
                }
            } else {
                for ($i = 1; $i <= $number; $i++) {
                    $temp = $category . $i;
                    if (isset ($_POST [$temp])) {
                        $usermanager->save_answer($userid, $category, $_POST [$temp], $i);
                    }
                }
            }
        }
    }
}

if ($direction == 0 && $_POST ["percent"] == 0) {
    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $cm->id, 'back' => '1'));
    redirect($returnurl);
}

$available = $store->is_questionnaire_available() || $store->is_questionnaire_accessible();
$isteacher = has_capability('mod/groupformation:editsettings', $context);
if (($available || $isteacher) && ($category == '' || $inarray)) {

    echo $OUTPUT->header();


    // Print the tabs.
    require('tabs.php');

    if ($store->is_archived() && !has_capability('mod/groupformation:editsettings', $context)) {
        echo '<div class="alert" id="commited_view">' . get_string('archived_activity_answers', 'groupformation') .
            '</div>';
    } else if ($store->is_archived() && has_capability('mod/groupformation:editsettings', $context)) {
        echo '<div class="alert" id="commited_view">' . get_string('archived_activity_admin', 'groupformation') .
            '</div>';
    } else {
        $controller = new mod_groupformation_questionnaire_controller($groupformation->id,
            get_string('language',
                'groupformation'),
            $userid, $category, $cm->id);
        if ($direction == 0) {
            $controller->go_back();
        }

        $controller->print_page();
    }
} else if (!$available || $category == 'no') {

    if (isset ($_POST ["action"]) && $_POST ["action"] == 1) {
        $usermanager->change_status($userid);
    }

    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
        'id' => $cm->id, 'do_show' => 'view', 'back' => '1'));
    redirect($returnurl);
} else {

    echo $OUTPUT->heading('Category has been manipulated');
}


echo $OUTPUT->footer();

