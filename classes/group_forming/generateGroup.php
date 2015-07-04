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

/**
 * Create and allocate users to groups
 * This code is extracted out of /group/autogroup.php
 *
 * @package mod_groupformation
 * @copyright Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once ('../config.php');
require_once ('lib.php');
// ($CFG->dirroot.'/group/lib.php');
require_once ($CFG->dirroot . '/group/lib.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager_groups.php');
class mod_groupformation_moodle_group_generator {
	
	// array aus arrays -> jedes einzelarray steht f�r eine Gruppe || Inahlt sind die jeweiligen Userids
	private $store;
	
	/**
	 * Constructs instance
	 *
	 * @param unknown $groupformationid        	
	 */
	public function __construct($groupformationid) {
		$this->store = new mod_groupformation_storage_manager_groups ( $groupformationid );
	}
	
	/**
	 * Generates moodle groups and sets ids in groupal generated groups
	 *
	 * @param unknown $groupal_groups        	
	 */
	public function generateMoodleGroups($groupal_groups) {
		global $COURSE;
		
		// TODO @Nora
		// Du musst beim Erstellen von Moodle-Gruppen annehmen,
		// dass es bereits GroupalGruppen mit Usern gibt (fiktiv; gespeichert
		// in Tabelle groupformation_groups), diesen haben daher eine ID und schon den Namen
		// $groupal_groups : array(key=>value) mit key aus groupalgroupids 
		// (Ids der Tabelle groupformation_groups) und values sind userids
		
		$position = 0;
		$created_moodle_groups = array ();
		
		$error = '';
		$failed = false;
		
		// allocate the users
		foreach ( $groupal_groups as $groupid => $users ) {
			
			$groupname = $this->store->getGroupName ( $groupid );
			
			$parsed_groupname = groups_parse_name ( $groupname, $position );
			
			if (groups_get_group_by_name ( $COURSE->id, $parsed_groupname )) {
				$error = get_string ( 'groupnameexists', 'groupformation', $parsed_groupname );
				$failed = true;
				break;
			}
			
			// create group
			$new_moodlegroup = new stdClass ();
			$new_moodlegroup->courseid = $COURSE->id;
			$new_moodlegroup->name = $parsed_groupname;
			
			$moodlegroupid = groups_create_group ( $new_moodlegroup );
			
			$created_moodle_groups [] = $moodlegroupid;
			// put user into group
			foreach ( $users as $user ) {
				groups_add_member ( $moodlegroupid, $user );
			}
			
			// TODO @Nora: Erledigt
			$this->store->saveMoodleGroupID ( $groupid, $moodlegroupid );
			
			// Invalidate the course groups cache seeing as we've changed it.
			cache_helper::invalidate_by_definition ( 'core', 'groupdata', array (), array (
					$COURSE->id 
			) );
			
			if ($failed) {
				foreach ( $created_moodle_groups as $groupid => $moodlegroupid ) {
					
					groups_delete_group ( $moodlegroupid );
					// TODO @Nora Erledigt
					$this->store->deleteMoodleGroupID ( $moodlegroupid );
				}
			} else {
			}
			
			// for ($j=0; $j<$userpergrp; $j++) {
			// if (empty($users)) {
			// break 2;
			// }
			// $user = array_shift($users);
			// $groups[$i]['members'][$user->id] = $user;
			// }
		}
	}
	
	// if (isset($data->preview)) {
	// $table = new html_table();
	// $table->head = array(get_string('groupscount', 'groupformation', $numgrps));
	// $table->size = array('100%');
	// $table->align = array('left');
	// $table->width = '40%';
	// $table->data = array();
	
	// foreach ($groups as $group) {
	// $line = array();
	// if (groups_get_group_by_name($courseid, $group['name'])) {
	// $line[] = '<span class="notifyproblem">'.get_string('groupnameexists', 'groupformation', $group['name']).'</span>';
	// $error = get_string('groupnameexists', 'groupformation', $group['name']);
	// } else {
	// $line[] = $group['name'];
	// }
	// $table->data[] = $line;
	// }
	
	// $preview .= html_writer::table($table);
	
	// }

/**
 * NO GROUPING
 */
	
	// // Save the groups data
	// foreach ($groups as $key=>$group) {
	// if (groups_get_group_by_name($courseid, $group['name'])) {
	// $error = get_string('groupnameexists', 'groupformation', $group['name']);
	// $failed = true;
	// break;
	// }
	// $newgroup = new stdClass();
	// $newgroup->courseid = $data->courseid;
	// $newgroup->name = $group['name'];
	// $groupid = groups_create_group($newgroup);
	// $created_moodle_groups[] = $groupid;
	// foreach($group['members'] as $user) {
	// groups_add_member($groupid, $user->id);
	// }
	// }
}

