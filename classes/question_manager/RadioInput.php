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

class RadioInput {

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
		
		echo '<tr>';
		//echo '<th scope="row">' . $this->question . '</th>';
		echo '<td> <label for="' . $this->category . $this->qnumber . '">' .
				$this->question . '</label> </td>';
		
		$radioCounter = 1;
		$answer = -1;
		if($hasAnswer){
			//$answer ist die position im optionArray von der Antwort
			$answer = $q[3];
		}
		foreach ($this->optArray as $option){
			if($answer == $radioCounter){
				echo '<td data-title="' . $option .
				'" class="radioleft select-area"><input type="radio" name="' .
				$this->category . $this->qnumber .
				'" value="' . $radioCounter . '" checked="checked"/></td>';
			}else{
			echo '<td data-title="' . $option .
				'" class="radioleft select-area"><input type="radio" name="' .
				$this->category . $this->qnumber .
				'" value="' . $radioCounter . '"/></td>';
			}
			$radioCounter++;
		}
		echo '</tr>';
		
	}
	
	
}	
	
?>