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
 * Interface betweeen DB and Plugin
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/group/lib.php');

class mod_groupformation_storage_manager {
    private $groupformationid;
    private $data;
    private $gm;

    /**
     * Constructs storage manager for a specific groupformation
     *
     * @param int $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->data = new mod_groupformation_data ();
        $this->gm = new mod_groupformation_groups_manager ($groupformationid);
    }

    /**
     * Returns whether the activity is archived
     *
     * @return bool
     */
    public function is_archived() {
        global $DB;
        $record = $DB->get_record('groupformation_q_settings', array(
            'groupformation' => $this->groupformationid
        ));

        return $record->archived == 1;
    }

    /**
     * Returns whether the activity is accessible
     *
     * @param $userid
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
     */
    public function catalog_table_not_set($category = 'grade') {
        global $DB;

        $count = $DB->count_records('groupformation_' . $category);

        return $count == 0;
    }

    /**
     * Returns course id
     *
     * @return mixed
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
     * Deletes all questions in a specific category
     *
     * @param string $category
     */
    public function delete_all_catalog_questions($category) {
        global $DB;
        $DB->delete_records('groupformation_' . $category);
    }

    /**
     * Adds a catalog question in a specific language and category
     *
     * @param array $question
     * @param string $language
     * @param string $category
     */
    public function add_catalog_question($question, $language, $category) {
        global $DB;

        $data = new stdClass ();

        $data->type = $question ['type'];
        $data->question = $question ['question'];
        $data->options = $this->convert_options($question ['options']);
        $data->position = $question ['position'];
        $data->questionid = $question ['questionid'];
        $data->language = $language;
        $data->optionmax = count($question ['options']);
        $DB->insert_record('groupformation_' . $category, $data);
    }

