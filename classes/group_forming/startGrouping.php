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

require_once(dirname(__FILE__).'/userid_filter.php');
require_once(dirname(__FILE__).'/calculateCriterions.php');
require_once($CFG->dirroot.'/mod/groupformation/classes/util/define_file.php');


class mod_groupformation_startGrouping{
	
	private $labels = array();
	
	public static function start($groupformationID){
		echo 'Hier startet die Berechnung';
		$userFilter = new mod_groupformation_userid_filter($groupformationID);
		$users = $userFilter->getCompletedIds();
		$szenario = $userFilter->getSzenario();
		$data = new mod_groupformation_data();
		$this->labels = $data->getLabels();
		$this->setNulls($szenario);
		$calculator = new mod_groupformation_calculateCriterions($groupformationID);
		if(count($users)>0){
			$gradeP = $calculator->getGradePosition($users);
		}
		$array = array();
		foreach($users as $user){
			$object = new stdClass();
			foreach($this->labels as $label){
				if($label != ""){
					$value = array();
					if($label == 'userid'){
						$value[] = $user;
					}
					if($label == 'lang'){
						$value[] = $data->getLangNumber($calculator->getLang($user));
					}
					if($label == 'topic'){
						//TODO
					}
					if($label == 'knowledge_heterogen'){
						$value = $calculator->knowledgeAll($user);
					}
					if($label == 'knowledge_homogen'){
						$value[] = $calculator->knowledgeAverage($user);
					}
					if($label == 'grade'){
						
					}
					if($label == 'big5'){
						$value = $calculator->getBig5($user);
					}
					if($label == 'fam'){
						$value = $calculator->getFAM($user);
					}
					if($label == 'learning'){
						$value = $calculator->getLearn($user);
					}
					if($label == 'team'){
						$value = $calculator->getTeam($user);
					}
					$object->$label = $value;
				} 
			}
			$array[] = $object;
		}
		
		var_dump($array);
	}
	
	//noch hartgecodet
	private function setNulls($szenario){
		if($szenario == 1){
			$this->labels[9] = "";
		}
		
		if($szenario == 2){
			$this->labels[4] = "";
			$this->labels[8] = "";
		}
		
		if($szenario == 3){
			$this->labels[3] = "";
			$this->labels[4] = "";
			$this->labels[5] = "";
			$this->labels[6] = "";
			$this->labels[7] = "";
			$this->labels[8] = "";
			$this->labels[9] = "";
		}
	}
}