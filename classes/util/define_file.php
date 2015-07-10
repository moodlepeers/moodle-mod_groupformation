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
 * define something
 *
 * @package mod_groupformation
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// define('CATEGORY_NAMES', array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation'));
// define('MOTIVATION', 7);
// define('TEAM', 4);
// define('LEARNING', 6);
// define('CHARACTER', 5);
// define('GENERAL', 2);
// define('KNOWLEDGE', 1);
// define('TOPIC', 0);
// define('GRADE', 3);
if (! defined ( 'MOODLE_INTERNAL' )) {
	die ( 'Direct access to this script is forbidden.' ); // / It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
class mod_groupformation_data {
	private $SCENARIO_NAMES = array (
			'project',
			'homework',
			'presentation' 
	);
	// NORMAL
	// private $CATEGORY_NAMES = array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation');
	// private $CRITERION_CATEGORYS = array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation');
	// private $LABELS = array('userid', 'lang', 'topic', 'knowledge_heterogen', 'knowledge_homogen', 'grade', 'big5', 'team', 'fam', 'learning');
	// private $CATEGORY_SETS = array (
	// '1' => array (
	// 'topic',
	// 'knowledge',
	// 'general',
	// 'grade',
	// 'team',
	// 'character',
	// 'motivation'
	// ),
	// '2' => array (
	// 'topic',
	// 'knowledge',
	// 'general',
	// 'grade',
	// 'team',
	// 'character',
	// 'learning'
	// ),
	// '3' => array (
	// 'topic',
	// 'knowledge',
	// 'general',
	// 'grade',
	// 'character',
	// 'motivation',
	// )
	// );
	
	// MATHEVORKURS
	private $CATEGORY_NAMES = array (
			'topic',
			'knowledge',
			'grade',
			'team',
			'character',
			'learning',
			'motivation',
			'sellmo',
			'self',
			'srl' 
	);
	private $CRITERION_CATEGORYS = array (
			'topic',
			'knowledge',
			'grade',
			'team',
			'character',
			'learning',
			'motivation' 
	);
	private $LABELS = array (
			
			'topic',
			'knowledge_heterogen',
			'knowledge_homogen',
			'grade',
			'big5_heterogen',
			'big5_homogen',
			'team',
			'fam',
			'learning' 
	);
	private $LABEL_SETS = array (
			'1' => array (
					'topic',
					'knowledge_heterogen',
					'knowledge_homogen',
					'grade',
					'big5_heterogen',
					'big5_homogen',
					'team',
					'fam' 
			),
			'2' => array (
					'topic',
					'knowledge_heterogen',
					'grade',
					'big5_heterogen',
					'big5_homogen',
					'team',
					'learning' 
			),
			'3' => array (
					'topic' 
			) 
	);
	private $HOMOGEN_SETS = array (
			'1' => array (
					'topic' => true,
					'knowledge_heterogen' => false,
					'knowledge_homogen' => true,
					'grade' => true,
					'big5_heterogen' => false,
					'big5_homogen' => true,
					'team' => true,
					'fam' => true
			),
			'2' => array (
					'topic' => true,
					'knowledge_heterogen' => false,
					'grade' => false,
					'big5_heterogen' => false,
					'big5_homogen' => true,
					'team' => true,
					'learning' => false 
			),
			'3' => array (
					'topic' => true
			)
	);
	private $Big5HomogenExtra_LABEL = array (
			'Gewissenhaftigkeit',
			'Vertraeglichkeit' 
	);
	private $Big5HeterogenExtra_LABEL = array (
			'Extraversion',
			'Neurotizismus',
			'Offenheit' 
	);
	private $FamExtra_LABEL = array (
			'Herausforderung',
			'Interesse',
			'Erfolg',
			'Misserfolg' 
	);
	private $LearnExtra_LABEL = array (
			'KE',
			'AE',
			'RB',
			'AB' 
	);
	private $CATEGORY_SETS = array (
			'1' => array (
					'topic',
					'knowledge',
					'grade',
					'team',
					'character',
					'motivation',
					'sellmo',
					'self',
					'srl' 
			),
			'2' => array (
					'topic',
					'knowledge',
					'grade',
					'team',
					'character',
					'learning',
					'sellmo',
					'self',
					'srl' 
			),
			'3' => array (
					'topic',
					'sellmo',
					'self',
					'srl' 
			) 
	);
	private $CRITERION_SETS = array (
			'1' => array (
					'topic',
					'knowledge',
					'grade',
					'team',
					'character',
					'motivation',
					'sellmo',
					'self',
					'srl' 
			),
			'2' => array (
					'topic',
					'knowledge',
					'grade',
					'team',
					'character',
					'learning',
					'sellmo',
					'self',
					'srl'
			),
			'3' => array (
					'topic',
					'sellmo',
					'self',
					'srl'
			) 
	);
	const MOTIVATION = 6;
	const TEAM = 3;
	const LEARNING = 5;
	const CHARACTER = 4;
	// const GENERAL = 2;
	const KNOWLEDGE = 1;
	const TOPIC = 0;
	const GRADE = 2;
	// const MOTIVATION = 7;
	// const TEAM = 4;
	// const LEARNING = 6;
	// const CHARACTER = 5;
	// const GENERAL = 2;
	// const KNOWLEDGE = 1;
	// const TOPIC = 0;
	// const GRADE = 3;
	private $job_status_options = array (
			'ready' => '0000',
			'waiting' => '1000',
			'started' => '0100',
			'aborted' => '0010',
			'done' => '0001' 
	);
	public function __construct() {
	}
	public function getNames() {
		return $this->CATEGORY_NAMES;
	}
	public function getLangNumber($lang) {
		$p = 0;
		if ($lang == 'en') {
			$p = 1;
		}
		
		return $p;
	}
	public function getLabels() {
		return $this->LABELS;
	}
	public function getCriterionNames() {
		return $this->CRITERION_CATEGORYS;
	}
	public function getExtraLabel($label, $scenario = null) {
		if ($label == 'fam') {
			return $this->FamExtra_LABEL;
		}
		
		if ($label == 'learning') {
			return $this->LearnExtra_LABEL;
		}
		
		if ($label == 'big5_homogen') {
			return $this->Big5HomogenExtra_LABEL;
		}
		
		if ($label == 'big5_heterogen') {
			return $this->Big5HeterogenExtra_LABEL;
		}
	}
	public function getPositions($category, $scenario, $groupformationid = null) {
		$array = $this->getCategorySet ( $scenario, $groupformationid );
		$position = 0;
		foreach ( $array as $c ) {
			if ($category == $c) {
				return $position;
			}
			$position ++;
		}
	}
	public static function getPosition($category) {
		if ($category == 'topic') {
			return self::TOPIC;
		}
		
		if ($category == 'team') {
			return self::TEAM;
		}
		
		if ($category == 'motivation') {
			return self::MOTIVATION;
		}
		
		if ($category == 'learning') {
			return self::LEARNING;
		}
		
		if ($category == 'knowledge') {
			return self::KNOWLEDGE;
		}
		
		if ($category == 'grade') {
			return self::GRADE;
		}
		
		if ($category == 'general') {
			return self::GENERAL;
		}
		
		if ($category == 'character') {
			return self::CHARACTER;
		}
	}
	public function getCategorySet($scenario, $groupformationid = null) {
		$array = $this->CATEGORY_SETS [$scenario];
		if ($groupformationid != null) {
			$store = new mod_groupformation_storage_manager ( $groupformationid );
			if ($store->hasGrades () != 1) {
				$position = 0;
				foreach ( $array as $c ) {
					if ('grade' == $c) {
						unset ( $array [$position] );
						return $array;
					}
					$position ++;
				}
			}
		}
		return $array;
	}
	public function getLabelSet($scenario, $groupformationid = null){
		$array = $this->LABEL_SETS [$scenario];
		if ( $groupformationid != null){
			$store = new mod_groupformation_storage_manager($groupformationid);
			$hasTopic = $store->getNumber("topic");
			$hasKnowledge = $store->getNumber("knowledge");
			$grades = $store->askForGrade();
			
				$position = 0;
				foreach($array as $c){
					if(('grade' == $c && $grades == false) || ($hasTopic == 0 && 'topic' == $c) || ($hasKnowledge == 0 && ('knowledge_heterogen' == $c || 'knowledge_homogen' == $c))){
						unset($array[$position]);
					}
					
					$position++;	
				}
			
		}
		return $array;
	}
	
	public function getCriterionSet($scenario, $groupformationid = null){
		$array = $this->CRITERION_SETS [$scenario];
		if ( $groupformationid != null){
			$store = new mod_groupformation_storage_manager($groupformationid);
			$hasTopic = $store->getNumber("topic");
			$hasKnowledge = $store->getNumber("knowledge");
			$grades = $store->askForGrade();
			
				$position = 0;
				foreach($array as $c){
					if(('grade' == $c && $grades == false) || ($hasTopic == 0 && 'topic' == $c) || ($hasKnowledge == 0 && ('knowledge_heterogen' == $c || 'knowledge_homogen' == $c))){
						unset($array[$position]);
					}
					
					$position++;	
				}
				
				
			return $array;
		}
		return $array;
	}
	public function getHomogenSet($scenario) {
		return $this->HOMOGEN_SETS [$scenario];
	}
	/**
	 * Return job status options
	 * 
	 * @return multitype:string
	 */
	public function get_job_status_options() {
		return $this->job_status_options;
	}
}	


