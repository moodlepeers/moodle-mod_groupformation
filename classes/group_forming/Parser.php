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
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/lib/groupal/classes/Criteria/SpecificCriterion.php');
require_once($CFG->dirroot.'/lib/groupal/classes/Participant.php');

class lib_groupal_Parser {
	public static function parse($infos, $labels, $groupsize){
		var_dump($infos);
		$array = array();
		foreach($infos as $user){
			$position = 0;
			$participant;
			foreach($labels as $label){
				$value = $user->$label;
				$count = count($value);
				$homogen = $value[$count-1];
				// an letzter Stelle im Array wird übergeben, ob es homogen ist
				unset($value[$count-1]);
				$c = new SpecificCriterion($label, $value, 0.0, 1.0, $homogen, 1.0);
// 				$c = new Criterion();
// 				$c->setName($label);
// 				$c->setValues($user->$label);
// 				$c->setIsHomogeneous($user->homogen);
				if($position == 0){
					$participant = new Participant($c, $user->id);
				}else{
					$participant->addCriteria($c);
				}
			}
			
			$array[] = $participant;
		}
		
		//An den GroupFormationAlagorithm übergeben/starten
	}
}