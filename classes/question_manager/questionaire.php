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
 * @author  Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// TODO @EG hier ist Jquery eingebunden worden ohne Fehler!
// addjQuery($PAGE);


    
	require_once(dirname(__FILE__).'/question_controller.php');
	require_once(dirname(__FILE__).'/RadioInput.php');
	require_once(dirname(__FILE__).'/TopicsTable.php');
	require_once(dirname(__FILE__).'/RangeInput.php');
	require_once(dirname(__FILE__).'/DropdownInput.php');
	require_once(dirname(__FILE__).'/HeaderOfInputs.php');

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}
	
	class mod_groupformation_questionaire {

		
			
		
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
			
		
		
		public function __construct($groupformationid, $lang, $userId, $category){
			
			
			$this->groupformationid = $groupformationid;
			$this->lang = $lang;
			$this->question_manager = new mod_groupformation_question_controller($groupformationid, $lang, $userId, $category);
			$this->header = new HeaderOfInput();
			$this->range = new RangeInput();
			$this->radio = new RadioInput();
			$this->dropdown = new DropdownInput();
			$this->topics = new TopicsTable();
		}
		
		public function goback(){
			$this->question_manager->goBack();
		}
		
		public function getQuestions(){
			
			// TODO @Nora @Rene
			// Es muss eine Methode eingebaut werden um den Fragebogen in mehrere Tabs zu splitten.
			
			$hasNext = $this->question_manager->hasNext();
			if($this->question_manager->questionsToAnswer() && $hasNext){
				//while($hasNext){
					$percent = $this->question_manager->getPercent();
					var_dump((float) $percent);
					$this->category = $this->question_manager->getCurrentCategory();
// 					var_dump($this->category);
					$question = $this->question_manager->getNextQuestion();

					// print current $question Array
// 					var_dump($question);
					
					$tableType = $question[0][0];
					$headerOptArray = $question[0][2];
					
					//echo '<form action="questionaire.php" method="post">';
					echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post" autocomplete="off">';
					
					//hier schicke ich verdeckt die momentane Kategorie und groupformationID mit
					echo '<input type="hidden" name="category" value="' . $this->category . '"/>';
					
					echo '<input type="hidden" name="percent" value="' . $percent . '"/>';
					
					$activity_id = optional_param('id', false, PARAM_INT);
					if ($activity_id) {
						echo '<input type="hidden" name="id" value="' . $activity_id . '"/>';
					}else{
						echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
					}
					
				//	echo '<input type="hidden" name="userid" value="' . $this->userID . '"/>';
					
					echo ' <h4 class="view_on_mobile">' . get_string('category_'.$this->category,'groupformation'). '</h4>' ;

					// Print the Header of a table or unordered list
					$this->header->__printHTML($this->category, $tableType, $headerOptArray);

					$hasAnswer = count($question[0]) == 4;
					
					// each question with inputs
					foreach($question as $q){
						if($q[0] == 'dropdown'){
							$this->dropdown->__printHTML($q, $this->category, $this->qNumber, $hasAnswer);
						}
						
						if($q[0] == 'radio'){
							$this->radio->__printHTML($q, $this->category, $this->qNumber, $hasAnswer);
						}
						
						if($q[0] == 'typThema'){
							$this->topics->__printHTML($q, $this->category, $this->qNumber, $hasAnswer);
						}
						
						if($q[0] == 'typVorwissen'){
							$this->range->__printHTML($q, $this->category, $this->qNumber, $hasAnswer);
						}
						$this->qNumber++;
					}

					// closing the table or unordered list
					if($tableType == 'typThema'){
						//close unordered list
						echo '</ul>';
					}else{
						// close tablebody and close table
						echo ' </tbody>
		                  </table>';
					}

					
					// Reset the Question Number, so each HTML table starts with 0
					$this->qNumber = 1;
					
// 					$hasAnswer = $this->question_manager->hasAnswers();
// 					var_dump($hasAnswer);
// 					if($hasAnswer){
// 						var_dump($this->question_manager->getAnswers());
// 					}
					//$hasNext = $this->question_manager->hasNext();
					//$answers = array('0');
					//$this->question_manager->saveAnswers($answers);
					
					echo '
						<div class="grid">
						<div class="col_100 questionaire_button_row">
							<button type="submit" name="direction" value="0" class="f_btn">'.get_string('previous').'</button>
							<button type="submit" name="direction" value="1" class="f_btn">'.get_string('next').'</button>
						</div>
						</div>
							
						</form>';
				//}
			}else{
				echo '<div class="col_100"><h4>'.get_string('questionaire_no_more_questions','groupformation').'</h></div>';
				echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post" autocomplete="off">';
					
				//hier schicke ich verdeckt die momentane Kategorie und groupformationID mit
				echo '<input type="hidden" name="category" value="no"/>';
					
				$activity_id = optional_param('id', false, PARAM_INT);
				if ($activity_id) {
					echo '<input type="hidden" name="id" value="' . $activity_id . '"/>';
				}else{
					echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
				}
				
				echo '
						<div class="grid">
						<div class="questionaire_button_text">'.get_string('questionaire_press_beginning_submit','groupformation').'</div>
						<div class="col_100 questionaire_button_row">
							<button type="submit" name="action" value="0">'.get_string('questionaire_go_to_start','groupformation').'</button>
							<button type="submit" name="action" value="1">'.get_string('submit').'</button>
						</div>
						</div>
							
						</form>';
			}
			

			// TODO @Nora @Rene: Die Buttons des Formulars einfach per echo ausgeben oder müssen an dieser Stelle Moodle Elemente benutzt werden?
			// Die Buttons sowie die Schließung des Formulars muss am Ende jedes Tabs erfolgen.
// 			echo '
// 			<div class="grid">
// 			<div class="col_100">
// 			<button type="reset" class="f_btn">Cancel</button>
// 			<button type="button" class="f_btn">Save</button>
// 			<button type="submit" class="f_btn">Next</button>
// 			</div>
// 			</div> <!-- /grid -->';
// 			// Ende des Formulars
// 			echo '</form>';
		}
	}