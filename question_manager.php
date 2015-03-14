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
	require_once(dirname(__FILE__).'/storage_manager.php');
	require_once(dirname(__FILE__).'/xml_loader.php');


	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	class mod_groupformation_question_manager {
		
		private $groupformationid;
		private $store;
		private $xml;
		private $szenario;
		
		private $currentPosition = 1;
		private $currentCategory;
		
		public function __construct($groupformationid){
			$this->groupformationid = $groupformationid;
			$this->store = new mod_groupformation_storage_manager($groupformationid);
			$this->xml = new mod_groupformation_xml_loader();
			$this->init();
		}
		
		private function init(){
			$this->szenario = $this->store->getSzenario();
			var_dump($this->szenario);
		}
		
		public function getNextQuestion(){
			
		}
		
		public function getFirstQuestion(){
			$number = $this->store->firstQuestionNumber();
			
			$questions = array();
			
			if($number > 0){
				for($i = 1; $i <= $number; $i++){
					$array = $this->store->getQuestion($i);
					$questions[] = $array->type;
					$questions[] = $array->question;
					//$questions[] = $this->xml->xmlToArray($array->options);
					$questions[] = $array->options;
				}
			}
			
			var_dump($questions);
			return $questions;
		}
		
	}