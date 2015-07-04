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
class TopicsTable{

	private $category;
	private $qnumber;
	private $question;	
	
	/**
	 * 
	 * print HTML for Topics Table
	 * 
	 * @param unknown $q
	 * @param unknown $cat
	 * @param unknown $qnumb
	 * @param unknown $hasAnswer
	 */
	public function __printHTML($q, $cat, $qnumb, $hasAnswer){
		$this->question = $q[1];
		$this->optArray = $q[2];
		$this->category = $cat;
		$this->qnumber = $qnumb;
		
		echo '<li id="'. $this->category .  $this->qnumber .'"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' . $this->question . '</li>';
		
		if($hasAnswer){
			$answer = $q[3];
		}
		
	}
}
?>


