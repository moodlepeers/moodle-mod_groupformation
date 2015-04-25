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
 * Prints a particular instance of groupformation
 *
 * @package mod_groupformation
 * @author  Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

	//TODO Noch nicht getestet
	require_once(dirname(__FILE__).'/userid_filter.php');

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}

	class mod_groupformation_submit_infos {
		
		private $groupformationid;
		private $userid_filter;
		
		public function __construct($groupformationid){
			$this->groupformationid = $groupformationid;
			$this->userid_filter = new mod_groupformation_userid_filter($groupformationid);
		}
		
		public function getInfos(){
			$numbers = $this->userid_filter->getNumbersOfAnswerStatus();
			var_dump('Es haben ' . $numbers[0] . ' Studenten den Fragebogen bearbeitet');
			var_dump('Davon haben ' . $numbers[1] . ' ihre Antworten schon fest abgegeben');
			$commitedNoneComplete = $this->userid_filter->getNumberOfCommitedNoneCompleted();
			var_dump('Von den fest abgegebenen Antworten sind ' . $commitedNoneComplete . ' nicht vollständig');
			$generalCompleted = $this->userid_filter->getNumberOfCompleted();
			var_dump('Generel gibt es ' . $generalCompleted . 'vollständig beantwortete Fragebögen');
		}
		
	}