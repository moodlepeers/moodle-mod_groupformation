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
require_once($CFG->dirroot . '/mod/groupformation/classes/forms/import_form.php');

// Read URL params.
$id = optional_param('id', null, PARAM_INT);
$doshow = optional_param('do_show', 'import_export', PARAM_TEXT);

// Import jQuery and js file.
groupformation_add_jquery($PAGE, 'settings_functions.js');

if ( data_submitted() && confirm_sesskey()){
    $id = optional_param('cmid', null, PARAM_INT);
}

// Determine instances of course module, course, groupformation.
groupformation_determine_instance($id, $cm, $course, $groupformation);

// Require user login if not already logged in.
require_login($course, true, $cm);

// Get useful stuff.
$context = $PAGE->context;
$userid = $USER->id;

// Redirect if no access is granted for user.
if (has_capability('mod/groupformation:editsettings', $context)) {
    $returnurl = new moodle_url('/mod/groupformation/analysis_view.php', array('id' => $id, 'do_show' => 'analysis'));
    redirect($returnurl);
} else {
    $currenttab = $doshow;
}

$store = new mod_groupformation_storage_manager($groupformation->id);
$usermanager = new mod_groupformation_user_manager($groupformation->id);

if (!$store->is_questionnaire_available() || $usermanager->is_completed($userid)) {
    $returnurl = new moodle_url('/mod/groupformation/view.php', array('id' => $id, 'do_show' => 'view'));
    redirect($returnurl);
}


// Set PAGE config.
$PAGE->set_url('/mod/groupformation/import_view.php', array('id' => $cm->id, 'do_show' => $doshow));
$PAGE->set_title(format_string($groupformation->name));
$PAGE->set_heading(format_string($course->fullname));

$cancel = optional_param('cancel',null,PARAM_BOOL);
if (isset($cancel) && $cancel) {
    // Handle form cancel operation.
    $returnurl = new moodle_url('/mod/groupformation/import_export_view.php', array('id' => $id, 'do_show' => 'import_export'));
    redirect($returnurl);

} else {

    // Echo standard header.
    echo $OUTPUT->header();

    // Print the tabs.
    require('tabs.php');

    // Instantiate form.
    $mform = new mod_groupformation_import_form();

    // Set default data (if any).
    $toform = array('cmid' => $cm->id);
    $mform->set_data($toform);

    $controller = new mod_groupformation_import_export_controller($groupformation->id, $cm);

    if ($fromform = $mform->get_data()) {

        // In this case you process validated data.
        // $mform->get_data() returns data posted in form.
        if ($content = $mform->get_file_content('userfile') && ($name = $mform->get_new_filename('userfile')) &&
            (substr($name, strlen($name) - 4, 4) == '.xml')
        ) {

            $content = $mform->get_file_content('userfile');
            try {
                $controller->import_xml($content);
                $controller->render_result(true);
            } catch (Exception $e) {
                $controller->render_result(false);
            }
        } else {
            // Render form for file upload.
            $controller->render_form($mform, true);
        }

    } else {

        // Render form for file upload.
        $controller->render_form($mform,false);

    }
}

echo $OUTPUT->footer();
