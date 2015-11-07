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
require_once ($CFG->dirroot . '/mod/groupformation/classes/questionnaire/radio_question.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/questionnaire/topics_table.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/questionnaire/range_question.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/questionnaire/dropdown_question.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/questionnaire/question_table_header.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/xml_loader.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}
class mod_groupformation_questionnaire_controller {
	private $SAVE = 0;
	private $COMMIT = 1;
	private $status;
	private $numbers = array ();
	private $categories = array ();
	private $groupformationid;
	private $store;
	private $user_manager;
	private $xml;
	private $scenario;
	private $lang;
	private $userid;
	private $cmid;
	private $context;
	private $currentCategoryPosition = 0;
	private $numberOfCategory;
	private $hasAnswer;
	private $current_category;
	
	/**
	 * Constructs an instance of question controller
	 *
	 * @param int $groupformationid        	
	 * @param string $lang        	
	 * @param int $userid        	
	 * @param string $oldCategory        	
	 */
	public function __construct($groupformationid, $lang, $userid, $old_category, $cmid) {
		$this->groupformationid = $groupformationid;
		$this->lang = $lang;
		$this->userid = $userid;
		$this->cmid = $cmid;
		$this->store = new mod_groupformation_storage_manager ( $groupformationid );
		$this->user_manager = new mod_groupformation_user_manager ( $groupformationid );
		$this->xml = new mod_groupformation_xml_loader ();
		$this->scenario = $this->store->get_scenario ();
		$this->categories = $this->store->get_categories ();
		$this->numberOfCategory = count ( $this->categories );
		$this->init ( $userid );
		$this->set_internal_number ( $old_category );
		$this->context = context_module::instance ( $this->cmid );
	}
	
	// --- Mathevorkurs
	// public function goNotOn(){
	// $this->currentCategoryPosition = max($this->currentCategoryPosition - 1,0);
	// }
	
	// ---
	/**
	 * Triggers going a category page back
	 */
	public function go_back() {
		$this->currentCategoryPosition = max ( $this->currentCategoryPosition - 2, 0 );
	}
	
	/**
	 * Returns percent of progress in questionnaire
	 *
	 * @param string $category        	
	 * @return number
	 */
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
	
	/**
	 * Handles initialization
	 *
	 * @param unknown $userid        	
	 */
	private function init($userid) {
		if (! $this->store->catalog_table_not_set ()) {
			$this->numbers = $this->store->get_numbers ( $this->categories );
		}
		
		$this->status = $this->user_manager->get_answering_status ( $userid );
	}
	
	/**
	 * Sets internal page number
	 *
	 * @param unknown $category        	
	 */
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
		return ($this->currentCategoryPosition != - 1 && $this->currentCategoryPosition < $this->numberOfCategory);
	}
	
	/**
	 * Returns question in current language or possible default language
	 *
	 * @param int $i        	
	 * @return stdClass
	 */
	public function get_question($i) {
		$record = $this->store->get_catalog_question ( $i, $this->current_category, $this->lang );
		
		if (empty ( $record )) {
			if ($this->lang != 'en') {
				$record = $this->store->get_catalog_question ( $i, $this->current_category, 'en' );
			} else {
				$lang = $this->store->get_possible_language ( $this->current_category );
				$record = $this->store->get_catalog_question ( $i, $this->current_category, $lang );
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
			
			$this->hasAnswer = $this->user_manager->has_answers ( $this->userid, $this->current_category );
			
			if ($this->is_knowledge () || $this->is_topics ()) {
				// ---------------------------------------------------------------------------------------------------------
				$temp = $this->store->get_knowledge_or_topic_values ( $this->current_category );
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
						$answer = $this->user_manager->get_single_answer ( $this->userid, $this->current_category, $position );
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
							$answer = $this->user_manager->get_single_answer ( $this->userid, $this->current_category, $i );
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
			// $this->currentCategoryPosition ++;
			
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
				$answer = $this->user_manager->get_single_answer ( $this->userid, $this->current_category, $i );
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
	 * Prints action buttons for questionaire page
	 */
	public function print_action_buttons() {
		echo '<div class="grid">
						<div class="col_100 questionaire_button_row">
							<button type="submit" name="direction" value="0" class="gf_button gf_button_pill gf_button_small">' . get_string ( 'previous' ) . '</button>
							<button type="submit" name="direction" value="1" class="gf_button gf_button_pill gf_button_small">' . get_string ( 'next' ) . '</button>
						</div>
						</div>';
	}
	
	/**
	 * Prints navigation bar
	 *
	 * @param string $activeCategory        	
	 */
	public function print_navbar($activeCategory = null) {
		$temp_categories = $this->store->get_categories ();
		$categories = array ();
		foreach ( $temp_categories as $category ) {
			if ($this->store->get_number ( $category ) > 0) {
				$categories [] = $category;
			}
		}
		echo '<div id="questionaire_navbar">';
		echo '<ul id="accordion">';
		$prev_complete = true;
		foreach ( $categories as $category ) {
			$url = new moodle_url ( 'questionnaire_view.php', array (
					'id' => $this->cmid,
					'category' => $category 
			) );
			$positionActiveCategory = $this->store->get_position ( $activeCategory );
			$positionCategory = $this->store->get_position ( $category );
			
			$beforeActive = ($positionCategory <= $positionActiveCategory);
			$class = (has_capability ( 'mod/groupformation:editsettings', $this->context ) || $beforeActive || $prev_complete) ? '' : 'no-active';
			echo '<li class="' . (($activeCategory == $category) ? 'current' : 'accord_li') . '">';
			echo '<span>' . ($positionCategory + 1) . '</span><a class="' . $class . '"  href="' . $url . '">' . get_string ( 'category_' . $category, 'groupformation' ) . '</a>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	
	/**
	 * Prints final page of questionaire
	 */
	public function print_final_page() {
		echo '<div class="col_100"><h4>' . get_string ( 'questionaire_no_more_questions', 'groupformation' ) . '</h></div>';
		echo '	<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		echo '		<input type="hidden" name="category" value="no"/>';
		
		$activity_id = optional_param ( 'id', false, PARAM_INT );
		if ($activity_id) {
			echo '	<input type="hidden" name="id" value="' . $activity_id . '"/>';
		} else {
			echo '	<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		}
		
		if (has_capability ( 'mod/groupformation:editsettings', $this->context ))
			echo '<div class="alert col_100 questionaire_hint">' . get_string ( 'questionaire_submit_disabled_teacher', 'groupformation' ) . '</div>';
		
		$url = new moodle_url ( '/mod/groupformation/view.php', array (
				'id' => $this->cmid,
				'do_show' => 'view' 
		) );
		
		echo '<div class="grid">';
		echo '	<div class="questionaire_button_text">' . get_string ( 'questionaire_press_beginning_submit', 'groupformation' ) . '</div>';
		echo '	<div class="col_100 questionaire_button_row">';
		echo '		<a href=' . $url->out() . '><span class="gf_button gf_button_pill gf_button_small">' . get_string ( 'questionaire_go_to_start', 'groupformation' ) . '</span></a>';
		// echo ' <button class="gf_button gf_button_pill gf_button_small" type="submit" name="action" value="1" ' . (($disabled || has_capability ( 'mod/groupformation:editsettings', $this->context )) ? 'disabled' : '') . '>' . get_string ( 'questionaire_submit', 'groupformation' ) . '</button>';
		echo '	</div>';
		echo '</div>';
		
		echo '</form>';
	}
	
	/**
	 * Prints questionaire page
	 */
	public function print_page() {
		if ($this->has_next ()) {
			$this->current_category = $this->categories [$this->currentCategoryPosition];
			$isTeacher = has_capability ( 'mod/groupformation:editsettings', $this->context );
			
			if ($isTeacher)
				echo '<div class="alert">' . get_string ( 'questionaire_preview', 'groupformation' ) . '</div>';
			
			if ($this->user_manager->is_completed ( $this->userid ) || ! $this->store->is_questionnaire_available ()) {
				echo '<div class="alert" id="commited_view">' . get_string ( 'questionaire_commited', 'groupformation' ) . '</div>';
			}
			
			$category = $this->current_category;
			
			$percent = $this->get_percent ( $category );
			
			$this->print_navbar ( $category );
			
			$this->print_progressbar ( $percent );
			
			// --- Mathevorkurs
			// if ($this->notAllAnswers) {
			// $this->notAllAnswers ();
			// }
			// ---
			
			$questions = $this->get_next_questions ();
			
			$this->print_questions ( $questions, $percent );
			
			// Log access to page
			groupformation_info ( $this->userid, $this->groupformationid, '<view_questionaire_category_' . $category . '>' );
		} else {
			
			$this->print_final_page ();
			
			// Log access to page
			groupformation_info ( $this->userid, $this->groupformationid, '<view_questionaire_final_page>' );
		}
	}
	
	/**
	 * Prints table with questions
	 *
	 * @param unknown $questions        	
	 * @param unknown $percent        	
	 */
	public function print_questions($questions, $percent) {
		$tableType = $questions [0] [0];
		$headerOptArray = $questions [0] [2];
		$category = $this->current_category;
		$header = new mod_groupformation_question_table_header ();
		$range = new mod_groupformation_range_question ();
		$radio = new mod_groupformation_radio_question ();
		$dropdown = new mod_groupformation_dropdown_question ();
		$topics = new mod_groupformation_topics_table ();
		
		echo '<form style="width:100%; float:left;" action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		if (! is_null ( $questions ) && count ( $questions ) != 0) {
			
			// hier schicke ich verdeckt die momentane Kategorie und groupformationID mit
			echo '<input type="hidden" name="category" value="' . $category . '"/>';
			
			echo '<input type="hidden" name="percent" value="' . $percent . '"/>';
			
			$activity_id = optional_param ( 'id', false, PARAM_INT );
			
			if ($activity_id) {
				echo '<input type="hidden" name="id" value="' . $activity_id . '"/>';
			} else {
				echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
			}
			
			// echo '<input type="hidden" name="userid" value="' . $this->userID . '"/>';
			
			echo ' <h4 class="view_on_mobile">' . get_string ( 'category_' . $category, 'groupformation' ) . '</h4>';
			
			// Print the Header of a table or unordered list
			$header->print_html ( $category, $tableType, $headerOptArray );
			
			$hasAnswer = count ( $questions [0] ) == 4;
			$hasTopicNumbers = count ( $questions [0] ) == 5;
			// var_dump($questions);
			// var_dump($category);
			
			// each question with inputs
			$i = 1;
			
			foreach ( $questions as $q ) {
				if ($q [0] == 'dropdown') {
					$dropdown->print_html ( $q, $category, $i, $hasAnswer );
				}
				
				if ($q [0] == 'radio') {
					$radio->print_html ( $q, $category, $i, $hasAnswer );
				}
				
				if ($q [0] == 'type_topics') {
					if ($hasTopicNumbers) {
						$topics->print_html ( $q, $category, $q [4] + 1, true );
					} else {
						$topics->print_html ( $q, $category, $i, $hasAnswer );
					}
				}
				
				if ($q [0] == 'type_knowledge') {
					$range->print_html ( $q, $category, $i, $hasAnswer );
				}
				
				if ($q [0] == 'type_points') {
					$range->print_html ( $q, $category, $i, $hasAnswer );
				}
				
				// TODO
				if ($q [0] == 'range') {
					$range->print_html ( $q, $category, $i, $hasAnswer );
				}
				$i ++;
			}
			
			// closing the table or unordered list
			if ($tableType == 'type_topics') {
				// close unordered list
				echo '</ul>';
				
				echo '<div id="invisible_topics_inputs">
                            </div>';
			} else {
				// close tablebody and close table
				echo ' </tbody>
		                  </table>';
			}
			
			// Reset the Question Number, so each HTML table starts with 0
			$i = 1;
		}
		
		$this->print_action_buttons ();
		
		echo '</form>';
	}
	
	/**
	 * Prints progress bar
	 *
	 * @param unknown $percent        	
	 */
	public function print_progressbar($percent) {
		echo '<div class="progress">';
		
		echo '	<div class="questionaire_progress-bar" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width:' . $percent . '%"></div>';
		
		echo '</div>';
	}
}