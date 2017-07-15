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
 * Debugging actions and buttons for analysis view
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Reads URL parameters.
$runjob = optional_param('runjob', false, PARAM_BOOL);
$createusers = optional_param('create_users', 0, PARAM_INT);
$createanswers = optional_param('create_answers', false, PARAM_BOOL);
$randomanswers = optional_param('random_answers', false, PARAM_BOOL);
$deleteusers = optional_param('delete_users', false, PARAM_BOOL);
$resetjob = optional_param('reset_job', false, PARAM_BOOL);

$debug_buttons = "";

// Only if debug mode is activated.
if ($CFG->debug === 32767) {

    // Reset job action.
    if ($resetjob) {
        global $DB;

        $DB->delete_records('groupformation_jobs', array('groupformationid' => $groupformation->id));
    }

    // Run job action.
    if ($runjob) {
        $job = null;

        $job = $ajm::get_job($groupformation->id);

        if (!is_null($job)) {
            $result = $ajm::do_groupal($job);
            $saved = $ajm::save_result($job, $result);
            $ajm::set_job('done');
        }
    }

    $cqt = new mod_groupformation_test_user_generator ($cm);

    // Delete test users with all answers.
    if ($deleteusers) {
        $cqt->delete_test_users($groupformation->id);
        $return = new moodle_url ('/mod/groupformation/analysis_view.php', array(
                'id' => $id, 'do_show' => 'analysis'));
        redirect($return->out());
    }

    // Create test users with or without answers.
    if ($createusers > 0) {
        $cqt->create_test_users($createusers, $groupformation->id, $createanswers, $randomanswers);
        $return = new moodle_url ('/mod/groupformation/analysis_view.php', array(
                'id' => $id, 'do_show' => 'analysis'));
        redirect($return->out());
    }

    // Generate debug actions as buttons.
    $debug_buttons = "";
    $debug_buttons .= '<div class="gf_pad_header">';
    $debug_buttons .= 'Developer options';
    $debug_buttons .= '</div>';
    $debug_buttons .= '<div class="gf_pad_content">';

    $debug_buttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 10, 'create_answers' => 1)))->out() . '">';
    $debug_buttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debug_buttons .= 'Create 10 users with answers';
    $debug_buttons .= '</span>';
    $debug_buttons .= '</a>';
    $debug_buttons .= '<br>';

    $debug_buttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 1, 'create_answers' => 1)))->out() . '">';
    $debug_buttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debug_buttons .= 'Create 1 user with answers';
    $debug_buttons .= '</span>';
    $debug_buttons .= '</a>';
    $debug_buttons .= '<br>';

    $debug_buttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 10)))->out() . '">';
    $debug_buttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debug_buttons .= 'Create 10 users without answers';
    $debug_buttons .= '</span>';
    $debug_buttons .= '</a>';
    $debug_buttons .= '<br>';

    $debug_buttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 1)))->out() . '">';
    $debug_buttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debug_buttons .= 'Create 1 user without answers';
    $debug_buttons .= '</span>';
    $debug_buttons .= '</a>';
    $debug_buttons .= '<br>';

    $debug_buttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'delete_users' => 1)))->out() . '">';
    $debug_buttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debug_buttons .= 'Delete all users with answers';
    $debug_buttons .= '</span>';
    $debug_buttons .= '</a>';
    $debug_buttons .= '<br>';

    $debug_buttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'reset_job' => 1)))->out() . '">';
    $debug_buttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debug_buttons .= 'Delete jobs of this activity';
    $debug_buttons .= '</span>';
    $debug_buttons .= '</a>';

    $debug_buttons .= '</div>';
}