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
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
	//TODO einige Methoden noch nicht getestet
//defined('MOODLE_INTERNAL') || die();  -> template
	//namespace mod_groupformation\moodle_interface;

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}
	
	class mod_groupformation_storage_manager {

		private $groupformationid;
		
		public function __construct($groupformationid){
			$this->groupformationid = $groupformationid;
		}
		
// 		public function add_question($question){
// 			global $CFG, $DB;
			
// 			$data = new stdClass();
// 			$data->groupformation = $this->groupformationid;
			
// 			$data->type = $question['type'];
//  			$data->question = $question['question'];
//  			$data->options = $this->convertOptions($question['options']);
 			
//  			var_dump($data);
 			
//  			//var_dump($data);
//  			if($DB->count_records('groupformation_question', array('groupformation' => $this->groupformationid)) == 0){
//  				$DB->insert_record('groupformation_question', $data);
//  			}	
// 		}
	
		//es wird davon ausgegangen, dass alle Fragentabellen immer auf dem gleichen Stand sind
		public function catalogTableNotSet($category = 'general'){
			 global $CFG, $DB;
			// $indexes = $DB->get_indexes('groupformation_en_team');
			 $count = $DB->count_records('groupformation_'.$category);
			 //var_dump($count);
			 return $count == 0;
		}		

		public function delete_old_catalog_question($category){
			global $DB;
			$DB->delete_records('groupformation_'.$category);
		}
		
		public function add_catalog_question($question, $language, $category){
			global $CFG, $DB;
				
			$data = new stdClass();
				
			$data->type = $question['type'];
			$data->question = $question['question'];
			$data->options = $this->convertOptions($question['options']);
			$data->position = $question['position'];
			$data->language = $language;
			
			$DB->insert_record('groupformation_' . $category, $data);
			
		}
		
		public function getTotalUserIds(){
			global $DB;
				
			$array = array();
			$records = $DB->get_records('groupformation_answer', array('groupformation' => $this->groupformationid));
			foreach($records as $record){
				$array[] = $record->userid;
			}
				
			return $array;
		}
		
		public function getUserIdsCompleted(){
			global $DB;
			
			$array = array();
			$records = $DB->get_records('groupformation_started', array('groupformation' => $this->groupformationid, 'completed' => '1'));
			foreach($records as $record){
				$array[] = $record->userid;
			}
		}
		
		public function answerNumberForUser($userId){
			global $DB;
				
			return $DB->count_records('groupformation_answer', array('groupformation' => $this->groupformationid, 'userid' => $userId));
		}
		
		
		public function add_catalog_version($category, $numbers, $version, $init){
			global $DB;

			$data = new stdClass();
			$data->category = $category;
			$data->version = $version;
			$data->numberofquestion = $numbers;
			
			if($init){
				$DB->insert_record('groupformation_q_version', $data);
			}else{
				$data->id = $DB->get_field('groupformation_q_version', 'id', array('category' => $category));
				$DB->update_record('groupformation_q_version', $data);
			}
		}
		
		public function latestVersion($category, $version){
			global $DB;
			
			$count = $DB->count_records('groupformation_q_version', array('category' => $category, 'version' => $version));
			
			return $count == 1;
		}
		
		// $init true, wenn es eine initialisierung ist | false wenn es ein Update ist
		public function add_setting_question($knowledge, $topics, $init){
            global $DB;
            
            $data = new stdClass();
            $data->groupformation = $this->groupformationid;
            $data->szenario = 'v';
            $data->topicvalues = $this->convertOptions($topics);
            $data->knowledgevalues = $this->convertOptions($knowledge);
            $data->topicvaluesnumber = count($topics);
            $data->knowledgevaluesnumber = count($knowledge);
            
            
            if($init){
            	$DB->insert_record('groupformation_q_settings', $data);
            }elseif ($DB->count_records('groupformation_answer', array('groupformation' => $this->groupformationid)) == 0){
            	$data->id = $DB->get_field('groupformation_q_settings', 'id', array('groupformation' => $this->groupformationid));
            	$DB->update_record('groupformation_q_settings', $data);
            }
		}
		
		// gibt ein array zurück, in dem auf der ersten Position die Startzeit gespeichert ist und auf der zweiten Position die Endzeit
		public function getTime(){
			global $DB;
			$times = array();
			$times[] = $DB->get_field('groupformation', 'timeopen', array('id' => $this->groupformationid));
			$times[] = $DB->get_field('groupformation', 'timeclose', array('id' => $this->groupformationid));
			
			return $times;
		}
		
		private function convertOptions($options){
			$op = implode("</OPTION>  <OPTION>", $options);
			return "<OPTION>" . $op . "</OPTION>";
		}
		
		public function getNumbers(array $names){
			global $DB;
			
			$array = array();
			foreach($names as $name){
				if($name == 'topic' || $name == 'knowledge'){
					$array[] = $DB->get_field('groupformation_q_settings', $name . 'valuesnumber', array('groupformation' => $this->groupformationid));
				}else{
					$array[] = $DB->get_field('groupformation_q_version', 'numberofquestion', array('category' => $name));
				}	
			}
			
			return $array;
		}
		
		public function getDozentQuestion($category){
			global $DB;
			
			return $DB->get_field('groupformation_q_settings', $category . 'values', array('id' => $this->groupformationid));
		}
		
		public function getCatalogQuestion($i, $category = 'general', $lang = 'en'){
			global $DB;
			
// 			if($category == null){
// 				$return =  $DB->get_record('groupformation_question', array('groupformation' => $this->groupformationid, 'id' => $id));
// 			}
			
// 			if($category == 'team'){
// 				$table = "groupformation_" . $lang . "_team";
// 				$return = $DB->get_record($table, array('id' => $id));
// 				var_dump($return);
// 			}

			$table = "groupformation_" . $category;
			$return = $DB->get_record($table, array('language' => $lang, 'position' => $i));
			
			return $return;
		}
		
		public function getSzenario(){
			global $DB;
			
			$settings = $DB->get_record('groupformation', array('id' => $this->groupformationid));
			
			return $settings->szenario;
		}
		
// 		public function firstQuestionNumber(){
// 			global $DB;
			
// 			$count = $DB->count_records('groupformation_question', array('groupformation' => $this->groupformationid));
			
// 			return $count;
// 		}
		
		public function statusChanged($userId){
			global $DB;
			
			$status = $this->answeringStatus($userId);
			
			$data = new stdClass();
			$data->groupformation = $this->groupformationid;
			$data->userid = $userId;
			
			if($status == -1){
				$data->completed = 0;
				$DB->insert_record('groupformation_started', $data);
			}
			
			if($status == 0){
				$data->completed = 1;
				$data->id = $DB->get_field('groupformation_started', 'id', array('groupformation' => $this->groupformationid, 'userid' => $userId));
				$DB->update_record('groupformation_started', $data);
			}
		}
		
		// -1 kein Eintrag, 0 hat schon mal angefangen zu beantworten, 1 hat seine Antworten abgegeben
		public function answeringStatus($userId){
			global $DB;
			
			$seen = $DB->count_records('groupformation_started', array('groupformation' => $this->groupformationid, 'userid' => $userId, 'completed' => '0'));
			$completed = $DB->count_records('groupformation_started', array('groupformation' => $this->groupformationid, 'userid' => $userId, 'completed' => '1'));
			
			if($seen == 1){
				return 0;
			}elseif ($completed == 1){
				return 1;
			}else{
				return -1;
			}
		}
		
		//$completed gibt an, ob nur die Anzahl der abgegebenen Fragebögen zurückgegeben werden soll
		public function getNumberofAnswerStauts($completed){
			global $DB;
				
			$number = 0;
			$number = $DB->count_records('groupformation_started', array('groupformation' => $this->groupformationid, 'completed' => '1'));
			if(!$completed){
				$number = $number + $DB->count_records('groupformation_started', array('groupformation' => $this->groupformationid, 'completed' => '0'));
			}
			
			return $number;
		}
		
		public function answerExist($userId, $category, $questionId){
			global $DB;
			
			$count = $DB->count_records('groupformation_answer', array('groupformation' => $this->groupformationid, 'userid' => $userId,
					'category' => $category, 'questionid' => $questionId));
			//var_dump($count);
			return $count == 1;
		}
		
		
		public function getAnswer($userId, $category){
			global $DB;
			
			return $DB->get_records('groupformation_answer', array('groupformation' => $this->groupformationid, 'userid' => $userId, 'category' => $category));
		}
		
		public function saveAnswer($userId, $answer, $category, $questionId){
			global $DB;
			
			$answerAlreadyExist = $this->answerExist($userId, $category, $questionId);
			
			$data = new stdClass();
			$data->groupformation = $this->groupformationid;
			
			$data->userid = $userId;
			$data->category = $category;
			$data->questionid = $questionId;
			$data->answer = $answer;
			
			if(!$answerAlreadyExist){
				$DB->insert_record('groupformation_answer', $data);
			}else{
				$data->id = $DB->get_field('groupformation_answer', 'id', array('groupformation' => $this->groupformationid, 'userid' => $userId, 
					'category' => $category, 'questionid' => $questionId));
				$DB->update_record('groupformation_answer', $data);
			}
		}
		
// 		public function existSetting(){
// 			global $DB;
			
// 			$count = $DB->count_records('groupformation_q_settings', array('groupformation' => $this->groupformationid));
			
// 			return $count == 1;
// 		}
	}