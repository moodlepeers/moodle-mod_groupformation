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

	require_once(dirname(__FILE__).'/question_controller.php');
	require_once(dirname(__FILE__).'/RadioTable.php');
	require_once(dirname(__FILE__).'/TopicsTable.php');
	require_once(dirname(__FILE__).'/PreknowledgeTable.php');
	require_once(dirname(__FILE__).'/ValuationTable.php');

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	class mod_groupformation_questionaire {

		
		private $groupformationid;
		private $lang;
		private $question_manager;
		private $preknowledge;
		private $radio;
		private $topics;
		private $valuation;
		
		public function __construct($groupformationid, $lang, $userId){
			$this->groupformationid = $groupformationid;
			$this->lang = $lang;
			$this->question_manager = new mod_groupformation_question_controller($groupformationid, $lang, $userId);
			$this->preknowledge = new preKnowledge(array());
			$this->radio = new RadioTable(array());
			$this->valuation = new ValuationTable(array());
			$this->topics = new TopicsTable(array());
		}
		
		public function getQuestions(){
			
			$hasNext = $this->question_manager->hasNext();
			if($this->question_manager->questionsToAnswer()){
				while($hasNext){
					$category = $this->question_manager->getCurrentCategory();
					var_dump($category);
					$question = $this->question_manager->getNextQuestion();
						
					var_dump($question);
					
					//So müsste es mal aussehen
// 					foreach($question as $q){
// 						if($q[0] == 'dropdown'){
// 							$this->valuation->__printHTML($q);
// 						}
						
// 						if($q[0] == 'radio'){
// 							$this->radio->__printHTML($q);
// 						}
						
// 						if($q[0] == 'typThema'){
// 							$this->topics->__printHTML($q);
// 						}
						
// 						if($q[0] == 'typVorwissen'){
// 							$this->preknowledge->__printHTML($q);
// 						}
// 					}
					
					$optionsarray = $question[0][2];
					$header = $question[0][1];
						
					// TODO @EG: Das sollte in einer QuestionTableView-Klasse gekapselt sein, so dass wir hier nur einen Methodenaufruf haben! (JK)
					echo '<form action="">';
					echo '<div class="grid">
                <div class="col_100"> ';
					
					echo ' <h4 class="view_on_mobile">' . $category . '</h4>' ;
					
					
					echo '<table class="responsive-table">' .
							//TODO @Nora || EG : 	je nach Anzahl($optNumb) werden die entsprechenden widths in % angefügt
					//						in diesem Fall: 2-5 collumn sind jeweils 36%/4, 1 collumn hätte keine width sondern nur die classe="firstCol"
					'<colgroup>
						<col class="firstCol">
						<col width="36%">
					<colgroup>';
					echo '<thead>
                      <tr>
                        <th scope="col">'. $category . '</th>
                        <th scope="col"></th>
                      </tr>
                    </thead>
                    <tbody>';
						
					$questionCounter = 0;
					foreach ($question as $datarow){
						// 				$optionCounter = 0;
						echo '<tr>
				<th scope="row">' . $datarow[1] . '</th>
				<td class="center">
						<select name="' .$category . $questionCounter . '" id="' .$category . $questionCounter . '">';
						foreach ($optionsarray as $option){
							echo '<option value="' . $option . '">' . $option . '</option>';
						}
						echo '</select>
					</td>
				</tr>';
						$questionCounter ++;
					}
					echo ' </tbody>
                  </table>
                </div>';
						
					
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
		}
	}