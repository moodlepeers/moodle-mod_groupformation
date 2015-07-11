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
 * interface betweeen DB and Plugin
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// TODO einige Methoden noch nicht getestet
// defined('MOODLE_INTERNAL') || die(); -> template
// namespace mod_groupformation\moodle_interface;
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once ($CFG->dirroot . '/group/lib.php');
class mod_groupformation_storage_manager {
	private $groupformationid;
	
	/**
	 * Constructs storage manager for a specific groupformation
	 *
	 * @param unknown $groupformationid        	
	 */
	public function __construct($groupformationid) {
		$this->groupformationid = $groupformationid;
	}
	
	// public function add_question($question){
	// global $CFG, $DB;
	
	// $data = new stdClass();
	// $data->groupformation = $this->groupformationid;
	
	// $data->type = $question['type'];
	// $data->question = $question['question'];
	// $data->options = $this->convertOptions($question['options']);
	
	// var_dump($data);
	
	// //var_dump($data);
	// if($DB->count_records('groupformation_question', array('groupformation' => $this->groupformationid)) == 0){
	// $DB->insert_record('groupformation_question', $data);
	// }
	// }
	
	// es wird davon ausgegangen, dass alle Fragentabellen immer auf dem gleichen Stand sind
	/**
	 * Returns if DB does not contain questions for a specific category
	 *
	 * @param string $category        	
	 * @return boolean
	 */
	public function catalogTableNotSet($category = 'grade') {
		global $DB;
		// $indexes = $DB->get_indexes('groupformation_en_team');
		$count = $DB->count_records ( 'groupformation_' . $category );
		// var_dump($count);
		return $count == 0;
	}
	
	/**
	 *
	 * @param moodleform_mod $mform        	
	 */
	function changesPossible(&$mform) {
		global $DB;
		// Are changes possible?
		// check if somebody submitted an answer already
		$id = $this->groupformationid;
		if ($id != '') {
			$count = $DB->count_records ( 'groupformation_answer', array (
					'groupformation' => $id 
			) );
			if ($count > 0)
				return False;
		}
		return true;
	}
	
	/**
	 * Deletes all questions in a specific category
	 *
	 * @param string $category        	
	 */
	public function delete_all_catalog_questions($category) {
		global $DB;
		$DB->delete_records ( 'groupformation_' . $category );
	}
	
	/**
	 * Adds a catalog question in a specific language and category
	 *
	 * @param array $question        	
	 * @param string $language        	
	 * @param string $category        	
	 */
	public function add_catalog_question($question, $language, $category) {
		global $CFG, $DB;
		
		$data = new stdClass ();
		
		$data->type = $question ['type'];
		$data->question = $question ['question'];
		$data->options = $this->convertOptions ( $question ['options'] );
		$data->position = $question ['position'];
		$data->language = $language;
		$data->optionmax = count ( $question ['options'] );
		
		$DB->insert_record ( 'groupformation_' . $category, $data );
	}
	
