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
 * The main groupformation configuration form
 *
 * @package mod_groupformation
 * @copyright 2014 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

	//defined('MOODLE_INTERNAL') || die();  -> template

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	require_once($CFG->dirroot.'/course/moodleform_mod.php');
	require_once($CFG->dirroot.'/mod/groupformation/lib.php');  // not in the template

	class mod_groupformation_mod_form extends moodleform_mod {

		function definition() {
	//		global $CFG, $DB, $OUTPUT;  

			$mform =& $this->_form;
		
			// Adding the "general" fieldset, where all the common settings are showed.
			$mform->addElement('header', 'general', get_string('general', 'form'));
			
			// Adding the standard "name" field.
			$mform->addElement('text', 'name', get_string('groupformationname', 'groupformation'), array('size' => '64'));
			if (!empty($CFG->formatstringstriptags)) {
				$mform->setType('name', PARAM_TEXT);
			} else {
				$mform->setType('name', PARAM_CLEAN);
			}
			
			$mform->addRule('name', null, 'required', null, 'client');
			$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
			$mform->addHelpButton('name', 'groupformationname', 'groupformation');
			
			// Adding the standard "intro" and "introformat" fields.
			$this->add_intro_editor();
			
// 			// Adding the rest of groupformation settings, spreeading all them into this fieldset
// 			// ... or adding more fieldsets ('header' elements) if needed for better logic.
// 			$mform->addElement('static', 'label1', 'groupformationsetting1', 'Your newmodule fields go here. Replace me!');
// 			$mform->addElement('header', 'groupformationfieldset', get_string('groupformationfieldset', 'groupformation'));
// 			$mform->addElement('static', 'label2', 'groupformationsetting2', 'Your newmodule fields go here. Replace me!');
			
			$mform->addElement('header', 'time', get_string('time', 'groupformation'));
			
			$mform->addElement('date_time_selector', 'timeopen', get_string('from'));
			$mform->addElement('date_time_selector', 'timeclose', get_string('to'));
			
			// Add standard grading elements.
			$this->standard_grading_coursemodule_elements();
			
			// Add standard elements, common to all modules.
			$this->standard_coursemodule_elements();
			
			// Add standard buttons, common to all modules.
			$this->add_action_buttons();
		
	}
}

