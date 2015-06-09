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
// require_once ($CFG->dirroot.'/mod/feedback/lib.php');

$id = optional_param ( 'id', 0, PARAM_INT ); // Course Module ID
$g = optional_param ( 'g', 0, PARAM_INT ); // groupformation instance ID

$current_tab = 'analyse';

if ($id) {
	$cm = get_coursemodule_from_id ( 'groupformation', $id, 0, false, MUST_EXIST );
	$course = $DB->get_record ( 'course', array (
			'id' => $cm->course 
	), '*', MUST_EXIST );
	$groupformation = $DB->get_record ( 'groupformation', array (
			'id' => $cm->instance 
	), '*', MUST_EXIST );
} else if ($g) {
	$groupformation = $DB->get_record ( 'groupformation', array (
			'id' => $g 
	), '*', MUST_EXIST );
	$course = $DB->get_record ( 'course', array (
			'id' => $groupformation->course 
	), '*', MUST_EXIST );
	$cm = get_coursemodule_from_instance ( 'groupformation', $groupformation->id, $course->id, false, MUST_EXIST );
} else {
	error ( 'You must specify a course_module ID or an instance ID' );
}

require_login ( $course, true, $cm );
// $context = context_module::instance($cm->id);

$event = \mod_groupformation\event\course_module_viewed::create ( array (
		'objectid' => $PAGE->cm->instance,
		'context' => $PAGE->context 
) );

$event->add_record_snapshot ( 'course', $PAGE->course );
$event->add_record_snapshot ( $PAGE->cm->modname, $groupformation );
$event->trigger ();

$PAGE->set_url ( '/mod/groupformation/analyse.php', array (
		'id' => $cm->id,
		'do_show' => 'analyse' 
) );
$PAGE->set_title ( format_string ( $groupformation->name ) );
$PAGE->set_heading ( format_string ( $course->fullname ) );
// $PAGE->set_context($context);

echo $OUTPUT->header ();

// Print the tabs.
require ('tabs.php');

// Conditions to show the intro can change to look for own settings or whatever.
if ($groupformation->intro) {
	echo $OUTPUT->box ( format_module_intro ( 'groupformation', $groupformation, $cm->id ), 'generalbox mod_introbox', 'groupformationintro' );
}

// Replace the following lines with you own code.
//echo $OUTPUT->heading ( $groupformation->name );

require_once (dirname ( __FILE__ ) . '/classes/group_forming/submit_infos.php');
$infos = new mod_groupformation_submit_infos ( $groupformation->id );
$surveyStatisticNumers = $infos->getInfos ();



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
                                <span class="toolt" tooltip="Aktivit&auml;t stoppen um Gruppen zu bilden" style="margin-right:0.7em;"></span><button class="';
//                     		gf_button gf_button_pill gf_button_small
                    		echo '">Aktivit&auml;t stoppen</button>
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
                </div>


                <div class="gf_settings_pad">
                    <div class="gf_pad_header">
                        Gruppenbildung
                    </div>
                    <div class="gf_pad_content bp_align_left-middle">
                        <button class="gf_button gf_button_pill gf_button_small" disabled>Gruppen bilden</button>
                        <button class="gf_button gf_button_pill gf_button_small" disabled>Gruppenbildung stoppen</button>
                        <button class="gf_button gf_button_pill gf_button_small" >Gruppen l&ouml;schen</button>
                        <p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
                    </div>
                    <div class="gf_pad_header_small">
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
                    </div>

                </div>';





echo $OUTPUT->footer ();