	/**
	 *
	 * Returns all users (user IDs) who answered any questions
	 *
	 * @return multitype:NULL
	 */
	public function getTotalUserIds() {
		global $DB;
		
		$array = array ();
		
		$records = $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid 
		) );
		
		foreach ( $records as $record ) {
			
			$array [] = $record->userid;
		}
		
		return $array;
	}
	
	/**
	 * Returns all users (user IDs) who completed the questionaire
	 *
	 * @return multitype:NULL
	 */
	public function getUserIdsCompleted() {
		global $DB;
		
		$array = array ();
		$records = $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'completed' => '1' 
		) );
		foreach ( $records as $record ) {
			$array [] = $record->userid;
		}
		
		return $array;
	}
	
	/**
	 * Determines the number of answered questions of a user (in all categories or a specified category)
	 *
	 * @param int $userId        	
	 * @param string $category        	
	 * @return number
	 */
	public function answerNumberForUser($userId, $category = null) {
		global $DB;
		
		if (! is_null ( $category )) {
			return $DB->count_records ( 'groupformation_answer', array (
					'groupformation' => $this->groupformationid,
					'userid' => $userId,
					'category' => $category 
			) );
		}
		
		$data = new mod_groupformation_data ();
		
		$scenario = $this->getScenario ();
		$names = $data->getCriterionSet ( $scenario , $this->groupformationid);
		$number = 0;
		foreach ( $names as $name ) {
			$number = $number + $DB->count_records ( 'groupformation_answer', array (
					'groupformation' => $this->groupformationid,
					'userid' => $userId,
					'category' => $name 
			) );
		}
		return $number;
	}
	
	/**
	 * Determines whether user has answered every question or not
	 *
	 * @param int $userid        	
	 * @return boolean
	 */
	public function hasAnsweredEverything($userid) {
		$scenario = $this->getScenario ();
		$data = new mod_groupformation_data ();
		$categories = $this->getCategories();
		$sum = array_sum ( $this->getNumbers ( $categories ) );
		$user_sum = $this->answerNumberForUser ( $userid );
		return $sum == $user_sum;
	}
	
	/**
	 * TODO @Nora
	 *
	 * @param unknown $category        	
	 * @param unknown $numbers        	
	 * @param unknown $version        	
	 * @param unknown $init        	
	 */
	public function add_catalog_version($category, $numbers, $version, $init) {
		global $DB;
		
		$data = new stdClass ();
		$data->category = $category;
		$data->version = $version;
		$data->numberofquestion = $numbers;
		
	//hier was ver�ndert
		if ($init || $DB->count_records('groupformation_q_version', array('category' => $category)) == 0) {
			$DB->insert_record ( 'groupformation_q_version', $data );
		} else {
			$data->id = $DB->get_field ( 'groupformation_q_version', 'id', array (
					'category' => $category 
			) );
			$DB->update_record ( 'groupformation_q_version', $data );
		}
	}
	
	//alle werden auf "abgegeben" gesetzt
	public function setAllCommited($users){
		
		foreach($users as $userId){
			if($this->answeringStatus($userId) != 1){
				$this->statusChanged($userId);
			}
		}
	}
	
	/**
	 * Determines whether the DB contains for a specific category a specific version or not
	 *
	 * @param string $category        	
	 * @param string $version        	
	 * @return boolean
	 */
	public function latestVersion($category, $version) {
		global $DB;
		
		$count = $DB->count_records ( 'groupformation_q_version', array (
				'category' => $category,
				'version' => $version 
		) );
		
		return $count == 1;
	}
	
	// $init true, wenn es eine initialisierung ist | false wenn es ein Update ist
	/**
	 * Adds/Updates knowledge and topic setting of groupformation
	 *
	 * @param unknown $knowledge        	
	 * @param unknown $topics        	
	 * @param unknown $init        	
	 */
	public function add_setting_question($knowledge, $topics, $init) {
		global $DB;
		
		$data = new stdClass ();
		$data->groupformation = $this->groupformationid;
		$data->topicvalues = $this->convertOptions ( $topics );
		$data->knowledgevalues = $this->convertOptions ( $knowledge );
		$data->topicvaluesnumber = count ( $topics );
		$data->knowledgevaluesnumber = count ( $knowledge );
		
		if ($init) {
			$DB->insert_record ( 'groupformation_q_settings', $data );
		} elseif ($DB->count_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid 
		) ) == 0) {
			$data->id = $DB->get_field ( 'groupformation_q_settings', 'id', array (
					'groupformation' => $this->groupformationid 
			) );
			$DB->update_record ( 'groupformation_q_settings', $data );
		}
	}
	
	// gibt ein array zur�ck, in dem auf der ersten Position die Startzeit gespeichert ist und auf der zweiten Position die Endzeit
	/**
	 * Returns map with availability times (xxx_raw is timestamp, xxx is formatted time for display)
	 * 
	 * @return multitype:string NULL mixed
	 */
	public function getTime() {
		global $DB;
		$times = array ();
		$times ['start_raw'] = $DB->get_field ( 'groupformation', 'timeopen', array (
				'id' => $this->groupformationid 
		) );
		$times ['end_raw'] = $DB->get_field ( 'groupformation', 'timeclose', array (
				'id' => $this->groupformationid 
		) );
		
		$times ['start'] = date ( "Y-m-d H:i", $times ['start_raw'] );
		$times ['end'] = date ( "Y-m-d H:i", $times ['end_raw'] );
		
		return $times;
	}
	
	/**
	 * Converts knowledge or topic array into XML-based syntax
	 * 
	 * @param unknown $options        	
	 * @return string
	 */
	private function convertOptions($options) {
		$op = implode ( "</OPTION>  <OPTION>", $options );
		return "<OPTION>" . $op . "</OPTION>";
	}
	
	/**
	 * Returns an array with number of questions in each category
	 *
	 * @param array $names        	
	 * @return multitype:mixed
	 */
	public function getNumbers(array $categories) {
		global $DB;
		
		$array = array ();
		foreach ( $categories as $category ) {
			$array [] = $this->getNumber ( $category );
			// if($category == 'topic' || $category == 'knowledge'){
			// $array[] = $DB->get_field('groupformation_q_settings', $category . 'valuesnumber', array('groupformation' => $this->groupformationid));
			// }else{
			// $array[] = $DB->get_field('groupformation_q_version', 'numberofquestion', array('category' => $category));
			// }
		}
		
		return $array;
	}
	
	/**
	 * Returns the number of questions in a specified category
	 *
	 * @param string $category        	
	 * @return mixed
	 */
	public function getNumber($category) {
		global $DB;
		
		if ($category == 'topic' || $category == 'knowledge') {
			return $DB->get_field ( 'groupformation_q_settings', $category . 'valuesnumber', array (
					'groupformation' => $this->groupformationid 
			) );
		} else {
			return $DB->get_field ( 'groupformation_q_version', 'numberofquestion', array (
					'category' => $category 
			) );
		}
	}
	
	/**
	 * Returns either knowledge or topic values
	 *
	 * @param unknown $category        	
	 * @return mixed
	 */
	public function getKnowledgeOrTopicValues($category) {
		global $DB;
		
		return $DB->get_field ( 'groupformation_q_settings', $category . 'values', array (
				'groupformation' => $this->groupformationid 
		) );
	}
	
	/**
	 * Returns max number of options for a specific question in a specific category
	 *
	 * @param unknown $i        	
	 * @param string $category        	
	 * @return mixed
	 */
	public function getMaxOptionOfCatalogQuestion($i, $category = 'grade') {
		global $DB;
		
		$table = "groupformation_" . $category;
		return $DB->get_field ( $table, 'optionmax', array (
				'language' => 'en',
				'position' => $i 
		) );
	}
	
	/**
	 * Returns a specific question in a specific category
	 *
	 * @param unknown $i        	
	 * @param string $category        	
	 * @param string $lang        	
	 * @return mixed
	 */
	public function getCatalogQuestion($i, $category = 'general', $lang = 'en') {
		global $DB;
		
		$table = "groupformation_" . $category;
		$return = $DB->get_record ( $table, array (
				'language' => $lang,
				'position' => $i 
		) );
		
		return $return;
	}
	
	/**
	 * Returns the scenario
	 *
	 * @return int
	 */
	public function getScenario() {
		global $DB;
		
		$settings = $DB->get_record ( 'groupformation', array (
				'id' => $this->groupformationid 
		) );
		
		return $settings->szenario;
	}
	
	/**
	 * Changes status of questionaire for a specific user
	 *
	 * @param unknown $userId        	
	 * @param number $complete        	
	 */
	public function statusChanged($userId, $complete = 0) {
		global $DB;
		
		$status = 0;
		if ($complete == 0) {
			$status = $this->answeringStatus ( $userId );
		}
		
		$data = new stdClass ();
		$data->groupformation = $this->groupformationid;
		$data->userid = $userId;
		
		if ($status == - 1) {
			$data->completed = 0;
			$DB->insert_record ( 'groupformation_started', $data );
		}
		
		if ($status == 0) {
			$data->completed = 1;
			$data->id = $DB->get_field ( 'groupformation_started', 'id', array (
					'groupformation' => $this->groupformationid,
					'userid' => $userId 
			) );
			$data->timecompleted = time ();
			
			$DB->update_record ( 'groupformation_started', $data );
			
			// TODO @Rene Here Code for Assigning to GROUP A or GROUP B for MATHE
			$this->assignToGroup ( $userId );
		}
	}
	
	/**
	 * Returns answering status for user
	 * 0 seen
	 * 1 completed
	 * -1 otherwise
	 *
	 * @param unknown $userId        	
	 * @return number
	 */
	public function answeringStatus($userId) {
		global $DB;
		
		$seen = $DB->count_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userId,
				'completed' => '0' 
		) );
		$completed = $DB->count_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userId,
				'completed' => '1' 
		) );
		
		if ($seen == 1) {
			return 0;
		} elseif ($completed == 1) {
			return 1;
		} else {
			return - 1;
		}
	}
	
	/**
	 * Returns either number of completed questionaire or number of all started and completed questionaires
	 *
	 * @param boolean $completed        	
	 * @return number
	 */
	public function getNumberofAnswerStauts($completed) {
		global $DB;
		
		$number = 0;
		$number = $DB->count_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'completed' => '1' 
		) );
		if (! $completed) {
			$number = $number + $DB->count_records ( 'groupformation_started', array (
					'groupformation' => $this->groupformationid,
					'completed' => '0' 
			) );
		}
		
		return $number;
	}
	
	/**
	 * Returns whether answer of a specific user for a specific question in a specific category exists or not
	 *
	 * @param int $userId        	
	 * @param string $category        	
	 * @param int $questionId        	
	 * @return boolean
	 */
	public function answerExist($userId, $category, $questionId) {
		global $DB;
		
		$count = $DB->count_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userId,
				'category' => $category,
				'questionid' => $questionId 
		) );
		return $count == 1;
	}
	
	/**
	 * Returns categories with at least one question, not just the scenario-based category set
	 *
	 * @return multitype:multitype:string
	 */
	public function getCategories() {
		$data = new mod_groupformation_data ();
		$category_set = $data->getCategorySet ( $this->getScenario () );
		$categories = array ();
		foreach ( $category_set as $category ) {
			if ($this->getNumber ( $category ) > 0) {
				// if ($category != 'general' || ($category == 'general' && $this->getEvaluationMethod() != 1))
				if ($category != 'grade' || $this->askForGrade())
				$categories [] = $category;
			}
		}
		return $categories;
	}
	
	public function getPreviousCategory($category){
		$categories = $this->getCategories ();
		$pos = $this->getPosition($category);
		if ($pos >= 1)
			$previous = $categories[$pos - 1];
		else
			$previous = '';
		return $previous;
	}
	
	public function askForGrade(){
		global $DB;
		$evaluationmethod = $DB->get_field('groupformation', 'evaluationmethod', array('id'=>$this->groupformationid));
		if ($evaluationmethod != 1 && $evaluationmethod != 2)
			return false; 
		return true;
	}
	
	/**
	 * Determines if no answers for groupformation exist
	 *
	 * @return boolean
	 */
	public function generalAnswerNotExist() {
		global $DB;
		
		return ($DB->count_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid 
		) ) == 0);
	}
	
	/**
	 * Returns all answers of a specific user in a specific category
	 *
	 * @param unknown $userId        	
	 * @param unknown $category        	
	 * @return multitype:
	 */
	public function getAnswers($userId, $category) {
		global $DB;
		
		return $DB->get_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userId,
				'category' => $category 
		) );
	}
	
	/**
	 * Returns all answers to a specific question in a specific category
	 *
	 * @param unknown $category        	
	 * @param unknown $questionId        	
	 * @return multitype:
	 */
	public function getAnswersToSpecialQuestion($category, $questionId) {
		global $DB;
		
		return $DB->get_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid,
				'category' => $category,
				'questionid' => $questionId 
		) );
	}
	
	/**
	 * Returns answer of a specific user to a specific question in a specific category
	 *
	 * @param unknown $userId        	
	 * @param unknown $category        	
	 * @param unknown $qID        	
	 * @return mixed
	 */
	public function getSingleAnswer($userId, $category, $qID) {
		global $DB;
		
		return $DB->get_field ( 'groupformation_answer', 'answer', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userId,
				'category' => $category,
				'questionid' => $qID 
		) );
	}
	
	/**
	 * Saves answer of a specific user to a specific question in a specific category
	 *
	 * @param unknown $userId        	
	 * @param unknown $answer        	
	 * @param unknown $category        	
	 * @param unknown $questionId        	
	 */
	public function saveAnswer($userId, $answer, $category, $questionId) {
		global $DB;
		
		$answerAlreadyExist = $this->answerExist ( $userId, $category, $questionId );
		
		$data = new stdClass ();
		$data->groupformation = $this->groupformationid;
		
		$data->userid = $userId;
		$data->category = $category;
		$data->questionid = $questionId;
		$data->answer = $answer;
		
		if (! $answerAlreadyExist) {
			$DB->insert_record ( 'groupformation_answer', $data );
		} else {
			$data->id = $DB->get_field ( 'groupformation_answer', 'id', array (
					'groupformation' => $this->groupformationid,
					'userid' => $userId,
					'category' => $category,
					'questionid' => $questionId 
			) );
			$DB->update_record ( 'groupformation_answer', $data );
		}
	}
	
	/**
	 * Returns whether questionaire is available or not
	 *
	 * @return boolean
	 */
	public function isQuestionaireAvailable() {
		global $DB;
		$now = time ();
		
		$time = $this->getTime ();
		
		$start = intval ( $time ['start_raw'] );
		$end = intval ( $time ['end_raw'] );
		
		if (($start == 0) && ($end == 0)) {
			return true;
		} elseif (($start == 0) && ($now <= $end)) {
			return true;
		} elseif (($now >= $start) && ($end == 0)) {
			return true;
		} elseif (($now >= $start) && ($now <= $end)) {
			return true;
		}
		
		return false;
	}

	/**
	 * Sets timestamp in groupformation in order to close/terminate questionaire
	 */
    public function closeQuestionnaire() {
        global $DB;

        $data = new stdClass ();
        $data->id = $this->groupformationid;
        $data->timeclose = time ()-1;

        $DB->update_record ( 'groupformation', $data );
    }

    /**
     * Sets timestamp in groupformation in order to open/begin questionaire
     */
    public function openQuestionnaire() {
        global $DB;

        $data = new stdClass ();
        $data->id = $this->groupformationid;
        $data->timeclose = 0;
        $data->timeopen = time ()-1;

        $DB->update_record ( 'groupformation', $data );
    }
	
	/**
	 * Returns whether questionaire was completed and send by user or not
	 *
	 * @param int $userid        	
	 * @return boolean
	 */
	public function isQuestionaireCompleted($userid) {
		global $DB;
		
		if (! $DB->record_exists ( 'groupformation_started', array (
				'userid' => $userid,
				'groupformation' => $this->groupformationid 
		) ))
			return false;
		$record = $DB->get_record ( 'groupformation_started', array (
				'userid' => $userid,
				'groupformation' => $this->groupformationid 
		) );
		
		return ($record->completed == '1');
	}
	
	/**
	 * @Rene willst du dass nicht lieber in eine neue Klasse auslagern?
	 * Assigns user to group A or group B (creates those if they do not exist)
	 *
	 * @param unknown $userid        	
	 */
	public function assignToGroup($userid) {
		global $DB, $COURSE;
		$completed = 1;
		
		if (! $DB->record_exists ( 'groups', array (
				'courseid' => $COURSE->id,
				'name' => 'Gruppe A' 
		) )) {
			$record = new stdClass ();
			$record->courseid = $COURSE->id;
			$record->name = "Gruppe A";
			$record->timecreated = time ();
			
			$a = groups_create_group ( $record );
		}
		if (! $DB->record_exists ( 'groups', array (
				'courseid' => $COURSE->id,
				'name' => 'Gruppe B' 
		) )) {
			$record = new stdClass ();
			$record->courseid = $COURSE->id;
			$record->name = "Gruppe B";
			$record->timecreated = time ();
			
			$b = groups_create_group ( $record );
		}
		
		$records = $DB->get_records ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'completed' => $completed 
		), 'timecompleted', 'id, userid, timecompleted' );
		
		if (count ( $records ) > 0) {
			$i = 0;
			foreach ( $records as $id => $record ) {
				if ($record->userid == $userid) {
					break;
				}
				$i ++;
			}
			
			$a = $DB->get_field ( 'groups', 'id', array (
					'courseid' => $COURSE->id,
					'name' => 'Gruppe A' 
			) );
			$b = $DB->get_field ( 'groups', 'id', array (
					'courseid' => $COURSE->id,
					'name' => 'Gruppe B' 
			) );
			
			if ($i % 2 == 0) {
				// sort to group A
				groups_add_member ( $a, $userid );
				$DB->set_field ( 'groupformation_started', 'groupid', $a, array (
						'groupformation' => $this->groupformationid,
						'completed' => $completed,
						'userid' => $userid 
				) );
			}
			
			if ($i % 2 == 1) {
				// sort to group B
				groups_add_member ( $b, $userid );
				$DB->set_field ( 'groupformation_started', 'groupid', $b, array (
						'groupformation' => $this->groupformationid,
						'completed' => $completed,
						'userid' => $userid 
				) );
			}
		}
	}
	
	/**
	 * Returns group size
	 *
	 * @return mixed
	 */
	public function getGroupSize() {
		global $DB;
		return $DB->get_field ( 'groupformation', 'maxmembers', array (
				'id' => $this->groupformationid 
		) );
	}
	
	/**
	 * computes stats about answered and misssing questions
	 *
	 * @return multitype:multitype:number stats
	 */
	public function getStats($userid) {
		$scenario = $this->getScenario ();
	
		$data = new mod_groupformation_data ();
	
		$category_set = $this->getCategories();
	
		$categories = array ();
	
		foreach ( $category_set as $category ) {
			$categories [$category] = $this->getNumber ( $category );
		}
	
		$stats = array ();
		foreach ( $categories as $category => $value ) {
			$count = $this->answerNumberForUser ( $userid, $category );
			$stats [$category] = array (
					'questions' => $value,
					'answered' => $count,
					'missing' => $value - $count
			);
		}
		return $stats;
	}
	
	public function getGroupName(){
		global $DB;
		return $DB->get_field('groupformation', 'groupname',array (
				'id' => $this->groupformationid 
		) );
	}
	
	public function getName(){
		global $DB;
		return $DB->get_field('groupformation', 'name',array (
				'id' => $this->groupformationid
		) );
	}
    
    public function getPosition($category){
    	$categories = $this->getCategories();
    	if (in_array($category, $categories)){
	    	$pos = array_search($category, $categories);
	    	return $pos;
    	}else{
    		return -1;
    	}
    }
    
    public function getPositions(){
    	$categories = $this->getCategories();
    	return array_keys($categories);
    }
    
}