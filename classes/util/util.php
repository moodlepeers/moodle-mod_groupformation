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
		
		$scenario_name = get_string ( 'scenario_' . $store->getScenario ( true ), 'groupformation' );
		$a = new stdClass ();
		$a->scenario_name = $scenario_name;
		
		$s = '<p><a class="show">' . get_string ( 'info_header_' . $role, 'groupformation' ) . '</a></p>';
		$s .= '<div id="info_text" style="display: ' . (($unfolded) ? 'block' : 'none') . ';">';
		$s .= '<p style="padding-left: 10px;">' . get_string ( 'info_text_' . $role, 'groupformation', $a ) . '</p>';
		$s .= '</div>';
		$s .= self::get_js_for_info_text ();
		
		return $s;
	}
}