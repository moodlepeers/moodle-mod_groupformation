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
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
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
	require_once($CFG->dirroot.'/mod/groupformation/classes/util/xml_loader.php');



	class mod_groupformation_criterion_calculator {

		private $store;
		private $groupformationid;
		private $xml;
		
		//Extraversion | Gewissenhaftigkeit | Vertr�glichkeit | Neurotizismus | Offenheit
		//                          12        14       8   17         15        16
		private $BIG5 = array(array(6), array(8), array(2, 11), array(9), array(10));
		//                                -7        -9       -13        -10      -11
		private $BIG5Invert = array(array(1), array(3), array(7), array(4), array(5));
		private $BIG5Homogen = array(1, 2);
		//Herausforderung | Interesse | Ergolgswahrscheinlichkeit | Misserfolgsbef�rchtung
		private $FAM = array(array(6, 8, 10, 15, 17), array(1, 4, 7, 11), array(2, 3, 13, 14), array(5, 9, 12, 16, 18));
		//Konkrete Erfahrung | Aktives Experimentieren | Reflektierte Beobachtung | Abstrakte Begriffsbildung
		private $LEARN = array(array(1, 5, 11, 14, 20, 22), array(2, 8, 10, 16, 17, 23), array(3, 6, 9, 13, 19, 21), array(4, 7, 12, 15, 18, 24));
		
		
		/**
		 * 
		 * @param unknown $groupformationid
		 */
		public function __construct($groupformationid){
			$this->groupformationid = $groupformationid;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
			$this->xml = new mod_groupformation_xml_loader();
		}
		
		private function inverse($qId, $category, $answer){
			$max = $this->store->getMaxOptionOfCatalogQuestion($qId, $category);
			//Da intern bei 0 und nicht bei 1 angefangen wird
			$max++;
			return $max - $answer;
			
		}
		
		/**
		 * Determines values in category 'general' chosen by user
		 * 
		 * @param int $userId
		 * @return string
		 */
		public function getGeneralValues($userId){
			$value = $this->store->getSingleAnswer($userId, 'general', 1);
			
			$question = $this->store->getCatalogQuestion(1, 'general', 'en');
			
			if ($value == 1){
				// ENGLISH 1.0
				// GERMAN 0.0
				$values = array(1.0,0.0);
			} elseif ($value == 2){
				// ENGLISH 0.0
				// GERMAN 1.0
				$values = array(0.0,1.0);
			} elseif ($value == 3){
				// ENGLISH 1.0
				// GERMAN 0.5
				$values = array(1.0,0.5);
			} elseif ($value == 4){
				// ENGLISH 0.5
				// GERMAN 1.0
				$values = array(0.5,1.0);
			}
			
			return $values;
		}
		
		
		//gibt ein Array aus arrays zur�ck | in den einzelarray sind Position 0 -> Vorwissen Position 1 -> Antwort
		/**
		 * Determines all answers for knowledge given by the user
		 * 
		 * returns an array of arrays with 
		 * 			position_0 -> knowledge area
		 * 			position_1 -> answer
		 * 
		 * @param int $userId
		 * @return multitype:multitype:mixed float
		 */
		public function knowledgeAll($userId){
			$knowledge = array();
			$position = 0;
			
			$temp = $this->store->getKnowledgeOrTopicValues('knowledge');
 			$values = $this->xml->xmlToArray('<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>');
					
			foreach($values as $question){
			//	$t = array();
			//	$t[] = $question;
				$t = floatval($this->store->getSingleAnswer($userId, 'knowledge', $position));
				$knowledge[] = $t/100.0;
				$position++;
			}
			return $knowledge;
		}
		
		/**
		 * Determines the average of the answers of the user in the category knowledge
		 * 
		 * @param int $userId
		 * @return float
		 */
		public function knowledgeAverage($userId){
			$total = 0;
			$answers = $this->store->getAnswers($userId, 'knowledge');
			$numberOfQuestion = count($answers);
			foreach($answers as $answer){
				$total = $total + $answer->answer;
			}
			
			if($numberOfQuestion != 0){
				$temp = floatval($total) / ($numberOfQuestion);
				return floatval($temp)/100;
			}else{
				return 0.0;
			}
		}
		
		/**
		 * Returns the answer of the n-th grade question
		 * 
		 * @param int $position
		 * @param int $userId
		 * @return float
		 */
		public function getGrade($position, $userId){
			$question = $this->store->getCatalogQuestion($position, 'grade');
			$o = $question->options;
			$options = $this->xml->xmlToArray('<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $o . ' </OPTIONS>');
			$answer = $this->store->getSingleAnswer($userId, 'grade', $position);
			//return floatval($options[$answer-1]);
			return floatval($answer/$this->store->getMaxOptionOfCatalogQuestion($position));
		}
		
		/**
		 * Returns the answer of the n-th grade question
		 *
		 * @param int $position
		 * @param int $userId
		 * @return float
		 */
		public function getPoints($position, $userId){
			$question = $this->store->getCatalogQuestion($position, 'points');
			$max = $this->store->get_max_points();
			$answer = $this->store->getSingleAnswer($userId, 'points', $position);
			//return floatval($options[$answer-1]);
			return floatval($answer/$max);
		}
		
		/**
		 * Returns the position of the question, which is needed for the grade criterion
		 *
		 * $users are the ids for the variance calculation
		 *
		 * @param unknown $users
		 * @return number
		 */
		public function getGradePosition($users){
			$varianz = 0;
			$position = 1;
			$total = 0;
			$totalOptions = 0;
				
			// iterates over three grade questions
			for($i = 1; $i <= 3; $i++){
		
				// answers for catalog question in category 'grade'
				$answers = $this->store->getAnswersToSpecialQuestion('grade', $i);
		
				// number of options for catalog question
				$totalOptions = $this->store->getMaxOptionOfCatalogQuestion($i, 'grade');
		
				//
				$dist = $this->getInitialArray($totalOptions);
		
				// iterates over answers for grade questions
				foreach($answers as $answer){
					
				// checks if answer is relevant for this group of users
					if(in_array($answer->userid, $users)){
		
					// increments count for answer option
						$dist[($answer->answer)-1]++;
		
						// increments count for total
						if($i == 1){
						$total++;
					}
					}
					}
		
					// computes tempE for later use
					$tempE = 0;
					$p = 1;
					foreach($dist as $d){
					$tempE = $tempE + ($p * ($d / $total));
					$p++;
					}
		
						// computes tempV to find maximal variance
						$tempV = 0;
						$p = 1;
						foreach($dist as $d){
						$tempV = $tempV + ((pow(($p - $tempE),2)) * ($d / $total));
							$p++;
						}
		
						// sets position by maximal variance
						if($varianz < $tempV){
						$varianz = $tempV;
						$position = $i;
			}
			}
				
			return $position;
			}
		
		/**
		 * Returns the position of the question, which is needed for the points criterion
		 * 
		 * $users are the ids for the variance calculation
		 * 
		 * @param unknown $users
		 * @return number
		 */
		public function getPointsPosition($users){
			$varianz = 0;
			$position = 1;
			$total = 0;
			$totalOptions = 0;
			
			// iterates over three grade questions
			for($i = 1; $i <= $this->store->getNumber('points'); $i++){
				
				// answers for catalog question in category 'grade'
				$answers = $this->store->getAnswersToSpecialQuestion('points', $i);
				
				$min_value = 0;
				$max_value = $this->store->get_max_points();
				
				// number of options for catalog question
				$totalOptions = $this->store->getMaxOptionOfCatalogQuestion($i, 'points');
				
				// 
				$dist = $this->getInitialArray($totalOptions);
				
				// iterates over answers for grade questions
				foreach($answers as $answer){
					
					// checks if answer is relevant for this group of users
					if(in_array($answer->userid, $users)){
						
						// increments count for answer option
						$dist[($answer->answer)-1]++;
						
						// increments count for total
						if($i == 1){
							$total++;
						}
					}
				}
				
				// computes tempE for later use
				$tempE = 0;
				$p = 1;
				foreach($dist as $d){
					$tempE = $tempE + ($p * ($d / $total));
					$p++;
				}
				
				// computes tempV to find maximal variance
				$tempV = 0;
				$p = 1;
				foreach($dist as $d){
					$tempV = $tempV + ((pow(($p - $tempE),2)) * ($d / $total));
					$p++;
				}
				
				// sets position by maximal variance
				if($varianz < $tempV){
					$varianz = $tempV;
					$position = $i;
				}
			}
			
			return $position;
		}
		
		/** 
		 * returns an array with n = $total fields
		 * 
		 * @param unknown $total
		 * @return multitype:array
		 */
		private function getInitialArray($total){
			$array = array();
			for($i = 0; $i<$total; $i++){
				$array[] = 0;
			}
			return $array;
		}
		
		/**
		 * returns the Big 5 by user
		 * 
		 * @param unknown $userId
		 * @return multitype:array
		 */
		//array aus zwei arrays -> Position 0 heterogen | Position 1 homogen
		public function getBig5($userId){
			
			$array = array();
			$heterogen = array();
			$homogen = array();
			$category = 'character';
			
			$count = count($this->BIG5);
			$scenario = $this->store->getScenario();
			if($scenario == 2){
				$count = $count - 2;
			}
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				$maxValue = 0;
				foreach ($this->BIG5[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($userId, $category, $num);
					$maxValue = $maxValue + $this->store->getMaxOptionOfCatalogQuestion($num, $category);
				}
				foreach ($this->BIG5Invert[$i] as $num){
					$temp = $temp + $this->inverse($num, $category, $this->store->getSingleAnswer($userId, $category, $num));
					$maxValue = $maxValue + $this->store->getMaxOptionOfCatalogQuestion($num, $category);
				}
				if(in_array($i, $this->BIG5Homogen)){
					$homogen[] = floatval($temp)/($maxValue);
				}else{
					$heterogen[] = floatval($temp)/($maxValue);
				}
			}
			
			$array[] = $heterogen;
			$array[] = $homogen;
			return $array;
// 			//Extraversion
// 			$temp = 0;
// 			$temp = $temp + $this->inverse(1, $category, 
// 					$this->store->getSingleAnswer($this->userId, $category, 1));
// 			$temp = $temp + $this->store->getSingleAnswer($this->userId, $category, 6);
			
// 			$array[] = $temp;
			
			
			
		}
		
		/**
		 * returns the FAM (motivation criterion) of the user specified by �userId

		 * 
		 * @param unknown $userId
		 * @return multitype:array
		 */
		public function getFAM($userId){
				
			$array = array();
			$category = 'motivation';
				
			$count = count($this->FAM);
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				$maxValue = 0;
				foreach ($this->FAM[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($userId, $category, $num);
					$maxValue = $maxValue + $this->store->getMaxOptionOfCatalogQuestion($num, $category);
				}
				$array[] = floatval($temp)/($maxValue);
			}
				
			return $array;
		}
		
		/**
		 * returns the learning criterion of the user specified by �userId
		 * 
		 * @param unknown $userId
		 * @return multitype:array
		 */
		public function getLearn($userId){
		
			$array = array();
			$category = 'learning';
		
			$count = count($this->LEARN);
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				$maxValue = 0;
				foreach ($this->LEARN[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($userId, $category, $num);
					$maxValue = $maxValue + $this->store->getMaxOptionOfCatalogQuestion($num, $category);
				}
				$array[] = floatval($temp)/($maxValue);
			}
		
			return $array;
		}
		
		/**
		 * returns the team (Teamorientierung) criterion of the user specified by �userId

		 * 
		 * @param unknown $userId
		 * @return multitype:number // later on this will be an array
		 */
		public function getTeam($userId){
			$total = 0.0;
			$maxValue = 0.0;
			$array = array();
			$answers = $this->store->getAnswers($userId, 'team');
			$numberOf = count($answers);
			foreach($answers as $answer){
				$total = $total + $answer->answer;
				$maxValue = $maxValue + $this->store->getMaxOptionOfCatalogQuestion($numberOf, 'team');
			}
			
			if($numberOf != 0){
				$temp = $total/$numberOf;
				$temptotalValue = $maxValue/$numberOf;
				$array[] = floatval($temp/$temptotalValue);
				//$array[] = floatval($total/$numberOf);
			}else{
				$array[] = 0.0;
			}
			
			return $array;
		}
	}