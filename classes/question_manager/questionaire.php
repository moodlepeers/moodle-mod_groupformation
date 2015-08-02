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

require_once (dirname ( __FILE__ ) . '/question_controller.php');
require_once (dirname ( __FILE__ ) . '/RadioInput.php');
require_once (dirname ( __FILE__ ) . '/TopicsTable.php');
require_once (dirname ( __FILE__ ) . '/RangeInput.php');
require_once (dirname ( __FILE__ ) . '/DropdownInput.php');
require_once (dirname ( __FILE__ ) . '/HeaderOfInputs.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}
class mod_groupformation_questionaire {
	private $cmid;
	private $groupformationid;
	private $lang;
	private $question_manager;
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
	private $notAllAnswers = false;
	
	// ---
	public function __construct($cmid, $groupformationid, $lang, $userId, $category, $context) {
		$this->cmid = $cmid;
		$this->groupformationid = $groupformationid;
		$this->lang = $lang;
		$this->userid = $userId;
		$this->context = $context;
		$this->question_manager = new mod_groupformation_question_controller ( $groupformationid, $lang, $userId, $category );
		$this->header = new HeaderOfInput ();
		$this->range = new RangeInput ();
		$this->radio = new RadioInput ();
		$this->dropdown = new DropdownInput ();
		$this->topics = new TopicsTable ();
		$this->category = $category;
	}
	public function goBack() {
		$this->question_manager->goBack ();
	}
	
	// --- Mathevorkurs
	public function goNotOn() {
		$this->question_manager->goNotOn ();
		$this->notAllAnswers = true;
	}
	// ---
	private function printProgressbar($percent) {
		$percentage = $percent;
		echo '<div class="progress">
  							<div class="questionaire_progress-bar" role="progressbar" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100" style="width:' . $percentage . '%">
    							</div>
						  </div>';
	}
	/*private function printOverviewbar($activeCategory = null) {
		$data = new mod_groupformation_data ();
		$store = new mod_groupformation_storage_manager ( $this->groupformationid );
		$scenario = $store->getScenario ();
		$temp_categories = $store->getCategories();
		$categories = array ();
		foreach ( $temp_categories as $category ) {
			if ($store->getNumber ( $category ) > 0) {
				$categories [] = $category;
			}
		}
		echo '<div class="questionaire_navbar">';
		echo '<ul class="questionaire_navbar">';
		$width = 100.0 / count ( $categories );
		$stats = $store->getStats($this->userid);
		$prev_complete = true;
		foreach ( $categories as $category ) {
			$url = new moodle_url ( 'questionaire_view.php', array (
					'id' => $this->cmid,
					'category' => $category 
			) );
			$positionActiveCategory = $store->getPosition($activeCategory);
			$positionCategory = $store->getPosition($category);
			
			$beforeActive = ($positionCategory<=$positionActiveCategory);
			$class = (has_capability('mod/groupformation:editsettings', $this->context) || $beforeActive || $prev_complete)?'':'no-active';
			echo '<li class="questionaire_navbar '.$class.'" style="width:' . $width . '%;">';
			echo '<a class="questionaire_navbar_link" ' . (($activeCategory == $category) ? 'style="background-color: #2d2d2d; color: #FFFFFF"' : '') . ' href="' . $url . '">' . get_string ( 'category_' . $category, 'groupformation' ) . '</a>';
			echo '</li>';
			$prev_complete = $stats[$category]['missing'] == 0;
			// <li><a href="a.html" class="ui-btn-active">One</a></li>
			// <li><a href="b.html">Two</a></li>
		}
		echo '</ul>';
		echo '</div><!-- /navbar -->';
	}*/

    private function printOverviewbar($activeCategory = null) {
        $data = new mod_groupformation_data ();
        $store = new mod_groupformation_storage_manager ( $this->groupformationid );
        $scenario = $store->getScenario ();
        $temp_categories = $store->getCategories();
        $categories = array ();
        foreach ( $temp_categories as $category ) {
            if ($store->getNumber ( $category ) > 0) {
                $categories [] = $category;
            }
        }
        echo '<div id="questionaire_navbar">';
        echo '<ul id="accordion">';
        $stats = $store->getStats($this->userid);
        $prev_complete = true;
        foreach ( $categories as $category ) {
            $url = new moodle_url ( 'questionaire_view.php', array (
                'id' => $this->cmid,
                'category' => $category
            ) );
            $positionActiveCategory = $store->getPosition($activeCategory);
            $positionCategory = $store->getPosition($category);

            $beforeActive = ($positionCategory<=$positionActiveCategory);
            $class = (has_capability('mod/groupformation:editsettings', $this->context) || $beforeActive || $prev_complete)?'':'no-active';
            echo '<li class="'. (($activeCategory == $category) ? 'current' : 'accord_li') . '">';
            echo '<span>'. ( $positionCategory + 1) .'</span><a class="' . $class .'"  href="' . $url . '">'  . get_string ( 'category_' . $category, 'groupformation' ) .'</a>';
            echo '</li>';
            $prev_complete = $stats[$category]['missing'] == 0;
            // <li><a href="a.html" class="ui-btn-active">One</a></li>
            // <li><a href="b.html">Two</a></li>
        }
        echo '</ul>';
        echo '</div>';
    }
	
	// --- Mathevorkurs
	private function notAllAnswers() {
        echo '<div class="col_100 survey_warnings">
                             <p>Du hast nicht alle Fragen beantwortet</p>
                    </div>';

		//echo 'Du hast nicht alle Fragen beantwortet';
	}
	// ---
	
	private function printQuestions($questions, $percent) {
		$tableType = $questions [0] [0];
		$headerOptArray = $questions [0] [2];
		
		echo '<div class="col_100">';
		// echo '<form action="questionaire.php" method="post">';
		echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
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
		$this->header->__printHTML ( $this->category, $tableType, $headerOptArray );
		
		$hasAnswer = count ( $questions [0] ) == 4;

        // var_dump($questions);
        // var_dump($this->category);

		// each question with inputs
		foreach ( $questions as $q ) {
			if ($q [0] == 'dropdown') {
				$this->dropdown->__printHTML ( $q, $this->category, $this->qNumber, $hasAnswer );
			}
			
			if ($q [0] == 'radio') {
				$this->radio->__printHTML ( $q, $this->category, $this->qNumber, $hasAnswer );
			}
			
			if ($q [0] == 'typThema') {
				$this->topics->__printHTML ( $q, $this->category, $this->qNumber, $hasAnswer );
			}
			
			if ($q [0] == 'typVorwissen') {
				$this->range->__printHTML ( $q, $this->category, $this->qNumber, $hasAnswer );
			}
			$this->qNumber ++;
		}
		
		// closing the table or unordered list
		if ($tableType == 'typThema') {
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
		
		// $hasAnswer = $this->question_manager->hasAnswers();
		// var_dump($hasAnswer);
		// if($hasAnswer){
		// var_dump($this->question_manager->getAnswers());
		// }
		// $hasNext = $this->question_manager->hasNext();
		// $answers = array('0');
		// $this->question_manager->saveAnswers($answers);
		
		echo '
						<div class="grid">
						<div class="col_100 questionaire_button_row">
							<button type="submit" name="direction" value="0" class="f_btn">' . get_string ( 'previous' ) . '</button>
							<button type="submit" name="direction" value="1" class="f_btn">' . get_string ( 'next' ) . '</button>
						</div>
						</div>
				
						</form>
						</div>';
	}
	
	private function printFinalPage() {
		echo '<div class="col_100"><h4>' . get_string ( 'questionaire_no_more_questions', 'groupformation' ) . '</h></div>';
		echo '	<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		// hier schicke ich verdeckt die momentane Kategorie und groupformationID mit
		echo '		<input type="hidden" name="category" value="no"/>';
		
		$activity_id = optional_param ( 'id', false, PARAM_INT );
		if ($activity_id) {
			echo '	<input type="hidden" name="id" value="' . $activity_id . '"/>';
		} else {
			echo '	<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		}
		
		// $store = new mod_groupformation_storage_manager($this->groupformationid);
		
		// $hasAnsweredEverything = $store->hasAnsweredEverything($this->userId);
		
		$hasAnsweredEverything = $this->question_manager->hasAllAnswered ();
		
		$disabled = ! $hasAnsweredEverything;
		if (has_capability('mod/groupformation:editsettings', $this->context))
			echo '<div class="col_100 questionaire_hint">'.get_string('questionaire_submit_disabled_teacher','groupformation').'</div>';
		
		echo '		<div class="grid">
						<div class="questionaire_button_text">' . get_string ( 'questionaire_press_beginning_submit', 'groupformation' ) . '</div>
						<div class="col_100 questionaire_button_row">';
		echo '				<button type="submit" name="action" value="0" >' . get_string ( 'questionaire_go_to_start', 'groupformation' ) . '</button>
							<button type="submit" name="action" value="1" ' . (($disabled || has_capability('mod/groupformation:editsettings', $this->context)) ? 'disabled' : '') . '>' . get_string ( 'questionaire_submit', 'groupformation' ) . '</button>
						';
		echo '			</div>
					</div>
				</form>';
	}
	
	public function printQuestionairePage() {
		if ($this->question_manager->questionsToAnswer () && $this->question_manager->hasNext ()) {
			if (has_capability('mod/groupformation:editsettings', $this->context))
				echo '<div class="col_100 questionaire_hint">'.get_string('questionaire_preview','groupformation').'</div>';
			
			$this->category = $this->question_manager->getCurrentCategory ();
			
			$percent = $this->question_manager->getPercent ( $this->category );
			
			$this->printOverviewbar ( $this->category );
			
			$this->printProgressbar ( $percent );
			
			// TODO @Nora Das kann man auch auslagern mit einer Konstantem im Define-File
			// --- MAthevorkurs
			if ($this->notAllAnswers) {
				$this->notAllAnswers ();
			}
			// ---
			
			$questions = $this->question_manager->getNextQuestion ();
			
			$this->printQuestions ( $questions, $percent );
			
			// Log access to page
			groupformation_info($this->userid,$this->groupformationid,'<view_questionaire_category_'.$this->category.'>');
			
		} else {
			
			$this->printFinalPage ();
			
			// Log access to page
			groupformation_info($this->userid,$this->groupformationid,'<view_questionaire_final_page>');
				
		}
	}
}