    /**
     * Add new question from XML to DB
     *
     * @param string $category
     * @param int $numbers
     * @param unknown $version
     * @param boolean $init
     */
    public function add_catalog_version($category, $numbers, $version, $init) {
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
     * Determines whether the DB contains for a specific category a specific version or not
     *
     * @param string $category
     * @param string $version
     * @return boolean
     */
    public function latest_version($category, $version) {
        global $DB;

        $count = $DB->count_records('groupformation_q_version', array(
            'category' => $category,
            'version' => $version
        ));

        return $count == 1;
    }

    /**
     * Adds/Updates knowledge and topic setting of groupformation
     *
     * @param unknown $knowledge
     * @param unknown $topics
     * @param unknown $init
     */
    public function add_setting_question($knowledge, $topics, $init) {
        global $DB;

        $data = new stdClass ();
        $data->groupformation = $this->groupformationid;
        $data->topicvalues = $this->convert_options($topics);
        $data->knowledgevalues = $this->convert_options($knowledge);
        $data->topicvaluesnumber = count($topics);
        $data->knowledgevaluesnumber = count($knowledge);

        if ($init) {
            $DB->insert_record('groupformation_q_settings', $data);
        } else if ($DB->count_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid
            )) == 0
        ) {
            $data->id = $DB->get_field('groupformation_q_settings', 'id', array(
                'groupformation' => $this->groupformationid
            ));
            $DB->update_record('groupformation_q_settings', $data);
        }
    }

    /**
     * Returns map with availability times (xxx_raw is timestamp, xxx is formatted time for display)
     *
     * @return multitype:string NULL mixed
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
        } else if ('de' == get_string("language", "groupformation")) {
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

        return $times;
    }

    /**
     * Converts knowledge or topic array into XML-based syntax
     *
     * @param unknown $options
     * @return string
     */
    private function convert_options($options) {
        $op = implode("</OPTION>  <OPTION>", $options);

        return "<OPTION>" . $op . "</OPTION>";
    }

    /**
     * Returns an array with number of questions in each category
     *
     * @param $categories
     * @return array
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
     * @param unknown $category
     * @return mixed
     */
    public function get_possible_language($category) {
        global $DB;

        $table = 'groupformation_' . $category;
        $lang = $DB->get_field($table, 'language', array(), IGNORE_MULTIPLE);

        return $lang;
    }

    /**
     * Returns the number of questions in a specified category
     *
     * @param string $category
     * @return mixed
     */
    public function get_number($category = null) {
        global $DB;

        if ($category == 'topic' || $category == 'knowledge') {
            return $DB->get_field('groupformation_q_settings', $category . 'valuesnumber', array(
                'groupformation' => $this->groupformationid
            ));
        } else {
            return $DB->get_field('groupformation_q_version', 'numberofquestion', array(
                'category' => $category
            ));
        }
    }

    /**
     * Returns either knowledge or topic values
     *
     * @param unknown $category
     * @return mixed
     */
    public function get_knowledge_or_topic_values($category) {
        global $DB;

        return $DB->get_field('groupformation_q_settings', $category . 'values', array(
            'groupformation' => $this->groupformationid
        ));
    }

    /**
     * Returns max number of options for a specific question in a specific category
     *
     * @param unknown $i
     * @param string $category
     * @return int
     */
    public function get_max_option_of_catalog_question($i, $category = 'grade') {
        global $DB;
        if ($category == 'points') {
            return $this->get_max_points();
        }
        $table = "groupformation_" . $category;

        return $DB->get_field($table, 'optionmax', array(
            'language' => 'en',
            'questionid' => $i
        ));
    }

    /**
     * Returns a specific question in a specific category
     *
     * @param unknown $i
     * @param string $category
     * @param string $lang
     * @return mixed
     */
    public function get_catalog_question($i, $category = 'general', $lang = 'en') {
        global $DB;
        $table = "groupformation_" . $category;
        $return = $DB->get_record($table, array(
            'language' => $lang,
            'questionid' => $i
        ));

        return $return;
    }

    /**
     * Returns the scenario
     *
     * @param bool|false $name
     * @return string
     */
    public function get_scenario($name = false) {
        global $DB;

        $settings = $DB->get_record('groupformation', array(
            'id' => $this->groupformationid
        ));

        if ($name) {
            return $this->data->get_scenario_name($settings->szenario);
        }

        return $settings->szenario;
    }

    /**
     * Returns logging data
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
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
     */
    public function get_raw_categories() {
        return $this->data->get_category_set($this->get_scenario());
    }

    /**
     * Returns categories with at least one question, not just the scenario-based category set
     *
     * @return array
     */
    public function get_categories() {
        $categoryset = $this->data->get_category_set($this->get_scenario());
        $categories = array();

        $hasknowledge = $this->get_number('knowledge');
        if ($this->ask_for_knowledge() && $hasknowledge != 0) {
            $categories[] = 'knowledge';
        }

        foreach ($categoryset as $category) {
            if ($this->get_number($category) > 0) {
                if ($category == 'grade' && $this->ask_for_grade()) {
                    $categories [] = $category;
                } else if ($category == 'points' && $this->ask_for_points()) {
                    $categories [] = $category;
                } else if ($category != 'grade' && $category != 'points') {
                    $categories [] = $category;
                }
            }
        }

        $hastopic = $this->get_number('topic');
        if ($this->ask_for_topics() && $hastopic != 0) {
            $categories = arraY('topic');
        }

        return $categories;
    }

    /**
     * Returns all exportable categories
     *
     * @return array
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
     * Gets previous category
     *
     * @param string $category
     * @return string
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
     */
    public function is_editable() {
        global $DB;

        return ($DB->count_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid
            )) == 0);
    }

    /**
     * Returns all answers to a specific question in a specific category
     *
     * @param string $category
     * @param int $questionid
     * @return array
     */
    public function get_answers_to_special_question($category, $questionid) {
        global $DB;

        return $DB->get_records('groupformation_answer', array(
            'groupformation' => $this->groupformationid,
            'category' => $category,
            'questionid' => $questionid
        ));
    }

    /**
     * Returns maximum number of points
     *
     * @return mixed
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
     */
    public function is_questionnaire_available() {
        $now = time();

        $time = $this->get_time();

        $start = intval($time ['start_raw']);
        $end = intval($time ['end_raw']);

        if (($start == 0) && ($end == 0)) {
            return true;
        } else if (($start == 0) && ($now <= $end)) {
            return true;
        } else if (($now >= $start) && ($end == 0)) {
            return true;
        } else if (($now >= $start) && ($now <= $end)) {
            return true;
        }

        return false;
    }

    /**
     * Sets timestamp in groupformation in order to close/terminate questionaire
     */
    public function close_questionnaire() {
        global $DB;

        $data = new stdClass ();
        $data->id = $this->groupformationid;
        $data->timeclose = time() - 1;

        $DB->update_record('groupformation', $data);
    }

    /**
     * Sets timestamp in groupformation in order to open/begin questionaire
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
     */
    public function get_group_option() {
        global $DB;

        return boolval($DB->get_field('groupformation', 'groupoption', array(
            'id' => $this->groupformationid
        )));
    }

    /**
     * Returns group name prefix
     *
     * @return mixed
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
     * Returns if questionaire is closed
     *
     * @return boolean
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
     */
    public function get_email_setting() {
        global $DB;

        return $DB->get_field('groupformation', 'emailnotifications', array('id' => $this->groupformationid));
    }

    /**
     * Returns label set
     *
     * @return array
     */
    public function get_label_set() {
        $array = $this->data->get_label_set($this->get_scenario());

        if ($this->groupformationid != null) {
            $hastopic = $this->get_number('topic');
            $hasknowledge = $this->get_number('knowledge');
            $grades = $this->ask_for_grade();
            $points = $this->ask_for_points();
            $position = 0;
            foreach ($array as $c) {
                if (('points' == $c && $points == false) ||
                    ('grade' == $c && $grades == false) ||
                    ($hastopic == 0 && 'topic' == $c) ||
                    ($hasknowledge == 0 && ('knowledge_heterogen' == $c || 'knowledge_homogen' == $c))) {
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

    /**
     * Returns whether 'topic' is a valid category in this instance or not
     * @return boolean
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
     * @return boolean
     */
    public function ask_for_knowledge() {
        global $DB;
        $evaluationmethod = $DB->get_field('groupformation', 'knowledge', array(
            'id' => $this->groupformationid
        ));

        return $evaluationmethod == 1;
    }

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
}