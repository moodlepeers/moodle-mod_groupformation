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

/**
 * Controller for grouping view
 *
 * @package mod_groupformation
 * @author MoodlePeers
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');

require_once ($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/userid_filter.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');
require_once ($CFG->dirroot . '/mod/groupformation/locallib.php');
class mod_groupformation_grouping_controller {
	private $groupformationid;
	private $cmid;
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
	 * @param int $groupformationid        	
	 */
	public function __construct($groupformationid, $cmid = null) {
		$this->groupformationid = $groupformationid;
		$this->cmid = $cmid;
		
		$this->store = new mod_groupformation_storage_manager ( $groupformationid );
		
		$this->groups_store = new mod_groupformation_groups_manager ( $groupformationid );
		
		$this->view = new mod_groupformation_template_builder ();
		
		$this->groups = $this->groups_store->get_generated_groups ();
		
		$this->job = mod_groupformation_job_manager::get_job ( $this->groupformationid );
		
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
		groupformation_info ( $USER->id, $this->groupformationid, 'groupal job queued by course manager/teacher' );
		
		$users = $this->handle_complete_questionaires ();
		mod_groupformation_job_manager::set_job ( $this->job, "waiting", true );
		$this->determine_status ();
		
		$context = groupformation_get_context ( $this->groupformationid );
		$enrolled_users = get_enrolled_users ( $context, 'mod/groupformation:onlystudent' );
		
		foreach ( $enrolled_users as $key => $user ) {
			groupformation_set_activity_completion ( $course, $cm, $user->id );
		}
		
		return $users;
	}
	
	/**
	 * POST action to abort current waiting or running job
	 */
	public function abort() {
		global $USER;
		
		// logging
		groupformation_info ( $USER->id, $this->groupformationid, 'groupal job aborted by course manager/teacher' );
		
		mod_groupformation_job_manager::set_job ( $this->job, "aborted", false, false );
		$this->determine_status ();
	}
	
	/**
	 * POST action to adopt groups to moodle
	 */
	public function adopt() {
		global $USER;
		
		// logging
		groupformation_info ( $USER->id, $this->groupformationid, 'groupal job results adopted to moodle groups by course manager/teacher' );
		
		mod_groupformation_group_generator::generate_moodle_groups ( $this->groupformationid );
		$this->determine_status ();
	}
	
	/**
	 * POST action to delete generated and/or adopted groups (moodle groups)
	 */
	public function delete() {
		global $USER;
		
		// logging
		groupformation_info ( $USER->id, $this->groupformationid, 'groupal job results deleted by course manager/teacher' );
		
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
		$this->view->setTemplate ( 'wrapper_grouping' );
		$this->view->assign ( 'grouping_title', $this->store->getName () );
		$this->view->assign ( 'grouping_settings', $this->load_settings () );
		$this->view->assign ( 'grouping_statistics', $this->load_statistics () );
		$this->view->assign ( 'grouping_incomplete_groups', $this->load_incomplete_groups () );
		$this->view->assign ( 'grouping_generated_groups', $this->load_generated_groups () );
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
		$settingsGroupsView->setTemplate ( 'grouping_settings' );
		
		switch ($this->view_state) {
			case 0 :
				// zweiter Parameter des Array gibt an, ob wichtiger Hinweis (1) oder nicht (0)
				$settingsGroupsView->assign ( 'status', array (
						get_string ( 'statusGrupping0', 'groupformation' ),
						0 
				) );
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_start', 'groupformation' ) 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_delete', 'groupformation' ) 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_adopt', 'groupformation' ) 
						) 
				) );
				
				break;
			
			case 1 :
				$settingsGroupsView->assign ( 'status', array (
						get_string ( 'statusGrupping1', 'groupformation' ),
						0 
				) );
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => '',
								'text' => get_string ( 'grouping_start', 'groupformation' ) 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_delete', 'groupformation' ) 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_adopt', 'groupformation' ) 
						) 
				) );
				
				break;
			
			case 2 :
				$settingsGroupsView->assign ( 'status', array (
						get_string ( 'statusGrupping2', 'groupformation' ),
						1 
				) );
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'abort',
								'value' => '',
								'state' => '',
								'text' => get_string ( 'grouping_abort', 'groupformation' ) 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_delete', 'groupformation' ) 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_adopt', 'groupformation' ) 
						) 
				) );
				
				$settingsGroupsView->assign ( 'emailnotifications', $this->store->get_email_setting() );
				break;
			
			case 3 :
				$settingsGroupsView->assign ( 'status', array (
						get_string ( 'statusGrupping3', 'groupformation' ),
						1 
				) );
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_start', 'groupformation' ) 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_delete', 'groupformation' ) 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_adopt', 'groupformation' ) 
						) 
				) );
				
				break;
			
			case 4 :
				$settingsGroupsView->assign ( 'status', array (
						get_string ( 'statusGrupping4', 'groupformation' ),
						0 
				) );
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_start', 'groupformation' ) 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => '',
								'text' => get_string ( 'grouping_delete', 'groupformation' ) 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => '',
								'text' => get_string ( 'grouping_adopt', 'groupformation' ) 
						) 
				) );
				
				break;
			
			case 5 :
				$settingsGroupsView->assign ( 'status', array (
						get_string ( 'statusGrupping5', 'groupformation' ),
						0 
				) );
				$settingsGroupsView->assign ( 'buttons', array (
						'button1' => array (
								'type' => 'submit',
								'name' => 'start',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_start', 'groupformation' ) 
						),
						'button2' => array (
								'type' => 'submit',
								'name' => 'delete',
								'value' => '',
								'state' => '',
								'text' => get_string ( 'moodlegrouping_delete', 'groupformation' ) 
						),
						'button3' => array (
								'type' => 'submit',
								'name' => 'adopt',
								'value' => '',
								'state' => 'disabled',
								'text' => get_string ( 'grouping_adopt', 'groupformation' ) 
						) 
				) );
				break;
			
			case 'default' :
			default :
				
				break;
		}
		
		$userfilter = new mod_groupformation_userid_filter ( $this->groupformationid );
		
		$count = count ( $userfilter->getCompletedIDs () );
		$count += count ( $userfilter->getNoneCompletedIDs () );
		
		$context = $PAGE->context;
		$count = count ( get_enrolled_users ( $context, 'mod/groupformation:onlystudent' ) );
		
		$settingsGroupsView->assign ( 'student_count', $count );
		$settingsGroupsView->assign ( 'cmid', $this->cmid);
		$settingsGroupsView->assign ( 'onlyactivestudents', $this->store->get_grouping_setting() );

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
			
			$statisticsView->setTemplate ( 'grouping_statistics' );
			
			$statisticsView->assign ( 'performance', $this->job->performance_index );
			$statisticsView->assign ( 'numbOfGroups', count ( $this->groups_store->get_generated_groups () ) );
			$statisticsView->assign ( 'maxSize', $this->store->getGroupSize () );
		} else {
			$statisticsView->setTemplate ( 'grouping_no_data' );
			$statisticsView->assign ( 'grouping_no_data', get_string ( 'no_data_to_display', 'groupformation' ) );
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
			
			$incompleteGroupsView->setTemplate ( 'grouping_incomplete_groups' );
			
			foreach ( $this->incomplete_groups as $key => $value ) {
				
				$incompleteGroupsView->assign ( $key, array (
						'groupname' => $value->groupname,
						'scrollTo_group' => $this->get_scroll_to_link ( $key ),
						'grouplink' => $this->get_group_link ( $value->moodlegroupid ),
						'groupsize' => $value->groupsize 
				) );
			}
		} else {
			$incompleteGroupsView->setTemplate ( 'grouping_no_data' );
			$incompleteGroupsView->assign ( 'grouping_no_data', get_string ( 'no_data_to_display', 'groupformation' ) );
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
			
			$generatedGroupsView->setTemplate ( 'grouping_generated_groups' );
			
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
			$generatedGroupsView->setTemplate ( 'grouping_no_data' );
			$generatedGroupsView->assign ( 'grouping_no_data', get_string ( 'no_data_to_display', 'groupformation' ) );
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
			$url = $CFG->wwwroot . '/user/view.php?id=' . $user->userid . '&course=' . $COURSE->id;
			
			$userName = $user->userid;
			$user_record = mod_groupformation_util::get_user_record ( $user->userid );
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
	 * @param int $groupid        	
	 * @return array
	 */
	private function get_group_link($groupid) {
		$link = array ();
		if ($this->groups_created) {
			$url = new moodle_url ( '/group/members.php', array (
					'group' => $groupid 
			) );
			$link [] = $url;
			$link [] = '';
		} else {
			
			$link [] = '';
			$link [] = 'disabled';
		}
		return $link;
	}
	
	/**
	 * Handles complete questionaires (userids) and sets them to completed/commited
	 */
	private function handle_complete_questionaires() {
		$userFilter = new mod_groupformation_userid_filter ( $this->groupformationid );
		
		$users = $userFilter->getCompletedIDs ();
		
		foreach ( $users as $user ) {
			$this->store->setCompleted ( $user, true );
		}
		
		return $users;
	}
}

?>