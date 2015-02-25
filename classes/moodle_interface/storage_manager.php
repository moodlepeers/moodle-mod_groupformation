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
 * @copyright 2015 Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die();  -> template

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}
	
	class mod_groupformation_storage_manager {

		private $groupformationid;
		
		public function __construct($groupformationid){
			$this->groupformationid = $groupformationid;
		}
		
		public function add_question($question){
			global $CFG, $DB;
			
			$data = new stdClass();
			$data->groupformation = $this->groupformationid;
			
			$data->type = $question->type;
			$data->page = $question->page;
			$data->question = $question->question;
			$data->category = $question->category;
			$data->options = $this->convertOptions($question->options);
			
			$data->id = $DB->insert_record('groupformation_question', $data);
		}
	
		public function add_settings($knowledge, $szenario, $topics, $number){
			global $DB;
			
			$data = new stdClass();
			$data->groupformation = $this->groupformationid;
			
			$data->szenario = $szenario;
			$data->questionnumber = $number;
			$data->topicvalues = $this->convertOptions($topics);
			$data->knowledgevalues = $this->convertOptions($knowledge);
			
			$data->id = $DB->insert_record("groupformation_question_settings", $data);
		}
		
		private function convertOptions($options){
			$op = implode("</OPTION> /n <OPTION>", $options);
			return "<OPTION>" . $op . "</OPTION>";
		}
	}