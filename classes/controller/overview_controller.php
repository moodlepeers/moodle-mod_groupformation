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
 *
 * @package mod_groupformation
 * @@author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');

class mod_groupformation_overview_controller {

    /** @var int ID of course module*/
    public $cmid;

    /** @var int ID of user */
    public $userid;

    /** @var int ID of module instance */
    public $groupformationid;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store;

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    private $viewstate;
    private $groupformationstateinfo = array();
    private $buttonsarray = array();
    private $buttonsinfo;
    private $groupformationinfo;

    /**
     * mod_groupformation_student_overview_controller constructor.
     * @param $cmid
     * @param $groupformationid
     * @param $userid
     */
    public function __construct($cmid, $groupformationid, $userid) {
        $this->cmid = $cmid;
        $this->userid = $userid;
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);

        $this->determine_view();
    }

    /**
     * set all variable to the current state
     */
    private function determine_view() {
        global $PAGE;

        if (has_capability('mod/groupformation:onlystudent', $PAGE->context)) {
            $isbuild = $this->groupsmanager->is_build();
            if ($isbuild) {
                $this->viewstate = 2;
            } else {
                if ($this->store->is_questionnaire_available()) {
                    $this->viewstate = $this->usermanager->get_answering_status($this->userid);
                } else {
                    $this->viewstate = 4;
                }
            }
        } else {
            $this->viewstate = 3;
        }

        switch ($this->viewstate) {
            case -1 : // Questionnaire is available but not started yet.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(
                    true, $this->groupformationid);
                $this->groupformationstateinfo = array(
                    $this->get_availability_state());
                $pc = mod_groupformation_data::ask_for_participant_code();
                $buttoncaption = ($pc) ? "questionnaire_press_to_begin_participant_code" : "questionnaire_press_to_begin";
                $this->buttonsinfo = get_string($buttoncaption, 'groupformation');
                $this->buttonsarray = array(
                    array(
                        'type' => 'submit', 'name' => '', 'value' => get_string("next"), 'state' => '',
                        'text' => get_string("next")));
                break;

            case 0 :
                // Questionnaire is available, started, not finished and not submited.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(
                    false, $this->groupformationid);
                $this->groupformationstateinfo = array(
                    $this->get_availability_state(), get_string('questionnaire_not_submitted', 'groupformation'));
                $this->buttonsinfo = get_string('questionnaire_press_continue_submit', 'groupformation');

                $disabled = $this->store->all_answers_required() && !$this->usermanager->has_answered_everything($this->userid);

                $this->buttonsarray = array(
                    array(
                        'type' => 'submit', 'name' => 'begin', 'value' => '1', 'state' => '',
                        'text' => get_string('edit')),
                    array(
                        'type' => 'submit', 'name' => 'begin', 'value' => '0',
                        'state' => (($disabled) ? 'disabled' : ''),
                        'text' => get_string('questionnaire_submit', 'groupformation')),
                    array(
                        'type' => 'submit', 'name' => 'begin', 'value' => '-1',
                        'state' => '',
                        'text' => get_string('questionnaire_delete', 'groupformation'))
                );
                break;

            case 1 :
                // Questionnaire is submitted.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(
                    true, $this->groupformationid);
                $this->groupformationstateinfo = array(
                    get_string('questionnaire_submitted', 'groupformation'));
                $math = mod_groupformation_data::is_math_prep_course_mode();
                $this->buttonsinfo = get_string('questionnaire_press_revert', 'groupformation');
                $this->buttonsarray = array(
                    array(
                        'type' => 'submit', 'name' => 'begin', 'value' => '0', 'state' => $math?'disabled':'',
                        'text' => get_string('revert')),
                    array(
                        'type' => 'submit', 'name' => 'begin', 'value' => '-1',
                        'state' => '',
                        'text' => get_string('questionnaire_delete', 'groupformation'))
                );
                break;

            case 2 :
                // Groups are built.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(
                    false, $this->groupformationid);
                $this->groupformationstateinfo = array(
                    get_string('groups_build', 'groupformation'));
                $this->buttonsinfo = '';
                $this->buttonsarray = array();
                break;

            case 3 :
                // The activity is not accessible for the student/teacher.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(
                    false, $this->groupformationid);
                $this->groupformationstateinfo = array(
                    get_string('activity_visible', 'groupformation'));
                $this->buttonsinfo = '';
                $this->buttonsarray = array();
                break;

            case 4 :
                // The questionnaire is not available, but groups are not build yet.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(
                    true, $this->groupformationid);
                $this->groupformationstateinfo = array(
                    $this->get_availability_state());
                $this->buttonsinfo = '';
                $this->buttonsarray = array();

                break;

            default :
                $this->groupformationstateinfo = array(
                    get_string('invalid', 'groupformation'));
                $this->buttonsinfo = '';
                $this->buttonsarray = array();
                break;
        }
    }

    /**
     * return the status of the survey
     *
     * @return string
     *
     */
    private function get_availability_state() {
        $a = $this->store->get_time();
        $begin = intval($a ['start_raw']);
        $end = intval($a ['end_raw']);
        $now = time();
        if ($begin == 0 & $end == 0) {
            return get_string('questionnaire_available', 'groupformation', $a);
        } else if ($begin != 0 & $end == 0) {
            // Available from $begin.
            if ($now < $begin) {
                // Not available now.
                return get_string('questionnaire_not_available_begin', 'groupformation', $a);
            } else if ($now >= $begin) {
                // Available.
                return get_string('questionnaire_available', 'groupformation', $a);
            }
        } else if ($begin == 0 & $end != 0) {
            // Just available till $end.
            if ($now <= $end) {
                // Available.
                return get_string('questionnaire_available_end', 'groupformation', $a);
            } else if ($now > $end) {
                // Not available any more.
                return get_string('questionnaire_not_available', 'groupformation', $a);
            }
        } else if ($begin != 0 & $end != 0) {
            // Available between $begin and $end.
            if ($now < $begin & $now < $end) {
                // Not available yet.
                return get_string('questionnaire_not_available_begin_end', 'groupformation', $a);
            } else if ($now >= $begin & $now <= $end) {
                // Available.
                return get_string('questionnaire_available', 'groupformation', $a);
            } else if ($now > $begin & $now > $end) {
                // Not available any more.
                return get_string('questionnaire_not_available_end', 'groupformation', $a);
            }
        }
    }

    /**
     * Prints stats about answered and misssing questions
     */
    private function determine_survey_stats() {
        $stats = mod_groupformation_util::get_stats($this->groupformationid, $this->userid);

        $previncomplete = false;
        $array = array();
        foreach ($stats as $key => $values) {

            $a = new stdClass ();
            $a->category = get_string('category_' . $key, 'groupformation');
            $a->questions = $values ['questions'];
            $a->answered = $values ['answered'];
            if ($values ['questions'] > 0) {
                $url = new moodle_url ('questionnaire_view.php', array(
                    'id' => $this->cmid, 'category' => $key));

                if (!$this->store->all_answers_required() || !$previncomplete) {
                    $a->category = '<a href="' . $url . '">' . $a->category . '</a>';
                }
                if ($values ['missing'] == 0) {
                    $array [] = get_string('stats_all', 'groupformation', $a) .
                        ' <span class="questionaire_all">&#10004;</span>';
                    $previncomplete = false;
                } else if ($values ['answered'] == 0) {
                    $array [] = get_string('stats_none', 'groupformation', $a) .
                        ' <span class="questionaire_none">&#10008;</span>';
                    $previncomplete = true;
                } else {
                    $array [] = get_string('stats_partly', 'groupformation', $a);
                    $previncomplete = true;
                }
            }
        }
        return $array;
    }

    /**
     * Returns info about activity
     *
     * @return array
     */
    public function load_info() {
        $assigns = array();

        $assigns['cmid'] = $this->cmid;
        $assigns['intro_box'] = $this->store->get_intro($this->cmid);
        $assigns['student_overview_title'] = $this->store->get_name();
        $assigns['student_overview_groupformation_info'] = $this->groupformationinfo;
        $assigns['student_overview_groupformation_status'] = $this->groupformationstateinfo;

        return $assigns;
    }

    /**
     * Returns statistics about questionnaire
     *
     * @return array
     */
    public function load_statistics() {
        $assigns = array();

        if ($this->viewstate == 0) {
            $assigns['ask_for_topics'] = $this->store->ask_for_topics();
            $assigns['survey_states'] = $this->determine_survey_stats();
            $assigns['questionnaire_answer_stats'] = get_string('questionnaire_answer_stats', 'groupformation');
            $assigns['participant_code'] = mod_groupformation_data::ask_for_participant_code();
            $assigns['participant_code_user'] = $this->usermanager->get_participant_code($this->userid);
            $assigns[''] = '';
        }

        return $assigns;
    }

    /**
     * Returns settings buttons
     *
     * @return array
     */
    public function load_settings() {
        $assigns = array();

        if ($this->viewstate == -1 || $this->viewstate == 0) {
            $assigns['cmid'] = $this->cmid;
            $assigns['buttons'] = $this->buttonsarray;
            $assigns['buttons_infos'] = $this->buttonsinfo;
            $assigns['participant_code'] = mod_groupformation_data::ask_for_participant_code();
            $assigns['participant_code_user'] = $this->usermanager->get_participant_code($this->userid);
            $assigns['consentheader'] = get_string('consent_header', 'groupformation');
            $assigns['consenttext'] = get_string('consent_message', 'groupformation');
            $assigns['consentvalue'] = $this->usermanager->get_consent($this->userid);
        } else {
            $assigns['cmid'] = $this->cmid;
            $assigns['buttons'] = $this->buttonsarray;
            $assigns['buttons_infos'] = $this->buttonsinfo;
        }

        return $assigns;
    }
}

