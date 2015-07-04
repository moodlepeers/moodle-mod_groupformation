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
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// TODO einige Methoden noch nicht getestet
// defined('MOODLE_INTERNAL') || die(); -> template
// namespace mod_groupformation\moodle_interface;
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
		
		//speichert zu einem User seine groupid der Moodlegruppe
		public function saveUsersGroupID($user, $groupid){
			global $DB;
			$data = new stdClass();
			$data->groupformation = $this->groupformationid;
			$data->userid = $user;
			$data->groupid = $groupid;
			
			$DB->insert_record('groupformation_groupid_al', $data);
		}
		
		//löscht alle Einträge für diese groupformaton Instanz für den Fall, dass beim Gruppenbilden in Moodle etwas schiefgegangen ist
		public function deleteAllGroupid(){
			global $DB;
			
			$DB->delete_records('groupformation_groupid_al', array('groupformation' => $this->groupformationid));
		}
		
		//gibt ein array zurück, dass die anderen User der Gruppe enthält, in der der angegebene User enthalten ist
		public function getGroupMembers($userid){
			global $DB;
			
			$array = array();
			$records = $DB->get_records('groupformation_groupid_al', array('groupformation' => $this->groupformationid, 'groupid' => $this->getGroupid($userid)));
			foreach ( $records as $record ){
				$id = $record->userid;
				if ( $id != $userid ){
					$array[] = $id;
				}
			}
			
			return $array;
			
		}
		
		//gibt -1 zurück, wenn der User (noch) in keiner Gruppe ist, und die Gruppenid wenn er eingeteilt ist
		public function haveGroup($userid){
			global $DB;
			$id = -1;
			$count = $DB->count_records('groupformation_groupid_al', array('groupformation' => $this->groupformationid, 'userid' => $userid));
			if ( $count == 1 ){
				$id = $this->getGroupid($userid);
			}
			return $id;
		}
		
		//gibt die id der Moodlegruppe zurück, in der der User eingeteilt ist
		private function getGroupid($userid){
			global $DB;
			
			return $DB->get_field('groupformation_groupid_al', 'groupid', array('groupformation' => $this->groupformationid, 'userid' => $userid));
		}
	}