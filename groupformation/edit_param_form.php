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
				
			$attribut = array('learningGroup' => get_string('learningGroup', 'groupformation'),
					'exam' => get_string('exam', 'groupformation'),
					'seminar' => get_string('seminar', 'groupformation')
			);
			
			$mform->addElement('select', 'szenario', 'Szenario', $attribut);
			$mform->addHelpButton("szenario", 'szenario', 'groupformation');
			
			$mform->addElement('static', 'parameter', 'Parameter');
			$mform->addHelpButton('parameter', 'parameter', 'groupformation');
			
			$mform->addElement('advcheckbox', 'lernstil', get_string('lernstil', 'groupformation'));
			
			$learningarray=array();
			$learningarray[] =& $mform->createElement('radio', 'yesnol', '', get_string('similar', 'groupformation'), 1);
			$learningarray[] =& $mform->createElement('radio', 'yesnol', '', get_string('different', 'groupformation'), 0);
			$learningarray[] =& $mform->createElement('text', 'textl', '', '');
			$learningarray[] =& $mform->createElement('static', 'percentl', '', 'percent');
			$mform->addGroup($learningarray, 'learning', '', array(' '), false);
			
			$mform->addElement('advcheckbox', 'motivation', get_string('motivation', 'groupformation'));
			
			$motivarray=array();
			$motivarray[] =& $mform->createElement('radio', 'yesnom', '', get_string('similar', 'groupformation'), 1);
			$motivarray[] =& $mform->createElement('radio', 'yesnom', '', get_string('different', 'groupformation'), 0);
			$motivarray[] =& $mform->createElement('text', 'textm', '', '');
			$motivarray[] =& $mform->createElement('static', 'percentm', '', 'percent');
			$mform->addGroup($motivarray, 'motivation', '', array(' '), false);
			
			$mform->addElement('advcheckbox', 'topics', get_string('topics', 'groupformation'));
				
			$mform->addElement('static', 'hintTopic', get_string('topicchoice', 'groupformation'), get_string('useOneLineForEachTopic', 'groupformation'));
			$mform->addElement('textarea', 'topicValues', '', 'wrap="virtual" rows="10" cols="65"');
				
			$mform->addElement('advcheckbox', 'similarKnowledge', get_string('similarKnowledge', 'groupformation'));
			
			$mform->addElement('static', 'hintSimilarKnowledge', get_string('similarKnowledgeChoice', 'groupformation'), get_string('useOneLineForEachKnowledge', 'groupformation'));
			$mform->addElement('textarea', 'similarKnowledgeValues', '', 'wrap="virtual" rows="10" cols="65"');
			
			$mform->addElement('advcheckbox', 'differentKnowledge', get_string('differentKnowledge', 'groupformation'));
			
			$mform->addElement('static', 'hintdifferentKnowledge', get_string('differentKnowledgeChoice', 'groupformation'), get_string('useOneLineForEachKnowledge', 'groupformation'));
			$mform->addElement('textarea', 'differentKnowledgeValues', '', 'wrap="virtual" rows="10" cols="65"');
			
			$mform->addElement('date_time_selector', 'assesstimestart', get_string('from'));
			$mform->addHelpButton('assesstimestart', 'assesstimestart', 'groupformation');
			$mform->addElement('date_time_selector', 'assesstimeend', get_string('to'));
			
			$this->add_action_buttons();
			}
	}