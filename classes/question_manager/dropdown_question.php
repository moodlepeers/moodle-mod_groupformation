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
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dropdown_question {
	private $category;
	private $qnumber;
	private $question;
	private $optArray = array ();
	
	/**
	 * Print HTML of Dropdown Input
	 *
	 * @param unknown $q        	
	 * @param unknown $cat        	
	 * @param unknown $qnumb        	
	 * @param boolean $has_answer        	
	 */
	public function __printHTML($q, $category, $question_number, $has_answer) {
		$question = $q [1];
		$options = $q [2];
		
		$answer = - 1;
		$questionCounter = 1;
		
		if ($has_answer) {
			// $answer ist die position im optionArray von der Antwort
			$answer = $q [3];
		}
		
		if ($has_answer && $q [3] != - 1) {
			echo '<tr>';
			echo '<th scope="row">' . $question . '</th>';
		} else {
			echo '<tr class="noAnswer">';
			echo '<th scope="row">' . $question . '</th>';
		}
		
		echo '<td class="center">
				<select name="' . $category . $question_number . '" id="' . $category . $question_number . '">';
		echo '<option value="0"> - </option>';
		
		foreach ( $options as $option ) {
			if ($answer == $questionCounter) {
				echo '<option value="' . $questionCounter . '" selected="selected">' . $option . '</option>';
			} else {
				
				echo '<option value="' . $questionCounter . '">' . $option . '</option>';
			}
			$questionCounter ++;
		}
		
		echo '</select>
			</td>
		</tr>';
	}
}

?>