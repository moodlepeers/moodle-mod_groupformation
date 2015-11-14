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
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// defined('MOODLE_INTERNAL') || die(); -> template
// namespace mod_groupformation\classes\lecturer_settings;
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

// require_once 'storage_manager.php';
require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once (dirname ( __FILE__ ) . '/define_file.php');
class mod_groupformation_util {
	
	public static function update_questions(mod_groupformation_storage_manager $store){
		$names = $store->get_raw_categories ();
		$xmlLoader = new mod_groupformation_xml_loader ();
		$xmlLoader->set_store ( $store );
		// wenn die Datenbank noch komplett leer ist, speicher einfach alle Infos aus den xml's ab
		// ansonsten überprüfe zu jeder Kategorie die Versionsnummer und ändere bei bedarf
		if ($store->catalog_table_not_set ()) {
				
			foreach ( $names as $category ) {
		
				if ($category != 'topic' && $category != 'knowledge') {
					$array = $xmlLoader->save_data ( $category );
					$version = $array [0] [0];
					$numbers = $array [0] [1];
					$store->add_catalog_version ( $category, $numbers, $version, TRUE );
				}
			}
		} else {
			// TODO Wenn man die Fragen ändert, ändern sich auch in den alten groupformation-Instanzen
			// Da gibt es dann unter Umständen Konsistenzprobleme
			// Da müssen wir nochmal drüber sprechen
			foreach ( $names as $category ) {
				if ($category != 'topic' && $category != 'knowledge') {
					$xmlLoader->latest_version ( $category );
				}
			}
		}
	}
	
	/**
	 * Returns html code for info text for teachers
	 * 
	 * @param string $unfolded
	 * @param string $page
	 * @return string
	 */
	public static function get_info_text_for_teacher($unfolded = false, $page = "settings") {
		$s = '<p><a class="show">' . get_string ( 'info_header_teacher_' . $page, 'groupformation' ) . '</a></p>';
		$s .= '<div id="info_text" style="display: ' . (($unfolded) ? 'block' : 'none') . ';">';
		$s .= '<p style="padding-left: 10px;">' . get_string ( 'info_text_teacher_' . $page, 'groupformation' ) . '</p>';
		$s .= '</div>';
		$s .= self::get_js_for_info_text ();
		
		return $s;
	}
	
	/**
	 * Returns inline JS
	 * 
	 * @return string
	 */
	private static function get_js_for_info_text() {
		$s = "";
		$s .= '<script type="text/javascript">';
		$s .= '		$(function() {';
		$s .= '			$(\'.show\').click(function() {';
		$s .= '				$(\'#info_text\').slideToggle();';
		$s .= '	    	});';
		$s .= '		});';
		$s .= '</script>';
		return $s;
	}
	
	/**
	 * Returns html code for info text for students
	 * 
	 * @param string $unfolded
	 * @param int $groupformationid
	 * @param string $role
	 * @return string
	 */
	public static function get_info_text_for_student($unfolded = false, $groupformationid = null, $role = "student") {
		if (is_null ( $groupformationid )) {
			return "";
		}
		$store = new mod_groupformation_storage_manager ( $groupformationid );
		
		$scenario_name = get_string ( 'scenario_' . $store->get_scenario ( true ), 'groupformation' );
		$a = new stdClass ();
		$a->scenario_name = $scenario_name;
		
		$s = '<p><a class="show">' . get_string ( 'info_header_' . $role, 'groupformation' ) . '</a></p>';
		$s .= '<div id="info_text" style="display: ' . (($unfolded) ? 'block' : 'none') . ';">';
		$s .= '<p style="padding-left: 10px;">' . get_string ( 'info_text_' . $role, 'groupformation', $a ) . '</p>';
		$s .= '</div>';
		$s .= self::get_js_for_info_text ();
		
		return $s;
	}
	
	/**
	 * Returns user record
	 * 
	 * @param int $userid
	 * @return stdClass|null
	 */
	public static function get_user_record($userid){
		global $DB;
		if ($DB->record_exists ( 'user', array (
				'id' => $userid
		) )) {
			return $DB->get_record ( 'user', array (
					'id' => $userid
			) );
		}
		return null;
	}
	

	/**
	 * computes stats about answered and misssing questions
	 *
	 * @return multitype:multitype:number stats
	 */
	public static function get_stats($groupformationid, $userid) {
		$user_manager = new mod_groupformation_user_manager($groupformationid);
		$store = new mod_groupformation_storage_manager($groupformationid);
		
		$category_set = $store->get_categories ();
	
		$categories = array ();
	
		foreach ( $category_set as $category ) {
			$categories [$category] = $store->get_number ( $category );
		}
	
		$stats = array ();
		foreach ( $categories as $category => $value ) {
			$count = $user_manager->get_number_of_answers ( $userid, $category );
			$stats [$category] = array (
					'questions' => $value,
					'answered' => $count,
					'missing' => $value - $count
			);
		}
		return $stats;
	}
	
	/**
	 * Returns stats about answered questionnaires
	 * 
	 * @param unknown $groupformationid
	 * @return multitype:number
	 */
	public static function get_infos($groupformationid) {
		$user_manager = new mod_groupformation_user_manager ( $groupformationid );
		$store = new mod_groupformation_storage_manager ( $groupformationid );
	
		$total_answer_count = $store->get_total_number_of_answers ();
	
		$stats = array ();
	
		$context = groupformation_get_context ( $groupformationid );
		$students = get_enrolled_users ( $context, 'mod/groupformation:onlystudent' );
		$student_count = count ( $students );
	
		$stats [] = $student_count;
	
		$started = $user_manager->get_started ();
		$started_count = count ( $started );
	
		$stats [] = $started_count;
	
		$completed = $user_manager->get_completed ();
		$completed_count = count ( $completed );
	
		$stats [] = $completed_count;
	
		$no_missing_answers = $user_manager->get_completed_by_answer_count ();
		$no_missing_answers_count = count ( $no_missing_answers );
	
		$stats [] = $no_missing_answers_count;
	
		$missing_answers = $user_manager->get_not_completed_but_submitted ();
		$missing_answers_count = count ( $missing_answers );
	
		$stats [] = $missing_answers_count;
	
		return $stats;
	}
	
	/**
	 * Converts OPTIONS xml to array
	 * 
	 * @param unknown $xml_content
	 * @return multitype:string
	 */
	public static function xml_to_array($xml_content){
		$xml = simplexml_load_string($xml_content);
		$optionArray = array();
		foreach ($xml->OPTION as $option){
			$optionArray[] = trim($option);
		}
		return $optionArray;
	}
	
}