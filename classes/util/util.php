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
 * Utility class for various methods
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once(dirname(__FILE__) . '/define_file.php');

/**
 * Class mod_groupformation_util
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_util {

    /**
     * Returns html code for info text for teachers
     *
     * @param bool|false $unfolded
     * @param string $page
     * @return string
     * @throws coding_exception
     */
    public static function get_info_text_for_teacher($page = "settings") {
        $t = '
        <p>
            <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#exampleModal">';
        $t .= get_string('info_header_teacher_' . $page, 'groupformation');
        $t .= '
            </button>
        </p>
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">';
        $t .= get_string('info_header_teacher_' . $page, 'groupformation');
        $t .= '         </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">';
        $t .= get_string('info_text_teacher_' . $page, 'groupformation');
        $t .= '     </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>';
        return $t;
    }

    /**
     * Returns html code for info text for students
     *
     * @param null $groupformationid
     * @param string $role
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function get_info_text_for_student($groupformationid = null, $role = "student") {
        if (is_null($groupformationid)) {
            return "";
        }
        $store = new mod_groupformation_storage_manager ($groupformationid);

        $scenarioname = get_string('scenario_' . $store->get_scenario_name(), 'groupformation');
        $a = new stdClass ();
        $a->scenario_name = $scenarioname;

        $t = '
        <p>
            <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#exampleModal">';
        $t .= get_string('info_header_' . $role, 'groupformation');
        $t .= '
            </button>
        </p>
        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">';
        $t .= get_string('info_header_' . $role, 'groupformation');
        $t .= '         </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">';
        $t .= get_string('info_text_' . $role, 'groupformation', $a);
        $t .= '     </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>';
        return $t;
    }

    /**
     * Returns user record
     *
     * @param int $userid
     * @return stdClass|null
     * @throws dml_exception
     */
    public static function get_user_record($userid) {
        global $DB;
        if ($DB->record_exists('user',
                array(
                        'id' => $userid
                )
        )
        ) {
            return $DB->get_record('user',
                    array(
                            'id' => $userid
                    )
            );
        } else {
            return null;
        }
    }

    /**
     * Computes stats about answered and misssing questions
     *
     * @param int $groupformationid
     * @param int $userid
     * @return array
     * @throws dml_exception
     */
    public static function get_stats($groupformationid, $userid) {
        $usermanager = new mod_groupformation_user_manager($groupformationid);
        $store = new mod_groupformation_storage_manager($groupformationid);

        $categoryset = $store->get_categories();

        $categories = array();

        foreach ($categoryset as $category) {
            $categories [$category] = $store->get_number($category);
        }

        $stats = array();
        foreach ($categories as $category => $value) {
            $count = $usermanager->get_number_of_answers($userid, $category);
            $stats [$category] = array(
                    'questions' => $value,
                    'answered' => $count,
                    'missing' => $value - $count
            );
        }

        return $stats;
    }

    /**
     * Converts OPTIONS xml to array
     *
     * @param string $xmlcontent
     * @return array
     */
    public static function xml_to_array($xmlcontent) {
        $xml = simplexml_load_string($xmlcontent);

        $options = array();

        foreach ($xml->children() as $option) {
            $options[] = trim($option);
        }
        return $options;
    }

    /**
     * Deletes user-related data (e.g. answers)
     *
     * @param int $groupformationid
     * @throws dml_exception
     */
    public static function delete_user_related_data($groupformationid) {
        global $DB;

        $DB->delete_records('groupformation_answers', array('groupformation' => $groupformationid));
    }

    /**
     * Archives activity
     *
     * @param int $groupformationid
     * @throws dml_exception
     */
    public static function archive_activity($groupformationid) {
        global $DB;

        $record = $DB->get_record('groupformation', array('id' => $groupformationid));
        $record->archived = 1;
        $DB->update_record('groupformation', $record);
    }

    /**
     *  Handles old instances
     */
    public static function handling_old_instances() {
        global $DB;
        $now = time();

        $configvalue = get_config('groupformation', 'archiving_time');

        if (is_null($configvalue) || intval($configvalue) <= 0) {
            $configvalue = 360;
        }

        $difference = intval($configvalue) * 24 * 60 * 60;
        $instances = $DB->get_records('groupformation');

        foreach ($instances as $groupformation) {
            $query = "SELECT MAX(timecreated) FROM {logstore_standard_log} WHERE courseid = :courseid";
            $lastactivity = intval($DB->get_field_sql($query, array('courseid' => $groupformation->course)));

            if (($now - $lastactivity) > $difference) {

                self::delete_user_related_data($groupformation->id);
                self::archive_activity($groupformation->id);
            }
        }
    }

    /**
     * Returns student user ids of the course
     *
     * @param null $groupformationid
     * @param mod_groupformation_storage_manager $store
     * @param null $context
     * @param null $job
     * @return array
     * @throws dml_exception
     */
    public static function get_users($groupformationid = null, $store = null, $context = null, $job = null) {
        $ajm = new mod_groupformation_advanced_job_manager();

        if (is_null($job)) {
            $job = $ajm::get_job($groupformationid);
        }

        if (is_null($store)) {
            $store = new mod_groupformation_storage_manager($groupformationid);
        }

        $courseid = $store->get_course_id();
        $context = context_course::instance($courseid);

        $enrolledstudents = null;

        if (intval($job->groupingid) != 0) {
            $enrolledstudents = array_keys(groups_get_grouping_members($job->groupingid));
        } else {
            $enrolledstudents = array_keys(get_enrolled_users($context, 'mod/groupformation:onlystudent'));
            $enrolledprevusers = array_keys(get_enrolled_users($context, 'mod/groupformation:editsettings'));
            $diff = array_diff($enrolledstudents, $enrolledprevusers);
            $enrolledstudents = $diff;
        }
        if (is_null($enrolledstudents) || count($enrolledstudents) <= 0) {
            return array();
        }

        return $enrolledstudents;
    }

}