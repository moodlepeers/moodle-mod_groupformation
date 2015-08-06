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

require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/userid_filter.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');

class mod_groupformation_grouping_controller {
	private $groupformationID;
	private $view_state = 0;
	private $groups = array ();
	private $incomplete_groups = array ();
	private $store = NULL;
	private $groups_store = NULL;
	private $job = NULL;
	private $view = NULL;
	private $groups_created;
	
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
		
		$this->groups = $this->groups_store->get_generated_groups ();
		
		$this->job = mod_groupformation_job_manager::get_job ( $this->groupformationID );
		
		$this->determine_status ();
	}
	
	/**
	 * Determines status of grouping_view
	 */
	public function determine_status() {
		$activity_state = $this->store->isQuestionaireAvailable ();
		
		$job_status = mod_groupformation_job_manager::get_status ( $this->job );
		
		$this->groups_created = $this->groups_store->groups_created ();
		
		if ($activity_state == 'true') {
			/* Survey läuft noch */
			$this->view_state = 0;
		} elseif ($job_status == 'ready') {
			/* Survey beendet, aber keine Gruppen generiert */
			$this->view_state = 1;
		} elseif ($job_status == 'waiting' || $job_status == 'started') {
			/* Gruppenbildung läuft */
			$this->view_state = 2;
		} elseif ($job_status == 'aborted') {
			/* Gruppen generiert, aber nicht ins Moodle integriert */
			$this->view_state = 3;
		} elseif ($job_status == 'done' && ! $this->groups_created) {
			// Moodlegroups are created
			$this->view_state = 4;
		} elseif ($job_status == 'done' && $this->groups_created) {
			// currently everything block til job is aborted and reset by cron
			$this->view_state = 5;
		}
	}
	
	/**
	 * POST action to start job, sets it to 'waiting'
	 */
	public function start($course, $cm) {
		global $USER;
		
		// logging
		groupformation_info ( $USER->id, $this->groupformationID, 'groupal job queued by course manager/teacher' );
		
		$users = $this->handle_complete_questionaires ();
		mod_groupformation_job_manager::set_job ( $this->job, "waiting", true );
		$this->determine_status ();
		
		foreach ( $users as $userid ) {
			$completion = new completion_info ( $course );
			$completion->set_module_viewed ( $cm, $userid );
		}
		
		return $users;
	}
	
	/**
	 * POST action to abort current waiting or running job
	 */
	public function abort() {
		global $USER;
		
		// logging
		groupformation_info ( $USER->id, $this->groupformationID, 'groupal job aborted by course manager/teacher' );
		
		mod_groupformation_job_manager::set_job ( $this->job, "aborted", false, false );
		$this->determine_status ();
	}
	
	/**
	 * POST action to adopt groups to moodle
	 */
	public function adopt() {
		global $USER;
		
		// logging
		groupformation_info ( $USER->id, $this->groupformationID, 'groupal job results adopted to moodle groups by course manager/teacher' );
		
		mod_groupformation_group_generator::generate_moodle_groups ( $this->groupformationID );
		$this->determine_status ();
	}
	
	/**
	 * POST action to delete generated and/or adopted groups (moodle groups)
	 */
	public function delete() {
		global $USER;
		
		// logging
		groupformation_info ( $USER->id, $this->groupformationID, 'groupal job results deleted by course manager/teacher' );
		
		mod_groupformation_job_manager::set_job ( $this->job, "ready", false, true );
		$this->groups_store->delete_generated_groups ();
		$this->determine_status ();
	}
	
	/**
	 * Generate and return the HTMl Page with templates and data
	 *
	 * @return string
	 */
	public function display() {
		$this->determine_status ();
		$this->view->setTemplate ( 'wrapper_groupingView' );
        $this->view->assign ( 'groupingView_Title', $this->store->getName() );
		$this->view->assign ( 'groupingView_settings', $this->load_settings () );
		$this->view->assign ( 'groupingView_statistic', $this->load_statistics () );
		$this->view->assign ( 'groupingView_incompleteGroups', $this->load_incomplete_groups () );
		$this->view->assign ( 'groupingView_generatedGroups', $this->load_generated_groups () );
		return $this->view->loadTemplate ();
	}
	
	/**
	 * sets the buttons of grouping settings
	 *
	 * @return string
	 */
	private function load_settings() {
		global $PAGE;
		$settingsGroupsView = new mod_groupformation_template_builder ();
		$settingsGroupsView->setTemplate ( 'groupingView_settings' );
		
		switch ($this->view_state) {
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
		
		$userfilter = new mod_groupformation_userid_filter ( $this->groupformationID );
		
		$count = count ( $userfilter->getCompletedIDs () );
		$count += count ( $userfilter->getNoneCompletedIDs () );
		
		$context = $PAGE->context;
		$count = count ( get_enrolled_users ( $context, 'mod/groupformation:onlystudent' ) );
		
		$settingsGroupsView->assign ( 'student_count', $count );
		
		return $settingsGroupsView->loadTemplate ();
	}
	
	/**
	 * Loads statistics
	 *
	 * @return string
	 */
	private function load_statistics() {
		$statisticsView = new mod_groupformation_template_builder ();
		
		if ($this->view_state == 4 || $this->view_state == 5) {
			// TODO get statistics
			$statisticsView->setTemplate ( 'groupingView_statistics' );
			
			$statisticsView->assign ( 'performance', $this->job->performance_index );
			$statisticsView->assign ( 'numbOfGroups', count ( $this->groups_store->get_generated_groups () ) );
			$statisticsView->assign ( 'maxSize', $this->store->getGroupSize () );
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
	private function load_incomplete_groups() {
		$incompleteGroupsView = new mod_groupformation_template_builder ();
		
		if ($this->view_state == 4 || $this->view_state == 5) {
			$this->set_incomplete_groups ();
			
			$incompleteGroupsView->setTemplate ( 'groupingView_incompleteGroups' );
			
			foreach ( $this->incomplete_groups as $key => $value ) {
				
				$incompleteGroupsView->assign ( $key, array (
						'groupname' => $value->groupname,
						'scrollTo_group' => $this->get_scroll_to_link ( $key ),
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
	private function get_scroll_to_link($groupID) {
		// TODO function to scroll not implemented; return placeholder
		return '#' . $groupID;
	}
	
	/**
	 * Sets the array with incompleted groups
	 */
	private function set_incomplete_groups() {
		$maxSize = $this->store->getGroupSize ();
		
		foreach ( $this->groups as $key => $value ) {
			$usersIDs = $this->groups_store->get_users_for_generated_group ( $key );
			$size = count ( $usersIDs );
			if ($size < $maxSize) {
				$a = ( array ) $this->groups [$key];
				$a ['groupsize'] = $size;
				$this->incomplete_groups [$key] = ( object ) $a;
			}
		}
	}
	
	/**
	 * Assign groups-data to template
	 *
	 * @return string
	 */
	private function load_generated_groups() {
		$generatedGroupsView = new mod_groupformation_template_builder ();
		
		if ($this->view_state == 4 || $this->view_state == 5) {
			
			$generatedGroupsView->setTemplate ( 'groupingView_generatedGroups' );
			
			foreach ( $this->groups as $key => $value ) {
				
				$gpi = (is_null ( $value->performance_index )) ? '-' : $value->performance_index;
				
				$generatedGroupsView->assign ( $key, array (
						'groupname' => $value->groupname,
						'groupquallity' => $gpi,
						'grouplink' => $this->get_group_link ( $value->moodlegroupid ),
						'group_members' => $this->get_group_members ( $key ) 
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
	private function get_group_members($groupID) {
		global $CFG, $COURSE, $USER;
		$usersIDs = $this->groups_store->get_users_for_generated_group ( $groupID );
		$groupMembers = array ();
		
		foreach ( $usersIDs as $user ) {
			// TODO: get link of user and username with the userID
			$url = $CFG->wwwroot . '/user/view.php?id=' . $user->userid . '&course=' . $COURSE->id;
			
			$userName = $user->userid;
			$user_record = $this->store->getUser ( $user->userid );
			if (! is_null ( $user_record ))
				$userName = fullname ( $user_record );
			
			if (! (strlen ( $userName ) > 2)) {
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
	private function get_group_link($groupID) {
		global $COURSE, $CFG;
		$link = array ();
		if ($this->groups_created) {
			$url = new moodle_url ( '/group/members.php', array (
					'group' => $groupID 
			) ); // ='.$groupID;
			return $link = [ 
					$url,
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
	 * Handles complete questionaires (userids) and sets them to completed/commited
	 */
	private function handle_complete_questionaires() {
		$userFilter = new mod_groupformation_userid_filter ( $this->groupformationID );
		
		$users = $userFilter->getCompletedIDs ();
		
		foreach ( $users as $user ) {
			$this->store->setCompleted ( $user, true );
		}
		
		return $users;
	}
	
}

?>