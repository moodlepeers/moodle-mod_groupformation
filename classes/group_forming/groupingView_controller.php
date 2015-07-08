<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/group_forming/group_forming_view.php');

class mod_groupformation_groupingView_controller {
	
    //state of the controller
    private $viewState = 0;
    private $groups = array();
    private $incompleteGroups = array();

    private $store = NULL;
    private $job = NULL;
    private $view = NULL;

    private $job_status;
    private $job_status_options;

    private $surveyState;

    /* generierte gruppen ins moodle übernommen?
     */
    private $groupsAddopted;

    private $test;
    
    public function __construct($groupformationID){
        $this->store = new mod_groupformation_storage_manager($groupformationID);
        $this->view = new mod_groupformation_group_forming_view();

        $this->groups = $this->store->getGeneratedGroups();

        // status job
        $data = new mod_groupformation_data();
        $this->job_status_options = $data->get_job_status_options();
        $this->job = mod_groupformation_job_manager::get_job($groupformationID);
        $this->job_status = mod_groupformation_job_manager::get_status($this->job);

        //survey status
        $this->surveyState = $this->store->isQuestionaireAvailable();
        //$this->surveyState = 'false';

        // TODO groupsAdopted soll aus db abgefragt werden
        $this->groupsAdopted = 0;

        /* Survey läuft noch */
        if($this->surveyState == 'true'){
            $this->viewState = 0;
        }
        /* Survey beendet, aber keine Gruppen generiert*/
        //elseif($this->surveyState == false && !(isset($this->groups) && !empty($this->groups) ))
        elseif($this->job_status == $this->job_status_options[0])
        {
            $this->viewState = 1;
        }
        /* Gruppenbildung läuft */
        //elseif($this->surveyState == false && 0)
        elseif($this->job_status == $this->job_status_options[1])
        {
            $this->viewState = 2;
        }
        /* Gruppen generiert, aber nicht ins Moodle integriert */
        //elseif (isset($this->groups) && !empty($this->groups) && $this->groupsAddopted == 0)
        elseif($this->job_status == $this->job_status_options[4] && $this->groupsAddopted == 0)
        {
            $this->viewState = 3;
        }
        /* Gruppen generiert und ins Moodle integriert */
        else
        {
            $this->viewState = 4;
        }
    }

    /**
     * 
     */
    public function start(){
        $this->test = 'gestartet';
    }

    /**
     * 
     */
    public function abort(){
        $this->test = 'abgebrochen';
    }

    /**
     * 
     */
    public function adopt(){
        $this->test = 'adoptiert';
    }

    /**
     * 
     */
    public function delete(){
        $this->test = 'gelöscht';
    }

    /**
     * 
     */
    public function showTest(){
        return var_dump($this->test);
    }

    /** sets the buttons of groupform-settings
     * @return string
     */
    private function loadSettings(){
        $settingsGroupsView = new mod_groupformation_group_forming_view();
        $settingsGroupsView->setTemplate('groupingView_settings');

        switch($this->viewState){
            case 0:
                $settingsGroupsView->assign(
                    'buttons', array(
                        'button1' => array(
                            'type' => 'submit',
                            'name' => 'start',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppenbildung starten'
                        ),
                        'button2' => array(
                            'type' => 'submit',
                            'name' => 'delete',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppen l&ouml;schen'
                        ),
                        'button3' => array(
                            'type' => 'submit',
                            'name' => 'adopt',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppe übernehmen'
                        ),
                    )
                );

                break;

            case 1:
                $settingsGroupsView->assign(
                    'buttons', array(
                        'button1' => array(
                            'type' => 'submit',
                            'name' => 'start',
                            'value' => '',
                            'state' => '',
                            'text' => 'Gruppenbildung starten'
                        ),
                        'button2' => array(
                            'type' => 'submit',
                            'name' => 'delete',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppen l&ouml;schen'
                        ),
                        'button3' => array(
                            'type' => 'submit',
                            'name' => 'adopt',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppe übernehmen'
                        ),
                    )
                );

                break;

            case 2:
                $settingsGroupsView->assign(
                    'buttons', array(
                        'button1' => array(
                            'type' => 'submit',
                            'name' => 'abort',
                            'value' => '',
                            'state' => '',
                            'text' => 'Gruppenbildung abbrechen'
                        ),
                        'button2' => array(
                            'type' => 'submit',
                            'name' => 'delete',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppen l&ouml;schen'
                        ),
                        'button3' => array(
                            'type' => 'submit',
                            'name' => 'adopt',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppe übernehmen'
                        ),
                    )
                );

                break;

            case 3:
                $settingsGroupsView->assign(
                    'buttons', array(
                        'button1' => array(
                            'type' => 'submit',
                            'name' => 'start',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppenbildung starten'
                        ),
                        'button2' => array(
                            'type' => 'submit',
                            'name' => 'delete',
                            'value' => '',
                            'state' => '',
                            'text' => 'Gruppen l&ouml;schen'
                        ),
                        'button3' => array(
                            'type' => 'submit',
                            'name' => 'adopt',
                            'value' => '',
                            'state' => '',
                            'text' => 'Gruppe übernehmen'
                        ),
                    )
                );

                break;

            case 4:
                $settingsGroupsView->assign(
                    'buttons', array(
                        'button1' => array(
                            'type' => 'submit',
                            'name' => 'start',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppenbildung starten'
                        ),
                        'button2' => array(
                            'type' => 'submit',
                            'name' => 'delete',
                            'value' => '',
                            'state' => '',
                            'text' => 'Gruppen l&ouml;schen'
                        ),
                        'button3' => array(
                            'type' => 'submit',
                            'name' => 'adopt',
                            'value' => '',
                            'state' => 'disabled',
                            'text' => 'Gruppe übernehmen'
                        ),
                    )
                );

                break;

            case 'default':
            default:

                break;
        }
        return $settingsGroupsView->loadTemplate();
    }
    
