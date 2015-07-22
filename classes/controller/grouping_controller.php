<?php
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/userid_filter.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');
class mod_groupformation_grouping_controller {
	private $groupformationID;
	
	// state of the controller
	private $viewState = 0;
	private $groups = array ();
	private $incompleteGroups = array ();
	private $store = NULL;
	private $groups_store = NULL;
	private $job = NULL;
	private $view = NULL;
	private $job_status;
	private $surveyState;
	
	// generierte Gruppen als Moodlegruppen übernommen
	private $groupsAdopted;
	private $test;
	
	/**
	 * Creates an instance of grouping_controller for groupformation
	 * 
	 * @param unknown $groupformationID
	 */
	public function __construct($groupformationID) {
		$this->groupformationID = $groupformationID;
		$this->store = new mod_groupformation_storage_manager ( $groupformationID );
		
		$this->groups_store = new mod_groupformation_groups_manager ( $groupformationID );
		
		$this->view = new mod_groupformation_template_builder ();
		
		$this->groups = $this->groups_store->getGeneratedGroups ();
		
		$this->determineStatus ();
	}
	
	/**
	 * Determines status of grouping_view
	 */
	public function determineStatus() {
		$this->surveyState = $this->store->isQuestionaireAvailable ();
		
		// status job
		$this->job = mod_groupformation_job_manager::get_job ( $this->groupformationID );
		$this->job_status = mod_groupformation_job_manager::get_status ( $this->job );
		
		$this->groupsAdopted = $this->groups_store->groupsCreated ( $this->groupformationID );
		
		/* Survey läuft noch */
		if ($this->surveyState == 'true') {
			$this->viewState = 0;
		} /* Survey beendet, aber keine Gruppen generiert */
		// elseif($this->surveyState == false && !(isset($this->groups) && !empty($this->groups) ))
		elseif ($this->job_status == 'ready') {
			$this->viewState = 1;
		} /* Gruppenbildung läuft */
		// elseif($this->surveyState == false && 0)
		elseif ($this->job_status == 'waiting' || $this->job_status == 'started') {
			$this->viewState = 2;
		} /* Gruppen generiert, aber nicht ins Moodle integriert */
		// elseif (isset($this->groups) && !empty($this->groups) && $this->groupsAdopted == 0)
		elseif ($this->job_status == 'aborted') {
			$this->viewState = 3;
		} // Moodlegroups are created
elseif ($this->job_status == 'done' && ! $this->groupsAdopted) {
			$this->viewState = 4;
		} // currently everything block til job is aborted and reset by cron
elseif ($this->job_status == 'done' && $this->groupsAdopted) {
			$this->viewState = 5;
		}
	}
	
	/**
	 * POST action to start job, sets it to 'waiting'
	 */
	public function start() {
		$this->handle_complete_questionaires ();
		mod_groupformation_job_manager::set_job ( $this->job, "waiting", true );
		$this->determineStatus ();
	}
	
	/**
	 * POST action to abort current waiting or running job
	 */
	public function abort() {
		mod_groupformation_job_manager::set_job ( $this->job, "aborted", false, false );
		$this->determineStatus ();
	}
	
	/**
	 * POST action to adopt groups to moodle
	 */
	public function adopt() {
		mod_groupformation_group_generator::generateMoodleGroups ( $this->groupformationID );
		$this->determineStatus ();
	}
	
	/**
	 * POST action to delete generated and/or adopted groups (moodle groups)
	 */
	public function delete() {
		mod_groupformation_job_manager::set_job ( $this->job, "ready", false, true );
		$this->groups_store->deleteGeneratedGroups ();
		$this->determineStatus ();
	}
	
