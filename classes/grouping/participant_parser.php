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
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->dirroot . '/lib/groupal/classes/Criteria/SpecificCriterion.php');
require_once ($CFG->dirroot . '/lib/groupal/classes/Participant.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/grouping/criterion_calculator.php');
class mod_groupformation_participant_parser {
	private $groupformationID;
	public function __construct($groupformationID) {
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
	private function parse($users, $labels) {
		$participants = array ();
		foreach ( $users as $user ) {
			$position = 0;
			$participant = null;

// 			var_dump($labels);
			foreach ( $labels as $label ) {
				$value = $user->$label;
// 				var_dump($label);
// 				var_dump($value);
				$count = count ( $value );
				$homogen = $value ["homogen"];
				unset ( $value ["homogen"] );
// 				var_dump($value);
				// on key "minVal" is the minValue
				// on key "maxVal is the maxValue
				$minVal = 0.0;
				$maxVal = 1.0;
				// unset($value["minVal"]);
				// unset($value["maxVal"]);
				// all remaining $value values are indexed array values
				

				/*
				 * Sprache: Es soll ein 2-dim Vektor rauskommen. (HOMOGEN zu matchen)
				 * Wert1: Englisch = 1 wenn Englisch ausgewählt
				 * Wert2: Deutsch = 1 wenn Deutsch ausgewählt
				 *
				 * Wurde Deutsch bevorzugt ausgewählt, wird Englisch nicht auf 0 sondern
				 * auf 0.5 gesetzt. Ebenso bei Englisch bevorzugt wird Deutsch auf 0.5 gesetzt.
				 *
				 * Gewichtung: Bei Übergabe an GroupAL folgender PseudoCode:
				 * var numCrit = Anzahl aller Kriterien die aktuell an GroupAL übergeben
				 * werden sollen (die haben standardmäßig ein Gewicht von 1 (weight=1).
				 * var weightLanguage = (numCrit-1)/2
				 *
				 * ((Dieser Code sollte sicher nach dem Code stehen, der entscheided ob die
				 * Noten/Punkte-Angaben überhaupt als Kriterium an GroupAL übergeben werden oder
				 * ignoriert werden.))
				 */
				
				$weight = 1;
				
				if ($label == 'general'){
					$weight = (count($labels) - 1)/2;
				}
				
				$criterion = new SpecificCriterion ( $label, $value, $minVal, $maxVal, $homogen, $weight);
// 				var_dump($criterion);
				// $criterion = new Criterion();
				// $criterion->setName($label);
				// $criterion->setValues($user->$label);
				// $criterion->setIsHomogeneous($user->homogen);
				if ($position == 0) {
					$participant = new Participant ( array (
							$criterion 
					), $user->id );
				} else {
					$participant->addCriterion ( $criterion );
				}
				$position ++;
			}
// 			var_dump($participant->getCriteria());
			$participants [] = $participant;
		}
		
		return $participants;
	}
	
	/**
	 * Builds Participants array using a parser (at the end)
	 *
	 * @param unknown $users        	
	 * @return multitype:Participant
	 */
	public function build_participants($users) {
		if (count ( $users ) == 0) {
			return array ();
		}
		
		$starttime = microtime ( true );
		
		$groupformationid = $this->groupformationID;
		
		$store = new mod_groupformation_storage_manager ( $groupformationid );
		
		$scenario = $store->getScenario ();
		
		// self::handle_complete_questionaires($groupformationid);
		
		$data = new mod_groupformation_data ();
		
		$labels = $store->getLabelSet ();
		$homogen = $store->getHomogenSet ();
		// $minVals = $data->getMinValSet($scenario);
		// $maxVals = $data->getMaxValSet($scenario);
		
// 		var_dump ( $labels, $homogen );
		
		$calculator = new mod_groupformation_criterion_calculator ( $groupformationid );
		
		$gradeP = - 1;
		// determines the question position with maximal variance (if grade is in questionnaire)
		if (in_array ( 'grade', $labels )) {
			$gradeP = $calculator->getGradePosition ( $users );
		}
		
		$pointsP = - 1;
		// determines the question position with maximal variance (if grade is in questionnaire)
		if (in_array ( 'points', $labels )) {
			$pointsP = $calculator->getPointsPosition ( $users );
		}
		
		$array = array ();
		$totalLabel = array ();
		$userPosition = 0;
		
		// hier werden die einzelnen Extralabels gebildet und dann in diese array gespeichert
		// TODO Nora: Comments please in this code! (not easily understandable) (JK)
		
		// iterates over set of users
		foreach ( $users as $user ) {
			
			// precomputes values and generates and object which can be parsed into participants with criteria
			$object = new stdClass ();
			$object->id = $user;
			
			// computes BIG5 if in labels (first part is heterogen, second part is homogen)
			$big5 = array ();
			if (in_array ( 'big5_homogen', $labels ) || in_array ( 'big5_heterogen', $labels )) {
				$big5 = $calculator->getBig5 ( $user );
			}
			
			$labelPosition = 0;
			foreach ( $labels as $label ) {
				
				$value = array ();
				
				// handles 'general'
				if ($label == 'general') {
					$values = $calculator->getGeneralValues ( $user );
					foreach ($values as $v){
						$value[] = $v;
					}
					$value ["homogen"] = $homogen [$label];
					
					$object->$label = $value;
					if ($userPosition == 0) {
						$totalLabel [] = $label;
					}
				}
				
				// Behandlung der Topics
				if ($label == 'topic') {
					// TODO
				}
				
				// Behandlung des Vorwissens ( alle Vorwissenwerte in einem Array zusammengefasst )
				if ($label == 'knowledge_heterogen') {
					$value = $calculator->knowledgeAll ( $user );
					$value ["homogen"] = $homogen [$label];
					// $value["minVal"] = $minVals[$label];
					// $value["maxVal"] = $maxVals[$label];
					// foreach ($value as $k=>$v){
					// if (is_array($v))
					// $value[$k]=$v[1];
					// }
					$object->$label = $value;
					if ($userPosition == 0) {
						$totalLabel [] = $label;
					}
				}
				
				// Behandlung des Vorwissen ( Durchschnittwert )
				if ($label == 'knowledge_homogen') {
					$value [] = $calculator->knowledgeAverage ( $user );
					$value ["homogen"] = $homogen [$label];
					// $value["minVal"] = $minVals[$label];
					// $value["maxVal"] = $maxVals[$label];
					$object->$label = $value;
					// beim ersten Users sollen die Namen der (erstellten) Labels mit abgespeichert werden
					if ($userPosition == 0) {
						$totalLabel [] = $label;
					}
				}
				// TODO @Nora - Ich hab bei Bewertungsmethode nach "Just Pass" gearbeitet,
				// sprich die Fragebogenseite "Grade" wird nicht angezeigt,
				// keine Antwort vom Studenten gespeichert und somit hier keine Antwort gefunden!
				// Bitte eine Abstraktion von getLabelSet und getHomogenSet in store bauen,
				// die die Fälle von grade, points, just pass, no method löst
				// Wegen der Abstraktion gehören solche Methoden meiner Meinung nach nicht in Data
				
				// Behandlung von Note
				if ($label == 'grade') {
					// falls eine Varianz berechnet wurde
					if ($gradeP != - 1) {
						$value [] = $calculator->getGrade ( $gradeP, $user );
						$value ["homogen"] = $homogen [$label];
						// $value["minVal"] = $minVals[$label];
						// $value["maxVal"] = $maxVals[$label];
						$object->$label = $value;
						if ($userPosition == 0) {
							$totalLabel [] = $label;
						}
					}
				}
				
				if ($label == 'points'){
					// falls eine Varianz berechnet wurde
					if ($pointsP != - 1) {
						$value [] = $calculator->getPoints ( $pointsP, $user );
						$value ["homogen"] = $homogen [$label];
						// $value["minVal"] = $minVals[$label];
						// $value["maxVal"] = $maxVals[$label];
						$object->$label = $value;
						if ($userPosition == 0) {
							$totalLabel [] = $label;
						}
					}
				}
				
				// Behandlung von Big5 heterogen
				if ($label == 'big5_heterogen') {
					$bigTemp = $big5 [0]; // siehe init von $big5
					$l = $data->getExtraLabel ( $label, $scenario );
					$p = 0;
					$h = $homogen [$label];
					// erstellen der detailierten Labels f�r die verschiedene Big5's
					foreach ( $bigTemp as $ls ) {
						$value = array ();
						$name = $label . '_' . $l [$p];
						if ($userPosition == 0) {
							$totalLabel [] = $name;
						}
						$value [] = $ls;
						$value ["homogen"] = $h;
						// $value["minVal"] = $minVals[$label];
						// $value["maxVal"] = $maxVals[$label];
						$object->$name = $value;
						$p ++;
					}
					
				}
				
				// Behandlung der Big5 homogen
				if ($label == 'big5_homogen') {
					$bigTemp = $big5 [1]; // siehe init von $Big5
					
					$l = $data->getExtraLabel ( $label );
					$p = 0;
					$h = $homogen [$label];
					// erstellen der detailierten Labels f�r die verschiedene Big5's
					foreach ( $bigTemp as $ls ) {
						$value = array ();
						$name = $label . '_' . $l [$p];
						if ($userPosition == 0) {
							$totalLabel [] = $name;
						}
						$value [] = $ls;
						$value ["homogen"] = $h;
						// $value["minVal"] = $minVals[$label];
						// $value["maxVal"] = $maxVals[$label];
						$object->$name = $value;
						$p ++;
					}
				}
				
				// Behandlung von FAM
				if ($label == 'fam') {
					$famTemp = $calculator->getFAM ( $user );
					$l = $data->getExtraLabel ( $label );
					$p = 0;
					$h = $homogen [$label];
					foreach ( $l as $ls ) {
						$value = array ();
						$name = $label . '_' . $ls;
						if ($userPosition == 0) {
							$totalLabel [] = $name;
						}
						$value [] = $famTemp [$p];
						$value ["homogen"] = $h;
						// $value["minVal"] = $minVals[$label];
						// $value["maxVal"] = $maxVals[$label];
						$object->$name = $value;
						$p ++;
					}
				}
				
				// Behandlung von Learning
				if ($label == 'learning') {
					$learnTemp = $calculator->getLearn ( $user );
					$l = $data->getExtraLabel ( $label );
					$p = 0;
					$h = $homogen [$label];
					foreach ( $l as $ls ) {
						$value = array ();
						$name = $label . '_' . $ls;
						if ($userPosition == 0) {
							$totalLabel [] = $name;
						}
						$value [] = $learnTemp [$p];
						$value ["homogen"] = $h;
						// $value["minVal"] = $minVals[$label];
						// $value["maxVal"] = $maxVals[$label];
						$object->$name = $value;
						$p ++;
					}
				}
				
				// Behandlung von Teamorientierung
				if ($label == 'team') {
					$value = $calculator->getTeam ( $user );
					$value ["homogen"] = $homogen [$label];
					// $value["minVal"] = $minVals[$label];
					// $value["maxVal"] = $maxVals[$label];
					$object->$label = $value;
					if ($userPosition == 0) {
						$totalLabel [] = $label;
					}
				}
				
				// $object->$label = $value;
				// $object->homogen = $homogen[$labelPosition];
				
				$labelPosition ++;
			}
			$array [] = $object;
			$userPosition ++;
		}
		
		// var_dump($array);
		$res = $this->parse ( $array, $totalLabel );
		
		$endtime = microtime ( true );
		$comptime = $endtime - $starttime;
		groupformation_info ( null, $groupformationid, 'building groupal participants needed ' . $comptime . 'ms' );
		
		return $res;
	}
	
	/**
	 * Generates participants without criterions
	 *
	 * @param unknown $users        	
	 */
	public function build_empty_participants($users) {
		$starttime = microtime ( true );
		$participants = array ();
		foreach ( $users as $user ) {
			$participant = new Participant ( null, $user );
			$participants [] = $participant;
		}
		$endtime = microtime ( true );
		$comptime = $endtime - $starttime;
		groupformation_info ( null, $this->groupformationID, 'building empty participants needed ' . $comptime . 'ms' );
		return $participants;
	}
}