    /**
     * @return string
     */
    private function loadStatistics(){
        $statisticsView = new mod_groupformation_group_forming_view();

        if($this->viewState == 3 || $this->viewState == 4) {
            // TODO get statistics
            $statisticsView->setTemplate('groupingView_statistics');

            $statisticsView->assign('performance', '');
            $statisticsView->assign('numbOfGroups', '');
            $statisticsView->assign('maxSize', '');

        }else{
            $statisticsView->setTemplate('groupingView_noData');
            $statisticsView->assign('groupingView_noData', 'keine Daten vorhanden');
        }
        return $statisticsView->loadTemplate();
    }
    
    /** 
     * assign incompletegroups-data to template
     * 
     * @return string
     */
    private function loadIncompleteGroups(){

        $incompleteGroupsView = new mod_groupformation_group_forming_view();

        if($this->viewState == 3 || $this->viewState == 4) {
            $this->setIncompleteGroups();

            $incompleteGroupsView->setTemplate('groupingView_incompleteGroups');

            foreach($this->incompleteGroups as $key=>$value) {

                $incompleteGroupsView->assign(
                    $key, array(
                        'groupname' => $value->groupname,
                        'scrollTo_group' => $this->scrollToGoupLink($key),
                        'groupsize' => $value->groupsize )
                );
            }
        }
        else{
            $incompleteGroupsView->setTemplate('groupingView_noData');
            $incompleteGroupsView->assign('groupingView_noData', 'keine Daten vorhanden');
        }
        return $incompleteGroupsView->loadTemplate();
    }
    
    /** 
     * Returns link for scrollTo function
     * 
     * @param $groupID
     * @return string
     */
    private function scrollToGoupLink($groupID){
        //TODO function to scroll not implemented; return placeholder
        return '#' . $groupID;
    }
    
    /** 
     * Set the array with incompleted groups
     */
    private function setIncompleteGroups(){

        $maxSize = $this->store->getGroupSize();

        foreach($this->groups as $key=>$value) {
            $usersIDs = $this->store->getUsersFromGeneratedGroups($key);
            $size = count($usersIDs);
            if($size < $maxSize){
                $a = (array) $this->groups[$key];
                $a['groupsize'] = $size;
                $this->incompleteGroups[$key] = (object) $a;
            }
        }
    }
    
    /** 
     * Assign groups-data to template
     * 
     * @return string
     */
    private function loadGeneratedGroups(){
        $generatedGroupsView = new mod_groupformation_group_forming_view();

        if($this->viewState == 3 || $this->viewState == 4) {

            $generatedGroupsView->setTemplate('groupingView_generatedGroups');

            foreach($this->groups as $key=>$value) {

                $generatedGroupsView->assign(
                    $key, array(
                        'groupname' => $value->groupname,
                        'groupquallity' => '0.74',
                        'grouplink' => $this->linkToGoup($key),
                        'group_members' => $this->getGroupMembers($key)));
            }
        }
        else{
            $generatedGroupsView->setTemplate('groupingView_noData');
            $generatedGroupsView->assign('groupingView_noData', 'keine Daten vorhanden');
        }
        return $generatedGroupsView->loadTemplate();
    }
    
    /** 
     * Gets the name and moodle-link of groupmembers
     * 
     * @param $groupID
     * @return array
     */
    private function getGroupMembers($groupID){
        $usersIDs = $this->store->getUsersFromGeneratedGroups($groupID);
        $groupMembers = array();

        foreach ($usersIDs as $user) {
            //TODO: get link of user and username with the userID
            $userName = $user->userid;
            $userLink = 'userlink';

            $groupMembers[$user->userid] = [
                'name' => $userName,
                'link' => $userLink
            ];
        }
        return $groupMembers;
    }
    
    /** 
     * Get the moodle-link to group and set state of the link(enabled || disabled)
     * 
     * @param $groupID
     * @return array
     */
    private function linkToGoup($groupID)
    {
        $link = array();
        if($this->viewState == 4){
            // generate link, button enabled
            //TODO: get link to group with the groupID
            return $link = ['#'.$groupID, ''];
            //return '<a href="#"><button class="gf_button gf_button_pill gf_button_tiny">zur Moodle Gruppenansicht</button></a>';
        }
        else
        {   //no link, button disabled
            return $link = ['', 'disabled'];
            //return '<a href="#"><button class="gf_button gf_button_pill gf_button_tiny" disabled>zur Moodle Gruppenansicht</button></a>';
        }

    }
    
    /** 
     * Generate and return the HTMl Page with templates and data
     * 
     * @return string
     */
    public function display(){
        $this->view->setTemplate('wrapper_groupingView');
        $this->view->assign('groupingView_settings', $this->loadSettings());
        $this->view->assign('groupingView_statistic', $this->loadStatistics());
        $this->view->assign('groupingView_incompleteGroups', $this->loadIncompleteGroups());
        $this->view->assign('groupingView_generatedGroups', $this->loadGeneratedGroups());
        return $this->view->loadTemplate();

    }
}


?>