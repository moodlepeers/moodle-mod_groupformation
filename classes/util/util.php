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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once(dirname(__FILE__) . '/define_file.php');

class mod_groupformation_util {

    /**
     * Returns html code for info text for teachers
     *
     * @param bool|false $unfolded
     * @param string $page
     * @return string
     * @throws coding_exception
     */
    public static function get_info_text_for_teacher($unfolded = false, $page = "settings") {
        $s = '<p><a class="show">' . get_string('info_header_teacher_' . $page, 'groupformation') . '</a></p>';
        $s .= '<div id="info_text" style="display: ' . (($unfolded) ? 'block' : 'none') . ';">';
        $s .= '<p style="padding-left: 10px;">' . get_string('info_text_teacher_' . $page, 'groupformation') . '</p>';
        $s .= '</div>';
        $s .= self::get_js_for_info_text();

        return $s;
    }

    /**
     * Returns inline JS
     *
     * @return string
     */
    private static function get_js_for_info_text() {
        $s = "";
        $s .= '<script type="text/javascript">';
        $s .= '        $(function() {';
        $s .= '            $(\'.show\').click(function() {';
        $s .= '                $(\'#info_text\').slideToggle();';
        $s .= '            });';
        $s .= '        });';
        $s .= '</script>';

        return $s;
    }

    /**
     * Returns html code for info text for students
     *
     * @param bool|false $unfolded
     * @param null $groupformationid
     * @param string $role
     * @return string
     * @throws coding_exception
     */
    public static function get_info_text_for_student($unfolded = false, $groupformationid = null, $role = "student") {
        if (is_null($groupformationid)) {
            return "";
        }
        $store = new mod_groupformation_storage_manager ($groupformationid);

        $scenarioname = get_string('scenario_' . $store->get_scenario_name(), 'groupformation');
        $a = new stdClass ();
        $a->scenario_name = $scenarioname;

        $s = '<p><a class="show">' . get_string('info_header_' . $role, 'groupformation') . '</a></p>';
        $s .= '<div id="info_text" style="display: ' . (($unfolded) ? 'block' : 'none') . ';">';
        $s .= '<p style="padding-left: 10px;">' . get_string('info_text_' . $role, 'groupformation', $a) . '</p>';
        $s .= '</div>';
        $s .= self::get_js_for_info_text();

        return $s;
    }

    /**
     * Returns user record
     *
     * @param int $userid
     * @return stdClass|null
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
     * @param $groupformationid
     * @param $userid
     * @return array
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
     * @param $xmlcontent
     * @return array
     */
    public static function xml_to_array($xmlcontent) {
        $xml = simplexml_load_string($xmlcontent);
        $options = array();
        foreach ($xml->OPTION as $option) {
            $options[] = trim($option);
        }

        return $options;
    }

    /**
     * Deletes user-related data (e.g. answers)
     *
     * @param int $groupformationid
     */
    public static function delete_user_related_data($groupformationid) {
        global $DB;

        $DB->delete_records('groupformation_answer', array('groupformation' => $groupformationid));
    }

    /**
     * Archives activity
     *
     * @param int $groupformationid
     */
    public static function archive_activity($groupformationid) {
        global $DB;

        $record = $DB->get_record('groupformation_q_settings', array('groupformation' => $groupformationid));
        $record->archived = 1;
        $DB->update_record('groupformation_q_settings', $record);
    }

    /**
     *  Handles old instances
     */
    public static function handling_old_instances() {
        global $DB;
        $now = time();

        $configValue = get_config('groupformation', 'archiving_time');

        if (is_null($configValue) || intval($configValue) <= 0) {
            $configValue = 360;
        }

        $difference = intval($configValue) * 24 * 60 * 60;
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
     * @param $store
     * @return array
     */
    public static function get_users($groupformationid = null, $store = null, $context = null, $job = null) {
        if (is_null($job)) {
            $job = mod_groupformation_job_manager::get_job($groupformationid);
        }

        if (is_null($store)) {
            $store = new mod_groupformation_storage_manager($groupformationid);
        }

        if (is_null($context)) {
            $courseid = $store->get_course_id();
            $context = context_course::instance($courseid);
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
            return null;
        }

        return $enrolledstudents;
    }

}