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
 * Internal library of functions for module groupformation
 *
 * All the newmodule specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die ();

require_once($CFG->dirroot . '/mod/groupformation/classes/controller/logging_controller.php');

/**
 * Adds jQuery
 *
 * @param unknown $PAGE
 * @param string $filename
 */
function groupformation_add_jquery($PAGE, $filename = null) {
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');

    if (!is_null($filename)) {
        $PAGE->requires->js('/mod/groupformation/js/' . $filename);
    }
}

/**
 * Logs message
 *
 * @param integer $userid
 * @param integer $groupformationid
 * @param string $message
 * @param string $level
 * @return boolean
 */
function groupformation_log($userid, $groupformationid, $message, $level = 'info') {
    return false;
    // $logging_controller = new mod_groupformation_logging_controller ();
    // return $logging_controller->handle ( $userid, $groupformationid, $message, $level );
}

/**
 * Logs debug message
 *
 * @param integer $userid
 * @param integer $groupformationid
 * @param string $message
 * @return boolean
 */
function groupformation_debug($userid, $groupformationid, $message) {
    return groupformation_log($userid, $groupformationid, $message, $level = 'debug');
}

/**
 * Logs info message
 *
 * @param integer $userid
 * @param integer $groupformationid
 * @param string $message
 * @return boolean
 */
function groupformation_info($userid, $groupformationid, $message) {
    return groupformation_log($userid, $groupformationid, $message, $level = 'info');
}

/**
 * Logs warn message
 *
 * @param integer $userid
 * @param integer $groupformationid
 * @param string $message
 * @return boolean
 */
function groupformation_warn($userid, $groupformationid, $message) {
    return groupformation_log($userid, $groupformationid, $message, $level = 'warn');
}

/**
 * Logs error message
 *
 * @param integer $userid
 * @param integer $groupformationid
 * @param string $message
 * @return boolean
 */
function groupformation_error($userid, $groupformationid, $message) {
    return groupformation_log($userid, $groupformationid, $message, $level = 'error');
}

/**
 * Logs fatal message
 *
 * @param integer $userid
 * @param integer $groupformationid
 * @param string $message
 * @return boolean
 */
function groupformation_fatal($userid, $groupformationid, $message) {
    return groupformation_log($userid, $groupformationid, $message, $level = 'fatal');
}

/**
 * Determines instances of course module, course and groupformation by id
 *
 * @param int $id
 * @param stdClass $cm
 * @param stdClass $course
 * @param stdClass $groupformation
 */
function groupformation_determine_instance($id, &$cm, &$course, &$groupformation) {
    global $DB;
    if ($id) {
        $cm = get_coursemodule_from_id('groupformation', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array(
            'id' => $cm->course), '*', MUST_EXIST);
        $groupformation = $DB->get_record('groupformation', array(
            'id' => $cm->instance), '*', MUST_EXIST);
    } else {
        error('You must specify a course_module ID or an instance ID');
    }
}

/**
 * Returns context for groupformation id
 *
 * @param int $groupformationid
 * @return context_course
 */
function groupformation_get_context($groupformationid) {
    $store = new mod_groupformation_storage_manager ($groupformationid);

    $courseid = $store->get_course_id();

    $context = context_course::instance($courseid);

    return $context;
}

/**
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param int $userid
 */
function groupformation_set_activity_completion($course, $cm, $userid) {
    $completion = new completion_info ($course);
    $completion->set_module_viewed($cm, $userid);
}

/**
 * send confirmation for finishing group formation
 *
 * @param stdClass $recipient
 * @param string $subject
 * @param string $message
 *
 */
