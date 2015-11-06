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
 * Prints a particular instance of groupformation
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( __FILE__ ) . '/view_elements/question_controller.php');
require_once (dirname ( __FILE__ ) . '/view_elements/radio_question.php');
require_once (dirname ( __FILE__ ) . '/view_elements/topics_table.php');
require_once (dirname ( __FILE__ ) . '/view_elements/range_question.php');
require_once (dirname ( __FILE__ ) . '/view_elements/dropdown_question.php');
require_once (dirname ( __FILE__ ) . '/view_elements/question_table_header.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}
class mod_groupformation_questionnaire {
	private $cmid;
	private $groupformationid;
	private $lang;
	private $question_controller;
	private $range;
	private $radio;
	private $topics;
	private $dropdown;
	private $header;
	private $qNumber = 1;
	private $gradesCount;
	private $category = "";
	private $context;
	private $userid;
	
	// --- Mathevorkurs
	// private $notAllAnswers = false;
	
	// ---
	
	/**
	 * Creates instance of questionaire
	 *
	 * @param unknown $cmid        	
	 * @param unknown $groupformationid        	
	 * @param unknown $lang        	
	 * @param unknown $userId        	
	 * @param unknown $category        	
	 * @param unknown $context        	
	 */
	public function __construct($cmid, $groupformationid, $lang, $userId, $category, $context) {
		$this->cmid = $cmid;
		$this->groupformationid = $groupformationid;
		$this->lang = $lang;
		$this->userid = $userId;
		$this->context = $context;
		
		$this->question_controller = new mod_groupformation_question_controller ( $groupformationid, $lang, $userId, $category );
		
		$this->header = new mod_groupformation_question_table_header ();
		$this->range = new mod_groupformation_range_question ();
		$this->radio = new mod_groupformation_radio_question ();
		$this->dropdown = new mod_groupformation_dropdown_question ();
		$this->topics = new mod_groupformation_topics_table ();
		$this->category = $category;
	}
	
	/**
	 * Go Back
	 */
	public function go_back() {
		$this->question_controller->go_back ();
	}
	
	// --- Mathevorkurs
	// public function goNotOn() {
	// $this->question_controller->goNotOn ();
	// $this->notAllAnswers = true;
	// }
	// ---
	
	/**
	 * Prints progress bar
	 *
	 * @param unknown $percent        	
	 */
	private function print_progressbar($percent) {
		echo '<div class="progress">';
		
		echo '	<div class="questionaire_progress-bar" role="progressbar" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100" style="width:' . $percent . '%"></div>';
		
		echo '</div>';
	}
	
	/**
	 * Prints navigation bar
	 *
	 * @param string $activeCategory        	
	 */
	private function print_navbar($activeCategory = null) {
		$data = new mod_groupformation_data ();
		$store = new mod_groupformation_storage_manager ( $this->groupformationid );
		$scenario = $store->get_scenario ();
		$temp_categories = $store->get_categories ();
		$categories = array ();
		foreach ( $temp_categories as $category ) {
			if ($store->get_number ( $category ) > 0) {
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
			$positionActiveCategory = $store->get_position ( $activeCategory );
			$positionCategory = $store->get_position ( $category );
			
			$beforeActive = ($positionCategory <= $positionActiveCategory);
			$class = (has_capability ( 'mod/groupformation:editsettings', $this->context ) || $beforeActive || $prev_complete) ? '' : 'no-active';
			echo '<li class="' . (($activeCategory == $category) ? 'current' : 'accord_li') . '">';
			echo '<span>' . ($positionCategory + 1) . '</span><a class="' . $class . '"  href="' . $url . '">' . get_string ( 'category_' . $category, 'groupformation' ) . '</a>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	
	// --- Mathevorkurs
	// private function notAllAnswers() {
	// echo '<div class="survey_warnings">
	// <p>Du hast nicht alle Fragen beantwortet</p>
	// </div>';
	
	// echo 'Du hast nicht alle Fragen beantwortet';
	// }
	// ---
	
	/**
	 * Prints table with questions
	 *
	 * @param unknown $questions        	
	 * @param unknown $percent        	
	 */
	private function print_questions($questions, $percent) {
		$tableType = $questions [0] [0];
		$headerOptArray = $questions [0] [2];
		
		echo '<form style="width:100%; float:left;" action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		if (! is_null ( $questions ) && count ( $questions ) != 0) {
			
			// hier schicke ich verdeckt die momentane Kategorie und groupformationID mit
			echo '<input type="hidden" name="category" value="' . $this->category . '"/>';
			
			echo '<input type="hidden" name="percent" value="' . $percent . '"/>';
			
			$activity_id = optional_param ( 'id', false, PARAM_INT );
			
			if ($activity_id) {
				echo '<input type="hidden" name="id" value="' . $activity_id . '"/>';
			} else {
				echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
			}
			
			// echo '<input type="hidden" name="userid" value="' . $this->userID . '"/>';
			
			echo ' <h4 class="view_on_mobile">' . get_string ( 'category_' . $this->category, 'groupformation' ) . '</h4>';
			
			// Print the Header of a table or unordered list
			$this->header->print_html ( $this->category, $tableType, $headerOptArray );
			
			$hasAnswer = count ( $questions [0] ) == 4;
			$hasTopicNumbers = count ( $questions [0] ) == 5;
			// var_dump($questions);
			// var_dump($this->category);
			
			// each question with inputs
			
			foreach ( $questions as $q ) {
				if ($q [0] == 'dropdown') {
					$this->dropdown->print_html ( $q, $this->category, $this->qNumber, $hasAnswer );
				}
				
				if ($q [0] == 'radio') {
					$this->radio->print_html ( $q, $this->category, $this->qNumber, $hasAnswer );
				}
				
				if ($q [0] == 'type_topics') {
					if ($hasTopicNumbers) {
						$this->topics->print_html ( $q, $this->category, $q [4] + 1, true );
					} else {
						$this->topics->print_html ( $q, $this->category, $this->qNumber, $hasAnswer );
					}
				}
				
				if ($q [0] == 'type_knowledge') {
					$this->range->print_html ( $q, $this->category, $this->qNumber, $hasAnswer );
				}
				
				if ($q [0] == 'type_points') {
					$this->range->print_html ( $q, $this->category, $this->qNumber, $hasAnswer );
				}
				
				// TODO
				if ($q [0] == 'range') {
					$this->range->print_html ( $q, $this->category, $this->qNumber, $hasAnswer );
				}
				$this->qNumber ++;
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
			$this->qNumber = 1;
		}
		
		$this->print_action_buttons ();
		
		echo '</form>';
	}
	
	/**
	 * Prints action buttons for questionaire page
	 */
	private function print_action_buttons() {
		echo '<div class="grid">
						<div class="col_100 questionaire_button_row">
							<button type="submit" name="direction" value="0" class="f_btn">' . get_string ( 'previous' ) . '</button>
							<button type="submit" name="direction" value="1" class="f_btn">' . get_string ( 'next' ) . '</button>
						</div>
						</div>';
	}
	
	/**
	 * Prints final page of questionaire
	 */
	private function print_final_page() {
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
		
		echo '<div class="grid">';
		echo '	<div class="questionaire_button_text">' . get_string ( 'questionaire_press_beginning_submit', 'groupformation' ) . '</div>';
		echo '	<div class="col_100 questionaire_button_row">';
		echo '		<button type="submit" name="action" value="0" >' . get_string ( 'questionaire_go_to_start', 'groupformation' ) . '</button>';
		// echo ' <button type="submit" name="action" value="1" ' . (($disabled || has_capability ( 'mod/groupformation:editsettings', $this->context )) ? 'disabled' : '') . '>' . get_string ( 'questionaire_submit', 'groupformation' ) . '</button>';
		echo '	</div>';
		echo '</div>';
		
		echo '</form>';
	}
	
	/**
	 * Prints questionaire page
	 */
	public function print_page() {
		if ($this->question_controller->has_next ()) {
			$isTeacher = has_capability ( 'mod/groupformation:editsettings', $this->context );
			
			if ($isTeacher)
				echo '<div class="alert">' . get_string ( 'questionaire_preview', 'groupformation' ) . '</div>';
			
			$store = new mod_groupformation_storage_manager ( $this->groupformationid );
			
			if ($this->question_controller->has_committed () || ! $store->is_questionnaire_available ()) {
				echo '<div class="alert" id="commited_view">' . get_string ( 'questionaire_commited', 'groupformation' ) . '</div>';
			}
			
			$this->category = $this->question_controller->get_current_category ();
			
			$percent = $this->question_controller->get_percent ( $this->category );
			
			$this->print_navbar ( $this->category );
			
			$this->print_progressbar ( $percent );
			
			// --- Mathevorkurs
			// if ($this->notAllAnswers) {
			// $this->notAllAnswers ();
			// }
			// ---
			
			$questions = $this->question_controller->get_next_questions ();
			
			$this->print_questions ( $questions, $percent );
			
			// Log access to page
			groupformation_info ( $this->userid, $this->groupformationid, '<view_questionaire_category_' . $this->category . '>' );
		} else {
			
			$this->print_final_page ();
			
			// Log access to page
			groupformation_info ( $this->userid, $this->groupformationid, '<view_questionaire_final_page>' );
		}
	}
}