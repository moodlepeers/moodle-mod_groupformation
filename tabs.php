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
 * prints the tabbed bar
*
* @author Nora Wester
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
* @package mod_groupformation
*/
	defined('MOODLE_INTERNAL') OR die('not allowed');

	$tabs = array();
	$row  = array();
	$inactive = array();
	$activated = array();

	//some pages deliver the cmid instead the id
	if (isset($cmid) AND intval($cmid) AND $cmid > 0) {
		$usedid = $cmid;
	} else {
		$usedid = $id;
	}

	$context = context_module::instance($usedid);

	$courseid = optional_param('courseid', false, PARAM_INT);
	// $current_tab = $SESSION->feedback->current_tab;
	if (!isset($current_tab)) {
		$current_tab = '';
	}

	$viewurl = new moodle_url('/mod/groupformation/view.php', array('id'=>$usedid, 'do_show'=>'view'));
	$row[] = new tabobject('view', $viewurl->out(), get_string('overview', 'groupformation'));

//	if (has_capability('mod/groupformation:editparams', $context)) {
		$editurl = new moodle_url('/mod/groupformation/edit_param.php', array('id'=>$usedid, 'do_show'=>'edit_param'));
		$row[] = new tabobject('edit_param', $editurl->out(), get_string('edit_param', 'groupformation'));
//	}



	if (count($row) > 1) {
		$tabs[] = $row;

		print_tabs($tabs, $current_tab, $inactive, $activated);
	}