function groupformation_send_message($recipient, $subject, $messagetext, $contexturl = null, $contexturlname = null) {
    global $DB;

    // Get admin user for setting as "userfrom".
    $admin = array_pop($DB->get_records('user', array(
        'username' => 'admin')));

    // Prepare the message.
    $message = new \core\message\message ();
    $message->component = 'moodle';
    $message->name = 'instantmessage';
    $message->userfrom = $admin;
    $message->userto = $recipient;
    $message->subject = $subject;
    $message->fullmessage = $messagetext;
    $message->fullmessageformat = FORMAT_MARKDOWN;
    $message->fullmessagehtml = '<p>' . $messagetext . '</p>';
    $message->smallmessage = $messagetext;
    $message->notification = '0';
    $message->contexturl = $contexturl;
    $message->contexturlname = $contexturlname;
    $message->replyto = "noreply@moodle.com";
    $content = array(
        '*' => array(
            'header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor.
    $message->set_additional_content('email', $content);

    // Send message.
    message_send($message);
}

/**
 * Checks for cronjob whether it is running or not
 * @throws coding_exception
 */
function groupformation_check_for_cron_job() {
    global $DB;

    $record = $DB->get_record('task_scheduled', array(
        'component' => 'mod_groupformation', 'classname' => '\mod_groupformation\task\build_groups_task'));
    $now = time();
    $lastruntime = $record->lastruntime;

    if (($now - intval($lastruntime)) > 60 * 60 * 24) {
        echo '<div class="alert">' . get_string('cron_job_not_running', 'groupformation') . '</div>';
    }
}

/**
 * Reads questionnaire file
 *
 * @param mod_groupformation_storage_manager $store
 * @param string $filename
 */
function groupformation_import_questionnaire_configuration($filename = 'questionnaire.xml') {
    global $CFG, $DB;

    $xmlfile = $CFG->dirroot . '/mod/groupformation/xml_question/' . $filename;

    if (file_exists($xmlfile)) {
        $xml = simplexml_load_file($xmlfile);

        $current_version = groupformation_get_current_questionnaire_version();
        $new_version = intval(trim($xml['version']));


        $new_categories = array();

        foreach ($xml->categories->category as $cat) {
            $new_categories[] = trim($cat);
        }

        $new_languages = array();

        foreach ($xml->languages->language as $lang) {
            $new_languages[] = trim($lang);
        }

        if ($new_version > $current_version) {

            $xmlloader = new mod_groupformation_xml_loader();

            $number = 0;

            foreach ($new_categories as $category) {

                $prev_version = groupformation_get_catalog_version($category);

                foreach ($new_languages as $language) {

                    $data = $xmlloader->save($category, $language);

                    $version = $data[0];
                    $numberofquestions = $data[1];
                    $questions = $data[2];

                    if ($version > $prev_version || !$prev_version) {
                        groupformation_delete_all_catalog_questions($category, $language);

                        $DB->insert_records('groupformation_question', $questions);
                        groupformation_add_catalog_version($category, $numberofquestions, $version, false);
                    }
                }
                $number += $numberofquestions;
            }

            groupformation_add_catalog_version('questionnaire', $number, $new_version, false);

        }

    }

}

/**
 * Add new question from XML to DB
 *
 * @param string $category
 * @param int $numbers
 * @param unknown $version
 * @param boolean $init
 */
function groupformation_add_catalog_version($category, $numbers, $version, $init) {
    global $DB;

    $data = new stdClass ();
    $data->category = $category;
    $data->version = $version;
    $data->numberofquestion = $numbers;

    if ($init || $DB->count_records('groupformation_q_version', array(
            'category' => $category
        )) == 0
    ) {
        $DB->insert_record('groupformation_q_version', $data);
    } else {
        $data->id = $DB->get_field('groupformation_q_version', 'id', array(
            'category' => $category
        ));
        $DB->update_record('groupformation_q_version', $data);
    }
}

/**
 * Deletes all questions in a specific category
 *
 * @param string $category
 */
function groupformation_delete_all_catalog_questions($category, $language) {
    global $DB;

    $DB->delete_records('groupformation_question', array('category' => $category, 'language' => $language));
}

/**
 * Returns current questionnaire version
 *
 * @return mixed|null
 */
function groupformation_get_current_questionnaire_version() {
    global $DB;

    $field = $DB->get_field('groupformation_q_version', 'version', array('category' => 'questionnaire'));

    if ($field !== false) {
        return $field;
    } else {
        return 0;
    }
}

function groupformation_get_catalog_version($category) {
    global $DB;

    $field = $DB->get_field('groupformation_q_version', 'version', array('category' => $category));

    if ($field !== false) {
        return $field;
    } else {
        return 0;
    }
}

/**
 * Converts knowledge or topic array into XML-based syntax
 *
 * @param unknown $options
 * @return string
 */
function groupformation_convert_options($options) {
    $op = implode("</OPTION>  <OPTION>", $options);

    return "<OPTION>" . $op . "</OPTION>";
}
