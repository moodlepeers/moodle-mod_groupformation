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
    die ('Direct access to this script is forbidden.');
}

class mod_groupformation_user_manager
{
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
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     */
    public function get_completed($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'completed' => 1
        ), $sortedby, $fieldset);
    }

    /**
     * Returns array of records of table groupformation_started where completed is 0
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     */
    public function get_not_completed($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'completed' => 0
        ), $sortedby, $fieldset);
    }

    /**
     * Returns array of records of table groupformation_started
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     */
    public function get_started($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid
        ), $sortedby, $fieldset);
    }

    /**
     * Returns array of records of table_groupformation_started if answer_count is equal to
     * the total answer count for this activity
     *
     * @param string $sortedby
     * @param string $fieldset
     * @return multitype:unknown
     */
    public function get_completed_by_answer_count($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'answer_count' => $this->store->get_total_number_of_answers()
        ), $sortedby, $fieldset);
    }

    /**
     * Returns array of records of table_groupformation_started if answer_count is not equal to
     * the total answer count for this activity
     *
     * @param string $sortedby
     * @param string $fieldset
     * @return multitype:unknown
     */
    public function get_not_completed_by_answer_count($sortedby = null, $fieldset = '*') {
        global $DB;
        $tablename = 'groupformation_started';
        $query = "SELECT " . $fieldset . " FROM {{$tablename}} " .
            "WHERE groupformation = ? AND answer_count <> ? ORDER BY ?" . $sortedby;
        return $DB->get_records_sql($query, array(
            $this->groupformationid,
            $this->store->get_total_number_of_answers(),
            $sortedby
        ));
    }

    /**
     * Returns array of records of table_groupformation_started if answer_count is not equal to
     * the total answer count for this activity but the record was submitted
     *
     * @param string $sortedby
     * @param string $fieldset
     * @return multitype:unknown
     */
    public function get_not_completed_but_submitted($sortedby = null, $fieldset = '*') {
        global $DB;
        // TODO HOTFIX FOR HRZ SOSE2016
        return array();
//        $tablename = 'groupformation_started';
//        $query = "SELECT " . $fieldset . " FROM {{$tablename}} ".
//            "WHERE groupformation = ? AND completed = 1 AND answer_count <> ? ORDER BY ?" . $sortedby;
//        return $DB->get_records_sql($query, array(
//            $this->groupformationid,
//            $this->store->get_total_number_of_answers(),
//            $sortedby
//        ));
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
     * @param $userid
     * @param bool|false $completed
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
     * Changes status of questionnaire for a specific user
     *
     * @param $userid
     * @param bool|false $complete
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
     * @param $userid
     * @return int|mixed
     */
    public function get_answering_status($userid) {
        global $DB;

        if (!$this->get_consent($userid) || !$this->has_participant_code($userid)) {
            return -1;
        }

        $exists = $DB->record_exists('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ));
        if ($exists) {
            $value = $DB->get_field('groupformation_started', 'completed', array(
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
     */
    public function is_completed($userid) {
        return array_key_exists($userid,$this->get_completed(null,'userid'));
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

        $usersum = $this->get_number_of_answers($userid);

        return $sum <= $usersum;
    }

    /**
     * Determines if someone already answered at least one question
     *
     * @param null $userid
     * @param null $categories
     * @return bool
     */
    public function already_answered($userid = null, $categories = null) {
        global $DB;
        if (is_null($categories) && is_null($userid)) {
            return !($DB->count_records('groupformation_answer', array(
                    'groupformation' => $this->groupformationid
                )) == 0);
        } else if (is_null($categories) && !is_null($userid)) {
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
     * @param $userid
     * @param $category
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     */
    public function get_answers($userid, $category, $sortedby = null, $fieldset = '*') {
        global $DB;
        if (is_null($userid) && is_null($category)) {
            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
            ), $sortedby, $fieldset);
        } else if (is_null($userid)) {

            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'category' => $category
            ), $sortedby, $fieldset);
        } else if (is_null($category)) {

            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ), $sortedby, $fieldset);
        } else {
            return $DB->get_records('groupformation_answer', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid,
                'category' => $category
            ), $sortedby, $fieldset);
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
     * @param $userid
     * @param $category
     * @param $answer
     * @param $questionid
     */
    public function save_answer($userid, $category, $answer, $questionid) {
        global $DB;
        $status = $this->get_answering_status($userid);

        if (($category == 'grade' || $category == 'general') && $answer == '0') {
            return;
        } else {
            $answeralreadyexists = $this->has_answer($userid, $category, $questionid);

            if ($answeralreadyexists) {
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
     * @param int $questionid
     * @return boolean
     */
    public function has_answer($userid, $category, $questionid) {
        global $DB;

        return $DB->record_exists('groupformation_answer', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid,
            'category' => $category,
            'questionid' => $questionid
        ));;
    }

    /**
     * Returns whether a user has answers in a specific category or not
     *
     * @param $userid
     * @param $category
     * @return bool
     */
    public function has_answers($userid, $category) {
        $firstcondition = ($this->get_answering_status($userid) > -1);
        $secondcondition = (count($this->get_answers($userid, $category)) > 0);

        return ($firstcondition && $secondcondition);
    }

    /**
     * Returns the most chosen topics wrt the users
     *
     * @param $numberoftopics
     * @return array
     */
    public function get_most_common_topics($numberoftopics) {
        global $DB;
        $scores = [];
        for ($i = 1; $i <= $numberoftopics; $i++) {
            $answers = $DB->get_records('groupformation_answer',
                array('groupformation' => $this->groupformationid, 'category' => 'topic', 'questionid' => $i));
            $score = 0;
            foreach ($answers as $answer) {
                $score += $answer->answer;
            }
            $scores[] = array('id' => strval($i), 'score' => $score);
        }
        usort($scores, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        $result = array_slice($scores, 0, $numberoftopics);

        return $result;
    }

    /**
     * Returns consent value for user
     *
     * @param $userid
     * @return bool
     */
    public function get_consent($userid) {
        global $DB;
        if ($DB->record_exists('groupformation_started',
            array('groupformation' => $this->groupformationid, 'userid' => $userid))
        ) {
            return boolval($DB->get_field('groupformation_started', 'consent', array('groupformation' => $this->groupformationid, 'userid' => $userid)));
        } else {
            return false;
        }
    }

    /**
     * Sets consent by value
     *
     * @param $userid
     * @param $value
     */
    public function set_consent($userid, $value) {
        global $DB;
        $this->set_status($userid);
        $record = $DB->get_record('groupformation_started', array('groupformation' => $this->groupformationid, 'userid' => $userid));
        $record->consent = $value;
        $DB->update_record('groupformation_started', $record);
    }

    /**
     * Deletes all answers
     *
     * @param $userid
     */
    public function delete_answers($userid) {
        global $DB;
        $DB->delete_records('groupformation_started', array('groupformation' => $this->groupformationid, 'userid' => $userid));
        $DB->delete_records('groupformation_answer', array('groupformation' => $this->groupformationid, 'userid' => $userid));
    }

    /**
     * Returns whether participant code is correctly computed or not
     *
     * @param $participantcode
     * @return bool
     */
    public function validate_participant_code($participantcode) {
        if (strlen($participantcode) !== 8) {
            return false;
        }
        $array = array(true, false, true, false);
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
     * @param $userid
     * @return bool
     */
    public function has_participant_code($userid) {
        global $DB;
        $exists = $DB->record_exists('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ));
        if ($exists) {
            $value = $DB->get_field('groupformation_started', 'participantcode', array(
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
     * @param $userid
     * @param $participantcode
     */
    public function register_participant_code($userid, $participantcode) {
        global $DB;
        $this->set_status($userid);
        $record = $DB->get_record('groupformation_started', array('groupformation' => $this->groupformationid, 'userid' => $userid));
        $record->participantcode = $participantcode;
        $DB->update_record('groupformation_started', $record);
    }

    /**
     * Returns participant code
     *
     * @param $userid
     * @return mixed|string
     */
    public function get_participant_code($userid) {
        global $DB;
        $exists = $DB->record_exists('groupformation_started', array(
            'groupformation' => $this->groupformationid,
            'userid' => $userid
        ));
        if ($exists) {
            $value = $DB->get_field('groupformation_started', 'participantcode', array(
                'groupformation' => $this->groupformationid,
                'userid' => $userid
            ));
            return $value;
        }

        return '';
    }
}
