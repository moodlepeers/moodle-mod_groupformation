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

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

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
	private $store;
	public function __construct($groupformationid, $userid, $truegroupformationid) {
		// its not the groupformation id -> its also unused so far
		$this->groupformationid = $groupformationid;
		$this->userid = $userid;
		$this->truegroupformationid = $truegroupformationid;
		$this->store = new mod_groupformation_storage_manager ( $truegroupformationid );
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
		global $USER;
		$this->printStats ();
		echo '<div class="col_100">' . get_string ( 'questionaire_not_submitted', 'groupformation' ) . '</div>';
		echo '<div class="col_100">' . get_string ( 'questionaire_press_continue_submit', 'groupformation' ) . '</div>';
		echo '<div class="col_100">';
		echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
		
		// hier schicke ich verdeckt groupformationID und die Information, ob der Fragebogen angezeigt werden soll
		// 1 => ja
		echo '<input type="hidden" name="questions" value="1"/>';
		
		echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
		
		$hasAnsweredEverything = $this->store->hasAnsweredEverything($USER->id);
		
		$disabled = !$hasAnsweredEverything;
		
		echo '
						<div class="grid">
						<div class="col_100">
							<button type="submit" name="begin" value="1">' . get_string ( 'edit' ) . '</button>
							<button type="submit" name="begin" value="0" ' . (($disabled) ? 'disabled' : '') . '>' . get_string ( 'questionaire_submit', 'groupformation' ) . '</button>
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
		// echo '<input type="hidden" name="questions" value="1"/>';
		
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
		$scenario = $this->store->getScenario ();
		
		$data = new mod_groupformation_data ();
		
		// $scenario = $DB->get_record('groupformation', array('id'=>$this->truegroupformationid))->szenario;
		
		$category_set = $data->getCategorySet ( $scenario );
		
		$categories = array ();
		
		foreach ( $category_set as $category ) {
			$categories [$category] = $this->store->getNumber ( $category );
		}
		
		$stats = array ();
		foreach ( $categories as $category => $value ) {
			$count = $this->store->answerNumberForUser ( $this->userid, $category );
			$stats [$category] = array (
					'questions' => $value,
					'answered' => $count,
					'missing' => $value - $count 
			);
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
		$stats = $this->getStats ();
		
		foreach ( $stats as $key => $values ) {
			$a = new stdClass ();
			$a->category = get_string ( 'category_' . $key, 'groupformation' );
			$a->questions = $values ['questions'];
			$a->answered = $values ['answered'];
			if ($values ['questions'] > 0) {
				echo '<tr><th scope="row" class="questionaire_stats_row"><span>';
				$url = new moodle_url ( 'answeringView.php', array (
						'id' => $this->groupformationid,
						'category' => $key 
				) );
				$a->category = '<a href="' . $url . '">' . $a->category . '</a>';
				if ($values ['missing'] == 0) {
					echo get_string ( 'stats_all', 'groupformation', $a ) . ' <span class="questionaire_all">&#10004;</span>';
				} elseif ($values ['answered'] == 0) {
					echo get_string ( 'stats_none', 'groupformation', $a ) . ' <span class="questionaire_none">&#10008;</span>';
				} else {
					echo get_string ( 'stats_partly', 'groupformation', $a );
				}
				echo '</span></th></tr>';
			}
		}
		echo '</tbody>';
		echo '</table>';
		
		echo '</div>';
	}
	public function availabilityInfo() {
		$a = $this->store->getTime ();
		$start = intval ( $a ['start_raw'] );
		$end = intval ( $a ['end_raw'] );
		
		if (! ($start == 0) && ! ($end == 0)) {
			echo '<div class="questionaire_status">' . get_string ( 'questionaire_availability_info_now', 'groupformation', $a ) . '</div>';
		} elseif (($start == 0) && ($end > 0)) {
			echo '<div class="questionair_status">' . get_string ( 'questionaire_availability_info_until', 'groupformation', $a ) . '</div>';
		}
	}
	public function notAvailableInfo() {
		$a = $this->store->getTime ();
		$start = intval ( $a ['start_raw'] );
		$end = intval ( $a ['end_raw'] );
		
		if (! ($start == 0) && ! ($end == 0)){
			echo '<div class="questionaire_status">' . get_string ( 'questionaire_not_available', 'groupformation', $a ) . '</div>';
			echo '<div class="questionaire_status">' . get_string ( 'questionaire_availability_info_future', 'groupformation', $a ) . '</div>';
		} elseif (($start > 0) && ($end == 0)) {
			echo '<div class="questionaire_status">' . get_string ( 'questionaire_not_available', 'groupformation', $a ) . '</div>';
			echo '<div class="questionair_status">' . get_string ( 'questionaire_availability_info_from', 'groupformation', $a ) . '</div>';
		}
	}
}