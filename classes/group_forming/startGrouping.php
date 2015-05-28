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

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once(dirname(__FILE__).'/userid_filter.php');
require_once(dirname(__FILE__).'/calculateCriterions.php');

class mod_groupformation_startGrouping{
	
	public static function start($groupformationID){
		echo 'Hier startet die Berechnung';
		$userFilter = new mod_groupformation_userid_filter($groupformationID);
		$users = $userFilter->getCompletedIds();
		$szenario = $userFilter->getSzenario();
		$calculator = new mod_groupformation_calculateCriterions($groupformationID);
		$gradeP = $calculator->getGradePosition();
		$array = array();
		foreach($users as $user){
			//Position 0 UserID
			$array[] = $user;
			//Position 1 Sprache
			$array[] = $calculator->getLang($user);
			//Position 2 Themen
			//TODO
			if($szenario != 3){
				//Position 3 Vorwissen
				//TODO abklären wie 
				
				//Position 4 Note
				$array[] = $calculator->getGrade($gradeP, $user);
				//Position 5 Persönlichkeit
				$array[] = $calculator->getBig5($user);
				//Position 6 Team
				//TODO
				//Position 7 Motivation bei Projekt /Lernstil bei Hausaufgaben
				if($szenario == 1){
					$array[] = $calculator->getFAM($user);
				}else{
					$array[] = $calculator->getLearn($user);
				}
			}
		}
		
		var_dump($array);
	}

}