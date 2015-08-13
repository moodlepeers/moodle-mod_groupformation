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
 * Interface betweeen DB and Plugin
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once ($CFG->dirroot . '/group/lib.php');

class mod_groupformation_answer_manager {
	private $groupformationid;
	private $data;
	private $sm;
	/**
	 * Constructs storage manager for a specific groupformation
	 *
	 * @param int $groupformationid        	
	 */
	public function __construct($groupformationid) {
		$this->groupformationid = $groupformationid;
		$this->data = new mod_groupformation_data();
		$this->sm = new mod_groupformation_storage_manager($groupformationid);
	}
	
	/**
	 * Sets answer count for user
	 * 
	 * @param int $userid
	 */
	public function set_answer_count($userid){
		global $DB;
		
		$record = $DB->get_record ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid, 'userid'=>$userid
		) );
		
		$record->answer_count = $DB->count_records('groupformation_answer',array('groupformation'=>$this->groupformationid,'userid'=>$userid));
		
		$DB->update_record('groupformation_started', $record);
	}
	
	/**
	 *
	 * Returns all users (as ids) who started the questionaire (answered at least one question)
	 *
	 * @return array
	 */
	public function get_started_userids() {
		global $DB;
		
		$records = $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid 
		),'userid','userid' );
		
		$userids = array_keys($records);
		
		return $userids;
	}
	
	/**
	 * Returns all users (user IDs) who completed the questionaire
	 *
	 * @return multitype:NULL
	 */
	public function getUserIdsCompleted() {
		global $DB;
		
		$records = $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'completed' => '1' 
		),'userid','userid' );
		
		$userids = array_keys($userids);
		
		return $userids;
	}
	
// 	/**
// 	 * Determines the number of answered questions of a user (in all categories or a specified category)
// 	 *
// 	 * @param int $userId        	
// 	 * @param string $category        	
// 	 * @return number
// 	 */
// 	public function get_number_of_answers($userId, $category = null) {
// 		global $DB;
		
// 		if (! is_null ( $category )) {
// 			return $DB->count_records ( 'groupformation_answer', array (
// 					'groupformation' => $this->groupformationid,
// 					'userid' => $userId,
// 					'category' => $category 
// 			) );
// 		}

// 		$scenario = $this->getScenario ();
// 		$names = $this->data->getCriterionSet ( $scenario, $this->groupformationid );
// 		$number = 0;
// 		foreach ( $names as $name ) {
// 			$number = $number + $DB->count_records ( 'groupformation_answer', array (
// 					'groupformation' => $this->groupformationid,
// 					'userid' => $userId,
// 					'category' => $name 
// 			) );
// 		}
// 		return $number;
// 	}
	
// 	/**
// 	 * Determines whether user has answered every question or not
// 	 *
// 	 * @param int $userid        	
// 	 * @return boolean
// 	 */
// 	public function hasAnsweredEverything($userid) {
// 		$scenario = $this->getScenario ();
// 		$categories = $this->getCategories ();
// 		$sum = array_sum ( $this->getNumbers ( $categories ) );
// 		$user_sum = $this->get_number_of_answers ( $userid );
// 		return $sum == $user_sum;
// 	}
	
// 	/**
// 	 * Converts knowledge or topic array into XML-based syntax
// 	 *
// 	 * @param unknown $options        	
// 	 * @return string
// 	 */
// 	private function convert_options($options) {
// 		$op = implode ( "</OPTION>  <OPTION>", $options );
// 		return "<OPTION>" . $op . "</OPTION>";
// 	}
	
// 	/**
// 	 * Returns an array with number of questions in each category
// 	 *
// 	 * @param array $names        	
// 	 * @return multitype:mixed
// 	 */
// 	public function getNumbers(array $categories) {
// 		global $DB;
		
// 		$array = array ();
// 		foreach ( $categories as $category ) {
// 			$array [] = $this->getNumber ( $category );
// 			// if($category == 'topic' || $category == 'knowledge'){
// 			// $array[] = $DB->get_field('groupformation_q_settings', $category . 'valuesnumber', array('groupformation' => $this->groupformationid));
// 			// }else{
// 			// $array[] = $DB->get_field('groupformation_q_version', 'numberofquestion', array('category' => $category));
// 			// }
// 		}
		
// 		return $array;
// 	}

// 	/**
// 	 * Returns the number of questions in a specified category
// 	 *
// 	 * @param string $category        	
// 	 * @return mixed
// 	 */
// 	public function getNumber($category = null) {
// 		global $DB;
		
// 		if ($category == 'topic' || $category == 'knowledge') {
// 			return $DB->get_field ( 'groupformation_q_settings', $category . 'valuesnumber', array (
// 					'groupformation' => $this->groupformationid 
// 			) );
// 		} else {
// 			return $DB->get_field ( 'groupformation_q_version', 'numberofquestion', array (
// 					'category' => $category 
// 			) );
// 		}
// 	}
	
