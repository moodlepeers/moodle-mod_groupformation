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
			1 =>'projectteams',
			2 =>'homeworkgroups',
			3 =>'presentationgroups' 
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
	// provides the minimum values each "value" can have in such a vector
	private $MINVAL_SETS = array (
			'1' => array (
					'topic' => 0.0,
					'knowledge_heterogen' => 0.0,
					'knowledge_homogen' => 0.0,
					'grade' => 1.0,
					'big5_heterogen' => 2.0,
					'big5_homogen' => 2.0,
					'team' => 1.0,
					'fam' => 4.0
			),
			'2' => array (
					'topic' => 0.0,
					'knowledge_heterogen' => 0.0,
					'grade' => 0.0, // TOO @Eduard: Egal ob Noten, Punkte etc. die Skala sollte immer zw. 0 und 1 abspeichern!
					'big5_heterogen' => 2.0,
					'big5_homogen' => 2.0,
					'team' => 1.0,
					'learning' => 6.0
			),
			'3' => array (
					'topic' => 0.0
			)
	);
	// provides the max values each of the "values" in a vector can have
	private $MAXVAL_SETS = array (
			'1' => array (
					'topic' => 100, // XXX this is hard to say...
					'knowledge_heterogen' => 100.0,  // TODO: @Eduard: check that the slider encodes to 0...100. If 0.. 1.0 then set here 1.0
					'knowledge_homogen' => 100.0,  // and here as well.
					'grade' => 1.0, // TODO @Eduard: Egal ob Noten, Punkte etc. die Skala sollte immer zw. 0 und 1 abspeichern!
					'big5_heterogen' => 18.0, // FIXME This is crap, as big5 has value areas from 2 to 12 and "verträglichkeit" has 3 to 18. needs normation!
					'big5_homogen' => 18.0,
					'team' => 6.0,
					'fam' => 30.0 // FIXME: @Nora: FAM sollte jede der vier Variablen die berechnet werden auf das Interval 0..1 normiert werde (dann hier und bei minVal aktualisieren), denn: einige berechnen sich aus 4 variablen (min max ist dann 4 bis 24) und einige aus 5 (min max 5 bis 30). Das ist nicht gut. Sollte einheitliche min max haben (bspw. normiert auf 0...1 Interval)
			),
			'2' => array (
					'topic' => 100.0,
					'knowledge_heterogen' => 100.0,
					'grade' => 1.0,
					'big5_heterogen' => 18.0,
					'big5_homogen' => 18.0,
					'team' => 6.0,
					'learning' => 36.0
			),
			'3' => array (
					'topic' => 100.0
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
	
	private $question_types = array (
		'motivation' => 'radio',
		'character' => 'radio',
		'learning' => 'radio',
		'sellmo' => 'radio',
		'srl' => 'radio',
		'topic' => 'sortable',
		'knowledge' => 'range',
		'grade' => 'dropdown',
		'general' => 'dropdown',
		'self' => 'radio',
	);
	
	/**
	 * Returns scenario name
	 * 
	 * @param int $scenario
	 * @return string
	 */
	public function get_scenario_name($scenario){
		if ($scenario>=1 && $scenario<=3)
			return $this->SCENARIO_NAMES[$scenario];
		else
			return $this->SCENARIO_NAMES[1];
	}
	
	/**
	 * Returns question types
	 * 
	 * @deprecated not uses til now
	 * @return multitype:string
	 */
	public function get_question_types(){
		return $this->question_types;
	}
	
	/**
	 * Returns names of categories
	 * 
	 * @return multitype:string
	 */
	public function getNames() {
		return $this->CATEGORY_NAMES;
	}

	/**
	 * Returns extra labels for criteria like fam, learning, big5_xxx
	 * 
	 * @param unknown $label
	 * @param string $scenario
	 * @return multitype:string
	 */
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
	
	/**
	 * Returns category set
	 * 
	 * @param int $scenario
	 * @param string $groupformationid
	 * @return multitype:multitype:string
	 */
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
	
	/**
	 * Returns label set
	 * 
	 * @param int $scenario
	 * @param string $groupformationid
	 * @return multitype:multitype:string
	 */
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
	
	/**
	 * Returns criterion set
	 * 
	 * @param unknown $scenario
	 * @param string $groupformationid
	 * @return multitype:multitype:string
	 */
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
	
	/**
	 * Returns homogen criteria set
	 * 
	 * @param unknown $scenario
	 * @return multitype:multitype:boolean
	 */
	public function getHomogenSet($scenario) {
		return $this->HOMOGEN_SETS[$scenario];
	}
	
	/**
	 * Returns min val set
	 * 
	 * @param unknown $scenario
	 * @return multitype:multitype:number
	 */
	public function getMinValSet($scenario) {
		return $this->MINVAL_SETS[$scenario];
	}
	
	/**
	 * Returns max val set
	 * 
	 * @param unknown $scenario
	 * @return multitype:multitype:number
	 */
	public function getMaxValSet($scenario) {
		return $this->MAXVAL_SETS[$scenario];
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


