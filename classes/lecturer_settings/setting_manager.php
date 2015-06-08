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

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
	
	//require_once 'storage_manager.php';
	require_once(dirname(__FILE__).'/classes/moodle_interface/storage_manager.php');

	
	class mod_groupformation_setting_manager {

		private $groupformationid;
		private $szenario;
		private $topicValues;
		private $knowledgeValues;
		private $store;
		
		private $number = 0;

		/**
		 * 
		 * @param unknown $groupformationid
		 * @param unknown $szenario
		 * @param array $topicValues
		 * @param array $knowledgeValues
		 */
		public function __construct($groupformationid, $szenario, array $topicValues, array $knowledgeValues){
			$this->groupformationid = $groupformationid;
			$this->szenario = $szenario;
			$this->knowledgeValues = $knowledgeValues;
			$this->topicValues = $topicValues;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
		}
		
		/**
		 * 
		 * @param $german indicates whether the question should be in german
		 */
		public function create_Questions($german){
			//'Sprache für die Gruppenarbeit / Language for Team Work'
			
			if($german){
				$languageQ = "Bitte wählen Sie, in welcher Sprache es Ihnen möglich ist mit ihrer Gruppe zu kommunizieren";
				$options = array ("deutsch", "deutsch/englisch", "englisch");
			}
			else {
				$languageQ = "Please select in which languages you can possibly communicate with your team.";
				$options = array ("german", "german/english", "english");
			}
			
			$question = array('type' => 'dropdown',
					'page' => 1,
					'question' => $languageQ,
					'category' => 'general',
					'options' => $options
			);
			
			$this->store->add_Question($question);
			$this->number++;
			
			if(german){
				$options = array ("sehr gut","","","","nicht vorhanden");
			} else {
				$options = array ("excellent", "", "", "","none");
			}
			
			if($this->szenario != 'seminar'){
				foreach($this->knowledgeValues as $knowledge){
			
					if($german)
						$knowledgename = "Wie schätzen Sie ihr persönliches Vorwissen in $knowledge ein?";
					else $knowledgename = "";
				
					$question = array('type' => 'dropdown',
							'page' => 1,
							'question' => $knowledgename,
							'category' => 'general',
							'options' => $options
					);
					
					$this->store->add_question($question);
					$this->number++;
				}
			}
			
			//Bitte sortieren Sie die zur Wahl stehenden Themen entsprechend Ihrer Präferenz, beginnend mit Ihrem bevorzugten Thema.
			//Please sort topics available according to your preference, starting with your prefered topic.
			foreach($this->topicValues as $topic){
				
				if($german)
					$topicname = "Wie groß ist Ihr Interesse an $topic";
				else $topicname = "";
				
				$question = array('type' => 'dropdown',
						'page' => 1,
						'question' => $topicname,
						'category' => 'general',
						'options' => $options
				);
				
				$this->store->add_question($question);
				$this->number++;
			}
			
			//je nach szenario andere Werte und Fragen
			if($this->szenario == 'project'){
					//importieren Persönlichkeit motivation (Teamoreintierung)
			} 
			
			if($this->szenario == 'homework'){
					//importieren Persönlichkeit{ohne 4,5,9,10} Lernstil (Teamorientierung)
			} 
		}
		
		public function save_settings(){
			
			$this->store->add_settings($this->knowledgeValues, $this->szenario, $this->topicValues, $this->number);
		}
	}