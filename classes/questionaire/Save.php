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

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');
	
	define('SAVE', 0);
	
	class mod_groupformation_save {

		private $groupformationid;
		private $store;
		private $userId;
		private $category;
		
		public function __construct($groupformationid, $userId, $category){
			
			$this->groupformationid = $groupformationid;
			$this->userId = $userId;
			$this->category = $category;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
			$this->status = $this->store->answeringStatus($userId);
		}
		
		/**
		 * Saves answer
		 * 
		 * @param unknown $answer
		 * @param unknown $position
		 */
		public function save($answer, $position){
            // if the answer in category "grade"(dropdowns) is default(0) - return without saving
			if(($this->category == 'grade' || $this->category == 'general') && $answer == '0'){
                /*if($this->status == -1){
                    $this->status = SAVE;
                    $this->store->statusChanged($this->userId);
                }*/
                return;}
            else{
                $this->store->saveAnswer($this->userId, $answer, $this->category, $position);
                if($this->status == -1){
                    $this->status = SAVE;
                    $this->store->statusChanged($this->userId);
                }
                $this->store->set_answer_count($this->userId);
            }
		}
	}