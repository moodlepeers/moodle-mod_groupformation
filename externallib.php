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