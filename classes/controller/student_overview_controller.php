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
 * This file contains a controller class for overview
 *
 * @package     mod_groupformation
 * @author     Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');

/**
 * Controller for student overview
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_student_overview_controller {

    /** @var int id of the course module */
    private $cmid;

    /** @var int id of the user */
    private $userid;

    /** @var int id of the activity */
    private $groupformationid;

    /** @var mod_groupformation_storage_manager Storage manager */
    private $store;

    /** @var mod_groupformation_groups_manager Groups manager */
    private $groupsmanager;

    /** @var mod_groupformation_user_manager User manager */
    private $usermanager;

    /** @var int current view state of the activity */
    private $viewstate;

    /** @var array current activity state information */
    private $groupformationstateinfo = array();

    /** @var array current buttons */
    private $buttonsarray = array();

    /** @var string current button info */
    private $buttonsinfo;

    /** @var array current questionnaire state information */
    private $questionnairestatearray = array();

    /** @var string current information text for student */
    private $groupformationinfo;

    /** @var mod_groupformation_template_builder template builder for view */
    private $view = null;

    /**
     * Constructor for studentent overview controller
     *
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

        $this->view = new mod_groupformation_template_builder ();

        $this->determine_status();
        $this->determine_view();
    }

    /**
     * Determines status of grouping_view
     */
    public function determine_status() {
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
    }

    /**
     * set all variable to the current state
     */
    private function determine_view() {
        switch ($this->viewstate) {
            case -1 : // Questionaire is available but not started yet.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(true,
                    $this->groupformationid);
                $this->groupformationstateinfo = array(
                    $this->get_availability_state());
                $this->buttonsinfo = get_string('questionnaire_press_to_begin', 'groupformation');
                $this->buttonsarray = array(
                    array(
                        'type' => 'submit', 'name' => '', 'value' => get_string("next"), 'state' => '',
                        'text' => get_string("next")));
                break;

            case 0 : // Questionaire is available, started, not finished and not submited.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(false,
                    $this->groupformationid);
                $this->groupformationstateinfo = array(
                    $this->get_availability_state(), get_string('questionnaire_not_submitted', 'groupformation'));
                $this->buttonsinfo = get_string('questionnaire_press_continue_submit', 'groupformation');

                $this->determine_survey_stats();

                $this->buttonsarray = array(
                    array(
                        'type' => 'submit', 'name' => 'begin', 'value' => '1', 'state' => '',
                        'text' => get_string('edit')), array(
                        'type' => 'submit', 'name' => 'begin', 'value' => '0',
                        'state' => '',
                        'text' => get_string('questionnaire_submit', 'groupformation')));
                break;

            case 1 : // Questionaire is submitted.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(true,
                    $this->groupformationid);
                $this->groupformationstateinfo = array(
                    get_string('questionnaire_submitted', 'groupformation'));
                $this->buttonsinfo = '';
                $this->buttonsarray = array();
                break;

            case 2 : // Groups are built.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(false,
                    $this->groupformationid);
                $this->groupformationstateinfo = array(
                    get_string('groups_build', 'groupformation'));
                $this->buttonsinfo = '';
                $this->buttonsarray = array();
                break;

            case 3 : // The activity is not accessible for the student/teacher.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(false,
                    $this->groupformationid);
                $this->groupformationstateinfo = array(
                    get_string('activity_visible', 'groupformation'));
                $this->buttonsinfo = '';
                $this->buttonsarray = array();
                break;

            case 4 : // The questionnaire is not available, but groups are not build yet.
                $this->groupformationinfo = mod_groupformation_util::get_info_text_for_student(true,
                    $this->groupformationid);
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

                if (true || !$previncomplete) {
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
        $this->questionnairestatearray = $array;
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
     * Generate and return the HTMl Page with templates and data
     *
     * @return string
     */
    public function display() {
        $this->determine_status();
        $this->determine_view();

        $this->view->set_template('wrapper_students_overview');
        $this->view->assign('cmid', $this->cmid);

        $this->view->assign('student_overview_title', $this->store->get_name());
        $this->view->assign('student_overview_groupformation_info', $this->groupformationinfo);
        $this->view->assign('student_overview_groupformation_status', $this->groupformationstateinfo);

        if ($this->viewstate == 0) {
            $surveystatsview = new mod_groupformation_template_builder ();
            $surveystatsview->set_template('students_overview_survey_states');
            $surveystatsview->assign('survey_states', $this->questionnairestatearray);
            $surveystatsview->assign('questionnaire_answer_stats', get_string('questionnaire_answer_stats', 'groupformation'));
            $this->view->assign('student_overview_survey_state_temp', $surveystatsview->load_template());
        } else {
            $this->view->assign('student_overview_survey_state_temp', '');
        }

        if ($this->viewstate == -1 || $this->viewstate == 0) {
            $surveyoptionsview = new mod_groupformation_template_builder ();
            $surveyoptionsview->assign('cmid', $this->cmid);
            $surveyoptionsview->set_template('students_overview_options');
            $surveyoptionsview->assign('buttons', $this->buttonsarray);
            $surveyoptionsview->assign('buttons_infos', $this->buttonsinfo);
            $this->view->assign('student_overview_survey_options', $surveyoptionsview->load_template());
        } else {
            $this->view->assign('student_overview_survey_options', '');
        }

        return $this->view->load_template();
    }
}

