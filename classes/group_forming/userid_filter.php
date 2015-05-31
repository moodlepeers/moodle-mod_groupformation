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
 * 
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
	//TODO noch nicht getestet
//defined('MOODLE_INTERNAL') || die();  -> template
//namespace mod_groupformation\classes\lecturer_settings;

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

//require_once 'storage_manager.php';
	require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');
	require_once($CFG->dirroot.'/mod/groupformation/classes/util/util.php');



	class mod_groupformation_userid_filter {

	private $store;
	private $groupformationid;
	private $util;
	private $totalUserIds = array();
	private $total;

	/**
	 *
	 * @param unknown $groupformationid
	
	 */
	public function __construct($groupformationid){
		$this->groupformationid = $groupformationid;
		$this->store = new mod_groupformation_storage_manager($groupformationid);
		$this->util = new mod_groupformation_util($groupformationid);
		$this->totalUserIds = $this->store->getTotalUserIds();
		$this->total = $this->util->getTotalNumber();
	}
	
	public function getScenario(){
		return $this->store->getScenario();
	}
	
	public function getCompletedIds(){
		$completed = array();
		foreach($this->totalUserIds as $user){
			$number = $this->store->answerNumberForUser($user);
			if($this->total == $number){
				$completed[] = $user;
			}
		}
		
		return $completed;
	}
	
	public function getNoneCompletedIds(){
		
		$noneCompleted = array();
		//var_dump($this->totalUserIds);
		foreach($this->totalUserIds as $user){
			$number = $this->store->answerNumberForUser($user);
			if($this->total != $number){
				$noneCompleted[] = $user;
			}
		}
		
		return $noneCompleted;
	}
	
	public function getNumberOfCompleted(){
		return count($this->totalUserIds) - count($this->getNoneCompletedIds());
	}
	
	public function getNumberOfCommitedNoneCompleted(){
		$totalNumber = 0;
		$ids = $this->store->getUserIdsCompleted();
		foreach($ids as $user){
			$number = $this->store->answerNumberForUser($user);
			if($this->total != $number){
				$totalNumber++;
			}
		}
		
		return $totalNumber;
	}
	
	// 0 -> die Anzahl an Studenten, die den Fragebogen bearbeitet haben 
	// 1 -> die Anzahl der Studenten, die abgegeben haben
	public function getNumbersOfAnswerStatus(){
		$numbers = array();
		$numbers[] = $this->store->getNumberofAnswerStauts(FALSE);
		$numbers[] = $this->store->getNumberofAnswerStauts(TRUE);
		return $numbers;
	}
}