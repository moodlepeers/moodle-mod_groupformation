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
class mod_groupformation_storage_manager {
	private $groupformationid;
	private $data;
	/**
	 * Constructs storage manager for a specific groupformation
	 *
	 * @param int $groupformationid        	
	 */
	public function __construct($groupformationid) {
		$this->groupformationid = $groupformationid;
		$this->data = new mod_groupformation_data();
	}
	
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
	 * Returns course id
	 *
	 * @return mixed
	 */
	public function getCourseID() {
		global $DB;
		return $DB->get_field ( 'groupformation', 'course', array (
				'id' => $this->groupformationid 
		) );
	}
	
	/**
	 * Returns instance number of all groupformations in course
	 */
	public function getInstanceNumber() {
		global $DB;
		$courseid = $this->getCourseID ();
		$records = $DB->get_records ( 'groupformation', array (
				'course' => $courseid 
		), 'id', 'id' );
		$i = 1;
		foreach ( $records as $id => $record ) {
			if ($id == $this->groupformationid)
				return $i;
			else
				$i ++;
		}
		return $i;
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
		$data->options = $this->convert_options ( $question ['options'] );
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
	public function get_number_of_answers($userId, $category = null) {
		global $DB;
		
		if (! is_null ( $category )) {
			return $DB->count_records ( 'groupformation_answer', array (
					'groupformation' => $this->groupformationid,
					'userid' => $userId,
					'category' => $category 
			) );
		}

		$scenario = $this->getScenario ();
		$names = $this->data->getCriterionSet ( $scenario, $this->groupformationid );
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
		$categories = $this->getCategories ();
		$sum = array_sum ( $this->getNumbers ( $categories ) );
		$user_sum = $this->get_number_of_answers ( $userid );
		return $sum == $user_sum;
	}
	
	/**
	 * TODO @Nora
	 *
	 * @param string $category        	
	 * @param int $numbers        	
	 * @param unknown $version        	
	 * @param boolean $init        	
	 */
	public function add_catalog_version($category, $numbers, $version, $init) {
		global $DB;
		
		$data = new stdClass ();
		$data->category = $category;
		$data->version = $version;
		$data->numberofquestion = $numbers;
		
		if ($init || $DB->count_records ( 'groupformation_q_version', array (
				'category' => $category 
		) ) == 0) {
			$DB->insert_record ( 'groupformation_q_version', $data );
		} else {
			$data->id = $DB->get_field ( 'groupformation_q_version', 'id', array (
					'category' => $category 
			) );
			$DB->update_record ( 'groupformation_q_version', $data );
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
		$data->topicvalues = $this->convert_options ( $topics );
		$data->knowledgevalues = $this->convert_options ( $knowledge );
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
		
		if ('en' == get_string ( "language", "groupformation" )) {
			$format = "l jS \of F j, Y, g:i a";
			$trans = array ();
			$times ['start'] = strtr ( date ( $format, $times ['start_raw'] ), $trans );
			$times ['end'] = strtr ( date ( $format, $times ['end_raw'] ), $trans );
		} elseif ('de' == get_string ( "language", "groupformation" )) {
			$format = "l, d.m.y, H:m";
			$trans = array (
					'Monday' => 'Montag',
					'Tuesday' => 'Dienstag',
					'Wednesday' => 'Mittwoch',
					'Thursday' => 'Donnerstag',
					'Friday' => 'Freitag',
					'Saturday' => 'Samstag',
					'Sunday' => 'Sonntag',
					'Mon' => 'Mo',
					'Tue' => 'Di',
					'Wed' => 'Mi',
					'Thu' => 'Do',
					'Fri' => 'Fr',
					'Sat' => 'Sa',
					'Sun' => 'So',
					'January' => 'Januar',
					'February' => 'Februar',
					'March' => 'März',
					'May' => 'Mai',
					'June' => 'Juni',
					'July' => 'Juli',
					'October' => 'Oktober',
					'December' => 'Dezember' 
			);
			$times ['start'] = strtr ( date ( $format, $times ['start_raw'] ), $trans ) . ' Uhr';
			$times ['end'] = strtr ( date ( $format, $times ['end_raw'] ), $trans ) . ' Uhr';
		}
		
		// $times ['start'] = date ( $format, $times ['start_raw'] );
		// $times ['end'] = date ( $format, $times ['end_raw'] );
		
		return $times;
	}
	
	/**
	 * Converts knowledge or topic array into XML-based syntax
	 *
	 * @param unknown $options        	
	 * @return string
	 */
	private function convert_options($options) {
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
	
	
	public function getPossibleLang($category){
		global $DB;
		
		$table = 'groupformation_' . $category;
		$lang = $DB->get_field($table, 'language', array(), IGNORE_MULTIPLE);
		return $lang;
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
	 * @return int
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
	public function getScenario($name = false) {
		global $DB;
		
		$settings = $DB->get_record ( 'groupformation', array (
				'id' => $this->groupformationid 
		) );
		
		if ($name) {
			return $this->data->getScenarioName ( $settings->szenario );
		}
		
		return $settings->szenario;
	}
	
	public function getTotalNumber() {
		$names = $this->data->getCriterionSet ( $this->getScenario (), $this->groupformationid );
		$number = 0;
		$numbers = $this->getNumbers ( $names );
		foreach ( $numbers as $n ) {
			$number = $number + $n;
		}
		
		return $number;
	}
	
	/**
	 * Changes status of questionaire for a specific user
	 *
	 * @param unknown $userId        	
	 * @param number $complete        	
	 */
	public function statusChanged($userId, $complete = 0) {
		$status = 0;
		if ($complete == 0) {
			$status = $this->answeringStatus ( $userId );
		}
		
		if ($status == - 1) {
			$this->setCompleted ( $userId, false );
		}
		
		if ($status == 0) {
			$this->setCompleted ( $userId, true );
			// TODO Mathevorkurs
			$this->assign_to_group_AB ( $userId );
		}
	}
	
	/**
	 * set status to complete
	 *
	 * @param int $userid        	
	 */
	public function setCompleted($userid, $completed = false) {
		global $DB;
		
		$exists = $DB->record_exists ( 'groupformation_started', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userid 
		) );
		
		if ($exists) {
			$data = $DB->get_record ( 'groupformation_started', array (
					'groupformation' => $this->groupformationid,
					'userid' => $userid 
			) );
			$data->completed = $completed;
			$data->timecompleted = time ();
			$DB->update_record ( 'groupformation_started', $data );
		} else {
			$data = new stdClass ();
			$data->completed = $completed;
			$data->groupformation = $this->groupformationid;
			$data->userid = $userid;
			$DB->insert_record ( 'groupformation_started', $data );
		}
		// }
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
	public function has_answer($userId, $category, $questionId) {
		global $DB;

		return $DB->record_exists( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userId,
				'category' => $category,
				'questionid' => $questionId 
		) );;
	}
	
	/**
	 * Returns categories with at least one question, not just the scenario-based category set
	 *
	 * @return multitype:multitype:string
	 */
	public function getCategories() {
		$category_set = $this->data->getCategorySet ( $this->getScenario () );
		$categories = array ();
		foreach ( $category_set as $category ) {
			if ($this->getNumber ( $category ) > 0) {
				// if ($category != 'general' || ($category == 'general' && $this->getEvaluationMethod() != 1))
				if ($category != 'grade' || $this->askForGrade ())
					$categories [] = $category;
			}
		}
		return $categories;
	}
	public function getPreviousCategory($category) {
		$categories = $this->getCategories ();
		$pos = $this->getPosition ( $category );
		if ($pos >= 1)
			$previous = $categories [$pos - 1];
		else
			$previous = '';
		return $previous;
	}
	public function askForGrade() {
		global $DB;
		$evaluationmethod = $DB->get_field ( 'groupformation', 'evaluationmethod', array (
				'id' => $this->groupformationid 
		) );
		if ($evaluationmethod != 1 && $evaluationmethod != 2)
			return false;
		return true;
	}
	
	/**
	 * Determines if someone already answered at least one question
	 *
	 * @return boolean
	 */
	public function already_answered() {
		global $DB;
		
		return !($DB->count_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid 
		) ) == 0);
	}
	
	/**
	 * Returns all answers of a specific user in a specific category
	 *
	 * @param int $userid        	
	 * @param string $category        	
	 * @return array
	 */
	public function getAnswers($userid, $category) {
		global $DB;
		
		return $DB->get_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userid,
				'category' => $category 
		) );
	}
	
	/**
	 * Returns all answers to a specific question in a specific category
	 *
	 * @param string $category        	
	 * @param int $questionid        	
	 * @return array
	 */
	public function getAnswersToSpecialQuestion($category, $questionid) {
		global $DB;
		
		return $DB->get_records ( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid,
				'category' => $category,
				'questionid' => $questionid 
		) );
	}
	
	/**
	 * Returns answer of a specific user to a specific question in a specific category
	 *
	 * @param int $userid        	
	 * @param string $category        	
	 * @param int $questionid        	
	 * @return int
	 */
	public function getSingleAnswer($userid, $category, $questionid) {
		global $DB;
		
		return $DB->get_field ( 'groupformation_answer', 'answer', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userid,
				'category' => $category,
				'questionid' => $questionid 
		) );
	}
	
	/**
	 * Saves answer of a specific user to a specific question in a specific category
	 *
	 * @param int $userid        	
	 * @param int $answer        	
	 * @param string $category        	
	 * @param int $questionid        	
	 */
	public function saveAnswer($userid, $answer, $category, $questionid) {
		global $DB;
		
		$answerAlreadyExist = $this->has_answer ( $userid, $category, $questionid );
		
		if ($answerAlreadyExist){
			$record = $DB->get_record( 'groupformation_answer', array (
				'groupformation' => $this->groupformationid,
				'userid' => $userid,
				'category' => $category,
				'questionid' => $questionid 
		) );		
			$record->answer = $answer;
			$DB->update_record ( 'groupformation_answer', $record );
		}else{
			$record = new stdClass ();
			$record->groupformation = $this->groupformationid;
			
			$record->userid = $userid;
			$record->category = $category;
			$record->questionid = $questionid;
			$record->answer = $answer;
			$DB->insert_record ( 'groupformation_answer', $record );
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
	public function close_questionnaire() {
		global $DB;
		
		$data = new stdClass ();
		$data->id = $this->groupformationid;
		$data->timeclose = time () - 1;
		
		$DB->update_record ( 'groupformation', $data );
	}
	
	/**
	 * Sets timestamp in groupformation in order to open/begin questionaire
	 */
	public function open_questionnaire() {
		global $DB;
		
		$data = new stdClass ();
		$data->id = $this->groupformationid;
		$data->timeclose = 0;
		$data->timeopen = time () - 1;
		
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
	 * Assigns user to group A or group B (creates those if they do not exist)
	 *
	 * @param int $userid        	
	 */
	public function assign_to_group_AB($userid) {
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
		
		$category_set = $this->getCategories ();
		
		$categories = array ();
		
		foreach ( $category_set as $category ) {
			$categories [$category] = $this->getNumber ( $category );
		}
		
		$stats = array ();
		foreach ( $categories as $category => $value ) {
			$count = $this->get_number_of_answers ( $userid, $category );
			$stats [$category] = array (
					'questions' => $value,
					'answered' => $count,
					'missing' => $value - $count 
			);
		}
		return $stats;
	}
	public function get_group_name_setting() {
		global $DB;
		return $DB->get_field ( 'groupformation', 'groupname', array (
				'id' => $this->groupformationid 
		) );
	}
	public function getName() {
		global $DB;
		return $DB->get_field ( 'groupformation', 'name', array (
				'id' => $this->groupformationid 
		) );
	}
	public function getPosition($category) {
		$categories = $this->getCategories ();
		if (in_array ( $category, $categories )) {
			$pos = array_search ( $category, $categories );
			return $pos;
		} else {
			return - 1;
		}
	}
	public function getPositions() {
		$categories = $this->getCategories ();
		return array_keys ( $categories );
	}
	
}