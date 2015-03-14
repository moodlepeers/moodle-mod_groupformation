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
 * @copyright 2015 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die();  -> template

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}
	
	require_once(dirname(__FILE__).'/storage_manager.php');
	
	class mod_groupformation_xml_loader{
		
		private $storeM;
		
		public function __construct(){
				
		}
		
		public function setStore(mod_groupformation_storage_manager $store){
			$this->storeM = $store;
		}
		
		
		public function saveData($category){
			//$this->save($category, TRUE);
			$this->save($category, FALSE);
		}
		/**
		 * 
		 * @param unknown $category welche Kategory
		 * @param unknown $german bool ob deutsch oder nicht 
		 */
		private function save($category, $german){
			
			if($german){
				$xmlFile = 'question_de_'.$category.'.xml';
			}else{
				$xmlFile = 'question_en_'.$category.'.xml';
			}
			
			if (file_exists($xmlFile)) {
				$xml = simplexml_load_file($xmlFile);
			
				foreach ( $xml->QUESTIONS->QUESTION as $question )
				{	
					$options = $question->OPTIONS;
					$optionArray = array();
					foreach ($options->OPTION as $option){
						$optionArray[] = trim($option);
					}
					//options noch zerteilen
					$array = array('type' => trim($question['TYPE']),
							'category' => trim($question->CATEGORY),
							'question' => trim($question->QUESTIONTEXT),
							'options' => $optionArray
					);

					$this->storeM->add_catalog_question($array, $german, $category);
				}
			
			} else {
				exit("Datei $xmlFile kann nicht geöffnet werden.");
			}
			
		}
		
		public function xmlToArray($xmlContent){
			$xml = simplexml_load_file($xmlContent);
			$optionArray = array();
			foreach ($xml->OPTION as $option){
				$optionArray[] = trim($option);
			}
			
			return $optionArray;
		}
	}
