<?php
/**
 * Created by PhpStorm.
 * User: eduardgallwas
 * Date: 09.07.15
 * Time: 09:59
 */

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/submit_infos.php');



class mod_groupformation_analysis_controller
{
    private $groupformationID;

    // state of the controller
    //private $viewState = 0;
    private $store = NULL;
    private $view = NULL;

    private $questionnaire_status;
    private $activity_time;
    private $start_time;
    private $end_time;
    private $time_now;
    //private $activity_status_info;
    //private $activity_status_info_extend;

    private $analyse_infos = NULL;

    private $test;


    public function __construct($groupformationID)
    {
        $this->groupformationID = $groupformationID;
        $this->store = new mod_groupformation_storage_manager ($groupformationID);
        $this->view = new mod_groupformation_template_builder ();

        $this->analyse_infos = new mod_groupformation_submit_infos ( $groupformationID );

        /*if(($start == 0) && ($end == 0) && ($this->survey_status == true )){
            // die Aktivität ist die ganze zeit verfügbar und muss manuel beendet werden
            $this->viewState = 0;


            $this->test = get_string ( 'questionaire_availability_info_future', 'groupformation', $this->activity_time );
        } elseif (($start > $this->time_now ) && ($end == 0)) {
            // die Aktivität startet am ... und läuft bis sie manuel beendet wird
            $this->viewState = 1;

            $this->activity_status_info = 'test1';

        } elseif (($start > $this->time_now) && !($end == 0)) {
            // die Aktivität startet am ... und endet am ...
            $this->viewState = 2;

            $this->activity_status_info = 'test2';

        } elseif (($start < $this->time_now) && ($end == 0)) {
            // die Aktivität läuft bereits seit... und muss manuel gestoppt werden
            $this->viewState = 3;

            $this->activity_status_info = 'test3';

        } elseif(($start < $this->time_now) && ($end > $this->time_now)) {
            // die Aktivität läuft bereits seit .. und endet am ..
            $this->viewState = 4;

            $this->activity_status_info = 'test4';

        } elseif(($end < $this->time_now)) {
            // die Aktivität wurde am ... beendet
            $this->viewState = 5;

            $this->activity_status_info = 'test5';
        }*/
    }

    public function startQuestionnaire() {
        $this->store->openQuestionnaire();
    }

    public function stopQuestionnaire() {
        $this->store->closeQuestionnaire();
    }


    private function loadStatus(){

        $statusAnalysisView = new mod_groupformation_template_builder ();
        $statusAnalysisView->setTemplate ( 'analysis_status' );

        $this->questionnaire_status = $this->store->isQuestionaireAvailable();

        $this->activity_time = $this->store->getTime ();

        if( intval ( $this->activity_time ['start_raw'] ) == 0)
        {
            $this->start_time = 'Kein Zeitpunkt festgelegt';
        }
        else
        {
            $this->start_time = $this->activity_time ['start'];
        }

        if( intval ( $this->activity_time ['end_raw'] ) == 0)
        {
            $this->end_time = 'Kein Zeitpunkt festgelegt';
        }
        else
        {
            $this->end_time = $this->activity_time ['end'];
        }

        if($this->questionnaire_status  == true){

            $statusAnalysisView->assign ( 'button', array (
                    'type' => 'submit',
                    'name' => 'stop_questionnaire',
                    'value' => '',
                    'state' => '',
                    'text' => 'Aktivität beenden'
                )
            );

        }elseif($this->questionnaire_status  == false){

            $statusAnalysisView->assign ( 'button', array (
                    'type' => 'submit',
                    'name' => 'start_questionnaire',
                    'value' => '',
                    'state' => '',
                    'text' => 'Aktivität starten'
                )
            );
        }

        $info_teacher = mod_groupformation_util::get_info_text_for_teacher(false,"analysis");
        
		$statusAnalysisView->assign('info_teacher', $info_teacher);
        $statusAnalysisView->assign('analysis_time_start', $this->start_time );
        $statusAnalysisView->assign('analysis_time_end', $this->end_time );
        $statusAnalysisView->assign('analysis_status_info', 'here comes the important info text');


        return  $statusAnalysisView->loadTemplate();
    }


    private function loadStatistics()
    {
    	global $PAGE;
    	
        $questionnaire_StatisticNumbers = $this->analyse_infos->getInfos ();

        $statisticsAnalysisView = new mod_groupformation_template_builder ();
        $statisticsAnalysisView->setTemplate ( 'analysis_statistics' );
        $context = $PAGE->context;
		$count = count ( get_enrolled_users ( $context, 'mod/groupformation:onlystudent' ) );
		
        $statisticsAnalysisView->assign('statistics_enrolled', $count );
        $statisticsAnalysisView->assign('statistics_processed', $questionnaire_StatisticNumbers[0] );
        $statisticsAnalysisView->assign('statistics_submited', $questionnaire_StatisticNumbers[1] );
        $statisticsAnalysisView->assign('statistics_submited_incomplete', $questionnaire_StatisticNumbers[2] );
        $statisticsAnalysisView->assign('statistics_submited_complete', $questionnaire_StatisticNumbers[3] );

        return $statisticsAnalysisView->loadTemplate();
    }


    public function display() {
        $this->view->setTemplate ( 'wrapper_analysis' );
        $this->view->assign ( 'analysis_status_template', $this->loadStatus() );
        $this->view->assign ( 'analysis_statistics_template', $this->loadStatistics() );
        return $this->view->loadTemplate ();
    }


    // echo '<div class="questionaire_status">' . get_string ( 'questionaire_not_available', 'groupformation', $a ) . '</div>';
    // echo '<div class="questionaire_status">' . get_string ( 'questionaire_availability_info_future', 'groupformation', $a ) . '</div>';
    // echo '<div class="questionaire_status">' . get_string ( 'questionaire_not_available', 'groupformation', $a ) . '</div>';
    // echo '<div class="questionair_status">' . get_string ( 'questionaire_availability_info_from', 'groupformation', $a ) . '</div>';
}