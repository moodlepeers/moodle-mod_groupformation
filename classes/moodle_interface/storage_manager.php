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
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');

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

    public function get_version() {
        global $DB;

        return $DB->get_field('groupformation','version',array('id'=>$this->groupformationid));
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

        // $count = $DB->count_records('groupformation_' . $category);
        // TODO
        $count = $DB->count_records('groupformation_question', array('category' => $category));

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
        $data->topicvalues = groupformation_convert_options($topics);
        $data->knowledgevalues = groupformation_convert_options($knowledge);
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
    public function convert_options($options) {
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

        $table = 'groupformation_question';

        $lang = $DB->get_field($table, 'language', array('category' => $category), IGNORE_MULTIPLE);

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
            return intval($DB->get_field('groupformation_q_settings', $category . 'valuesnumber', array(
                'groupformation' => $this->groupformationid
            )));
        } else {
            return intval($DB->get_field('groupformation_q_version', 'numberofquestion', array(
                'category' => $category
            )));
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

        // $table = 'groupformation_' . $category;
        // TODO
        $table = 'groupformation_question';
        return $DB->get_field($table, 'optionmax', array(
            'language' => 'en', 'category' => $category,
            'questionid' => $i
        ));

        //return $DB->get_field($table, 'optionmax', array(
        //    'language' => 'en',
        //    'questionid' => $i
        //));
    }

    /**
     * Returns a specific question in a specific category
     *
     * @param unknown $i
     * @param string $category
     * @param string $lang
     * @return mixed
     */
    public function get_catalog_question($i, $category = 'general', $lang = 'en', $version = null) {
        global $DB;

        $table = 'groupformation_question';
        $return = $DB->get_record($table, array(
            'language' => $lang, 'category' => $category,
            'position' => $i, 'version' => $version
        ));

        return $return;
    }

    /**
     * Returns version of requested category stored in DB
     *
     * @param $category
     * @return mixed
     */
    public function get_catalog_version($category) {
        global $DB;

        $table = "groupformation_q_version";

        return $DB->get_field($table, 'version', array('category' => $category));
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

        if (!$this->is_math_prep_course_mode()) {
            $categories[] = 'general';
        }

        foreach ($categoryset as $category) {
            if ($this->get_number($category) > 0) {
                if ($category == 'grade' && $this->ask_for_grade()) {
                    $categories [] = $category;
                } else if ($category == 'points' && $this->ask_for_points()) {
                    $categories [] = $category;
                } else if ($category != 'grade' && $category != 'points' && $category != 'general') {
                    $categories [] = $category;
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

        if (is_null($this->groupformationid) || $this->groupformationid == ''){
            return true;
        }

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

        return (bool)($DB->get_field('groupformation', 'groupoption', array(
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
     * Returns if questionnaire is closed
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
                    ($hasknowledge == 0 && ('knowledge_heterogen' == $c || 'knowledge_homogen' == $c))
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

    /**
     * Returns users
     *
     * @return array|null
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
     * Returns whether a participant code is wanted or not
     *
     * @return bool
     */
    public function ask_for_participant_code() {
        return $this->data->ask_for_participant_code();
    }

    /**
     * Returns whether current mode is math prep course mode
     *
     * @return bool
     */
    public function is_math_prep_course_mode() {
        return $this->data->is_math_prep_course_mode();
    }

    /**
     * Determines group size
     *
     * @param $users
     * @param null $groupformationid
     * @return array|null
     */
    public function determine_group_size($users, $groupformationid = null) {
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
                foreach (array_values($topics) as $topic) {
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

                $userscount0 = count($users [0] + $users [1]);
                $maxmembers = ceil($userscount0 / $topicscount);
                $array = array();
                for ($i = 0; $i < $topicscount; $i = $i + 1) {
                    $array[] = $maxmembers;
                }
                return $array;
            }
        } else {

            $userscount0 = count($users [0]);
            $userscount1 = count($users [1]);
            $userscount = $userscount0 + $userscount1;

            if ($userscount <= 0) {
                return null;
            }
            $groupoption = $this->get_group_option();
            if ($groupoption) {
                $maxgroups = intval($this->get_max_groups());

                if ($userscount0 == 0) {
                    return array(
                        null, intval(ceil($userscount1 / $maxgroups)));
                } else if ($userscount1 == 0) {
                    return array(
                        intval(ceil($userscount0 / $maxgroups)), null);
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
                } else if ($groupnumber1 == 0) {
                    $groupnumber0 = $groupnumber0 - 1;
                    $groupnumber1 = $groupnumber1 + 1;
                } else if ($maxgroups == 2) {
                    $groupnumber0 = 1;
                    $groupnumber1 = 1;
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
     * @param $category
     * @param $version
     * @return array
     */
    public function get_questions($category){
        global $DB;

        return $DB->get_records('groupformation_question',array('category'=>$category,'language'=>'en'));
    }

    /**
     * Returns questions for a user in randomized order (with user-specific seed)
     *
     * @param $category
     * @param $userid
     * @return array
     */
    public function get_questions_randomized_for_user($category,$userid){
        $questions = array_values($this->get_questions($category));

        srand($userid);
        usort($questions,function($a,$b){return rand(-1,1);});

        return $questions;
    }

    /**
     * Returns question by position
     */
    public function get_question_by_position($category,$position){
        global $DB;

        return $DB->get_record('groupformation_question',array('category'=>$category,'language'=>'en','position'=>$position));

    }
}