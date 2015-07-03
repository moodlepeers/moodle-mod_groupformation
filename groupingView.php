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
 * @author Nora Wester,
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

	require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . '/config.php');
	require_once (dirname ( __FILE__ ) . '/lib.php');
	require_once (dirname ( __FILE__ ) . '/locallib.php');
	require_once ($CFG->dirroot . '/mod/groupformation/classes/group_forming/startGrouping.php');
	
	// Read URL params
	$id = optional_param ( 'id', 0, PARAM_INT ); // Course Module ID
// 	$g = optional_param ( 'g', 0, PARAM_INT ); // groupformation instance ID
	$do_show = optional_param('do_show', 'grouping', PARAM_TEXT);
	
	// Import jQuery and js file
	groupformation_add_jquery ( $PAGE, 'survey_functions.js' );
	
	// Determine instances of course module, course, groupformation
	groupformation_determine_instance($id, $cm, $course, $groupformation);
	
	// Require user login if not already logged in
	require_login ( $course, true, $cm );
	
	// Get useful stuff
	$context = $PAGE->context;
	$userid = $USER->id;
	
	if (!has_capability('mod/groupformation:editsettings', $context)){
		$returnurl = new moodle_url('/mod/groupformation/view.php', array('id' => $id, 'do_show' => 'view'));
		redirect($returnurl);
	}else{
		$current_tab = $do_show;
	}

    // Get data for HTML output
    require_once (dirname ( __FILE__ ) . '/classes/moodle_interface/storage_manager.php');
    require_once (dirname ( __FILE__ ) . '/classes/group_forming/groupingViewController.php');
    $store = new mod_groupformation_storage_manager($groupformation->id);

	
	$s = 0;
	if(isset($_POST["starting"])){
		$s = $_POST['starting'];
        // $store->something($_POST['starting']);
	}

    // set data and viewStatus of groupingView, after possible db update
    $controller = new mod_groupformation_GroupingViewController($groupformation->id);
	
	// Log access to page
	groupformation_log($USER->id,$groupformation->id,'<view_teacher_grouping>');
	
	// Trigger event TODO @Nora why?
	groupformation_trigger_event($cm, $course, $groupformation, $context);
	
	// Set PAGE config
	$PAGE->set_url ( '/mod/groupformation/groupingView.php', array ('id' => $cm->id, 'do_show' => $do_show ) );
	$PAGE->set_title ( format_string ( $groupformation->name ) );
	$PAGE->set_heading ( format_string ( $course->fullname ) );
	
	echo $OUTPUT->header ();
	
	// Print the tabs.
	require ('tabs.php');
	
	// Conditions to show the intro can change to look for own settings or whatever.
	if ($groupformation->intro) {
		echo $OUTPUT->box ( format_module_intro ( 'groupformation', $groupformation, $cm->id ), 'generalbox mod_introbox', 'groupformationintro' );
	}
	
	var_dump($s);
	if($s == 1){
		mod_groupformation_startGrouping::start($groupformation->id);
	}
	// Replace the following lines with you own code.
	//echo $OUTPUT->heading ( $groupformation->name );
	
	require_once (dirname ( __FILE__ ) . '/classes/group_forming/submit_infos.php');
	$infos = new mod_groupformation_submit_infos ( $groupformation->id );
	$surveyStatisticNumers = $infos->getInfos ();
	
	echo '<div style="color:red;">Diese Seite ist noch in der Entwicklung. Die Inhalte sind ggf. noch rein statisch und haben keinen Effekt oder keine Funktion</div>';
	
	echo '<form action="' . htmlspecialchars ( $_SERVER ["PHP_SELF"] ) . '" method="post" autocomplete="off">';
	
	echo '<input type="hidden" name="id" value="' . $id . '"/>';
	
	echo '
	<div class="gf_settings_pad">';

    $controller->displaySettings();

