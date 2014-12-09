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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * form for editing parameter for groupformation
*
* @package    mod_groupformation
* @copyright  Nora Wester
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	require_once("$CFG->libdir/formslib.php");

	class edit_param_form extends moodleform {

		//Add elements to form
		public function definition() {
			global $CFG;

			$mform =& $this->_form; // Don't forget the underscore!
 
			$mform->addElement('header', 'editparam', get_string('general'));
			
			$radiosimilararray = array();
			$radiosimilararray[] =& $mform->createElement('radio', 'similar', '', get_string('similar'), 1);
			$radiosimilararray[] =& $mform->createElement('radio', 'similar', '', get_string('different'), 0);
			//$mform->addGroup($radiosimilararray, 'similarar', '', array(' '), false);
			$similarAr = $mform->createElement('group', 'similarAr', get_string('label'), radiosimilararray, null, false);
			
			$radioimportancearray = array();
			$radioimportancearray[] =& $mform->createElement('radio', 'importance', '', get_string('high'), 2);
			$radioimportancearray[] =& $mform->createElement('radio', 'importance', '', get_string('middle'), 1);
			$radioimportancearray[] =& $mform->createElement('radio', 'importance', '', get_string('low'), 0);
			//$mform->addGroup($radioimportancearray, 'importancear', '', array(' '), false);
			$importanceAr = $mform->createElement('group', 'importanceAr', get_string('label'), radioimportancearray, null, false);
			
// 			$firstRowArray = array();
// 			$firstRowArray[] = &$mform->createElement('similarar', '', '');
// 			$firstRowArray[] = &$mform->createElement('import');

			$firstRowArray = $mform->createElement('group', 'firstRow', get_string('label'), array($similarAr, $importanceAr), null, false);
			
		//	$mform->addGroup($firstRowArray, 'firstRow', get_string('firstRow', 'groupformation'), ' ', false);
			
			$this->add_action_buttons(false, get_string('savechanges'));
		}
	}