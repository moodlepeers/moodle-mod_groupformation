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
 
			$mform->addElement('header', 'editparam', get_string('editparam', 'groupformation'));
				
			$mform->addElement('static', 'szenarioInfo', get_string('szenarioLabel', 'groupformation'), get_string('szenarioInfo', 'groupformation'));
			
			$attribut = array('project' => get_string('project', 'groupformation'),
					'homework' => get_string('homework', 'groupformation'),
					'presentation' => get_string('presentation', 'groupformation')
			);
			
			$mform->addElement('select', 'szenario', get_string('szenario', 'groupformation'), $attribut);
			
			$mform->addElement('static', 'hintTopic', get_string('topicchoice', 'groupformation'), get_string('useOneLineForEachTopic', 'groupformation'));
			$mform->addElement('textarea', 'topicValues', '', 'wrap="virtual" rows="10" cols="65"');
				
			$mform->addElement('static', 'hintKnowledge', get_string('knowledgeChoice', 'groupformation'), get_string('useOneLineForEachKnowledge', 'groupformation'));
			$mform->addElement('textarea', 'knowledgeValues', '', 'wrap="virtual" rows="10" cols="65"');
			
			$this->add_action_buttons();
			}
	}