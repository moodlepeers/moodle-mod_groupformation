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
//require_once($CFG->dirroot.'/mod/groupformation/classes/util/util.php');



	class mod_groupformation_calculateCriterions {

		private $store;
		private $groupformationid;
		private $userId;
		
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
		public function __construct($groupformationid, $userId){
			$this->groupformationid = $groupformationid;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
		
		}
		
		private function inverse($qId, $category, $answer){
			$max = $this->store->getMaxOfCatalogQuestionOptions($qId, $category);
			//Da intern bei 0 und nicht bei 1 angefangen wird
			$max++;
			return $max - $answer;
			
		}
		
		public function getBig5(){
			
			$array = array();
			$category = 'character';
			
			$count = count($this->BIG5);
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				foreach ($this->BIG5[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($this->userId, $category, $num);
				}
				foreach ($this->BIG5Invert[$i] as $num){
					$temp = $temp + $this->inverse($num, $category, $this->store->getSingleAnswer($this->userId, $category, $num));
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
		
		public function getFAM(){
				
			$array = array();
			$category = 'motivation';
				
			$count = count($this->FAM);
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				foreach ($this->FAM[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($this->userId, $category, $num);
				}
				$array[] = $temp;
			}
				
			return $array;
		}
		
		public function getLearn(){
		
			$array = array();
			$category = 'learning';
		
			$count = count($this->LEARN);
			for($i = 0; $i<$count; $i++){
				$temp = 0;
				foreach ($this->LEARN[$i] as $num){
					$temp = $temp + $this->store->getSingleAnswer($this->userId, $category, $num);
				}
				$array[] = $temp;
			}
		
			return $array;
		}
		
	}