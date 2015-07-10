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

//defined('MOODLE_INTERNAL') || die();  -> template
//namespace mod_groupformation\classes\lecturer_settings;

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

//require_once 'storage_manager.php';
require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once(dirname(__FILE__).'/define_file.php');



class mod_groupformation_util {

	private $store;
	private $groupformationid;
	
	//private $names = array('topic', 'knowledge', 'general','grade','team', 'character', 'learning', 'motivation');
	private $names = array();
	private $scenario;
	private $numbers = array();

	/**
	 *
	 * @param unknown $groupformationid

	 */
	public function __construct($groupformationid){
		$this->groupformationid = $groupformationid;
		$this->store = new mod_groupformation_storage_manager($groupformationid);
		$this->scenario = $this->store->getScenario();
		$data = new mod_groupformation_data();
		//$this->names = $data->getCriterionNames();
		$this->names = $data->getCriterionSet($this->scenario, $groupformationid);
	}
	
	public function getTotalNumber(){
		$number = 0;
		$this->numbers = $this->store->getNumbers($this->names);
		//$this->setNulls();
		foreach($this->numbers as $n){
			$number = $number + $n;
		}
		
		return $number;
	}
	
	private function getPosition($category){
		//$position = -1;
		for($i = 0; $i<count($this->names); $i++){
			if($this->names[$i] == $category){
				return $i;
			}
		}	
	}
	
	private function setNulls(){
		if($this->scenario == 'project' || $this->scenario == 1){
			$this->numbers[$this->store->getPosition('learning')] = 0;
		}
	
		if($this->scenario == 'homework' || $this->scenario == 2){
			$this->numbers[$this->store->getPosition('motivation')] = 0;
		}
			
		if($this->scenario == 'presentation' || $this->scenario == 3){
			for($i = 0; $i < count($this->numbers); $i++){
				if($i != $this->store->getPosition('topic') && $i != $this->store->getPosition('general')){
					$this->numbers[$i] = 0;
				}
			}
		}

	}
	
}