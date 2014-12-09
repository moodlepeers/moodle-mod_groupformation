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
 * Generating group form
 *
 * @package    mod_groupformation
 * @copyright  Nora Wester
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	//moodleform is defined in formslib.php
	require_once("$CFG->libdir/formslib.php");

	class generategroups_form extends moodleform {
	
		//Add elements to form
		public function definition() {
			global $CFG;

			$mform =& $this->_form; // Don't forget the underscore!
		
			//TODO userpergroup namingschema
			$mform->addElement('header', 'generategroup', get_string('general'));
			
			$mform->addElement('text', 'namingscheme', get_string('namingscheme', 'groupformation'));
			$mform->addHelpButton('namingscheme', 'namingscheme', 'groupformation');
			$mform->addRule('namingscheme', get_string('required'), 'required', null, 'client');
			$mform->setType('namingscheme', PARAM_TEXT);
			
			$mform->addElement('text', 'userpergroup', get_string('userpergroup', 'group'),'maxlength="4" size="4"');
			$mform->setType('userpergroup', PARAM_INT);
			$mform->addRule('userpergroup', null, 'numeric', null, 'client');
			$mform->addRule('userpergroup', get_string('required'), 'required', null, 'client');
			
			$buttonarray = array();
			//$buttonarray[] = &$mform->createElement('submit', 'preview', get_string('preview'));
			$buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
			$buttonarray[] = &$mform->createElement('cancel');
			$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
			$mform->closeHeaderBefore('buttonar');
				
		}
	}

