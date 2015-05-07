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
		private $qNumber = 0;
		private $gradesCount;
		private $category;
			
		
		
		public function __construct($groupformationid, $lang, $userId){
			
			
			$this->groupformationid = $groupformationid;
			$this->lang = $lang;
			$this->question_manager = new mod_groupformation_question_controller($groupformationid, $lang, $userId);
			$this->header = new HeaderOfInput();
			$this->range = new RangeInput(array(), $category, $qNumber);
			$this->radio = new RadioInput(array(), $category, $qNumber);
			$this->dropdown = new DropdownInput(array(), $category, $qNumber);
			$this->topics = new TopicsTable(array(), $category, $qNumber);
		}
		
		
		
		public function getQuestions(){
			
			// TODO @Nora @Rene
			// Es muss eine Methode eingebaut werden um den Fragebogen in mehrere Tabs zu splitten.
			
			$hasNext = $this->question_manager->hasNext();
			if($this->question_manager->questionsToAnswer()){
				while($hasNext){
					$this->category = $this->question_manager->getCurrentCategory();
					var_dump($this->category);
					$question = $this->question_manager->getNextQuestion();

					// print current $question Array
// 					var_dump($question);
					
					$tableType = $question[0][0];
					$headerOptArray = $question[0][2];
					
					echo '<form action="">';
					
					
					
					echo ' <h4 class="view_on_mobile">' . $this->category . '</h4>' ;

					// Print the Header of a table or unordered list
					$this->header->__printHTML($this->category, $tableType, $headerOptArray);

					
					// each question with inputs
					foreach($question as $q){
						if($q[0] == 'dropdown'){
							$this->dropdown->__printHTML($q, $this->category, $this->qNumber);
						}
						
						if($q[0] == 'radio'){
							$this->radio->__printHTML($q, $this->category, $this->qNumber);
						}
						
						if($q[0] == 'typThema'){
							$this->topics->__printHTML($q, $this->category, $this->qNumber);
						}
						
						if($q[0] == 'typVorwissen'){
							$this->range->__printHTML($q, $this->category, $this->qNumber);
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
					$this->qNumber = 0;
					
					$hasAnswer = $this->question_manager->hasAnswers();
					var_dump($hasAnswer);
					if($hasAnswer){
						var_dump($this->question_manager->getAnswers());
					}
					$hasNext = $this->question_manager->hasNext();
					//$answers = array('0');
					//$this->question_manager->saveAnswers($answers);
				}
			}
			

			// TODO @Nora @Rene: Die Buttons des Formulars einfach per echo ausgeben oder müssen an dieser Stelle Moodle Elemente benutzt werden?
			// Die Buttons sowie die Schließung des Formulars muss am Ende jedes Tabs erfolgen.
			echo '
			<div class="grid">
			<div class="col_100">
			<button type="reset" class="f_btn">Cancel</button>
			<button type="button" class="f_btn">Save</button>
			<button type="submit" class="f_btn">Next</button>
			</div>
			</div> <!-- /grid -->';
			// Ende des Formulars
			echo '</form>';
		}
	}