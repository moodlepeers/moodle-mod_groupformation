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
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function groupformation_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO :
            return true;
        case FEATURE_SHOW_DESCRIPTION :
            return false;
        case FEATURE_BACKUP_MOODLE2 :
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS :
            return true;
        case FEATURE_GROUPS :
            return true;
        case FEATURE_GROUPINGS :
            return true;
        default :
            return null;
    }
}

/**
 * Saves a new instance of the groupformation into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $groupformation
 * @param mod_groupformation_mod_form|null $mform
 * @return mixed
 * @throws coding_exception
 * @throws dml_exception
 */
function groupformation_add_instance(stdClass $groupformation, mod_groupformation_mod_form $mform = null) {
    global $DB;

    groupformation_import_questionnaire_configuration();

    $groupformation->timecreated = time();

    $groupformation->version = groupformation_get_current_questionnaire_version();

    // Checks all fields and sets them properly.
    $groupformation = groupformation_set_fields($groupformation);

    $id = $DB->insert_record('groupformation', $groupformation);

    // Get current DB record (with all DB defaults).
    $groupformation = $DB->get_record('groupformation', array(
            'id' => $id));

    groupformation_grade_item_update($groupformation);

    return $groupformation->id;
}

/**
 * Updates an instance of the groupformation in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $groupformation An object from the form in mod_form.php
 * @param mod_groupformation_mod_form $mform
 * @return boolean Success/Fail
 * @throws coding_exception
 * @throws dml_exception
 */
function groupformation_update_instance(stdClass $groupformation, mod_groupformation_mod_form $mform = null) {
    global $DB;

    // Checks all fields and sets them properly.
    $groupformation = groupformation_set_fields($groupformation);
    $groupformation->timemodified = time();
    $groupformation->id = $groupformation->instance;

    $origrecord = $DB->get_record('groupformation', array(
            'id' => $groupformation->id));
    $origrecord->intro = $groupformation->intro;
    $origrecord->groupoption = $groupformation->groupoption;
    $origrecord->maxmembers = $groupformation->maxmembers;
    $origrecord->maxgroups = $groupformation->maxgroups;
    $origrecord->timeopen = $groupformation->timeopen;
    $origrecord->timeclose = $groupformation->timeclose;
    $result = $DB->update_record('groupformation', $origrecord);

    // Get current DB record (with all DB defaults).
    $groupformation = $DB->get_record('groupformation', array(
            'id' => $groupformation->id));

    groupformation_grade_item_update($groupformation);

    return $result;
}

/**
 * Removes an instance of the groupformation from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 * @throws dml_exception
 */
function groupformation_delete_instance($id) {
    global $DB, $USER;

    if (!$groupformation = $DB->get_record('groupformation', array(
            'id' => $id))
    ) {
        return false;
    }

    // Delete any dependent records here.
    $DB->delete_records('groupformation', array(
            'id' => $groupformation->id));

    // Cascading deletion of all related db entries.
    $DB->delete_records('groupformation_answers', array(
            'groupformation' => $id));
    $DB->delete_records('groupformation_users', array(
            'groupformation' => $id));
    $DB->delete_records('groupformation_jobs', array(
            'groupformationid' => $id));
    $DB->delete_records('groupformation_user_values', array(
            'groupformationid' => $id));
    $DB->delete_records('groupformation_groups', array(
            'groupformation' => $id));
    $DB->delete_records('groupformation_group_users', array(
            'groupformation' => $id));
    groupformation_grade_item_delete($groupformation);

    return true;
}

/**
 * Returns user outline
 *
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $groupformation
 * @return stdClass|null
 */
function groupformation_user_outline($course, $user, $mod, $groupformation) {
    $return = new stdClass ();
    $return->time = 0;
    $return->info = '';

    return $return;
}

/**
 * Prints user complete
 *
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course
 *            the current course record
 * @param stdClass $user
 *            the record of the user we are generating report for
 * @param cm_info $mod
 *            course module info
 * @param stdClass $groupformation
 *            the module instance record
 * @return void, is supposed to echp directly
 */
