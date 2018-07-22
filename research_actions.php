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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

// Reads URL parameters.
$filterusers = optional_param('filterusers', false, PARAM_BOOL);
$unfilterusers = optional_param('unfilterusers', false, PARAM_BOOL);

$debugbuttons = "";

$stats = $store->get_honesty_stats();

$ajm = new mod_groupformation_advanced_job_manager();

$job = $ajm::get_job($groupformation->id);

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
    $debugbuttons .= get_string('math_prep_course_study', 'groupformation');
    $debugbuttons .= '</div>';
    $debugbuttons .= '<div class="gf_pad_content">';

    $debugbuttons .= '<p>';
    $debugbuttons .= get_string('honest_answers', 'groupformation', $stats['yes']);
    $debugbuttons .= '<br>';
    $debugbuttons .= get_string('dishonest_answers', 'groupformation', $stats['no']);
    $debugbuttons .= '</p>';
    $debugbuttons .= '<p>';
    $v = 0.0;
    if ($stats['yes'] !== 0 && $stats['no'] !== 0) {
        $v = round(floatval($stats['no']) / ($stats['yes'] + $stats['no']), 4) * 100;
    }
    $debugbuttons .= get_string('ratio_answers', 'groupformation', $v);
    $debugbuttons .= '</p>';

    if (!$store->uses_filter()) {
        $debugbuttons .= '<p>';
        $debugbuttons .= '<h5>';
        $debugbuttons .= get_string('filter_inactive', 'groupformation');
        $debugbuttons .= '</h5>';
        $debugbuttons .= '</p>';
    } else {
        $debugbuttons .= '<p>';
        $debugbuttons .= '<h5 style="color: red;">';
        $debugbuttons .= get_string('filter_active', 'groupformation');
        $debugbuttons .= '</h5>';
        $debugbuttons .= '</p>';
    }

    $state = $store->statemachine->get_state();
    if (in_array($state, array('q_closed'))) {

        $debugbuttons .= '<p>';
        $debugbuttons .= get_string('filter_description', 'groupformation');
        $debugbuttons .= '</p>';

        if (!$store->uses_filter()) {
            $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/grouping_view.php', array(
                    'id' => $id, 'do_show' => 'grouping', 'filterusers' => 1)))->out() . '">';
            $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
            $debugbuttons .= get_string('filter', 'groupformation');
            $debugbuttons .= '</span>';

            $debugbuttons .= '</a>';
        } else {
            $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/grouping_view.php', array(
                    'id' => $id, 'do_show' => 'grouping', 'unfilterusers' => 1)))->out() . '">';
            $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
            $debugbuttons .= get_string('not_filter', 'groupformation');
            $debugbuttons .= '</span>';
            $debugbuttons .= '</a>';
        }

    } else {
        $debugbuttons .= '<p>';
        $debugbuttons .= get_string('no_filter_change', 'groupformation');
        $debugbuttons .= '</p>';
    }

    $debugbuttons .= '</div>';
    $debugbuttons .= '<div class="gf_pad_header">';
    $debugbuttons .= get_string('pre_study_header', 'groupformation');
    $debugbuttons .= '</div>';
    $debugbuttons .= '<div class="gf_pad_content">';

    $scientificgrouping = new mod_groupformation_scientific_grouping_2($groupformation->id);

    $users = $store->get_users_for_grouping($job);
    $groupsizes = $store->determine_group_size($users);

    list($configurations, $specs) = $scientificgrouping->get_specification();

    if (count($users)==0){
        $numberofslices = 0;
        $bestslices = array();
    } else {
        $numberofslices = $scientificgrouping->determine_number_of_slices(count($users[0]),$groupsizes[0],count($configurations));
        $bestslices = $scientificgrouping->get_optimal_slices($users[0], $numberofslices, $specs, $groupsizes[0]);
    }

    $configurations2 = array();
    foreach($bestslices as $key => $bestslice) {
        $k = array_keys($configurations)[$key];
        $configurations2[$k]=$configurations[$k];
    }
    $configurations = $configurations2;

    $debugbuttons .= '<p>';
    $debugbuttons .= get_string('optimized_grouping', 'groupformation', count($users[0]));
    $debugbuttons .= '<br>';
    $debugbuttons .= get_string('randomized_grouping', 'groupformation', count($users[1]));
    $debugbuttons .= '<br>';
    $debugbuttons .= '<p>';
    $debugbuttons .= get_string('scientific_grouping_setup', 'groupformation', count($configurations));
    $debugbuttons .= '<ul>';

    foreach(array_keys($configurations) as $key => $value) {
        $debugbuttons .= '<li>'.$value.' - #users: '.count($bestslices[$key]).' - #groups: '.intval(ceil(count($bestslices[$key])/$groupsizes[0])).'</li>';
    }
    if (count($users[1]) != 0) {
        $debugbuttons .= '<li>random:1 - #users: ' . count($users[1]) . ' - #groups: ' .
                intval(ceil(count($users[1]) / $groupsizes[1])) . '</li>';
    }

    $debugbuttons .= '</ul>';
    $debugbuttons .= '</p>';

    $debugbuttons .= '</div>';
}