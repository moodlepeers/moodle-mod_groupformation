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
 * interface betweeen DB and Plugin
*
* @package mod_groupformation
* @copyright 2015 Nora Wester
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
// 	require_once(dirname(__FILE__).'/storage_manager.php');
// 	require_once(dirname(__FILE__).'/xml_loader.php');
	require_once($CFG->dirroot.'/mod/groupformation/moodle_interface/storage_manager.php');
	require_once($CFG->dirroot.'/mod/groupformation/util/xml_loader.php');


	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	class mod_groupformation_question_manager {
		
		private $MOTIVATION = 6;
		private $TEAM = 3;
		private $LEARNING = 5;
		private $CHARACTER = 4;
		private $GENERAL = 2;
		private $KNOWLEDGE = 1;
		private $TOPIC = 0;
		
		private $numbers = array();
		private $names = array('topic', 'knowledge', 'general','team', 'character', 'learning', 'motivation');
		
		private $groupformationid;
		private $store;
		private $xml;
		private $szenario;
		private $lang;
		
		private $currentCategoryPosition = 0;
	//	private $currentCategory;
		
		public function __construct($groupformationid, $lang){
			$this->groupformationid = $groupformationid;
			$this->lang = $lang;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
			$this->xml = new mod_groupformation_xml_loader();
			$this->init();
		}
		
		private function init(){
			if($this->store->existSetting()){
				$this->szenario = $this->store->getSzenario();
			}
			//var_dump($this->szenario);
			if(!$this->store->catalogTableNotSet()){
				$this->numbers = $this->store->getNumbers($this->names);
			}
			
		}
		
		public function hasNext(){
			
			if($this->currentCategoryPosition > -1){
				if($this->numbers[$this->currentCategoryPosition] == 0){
					$this->currentCategoryPosition++;
				}
			
				if($this->numbers[$this->currentCategoryPosition] == 1){
					$this->currentCategoryPosition++;
				}
			}
			return $this->currentCategoryPosition != -1;
		}
		
		public function getNextQuestion(){
			
			if($this->currentCategoryPosition != -1){
			
				$questions = array();
				
				if($this->currentCategoryPosition < 2){
					
						$temp = $this->store->getDozentQuestion($this->names[$this->currentCategoryPosition]);
						$values = $this->xml->xmlToArray('<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS>' . $temp . '</OPTIONS>');
						foreach ($values as $value){
							$question = array();
							$question[] = 'type';
							$question[] = $value;
							$question[] = 'options';
							$questions[] = $question;
						}
					
				}else{
				
					for($i = 1; $i <= $this->numbers[$this->currentCategoryPosition]; $i++){
						$array = $this->store->getCatalogQuestion($i, $this->names[$this->currentCategoryPosition], $this->lang);
						$question = array();
						$question[] = $array->type;
						$question[] = $array->question;
						$o = $array->options;
						$question[] = $this->xml->xmlToArray('<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS>' . $o . '</OPTIONS>');
						//$questions[] = $array->options;
						$questions[] = $question;
					}
				}		
				//hier wird schon die neue Kategory angesetzt
				//deswegen muss beim holen der Answers zurück gerechnet werden
				$this->currentCategoryPosition++;
				return $questions;
			}
		}
		
		// von jeder Frage muss auch eine Antwort im array vorhanden sein
		public function saveAnswers($userId, array $answers){
			$temp = 1;
			foreach($answers as $answer){
				$this->store->saveAnswer($userId, $answer, $this->names[$this->currentCategoryPosition-1], $temp);
				$temp++;
			}
			
			//schauen, ob an der Categoryposition noch etwas geändert werden muss bezüglich des Szenarios
			$this->getNextCategory();
			if($this->currentCategoryPosition-1 == 0){
				$this->store->statusChanged($userId);
			}
		}
		
		private function getNextCategory(){
			if($this->szenario == 'project'){
				if($this->currentCategoryPostiton == $this->LEARNING){
					$this->currentCategoryPosition++;
				}
			}
			
			if($this->szenario == 'homework'){
				if($this->currentCategoryPostiton == $this->MOTIVATION){
					$this->currentCategoryPosition++;
				}
			}
			


			if($this->currentCategoryPosition == 7 || $this->szenario == 'presentation'){
				$this->currentCategoryPosition = -1;
 			}
		}
		
		public function questionsToAnswer($userId){
			return $this->store->answeringStatus($userId) != 1;
		}
		
		public function hasAnswers($userId){
			$firstCondition = $this->store->answeringStatus($userId) == 0;
			$secondCondition = $this->store->answerExist($userId, $this->names[$this->currentCategoryPosition-1], 1);
			return $firstCondition && $firstCondition;
		}
		
		public function getAnswers($userId){
			$array = array();
			
			$answers = $this->store->getAnswer($userId, $this->names[$this->currentCategoryPosition-1]);
			foreach($answers as $answer){
				$temp = array(
						'position' => $answer->position,
						'answer' => $answer->answer
				);
				
				$array[] = $temp;
			}
			
			return $array;
		}
		
		public function getCurrentCategory(){
			return $this->names[$this->currentCategoryPosition];
		}
		
		public function commited($userId){
			$this->store->statusChanged($userId);
		}
// 		public function saveFirstAnswers($userId, array $answers){
// 			$temp = 0;
// 			foreach($answers as $answer){
// 				$this->store->saveAnswer($userId, $answer, 'general', $temp);
// 				$temp++;
// 			}
// 		}
		
// 		public function getFirstQuestion($userId){
			
// 			$answerStatus = $this->store->answeringStatus($userId);
			
// 			if($this->store->existSetting() && $this->currentCategoryPosition == 0 && $answerStatus != 1){
			
// 				$number = $this->store->firstQuestionNumber();
			
// 				$questions = array();
			
// 				if($number > 0){
// 					for($i = 1; $i <= $number; $i++){
// 						$array = $this->store->getQuestion($i);
// 						$questions[] = $array->type;
// 						$questions[] = $array->question;
// 						//$o = $array->options;
// 						//$questions[] = $this->xml->xmlToArray('<OPTIONS>' . $o . '</OPTIONS>');
// 						$questions[] = $array->options;
// 					}
// 				}
			
// 				//var_dump($questions);
// 				$this->currentCategoryPosition++;
// 				return $questions;
// 			}
// 		}
	}