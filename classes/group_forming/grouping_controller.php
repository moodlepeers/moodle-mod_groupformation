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
require_once(dirname(__FILE__).'/criterion_calculator.php');
require_once(dirname(__FILE__).'/participant_parser.php');
require_once($CFG->dirroot.'/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');

class mod_groupformation_grouping_controller{	
	
	private $groupformationID;
	private $store;
	
	public function __construct($groupformationID){
		$this->groupformationID = $groupformationID;
		$this->store = new mod_groupformation_storage_manager($groupformationID);
	}
	
	/**
	 * Builds Participants array using a parser (at the end)
	 * 
	 * @param unknown $users
	 * @return multitype:Participant
	 */
	public function build_participants($users){
		
		$scenario = $this->store->getScenario();
		
// 		self::handle_complete_questionaires($this->groupformationID);
		
		$data = new mod_groupformation_data();
		
		//$this->setNulls($scenario);
		
		$labels = $data->getLabelSet($scenario, $this->groupformationID);
		$homogen = $data->getHomogenSet($scenario);
		
		$calculator = new mod_groupformation_criterion_calculator($this->groupformationID);
		$gradeP = -1;
		if(count($users)>0 && in_array('knowledge_heterogen', $labels)){
			$gradeP = $calculator->getGradePosition($users);
		}
		
		$array = array();
		
		//hier werden die einzelnen Extralabels gebildet und dann in diese array gespeichert
		$totalLabel = array();
		$userPosition = 0;
		foreach($users as $user){
			$object = new stdClass();
			$object->id = $user;
			
			$big5 = array();
			if($scenario != 3){
				$big5 = $calculator->getBig5($user);
			}
		
			$labelPosition = 0;
			foreach($labels as $label){
				if($label != ""){
					$value = array();
// 					if($label == 'userid'){
// 						$value[] = $user;
// 					}
					if($label == 'lang'){
						$value[] = $data->getLangNumber($calculator->getLang($user));
						$value[] = $homogen[$label];
						$object->$label = $value;
						if($userPosition == 0){
							$totalLabel[] = $label;
						}
					}
					if($label == 'topic'){
						//TODO
					}
					if($label == 'knowledge_heterogen'){
						$value = $calculator->knowledgeAll($user);
						$value[] = $homogen[$label];
						$object->$label = $value;
						if($userPosition == 0){
							$totalLabel[] = $label;
						}
						
					}
					if($label == 'knowledge_homogen'){
						$value[] = $calculator->knowledgeAverage($user);
						$value[] = $homogen[$label];
						$object->$label = $value;
						if($userPosition == 0){
							$totalLabel[] = $label;
						}
					}
					// TODO @Nora - Ich hab bei Bewertungsmethode nach "Just Pass" gearbeitet, 
					// sprich die Fragebogenseite "Grade" wird nicht angezeigt, 
					// keine Antwort vom Studenten gespeichert und somit hier keine Antwort gefunden!
					// Bitte eine Abstraktion von getLabelSet und getHomogenSet in store bauen, 
					// die die Fälle von grade, points, just pass, no method löst
					// Wegen der Abstraktion gehören solche Methoden meiner Meinung nach nicht in Data
					
					if($label == 'grade'){
						if($gradeP != -1){
							$value[] = $calculator->getGrade($gradeP, $user);
							$value[] = $homogen[$label];
							$object->$label = $value;
							if($userPosition == 0){
								$totalLabel[] = $label;
							}
						}	
					}
					if($label == 'big5_heterogen'){
						$bigTemp = $big5[0];
						$l = $data->getExtraLabel($label, $scenario);
						$p = 0;
						$h = $homogen[$label];
						foreach($bigTemp as $ls){
							$value = array();
							$name = $label . '_' . $l[$p];
							if($userPosition == 0){
								$totalLabel[] = $name;
							}
							$value[] = $ls;
							$value[] = $h;
							$object->$name = $value;
							$p++;
						}
					}
					if($label == 'big5_homogen'){
						$bigTemp = $big5[1];
						
						$l = $data->getExtraLabel($label);
						$p = 0;
						$h = $homogen[$label];
						foreach($bigTemp as $ls){
							$value = array();
							$name = $label . '_' . $l[$p];
							if($userPosition == 0){
								$totalLabel[] = $name;
							}
							$value[] = $ls;
							$value[] = $h;
							$object->$name = $value;
							$p++;
						}
					}
					if($label == 'fam'){
						$famTemp = $calculator->getFAM($user);
						$l = $data->getExtraLabel($label);
						$p = 0;
						$h = $homogen[$label];
						foreach($l as $ls){
							$value = array();
							$name = $label . '_' . $ls;
							if($userPosition == 0){
								$totalLabel[] = $name;
							}
							$value[] = $famTemp[$p];
							$value[] = $h;
							$object->$name = $value;
							$p++;
						}
						
					}
					if($label == 'learning'){
						$learnTemp = $calculator->getLearn($user);
						$l = $data->getExtraLabel($label);
						$p = 0;
						$h = $homogen[$label];
						foreach($l as $ls){
							$value = array();
							$name = $label . '_' . $ls;
							if($userPosition == 0){
								$totalLabel[] = $name;
							}
							$value[] = $learnTemp[$p];
							$value[] = $h;
							$object->$name = $value;
							$p++;
						}
					}
					if($label == 'team'){
						$value = $calculator->getTeam($user);
						$value[] = $homogen[$label];
						$object->$label = $value;
						if($userPosition == 0){
							$totalLabel[] = $label;
						}
					}
					
// 					$object->$label = $value;
// 					$object->homogen = $homogen[$labelPosition];
				} 
				
				$labelPosition++;
			}
			$array[] = $object;
			$userPosition++;
		}
		
		return mod_groupformation_participant_parser::parse($array, $totalLabel);
	}
	
}