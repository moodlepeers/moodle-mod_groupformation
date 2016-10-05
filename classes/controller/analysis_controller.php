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
 * Controller for analysis view
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @package    mod_groupformation
 * @copyright  2015 MoodlePeers
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');

class mod_groupformation_analysis_controller {

    /** @var int The id of the groupformation activity */
    private $groupformationid;
    private $cm;
    private $jobstate;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store = null;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    private $view = null;
    private $questionnaireavailable;
    private $activitytime;
    private $starttime;
    private $endtime;
    private $timenow;
    private $test;
    private $state;

    /**
     * Creates instance of analysis controller
     *
     * @param int $groupformationid
     */
    public function __construct($groupformationid, $cm) {
        $this->cm = $cm;
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager($groupformationid);
        $this->view = new mod_groupformation_template_builder();
        $this->determine_status();
    }

    /**
     * Triggers questionnaire
     *
     * @param $switcher
     */
    public function trigger_questionnaire($switcher){

        switch($switcher){
            /**
             * Sets start time of questionnaire to now
             */
            case 1: $this->store->open_questionnaire();
                break;

            /**
             * Sets end time of questionnaire to now
             */
            case -1: $this->store->close_questionnaire();
                break;
        }
    }

    /**
     * Loads status for template
     *
     * @return string
     */
    private function load_status() {
        $statusanalysisview = new mod_groupformation_template_builder();
        $statusanalysisview->set_template('analysis_status');

        $this->activitytime = $this->store->get_time();

        if (intval($this->activitytime ['start_raw']) == 0) {
            $this->starttime = get_string('no_time', 'groupformation');
        } else {
            $this->starttime = $this->activitytime ['start'];
        }

        if (intval($this->activitytime ['end_raw']) == 0) {
            $this->endtime = get_string('no_time', 'groupformation');
        } else {
            $this->endtime = $this->activitytime ['end'];
        }

        $buttonvalue = ($this->questionnaireavailable) ? -1 : 1;
        $buttoncaption = ($this->questionnaireavailable) ?
            get_string('activity_end', 'groupformation') : get_string('activity_start', 'groupformation');
        $buttondisabled = ($this->jobstate !== "ready") ? "disabled" : "";

        $statusanalysisview->assign('button', array(
            'type' => 'submit', 'name' => 'questionnaire_switcher', 'value' => $buttonvalue, 'state' => $buttondisabled,
            'text' => $buttoncaption));

        $infoteacher = mod_groupformation_util::get_info_text_for_teacher(false, "analysis");

        $statusanalysisview->assign('info_teacher', $infoteacher);
        $statusanalysisview->assign('analysis_time_start', $this->starttime);
        $statusanalysisview->assign('analysis_time_end', $this->endtime);

        switch ($this->state) {
            case 1 :
                $statusanalysisview->assign('analysis_status_info', get_string('analysis_status_info0', 'groupformation'));
                break;
            case 2 :
                $statusanalysisview->assign('analysis_status_info', get_string('analysis_status_info1', 'groupformation'));
                break;
            case 3 :
                $statusanalysisview->assign('analysis_status_info', get_string('analysis_status_info2', 'groupformation'));
                break;
            case 4 :
                $statusanalysisview->assign('analysis_status_info', get_string('analysis_status_info4', 'groupformation'));
                break;
            default :
                $statusanalysisview->assign('analysis_status_info', get_string('analysis_status_info3', 'groupformation'));
        }

        return $statusanalysisview->load_template();
    }

    /**
     * Returns stats about answered questionnaires
     *
     * @return array
     */
    private function get_infos() {

        $usermanager = $this->usermanager;
        $stats = array();

        $context = groupformation_get_context($this->groupformationid);
        $students = get_enrolled_users($context, 'mod/groupformation:onlystudent');
        $studentcount = count($students);

        $studentcount = count(mod_groupformation_util::get_users($this->groupformationid));

        $stats [] = $studentcount;

        $started = $usermanager->get_started();
        $startedcount = count($started);

        $stats [] = $startedcount;

        $completed = $usermanager->get_completed();
        $completedcount = count($completed);

        $stats [] = $completedcount;

        $nomissinganswers = $usermanager->get_completed_by_answer_count();
        $nomissinganswerscount = count($nomissinganswers);

        $stats [] = $nomissinganswerscount;

        $missinganswers = $usermanager->get_not_completed_but_submitted();
        $missinganswerscount = count($missinganswers);

        $stats [] = $missinganswerscount;

        return $stats;
    }

