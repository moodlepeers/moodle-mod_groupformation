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

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

//require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');

class mod_groupformation_infoText {

	private $groupformationid;
	
	public function __construct($groupformationid){
			
		$this->groupformationid = $groupformationid;
	}
	
	public function statusA(){
		echo '<div class="questionaire_status">'.get_string('questionaire_not_started','groupformation').'</div>';
		echo '<div class="questionaire_button_text">'.get_string('questionaire_press_to_begin','groupformation').'</div>';
		echo '<div class="questionaire_button_row">';
		echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post" autocomplete="off">';
			
		//hier schicke ich verdeckt groupformationID und die Information, ob der Fragebogen angezeigt werden soll
		// 1 => ja
		echo '<input type="hidden" name="questions" value="1"/>';
			
		echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		echo '
						<div class="grid">
						<div class="col_100">
							<input type="submit" value="'.get_string("next").'" />
						</div>
						</div>
							
						</form>';
		echo  '</div>';
	}
	
	public function statusB(){
		echo '<div><h5>'.get_string('questionaire_answer_stats','groupformation').'</h></div>';
		echo '<div>'.'TODO show some stats about the categories and the missing answers'.'</div>';
		echo '<div class="questionaire_status">'.get_string('questionaire_not_submitted','groupformation').'</div>';
		echo '<div class="questionaire_button_text">'.get_string('questionaire_press_continue_submit','groupformation').'</div>';
		echo '<div class="questionaire_button_row">';
		echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post" autocomplete="off">';
			
		//hier schicke ich verdeckt groupformationID und die Information, ob der Fragebogen angezeigt werden soll
		// 1 => ja
		echo '<input type="hidden" name="questions" value="1"/>';
			
		echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		echo '
						<div class="grid">
						<div class="col_100">
							<button type="submit" name="begin" value="1">'.get_string('edit').'</button>
							<button type="submit" name="begin" value="0">'.get_string('submit').'</button>
						</div>
						</div>
							
						</form>';
		echo  '</div>';
	}
	
	public function statusC(){
		echo '<div class="questionaire_status">'.get_string('questionaire_submitted','groupformation').'</div>';
	}
	
	public function Dozent(){
		
		echo '<div class="questionaire_button_text">'.get_string('questionaire_press_preview','groupformation').'</div>';
		echo '<div class="questionaire_button_row">';
		echo '<form action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '" method="post" autocomplete="off">';
			
		//hier schicke ich verdeckt groupformationID und die Information, ob der Fragebogen angezeigt werden soll
		// 1 => ja
		echo '<input type="hidden" name="questions" value="1"/>';
			
		echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		echo '
						<div class="grid">
						<div class="col_100">
							<input type="submit" value="'.get_string('preview').'" />
						</div>
						</div>
							
						</form>';
		echo  '</div>';
	}
}