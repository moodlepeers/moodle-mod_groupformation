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
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

// require_once($CFG->dirroot.'/mod/groupformation/classes/moodle_interface/storage_manager.php');
class mod_groupformation_infoText {
	private $groupformationid;
	private $userid;
	private $truegroupformationid;
	private $categorysets = array (
			1 => array (
					'topic',
					'knowledge',
					'general',
					'grade',
					'team',
					'character',
					'motivation' 
			),
			2 => array (
					'topic',
					'knowledge',
					'general',
					'grade',
					'team',
					'character',
					'learning' 
			),
			3 => array (
					'topic',
					'general' 
			) 
	);
	public function __construct($groupformationid, $userid, $truegroupformationid) {
		$this->groupformationid = $groupformationid;
		$this->userid = $userid;
		$this->truegroupformationid = $truegroupformationid;
	}
	public function statusA() {
		echo '<div class="questionaire_status">' . get_string ( 'questionaire_not_started', 'groupformation' ) . '</div>';
		echo '<div class="questionaire_button_text">' . get_string ( 'questionaire_press_to_begin', 'groupformation' ) . '</div>';
		echo '<div class="questionaire_button_row">';
		echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		// hier schicke ich verdeckt groupformationID und die Information, ob der Fragebogen angezeigt werden soll
		// 1 => ja
		echo '<input type="hidden" name="questions" value="1"/>';
		
		echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		echo '
						<div class="grid">
						<div class="col_100">
							<input type="submit" value="' . get_string ( "next" ) . '" />
						</div>
						</div>
							
						</form>';
		echo '</div>';
	}
	public function statusB() {
		$this->printStats ();
		echo '<div class="col_100">' . get_string ( 'questionaire_not_submitted', 'groupformation' ) . '</div>';
		echo '<div class="col_100">' . get_string ( 'questionaire_press_continue_submit', 'groupformation' ) . '</div>';
		echo '<div class="col_100">';
		echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		// hier schicke ich verdeckt groupformationID und die Information, ob der Fragebogen angezeigt werden soll
		// 1 => ja
		echo '<input type="hidden" name="questions" value="1"/>';
		
		echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		echo '
						<div class="grid">
						<div class="col_100">
							<button type="submit" name="begin" value="1">' . get_string ( 'edit' ) . '</button>
							<button type="submit" name="begin" value="0">' . get_string ( 'submit' ) . '</button>
						</div>
						</div>
							
						</form>';
		echo '</div>';
	}
	
	public function statusC() {
		echo '<div class="questionaire_status">' . get_string ( 'questionaire_submitted', 'groupformation' ) . '</div>';
	}
	public function Dozent() {
		echo '<div class="questionaire_button_text">' . get_string ( 'questionaire_press_preview', 'groupformation' ) . '</div>';
		echo '<div class="questionaire_button_row">';
		echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		// hier schicke ich verdeckt groupformationID und die Information, ob der Fragebogen angezeigt werden soll
		// 1 => ja
	//	echo '<input type="hidden" name="questions" value="1"/>';
		
		echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		echo '
						<div class="grid">
						<div class="col_100">
							<button type="submit" name="dozent" value="1">' . get_string ( 'preview' ) . '</button>
							<button type="submit" name="dozent" value="2">Zur Analyse</button>
							<button type="submit" name="dozent" value="3">Gruppenformation starten</button>
						</div>
						</div>
							
						</form>';
		echo '</div>';
	}
	
	/**
	 * computes stats about answered and misssing questions
	 * 
	 * @return multitype:multitype:number stats
	 */
	private function getStats() {
		global $DB;
		$scenario = $DB->get_record('groupformation', array('id'=>$this->truegroupformationid))->szenario;
		$categories = array ();
		foreach ( $DB->get_records ( 'groupformation_q_version' ) as $record ) {
			$categories [$record->category] = ( int ) $record->numberofquestion;
		}
		$stats = array ();
		foreach ( $categories as $key => $value ) {
			if (in_array($key,$this->categorysets[$scenario])){
				$count = $DB->count_records ( 'groupformation_answer', array (
						'groupformation' => $this->truegroupformationid,
						'userid' => $this->userid,
						'category' => $key
				) );
				$stats [$key] = array (
						'questions' => $value,
						'answered' => $count,
						'missing' => $value - $count
				);
			}
		}
		return $stats;
	}
	
	/**
	 * echoes stats about answered and misssing questions
	 */
	private function printStats() {
		echo '<div class="questionaire_stats col_90">';
		echo '<table class="responsive-table">';
		echo '<thead><tr><th scope="col">';
		echo '<div>';
		echo get_string ( 'questionaire_answer_stats', 'groupformation' );
		echo '</div>';
		echo '</th></tr>
				</thead>';
		echo '<tbody>';
		$stats = $this->getStats();
		
		foreach ($stats as $key => $values){
			$a = new stdClass();
			$a->category = get_string('category_'.$key,'groupformation');
			$a->questions = $values['questions'];
			$a->answered = $values['answered'];
			echo '<tr><th scope="row" class="questionaire_stats_row"><span>';
			if ($values['missing']==0){
				echo get_string('stats_all','groupformation',$a).' <span class="questionaire_all">&#10004;</span>';
			} elseif ($values['answered']==0){
				echo get_string('stats_none','groupformation',$a).' <span class="questionaire_none">&#10008;</span>';
			} else {
				echo get_string('stats_partly','groupformation',$a);
			}
			echo '</span></th></tr>';
		}
		echo '</tbody>';
		echo '</table>';
				
		echo '</div>';
		
	}
}