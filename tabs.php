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
 * prints the tabbed bar
 *
 * @author Nora Wester
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_groupformation
 *         
 */
defined ( 'MOODLE_INTERNAL' ) or die ( 'not allowed' );

$tabs = array ();
$row = array ();
$inactive = array ();
$activated = array ();

// some pages deliver the cmid instead the id
if (isset ( $cmid ) and intval ( $cmid ) and $cmid > 0) {
	$usedid = $cmid;
} else {
	$usedid = $id;
}

$context = context_module::instance ( $usedid );

$courseid = optional_param ( 'courseid', false, PARAM_INT );
// $current_tab = $SESSION->feedback->current_tab;
if (! isset ( $current_tab )) {
	$current_tab = '';
}

if (has_capability ( 'mod/groupformation:editsettings', $context )) {
	$analyseurl = new moodle_url ( '/mod/groupformation/analysisView.php', array (
			'id' => $usedid,
			'do_show' => 'analysis' 
	) );
	$row [] = new tabobject ( 'analysis', $analyseurl->out (), get_string ( 'tab_analysis', 'groupformation' ) );
	$groupingurl = new moodle_url ( '/mod/groupformation/groupingView.php', array (
			'id' => $usedid,
			'do_show' => 'grouping'
	) );
	$row [] = new tabobject ( 'grouping', $groupingurl->out (), get_string ( 'tab_grouping', 'groupformation' ) );
	$answeringViewiewurl = new moodle_url ( '/mod/groupformation/answeringView.php', array (
			'id' => $usedid
	) );
	$row [] = new tabobject ( 'view', $answeringViewiewurl->out (), get_string ( 'tab_preview', 'groupformation' ) );
}elseif (!has_capability ( 'mod/groupformation:editsettings', $context ) && has_capability('mod/groupformation:onlystudent', $context)){
	$viewurl = new moodle_url ( '/mod/groupformation/view.php', array (
		'id' => $usedid,
		'do_show' => 'view' 
	) );
	$row [] = new tabobject ( 'view', $viewurl->out (), get_string ( 'tab_questionaire', 'groupformation' ) );
	$evaluationurl = new moodle_url ( '/mod/groupformation/evaluationView.php', array (
			'id' => $usedid,
			'do_show' => 'evaluation'
	) );
	$row [] = new tabobject ( 'evaluation', $evaluationurl->out (), get_string ( 'tab_evaluation', 'groupformation' ) );
	$groupurl = new moodle_url ( '/mod/groupformation/groupView.php', array (
			'id' => $usedid,
			'do_show' => 'group'
	) );
	$row [] = new tabobject ( 'group', $groupurl->out (), get_string ( 'tab_group', 'groupformation' ) );
}

if (count ( $row ) > 1) {
	$tabs [] = $row;
	
	print_tabs ( $tabs, $current_tab, $inactive, $activated );
}

