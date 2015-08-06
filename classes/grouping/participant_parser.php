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

require_once($CFG->dirroot.'/lib/groupal/classes/Criteria/SpecificCriterion.php');
require_once($CFG->dirroot.'/lib/groupal/classes/Participant.php');
require_once($CFG->dirroot.'/mod/groupformation/classes/grouping/criterion_calculator.php');


class mod_groupformation_participant_parser {
	
	private $groupformationID;
	
	public function __construct($groupformationID){
		$this->groupformationID = $groupformationID;
	}
	
	/**
	 * Parses infos to Participants
	 * 
	 * @param unknown $users
	 * @param unknown $labels
	 * @param unknown $groupsize
	 * @return multitype:Participant
	 */
	private function parse($users, $labels){
		$participants = array();
		foreach($users as $user){
			$position = 0;
			$participant = null;
			foreach($labels as $label){
				$value = $user->$label;
				$count = count($value);
				$homogen = $value["homogen"]; 
				unset($value["homogen"]);
				// on key "minVal" is the minValue
				// on key "maxVal is the maxValue
				$minVal = $value["minVal"];
				$maxVal = $value["maxVal"];
				unset($value["minVal"]);
				unset($value["maxVal"]);
				// all remaining $value values are indexed array values
				
				$criterion = new SpecificCriterion($label, $value, $minVal, $maxVal, $homogen, 1);
// 				var_dump($criterion);
// 				$criterion = new Criterion();
// 				$criterion->setName($label);
// 				$criterion->setValues($user->$label);
// 				$criterion->setIsHomogeneous($user->homogen);
				if($position == 0){
					$participant = new Participant(array($criterion), $user->id);
				}else{
					$participant->addCriterion($criterion);
				}
				$position++;
				
			}
			$participants[] = $participant;
		}
		
		return $participants;
	}

	/**
	 * Builds Participants array using a parser (at the end)
	 *
	 * @param unknown $users
	 * @return multitype:Participant
	 */
	
	public function build_participants($users){
		$groupformationid = $this->groupformationID;
		
		$store = new mod_groupformation_storage_manager($groupformationid);
		
		$scenario = $store->getScenario();
	
		// 		self::handle_complete_questionaires($groupformationid);
	
		$data = new mod_groupformation_data();
		
		$labels = $data->getLabelSet($scenario, $groupformationid);
		$homogen = $data->getHomogenSet($scenario);
		$minVals = $data->getMinValSet($scenario); 
		$maxVals = $data->getMaxValSet($scenario);
		
		$calculator = new mod_groupformation_criterion_calculator($groupformationid);
		$gradeP = -1;
		if(count($users)>0 && in_array('knowledge_heterogen', $labels)){
			$gradeP = $calculator->getGradePosition($users);
		}
	
		$array = array();
	
		//hier werden die einzelnen Extralabels gebildet und dann in diese array gespeichert
		// TODO Nora: Comments please in this code! (not easily understandable) (JK)
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
						$value["homogen"] = $homogen[$label]; // all these 3 could be set outside of if-clauses easily: same for all.
						$value["minVal"] = $minVals[$label];
						$value["maxVal"] = $maxVals[$label];						
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
						$value["homogen"] = $homogen[$label];
						$value["minVal"] = $minVals[$label];
						$value["maxVal"] = $maxVals[$label];						
						//foreach ($value as $k=>$v){
						//	if (is_array($v))
						//		$value[$k]=$v[1];
						//}
						$object->$label = $value;
						if($userPosition == 0){
							$totalLabel[] = $label;
						}
	
					}
					if($label == 'knowledge_homogen'){
						$value[] = $calculator->knowledgeAverage($user);
						$value["homogen"] = $homogen[$label];
						$value["minVal"] = $minVals[$label];
						$value["maxVal"] = $maxVals[$label];
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
							$value["homogen"] = $homogen[$label];
							$value["minVal"] = $minVals[$label];
							$value["maxVal"] = $maxVals[$label];
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
							$value["homogen"] = $h;
							$value["minVal"] = $minVals[$label];
							$value["maxVal"] = $maxVals[$label];								
							$object->$name = $value;
							$p++;
						}
					}
					if($label == 'big5_homogen'){
						$bigTemp = $big5[1];  // TODO: please explain this code or rename variable
	
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
							$value["homogen"] = $h;
							$value["minVal"] = $minVals[$label];
							$value["maxVal"] = $maxVals[$label];								
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
							$value["homogen"] = $h;
							$value["minVal"] = $minVals[$label];
							$value["maxVal"] = $maxVals[$label];								
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
							$value["homogen"] = $h;
							$value["minVal"] = $minVals[$label];
							$value["maxVal"] = $maxVals[$label];								
							$object->$name = $value;
							$p++;
						}
					}
					if($label == 'team'){
						$value = $calculator->getTeam($user);
						$value["homogen"] = $homogen[$label];
						$value["minVal"] = $minVals[$label];
						$value["maxVal"] = $maxVals[$label];						
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
	
		return $this->parse($array, $totalLabel);
	}
	
	/**
	 * Generates participants without criterions
	 * 
	 * @param unknown $users
	 */
	public function build_empty_participants ( $users ) {
		$participants = array();
		
		foreach ($users as $user){
			$participant = new Participant(null, $user);
			$participants[] = $participant;	
		}
		
		return $participants;
	}
}