<?php

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once ($CFG->dirroot . '/mod/groupformation/classes/controller/grouping_controller.php');

class mod_groupformation_test_user_generator {
	
	/**
	 * Creates automated username or username prefix
	 * 
	 * @param int $j
	 * @param int $groupformationid
	 * @return string
	 */
	private function get_username($j, $groupformationid) {
		return 'user_g' . $groupformationid . '_nr' . $j;
	}
	
	/**
	 * 
	 * @param int $n
	 * @param int $groupformationid
	 * @param string $setAnswers
	 * @return boolean
	 */
	public function create_test_users($n, $groupformationid, $setAnswers = false, $randomized = false) {
		global $COURSE, $DB;
		
		$store = new mod_groupformation_storage_manager ( $groupformationid );
		
		// we want answers for all categories
		$categories = $store->getCategories ();
		
		$username = $this->get_username ( null, $groupformationid );
			
		$user_records = $DB->get_records_sql ( 'SELECT * FROM {user} WHERE username LIKE \'' . $username . '%\'' );
		
		if (count($user_records)>0){
			$record = end($user_records);
			$prev_username = $record->username;
			$prev_username_nr = intval(substr($prev_username, strpos($prev_username,"nr")+2));
			$first = $prev_username_nr+1;
			$last = $prev_username_nr+$n;
		}else{
			$first = 1;
			$last = $n;
		}
		for($j = $first; $j <= $last; $j ++) {
			$all_records = array();
			$username = $this->get_username ( $j, $groupformationid );
			$password = 'Moodle1234';
			
			try {
				$user = create_user_record ( $username, $password );
				$user->firstname = "Dummy";
				$user->lastname = "User ".$j;
				$DB->update_record('user', $user);
				$userid = $user->id;
			} catch ( Exception $e ) {
				$this->echowarn ( "Error while creating user. The user might already exist." );
				return false;
			}
			
			try {
				enrol_try_internal_enrol ( $COURSE->id, $userid, 5 ); // 5 == student role
			} catch ( Exception $e ) {
				$this->echowarn ( "Error while enrolment. User with ID=" . $userid . " is already in course" );
				return false;
			}
			
			if ($setAnswers) {
				try {
					$record = new stdClass ();
					$record->groupformation = $groupformationid;
					$record->userid = $userid;
					$record->completed = ($setAnswers)?1:0;
					$record->answer_count = $store->get_total_number_of_answers();
					$record->timecompleted = ($setAnswers)?time():null;
					$record->groupid = NULL;
					$DB->insert_record ( "groupformation_started", $record );
					
				} catch ( Exception $e ) {
					$this->echowarn("Error while saving questionaire status for user.");
					return false;
				}
				try {
					
					foreach ( $categories as $category ) {
						$m = $store->getNumber ( $category );
						for($i = 1; $i <= $m; $i ++) {
							$record = new stdClass ();
							$record->groupformation = $groupformationid;
							$record->category = $category;
							$record->questionid = $i; // $i, weil anzahl topics = anzahl id's
							$record->userid = $userid;
							if ($category == "topic" || $category == "knowledge") {
								$record->answer = ($j % 2 == 0) ? ($i) : ($m + 1 - $i); // $i, damit topics nur einmal, in "erstellter" Reihenfolge, sortiert sind
							} else {
								if ($randomized) {
									$record->answer = rand(1, $store->getMaxOptionOfCatalogQuestion($i, $category));
								} else {
									$record->answer = ($j % $store->getMaxOptionOfCatalogQuestion($i, $category))+1;
								}
							}
							$all_records [] = $record;
						}
					}
					
					$DB->insert_records ( "groupformation_answer", $all_records );
					
				} catch ( Exception $e ) {
					$this->echowarn("Error while saving answers status for user.");
					return false;
				}
			}
		}
		if ($setAnswers)
			$this->echowarn ( 'Users (and answers) have been created.' );
		else
			$this->echowarn ( 'Users have been created.');
		return true;
	}
	
	/**
	 * Echoes warning message
	 *
	 * @param string $string        	
	 */
	private function echowarn($string) {
		echo '<div class="alert">' . $string . '</div>';
// 		echo '<script>alert("'.$string.'");</script>';
	}
	
	/**
	 * Deletes test users and all related DB entries
	 *
	 * @param int $groupformationid        	
	 */
	public function delete_test_users($groupformationid) {
		global $DB;
		
		$username = $this->get_username ( null, $groupformationid );
		
		$user_records = $DB->get_records_sql ( 'SELECT * FROM {user} WHERE username LIKE \'' . $username . '%\'' );
		
		if (count ( $user_records ) > 0) {
			foreach ( $user_records as $userid => $record ) {
				
				try {
					$grouping_controller = new mod_groupformation_grouping_controller($groupformationid);
					$grouping_controller->delete();	
					
					$DB->delete_records ( "user", array (
							'id' => $userid 
					) );
					
					$DB->delete_records ( "groupformation_answer", array (
							'userid' => $userid 
					) );
					
					$DB->delete_records ( "groupformation_started", array (
							'userid' => $userid 
					) );
					
					$DB->delete_records ( "user_enrolments", array (
							'userid' => $userid 
					) );
				} catch ( Exception $e ) {
					$this->echowarn ( 'User with ID=' . $userid . ' has not been deleted.' );
					return false;
				}
			}
			
			$this->echowarn ( "All users have been deleted." );
			return true;
		} else {
			$this->echowarn ( "There was nothing to delete." );
			return true;
		}
	}
}