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
 * Adapter class between DB and Plugin
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/advanced_job_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/state_machine.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_state_machine.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/group/lib.php');

/**
 * Class mod_groupformation_storage_manager
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_storage_manager {

    /** @var int ID of module instance */
    private $groupformationid;

    /** @var mod_groupformation_state_machine State machine */
    public $statemachine;

    /** @var mod_groupformation_user_state_machine User State machine */
    public $userstatemachine;

    /**
     * Constructs storage manager for a specific groupformation
     *
     * @param int $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->statemachine = new mod_groupformation_state_machine($groupformationid);
        $this->userstatemachine = new mod_groupformation_user_state_machine($groupformationid);
    }

    /**
     * Returns intro box if intro is set
     *
     * @param int $id
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_intro($id) {
        global $OUTPUT;

        $box = "";
        $gf = groupformation_get_by_id($this->groupformationid);

        if ($gf->intro) {
            $box = $OUTPUT->box(format_module_intro('groupformation', $gf, $id), 'generalbox mod_introbox',
                    'groupformationintro');
        }

        return $box;
    }

    /**
     * Returns version of groupformation instance
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_version() {
        global $DB;

        return $DB->get_field('groupformation', 'version', array('id' => $this->groupformationid));
    }

    /**
     * Returns whether all answers are required or not
     *
     * @return bool
     * @throws dml_exception
     */
    public function all_answers_required() {
        global $DB;

        return boolval($DB->get_field('groupformation', 'allanswersrequired', array('id' => $this->groupformationid)));
    }

    /**
     * Returns whether the activity is archived
     *
     * @return bool
     * @throws dml_exception
     */
    public function is_archived() {
        global $DB;
        $record = $DB->get_record('groupformation', array(
                'id' => $this->groupformationid
        ));

        return $record->archived == 1;
    }

    /**
     * Returns whether the activity is accessible
     *
     * @param int $userid
     * @return bool
     */
    public function is_accessible($userid) {
        global $PAGE;
        $cm = $PAGE->cm;
        $context = context_module::instance($cm->id);
        $groupingid = ($cm->groupmode != 0) ? $cm->groupingid : 0;
        $enrolledstudents = null;
        if (intval($cm->groupingid) != 0) {
            $enrolledstudents = array_keys(groups_get_grouping_members($groupingid));
        } else {
            $enrolledstudents = array_keys(get_enrolled_users($context, 'mod/groupformation:onlystudent'));
        }

        return in_array($userid, $enrolledstudents);
    }

    /**
     * Returns if DB does not contain questions for a specific category
     *
     * @param string $category
     * @return boolean
     * @throws dml_exception
     */
    public function catalog_table_not_set($category = 'grade') {
        global $DB;

        $count = $DB->count_records('groupformation_questions', array('category' => $category));

        return $count == 0;
    }

    /**
     * Returns course id
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_course_id() {
        global $DB;

        return $DB->get_field('groupformation', 'course', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * Returns instance number of all groupformations in course
     */
    public function get_instance_number() {
        global $DB;
        $courseid = $this->get_course_id();
        $records = $DB->get_records('groupformation', array(
                'course' => $courseid
        ), 'id', 'id');
        $i = 1;
        foreach ($records as $id => $record) {
            if ($id == $this->groupformationid) {
                return $i;
            } else {
                $i++;
            }
        }

        return $i;
    }

    /**
     * Returns map with availability times (xxx_raw is timestamp, xxx is formatted time for display)
     *
     * @return array :string NULL mixed
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_time() {
        global $DB;
        $times = array();
        $times ['start_raw'] = $DB->get_field('groupformation', 'timeopen', array(
                'id' => $this->groupformationid
        ));
        $times ['end_raw'] = $DB->get_field('groupformation', 'timeclose', array(
                'id' => $this->groupformationid
        ));

        if ('en' == get_string("language", "groupformation")) {
            $format = "l jS \of F j, Y, g:i a";
            $trans = array();
            $times ['start'] = strtr(date($format, $times ['start_raw']), $trans);
            $times ['end'] = strtr(date($format, $times ['end_raw']), $trans);
        } else {
            if ('de' == get_string("language", "groupformation")) {
                $format = "l, d.m.y, H:m";
                $trans = array(
                        'Monday' => 'Montag',
                        'Tuesday' => 'Dienstag',
                        'Wednesday' => 'Mittwoch',
                        'Thursday' => 'Donnerstag',
                        'Friday' => 'Freitag',
                        'Saturday' => 'Samstag',
                        'Sunday' => 'Sonntag',
                        'Mon' => 'Mo',
                        'Tue' => 'Di',
                        'Wed' => 'Mi',
                        'Thu' => 'Do',
                        'Fri' => 'Fr',
                        'Sat' => 'Sa',
                        'Sun' => 'So',
                        'January' => 'Januar',
                        'February' => 'Februar',
                        'March' => 'März',
                        'May' => 'Mai',
                        'June' => 'Juni',
                        'July' => 'Juli',
                        'October' => 'Oktober',
                        'December' => 'Dezember'
                );
                $times ['start'] = strtr(date($format, $times ['start_raw']), $trans) . ' Uhr';
                $times ['end'] = strtr(date($format, $times ['end_raw']), $trans) . ' Uhr';
            }
        }

        return $times;
    }

    /**
     * Converts knowledge or topic array into XML-based syntax
     *
     * @param unknown $options
     * @return string
     */
    public function convert_options($options) {
        $op = implode("</OPTION>  <OPTION>", $options);

        return "<OPTION>" . $op . "</OPTION>";
    }

    /**
     * Returns an array with number of questions in each category
     *
     * @param array $categories
     * @return array
     * @throws dml_exception
     */
    public function get_numbers($categories) {

        $array = array();
        foreach ($categories as $category) {
            $array [] = $this->get_number($category);
        }

        return $array;
    }

    /**
     * Returns possible language
     *
     * @param string $category
     * @return mixed
     * @throws dml_exception
     */
    public function get_possible_language($category) {
        global $DB;

        $table = 'groupformation_questions';

        $lang = $DB->get_field($table, 'language', array('category' => $category), IGNORE_MULTIPLE);

        return $lang;
    }

    /**
     * Returns number of possible choices of binquestion-answers
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_number_binchoices() {
        global $DB;
        return $DB->get_field('groupformation', 'binquestionnumber', array(
                'id' => $this->groupformationid));
    }

    /**
     * Returns the number of questions in a specified category
     *
     * @param string $category
     * @return mixed
     * @throws dml_exception
     */
    public function get_number($category = null) {
        global $DB;

        if ($category == 'binquestion' && ($DB->get_field('groupformation', $category . 'number', array(
                                'id' => $this->groupformationid
                        )) >= 1)) {
            return 1; // TODO absprechen ob so okay
        }
        if ($category == 'topic' || $category == 'knowledge' || $category == 'binquestion') {
            return $DB->get_field('groupformation', $category . 'number', array(
                    'id' => $this->groupformationid
            ));
        } else {
            return $DB->get_field('groupformation_q_version', 'numberofquestion', array(
                    'category' => $category
            ));
        }
    }

    /**
     * Returns the number of questions in a specified category
     *
     * @param string $category
     * @return mixed
     * @throws dml_exception
     */
    public function get_question_number($category = null) {
        global $DB;

        if ($category == 'binquesiton') {
            if ($DB->get_field('groupformation', $category . 'number', array(
                            'id' => $this->groupformationid
                    )) >= 1) {
                return 1;
            }
        }
        return $this->get_number($category);
    }

    /**
     * Returns either knowledge or topic values
     *
     * @param unknown $category
     * @return mixed
     * @throws dml_exception
     */
    public function get_knowledge_or_topic_values($category) {
        global $DB;

        return $DB->get_field('groupformation', $category . 'values', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * returns text of bin question
     *
     * @return mixed
     */
    public function get_binquestion_text() {
        global $DB;

        return $DB->get_field('groupformation', 'binquestiontext', array('id' => $this->groupformationid));
    }

    // public function get_binquestion_multiselect(){
    // global $DB;
    // return $DB->get_field('groupformation', 'binquestionmultiselect', array('id' => $this->groupformationid));
    // }
    /**
     * Returns max number of options for a specific question in a specific category
     *
     * @param unknown $i
     * @param string $category
     * @return int
     * @throws dml_exception
     */
    public function get_max_option_of_catalog_question($i, $category = 'grade') {
        global $DB;
        if ($category == 'points') {
            return $this->get_max_points();
        }

        $table = 'groupformation_questions';
        return $DB->get_field($table, 'optionmax', array(
                'language' => 'en', 'category' => $category,
                'questionid' => $i
        ));
    }

    /**
     * Returns a specific question in a specific category
     *
     * @param unknown $i
     * @param string $category
     * @param string $lang
     * @param null $version
     * @return mixed
     * @throws dml_exception
     */
    public function get_catalog_question($i, $category = 'general', $lang = 'en', $version = null) {
        global $DB;
        $table = 'groupformation_questions';
        $return = $DB->get_record($table, array(
                'language' => $lang, 'category' => $category,
                'position' => $i, 'version' => $version
        ));

        return $return;
    }

    /**
     * Returns version of requested category stored in DB
     *
     * @param string $category
     * @return mixed
     * @throws dml_exception
     */
    public function get_catalog_version($category) {
        global $DB;

        $table = "groupformation_q_version";

        return $DB->get_field($table, 'version', array('category' => $category));
    }

    /**
     * Returns the scenario
     *
     * @param boolean $assigned if scenario is assigned
     * @return string
     * @throws dml_exception
     */
    public function get_scenario($assigned = false) {
        global $DB;

        $settings = $DB->get_record('groupformation', array(
                'id' => $this->groupformationid
        ));

        if ($assigned) {
            return $settings->szenario;
        }

        $scenarios = $DB->get_records('groupformation_scenario');

        $scenarioid = $settings->szenario;

        return $DB->get_field('groupformation_scenario', 'id', array('assigned_id' => $scenarioid));
    }

    /**
     * Returns logging data
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     * @throws dml_exception
     */
    public function get_logging_data($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_logging', array(
                'groupformationid' => $this->groupformationid
        ), $sortedby, $fieldset);
    }

    /**
     * Returns raw categories without applying any activity based restrictions
     *
     * @return array
     * @throws dml_exception
     */
    public function get_raw_categories() {
        global $DB;

        $cats = $DB->get_records('groupformation_scenario_cats', array('scenario' => $this->get_scenario()));
        $categories = array();
        foreach ($cats as $cat) {
            $categories[] = $DB->get_field('groupformation_q_version', 'category', array('id' => $cat->category));
        }
        return $categories;
    }

    /**
     * Returns categories with at least one question, not just the scenario-based category set
     *
     * @return array
     * @throws dml_exception
     */
    public function get_categories() {
        $categoryset = $this->get_raw_categories();
        $categories = array();

        $hasknowledge = $this->get_number('knowledge');
        if ($this->ask_for_knowledge() && $hasknowledge != 0) {
            $categories[] = 'knowledge';
        }

        $hasbinquestion = $this->get_number('binquestion');
        if ($this->ask_for_binquestion() && $hasbinquestion != 0) {
            $categories[] = 'binquestion';
        }

        foreach ($categoryset as $category) {
            if ($this->get_number($category) > 0) {
                if ($category == 'grade' && $this->ask_for_grade()) {
                    $categories [] = $category;
                } else {
                    if ($category == 'points' && $this->ask_for_points()) {
                        $categories [] = $category;
                    } else {
                        if ($category != 'grade' && $category != 'points' && $category != 'general') {
                            $categories [] = $category;
                        }
                    }
                }
            }
        }

        $hastopic = $this->get_number('topic');

        if ($this->ask_for_topics() && $hastopic != 0) {
            $categories = array('topic');
        }

        return $categories;
    }

    /**
     * Returns all exportable categories
     *
     * @return array
     * @throws dml_exception
     */
    public function get_exportable_categories() {
        $exportablecategories = array();
        $categories = $this->get_categories();
        foreach ($categories as $category) {
            if (!in_array($category, array(
                    'points',
                    'knowledge',
                    'topic'
            ))
            ) {
                $exportablecategories [] = $category;
            }
        }

        return $exportablecategories;
    }

    /**
     * Gets next category
     *
     * @param string $category
     * @return string
     * @throws dml_exception
     */
    public function get_next_category($category) {
        $categories = $this->get_categories();
        $pos = $this->get_position($category);
        if ($pos < count($categories) - 1) {
            $previous = $categories [$pos + 1];
        } else {
            $previous = '';
        }

        return $previous;
    }

    /**
     * Gets previous category
     *
     * @param string $category
     * @return string
     * @throws dml_exception
     */
    public function get_previous_category($category) {
        $categories = $this->get_categories();
        $pos = $this->get_position($category);
        if ($pos >= 1) {
            $previous = $categories [$pos - 1];
        } else {
            $previous = '';
        }

        return $previous;
    }

    /**
     * Returns whether the questionnaire asks for grade
     *
     * @return boolean
     * @throws dml_exception
     */
    public function ask_for_grade() {
        global $DB;
        $evaluationmethod = $DB->get_field('groupformation', 'evaluationmethod', array(
                'id' => $this->groupformationid
        ));

        return $evaluationmethod == 1;
    }

    /**
     * Returns whether the questionnaire asks for points
     *
     * @return boolean
     * @throws dml_exception
     */
    public function ask_for_points() {
        global $DB;

        $evaluationmethod = $DB->get_field('groupformation', 'evaluationmethod', array(
                'id' => $this->groupformationid
        ));

        return $evaluationmethod == 2;
    }

    /**
     * Returns whether this instance is still editable or not
     *
     * @return boolean
     * @throws dml_exception
     */
    public function is_editable() {
        global $DB;

        if (is_null($this->groupformationid) || $this->groupformationid == '') {
            return true;
        }

        return ($DB->count_records('groupformation_answers', array(
                        'groupformation' => $this->groupformationid
                )) == 0);
    }

    /**
     * Returns all answers to a specific question in a specific category
     *
     * @param string $category
     * @param int $questionid
     * @return array
     * @throws dml_exception
     */
    public function get_answers_to_special_question($category, $questionid) {
        global $DB;

        return $DB->get_records('groupformation_answers', array(
                'groupformation' => $this->groupformationid,
                'category' => $category,
                'questionid' => $questionid
        ));
    }

    /**
     * Returns maximum number of points
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_max_points() {
        global $DB;

        return $DB->get_field('groupformation', 'maxpoints', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * Returns whether questionnaire is available or not
     *
     * @return boolean
     * @throws coding_exception
     * @throws dml_exception
     */
    public function is_questionnaire_available() {
        $now = time();

        $time = $this->get_time();

        $start = intval($time ['start_raw']);
        $end = intval($time ['end_raw']);

        if (($start == 0) && ($end == 0)) {
            return true;
        } else {
            if (($start == 0) && ($now <= $end)) {
                return true;
            } else {
                if (($now >= $start) && ($end == 0)) {
                    return true;
                } else {
                    if (($now >= $start) && ($now <= $end)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Sets timestamp in groupformation in order to close/terminate questionnaire
     */
    public function close_questionnaire() {
        global $DB;

        $data = new stdClass ();
        $data->id = $this->groupformationid;
        $data->timeclose = time() - 1;

        $DB->update_record('groupformation', $data);
    }

    /**
     * Sets timestamp in groupformation in order to open/begin questionnaire
     */
    public function open_questionnaire() {
        global $DB;

        $data = new stdClass ();
        $data->id = $this->groupformationid;
        $data->timeclose = 0;
        $data->timeopen = time() - 1;

        $DB->update_record('groupformation', $data);
    }

    /**
     * Returns group size as set in settings
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_max_members() {
        global $DB;

        return $DB->get_field('groupformation', 'maxmembers', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * Returns number of groups as set in settings
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_max_groups() {
        global $DB;

        return $DB->get_field('groupformation', 'maxgroups', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * Returns option if students with no answers should be exluded in formation
     *
     * @return boolean
     * @throws dml_exception
     */
    public function get_grouping_setting() {
        global $DB;

        return $DB->get_field('groupformation', 'onlyactivestudents', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * Returns the chosen option if whether group size or the number of group is fixed in settings
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_group_option() {
        global $DB;

        return (bool) ($DB->get_field('groupformation', 'groupoption', array(
                'id' => $this->groupformationid
        )));
    }

    /**
     * Returns group name prefix
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_group_name_setting() {
        global $DB;

        return $DB->get_field('groupformation', 'groupname', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * Returns the name of the groupformation instance
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_name() {
        global $DB;

        return $DB->get_field('groupformation', 'name', array(
                'id' => $this->groupformationid
        ));
    }

    /**
     * Returns position of the category
     *
     * @param unknown $category
     * @return mixed|number
     * @throws dml_exception
     */
    public function get_position($category) {
        $categories = $this->get_categories();
        if (in_array($category, $categories)) {
            $pos = array_search($category, $categories);

            return $pos;
        } else {
            return -1;
        }
    }

    /**
     * Returns if questionnaire is closed
     *
     * @return boolean
     * @throws coding_exception
     * @throws dml_exception
     */
    public function is_questionnaire_accessible() {
        $times = $this->get_time();
        $condition = $times ['end_raw'] < time();

        return (!$this->is_questionnaire_available() && $condition);
    }

    /**
     * Returns the total number of answers
     *
     * @return int
     * @throws dml_exception
     */
    public function get_total_number_of_answers() {
        $categories = $this->get_categories();
        $numbers = $this->get_numbers($categories);

        return array_sum($numbers);
    }

    /**
     * Returns whether the email setting is set or not
     *
     * @return number
     * @throws dml_exception
     */
    public function get_email_setting() {
        global $DB;

        return $DB->get_field('groupformation', 'emailnotifications', array('id' => $this->groupformationid));
    }

    /**
     * Returns label set
     *
     * @return array
     * @throws dml_exception
     */
    public function get_label_set($extended = false) {
        $array = null;
        if ($extended) {
            $array = mod_groupformation_data::get_extended_label_set($this->get_scenario(true));
        } else {
            $array = mod_groupformation_data::get_label_set($this->get_scenario(true));
        }
        if ($this->groupformationid != null) {
            $hastopic = $this->get_number('topic');
            $hasknowledge = $this->get_number('knowledge');
            $hasbinquestion = $this->get_number('binquestion');
            $grades = $this->ask_for_grade();
            $points = $this->ask_for_points();
            $position = 0;

            foreach ($array as $c) {
                if (($this->startswith($c,'points') && $points == false) ||
                        ($this->startswith($c,'grade') && $grades == false) ||
                        ($hastopic == 0 && $this->startswith($c,'topic')) ||
                        ($hasknowledge == 0 && $this->startswith($c,'knowledge')) ||
                        ($hasbinquestion == 0 && $this->startswith($c,'binquestion'))
                ) {
                    unset ($array [$position]);
                }

                $position++;
            }
            if ($hastopic != 0) {
                $array = array('topic');
            }
        }

        return $array;
    }

    private function startswith($string, $query) {
        return substr($string, 0, strlen($query)) === $query;
    }

    /**
     * Returns whether 'topic' is a valid category in this instance or not
     *
     * @return boolean
     * @throws dml_exception
     */
    public function ask_for_topics() {
        global $DB;
        $evaluationmethod = $DB->get_field('groupformation', 'topics', array(
                'id' => $this->groupformationid
        ));

        return $evaluationmethod == 1;
    }

    /**
     * Returns whether 'knowledge' is a valid category in this instance or not
     *
     * @return boolean
     * @throws dml_exception
     */
    public function ask_for_knowledge() {
        global $DB;
        $evaluationmethod = $DB->get_field('groupformation', 'knowledge', array(
                'id' => $this->groupformationid
        ));

        return $evaluationmethod == 1;
    }

    /**
     * Returns whether 'binquestion' is a valid category in this instance or not
     *
     * @return boolean
     * @throws dml_exception
     */
    public function ask_for_binquestion() {
        global $DB;
        $evaluationmethod = $DB->get_field('groupformation', 'binquestion', array(
                'id' => $this->groupformationid
        ));

        return $evaluationmethod == 1;
    }

    /**
     * Returns users
     *
     * @return array|null
     * @throws dml_exception
     */
    public function get_users() {
        global $PAGE;
        $courseid = $this->get_course_id();
        $context = context_course::instance($courseid);

        $enrolledstudents = null;

        if (intval($PAGE->cm->groupingid) != 0) {
            $enrolledstudents = array_keys(groups_get_grouping_members($PAGE->cm->groupingid));
        } else {
            $enrolledstudents = array_keys(get_enrolled_users($context, 'mod/groupformation:onlystudent'));
        }

        if (is_null($enrolledstudents) || count($enrolledstudents) <= 0) {
            return array();
        }

        return $enrolledstudents;
    }

    /**
     * Determines group size
     *
     * @param array $users
     * @param null $groupformationid
     * @return array|null
     * @throws dml_exception
     */
    public function determine_group_size($users, $groupformationid = null) {
        if (is_null($users) || count($users) == 0) {
            return array(0, 0);
        }
        if ($this->ask_for_topics()) {
            $groupoption = $this->get_group_option();
            if ($groupoption) {
                $maxgroups = intval($this->get_max_groups());
                $topicvalues = $this->get_knowledge_or_topic_values('topic');
                $topicvalues = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $topicvalues . ' </OPTIONS>';
                $topicsoptions = mod_groupformation_util::xml_to_array($topicvalues);
                $topicscount = count($topicsoptions);

                $userscount0 = count($users [0] + $users [1]);
                $ratio0 = $userscount0 / $maxgroups;

                $basegroupsize = floor($ratio0);

                $covereduserscount = $basegroupsize * $maxgroups;
                $remaininguserscount = $userscount0 - $covereduserscount;

                $usermanager = new mod_groupformation_user_manager ($groupformationid);

                $topics = $usermanager->get_most_common_topics($topicscount);

                $result = array();

                $i = 0;
                foreach ($topics as $key => $topic) {
                    if ($i < $remaininguserscount) {
                        $result [intval($topic ['id']) - 1] = intval(round($basegroupsize + 1));
                    } else {
                        $result [intval($topic ['id']) - 1] = intval(round($basegroupsize));
                    }
                    $i++;
                }

                return $result;
            } else {
                $topicvalues = $this->get_knowledge_or_topic_values('topic');
                $topicvalues = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $topicvalues . ' </OPTIONS>';
                $topicsoptions = mod_groupformation_util::xml_to_array($topicvalues);
                $topicscount = count($topicsoptions);

                $maxmembers = intval($this->get_max_members());
                $userscount0 = count($users [0] + $users [1]);
                $maxmembers = ceil($userscount0 / $topicscount);
                $array = array();
                for ($i = 0; $i < $topicscount; $i = $i + 1) {
                    $array[] = $maxmembers;
                }
                return $array;
            }

            return $sizearray;
        } else {
            $userscount0 = count($users [0]);
            $userscount1 = count($users [1]);
            $userscount = $userscount0 + $userscount1;

            $groupoption = $this->get_group_option();
            if ($groupoption) {
                $maxgroups = intval($this->get_max_groups());

                if ($userscount0 == 0) {
                    return array(
                            null, intval(ceil($userscount1 / $maxgroups)));
                } else {
                    if ($userscount1 == 0) {
                        return array(
                                intval(ceil($userscount0 / $maxgroups)), null);
                    }
                }

                $optimalsize = ceil($userscount / $maxgroups);

                $optimalsize0 = $optimalsize;
                $optimalsize1 = $optimalsize;

                $check0 = false;
                $check1 = false;

                $ratio0 = $userscount0 / $userscount;
                $ratio1 = $userscount1 / $userscount;

                $groupnumber0 = round($ratio0 * $maxgroups);
                $groupnumber1 = round($ratio1 * $maxgroups);

                if ($groupnumber0 + $groupnumber1 > $maxgroups) {
                    if ($userscount0 > $userscount1) {
                        $groupnumber0--;
                    } else {
                        $groupnumber1--;
                    }
                }

                if ($groupnumber0 == 0) {
                    $groupnumber0 = $groupnumber0 + 1;
                    $groupnumber1 = $groupnumber1 - 1;
                } else {
                    if ($groupnumber1 == 0) {
                        $groupnumber0 = $groupnumber0 - 1;
                        $groupnumber1 = $groupnumber1 + 1;
                    } else {
                        if ($maxgroups == 2) {
                            $groupnumber0 = 1;
                            $groupnumber1 = 1;
                        }
                    }
                }

                do {
                    $cond = ($groupnumber0 * $optimalsize0 > $userscount0) || ($optimalsize0 > $userscount0) ||
                            ($userscount0 % $optimalsize0 == 0);
                    if ($cond) {
                        $check0 = true;
                    } else {
                        $optimalsize0++;
                    }
                } while (!$check0);

                do {
                    $cond = ($groupnumber1 * $optimalsize1 > $userscount1) || ($optimalsize1 > $userscount1) ||
                            ($userscount1 % $optimalsize1 == 0);
                    if ($cond) {
                        $check1 = true;
                    } else {
                        $optimalsize1++;
                    }
                } while (!$check1);

                $basegroupsize = $optimalsize0;
                $groupsize1 = $optimalsize1;

                $cond = $maxgroups < (ceil($userscount0 / $basegroupsize) + ceil($userscount1 / $groupsize1));
                if ($cond) {
                    return null;
                }

                return array(
                        $basegroupsize, $groupsize1);
            } else {
                $maxmembers = intval($this->get_max_members());

                return array(
                        $maxmembers, $maxmembers);
            }
        }
    }

    /**
     * Returns questions
     *
     * @param string $category
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_questions($category) {
        global $DB;

        $lang = get_string('language', 'groupformation');

        if ($category == 'binquestion') {
            $temp = $this->get_knowledge_or_topic_values($category);
            $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
            $options = mod_groupformation_util::xml_to_array($xmlcontent);
            $questiontext = $this->get_binquestion_text();
            $question = array();

            $q = new stdClass();
            $q->id = 1;
            $q->category = $category;
            $q->questionid = 1;
            $q->question = $questiontext;
            $q->options = $options;
            // if ($this->get_binquestion_multiselect()){
            // $q->type = 'multiselect';
            // } else {
            $q->type = $category;
            // }

            $question[0] = $q;
            return $question;
        }

        if ($category == 'topic' || $category == 'knowledge') {
            $type = 'range';
            $temp = $this->get_knowledge_or_topic_values($category);
            $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
            $values = mod_groupformation_util::xml_to_array($xmlcontent);
            $createquestion = function(&$v, $key) use ($category) {
                $q = new stdClass();
                $q->id = $key + 1;
                $q->category = $category;
                $q->questionid = $key + 1;
                $q->question = $v;
                $q->options = array(
                        100 => get_string('excellent', 'groupformation'), 0 => get_string('none', 'groupformation'));
                $q->type = $category;
                $v = $q;
            };
            array_walk($values, $createquestion);
            return $values;
        }

        return $DB->get_records('groupformation_questions', array('category' => $category, 'language' => $lang));
    }

    /**
     * Returns questions for a user in randomized order (with user-specific seed)
     *
     * @param string $category
     * @param int $userid
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_questions_randomized_for_user($category, $userid) {
        $questions = array_values($this->get_questions($category));

        if (in_array($category, ['character', 'motivation', 'srl', 'self', 'sellmo', 'team'])) {
            srand($userid);
            usort($questions, function($a, $b) {
                return rand(-1, 1);
            });
        }

        return $questions;
    }

    /**
     * Returns scenario name
     *
     * @return string
     * @throws dml_exception
     */
    public function get_scenario_name() {
        global $DB;
        $scenario = $DB->get_field('groupformation', 'szenario', array(
                'id' => $this->groupformationid
        ));
        $scenarioname = $DB->get_field('groupformation_scenario', 'name', array('assigned_id' => $scenario));
        return $scenarioname;
    }

    /**
     * Returns question by position
     *
     * @param string $category
     * @param number $position
     * @return mixed
     * @throws dml_exception
     */
    public function get_question_by_position($category, $position) {
        global $DB;

        return $DB->get_record('groupformation_questions',
                array('category' => $category, 'language' => 'en', 'position' => $position));

    }

    /**
     * Saves statistics of groupformation cohort
     *
     * @param string $groupkey
     * @param stdClass $cohort
     * @throws dml_exception
     */
    public function save_statistics($groupkey, $cohort) {
        global $DB;

        $record = new stdClass();

        $record->groupformationid = $this->groupformationid;
        $record->group_key = $groupkey;

        $record->matcher_used = strval($cohort->whichmatcherused);
        $record->count_groups = floatval($cohort->countofgroups);

        $stats = $cohort->results;
        
        if (!is_null($stats)) {
            $record->avg_variance = !is_nan($stats->avgvariance) ? $stats->avgvariance : null;
            $record->variance = !is_nan($stats->variance) ? $stats->variance : null; 
            $record->avg = !is_nan($stats->avg) ? $stats->avg : null; 
            $record->st_dev = !is_nan($stats->stddev) ? $stats->stddev : null; 
            $record->norm_st_dev = !is_nan($stats->normstddev) ? $stats->normstddev : null; 
            $record->performance_index = !is_nan($stats->performanceindex) ? $stats->performanceindex : null;
        }

        $DB->insert_record('groupformation_stats', $record);
    }

    /**
     * Deletes all statistics of previous groupformations
     *
     * @throws dml_exception
     */
    public function delete_statistics() {
        global $DB;

        $DB->delete_records('groupformation_stats', array('groupformationid' => $this->groupformationid));
    }

    /**
     * Returns users that are available for group formation
     *
     * @param null $job
     * @return array
     * @throws dml_exception
     */
    public function get_users_for_grouping($job = null) {
        global $DB;

        $ajm = new mod_groupformation_advanced_job_manager();

        if (is_null($job)) {

            $job = $ajm::get_job($this->groupformationid);
        }

        $courseid = $this->get_course_id();
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
            return array(array(), array());
        }

        $groupingsetting = $this->get_grouping_setting();

        $allanswers = array();
        $someanswers = array();
        $noorsomeanswers = array();

        // Has_answered_everything.
        $categories = $this->get_categories();
        $sum = array_sum($this->get_numbers($categories));

        // Get userids of groupformation answers.
        $userids = $DB->get_fieldset_select('groupformation_answers', 'userid', 'groupformation = ?',
                array($this->groupformationid));

        // Returns an array using the userids as keys and their frequency in answers as values.
        $userfrequencies = array_count_values($userids);

        $numberofanswers = function($userid) use ($sum, $userfrequencies) {
            return array_key_exists($userid, $userfrequencies) ? $userfrequencies[$userid] : 0;
        };

        foreach (array_values($enrolledstudents) as $userid) {
            if ($sum <= $numberofanswers($userid) && !$this->is_filtered($userid)) {
                $allanswers [] = $userid;
            } else if ($groupingsetting && $numberofanswers($userid) > 0) {
                $someanswers [] = $userid;
            } else {
                $noorsomeanswers [] = $userid;
            }
        }

        $groupalusers = $allanswers;

        if ($groupingsetting) {
            $randomusers = $someanswers;
        } else {
            $randomusers = $noorsomeanswers;
        }

        return array(
                $groupalusers, $randomusers);

    }

    /**
     * Returns whether groupformation uses filtering due to dishonesty
     *
     * @return bool
     * @throws dml_exception
     */
    public function uses_filter() {
        global $DB;

        return ($DB->count_records('groupformation_users',
                        array('groupformation' => $this->groupformationid, 'filtered' => 1))) > 0;
    }

    /**
     * Returns whether a students will be filtered due to dishonesty
     *
     * @param int $userid
     * @return bool
     * @throws dml_exception
     */
    public function is_filtered($userid) {
        global $DB;

        return boolval($DB->get_field('groupformation_users', 'filtered',
                array('groupformation' => $this->groupformationid, 'userid' => $userid)));
    }

    /**
     * Returns stats about dishonesty
     *
     * @return array
     * @throws dml_exception
     */
    public function get_honesty_stats() {
        global $DB;

        $yes = $DB->count_records('groupformation_answers',
                array('groupformation' => $this->groupformationid, 'category' => 'honesty', 'answer' => 1));
        $maybe = $DB->count_records('groupformation_answers',
                array('groupformation' => $this->groupformationid, 'category' => 'honesty', 'answer' => 2));
        $no = $DB->count_records('groupformation_answers',
                array('groupformation' => $this->groupformationid, 'category' => 'honesty', 'answer' => 3));
        return ['yes' => $yes + $maybe, 'no' => $no];
    }

    /**
     * Filters users based on value
     *
     * @param number $value
     * @throws dml_exception
     */
    public function filter_users($value) {
        global $DB;

        $users = $DB->get_records('groupformation_answers',
                array('groupformation' => $this->groupformationid, 'category' => 'honesty', 'answer' => 3),
                'userid',
                'userid'
        );

        foreach (array_keys($users) as $userid) {
            $record = $DB->get_record('groupformation_users',
                    array('groupformation' => $this->groupformationid, 'userid' => $userid));
            $record->filtered = intval($value);

            $DB->update_record('groupformation_users', $record);
        }

    }

    /**
     * Returns DB entry for groupformation instance
     *
     * @param int $groupformationid
     * @return mixed
     * @throws dml_exception
     */
    public function get_instance($groupformationid) {
        global $DB;

        return $DB->get_record('groupformation', array('id' => $groupformationid));
    }

    /**
     * Returns all DB entries for groupformation instances in which a user is involved
     *
     * @param int $userid
     * @return array
     * @throws dml_exception
     */
    public function get_all_instances_with_user($userid) {
        global $DB;
        $ids = array();
        $records = $DB->get_records('groupformation_users', array('userid' => $userid));
        foreach ($records as $record) {
            $ids[] = $record->groupformation;
        }

        $instances = $DB->get_records_list('groupformation', 'id', $ids);

        return $instances;
    }

    /**
     * Returns activity state
     *
     * @param bool $internal
     * @return mixed
     * @throws dml_exception
     */
    public function get_state($internal = false) {
        return $this->statemachine->get_state($internal);
    }

    /**
     * Returns weights of criteria
     *
     * @return mixed
     */
    public function get_weights() {
        global $DB;
        return $DB->get_record('groupformation', array(
                'id' => $this->groupformationid));

    }
}