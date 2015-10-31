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
 * Interface betweeen DB and Plugin
 *
 * @package mod_groupformation
 * @author Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

class mod_groupformation_user_manager {
	private $groupformationid;
	public function __construct($groupformationid = null) {
		$this->groupformationid = $groupformationid;
		$this->store = new mod_groupformation_storage_manager ( $groupformationid );
	}
	
	/**
	 * Returns array of records of table groupformation_started where completed is 1
	 *
	 * @return array
	 */
	public function get_completed($sorted_by = null, $fieldset = '*') {
		global $DB;
		return $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'completed' => 1 
		), $sorted_by, $fieldset );
	}
	
	/**
	 * Returns array of records of table groupformation_started where completed is 0
	 *
	 * @return array
	 */
	public function get_not_completed($sorted_by = null, $fieldset = '*') {
		global $DB;
		return $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'completed' => 0 
		), $sorted_by, $fieldset );
	}
	
	/**
	 * Returns array of records of table groupformation_started
	 *
	 * @return array
	 */
	public function get_started($sorted_by = null, $fieldset = '*') {
		global $DB;
		return $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid 
		), $sorted_by,$fieldset);
	}
	
	/**
	 * Returns array of records of table_groupformation_started if answer_count is equal to
	 * the total answer count for this activity
	 *
	 * @param string $sorted_by        	
	 * @param string $fieldset        	
	 * @return multitype:unknown
	 */
	public function get_completed_by_answer_count($sorted_by = null, $fieldset = '*') {
		global $DB;
		return $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid , 'answer_count' => $this->store->get_total_number_of_answers()
		), $sorted_by, $fieldset );
	}
	
	/**
	 * Returns array of records of table_groupformation_started if answer_count is not equal to
	 * the total answer count for this activity
	 *
	 * @param string $sorted_by
	 * @param string $fieldset
	 * @return multitype:unknown
	 */
	public function get_not_completed_by_answer_count($sorted_by = null, $fieldset = '*') {
		global $DB;
		$tablename = 'groupformation_started';
		return $DB->get_records_sql("SELECT ".$fieldset." FROM {{$tablename}} WHERE groupformation = ? AND answer_count <> ? ORDER BY ?".$sorted_by,array($this->groupformationid
				,$this->store->get_total_number_of_answers(),$sorted_by));
	}
	
	/**
	 * Returns array of records of table_groupformation_started if answer_count is not equal to
	 * the total answer count for this activity but the record was submitted
	 *
	 * @param string $sorted_by
	 * @param string $fieldset
	 * @return multitype:unknown
	 */
	public function get_not_completed_but_submitted($sorted_by = null, $fieldset = '*') {
		global $DB;
		$tablename = 'groupformation_started';
		return $DB->get_records_sql("SELECT ".$fieldset." FROM {{$tablename}} WHERE groupformation = ? AND completed = 1 AND answer_count <> ? ORDER BY ?".$sorted_by,array($this->groupformationid
				,$this->store->get_total_number_of_answers(),$sorted_by));
	}
}