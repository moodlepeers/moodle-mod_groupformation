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
 * Test user generator
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/likert_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/topic_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/basic_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/range_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/knowledge_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/dropdown_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/freetext_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/multiselect_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/binquestion_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/number_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/question_table.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/grouping_controller.php');

/**
 * Class mod_groupformation_test_user_generator
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_test_user_generator {

    /** @var cm_info */
    private $cm;

    /**
     * mod_groupformation_test_user_generator constructor.
     *
     * @param null $cm
     */
    public function __construct($cm = null) {
        $this->cm = $cm;
    }

    /**
     * Creates automated username or username prefix
     *
     * @param int $j
     * @param int $groupformationid
     * @return string
     */
    private function get_username($j, $groupformationid) {
        return 'user_g' . $groupformationid . '_nr' . $j;
    }

    /**
     * Creates n test users
     *
     * @param int $n
     * @param int $groupformationid
     * @param bool|false $setanswers
     * @param bool|false $randomized
     * @return bool
     * @throws dml_exception
     */
    public function create_test_users($n, $groupformationid, $setanswers = false, $randomized = false) {
        global $COURSE, $DB;

        $store = new mod_groupformation_storage_manager ($groupformationid);

        $usermanager = new mod_groupformation_user_manager($groupformationid);

        $categories = $store->get_categories();

        $username = $this->get_username(null, $groupformationid);

        $userrecords = $DB->get_records_sql('SELECT * FROM {user} WHERE username LIKE \'' . $username . '%\'');

        if (count($userrecords) > 0) {
            $record = end($userrecords);
            $prevusername = $record->username;
            $prevusernamenr = intval(substr($prevusername, strpos($prevusername, "nr") + 2));
            $first = $prevusernamenr + 1;
            $last = $prevusernamenr + $n;
        } else {
            $first = 1;
            $last = $n;
        }
        for ($j = $first; $j <= $last; $j++) {
            $allrecords = array();
            $username = $this->get_username($j, $groupformationid);
            $password = 'Moodle1234';

            try {
                $user = create_user_record($username, $password);
                $user->firstname = "Dummy";
                $user->lastname = "User " . $j;
                $DB->update_record('user', $user);
                $userid = $user->id;
            } catch (Exception $e) {
                $this->echowarn("Error while creating user. The user might already exist.");

                return false;
            }

            try {
                enrol_try_internal_enrol($COURSE->id, $userid, 5);
            } catch (Exception $e) {
                $this->echowarn("Error while enrolment. User with ID=" . $userid . " is already in course");

                return false;
            }

            if ($setanswers) {
                try {
                    $record = new stdClass ();
                    $record->groupformation = $groupformationid;
                    $record->userid = $userid;
                    $record->completed = ($setanswers) ? 1 : 0;
                    $record->answer_count = $store->get_total_number_of_answers();
                    $record->timecompleted = ($setanswers) ? time() : null;
                    $record->groupid = null;
                    $DB->insert_record("groupformation_users", $record);

                } catch (Exception $e) {
                    $this->echowarn("Error while saving questionnaire status for user.");

                    return false;
                }
                try {
                    foreach ($categories as $category) {
                        $questions2 = $store->get_questions($category);

                        foreach (array_values($questions2) as $key => $question) {
                            $options = $question->options;
                            
                            if ($category == 'points') {
                                $options = array(
                                        $store->get_max_points() => get_string('excellent', 'groupformation'),
                                        0 => get_string('bad', 'groupformation'));
                            }

                            $name = 'mod_groupformation_' . $question->type . '_question';
                            /** @var mod_groupformation_basic_question $questionobj */
                            $questionobj = new $name($category, $question->questionid, $question->question, $options);
                            $answer = $questionobj->create_random_answer();
                            $record = new stdClass ();
                            $record->groupformation = $groupformationid;
                            $record->category = $category;
                            $record->questionid = $question->questionid;
                            $record->userid = $userid;
                            $record->timestamp = time();
                            $record->answer = $answer;
                            $allrecords [] = $record;
                        }
                    }
                    $DB->insert_records("groupformation_answers", $allrecords);

                    if ($usermanager->has_answered_everything($userid)) {
                        $usermanager->set_evaluation_values($userid);
                    }
                } catch (Exception $e) {
                    $this->echowarn("Error while saving answers status for user.");

                    return false;
                }
            }
        }
        if ($setanswers) {
            $this->echowarn('Users (and answers) have been created.');
        } else {
            $this->echowarn('Users have been created.');
        }

        return true;
    }

    /**
     * Echoes warning message
     *
     * @param string $string
     */
    private function echowarn($string) {
        echo '<div class="alert">' . $string . '</div>';
    }

    /**
     * Deletes test users
     *
     * @param int $groupformationid
     * @return bool
     * @throws dml_exception
     */
    public function delete_test_users($groupformationid) {
        global $DB;

        $username = $this->get_username(null, $groupformationid);

        $userrecords = $DB->get_records_sql('SELECT * FROM {user} WHERE username LIKE \'' . $username . '%\'');

        if (count($userrecords) > 0) {
            foreach (array_keys($userrecords) as $userid) {

                try {
                    $groupingcontroller = new mod_groupformation_grouping_controller($groupformationid, $this->cm);
                    $groupingcontroller->delete();

                    $DB->delete_records("user", array(
                            'id' => $userid
                    ));

                    $DB->delete_records("groupformation_answers", array(
                            'userid' => $userid
                    ));

                    $DB->delete_records("groupformation_users", array(
                            'userid' => $userid
                    ));

                    $DB->delete_records('groupformation_user_values', array(
                            'userid' => $userid
                    ));

                    $DB->delete_records("groupformation_group_users", array(
                            'userid' => $userid
                    ));

                    $DB->delete_records("user_enrolments", array(
                            'userid' => $userid
                    ));

                } catch (Exception $e) {
                    $this->echowarn('User with ID=' . $userid . ' has not been deleted.');

                    return false;
                }
            }

            $this->echowarn("All users have been deleted.");

            return true;
        } else {
            $this->echowarn("There was nothing to delete.");

            return true;
        }
    }
}