	/**
	 * sets the buttons of grouping settings
	 *
	 * @return string
	 */
	private function loadSettings() {
		$settingsGroupsView = new mod_groupformation_template_builder ();
		$settingsGroupsView->setTemplate ( 'groupingView_settings' );
		
		switch ($this->viewState) {
			case 0 :
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppenbildung starten' 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppen l&ouml;schen' 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppe übernehmen' 
						) 
				) );
				
				break;
			
			case 1 :
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => '',
								'text' => 'Gruppenbildung starten' 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppen l&ouml;schen' 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppe übernehmen' 
						) 
				) );
				
				break;
			
			case 2 :
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'abort',
								'value' => '',
								'state' => '',
								'text' => 'Gruppenbildung abbrechen' 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppen l&ouml;schen' 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppe &uuml;bernehmen' 
						) 
				) );
				
				break;
			
			case 3 :
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppenbildung starten' 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppenvorschlag l&ouml;schen' 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppenvorschlag &uuml;bernehmen' 
						) 
				) );
				
				break;
			
			case 4 :
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppenbildung starten' 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => '',
								'text' => 'Gruppenvorschlag l&ouml;schen' 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => '',
								'text' => 'Gruppenvorschlag übernehmen' 
						) 
				) );
				
				break;
			
			case 5 :
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppenbildung starten' 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => '',
								'text' => 'Moodle-Gruppen l&ouml;schen' 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => 'Gruppenvorschlag übernehmen' 
						) 
				) );
				
				break;
			
			case 'default' :
			default :
				
				break;
		}
		return $settingsGroupsView->loadTemplate ();
	}
	
	/**
	 * Loads statistics
	 *
	 * @return string
	 */
	private function loadStatistics() {
		$statisticsView = new mod_groupformation_template_builder ();
		
		if ($this->viewState == 4 || $this->viewState == 5) {
			// TODO get statistics
			$statisticsView->setTemplate ( 'groupingView_statistics' );
			
			$statisticsView->assign ( 'performance', '' );
			$statisticsView->assign ( 'numbOfGroups', '' );
			$statisticsView->assign ( 'maxSize', '' );
		} else {
			$statisticsView->setTemplate ( 'groupingView_noData' );
			$statisticsView->assign ( 'groupingView_noData', 'keine Daten vorhanden' );
		}
		return $statisticsView->loadTemplate ();
	}
	
	/**
	 * Assigns data about incomplete groups to template
	 *
	 * @return string
	 */
	private function loadIncompleteGroups() {
		$incompleteGroupsView = new mod_groupformation_template_builder ();
		
		if ($this->viewState == 4 || $this->viewState == 5) {
			$this->setIncompleteGroups ();
			
			$incompleteGroupsView->setTemplate ( 'groupingView_incompleteGroups' );
			
			foreach ( $this->incompleteGroups as $key => $value ) {
				
				$incompleteGroupsView->assign ( $key, array (
						'groupname' => $value->groupname,
						'scrollTo_group' => $this->scrollToGoupLink ( $key ),
						'groupsize' => $value->groupsize 
				) );
			}
		} else {
			$incompleteGroupsView->setTemplate ( 'groupingView_noData' );
			$incompleteGroupsView->assign ( 'groupingView_noData', 'keine Daten vorhanden' );
		}
		return $incompleteGroupsView->loadTemplate ();
	}
	
	/**
	 * Returns link for scrollTo function
	 *
	 * @param
	 *        	$groupID
	 * @return string
	 */
	private function scrollToGoupLink($groupID) {
		// TODO function to scroll not implemented; return placeholder
		return '#' . $groupID;
	}
	
	/**
	 * Sets the array with incompleted groups
	 */
	private function setIncompleteGroups() {
		$maxSize = $this->store->getGroupSize ();
		
		foreach ( $this->groups as $key => $value ) {
			$usersIDs = $this->groups_store->getUsersForGeneratedGroup ( $key );
			$size = count ( $usersIDs );
			if ($size < $maxSize) {
				$a = ( array ) $this->groups [$key];
				$a ['groupsize'] = $size;
				$this->incompleteGroups [$key] = ( object ) $a;
			}
		}
	}
	
	/**
	 * Assign groups-data to template
	 *
	 * @return string
	 */
	private function loadGeneratedGroups() {
		$generatedGroupsView = new mod_groupformation_template_builder ();
		
		if ($this->viewState == 4 || $this->viewState == 5) {
			
			$generatedGroupsView->setTemplate ( 'groupingView_generatedGroups' );
			
			foreach ( $this->groups as $key => $value ) {
				
				$generatedGroupsView->assign ( $key, array (
						'groupname' => $value->groupname,
						'groupquallity' => '0.74',
						'grouplink' => $this->linktToGroup ( $key ),
						'group_members' => $this->getGroupMembers ( $key ) 
				) );
			}
		} else {
			$generatedGroupsView->setTemplate ( 'groupingView_noData' );
			$generatedGroupsView->assign ( 'groupingView_noData', 'keine Daten vorhanden' );
		}
		return $generatedGroupsView->loadTemplate ();
	}
	
	/**
	 * Gets the name and moodle link of group members
	 *
	 * @param
	 *        	$groupID
	 * @return array
	 */
	private function getGroupMembers($groupID) {
		global $CFG, $COURSE, $USER;
		$usersIDs = $this->groups_store->getUsersForGeneratedGroup ( $groupID );
		$groupMembers = array ();
		
		foreach ( $usersIDs as $user ) {
			// TODO: get link of user and username with the userID
			$url = $CFG->wwwroot . '/user/view.php?id=' . $user->userid . '&course=' . $COURSE->id;
			
			$userName = $user->userid;
			$user_record = $this->store->getUser($user->userid);
			if (!is_null($user_record))
				$userName = fullname($user_record);
			
			if (!(strlen($userName)>2)){
				$userName = $user->userid;
			}
			$userLink = $url;
			
			$groupMembers [$user->userid] = [ 
					'name' => $userName,
					'link' => $userLink 
			];
		}
		return $groupMembers;
	}
	
	/**
	 * Get the moodle-link to group and set state of the link(enabled || disabled)
	 *
	 * @param
	 *        	$groupID
	 * @return array
	 */
	private function linktToGroup($groupID) {
		$link = array ();
		if ($this->viewState == 4) {
			// generate link, button enabled
			// TODO: get link to group with the groupID
			return $link = [ 
					'#' . $groupID,
					'' 
			];
			// return '<a href="#"><button class="gf_button gf_button_pill gf_button_tiny">zur Moodle Gruppenansicht</button></a>';
		} else { // no link, button disabled
			return $link = [ 
					'',
					'disabled' 
			];
			// return '<a href="#"><button class="gf_button gf_button_pill gf_button_tiny" disabled>zur Moodle Gruppenansicht</button></a>';
		}
	}
	
	/**
	 * Generate and return the HTMl Page with templates and data
	 *
	 * @return string
	 */
	public function display() {
		$this->determineStatus ();
		$this->view->setTemplate ( 'wrapper_groupingView' );
		$this->view->assign ( 'groupingView_settings', $this->loadSettings () );
		$this->view->assign ( 'groupingView_statistic', $this->loadStatistics () );
		$this->view->assign ( 'groupingView_incompleteGroups', $this->loadIncompleteGroups () );
		$this->view->assign ( 'groupingView_generatedGroups', $this->loadGeneratedGroups () );
		return $this->view->loadTemplate ();
	}
	
	/**
	 * Handles complete questionaires (userids) and sets them to completed/commited
	 */
	private function handle_complete_questionaires() {
		$userFilter = new mod_groupformation_userid_filter ( $this->groupformationID );
		
		// hole alle userids von komplett ausgefüllten Fragebögen und markiere diese als abgegeben
		$users = $userFilter->getCompletedIDs ();
		$this->store->setAllCommited ( $users );
		
		return $users;
	}
}

?>