    /**
     * Loads statistics for template
     *
     * @return string
     */
    private function load_statistics() {
        global $PAGE;

        $questionnairestats = $this->get_infos($this->groupformationid);

        $statisticsanalysisview = new mod_groupformation_template_builder();
        $statisticsanalysisview->set_template('analysis_statistics');
        $context = $PAGE->context;
        $count = count(get_enrolled_users($context, 'mod/groupformation:onlystudent'));

        $statisticsanalysisview->assign('statistics_enrolled', $questionnairestats [0]);
        $statisticsanalysisview->assign('statistics_processed', $questionnairestats [1]);
        $statisticsanalysisview->assign('statistics_submited', $questionnairestats [2]);
        $statisticsanalysisview->assign('statistics_submited_incomplete', $questionnairestats [4]);
        $statisticsanalysisview->assign('statistics_submited_complete', $questionnairestats [3]);

        return $statisticsanalysisview->load_template();
    }

    /**
     * Display all templates
     *
     * @return string
     */
    public function display() {
        $this->view->set_template('wrapper_analysis');
        $this->view->assign('analysis_name', $this->store->get_name());
        $this->view->assign('analysis_status_template', $this->load_status());
        $this->view->assign('analysis_statistics_template', $this->load_statistics());

        return $this->view->load_template();
    }

    /**
     * Determine status variables
     */
    public function determine_status() {
        global $DB;
        $this->questionnaireavailable = $this->store->is_questionnaire_available();
        $this->state = 1;
        $job = mod_groupformation_job_manager::get_job($this->groupformationid);
        if (is_null($job)) {
            $groupingid = ($this->cm->groupmode != 0) ? $this->cm->groupingid : 0;
            mod_groupformation_job_manager::create_job($this->groupformationid, $groupingid);
            $job = mod_groupformation_job_manager::get_job($this->groupformationid);
        }
        $this->jobstate = mod_groupformation_job_manager::get_status($job);
        if ($this->jobstate !== 'ready') {
            $this->state = 3;
        } else if ($this->questionnaireavailable) {
            $this->state = 1;
        } else if (count($this->usermanager->get_completed()) > 0) {
            $this->state = 4;
        } else {
            $this->state = 2;
        }
    }

    /** hot fix for answers */
    public function fix_answers(){
        global $DB;

        $answers = $DB->get_records('groupformation_answer',
            array('groupformation'=>$this->groupformationid, 'category'=>'srl')
        );

        $map = array(1=>63,2=>64,3=>65,4=>66,5=>67,6=>68,7=>69,8=>70,9=>71,10=>72,11=>73,12=>74,13=>75,14=>76,15=>77,16=>78,17=>79,18=>80,19=>81,20=>82,21=>83,22=>84,23=>85,24=>86,25=>87,26=>88);

        foreach ($answers as $answer){
            if (intval($answer->questionid) <= 26){
                $qid = $map[$answer->questionid];
                if ($DB->record_exists('groupformation_answer',
                    array('groupformation'=>$this->groupformationid, 'category'=>'srl','userid'=>$answer->userid,'questionid'=>$qid))){
                    $DB->delete_records('groupformation_answer',
                        array('groupformation'=>$this->groupformationid, 'category'=>'srl','userid'=>$answer->userid,'questionid'=>$answer->questionid));
                }else{
                    $answer->questionid = $qid;
                    $DB->update_record('groupformation_answer',$answer,true);
                }
            }elseif (intval($answer->questionid) <= 63){

            }
        }
    }
}