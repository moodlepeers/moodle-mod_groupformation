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
 * Interface for user-related activity data in DB
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class mod_groupformation_user_manager
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_user_manager {

    /** @var int ID of module instance */
    private $groupformationid;

    /** @var mod_groupformation_storage_manager */
    private $store;

    /**
     * Creates instance
     *
     * @param string $groupformationid
     */
    public function __construct($groupformationid = null) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
    }

    /**
     * Returns array of records of table groupformation_users where completed is 1
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     * @throws dml_exception
     */
    public function get_completed($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'completed' => 1
        ), $sortedby, $fieldset);
    }

    /**
     * Returns users started table content
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     * @throws dml_exception
     */
    public function get_users_started($sortedby = null, $fieldset = '*') {
        global $DB;
        return $DB->get_records('groupformation_users', array(
                'groupformation' => $this->groupformationid,
        ), $sortedby, $fieldset);
    }

    /**
     * Returns array of records of table groupformation_users where completed is 0
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     * @throws dml_exception
     */
    public function get_not_completed($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'completed' => 0
        ), $sortedby, $fieldset);
    }

    /**
     * Returns array of records of table groupformation_users
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     * @throws dml_exception
     */
    public function get_started($sortedby = null, $fieldset = '*') {
        global $DB;
        $started = array();
        $recs = $DB->get_records('groupformation_users', array(
                'groupformation' => $this->groupformationid
        ), $sortedby, $fieldset);
        foreach ($recs as $rec) {
            if ($rec->answer_count != 0) {
                $started[] = $rec;
            }
        }
        return $started;
    }

    /**
     * Returns record of groupformation_users instance
     * @param int $userid
     * @return mixed
     * @throws dml_exception
     */
    public function get_instance($userid) {
        global $DB;

        return $DB->get_record('groupformation_users', array(
                'groupformation' => $this->groupformationid, 'userid' => $userid));
    }

    /**
     * Returns array of records of table_groupformation_users if answer_count is equal to
     * the total answer count for this activity
     *
     * @param string $sortedby
     * @param string $fieldset
     * @return array :unknown
     * @throws dml_exception
     */
    public function get_completed_by_answer_count($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'answer_count' => $this->store->get_total_number_of_answers()
        ), $sortedby, $fieldset);
    }

    /**
     * Returns array of records of table_groupformation_users if answer_count is not equal to
     * the total answer count for this activity
     *
     * @param string $sortedby
     * @param string $fieldset
     * @return array :unknown
     * @throws dml_exception
     */
    public function get_not_completed_by_answer_count($sortedby = null, $fieldset = '*') {
        global $DB;
        $tablename = 'groupformation_users';
        $query = "SELECT " . $fieldset . " FROM {{$tablename}} " .
                "WHERE groupformation = ? AND answer_count <> ? ORDER BY ?" . $sortedby;
        return $DB->get_records_sql($query, array(
                $this->groupformationid,
                $this->store->get_total_number_of_answers(),
                $sortedby
        ));
    }

    /**
     * Sets answer counter for user
     *
     * @param int $userid
     * @throws dml_exception
     */
    public function set_answer_count($userid) {
        global $DB;
        if ($record = $DB->get_record('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
        ))
        ) {
            $record->answer_count = $DB->count_records('groupformation_answers', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid
            ));
            $DB->update_record('groupformation_users', $record);
        } else {
            $this->change_status($userid);
            $record = $DB->get_record('groupformation_users', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid
            ));
            $record->answer_count = $DB->count_records('groupformation_answers', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid
            ));
            $DB->update_record('groupformation_users', $record);
        }
    }

    /**
     * Initializes record
     *
     * @param $userid
     * @throws dml_exception
     */
    public function init($userid) {
        global $DB;
        if ($DB->count_records('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
        )) == 0
        ) {
            $data = new stdClass ();
            $data->groupformation = $this->groupformationid;
            $data->userid = $userid;
            $DB->insert_record('groupformation_users', $data);
        }
    }

    /**
     * set status to complete
     *
     * @param int $userid
     * @param bool|false $completed
     * @throws dml_exception
     */
    public function set_status($userid, $completed = false) {
        global $DB;
        if ($data = $DB->get_record('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
        ))
        ) {
            $data = $DB->get_record('groupformation_users', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid
            ));
            $data->completed = $completed;
            $data->timecompleted = time();
            $DB->update_record('groupformation_users', $data);
        } else {
            $this->init($userid);
            $this->set_status($userid, $completed);
        }
    }

    /**
     * Changes status of questionnaire for a specific user
     *
     * @param int $userid
     * @param bool|false $complete
     * @throws dml_exception
     */
    public function change_status($userid, $complete = false) {
        $status = 0;
        if (!$complete) {
            $status = $this->get_answering_status($userid);
        }

        if ($status == -1) {
            $this->set_status($userid, false);
        }

        if ($status == 0) {
            $this->set_status($userid, true);
        }
    }

    /**
     * Returns answering status for user
     * 0 seen
     * 1 completed
     * -1 otherwise
     *
     * @param int $userid
     * @return int|mixed
     * @throws dml_exception
     */
    public function get_answering_status($userid) {
        global $DB;

        $askforparticipantcode = mod_groupformation_data::ask_for_participant_code();

        if (!$this->get_consent($userid) || (!$this->has_participant_code($userid) && $askforparticipantcode)) {
            return -1;
        }

        $exists = $DB->record_exists('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
        ));
        if ($exists) {
            $value = $DB->get_field('groupformation_users', 'completed', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid
            ));
            return $value;
        } else {
            return -1;
        }
    }

    /**
     * Returns whether questionnaire was completed and send by user or not
     *
     * @param int $userid
     * @return boolean
     * @throws dml_exception
     */
    public function is_completed($userid) {
        global $DB;

        return $DB->get_field('groupformation_users', 'completed', array('groupformation' => $this->groupformationid, 'userid' => $userid));
    }

    /**
     * Determines the number of answered questions of a user (in all categories or a specified category)
     *
     * @param int $userid
     * @param string $category
     * @return number
     * @throws dml_exception
     */
    public function get_number_of_answers($userid, $category = null) {
        global $DB;

        if (!is_null($category)) {
            return $DB->count_records('groupformation_answers', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid,
                    'category' => $category
            ));
        }

        return $DB->count_records('groupformation_answers', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
        ));
    }

    /**
     * Determines whether user has answered every question or not
     *
     * @param int $userid
     * @return boolean
     * @throws dml_exception
     */
    public function has_answered_everything($userid) {
        $store = new mod_groupformation_storage_manager ($this->groupformationid);

        $categories = $store->get_categories();
        $sum = array_sum($store->get_numbers($categories));

        $usersum = $this->get_number_of_answers($userid);

        return $sum <= $usersum;
    }

    /**
     * Determines if someone already answered at least one question
     *
     * @param null $userid
     * @param null $categories
     * @return bool
     * @throws dml_exception
     */
    public function already_answered($userid = null, $categories = null) {
        global $DB;
        if (is_null($categories) && is_null($userid)) {
            return !($DB->count_records('groupformation_answers', array(
                            'groupformation' => $this->groupformationid
                    )) == 0);
        } else {
            if (is_null($categories) && !is_null($userid)) {
                return !($DB->count_records('groupformation_answers', array(
                                'groupformation' => $this->groupformationid,
                                'userid' => $userid
                        )) == 0);
            } else {
                foreach ($categories as $category) {
                    $answers = $this->get_answers($userid, $category);
                    if (count($answers) > 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
    //TODO entfernen
    public function binanswers_help($userid, $category, $sortedby = null, $fieldset = '*'){
        global $DB;
        return $DB->get_records('groupformation_answers', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid,
            'category' => $category
        ), $sortedby, $fieldset);
    }

    /**
     * Returns all answers of a specific user in a specific category
     *
     * @param int $userid
     * @param string $category
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     * @throws dml_exception
     */
    public function get_answers($userid, $category, $sortedby = null, $fieldset = '*') {
        global $DB;
        if (is_null($userid) && is_null($category)) {
            return $DB->get_records('groupformation_answers', array(
                    'groupformation' => $this->groupformationid,
            ), $sortedby, $fieldset);
        } else {
            if (is_null($userid)) {

                return $DB->get_records('groupformation_answers', array(
                        'groupformation' => $this->groupformationid,
                        'category' => $category
                ), $sortedby, $fieldset);
            } else {
                if (is_null($category)) {

                    return $DB->get_records('groupformation_answers', array(
                            'groupformation' => $this->groupformationid,
                            'userid' => $userid
                    ), $sortedby, $fieldset);
                } else {
                    return $DB->get_records('groupformation_answers', array(
                            'groupformation' => $this->groupformationid,
                            'userid' => $userid,
                            'category' => $category
                    ), $sortedby, $fieldset);
                }
            }
        }
    }

    /**
     * Returns answer of a specific user to a specific question in a specific category
     *
     * @param int $userid
     * @param string $category
     * @param int $questionid
     * @return int
     * @throws dml_exception
     */
    public function get_single_answer($userid, $category, $questionid) {
        global $DB;

        return $DB->get_field('groupformation_answers', 'answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => strval($userid),
                'category' => strval($category),
                'questionid' => strval($questionid)
        ));
    }

    /**
     * Delete answer
     *
     * @param int $userid
     * @param string $category
     * @param int $questionid
     * @throws dml_exception
     */
    public function delete_answer($userid, $category, $questionid) {
        global $DB;

        $DB->delete_records('groupformation_answers',
                array('groupformation' => $this->groupformationid,
                        'category' => $category,
                        'userid' => $userid,
                        'questionid' => $questionid
                )
        );
        $this->set_answer_count($userid);
    }

    /**
     * Saves answer
     *
     * @param int $userid
     * @param string $category
     * @param number $answer
     * @param int $questionid
     * @throws dml_exception
     */
    public function save_answer($userid, $category, $answer, $questionid) {
        global $DB;
        $status = $this->get_answering_status($userid);

        if ($record = $DB->get_record('groupformation_answers', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid,
                'category' => $category,
                'questionid' => $questionid
        ))) {

            $record->answer = $answer;
            $DB->update_record('groupformation_answers', $record);
        } else {
            $record = new stdClass ();
            $record->groupformation = $this->groupformationid;

            $record->userid = $userid;
            $record->category = $category;
            $record->questionid = $questionid;
            $record->answer = $answer;
            $record->timestamp = time();
            $DB->insert_record('groupformation_answers', $record);
        }

        if ($status == -1) {
            $this->change_status($userid);
        }
        $this->set_answer_count($userid);

    }

    /**
     * Returns whether answer of a specific user for a specific question in a specific category exists or not
     *
     * @param int $userid
     * @param string $category
     * @param int $questionid
     * @return boolean
     * @throws dml_exception
     */
    public function has_answer($userid, $category, $questionid) {
        global $DB;

        return $DB->record_exists('groupformation_answers', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid,
                'category' => $category,
                'questionid' => $questionid
        ));;
    }

    /**
     * Returns whether a user has answers in a specific category or not
     *
     * @param int $userid
     * @param string $category
     * @return bool
     * @throws dml_exception
     */
    public function has_answers($userid, $category) {
        $firstcondition = ($this->get_answering_status($userid) > -1);
        $secondcondition = (count($this->get_answers($userid, $category)) > 0);

        return ($firstcondition && $secondcondition);
    }

    /**
     * Returns the most chosen topics wrt the users
     *
     * @param number $numberoftopics
     * @return array
     * @throws dml_exception
     */
    public function get_most_common_topics($numberoftopics) {
        global $DB;
        $scores = [];
        for ($i = 1; $i <= $numberoftopics; $i++) {
            $answers = $DB->get_records('groupformation_answers',
                    array('groupformation' => $this->groupformationid, 'category' => 'topic', 'questionid' => $i));
            $score = 0;
            foreach ($answers as $answer) {
                $score += $answer->answer;
            }
            $scores[] = array('id' => strval($i), 'score' => $score);
        }
        usort($scores, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        $result = array_slice($scores, 0, $numberoftopics);

        return $result;
    }

    /**
     * Returns consent value for user
     *
     * @param int $userid
     * @return bool
     * @throws dml_exception
     */
    public function get_consent($userid) {
        global $DB;
        if ($DB->record_exists('groupformation_users',
                array('groupformation' => $this->groupformationid, 'userid' => $userid))
        ) {
            return (bool) ($DB->get_field('groupformation_users', 'consent',
                    array('groupformation' => $this->groupformationid, 'userid' => $userid)));
        } else {
            return false;
        }
    }

    /**
     * Sets consent by value
     *
     * @param int $userid
     * @param bool $value
     * @throws dml_exception
     */
    public function set_consent($userid, $value) {
        global $DB;
        $this->set_status($userid);
        $record = $DB->get_record('groupformation_users',
                array('groupformation' => $this->groupformationid, 'userid' => $userid));
        $record->consent = $value;
        $DB->update_record('groupformation_users', $record);
    }

    /**
     * Deletes all answers
     *
     * @param int $userid
     * @throws dml_exception
     */
    public function delete_answers($userid) {
        global $DB;
        $DB->delete_records('groupformation_users', array('groupformation' => $this->groupformationid, 'userid' => $userid));
        $DB->delete_records('groupformation_answers', array('groupformation' => $this->groupformationid, 'userid' => $userid));
        $DB->delete_records('groupformation_user_values',
                array('groupformationid' => $this->groupformationid, 'userid' => $userid));
    }

    /**
     * Returns whether participant code is correctly computed or not
     *
     * @param string $participantcode
     * @return bool
     */
    public function validate_participant_code($participantcode) {
        if (strlen($participantcode) !== 6) {
            return false;
        }
        $array = array(true, true, false);
        $valid = true;
        $i = 0;
        $d = 2;
        foreach ($array as $a) {
            if ($a) {
                $valid &= ctype_alpha(substr($participantcode, $i, $d));
            } else {
                $valid &= ctype_digit(substr($participantcode, $i, $d));
            }
            $i += 2;
        }
        return $valid;
    }

    /**
     * Returns whether the user has a valid participant code or not
     *
     * @param int $userid
     * @return bool
     * @throws dml_exception
     */
    public function has_participant_code($userid) {
        global $DB;
        $exists = $DB->record_exists('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
        ));
        if ($exists) {
            $value = $DB->get_field('groupformation_users', 'participantcode', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid
            ));
            return $this->validate_participant_code($value);
        }
        return false;
    }

    /**
     * Registers participant code for user
     *
     * @param int $userid
     * @param string $participantcode
     * @throws dml_exception
     */
    public function register_participant_code($userid, $participantcode) {
        global $DB;
        $this->set_status($userid);
        $record = $DB->get_record('groupformation_users',
                array('groupformation' => $this->groupformationid, 'userid' => $userid));
        $record->participantcode = strtoupper($participantcode);
        $DB->update_record('groupformation_users', $record);
    }

    /**
     * Returns participant code
     *
     * @param int $userid
     * @return mixed|string
     * @throws dml_exception
     */
    public function get_participant_code($userid) {
        global $DB;
        $exists = $DB->record_exists('groupformation_users', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
        ));
        if ($exists) {
            $value = $DB->get_field('groupformation_users', 'participantcode', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid
            ));
            return $value;
        }
        return '';
    }

    /**
     * Sets all values for a user.
     *
     * @param int $userid
     * @throws coding_exception
     * @throws dml_exception
     */
    public function set_evaluation_values($userid) {
        global $DB;
        $DB->delete_records('groupformation_user_values',
                array('groupformationid' => $this->groupformationid, 'userid' => $userid)
        );
        $cc = new mod_groupformation_criterion_calculator($this->groupformationid);
        $criteria = $this->store->get_label_set();
        $records = array();
        foreach ($criteria as $criterion) {
            $labels = mod_groupformation_data::get_criterion_specification($criterion);
            if (!is_null($labels) && count($labels) > 0) {
                $uservalues = $cc->get_values_for_user($criterion, $userid, $labels);
                foreach ($uservalues as $label => $values) {
                    $values = $values['values'];
                    foreach ($values as $dimension => $value) {
                        if ($criterion == 'binquestion'){
                            $record = new stdClass();
                            $record->groupformationid = $this->groupformationid;
                            $record->userid = $userid;
                            $record->criterion = $criterion;
                            $record->label = $label;
                            $record->dimension = $dimension;
                            $record->value = $value['importance'];
                            $record->binvalue = $value['binvalue'];
                            $records[] = $record;
                        } else {
                            $record = new stdClass();
                            $record->groupformationid = $this->groupformationid;
                            $record->userid = $userid;
                            $record->criterion = $criterion;
                            $record->label = $label;
                            $record->dimension = $dimension;
                            $record->value = $value;
                            $record->binvalue = '';
                            $records[] = $record;
                        }
                    }
                }
            }
        }
        $DB->insert_records('groupformation_user_values', $records);
    }

    /**
     * Handles complete questionnaires (userids) and sets them to completed/commited.
     */
    public function handle_complete_questionnaires() {
        $users = array_keys($this->get_completed_by_answer_count(null, 'userid'));
        foreach ($users as $user) {
            $this->set_status($user, true);
        }
        return $users;
    }

    /**
     * Returns whether the count of user_values is larger than zero to express that a user has evaluation values already
     *
     * @param int $userid
     * @return bool
     * @throws dml_exception
     */
    public function has_evaluation_values($userid) {
        global $DB;
        return 0 < $DB->count_records('groupformation_user_values',
                        array('groupformationid' => $this->groupformationid, 'userid' => $userid)
                );
    }

    /**
     * Returns (linearized) eval score for user
     *
     * @param int $userid
     * @param null $specs
     * @return float
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_eval_score($userid, $specs = null) {
        global $DB;

        if (!$this->has_evaluation_values($userid)) {
            $this->set_evaluation_values($userid);
        }

        $records = $DB->get_records('groupformation_user_values', array('userid' => $userid));

        $product = 1.0;

        foreach ($records as $record) {
            if (!is_null($specs)) {
                if (array_key_exists($record->criterion, $specs) &&
                        array_key_exists($record->label, $specs[$record->criterion]['labels'])) {
                    $product *= ($record->value + 1);
                }
            } else {
                $product *= ($record->value + 1);
            }
        }

        return $product;
    }

    /**
     * Sets completed status for user
     *
     * @param int $userid
     * @param number $value
     * @throws dml_exception
     */
    public function set_complete($userid, $value) {
        global $DB;
        $data = $DB->get_record('groupformation_users', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ));
        $data->completed = intval($value);
        $data->timecompleted = time();
        $DB->update_record('groupformation_users', $data);
    }

    /**
     * Returns score for a specific topic
     *
     * @param int $topicnumber
     * @return float|string
     * @throws dml_exception
     */
    public function get_topic_score($topicnumber) {
        global $DB;

        $score = 0;

        $questionid = $topicnumber;

        $num = $this->store->get_number('topic');

        $answers = $DB->get_records('groupformation_answers',
                array('groupformation' => $this->groupformationid, 'category' => 'topic', 'questionid' => $questionid));

        foreach ($answers as $answer) {
            $score += $answer->answer;
        }

        if (count($answers) == 0 || $num == 0) {
            return "-";
        }

        return floatval(floatval($score) / count($answers)) / $num;
    }

    /**
     * Returns all user values
     *
     * @param int $userid
     * @return array
     * @throws dml_exception
     */
    public function get_user_values($userid) {
        global $DB;

        return $DB->get_records('groupformation_user_values',
                array('groupformationid' => $this->groupformationid, 'userid' => $userid));
    }

    /**
     * Returns statistics about answers of users and submissions
     *
     * @return array
     * @throws dml_exception
     */
    public function get_statistics() {
        $stats = array();

        $studentcount = count(mod_groupformation_util::get_users($this->groupformationid));

        $stats ['enrolled'] = $studentcount;

        $started = $this->get_started();
        $startedcount = count($started);

        $stats ['processing'] = $startedcount;

        $completed = $this->get_completed();
        $completedcount = count($completed);

        $stats ['submitted'] = $completedcount;

        $nomissinganswers = $this->get_completed_by_answer_count();
        $nomissingcount = count($nomissinganswers);

        $stats ['submitted_completely'] = $nomissingcount;

        return $stats;
    }

    /**
     * Returns whether the the binquestion is a multiselect (1) or a single choice (0) question
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_binquestionmultiselect(){
        global  $DB;

        return $DB->get_field('groupformation', 'binquestionmultiselect', array(
            'id' => $this->groupformationid
        ));
    }

    /**
     * Returns binquestion importance value
     *
     * @return mixed
     * @throws dml_exception
     */
    public function get_binquestionimportance(){
        global  $DB;

        return $DB->get_field('groupformation', 'binquestionimportance', array(
            'id' => $this->groupformationid
        ));
    }
}