// 	public function getTotalNumber() {
// 		$names = $this->data->getCriterionSet ( $this->getScenario (), $this->groupformationid );
// 		$number = 0;
// 		$numbers = $this->getNumbers ( $names );
// 		foreach ( $numbers as $n ) {
// 			$number = $number + $n;
// 		}
		
// 		return $number;
// 	}
	
// 	/**
// 	 * Changes status of questionaire for a specific user
// 	 *
// 	 * @param unknown $userId        	
// 	 * @param number $complete        	
// 	 */
// 	public function statusChanged($userId, $complete = 0) {
// 		$status = 0;
// 		if ($complete == 0) {
// 			$status = $this->answeringStatus ( $userId );
// 		}
		
// 		if ($status == - 1) {
// 			$this->setCompleted ( $userId, false );
// 		}
		
// 		if ($status == 0) {
// 			$this->setCompleted ( $userId, true );
// 			// TODO Mathevorkurs
// 			$this->assign_to_group_AB ( $userId );
// 		}
// 	}
	
// 	/**
// 	 * set status to complete
// 	 *
// 	 * @param int $userid        	
// 	 */
// 	public function setCompleted($userid, $completed = false) {
// 		global $DB;
		
// 		$exists = $DB->record_exists ( 'groupformation_started', array (
// 				'groupformation' => $this->groupformationid,
// 				'userid' => $userid 
// 		) );
		
// 		if ($exists) {
// 			$data = $DB->get_record ( 'groupformation_started', array (
// 					'groupformation' => $this->groupformationid,
// 					'userid' => $userid 
// 			) );
// 			$data->completed = $completed;
// 			$data->timecompleted = time ();
// 			$DB->update_record ( 'groupformation_started', $data );
// 		} else {
// 			$data = new stdClass ();
// 			$data->completed = $completed;
// 			$data->groupformation = $this->groupformationid;
// 			$data->userid = $userid;
// 			$DB->insert_record ( 'groupformation_started', $data );
// 		}
// 		// }
// 	}
	
// 	/**
// 	 * Returns answering status for user
// 	 * 0 seen
// 	 * 1 completed
// 	 * -1 otherwise
// 	 *
// 	 * @param unknown $userId        	
// 	 * @return number
// 	 */
// 	public function answeringStatus($userId) {
// 		global $DB;
		
// 		$seen = $DB->count_records ( 'groupformation_started', array (
// 				'groupformation' => $this->groupformationid,
// 				'userid' => $userId,
// 				'completed' => '0' 
// 		) );
// 		$completed = $DB->count_records ( 'groupformation_started', array (
// 				'groupformation' => $this->groupformationid,
// 				'userid' => $userId,
// 				'completed' => '1' 
// 		) );
		
// 		if ($seen == 1) {
// 			return 0;
// 		} elseif ($completed == 1) {
// 			return 1;
// 		} else {
// 			return - 1;
// 		}
// 	}
	
// 	/**
// 	 * Returns either number of completed questionaire or number of all started and completed questionaires
// 	 *
// 	 * @param boolean $completed        	
// 	 * @return number
// 	 */
// 	public function getNumberofAnswerStauts($completed) {
// 		global $DB;
		
// 		$number = 0;
// 		$number = $DB->count_records ( 'groupformation_started', array (
// 				'groupformation' => $this->groupformationid,
// 				'completed' => '1' 
// 		) );
// 		if (! $completed) {
// 			$number = $number + $DB->count_records ( 'groupformation_started', array (
// 					'groupformation' => $this->groupformationid,
// 					'completed' => '0' 
// 			) );
// 		}
		
// 		return $number;
// 	}
	
// 	/**
// 	 * Returns whether answer of a specific user for a specific question in a specific category exists or not
// 	 *
// 	 * @param int $userId        	
// 	 * @param string $category        	
// 	 * @param int $questionId        	
// 	 * @return boolean
// 	 */
// 	public function has_answer($userId, $category, $questionId) {
// 		global $DB;

// 		return $DB->record_exists( 'groupformation_answer', array (
// 				'groupformation' => $this->groupformationid,
// 				'userid' => $userId,
// 				'category' => $category,
// 				'questionid' => $questionId 
// 		) );;
// 	}
	
// 	public function getPreviousCategory($category) {
// 		$categories = $this->getCategories ();
// 		$pos = $this->getPosition ( $category );
// 		if ($pos >= 1)
// 			$previous = $categories [$pos - 1];
// 		else
// 			$previous = '';
// 		return $previous;
// 	}

// 	/**
// 	 * Determines if someone already answered at least one question
// 	 *
// 	 * @return boolean
// 	 */
// 	public function already_answered() {
// 		global $DB;
		
// 		return !($DB->count_records ( 'groupformation_answer', array (
// 				'groupformation' => $this->groupformationid 
// 		) ) == 0);
// 	}
	
