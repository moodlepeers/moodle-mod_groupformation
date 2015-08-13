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
 * @author  Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

	require_once(dirname(__FILE__).'/userid_filter.php');

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	class mod_groupformation_submit_infos {
		
		private $groupformationid;
		private $userid_filter;
		private $store;
		
		public function __construct($groupformationid){
			$this->groupformationid = $groupformationid;
			$this->userid_filter = new mod_groupformation_userid_filter($groupformationid);
			$this->store = new mod_groupformation_storage_manager($groupformationid);
		}

		public function getInfos(){
			$total_answer_count = $this->store->get_total_number_of_answers();
			
			$stats = array();
			
			$context = groupformation_get_context($this->groupformationid);
			$students = get_enrolled_users($context,'mod/groupformation:onlystudent');
			$student_count = count($students);
			
			$stats[] = $student_count;
			
			$started = $this->userid_filter->get_started();
			$started_count = count($started);
			
			$stats[] = $started_count;
			
			$completed = $this->userid_filter->get_completed();
			$completed_count = count($completed);
			
			$stats[] = $completed_count;
			
			$no_missing_answers = array();
			foreach($started as $userid => $record){
				if ($record->answer_count == $total_answer_count)
					$no_missing_answers[]=$userid;
			}
			$no_missing_answers_count = count($no_missing_answers);
			
			$stats[] = $no_missing_answers_count;
			
			$missing_answers = array();
			foreach($completed as $userid => $record){
				if ($record->answer_count != $total_answer_count)
					$missing_answers[]=$userid;
			}
			$missing_answers_count = count($missing_answers);
			$stats[] = $missing_answers_count;

            return $stats;
		}


	}