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
 * Prints a particular instance of groupformation
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}
class mod_groupformation_submit_infos {
	private $groupformationid;
	
	/**
	 * Creates instance
	 *
	 * @param unknown $groupformationid        	
	 */
	public function __construct($groupformationid) {
		$this->groupformationid = $groupformationid;
	}
	
	/**
	 * Returns stats about answered questionnaires
	 *
	 * @return multitype:number
	 */
	public function get_infos() {
		$um = new mod_groupformation_user_manager ( $this->groupformationid );
		$store = new mod_groupformation_storage_manager ( $this->groupformationid );
		
		$total_answer_count = $store->get_total_number_of_answers ();
		
		$stats = array ();
		
		$context = groupformation_get_context ( $this->groupformationid );
		$students = get_enrolled_users ( $context, 'mod/groupformation:onlystudent' );
		$student_count = count ( $students );
		
		$stats [] = $student_count;
		
		$started = $um->get_started ();
		$started_count = count ( $started );
		
		$stats [] = $started_count;
		
		$completed = $um->get_completed ();
		$completed_count = count ( $completed );
		
		$stats [] = $completed_count;
		
		$no_missing_answers = $um->get_completed_by_answer_count ();
		$no_missing_answers_count = count ( $no_missing_answers );
		
		$stats [] = $no_missing_answers_count;
		
		$missing_answers = $um->get_not_completed_but_submitted ();
		$missing_answers_count = count ( $missing_answers );
		
		$stats [] = $missing_answers_count;
		
		return $stats;
	}
}