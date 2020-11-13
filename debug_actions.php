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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

// Reads URL parameters.
$runjob = optional_param('run_job', false, PARAM_BOOL);
$buildgroups = optional_param('build_groups', false, PARAM_BOOL);
$createusers = optional_param('create_users', 0, PARAM_INT);
$createanswers = optional_param('create_answers', false, PARAM_BOOL);
$randomanswers = optional_param('random_answers', false, PARAM_BOOL);
$deleteusers = optional_param('delete_users', false, PARAM_BOOL);
$resetjob = optional_param('reset_job', false, PARAM_BOOL);
$createcsv = optional_param('create_csv', false, PARAM_BOOL);

$debugbuttons = "";

// Only if debug mode is activated or if the user is a debug user.
if (isset($CFG->debugusers)) {
    $debugusers = explode(',', $CFG->debugusers);
}

if (true || ($CFG->debug === 32767) || (in_array($USER->id, $debugusers))) {

    // Reset job action.
    if ($resetjob) {
        global $DB;

        $DB->delete_records('groupformation_jobs', array('groupformationid' => $groupformation->id));

        $store->statemachine->set_state(1);

        $return = new moodle_url ('/mod/groupformation/view.php', array(
                'id' => $id, 'do_show' => 'analysis'));
        redirect($return->out());
    }

    // Run job action.
    if ($runjob) {
        $ajm = new mod_groupformation_advanced_job_manager();

        $job = null;

        $job = $ajm::get_job($groupformation->id);

        if (!is_null($job)) {
            $result = $ajm::do_groupal($job);
            $saved = $ajm::save_result($job, $result);
            $ajm::set_job($job, 'done');
            $store->statemachine->set_state(4);
        }
        $return = new moodle_url ('/mod/groupformation/analysis_view.php', array(
                'id' => $id, 'do_show' => 'analysis'));
        redirect($return->out());
    }

    // Run job action.
    if ($buildgroups) {
        $ajm = new mod_groupformation_advanced_job_manager();

        $job = null;

        $job = $ajm::get_job($groupformation->id);

        if (!is_null($job)) {
            mod_groupformation_group_generator::generate_moodle_groups($job->groupformationid);
            $ajm::set_job($job, 'done_groups');
            $store->statemachine->set_state(6);
        }
        $return = new moodle_url ('/mod/groupformation/analysis_view.php', array(
                'id' => $id, 'do_show' => 'analysis'));
        redirect($return->out());
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
    $debugbuttons = "";
    $debugbuttons .= '<div class="gf_pad_header">';
    $debugbuttons .= 'Developer options';
    $debugbuttons .= '</div>';
    $debugbuttons .= '<div class="gf_pad_content">';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 10, 'create_answers' => 1)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Create 10 users with answers';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 1, 'create_answers' => 1)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Create 1 user with answers';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';
    $debugbuttons .= '<br>';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 10)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Create 10 users without answers';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'create_users' => 1)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Create 1 user without answers';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';
    $debugbuttons .= '<br>';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'delete_users' => 1)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Delete all users with answers';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';
    $debugbuttons .= '<br>';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis', 'reset_job' => 1)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Delete jobs of this activity';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';
    $debugbuttons .= '<br>';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
        'id' => $id, 'do_show' => 'analysis', 'run_job' => 1)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Run group formation';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';
    $debugbuttons .= '<br>';

    $debugbuttons .= '<a href="' . (new moodle_url('/mod/groupformation/analysis_view.php', array(
        'id' => $id, 'do_show' => 'analysis', 'build_groups' => 1)))->out() . '">';
    $debugbuttons .= '<span class="gf_button gf_button_pill gf_button_small">';
    $debugbuttons .= 'Adopt groups to Moodle';
    $debugbuttons .= '</span>';
    $debugbuttons .= '</a>';

    $debugbuttons .= '</div>';

    // create csv with result of binquestion
    if ($createcsv) {

        // open the file "demosaved.csv" for writing
        $file = fopen('binquestion_group_result.csv', 'w');

        // save the column headers
        fputcsv($file, array("sep=,"));
        fputcsv($file, array('user id', 'answer', 'group id', 'group name', 'group size'));

        global $DB;
        $data = $DB->get_records_sql("SELECT result.userid, result.answer, result.groupid, groups.groupname, groups.group_size "
                . "FROM
            (
            SELECT result.userid, result.answer, group_user.groupid
            FROM
            (
                SELECT users.userid, answers.answer
                FROM mdl_groupformation_users AS users
                JOIN mdl_groupformation_answers as answers
                ON users.userid = answers.userid
                AND answers.category = \"binquestion\"
                AND answers.groupformation= 10
                AND users.groupformation = 10
            ) AS result
            JOIN mdl_groupformation_group_users as group_user
            ON group_user.groupformation = 10
            AND result.userid = group_user.userid
        ) as result
        JOIN mdl_groupformation_groups as groups
        ON result.groupid = groups.id");

        // save each row of the data
        foreach ($data as $row) {
            fputcsv($file, [$row->userid, $row->answer, $row->groupid, $row->groupname, $row->group_size]);
        }

        // Close the file
        fclose($file);

        $return = new moodle_url ('/mod/groupformation/analysis_view.php', array(
                'id' => $id, 'do_show' => 'analysis'));
        redirect($return->out());
    }
}
