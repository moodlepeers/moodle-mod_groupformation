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
	require_once($CFG->dirroot.'/mod/groupformation/classes/util/xml_loader.php');



	class mod_groupformation_calculateCriterions {

		private $store;
		private $groupformationid;
		
		//Extraversion | Gewissenhaftigkeit | Verträglichkeit | Neurotizismus | Offenheit
		private $BIG5 = array(array(6), array(8), array(2, 11), array(9), array(10));
		private $BIG5Invert = array(array(1), array(3), array(7), array(4), array(5));
		//Herausforderung | Interesse | Ergolgswahrscheinlichkeit | Misserfolgsbefürchtung
		private $FAM = array(array(6, 8, 10, 15, 17), array(1, 4, 7, 11), array(2, 3, 13, 14), array(5, 9, 12, 16, 18));
		//Konkrete Erfahrung | Aktives Experimentieren | Reflektierte Beobachtung | Abstrakte Begriffsbildung
		private $LEARN = array(array(1, 5, 11, 14, 20, 22), array(2, 8, 10, 16, 17, 23), array(3, 6, 9, 13, 19, 21), array(4, 7, 12, 15, 18, 24));
		//TODO @JK TEAM Auswertung fehlt noch
		/**
	 	*
	 	* @param unknown $groupformationid

	 	*/
		public function __construct($groupformationid){
			$this->groupformationid = $groupformationid;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
		
		}
		
		private function inverse($qId, $category, $answer){
			$max = $this->store->getMaxOfCatalogQuestionOptions($qId, $category);
			//Da intern bei 0 und nicht bei 1 angefangen wird
			$max++;
			return $max - $answer;
			
		}
		
		public function getLang($userId){
			$lang = $this->store->getSingleAnswer($userId, 'general', 1);
			
			if($lang == 1 || $lang == 3){
				return 'en';
			}else{
				return 'de';
			}
		}
		
		//gibt ein Array aus arrays zurück | in den einzelarray sind Position 0 -> Vorwissen Position 1 -> Antwort
		public function knowledgeAll($userId){
			$knowledge = array();
			$position = 0;
			
			$temp = $this->store->getDozentQuestion('knowledge');
 			$values = $this->xml->xmlToArray('<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>');
					
			foreach($values as $question){
				$t = array();
				$t[] = $question;
				$t[] = $this->store->getSingleAnswer($userId, 'knowledge', $position);
				$knowledge[] = $t;
			}
			return $knowledge;
		}
		
		public function knowledgeAverage($userId){
			$total = 0;
			$numberOfQuestion = 0;
			$answers = $this->store->getAnswer($userId, 'knowledge');
			foreach($answers as $answer){
				$total = $total + $answer->answer;
				$numberOfQuestion++;
			}
			
			if($numberOfQuestion != 0){
				return $total / $numberOfQuestion;
			}else{
				return 0;
			}
		}
		
		public function getGrade($position, $userId){
			return $this->store->getSingleAnswer($userId, 'grade', $position);
		}
		
		public function getGradePosition(){
			$varianz = 0;
			$position = 1;
			$total = 0;
			$totalOptions = 0;
			
			for($i = 1; $i <= 3; $i++){
				$answers = $this->store->getAnswersToSpecialQuestion('grade', $i);
				$totalOptions = $this->store->getMaxOfCatalogQuestionOptions($i, 'grade');
				$dist = $this->getInitalArray($totalOptions);
				foreach($answers as $answer){
					$dist[($answer->answer)-1]++;
					if($i == 1){
						$total++;
					}
				}
				
				$tempE = 0;
				$p = 1;
				foreach($dist as $d){
					$tempE = $tempE + ($p * ($d / $total));
					$p++;
				}
				
				$tempV = 0;
				$p = 1;
				foreach($dist as $d){
					$tempV = $tempV + ((pow(($p - $tempE),2)) * ($d / $total));
					$p++;
				}
				
				if($varianz < $tempV){
					$varianz = $tempV;
					$position = $i;
				}
			}
			
			return $position;
		}
		
		private function getInitalArray($total){
			$array = array();
			for($i = 0; $i<$total; $i++){
				$array[] = 0;
			}
			return $array;
		}
		
		public function getBig5($userId){
			
			$array = array();
			$category = 'character';
			
			$count = count($this->BIG5);
			$szenario = $this->store->getSzenario();
			if($szenario == 2){
				$count = $count - 2;
			}
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				foreach ($this->BIG5[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($userId, $category, $num);
				}
				foreach ($this->BIG5Invert[$i] as $num){
					$temp = $temp + $this->inverse($num, $category, $this->store->getSingleAnswer($userId, $category, $num));
				}
				$array[] = $temp;
			}
			
			return $array;
// 			//Extraversion
// 			$temp = 0;
// 			$temp = $temp + $this->inverse(1, $category, 
// 					$this->store->getSingleAnswer($this->userId, $category, 1));
// 			$temp = $temp + $this->store->getSingleAnswer($this->userId, $category, 6);
			
// 			$array[] = $temp;
			
			
			
		}
		
		public function getFAM($userId){
				
			$array = array();
			$category = 'motivation';
				
			$count = count($this->FAM);
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				foreach ($this->FAM[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($userId, $category, $num);
				}
				$array[] = $temp;
			}
				
			return $array;
		}
		
		public function getLearn($userId){
		
			$array = array();
			$category = 'learning';
		
			$count = count($this->LEARN);
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				foreach ($this->LEARN[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($userId, $category, $num);
				}
				$array[] = $temp;
			}
		
			return $array;
		}
		
	}