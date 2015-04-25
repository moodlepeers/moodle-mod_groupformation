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

	define('CATEGORY_NAMES', array('topic', 'knowledge', 'general', 'grade','team', 'character', 'learning', 'motivation'));
	define('MOTIVATION', 7);
	define('TEAM', 4);
	define('LEARNING', 6);
	define('CHARACTER', 5);
	define('GENERAL', 2);
	define('KNOWLEDGE', 1);
	define('TOPIC', 0);
	define('GRADE', 3);
	

	if (!defined('MOODLE_INTERNAL')) {
		die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
	}
