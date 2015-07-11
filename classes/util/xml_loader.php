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

//defined('MOODLE_INTERNAL') || die();  -> template

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}
	
	//require_once(dirname(__FILE__).'/storage_manager.php');
	require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');
	
	class mod_groupformation_xml_loader{
		
		private $storeM;
		
		public function __construct(){
				
		}
		
		// damit die Klasse die groupformationId nicht wissen muss, übergebe den kompletten storage_manager wenn du mir den Fragenkatalogen arbeitest
		public function setStore(mod_groupformation_storage_manager $store){
			$this->storeM = $store;
		}
		
		// bereitet das speichern der Daten aus einem bestimmten xml-Satz ( defineirt durch Kategorie ) vor
		// wenn schon etwas in der Datenbank zu dieser Kategorie enthalten ist, lösche alle Einträge
		// ansonsten speicher die Daten für englisch und deutsch und gebe die jeweiligen Versionsnummern und Fragenanzahl als array in einem array zurück  
		public function saveData($category){
			$array = array();
			$init = $this->storeM->catalogTableNotSet($category);
			if($init == FALSE){
				$this->storeM->delete_all_catalog_questions($category);
			}
			$array[] = $this->save($category, 'en');
			$array[] = $this->save($category, 'de');
			
			return $array;
		}
		
		//Überprüft, ob die Versionsnummern im xml und der Datenbank übereinstimmen
		//wenn nicht, wird die Datenbank erneuert
		public function latestVersion($category){
			global $CFG;
			
			//hier müsste man später noch die Versionsnummern von allen Sprachdateien überprüfen
			$xmlFile = $CFG->dirroot.'/mod/groupformation/xml_question/question_de_'.$category.'.xml';
				
			if (file_exists($xmlFile)) {
				$xml = simplexml_load_file($xmlFile);
					
				$version = trim($xml->QUESTIONS['VERSION']);
				if($this->storeM->latestVersion($category, $version)){
					
				}else{
					$array = $this->saveData($category);
					$number = $array[0][1];
					// falls in der englischen Datei nichts drin ist, nehme die Fragenanzahl der deutschen Datei
					if($array[1][1] > $number){
						$number = $array[1][1];
					}
					// speicher die Versionsnummer
					$this->storeM->add_catalog_version($category, $number, $version, FALSE);
				}
			}else{
				exit("Datei $xmlFile kann nicht geöffnet werden.");
			}
		}
		
		/**
		 * gibt ein array zurück, wo auf Position 0 die version und auf Position 1 die Anzahl der Fragen zu finden ist
		 * @param unknown $category welche Kategory
		 * @param unknown $german bool ob deutsch oder nicht 
		 */
		private function save($category, $lang){
			global $CFG;
			$xmlFile = $CFG->dirroot.'/mod/groupformation/xml_question/question_'.$lang.'_'.$category.'.xml';
			
			$return = array();
			
			if (file_exists($xmlFile)) {
				$xml = simplexml_load_file($xmlFile);
			
				$return[] = trim($xml->QUESTIONS['VERSION']);
				$numbers = 0;
				
				foreach ( $xml->QUESTIONS->QUESTION as $question )
				{	
					$options = $question->OPTIONS;
					$optionArray = array();
					//options zerlegen
					foreach ($options->OPTION as $option){
						$optionArray[] = trim($option);
					}
					
					$numbers++;
					
					$array = array('type' => trim($question['TYPE']),
							'question' => trim($question->QUESTIONTEXT),
							'options' => $optionArray,
							'position' => $numbers
					);

					//speichert die Fragen in der Datenbank ab
					$this->storeM->add_catalog_question($array, $lang, $category);
				}
				
				$return[] = $numbers;
				return $return;
			
			} else {
				exit("Datei $xmlFile kann nicht geöffnet werden.");
			}
			
		}
		
		// wandle den xml String aus der Datenbank (Feld options) in ein array
		public function xmlToArray($xmlContent){
			//var_dump($xmlContent);
			//$xml = simplexml_load_file($xmlContent);
			$xml = simplexml_load_string($xmlContent);
			$optionArray = array();
			foreach ($xml->OPTION as $option){
				$optionArray[] = trim($option);
			}
			
			return $optionArray;
		}
	}
