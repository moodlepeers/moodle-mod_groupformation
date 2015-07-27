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

class mod_groupformation_groups_manager {
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
	 * Creats user-group instance in DB
	 *
	 * @param integer $groupformationid
	 * @param integer $userid
	 * @param unknown $usergroup
	 * @param unknown $idmap
	 */
	public function assign_user_to_group($groupformationid,$userid,$groupalid,$idmap){
		global $DB;
	
		$record = new stdClass();
		$record->groupformation = $groupformationid;
		$record->userid = $userid;
		$record->groupid = $idmap[$groupalid];
	
		return $DB->insert_record('groupformation_group_users', $record);
	}
	
	/**
	 * Creates group instance in DB
	 *
	 * @param integer $groupalid
	 * @param string $name
	 * @param integer $groupformationid
	 * @return Ambigous <boolean, number>
	 */
	public function create_group($groupalid, $group, $name, $groupformationid,$flags){
		global $DB;
	
		$record = new stdClass();
		$record->groupformation = $groupformationid;
		$record->moodlegroupid = null;
		$record->groupname = $name;
		$record->performance_index = $group['gpi'];
		$record->groupal = $flags['groupal'];
		$record->random = $flags['random'];
		$record->mrandom = $flags['random'];
		$record->created = $flags['created'];
	
		$id = $DB->insert_record('groupformation_groups', $record);
	
		return $id;
	}
	
	/**
	 * Returns whether groups are created in moodle or not
	 * 
	 * @param unknown $groupformationID
	 */
	public function groupsCreated($groupformationID){
		global $DB;
		$records = $DB->get_records('groupformation_groups',array('groupformation'=>$this->groupformationid));
		
		foreach($records as $key=>$record){
			if ($record->created == 1)
				return true;
		}
		
		return false;
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
		$groupid = $DB->get_field('groupformation_group_users', 'groupid', array('groupformation'=>$this->groupformationid,'userid'=>$userid)); 
		
		return $DB->get_field ( 'groupformation_groups', 'groupname', array (
				'groupformation' => $this->groupformationid,
				'id' => $groupid 
		) );
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
		$count = $DB->count_records ( 'groupformation_group_users', array (
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
		
		return $DB->get_field ( 'groupformation_group_users', 'groupid', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userid 
		) );
	}
	
	/**
	 * Returns whether groups are build in moodle or just generated by GroupAL
	 * 
	 * @return boolean
	 */
	public static function isNotBuild($groupformationid){
		global $DB;
		$table = 'groupformation_groups';
		$count = $DB->count_records($table, array('groupformation' => $groupformationid, 'created' => 1));
		return $count == 0;
	}
	
	
	/**
	 * Returns groups which are generated by groupal
	 *
	 * @return mixed
	 */
	
	public function getGeneratedGroups(){
		global $DB;
		return $DB->get_records ( 'groupformation_groups', array (
				'groupformation' => $this->groupformationid
		), 'id', 'id, groupname,performance_index,moodlegroupid' );
	}
	
	/**
	 * Returns all users from groups which are generated by groupal
	 *
	 * @return mixed
	 */
	
	public function getUsersForGeneratedGroup($groupid){
		global $DB;
		return $DB->get_records('groupformation_group_users',
				array('groupformation'=>$this->groupformationid, 'groupid' => $groupid),null,'userid');
	
	}
	
	/**
	 * Deletes all generated group
	 */
	public function deleteGeneratedGroups(){
		global $DB;
		$records = $DB->get_records('groupformation_groups',array('groupformation'=>$this->groupformationid));
		
		foreach($records as $key=>$record){
			if ($record->created == 1)
				groups_delete_group($record->moodlegroupid);
		}
		$DB->delete_records('groupformation_groups',array('groupformation'=>$this->groupformationid));
		$DB->delete_records('groupformation_group_users',array('groupformation'=>$this->groupformationid));
	}
}