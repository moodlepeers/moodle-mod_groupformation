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
 * Library of interface functions and constants for module groupformation
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the newmodule specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die ();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');

/**
 * Returns activity state
 *
 * @param int $groupformationid ID of the activity
 * @return mixed
 * @throws dml_exception
 */
function groupformation_get_activity_state($groupformationid) {
    $store = new mod_groupformation_storage_manager($groupformationid);

    return $store->statemachine->get_state();
}

/**
 * Returns user state
 *
 * @param int $groupformationid ID of the activity
 * @param int $userid ID of the user
 * @return mixed
 * @throws dml_exception
 */
function groupformation_get_user_state($groupformationid, $userid) {
    $store = new mod_groupformation_storage_manager($groupformationid);

    return $store->userstatemachine->get_state($userid);
}

/**
 * Returns all instances of groupformation activities in a given course
 *
 * @param int courseid ID of the course
 * @return array stdClass
 * @throws dml_exception
 */
function groupformation_get_instances($courseid) {
    global $DB;

    $instances = $DB->get_records('groupformation', array('course' => $courseid));

    return $instances;
}

/**
 * Returns all instances of groupformation activities in a given course
 *
 * @param int courseid ID of the course
 * @return array stdClass
 * @throws dml_exception
 */
function groupformation_get_instance_by_id($groupformationid) {
    global $DB;

    $instance = $DB->get_record('groupformation', array('id' => $groupformationid));

    return $instance;
}

/**
 * Returns an array including all group members names.
 *
 * @param $groupformationid
 * @param $userid
 * @return array
 * @throws dml_exception
 */
function groupformation_get_group_members($groupformationid, $userid) {
    $groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
    $groupmembers = $groupsmanager->get_group_members($userid);
    $members = array();
    foreach ($groupmembers as $groupmember) {
        $user = get_complete_user_data('id', $groupmember);
        $members[$groupmember] = fullname($user);
    }
    return $members;
}

/**
 * Returns group name
 *
 * @param $groupformationid
 * @param $userid
 * @return mixed
 * @throws dml_exception
 */
function groupformation_get_group_name($groupformationid, $userid) {
    $groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
    return $groupsmanager->get_group_name($userid);
}

/**
 * Returns course module for activity
 *
 * @param $groupformationid
 * @return mixed
 * @throws dml_exception
 */
function groupformation_get_cm($groupformationid) {
    global $DB;
    $gfinstance = groupformation_get_instance_by_id($groupformationid);

    if ($gfinstance) {
        $moduleid = $DB->get_field('modules', 'id', array('name' => 'groupformation'));
        return $DB->get_field('course_modules', 'id', array('course' => $gfinstance->course, 'module' => $moduleid, 'instance' => $groupformationid));
    }

    return null;
    }

/**
 * Returns whether user has a group
 *
 * @param $groupformationid
 * @param $userid
 * @return bool
 * @throws dml_exception
 */
function groupformation_has_group($groupformationid, $userid) {
    $groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
    return $groupsmanager->has_group($userid);
}

/**
 * Returns total number of answers
 *
 * @param $groupformationid
 * @return int
 * @throws dml_exception
 */
function groupformation_get_number_of_questions($groupformationid) {
    $store = new mod_groupformation_storage_manager($groupformationid);
    return $store->get_total_number_of_answers();
}

/**
 * Returns number of answers questions
 *
 * @param $groupformationid
 * @param $userid
 * @return number
 * @throws dml_exception
 */
function groupformation_get_number_of_answered_questions($groupformationid, $userid) {
    $usermanager = new mod_groupformation_user_manager($groupformationid);
    return $usermanager->get_number_of_answers($userid);
}

/**
 * Returns statistics
 *
 * The returned array contains four values:
 * enrolled (number of enrolled students),
 * processing (number of students processing the questionnaire),
 * submitted (number of submitted questionnaires),
 * submitted completely (number of submitted questionnaires with complete answers to all questions)
 *
 * @param $groupformationid
 * @return array
 * @throws dml_exception
 */
function groupformation_get_progress_statistics($groupformationid) {
    $usermanager = new mod_groupformation_user_manager($groupformationid);
    return $usermanager->get_statistics();
}

/**
 * Returns dates for started and terminated if set.
 *
 * @param $groupformationid
 * @return array
 * @throws coding_exception
 * @throws dml_exception
 */
function groupformation_get_dates($groupformationid) {
    $store = new mod_groupformation_storage_manager($groupformationid);
    return $store->get_time();
}

/**
 * Return users for this activity
 *
 * @param $groupformationid
 * @return array
 * @throws dml_exception
 */
function groupformation_get_users($groupformationid) {
    $store = new mod_groupformation_storage_manager($groupformationid);
    return $store->get_users_for_grouping();
}

/**
 * Checks whethter a groupformation exists
 *
 * @param $instance
 * @return bool
 * @throws dml_exception
 */
function groupformation_check_instance($instance) {
    global $DB;

    return $DB->record_exists('groupformation',array('id' => $instance));
}

function get_groupformationids_for_user($userid) {
    global $DB;

    return $DB->get_fieldset_select('groupformation_users', 'groupformation','userid ='.$userid, array('userid' =>$userid));
}