// 	/**
// 	 * Returns all answers of a specific user in a specific category
// 	 *
// 	 * @param int $userid        	
// 	 * @param string $category        	
// 	 * @return array
// 	 */
// 	public function getAnswers($userid, $category) {
// 		global $DB;
		
// 		return $DB->get_records ( 'groupformation_answer', array (
// 				'groupformation' => $this->groupformationid,
// 				'userid' => $userid,
// 				'category' => $category 
// 		) );
// 	}
	
// 	/**
// 	 * Returns all answers to a specific question in a specific category
// 	 *
// 	 * @param string $category        	
// 	 * @param int $questionid        	
// 	 * @return array
// 	 */
// 	public function getAnswersToSpecialQuestion($category, $questionid) {
// 		global $DB;
		
// 		return $DB->get_records ( 'groupformation_answer', array (
// 				'groupformation' => $this->groupformationid,
// 				'category' => $category,
// 				'questionid' => $questionid 
// 		) );
// 	}
	
// 	/**
// 	 * Returns answer of a specific user to a specific question in a specific category
// 	 *
// 	 * @param int $userid        	
// 	 * @param string $category        	
// 	 * @param int $questionid        	
// 	 * @return int
// 	 */
// 	public function getSingleAnswer($userid, $category, $questionid) {
// 		global $DB;
		
// 		return $DB->get_field ( 'groupformation_answer', 'answer', array (
// 				'groupformation' => $this->groupformationid,
// 				'userid' => $userid,
// 				'category' => $category,
// 				'questionid' => $questionid 
// 		) );
// 	}
	
// 	/**
// 	 * Saves answer of a specific user to a specific question in a specific category
// 	 *
// 	 * @param int $userid        	
// 	 * @param int $answer        	
// 	 * @param string $category        	
// 	 * @param int $questionid        	
// 	 */
// 	public function saveAnswer($userid, $answer, $category, $questionid) {
// 		global $DB;
		
// 		$answerAlreadyExist = $this->has_answer ( $userid, $category, $questionid );
		
// 		if ($answerAlreadyExist){
// 			$record = $DB->get_record( 'groupformation_answer', array (
// 				'groupformation' => $this->groupformationid,
// 				'userid' => $userid,
// 				'category' => $category,
// 				'questionid' => $questionid 
// 		) );		
// 			$record->answer = $answer;
// 			$DB->update_record ( 'groupformation_answer', $record );
// 		}else{
// 			$record = new stdClass ();
// 			$record->groupformation = $this->groupformationid;
			
// 			$record->userid = $userid;
// 			$record->category = $category;
// 			$record->questionid = $questionid;
// 			$record->answer = $answer;
// 			$DB->insert_record ( 'groupformation_answer', $record );
// 		}
// 	}
	
// 	/**
// 	 * Returns whether questionaire was completed and send by user or not
// 	 *
// 	 * @param int $userid        	
// 	 * @return boolean
// 	 */
// 	public function isQuestionaireCompleted($userid) {
// 		global $DB;
		
// 		if (! $DB->record_exists ( 'groupformation_started', array (
// 				'userid' => $userid,
// 				'groupformation' => $this->groupformationid 
// 		) ))
// 			return false;
// 		$record = $DB->get_record ( 'groupformation_started', array (
// 				'userid' => $userid,
// 				'groupformation' => $this->groupformationid 
// 		) );
		
// 		return ($record->completed == '1');
// 	}
	
// 	/**
// 	 * Returns group size
// 	 *
// 	 * @return mixed
// 	 */
// 	public function getGroupSize() {
// 		global $DB;
// 		return $DB->get_field ( 'groupformation', 'maxmembers', array (
// 				'id' => $this->groupformationid 
// 		) );
// 	}
	
// 	/**
// 	 * computes stats about answered and misssing questions
// 	 *
// 	 * @return multitype:multitype:number stats
// 	 */
// 	public function getStats($userid) {
// 		$scenario = $this->getScenario ();
		
// 		$category_set = $this->getCategories ();
		
// 		$categories = array ();
		
// 		foreach ( $category_set as $category ) {
// 			$categories [$category] = $this->getNumber ( $category );
// 		}
		
// 		$stats = array ();
// 		foreach ( $categories as $category => $value ) {
// 			$count = $this->get_number_of_answers ( $userid, $category );
// 			$stats [$category] = array (
// 					'questions' => $value,
// 					'answered' => $count,
// 					'missing' => $value - $count 
// 			);
// 		}
// 		return $stats;
// 	}

// 	/**
// 	 * Returns the total number of answers
// 	 * 
// 	 * @return int
// 	 */
// 	public function get_total_number_of_answers(){
// 		$categories = $this->getCategories();
// 		$numbers = $this->getNumbers($categories);
// 		return array_sum($numbers);
// 	}
}