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
 * Research actions and buttons for analysis view
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

// Reads URL parameters.
$filterusers = optional_param('filterusers', false, PARAM_BOOL);
$unfilterusers = optional_param('unfilterusers', false, PARAM_BOOL);

$debugbuttons = "";

$stats = $store->get_honesty_stats();

// Only if debug mode is activated.
if (mod_groupformation_data::is_math_prep_course_mode()) {

    // Reset job action.
    if ($filterusers) {
        $store->filter_users(true);
        $returnurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
            'id' => $id, 'do_show' => 'grouping'));
        redirect($returnurl);
    }

    // Reset job action.
    if ($unfilterusers) {
        $store->filter_users(false);
        $returnurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
            'id' => $id, 'do_show' => 'grouping'));
        redirect($returnurl);
    }

    // Generate debug actions as buttons.
    $debugbuttons = "";
    $debugbuttons .= '<div class="gf_pad_header">';
    $debugbuttons .= 'Mathe-Vorkurs-Studie';
    $debugbuttons .= '</div>';
    $debugbuttons .= '<div class="gf_pad_content">';

    $debugbuttons .= '<p>';

    $debugbuttons .= 'Es haben <b>' . $stats['yes'] . '</b> Studierende geantwortet, dass sie ehrlich und ';
    $debugbuttons .= 'konzentriert geantwortet haben.';
    $debugbuttons .= '<br>';
    $debugbuttons .= 'Es haben <b>' . $stats['no'] . '</b> Studierende geantwortet, dass sie <b>nicht</b> ehrlich und ';
    $debugbuttons .= 'konzentriert geantwortet haben.';
    $debugbuttons .= '</p>';
    $debugbuttons .= '<p>';
    $v = 0.0;
    if ($stats['yes'] !== 0 && $stats['no'] !== 0){
        $v = round(floatval($stats['no']) / ($stats['yes'] + $stats['no']), 4) * 100;
    }
    $debugbuttons .= 'Es haben also <b>' . $v . '%</b> der Studierende geantwortet, dass sie <b>nicht</b> ehrlich und ';
    $debugbuttons .= 'konzentriert geantwortet haben.';
    $debugbuttons .= '</p>';

    if (!$store->uses_filter()) {
        $debugbuttons .= '<p>';
        $debugbuttons .= '<h5>';
        $debugbuttons .= 'Es wird aktuell nicht gefiltert.';
        $debugbuttons .= '</h5>';
        $debugbuttons .= '</p>';
    } else {
        $debugbuttons .= '<p>';
        $debugbuttons .= '<h5 style="color: red;">';
        $debugbuttons .= 'Es wird aktuell gefiltert.';
        $debugbuttons .= '</h5>';
        $debugbuttons .= '</p>';
    }

    $ajm = new mod_groupformation_advanced_job_manager();

    $job = $ajm::get_job($groupformation->id);

    if ($ajm::get_state($job) == 'ready') {

        $debugbuttons .= '<p>';
        $debugbuttons .= 'Klicken sie auf "Filtern", um die Studierenden, die nicht ehrlich und konzentriert geantwortet ';
        $debugbuttons .= 'haben von der optimierten Gruppierung auszuschließen und sie stattdessen randomisiert zu gruppieren. ';
        $debugbuttons .= 'Klicken Sie auf "Nicht filtern" um diese Aktion rückgängig zu machen.';
        $debugbuttons .= '</p>';

        if (!$store->uses_filter()) {
            $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/grouping_view.php', array(
                    'id' => $id, 'do_show' => 'grouping', 'filterusers' => 1)))->out() . '">';
            $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
            $debugbuttons .= 'Filtern';
            $debugbuttons .= '</span>';

            $debugbuttons .= '</a>';
        } else {
            $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/grouping_view.php', array(
                    'id' => $id, 'do_show' => 'grouping', 'unfilterusers' => 1)))->out() . '">';
            $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
            $debugbuttons .= 'Nicht filtern';
            $debugbuttons .= '</span>';
            $debugbuttons .= '</a>';
        }

    } else {
        $debugbuttons .= '<p>';
        $debugbuttons .= 'Aufgrund der laufenden oder schon abgeschlossenen Gruppenbildung ist ein Ändern ';
        $debugbuttons .= 'der Filtereinstellungen nicht möglich.';
        $debugbuttons .= '</p>';
    }

    $debugbuttons .= '</div>';
}