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
 * interface betweeen DB and Plugin
 *
 * @package mod_groupformation
 * @author Nora Wester, Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once ($CFG->dirroot . '/group/lib.php');

class mod_groupformation_storage_manager_groups {
	private $groupformationid;
	
	/**
	 * Constructs storage manager for a specific groupformation
	 *
	 * @param unknown $groupformationid        	
	 */
	public function __construct($groupformationid) {
		$this->groupformationid = $groupformationid;
	}
	
	/**
	 * Resets moodle groupids
	 * 
	 * @param unknown $moodlegroupid
	 * @return boolean
	 */
	public function deleteMoodleGroupID($moodlegroupid) {
		global $DB;
		
		$record = $DB->get_record ( 'groupformation_groups', array (
				'groupformation' => $this->groupformationid, 'moodlegroupid'=>$moodlegroupid 
		) );
		
		$record->moodlegroupid = null;
		$record->created = 0;
		
		return $DB->update_record('groupformation_groups',	$record);
	}
	
	/**
	 * Saves moodlegroupid in database
	 * 
	 * @param unknown $groupid
	 * @param unknown $moodlegroupid
	 */
	public function saveMoodleGroupID($groupid, $moodlegroupid){
		global $DB;
		
		$record = $DB->get_record('groupformation_groups', array('groupformation'=>$this->groupformationid,'id'=>$groupid));
		$record->moodlegroupid = $moodlegroupid;
		$record->created = 1;
		
		return $DB->update_record('groupformation_groups', $record);
	}
	
	/**
	 * Returns groupname
	 * 
	 * @param unknown $userid
	 * @return mixed
	 */
	public function getGroupName($userid){
		global $DB;
		$groupname = $DB->get_field('groupformation_groups', 'groupname', array('groupformation'=>$this->groupformationid,'userid'=>$userid)); 
		return $groupname;
	}
	
	/**
	 * Returns members (userids) of group of user
	 *
	 * @param integer $userid        	
	 * @return multitype:unknown
	 */
	public function getGroupMembers($userid) {
		global $DB;
		
		$array = array ();
		$groupid = $this->getGroupID ( $userid );
		$records = $DB->get_records ( 'groupformation_group_users', array (
				'groupformation' => $this->groupformationid,
				'groupid' => $groupid 
		) );
		foreach ( $records as $record ) {
			$id = $record->userid;
			if ($id != $userid) {
				$array [] = $id;
			}
		}
		
		return $array;
	}
	
	/**
	 * Returns whether user has a group or not
	 * TODO @Nora - return only true/false and optional query param 
	 * to ask if in moodle created or just in groupal generated
	 *
	 * @param unknown $userid        	
	 * @return Ambigous <number, mixed>
	 */
	public function hasGroup($userid, $moodlegroup = false) {
		global $DB;
		if ($moodlegroup)
			$count = $DB->count_records ( 'groupformation_groups', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userid, 'created' => 1
			) );
		else
			$count = $DB->count_records ( 'groupformation_groups', array (
					'groupformation' => $this->groupformationid,
					'userid' => $userid
			) );
		return ($count == 1);
	}
	
	/**
	 * Returns groupid for user
	 *
	 * @param integer $userid        	
	 * @return mixed
	 */
	public function getGroupID($userid) {
		global $DB;
		
		return $DB->get_field ( 'groupformation_groups', 'id', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userid 
		) );
	}
}