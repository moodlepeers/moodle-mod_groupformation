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
 * Question controller
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *         
 */
// require_once(dirname(__FILE__).'/storage_manager.php');
// require_once(dirname(__FILE__).'/xml_loader.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}
class mod_groupformation_question_controller {
	private $SAVE = 0;
	private $COMMIT = 1;
	private $status;
	private $numbers = array ();
	private $names = array ();
	private $groupformationid;
	private $store;
	private $user_manager;
	private $xml;
	private $scenario;
	private $lang;
	private $userId;
	private $currentCategoryPosition = 0;
	private $numberOfCategory;
	private $data;
	private $hasAnswer;
	
	/**
	 * Constructs an instance of question controller
	 *
	 * @param int $groupformationid        	
	 * @param string $lang        	
	 * @param int $userId        	
	 * @param string $oldCategory        	
	 */
	public function __construct($groupformationid, $lang, $userId, $oldCategory) {
		$this->groupformationid = $groupformationid;
		$this->lang = $lang;
		$this->userId = $userId;
		$this->store = new mod_groupformation_storage_manager ( $groupformationid );
		$this->user_manager = new mod_groupformation_user_manager ( $groupformationid );
		$this->xml = new mod_groupformation_xml_loader ();
		$this->data = new mod_groupformation_data ();
		$this->scenario = $this->store->get_scenario ();
		$this->names = $this->store->get_categories ();
		$this->numberOfCategory = count ( $this->names );
		$this->init ( $userId );
		$this->set_internal_number ( $oldCategory );
	}
	
	/**
	 * Returns where questionnaire has been submitted or not
	 *
	 * @return boolean
	 */
	public function has_committed() {
		$status = $this->user_manager->get_answering_status ( $this->userId );
		return $status == 1;
	}
	
	// --- Mathevorkurs
	// public function goNotOn(){
	// $this->go_internal_back(1);
	// }
	public function has_all_answered() {
		return $this->store->has_answered_everything ( $this->userId );
	}
	// ---
	public function go_back() {
		$this->go_internal_back ( 2 );
	}
	private function go_internal_back($back) {
		while ( $back > 0 && $this->currentCategoryPosition != 0 ) {
			if ($this->numbers [$this->currentCategoryPosition] != 0) {
				$back = $back - 1;
			}
			$this->currentCategoryPosition = $this->currentCategoryPosition - 1;
		}
	}
	public function get_percent($category = null) {
		if (! is_null ( $category )) {
			$categories = $this->store->get_categories ();
			$pos = array_search ( $category, $categories );
			return 100.0 * ((1.0 * $pos) / count ( $categories ));
		}
		
		$total = 0;
		$sub = 0;
		
		$temp = 0;
		
		foreach ( $this->numbers as $num ) {
			if ($num != 0) {
				$total ++;
				if ($temp < $this->currentCategoryPosition) {
					$sub ++;
				}
			}
			
			$temp ++;
		}
		
		return ($sub / $total) * 100;
	}
	
	private function init($userid) {
		if (! $this->store->catalog_table_not_set ()) {
			$this->numbers = $this->store->get_numbers ( $this->names );
		}
		
		$this->status = $this->user_manager->get_answering_status( $userid );
	}
	
	private function set_internal_number($category) {
		if ($category != "") {
			$this->currentCategoryPosition = $this->store->get_position ( $category );
			$this->currentCategoryPosition ++;
		}
	}
	
	/**
	 * Returns whether there is a next category or not
	 *
	 * @return boolean
	 */
	public function has_next() {
		if ($this->currentCategoryPosition >= 0 && $this->currentCategoryPosition < $this->numberOfCategory) {
			while ( $this->currentCategoryPosition < $this->numberOfCategory && $this->numbers [$this->currentCategoryPosition] == 0 ) {
				$this->currentCategoryPosition ++;
			}
		}
		return ($this->currentCategoryPosition != - 1 && $this->currentCategoryPosition < $this->numberOfCategory);
	}
	
	/**
	 * Returns question in current language or possible default language
	 *
	 * @param int $i        	
	 * @return stdClass
	 */
	public function get_question($i) {
		$record = $this->store->get_catalog_question ( $i, $this->names [$this->currentCategoryPosition], $this->lang );
		
		if (empty ( $record )) {
			if ($this->lang != 'en') {
				$record = $this->store->get_catalog_question ( $i, $this->names [$this->currentCategoryPosition], 'en' );
			} else {
				$lang = $this->store->get_possible_language ( $this->names [$this->currentCategoryPosition] );
				$record = $this->store->get_catalog_question ( $i, $this->names [$this->currentCategoryPosition], $lang );
			}
		}
		
		return $record;
	}
	/**
	 * Returns whether current category is 'topic' or not
	 *
	 * @return boolean
	 */
	public function is_topics() {
		return $this->currentCategoryPosition == $this->store->get_position ( 'topic' );
	}
	
	/**
	 * Returns whether current category is 'knowledge' or not
	 *
	 * @return boolean
	 */
	public function is_knowledge() {
		return $this->currentCategoryPosition == $this->store->get_position ( 'knowledge' );
	}
	
