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
		
		public function add_question($question){
			global $CFG, $DB;
			
			$data = new stdClass();
			$data->groupformation = $this->groupformationid;
			
			$data->type = $question['type'];
 			$data->question = $question['question'];
 			$data->options = $this->convertOptions($question['options']);
 			
 			var_dump($data);
 			
 			//var_dump($data);
 			if($DB->count_records('groupformation_question', array('groupformation' => $this->groupformationid)) == 0){
 				$DB->insert_record('groupformation_question', $data);
 			}	
		}
	
		public function catalogTableNotSet(){
			 global $CFG, $DB;
			// $indexes = $DB->get_indexes('groupformation_en_team');
			 $count = $DB->count_records('groupformation_en_team');
			 var_dump($count);
			 return $count == 0;
		}		

		public function add_catalog_question($question, $german, $category){
			global $CFG, $DB;
				
			$data = new stdClass();
				
			$data->type = $question['type'];
			$data->question = $question['question'];
			$data->options = $this->convertOptions($question['options']);
			
			if($german){
				if($category == 'team'){
					$DB->insert_record('groupformation_de_team', $data);
				}
			}else{
				if($category == 'team'){
					$DB->insert_record('groupformation_en_team', $data);
				}
			}
		}
		
		
		public function add_settings($knowledge, $szenario, $topics, $number){
            global $DB;
            
            $data = new stdClass();
            $data->groupformation = $this->groupformationid;
            
            $data->szenario = $szenario;
            $data->questionnumber = $number;
            $data->topicvalues = $this->convertOptions($topics);
            //TODO TippFehler in der Datenbank; muss noch behoben werden
            $data->knowledgevales = $this->convertOptions($knowledge);
            
            var_dump($data);
            if($DB->count_records('groupformation_q_settings', array('groupformation' => $this->groupformationid)) == 0){
            	$DB->insert_record('groupformation_q_settings', $data);
            }
		}
		
		private function convertOptions($options){
			$op = implode("</OPTION>  <OPTION>", $options);
			return "<OPTION>" . $op . "</OPTION>";
		}
		
		public function getQuestion($id, $category = null, $lang = 'en'){
			global $DB;
			
			$return;
			
			if($category == null){
				$return =  $DB->get_record('groupformation_question', array('groupformation' => $this->groupformationid, 'id' => $id));
			}
			
			if($category == 'team'){
				$table = "groupformation_" . $lang . "_team";
				$return = $DB->get_record($table, array('id' => $id));
				var_dump($return);
			}
			
			return $return;
		}
		
		public function getSzenario(){
			global $DB;
			
			$settings = $DB->get_record('groupformation_q_settings', array('groupformation' => $this->groupformationid));
			
			return $settings->szenario;
		}
		
		public function firstQuestionNumber(){
			global $DB;
			
			$count = $DB->count_records('groupformation_question', array('groupformation' => $this->groupformationid));
			
			return $count;
		}
	}