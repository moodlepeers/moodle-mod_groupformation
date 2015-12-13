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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/controller/grouping_controller.php');

class mod_groupformation_test_user_generator {

    /** @var cm_info */
    private $cm;

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
     * @param $n
     * @param $groupformationid
     * @param bool|false $setanswers
     * @param bool|false $randomized
     * @return bool
     */
    public function create_test_users($n, $groupformationid, $setanswers = false, $randomized = false) {
        global $COURSE, $DB;

        $store = new mod_groupformation_storage_manager ($groupformationid);

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
                    $DB->insert_record("groupformation_started", $record);

                } catch (Exception $e) {
                    $this->echowarn("Error while saving questionnaire status for user.");

                    return false;
                }
                try {
                    foreach ($categories as $category) {
                        $m = $store->get_number($category);
                        for ($i = 1; $i <= $m; $i++) {
                            $record = new stdClass ();
                            $record->groupformation = $groupformationid;
                            $record->category = $category;
                            $record->questionid = $i;
                            $record->userid = $userid;
                            $record->timestamp = time();
                            if ($category == "topic" || $category == "knowledge") {
                                $record->answer = ($j % 2 == 0) ? ($i) : ($m + 1 - $i);
                            } else {
                                if ($randomized) {
                                    $record->answer = rand(1, $store->get_max_option_of_catalog_question($i, $category));
                                } else {
                                    $record->answer = ($j % $store->get_max_option_of_catalog_question($i, $category)) + 1;
                                }
                            }
                            $allrecords [] = $record;
                        }
                    }
                    $DB->insert_records("groupformation_answer", $allrecords);
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
     * @param $groupformationid
     * @return bool
     */
    public function delete_test_users($groupformationid) {
        global $DB;

        $username = $this->get_username(null, $groupformationid);

        $userrecords = $DB->get_records_sql('SELECT * FROM {user} WHERE username LIKE \'' . $username . '%\'');

        if (count($userrecords) > 0) {
            foreach ($userrecords as $userid => $record) {

                try {
                    $groupingcontroller = new mod_groupformation_grouping_controller($groupformationid, $this->cm);
                    $groupingcontroller->delete();

                    $DB->delete_records("user", array(
                        'id' => $userid
                    ));

                    $DB->delete_records("groupformation_answer", array(
                        'userid' => $userid
                    ));

                    $DB->delete_records("groupformation_started", array(
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