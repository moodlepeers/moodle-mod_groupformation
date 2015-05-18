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
 * @author  
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class DropdownInput {
	
	
	private $category;
	private $qnumber;
	private $question;
	private $optArray = array();
	
	
	public function __construct(){
		
	}
	
	
	
	public function __printHTML($q, $cat, $qnumb, $hasAnswer){
		$this->question = $q[1];
		$this->optArray = $q[2];
		$this->category = $cat;
		$this->qnumber = $qnumb;
		
		$answer = -1;
		$questionCounter = 1;
		if($hasAnswer){
			//$answer ist die position im optionArray von der Antwort
			$answer = $q[3];
		}
		
		echo '<tr>';
		//echo '<th scope="row">' . $this->question . '</th>';
		echo '<td> <label for="' . $this->category . $this->qnumber . '">' .
				$this->question . '</label> </td>';
		
		
		echo '<td class="center">
				<select name="'. $this->category . $this->qnumber  .'" id="' . $this->category . $this->qnumber  .'">';
		
		foreach ($this->optArray as $option){
			if($answer == $questionCounter){
				echo '<option value="'. $questionCounter .'" selected="selected">'. $option .'</option>';
			}else{
				echo '<option value="'. $questionCounter .'">'. $option .'</option>';
			}
			$questionCounter++;
		}
		
		echo '</select>
			</td>
		</tr>';
		
		
	}
}

?>



<!--					 <tr> -->
<!--                         <th scope="row">Welche Note m&ouml;chten Sie erreichen?</th> -->

<!--                         <td class="center"> -->
<!--                             <select name="gradeA" id="gradeA"> -->
<!--                                 <option value="1.0">1,0</option> -->
<!--                                 <option value="1.3">1,3</option> -->
<!--                                 <option value="1.6">1,7</option> -->
<!--                                 <option value="2.0">2,0</option> -->
<!--                                 <option value="2.3">2,3</option> -->
<!--                                 <option value="2.6">2,7</option> -->
<!--                                 <option value="3.0">3,0</option> -->
<!--                                 <option value="3.3">3,3</option> -->
<!--                                 <option value="3.6">3,7</option> -->
<!--                                 <option value="4.0">4,0</option> -->
<!--                             </select> -->
<!--                             </td> -->
<!--                       </tr> -->