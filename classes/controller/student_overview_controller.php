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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

if (! defined ( 'MOODLE_INTERNAL' )) {
    die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');

require_once ($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/userid_filter.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');

class mod_groupformation_student_overview_controller {

    private $cmid;
    private $userid;
    private $groupformationid;
    private $store;
    private $groups_store;

    private $view_state;

    private $groupformation_state_info;
    private $buttons_array = array();
    private $buttons_info_array = array();
    private $survey_states_array = array();
    private $groupformation_info;
    private $survey_states_title = '';

    private $view = NULL;

    /**
     * Creates an instance of grouping_controller for groupformation
     *
     * @param unknown $groupformationID
     */
    public function __construct($cmid, $groupformationid, $userid) {
        // its not the groupformation id -> its also unused so far
        $this->cmid = $cmid;
        $this->userid = $userid;
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ( $groupformationid );
        $this->groups_store = new mod_groupformation_groups_manager($groupformationid);

        $this->view = new mod_groupformation_template_builder ();

        $this->determine_status ();
        $this->determine_view();
    }

    /**
     * Determines status of grouping_view
     */
    public function determine_status() {
        global $PAGE;

        if (has_capability('mod/groupformation:onlystudent', $PAGE->context)){
            $isBuild = $this->groups_store->is_build();
            if($isBuild){
                $this->view_state = 2;
            }else{
                if ($this->store->isQuestionaireAvailable()) {
                    $this->view_state = $this->store->answeringStatus($this->userid);
                }else{$this->view_state = 4;}
            }
        }else{
            $this->view_state = 3;
        }

    }

    public function test(){
        return $this->view_state;
    }


    /**
     * set all variable to the current state
     */
    private function determine_view(){
        switch ($this->view_state) {
            case -1 :
                $this->groupformation_info = mod_groupformation_util::get_info_text_for_student(true, $this->groupformationid);
                $this->groupformation_state_info = $this->availabilityState();
                $this->buttons_info_array = array(get_string ( 'questionaire_press_to_begin', 'groupformation' ));
                $this->buttons_array = array(
                    array('type' => 'submit',
                        'name' => '',
                        'value' => get_string("next"),
                        'state' => '',
                        'text' => get_string("next")
                    )
                );
                break;

            case 0:
                $this->groupformation_info = mod_groupformation_util::get_info_text_for_student(false, $this->groupformationid);
                $this->groupformation_state_info = $this->availabilityState();
                $this->buttons_info_array = array( get_string ( 'questionaire_not_submitted', 'groupformation' ), get_string ( 'questionaire_press_continue_submit', 'groupformation' ) );

                $this->determine_survey_stats();

                $hasAnsweredEverything = $this->store->hasAnsweredEverything ( $this->userid );
                $disabled = ! $hasAnsweredEverything;

                $this->buttons_array = array(
                    array('type' => 'submit',
                        'name' => 'begin',
                        'value' => '1',
                        'state' => '',
                        'text' => get_string ( 'edit' )
                    ),
                    array('type' => 'submit',
                        'name' => 'begin',
                        'value' => '0',
                        'state' => (($disabled) ? 'disabled' : ''),
                        'text' => get_string ( 'questionaire_submit', 'groupformation' )
                    ),
                );
                break;

            case 1:
                $this->groupformation_info = mod_groupformation_util::get_info_text_for_student(false, $this->groupformationid);
                $this->groupformation_state_info = $this->availabilityState();
                $this->buttons_info_array = array(get_string ( 'questionaire_submitted', 'groupformation' ));
                $this->buttons_array = array();
                break;

            case 2:
                $this->groupformation_info = mod_groupformation_util::get_info_text_for_student(false, $this->groupformationid);
                $this->groupformation_state_info = 'Gruppen sind gebildet';
                $this->buttons_info_array = array();
                $this->buttons_array = array();
                break;

            case 3:
                $this->groupformation_info = mod_groupformation_util::get_info_text_for_student(false, $this->groupformationid);
                $this->groupformation_state_info = 'This activity is not accessible for you';
                $this->buttons_info_array = array();
                $this->buttons_array = array();
                break;

            case 4;
                $this->groupformation_info = mod_groupformation_util::get_info_text_for_student(false, $this->groupformationid);
                $this->groupformation_state_info = $this->availabilityState();
                $this->buttons_info_array = array();
                $this->buttons_array = array();

                break;

            default:
                $this->groupformation_state_info = 'invalid status';
                $this->buttons_info_array = array();
                $this->buttons_array = array();
                break;
        }

    }

    /**
     * Prints stats about answered and misssing questions
     */
    private function determine_survey_stats() {

        $stats = $this->store->getStats ( $this->userid );
        $prev_incomplete = false;
        $array = array();
        foreach ( $stats as $key => $values ) {

            $a = new stdClass ();
            $a->category = get_string ( 'category_' . $key, 'groupformation' );
            $a->questions = $values ['questions'];
            $a->answered = $values ['answered'];
            if ($values ['questions'] > 0) {
                $url = new moodle_url ( 'questionaire_view.php', array (
                    'id' => $this->cmid,
                    'category' => $key
                ) );
                if (! $prev_incomplete) {
                    $a->category = '<a href="' . $url . '">' . $a->category . '</a>';
                }
                if ($values ['missing'] == 0) {
                    $array[] = get_string ( 'stats_all', 'groupformation', $a ) . ' <span class="questionaire_all">&#10004;</span>';
                    $prev_incomplete = false;
                } elseif ($values ['answered'] == 0) {
                    $array[] = get_string ( 'stats_none', 'groupformation', $a ) . ' <span class="questionaire_none">&#10008;</span>';
                    $prev_incomplete = true;
                } else {
                    $array[] = get_string ( 'stats_partly', 'groupformation', $a );
                    $prev_incomplete = true;
                }
            }
        }
        $this->survey_states_title = get_string ( 'questionaire_answer_stats', 'groupformation' );
        $this->survey_states_array = $array;
    }


    /**
     * return the status of the survey
     * @return string
     *
     */
    private  function availabilityState() {
        $a = $this->store->getTime ();
        $begin = intval ( $a ['start_raw'] );
        $end = intval ( $a ['end_raw'] );
        $now = time ();
        if ($begin == 0 & $end == 0) {
            return get_string ( 'questionaire_available', 'groupformation', $a );
        } elseif ($begin != 0 & $end == 0) {
            // erst ab $begin verfügbar
            if ($now < $begin) {
                // noch nicht verfügbar
                return get_string ( 'questionaire_not_available_begin', 'groupformation', $a );
            } elseif ($now >= $begin) {
                // verfügbar
                return get_string ( 'questionaire_available', 'groupformation', $a );
            }
        } elseif ($begin == 0 & $end != 0) {
            // nur verfügbar bis $end
            if ($now <= $end) {
                // verfügbar
                return get_string ( 'questionaire_available_end', 'groupformation', $a );
            } elseif ($now > $end) {
                // nicht mehr verfügbar
                return get_string ( 'questionaire_not_available', 'groupformation', $a );
            }
        } elseif ($begin != 0 & $end != 0) {
            // verfügbar zwischen $begin und $end
            if ($now < $begin & $now < $end) {
                // noch nicht verfügbar
                return get_string ( 'questionaire_not_available_begin_end', 'groupformation', $a );
            } elseif ($now >= $begin & $now <= $end) {
                // verfügbar
                return get_string ( 'questionaire_available', 'groupformation', $a );
            } elseif ($now > $begin & $now > $end) {
                // nicht mehr verfügbar
                return get_string ( 'questionaire_not_available_end', 'groupformation', $a );
            }
        }
    }



    /**
     * Generate and return the HTMl Page with templates and data
     *
     * @return string
     */
    public function display() {
        $this->determine_status ();
        $this->determine_view();

        $this->view->setTemplate ( 'wrapper_students_overview' );

        $this->view->assign ( 'student_overview_title', $this->store->getName() );
        $this->view->assign('student_overview_groupformation_info', $this->groupformation_info);
        $this->view->assign('student_overview_groupformation_status', $this->groupformation_state_info);

        if($this->view_state == 0){
            $survey_stats_view = new mod_groupformation_template_builder ();
            $survey_stats_view->setTemplate ( 'students_overview_survey_states' );
            $survey_stats_view->assign('survey_states', $this->survey_states_array);
            $survey_stats_view->assign('questionaire_answer_stats', $this->survey_states_title);
            $this->view->assign('student_overview_survey_state_temp', $survey_stats_view->loadTemplate());
        }else{
            $this->view->assign('student_overview_survey_state_temp', '');
        }


        $this->view->assign('cmid', $this->cmid);
        $this->view->assign('buttons', $this->buttons_array);
        $this->view->assign('buttons_infos', $this->buttons_info_array);


        return $this->view->loadTemplate ();
    }



}

?>