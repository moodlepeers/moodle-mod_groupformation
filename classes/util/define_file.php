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
 * @author  Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 //	define('CATEGORY_NAMES', array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation'));
// 	define('MOTIVATION', 7);
// 	define('TEAM', 4);
// 	define('LEARNING', 6);
// 	define('CHARACTER', 5);
// 	define('GENERAL', 2);
// 	define('KNOWLEDGE', 1);
// 	define('TOPIC', 0);
// 	define('GRADE', 3);
	

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}
	
	class mod_groupformation_data{
		
		private $CATEGORY_NAMES = array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation');
		private $CRITERION_CATEGORYS = array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation');
		const MOTIVATION = 7;
		const TEAM = 4;
		const LEARNING = 6;
		const CHARACTER = 5;
		const GENERAL = 2;
		const KNOWLEDGE = 1;
		const TOPIC = 0;
		const GRADE = 3;
		
		public function __construct(){
		
		}
		
		public function getNames(){
			return $this->CATEGORY_NAMES;
		}
		
		public function getCriterionNames(){
			return $this->CRITERION_CATEGORYS;
		}
		
		public static function getPosition($category){
			if($category == 'topic'){
				return self::TOPIC;
			}
			
			if($category == 'team'){
				return self::TEAM;
			}
			
			if($category == 'motivation'){
				return self::MOTIVATION;
			}
			
			if($category == 'learning'){
				return self::LEARNING;
			}
			
			if($category == 'knowledge'){
				return self::KNOWLEDGE;
			}
			
			if($category == 'grade'){
				return self::GRADE;
			}
			
			if($category == 'general'){
				return self::GENERAL;
			}
			
			if($category == 'character'){
				return self::CHARACTER;
			}
		}
	}