function groupformation_user_complete($course, $user, $mod, $groupformation) {
}

/**
 * Prints recent activity
 *
 * Given a course and a time, this module should find recent activity
 * that has occurred in newmodule activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @param stdClass $course
 * @param array $viewfullnames
 * @param number $timestart
 * @return boolean
 */
function groupformation_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link groupformation_print_recent_mod_activity()}.
 *
 * @param array $activities
 *            sequentially indexed array of objects with the 'cmid' property
 * @param int $index
 *            the index in the $activities to use for the next record
 * @param int $timestart
 *            append activity since this time
 * @param int $courseid
 *            the id of the course we produce the report for
 * @param int $cmid
 *            course module id
 * @param int $userid
 *            check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid
 *            check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function groupformation_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid = 0,
        $groupid = 0) {
}

/**
 * Prints single activity item
 *
 * @param stdClass $activity
 * @param int $courseid
 * @param stdClass $detail
 * @param array $modnames
 * @param array $viewfullnames
 * @return void
 */
function groupformation_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc .
 *
 * @return boolean
 */
function groupformation_cron() {
    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @return array
 */
function groupformation_get_extra_capabilities() {
    return array();
}

/**
 * Gradebook API //
 */
/**
 * Is a given scale used by the instance of groupformation?
 *
 * This function returns if a scale is being used by one groupformation
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $groupformationid
 *            ID of an instance of this module
 * @param int $scaleid
 * @return bool true if the scale is used by the given groupformation instance
 * @throws dml_exception
 */
function groupformation_scale_used($groupformationid, $scaleid) {
    global $DB;
    if ($scaleid and $DB->record_exists('groupformation', array(
                    'id' => $groupformationid, 'grade' => -$scaleid))
    ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of groupformation.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid
 * @return boolean true if the scale is used by any groupformation instance
 * @throws dml_exception
 */
function groupformation_scale_used_anywhere($scaleid) {
    global $DB;
    if ($scaleid and $DB->record_exists('groupformation', array(
                    'grade' => -$scaleid))
    ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give groupformation instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $groupformation
 *            instance object with extra cmidnumber and modname property
 * @param bool $reset
 * @return void
 * @throws coding_exception
 */
function groupformation_grade_item_update(stdClass $groupformation, $reset = false) {
    global $CFG;

    $item = array();
    $item ['itemname'] = clean_param($groupformation->name, PARAM_NOTAGS);
    $item ['gradetype'] = GRADE_TYPE_VALUE;

    if ($groupformation->grade > 0) {
        $item ['gradetype'] = GRADE_TYPE_VALUE;
        $item ['grademax'] = $groupformation->grade;
        $item ['grademin'] = 0;
    } else if ($groupformation->grade < 0) {
        $item ['gradetype'] = GRADE_TYPE_SCALE;
        $item ['scaleid'] = -$groupformation->grade;
    } else {
        $item ['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($reset) {
        $item ['reset'] = true;
    }

    grade_update('mod/groupformation', $groupformation->course, 'mod', 'groupformation', $groupformation->id, 0, null,
            $item);
}

/**
 * Delete grade item for given groupformation instance
 *
 * @param stdClass $groupformation
 *            instance object
 * @return grade_item
 */
function groupformation_grade_item_delete($groupformation) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/groupformation', $groupformation->course, 'mod', 'groupformation', $groupformation->id, 0,
            null, array(
                    'deleted' => 1));
}

/**
 * Update groupformation grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $groupformation
 *            instance object with extra cmidnumber and modname property
 * @param int $userid
 *            update grade of specific user only, 0 means all participants
 * @return void
 */
function groupformation_update_grades(stdClass $groupformation, $userid = 0) {
    $grades = array(); // Populate array of grade objects indexed by userid.
    grade_update('mod/groupformation', $groupformation->course, 'mod', 'groupformation', $groupformation->id, 0,
            $grades);
}

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function groupformation_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * File browsing support for groupformation file areas
 *
 * @package mod_groupformation
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function groupformation_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath,
        $filename) {
    return null;
}

/**
 * Handles files
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function groupformation_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'groupformation_answers' && $filearea !== 'anotherexpectedfilearea') {
        return false;
    }

    // Make sure the user is logged in and has access to the module
    // (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/groupformation:view', $context)) {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // The $args is empty => the path is '/' .
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // The $args contains elements of the filepath .
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_groupformation', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 86400, 0, true, $options);
}

/**
 * Navigation API //
 */
/**
 * Extends the global navigation tree by adding groupformation nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the newmodule module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function groupformation_extend_navigation(navigation_node $navref, stdClass $course, stdClass $module, cm_info $cm) {
    foreach ($navref->parent->get_children_key_list() as $key) {
        $node = $navref->parent->get($key);
        if (count($node->get_children_key_list()) == 0) {
            $node->nodetype = navigation_node::NODETYPE_LEAF;
        }
    }
}

/**
 * Extends the settings navigation with the groupformation settings
 *
 * This function is called when the context for the page is a groupformation module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav
 *            {@link settings_navigation}
 * @param navigation_node $groupformationnode
 *            {@link navigation_node}
 */
function groupformation_extend_settings_navigation(settings_navigation $settingsnav,
        navigation_node $groupformationnode = null) {
}

/**
 * Sets all the important fields and clears fields which are supposed to be empty or back on default.
 *
 * @param stdClass $groupformation
 * @return stdClass
 */
function groupformation_set_fields(stdClass $groupformation) {

    if (isset ($groupformation->binquestion) && $groupformation->binquestion == 0) {
        $groupformation->binquestion = 0;
        $groupformation->binquestiontext = "";
        $groupformation->binquestionlines = "";
        $groupformation->binquestionvalues = null;
        $groupformation->binquestionnumber = null;
        $groupformation->binquestionimportance = null;
        $groupformation->binquestionrelation = null;
        $groupformation->binquestionmultiselect = null;
    } else if (!isset ($groupformation->binquestion)) {
        $groupformation->binquestion = 0;
        $groupformation->binquestiontext = "";
        $groupformation->binquestionlines = "";
        $groupformation->binquestionvalues = null;
        $groupformation->binquestionnumber = null;
        $groupformation->binquestionimportance = null;
        $groupformation->binquestionrelation = null;
        $groupformation->binquestionmultiselect = null;
    } else if (isset ($groupformation->binquestion) && $groupformation->binquestion == 1 &&
            isset ($groupformation->binquestiontext) && $groupformation->binquestiontext == "") {
        $groupformation->binquestion = 0;
        $groupformation->binquestiontext = "";
        $groupformation->binquestionlines = "";
        $groupformation->binquestionvalues = null;
        $groupformation->binquestionnumber = null;
        $groupformation->binquestionimportance = null;
        $groupformation->binquestionrelation = null;
        $groupformation->binquestionmultiselect = null;
    } else if (isset ($groupformation->binquestion) && $groupformation->binquestion == 1 &&
            isset ($groupformation->binquestionlines) && $groupformation->binquestionlines == "") {
        $groupformation->binquestion = 0;
        $groupformation->binquestiontext = "";
        $groupformation->binquestionlines = "";
        $groupformation->binquestionvalues = null;
        $groupformation->binquestionnumber = null;
        $groupformation->binquestionimportance = null;
        $groupformation->binquestionrelation = null;
        $groupformation->binquestionmultiselect = null;
    } else if (!isset($groupformation->binquestionimportance)) {
        $groupformation->binquestion = 0;
        $groupformation->binquestiontext = "";
        $groupformation->binquestionlines = "";
        $groupformation->binquestionvalues = null;
        $groupformation->binquestionnumber = null;
        $groupformation->binquestionimportance = null;
        $groupformation->binquestionrelation = null;
        $groupformation->binquestionmultiselect = null;
    } else {
        $binanswerarray = array();
        if (!isset ($groupformation->binquestionrelation)) {
            $groupformation->binquestionrelation = 0;
        }
        if (!isset($groupformation->binquestionmultiselect)) {
            $groupformation->binquestionmultiselect = 0;
        }
        if ($groupformation->binquestion != 0) {
            $binanswerarray = explode("\n", $groupformation->binquestionlines);
        }
        $groupformation->binquestionvalues = groupformation_convert_options($binanswerarray);
        $groupformation->binquestionnumber = count($binanswerarray);
    }

    if (isset ($groupformation->knowledge) && $groupformation->knowledge == 0) {
        $groupformation->knowledge = 0;
        $groupformation->knowledgelines = "";
        $groupformation->knowledgevalues = null;
        $groupformation->knowledgenumber = null;
    } else if (!isset ($groupformation->knowledge)) {
        $groupformation->knowledge = 0;
        $groupformation->knowledgelines = "";
        $groupformation->knowledgevalues = null;
        $groupformation->knowledgenumber = null;
    } else if (isset ($groupformation->knowledge) && $groupformation->knowledge == 1 &&
            isset ($groupformation->knowledgelines) && $groupformation->knowledgelines == ""
    ) {
        $groupformation->knowledge = 0;
        $groupformation->knowledgelines = "";
        $groupformation->knowledgevalues = null;
        $groupformation->knowledgenumber = null;
    } else {
        $knowledgearray = array();
        if ($groupformation->knowledge != 0) {
            $knowledgearray = explode("\n", $groupformation->knowledgelines);
        }
        $groupformation->knowledgevalues = groupformation_convert_options($knowledgearray);
        $groupformation->knowledgenumber = count($knowledgearray);
    }

    if (isset ($groupformation->topics) && $groupformation->topics == 0) {
        $groupformation->topics = 0;
        $groupformation->topiclines = "";
        $groupformation->topicvalues = null;
        $groupformation->topicnumber = null;
    } else if (!isset ($groupformation->topics)) {
        $groupformation->topics = 0;
        $groupformation->topiclines = "";
        $groupformation->topicvalues = null;
        $groupformation->topicnumber = null;
    } else if (isset ($groupformation->topics) && $groupformation->topics == 1 && isset ($groupformation->topiclines) &&
            $groupformation->topiclines == ""
    ) {
        $groupformation->topics = 0;
        $groupformation->topiclines = "";
        $groupformation->topicvalues = null;
        $groupformation->topicnumber = null;
    } else {
        $topicarray = array();
        if ($groupformation->topics != 0) {
            $topicarray = explode("\n", $groupformation->topiclines);
        }
        $groupformation->topicvalues = groupformation_convert_options($topicarray);
        $groupformation->topicnumber = count($topicarray);
    }

    if (isset ($groupformation->groupoption) && $groupformation->groupoption == 1) {
        $groupformation->maxmembers = 0;
    } else if (isset ($groupformation->groupoption) && $groupformation->groupoption == 0) {
        $groupformation->maxgroups = 0;
    }

    if (isset ($groupformation->evaluationmethod) && $groupformation->evaluationmethod != 2) {
        $groupformation->maxpoints = 100;
    }

    if (isset ($groupformation->onlyactivestudents)) {
        $groupformation->onlyactivestudents = 1;
    } else {
        $groupformation->onlyactivestudents = 0;
    }

    if (isset ($groupformation->emailnotifications)) {
        $groupformation->emailnotifications = 1;
    } else {
        $groupformation->emailnotifications = 0;
    }

    if (isset ($groupformation->allanswersrequired)) {
        $groupformation->allanswersrequired = 1;
    } else {
        $groupformation->allanswersrequired = 0;
    }

    return $groupformation;
}

/**
 * Returns groupformation by id
 *
 * @param int $id
 * @return mixed
 * @throws dml_exception
 */
function groupformation_get_by_id($id) {
    global $DB;

    return $DB->get_record('groupformation', ['id' => $id]);
}