/*    echo'
		<div class="gf_pad_header">
		Gruppenbildung
		</div>
		<div class="gf_pad_content bp_align_left-middle">
			<button type="submit" name="starting" value="1" class="gf_button gf_button_pill gf_button_small">Gruppen bilden</button>
			<button class="gf_button gf_button_pill gf_button_small" disabled>Gruppenbildung stoppen</button>
			<button class="gf_button gf_button_pill gf_button_small" >Gruppen l&ouml;schen</button>
			<p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
		</div>';*/
	
	echo '</form>';


    $controller->displayAnalysis();
    $controller->displayUncompleteGroups();
    $controller->displayGroups();




/*	echo  '	<div class="gf_pad_header_small">
			Auswertung
		</div>
		<div class="gf_pad_content">
			<p>Gleichm&auml;&szlig;igkeit der Gruppen: <b>0.7</b><span class="toolt" tooltip="ein Wert > 0.5 ist gut"></span></p>
			<p>Anzahl gebildeter Gruppen: <b>100</b></p>
			<p>Maximale Gruppengr&ouml;&szlig;e: <b>6</b></p>
		</div>
		<div class="gf_pad_header_small">Maximale Gruppengr&ouml;&szlig;e wurde bei folgenden Gruppen nicht erreicht: </div>
		<div class="gf_pad_content">
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_16 - Anzahl Mitglieder: <b>3</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_18 - Anzahl Mitglieder: <b>3</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_25 - Anzahl Mitglieder: <b>1</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_36 - Anzahl Mitglieder: <b>2</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_99 - Anzahl Mitglieder: <b>3</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
		</div>
		<div class="gf_pad_header_small">
			&Uuml;bersicht gebildeter Gruppen
		</div>
		<div class="gf_pad_content">
			<div class="grid bottom_stripe">
				<div class="col_s_50">Name: <b>Gruppennamen_1</b></div>
				<div class="col_s_25">Gruppenqualit&auml;t: <b>0.63</b></div>
				<div class="col_s_25 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">zur Moodle Gruppenansicht</button></div>
				<div class="col_s_100 gf_group_links"><a href="#">Max Musterman</a><a href="#">Peter Lustig</a><a href="#">Cho Ngueng</a><a href="#">Mustafa Ghaffar</a><a href="#">Olivia Johnson</a><a href="#">Jurgen Ehrlich</a></div>
			</div>
		
			<div class="grid bottom_stripe">
				<div class="col_s_50">Name: <b>Gruppennamen_2</b></div>
				<div class="col_s_25">Gruppenqualit&auml;t: <b>0.63</b></div>
				<div class="col_s_25 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">zur Moodle Gruppenansicht</button></div>
				<div class="col_s_100 gf_group_links"><a href="#">Max Musterman</a><a href="#">Peter Lustig</a><a href="#">Cho Ngueng</a><a href="#">Mustafa Ghaffar</a><a href="#">Olivia Johnson</a><a href="#">Jurgen Ehrlich</a></div>
			</div>
		
			<div class="grid bottom_stripe">
				<div class="col_s_50">Name: <b>Gruppennamen_3</b></div>
				<div class="col_s_25">Gruppenqualit&auml;t: <b>0.63</b></div>
				<div class="col_s_25 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">zur Moodle Gruppenansicht</button></div>
				<div class="col_s_100 gf_group_links"><a href="#">Max Musterman</a><a href="#">Peter Lustig</a><a href="#">Cho Ngueng</a><a href="#">Mustafa Ghaffar</a><a href="#">Olivia Johnson</a><a href="#">Jurgen Ehrlich</a></div>
			</div>
		
			<div class="grid bottom_stripe">
				<div class="col_s_50">Name: <b>Gruppennamen_4</b></div>
				<div class="col_s_25">Gruppenqualit&auml;t: <b>0.63</b></div>
				<div class="col_s_25 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">zur Moodle Gruppenansicht</button></div>
				<div class="col_s_100 gf_group_links"><a href="#">Max Musterman</a><a href="#">Peter Lustig</a><a href="#">Cho Ngueng</a><a href="#">Mustafa Ghaffar</a><a href="#">Olivia Johnson</a><a href="#">Jurgen Ehrlich</a></div>
			</div>
		</div>';*/


	echo '</div>';
	
	echo $OUTPUT->footer ();