	/**
	 * Returns whether current category is 'points' or not
	 *
	 * @return boolean
	 */
	public function is_points() {
		return $this->currentCategoryPosition == $this->store->get_position ( 'points' );
	}
	
	/**
	 * Returns questions
	 *
	 * @return array
	 */
	public function get_next_questions() {
		if ($this->currentCategoryPosition != - 1) {
			
			$questions = array ();
			
			$this->hasAnswer = $this->has_answers ();
			
			if ($this->is_knowledge () || $this->is_topics ()) {
				// ---------------------------------------------------------------------------------------------------------
				$temp = $this->store->get_knowledge_or_topic_values ( $this->names [$this->currentCategoryPosition] );
				$values = $this->xml->xmlToArray ( '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>' );
				
				$text = '';
				
				$type;
				
				if ($this->is_topics ()) {
					$type = 'type_topics';
				} else {
					$type = 'type_knowledge';
				}
				
				$options = $options = array (
						100 => get_string ( 'excellent', 'groupformation' ),
						0 => get_string ( 'none', 'groupformation' ) 
				);
				
				$position = 1;
				$questionsfirst = array ();
				$answerPosition = array ();
				
				foreach ( $values as $value ) {
					$question = array ();
					$question [] = $type;
					$question [] = $text . $value;
					$question [] = $options;
					if ($this->hasAnswer) {
						$answer = $this->store->get_single_answer ( $this->userId, $this->names [$this->currentCategoryPosition], $position );
						if ($answer != false) {
							$question [] = $answer;
						} else {
							$question [] = - 1;
						}
						$answerPosition [$answer] = $position - 1;
						$position ++;
					}
					
					$questionsfirst [] = $question;
				}
				
				$l = count ( $answerPosition );
				
				if ($l > 0 && $this->currentCategoryPosition == $this->store->get_position ( 'topic' )) {
					for($k = 1; $k <= $l; $k ++) {
						$h = $questionsfirst [$answerPosition [$k]];
						$h [] = $answerPosition [$k];
						$questions [] = $h;
					}
				} else {
					$questions = $questionsfirst;
				}
				// ---------------------------------------------------------------------------------------------------------
			} elseif ($this->is_points ()) {
				// ---------------------------------------------------------------------------------------------------------
				for($i = 1; $i <= $this->numbers [$this->currentCategoryPosition]; $i ++) {
					$record = $this->get_question ( $i );
					
					$question = array ();
					
					if (count ( $record ) == 0) {
						echo '<div class="alert">This questionaire site is neither available in your favorite language nor in english!</div>';
						return null;
					} else {
						
						$question [] = 'type_points';
						$question [] = $record->question;
						$question [] = $options = $options = array (
								$this->store->get_max_points () => get_string ( 'excellent', 'groupformation' ),
								0 => get_string ( 'bad', 'groupformation' ) 
						);
						if ($this->hasAnswer) {
							$answer = $this->store->get_single_answer ( $this->userId, $this->names [$this->currentCategoryPosition], $i );
							if ($answer != false) {
								$question [] = $answer;
							} else {
								$question [] = - 1;
							}
						}
					}
					
					$questions [] = $question;
				}
				// ---------------------------------------------------------------------------------------------------------
			} else {
				// ---------------------------------------------------------------------------------------------------------
				for($i = 1; $i <= $this->numbers [$this->currentCategoryPosition]; $i ++) {
					$record = $this->get_question ( $i );
					
					$question = $this->prepare_question ( $i, $record );
					
					$questions [] = $question;
				}
				// ---------------------------------------------------------------------------------------------------------
			}
			$this->currentCategoryPosition ++;
			
			return $questions;
		}
	}
	
	/**
	 * Returns question array constructed by question record
	 *
	 * @param unknown $record        	
	 * @return multitype:number NULL multitype:string Ambigous <number, mixed>
	 */
	public function prepare_question($i, $record) {
		$question = array ();
		if (count ( $record ) == 0) {
			echo '<div class="alert">This questionaire site is neither available in your favorite language nor in english!</div>';
			return null;
		} else {
			
			$question [] = $record->type;
			$question [] = $record->question;
			$question [] = $this->xml->xmlToArray ( '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $record->options . ' </OPTIONS>' );
			
			if ($this->hasAnswer) {
				$answer = $this->store->get_single_answer ( $this->userId, $this->names [$this->currentCategoryPosition], $i );
				if ($answer != false) {
					$question [] = $answer;
				} else {
					$question [] = - 1;
				}
			}
		}
		return $question;
	}
	
	/**
	 * TODO comment
	 *
	 * @return boolean
	 */
	public function has_answers() {
		$firstCondition = ($this->user_manager->get_answering_status ( $this->userId ) > - 1);
		$secondCondition = ($this->store->get_answers ( $this->userId, $this->names [$this->currentCategoryPosition] ) > 0);
		return ($firstCondition && $secondCondition);
	}
	
	/**
	 * Returns current category
	 *
	 * @return multitype:
	 */
	public function get_current_category() {
		return $this->names [$this->currentCategoryPosition];
	}
}