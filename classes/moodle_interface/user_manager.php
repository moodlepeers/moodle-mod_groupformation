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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // / It must be included from a Moodle page
}

class mod_groupformation_user_manager {
    private $groupformationid;

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
     * Returns array of records of table groupformation_started where completed is 1
     *
     * @return array
     */
    public function get_completed($sorted_by = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'completed' => 1
        ), $sorted_by, $fieldset);
    }

    /**
     * Returns array of records of table groupformation_started where completed is 0
     *
     * @return array
     */
    public function get_not_completed($sorted_by = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'completed' => 0
        ), $sorted_by, $fieldset);
    }

    /**
     * Returns array of records of table groupformation_started
     *
     * @return array
     */
    public function get_started($sorted_by = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid
        ), $sorted_by, $fieldset);
    }

    /**
     * Returns array of records of table_groupformation_started if answer_count is equal to
     * the total answer count for this activity
     *
     * @param string $sorted_by
     * @param string $fieldset
     * @return multitype:unknown
     */
    public function get_completed_by_answer_count($sorted_by = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'answer_count' => $this->store->get_total_number_of_answers()
        ), $sorted_by, $fieldset);
    }

    /**
     * Returns array of records of table_groupformation_started if answer_count is not equal to
     * the total answer count for this activity
     *
     * @param string $sorted_by
     * @param string $fieldset
     * @return multitype:unknown
     */
    public function get_not_completed_by_answer_count($sorted_by = null, $fieldset = '*') {
        global $DB;
        $tablename = 'groupformation_started';

        return $DB->get_records_sql("SELECT " . $fieldset . " FROM {{$tablename}} WHERE groupformation = ? AND answer_count <> ? ORDER BY ?" . $sorted_by, array(
            $this->groupformationid,
            $this->store->get_total_number_of_answers(),
            $sorted_by
        ));
    }

    /**
     * Returns array of records of table_groupformation_started if answer_count is not equal to
     * the total answer count for this activity but the record was submitted
     *
     * @param string $sorted_by
     * @param string $fieldset
     * @return multitype:unknown
     */
    public function get_not_completed_but_submitted($sorted_by = null, $fieldset = '*') {
        global $DB;
        $tablename = 'groupformation_started';

        return $DB->get_records_sql("SELECT " . $fieldset . " FROM {{$tablename}} WHERE groupformation = ? AND completed = 1 AND answer_count <> ? ORDER BY ?" . $sorted_by, array(
            $this->groupformationid,
            $this->store->get_total_number_of_answers(),
            $sorted_by
        ));
    }

    /**
     * Sets answer counter for user
     *
     * @param int $userid
     */
    public function set_answer_count($userid) {
        global $DB;
        if ($record = $DB->get_record('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ))
        ) {
            $record->answer_count = $DB->count_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ));
            $DB->update_record('groupformation_started', $record);
        } else {
            $this->change_status($userid);
            $record = $DB->get_record('groupformation_started', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ));
            $record->answer_count = $DB->count_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ));
            $DB->update_record('groupformation_started', $record);
        }
    }

    /**
     * set status to complete
     *
     * @param int $userid
     */
    public function set_status($userid, $completed = false) {
        global $DB;
        if ($data = $DB->get_record('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ))
        ) {
            $data = $DB->get_record('groupformation_started', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ));
            $data->completed = $completed;
            $data->timecompleted = time();
            $DB->update_record('groupformation_started', $data);
        } else {
            $data = new stdClass ();
            $data->completed = $completed;
            $data->groupformation = $this->groupformationid;
            $data->userid = $userid;
            $DB->insert_record('groupformation_started', $data);
        }
    }

    /**
     * Changes status of questionaire for a specific user
     *
     * @param unknown $userId
     * @param number $complete
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
            // TODO Mathevorkurs
            // $this->gm->assign_to_group_AB( $userid);
        }
    }

    /**
     * Returns answering status for user
     * 0 seen
     * 1 completed
     * -1 otherwise
     *
     * @param unknown $userId
     * @return number
     */
    public function get_answering_status($userid) {
        global $DB;

        $exists = $DB->record_exists('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ));
        if ($exists) {
            return $DB->get_field('groupformation_started', 'completed', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ));
        } else {
            return -1;
        }
    }

    /**
     * Returns whether questionaire was completed and send by user or not
     *
     * @param int $userid
     * @return boolean
     */
    public function is_completed($userid) {
        global $DB;

        return $this->get_answering_status($userid) == 1;
    }

    /**
     * Determines the number of answered questions of a user (in all categories or a specified category)
     *
     * @param int $userid
     * @param string $category
     * @return number
     */
    public function get_number_of_answers($userid, $category = null) {
        global $DB;

        if (!is_null($category)) {
            return $DB->count_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid,
                'category' => $category
            ));
        }

        return $DB->count_records('groupformation_answer', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ));
    }

    /**
     * Determines whether user has answered every question or not
     *
     * @param int $userid
     * @return boolean
     */
    public function has_answered_everything($userid) {
        $store = new mod_groupformation_storage_manager ($this->groupformationid);

        $categories = $store->get_categories();
        $sum = array_sum($store->get_numbers($categories));

        $user_sum = $this->get_number_of_answers($userid);
        return $sum <= $user_sum;
    }

    /**
     * Determines if someone already answered at least one question
     *
     * @return boolean
     */
    public function already_answered($userid = null, $categories = null) {
        global $DB;
        if (is_null($categories) && is_null($userid)) {
            return !($DB->count_records('groupformation_answer', array(
                    'groupformation' => $this->groupformationid
                )) == 0);
        } elseif (is_null($categories) && !is_null($userid)) {
            return !($DB->count_records('groupformation_answer', array(
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

        return false;
    }

    /**
     * Returns all answers of a specific user in a specific category
     *
     * @param int $userid
     * @param string $category
     * @return array
     */
    public function get_answers($userid, $category, $sorted_by = null, $fieldset = '*') {
        global $DB;
        if (is_null($userid) && is_null($category)) {
            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
            ), $sorted_by, $fieldset);
        } else if (is_null($userid)) {

            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'category' => $category
            ), $sorted_by, $fieldset);
        } else if (is_null($category)) {

            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ), $sorted_by, $fieldset);
        } else {
            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid,
                'category' => $category
            ), $sorted_by, $fieldset);
        }
    }

    /**
     * Returns answer of a specific user to a specific question in a specific category
     *
     * @param int $userid
     * @param string $category
     * @param int $questionid
     * @return int
     */
    public function get_single_answer($userid, $category, $questionid) {
        global $DB;

        return $DB->get_field('groupformation_answer', 'answer', array(
            'groupformation' => $this->groupformationid,
            'userid' => strval($userid),
            'category' => strval($category),
            'questionid' => strval($questionid)
        ));
    }

    /**
     * Saves answer
     *
     * @param unknown $answer
     * @param unknown $position
     */
    public function save_answer($userid, $category, $answer, $questionid) {
        global $DB;
        $status = $this->get_answering_status($userid);
        // if the answer in category "grade"(dropdowns) is default(0) - return without saving
        if (($category == 'grade' || $category == 'general') && $answer == '0') {
            /*
             * if($status == -1){
             * $status = SAVE;
             * $this->change_status($userid);
             * }
             */
            return;
        } else {
            $answerAlreadyExist = $this->has_answer($userid, $category, $questionid);

            if ($answerAlreadyExist) {
                $record = $DB->get_record('groupformation_answer', array(
                    'groupformation' => $this->groupformationid,
                    'userid' => $userid,
                    'category' => $category,
                    'questionid' => $questionid
                ));
                $record->answer = $answer;
                $DB->update_record('groupformation_answer', $record);
            } else {
                $record = new stdClass ();
                $record->groupformation = $this->groupformationid;

                $record->userid = $userid;
                $record->category = $category;
                $record->questionid = $questionid;
                $record->answer = $answer;
                $record->timestamp = time();
                $DB->insert_record('groupformation_answer', $record);
            }

            if ($status == -1) {
                $this->change_status($userid);
            }
            $this->set_answer_count($userid);
        }
    }

    /**
     * Returns whether answer of a specific user for a specific question in a specific category exists or not
     *
     * @param int $userid
     * @param string $category
     * @param int $questionId
     * @return boolean
     */
    public function has_answer($userid, $category, $questionId) {
        global $DB;

        return $DB->record_exists('groupformation_answer', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid,
            'category' => $category,
            'questionid' => $questionId
        ));;
    }

    /**
     * Returns whether a user has answers in a specific category or not
     *
     * @return boolean
     */
    public function has_answers($userid, $category) {
        $firstCondition = ($this->get_answering_status($userid) > -1);
        $secondCondition = ($this->get_answers($userid, $category) > 0);

        return ($firstCondition && $secondCondition);
    }

    /**
     * Returns the most chosen topics wrt the users
     *
     * @param number $users
     */
    public function get_most_common_topics($number_of_topics) {
        global $DB;
        $scores = [];
        for ($i = 1; $i <= $number_of_topics; $i++) {
            $answers = $DB->get_records('groupformation_answer', array('groupformation' => $this->groupformationid, 'category' => 'topic', 'questionid' => $i));
            $score = 0;
            foreach ($answers as $answer) {
                $score += $answer->answer;
            }
            $scores[] = array('id' => strval($i), 'score' => $score);
        }
        usort($scores, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        $result = array_slice($scores, 0, $number_of_topics);

        return $result;
    }
}