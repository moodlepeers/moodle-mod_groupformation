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
	
	// Read URL params
	$id = optional_param ( 'id', 0, PARAM_INT ); // Course Module ID
// 	$g = optional_param ( 'g', 0, PARAM_INT ); // groupformation instance ID
	$do_show = optional_param('do_show', 'analysis', PARAM_TEXT);
	
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

	// Log access to page
	groupformation_log($USER->id,$groupformation->id,'<view_teacher_overview>');
	
	// Trigger event TODO @Nora why?
	// groupformation_trigger_event($cm, $course, $groupformation, $context);
	
	// Set PAGE config
	$PAGE->set_url ( '/mod/groupformation/analysisView.php', array ('id' => $cm->id, 'do_show' => $do_show ) );
	$PAGE->set_title ( format_string ( $groupformation->name ) );
	$PAGE->set_heading ( format_string ( $course->fullname ) );

	echo $OUTPUT->header ();

	// Print the tabs.
	require ('tabs.php');

	// Conditions to show the intro can change to look for own settings or whatever.
	if ($groupformation->intro) {
		echo $OUTPUT->box ( format_module_intro ( 'groupformation', $groupformation, $cm->id ), 'generalbox mod_introbox', 'groupformationintro' );
	}
	
	// Replace the following lines with you own code.
	//echo $OUTPUT->heading ( $groupformation->name );
	
	// ---------------------------------------------
// 	require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');
	
	$job = mod_groupformation_job_manager::get_next_job();
	
	if (!is_null($job)){
	var_dump($job);
	
	var_dump(mod_groupformation_job_manager::is_job_aborted($job));
	
	$result = mod_groupformation_job_manager::do_groupal($job);
	
	var_dump($result);
	
	var_dump(mod_groupformation_job_manager::save_result($job, $result));
	
	var_dump(mod_groupformation_job_manager::get_status($job));
	}
	//-----------------------------------------------
	
	require_once (dirname ( __FILE__ ) . '/classes/group_forming/submit_infos.php');
	$infos = new mod_groupformation_submit_infos ( $groupformation->id );
	$surveyStatisticNumers = $infos->getInfos ();
	
	echo '<div style="color:red;">Diese Seite ist noch in der Entwicklung. Die Inhalte sind ggf. noch rein statisch und haben keinen Effekt oder keine Funktion</div>';
	
	echo '
				<div class="gf_settings_pad">
                    <div class="gf_pad_header">Groupformation - '. $groupformation->name .'
                    </div>
                    <div class="gf_pad_content">
                        <div class="grid">
                            <div class="col_m_66 bp_align_left-middle">
                                <span>Die Aktivit&auml;t "Groupformation" l&auml;uft bereits und endet am 00.00.0000 um 0:00 Uhr.</span></br></br>
                                <span><i>Nach Abauf der Aktivität ist es den Studierenden nicht mehr möglich Fragebögen auszufühlen bzw abzugeben. Die Gruppenbildung kann nach dem Ablauf oder manuellen stoppen der Aktivität erfolgen!</i></span>
                                <span style="display:none;">Die Aktivit&auml;t "Groupformation" ist f&uuml;r Studierende ab dem 00.00.0000 um 0:00 Uhr verf&uuml;gbar und endet am 00.00.0000 um 0:00 Uhr</span>
                                <span><i></i></span>
                            </div>

                            <div class="col_m_33 bp_align_right-middle">
                                <span class="toolt" tooltip="Aktivit&auml;t stoppen um Gruppen zu bilden" style="margin-right:0.7em;"></span><button class="gf_button gf_button_pill gf_button_small"';
							//
                    		echo '>Aktivit&auml;t stoppen</button>
                            </div>
                        </div>
                    </div>

                    <div class="gf_pad_header_small">
                        Fragebogen Statistik
                    </div>
                    <div class="gf_pad_content">
                        <div class="grid row_highlight">
                            <div class="col_m_87-5">Es haben <b>'. $surveyStatisticNumers[0] .'</b> Studenten den Fragebogen bearbeitet</div>
                            <div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">view</button></div>
                        </div>
                        <div class="grid row_highlight">
                            <div class="col_m_87-5">Davon haben <b>'. $surveyStatisticNumers[1] .'</b> ihre Antworten schon fest abgegeben</div>
                            <div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">view</button></div>
                        </div>
                        <div class="grid row_highlight">
                            <div class="col_m_87-5">Von den fest abgegebenen Antworten sind <b>'. $surveyStatisticNumers[2] .'</b> nicht vollst&auml;ndig</div>
                            <div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">view</button></div>
                        </div>
                        <div class="grid row_highlight">
                            <div class="col_m_87-5">Generel gibt es <b>'. $surveyStatisticNumers[3] .'</b> vollst&auml;ndig beantwortete Frageb&ouml;gen</div>
                            <div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">view</button></div>
                        </div>
                    </div>
                </div>';
	
	
	
	
	
	echo $OUTPUT->footer ();
