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
 * Handles jobs for cron  (groupformation jobs)
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/scientific_grouping.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/basic_grouping.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/topic_grouping.php');

require_once($CFG->dirroot . '/mod/groupformation/lib/classes/criteria/specific_criterion.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/participant.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/matchers/group_centric_matcher.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/basic_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/random_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/topic_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/optimizers/optimizer.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/xml_writers/participant_writer.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/xml_writers/cohort_writer.php');

class mod_groupformation_job_manager {

    private static $jobstatusoptions = array(
        'ready' => '0000', 'waiting' => '1000', 'started' => '0100', 'aborted' => '0010', 'done' => '0001');

    /**
     * Selects next job and sets it on "started"
     *
     * @return Ambigous <>
     */
    public static function get_next_job() {
        global $DB;
        $jobs = $DB->get_records('groupformation_jobs', array(
            'waiting' => 1, 'started' => 0, 'aborted' => 0, 'done' => 0));

        if (count($jobs) == 0) {
            return null;
        }

        $next = null;

        foreach (array_values($jobs) as $job) {
            if ($job->timecreated != null && ($next == null || $job->timecreated < $next->timecreated)) {
                $next = $job;
            }
        }

        static::set_job($next, "started", true);

        groupformation_info(null, $next->groupformationid,
            'groupal job with groupformation id="' . $next->groupformationid . '" selected');

        return $next;
    }

    /**
     * Selects aborted but not started jobs and sets it on "started"
     *
     * @return Ambigous <>
     */
    public static function get_aborted_jobs() {
        global $DB;

        $jobs = $DB->get_records('groupformation_jobs', array(
            'waiting' => 0, 'started' => 0, 'aborted' => 1, 'done' => 0, 'timestarted' => 0));

        return $jobs;
    }

    /**
     * Resets job to "ready"
     *
     * @param stdClass $job
     */
    public static function reset_job($job) {
        static::set_job($job, "ready", false, true);
        groupformation_info(null, $job->groupformationid,
            'groupal job with groupformation id="' . $job->groupformationid . '" resetted');
    }

    /**
     * Sets job to state e.g. 1000 by passing state = ready, waiting, started, aborted, done
     *
     * @param $job
     * @param string $state, keywords: ready, waiting, started, aborted, done; you can pass a 4 digit bit-mask e.g 0000 for ready, 1000 for waiting etc.
     * @param bool|false $settime
     * @param bool|false $resettime
     * @return bool
     */
    public static function set_job($job, $state = "ready", $settime = false, $resettime = false) {
        global $DB, $USER;
        $statusoptions = static::$jobstatusoptions;

        if (array_key_exists($state, $statusoptions)) {
            $status = $statusoptions[$state];
        } else {
            $status = $state;
        }
        if (!(preg_match("/[0-1]{4}/", $status) && strlen($status) == 4)) {
            return false;
        }

        $job->waiting = $status[0];
        $job->started = $status[1];
        $job->aborted = $status[2];
        $job->done = $status[3];
        if ($job->waiting == 1 && $settime) {
            $job->timecreated = time();
            groupformation_info(null, $job->groupformationid, 'groupal job set to waiting');
        }
        if ($job->done == 1 && $settime) {
            $job->timefinished = time();
            groupformation_info(null, $job->groupformationid, 'groupal job set to done');
        }
        if ($job->started == 1 && $settime) {
            $job->timestarted = time();
            groupformation_info(null, $job->groupformationid, 'groupal job set to started');
        }
        if ($job->aborted == 1) {
            groupformation_info(null, $job->groupformationid, 'groupal job set to aborted');
        }
        if ($job->waiting == 0 && $resettime) {
            $job->timecreated = 0;
        }
        if ($job->done == 0 && $resettime) {
            $job->timefinished = 0;
        }
        if ($job->started == 0 && $resettime) {
            $job->timestarted = 0;
        }
        if ($resettime) {
            $job->matcher_used = null;
            $job->count_groups = null;
            $job->performance_index = null;
            $job->stats_avg_variance = null;
            $job->stats_variance = null;
            $job->stats_n = null;
            $job->stats_avg = null;
            $job->stats_st_dev = null;
            $job->stats_norm_st_dev = null;
            $job->stats_performance_index = null;
        }

        if ($job->waiting == 1) {
            $job->started_by = $USER->id;
        }

        return $DB->update_record('groupformation_jobs', $job);
    }

    /**
     * Checks whether job is aborted or not
     *
     * @param stdClass $job
     * @return boolean
     */
    public static function is_job_aborted($job) {
        global $DB;

        return $DB->get_field('groupformation_jobs', 'aborted', array(
            'id' => $job->id)) == '1';
    }

    /**
     * Returns users
     *
     * @param $job
     * @param $groupformationid
     * @param mod_groupformation_storage_manager $store
     * @return array|null
     */
    public static function get_users($groupformationid, $job = null, mod_groupformation_storage_manager $store = null) {
        global $DB;
        if (is_null($job)) {
            $job = self::get_job($groupformationid);
        }

        if (is_null($store)) {
            $store = new mod_groupformation_storage_manager($groupformationid);
        }

        $courseid = $store->get_course_id();
        $context = context_course::instance($courseid);

        $enrolledstudents = null;

        if (intval($job->groupingid) != 0) {
            $enrolledstudents = array_keys(groups_get_grouping_members($job->groupingid));
        } else {
            $enrolledstudents = array_keys(get_enrolled_users($context, 'mod/groupformation:onlystudent'));
            $enrolledprevusers = array_keys(get_enrolled_users($context, 'mod/groupformation:editsettings'));
            $diff = array_diff($enrolledstudents, $enrolledprevusers);
            $enrolledstudents = $diff;
        }
        if (is_null($enrolledstudents) || count($enrolledstudents) <= 0) {
            return null;
        }

        $groupingsetting = $store->get_grouping_setting();

        $allanswers = array();
        $someanswers = array();
        $noorsomeanswers = array();

        // has_answered_everything
        $store = new mod_groupformation_storage_manager ($groupformationid);
        $categories = $store->get_categories();
        $sum = array_sum($store->get_numbers($categories));
        
        // get userids of groupformation answers
        $userids = $DB->get_fieldset_select('groupformation_answer', 'userid', 'groupformation = ?', array($groupformationid));
        
        // returns an array using the userids as keys and their frequency in answers as values
        $user_frequencies = array_count_values($userids);
        
        $number_of_answers = function($userid) use ($sum, $user_frequencies) {
            return array_key_exists($userid, $user_frequencies) ? $user_frequencies[$userid] : 0;
        };
        
        foreach (array_values($enrolledstudents) as $userid) {
            if($sum <= $number_of_answers($userid)) {
                $allanswers [] = $userid;
            } else if($groupingsetting && $number_of_answers($userid) > 0) {
                $someanswers [] = $userid;
            } else {
                $noorsomeanswers [] = $userid;
                }
        }

        $groupalusers = $allanswers;

        if ($groupingsetting) {
            $randomusers = $someanswers;
        } else {
            $randomusers = $noorsomeanswers;
        }

        return array(
            $groupalusers, $randomusers);
    }

    /**
     * Runs groupal with job
     *
     * @param stdClass $job
     * @return array with 3 elements: groupal cohorts, random cohort and incomplete random cohort
     */
    public static function do_groupal($job) {

        $cohorts = array(
            null, null, null);

        $groupformationid = $job->groupformationid;

        $store = new mod_groupformation_storage_manager ($groupformationid);

        // Assign users.
        $users = self::get_users($groupformationid, $job, $store);
        if (is_null($users)) {
            return $cohorts;
        }

        if ($store->is_math_prep_course_mode()) {
            $sg = new mod_groupformation_scientific_grouping($job->groupformationid);
            return $sg->run_grouping($users);
        } else if ($store->ask_for_topics()) {
            $tg = new mod_groupformation_topic_grouping($groupformationid);
            return $tg->run_grouping($users);
        } else {
            $bg = new mod_groupformation_basic_grouping($groupformationid);
            return $bg->run_grouping($users);
        }
    }

    /**
     * Saves results
     *
     * @param stdClass $job
     * @param stdClass $result
     * @return boolean
     */
    public static function save_result($job, $result = null, $store = null) {

        if (is_null($store)) {
            $store = new mod_groupformation_storage_manager($job->groupformationid);
        }
        if ($store->is_math_prep_course_mode()) {
            self::delete_stats($job);
            foreach ($result as $groupkey => $cohort) {
                $cohortresult = $cohort->get_result();
                $flags = array('group_key' => $groupkey);
                $idmap = self::create_groups($job, $cohortresult->groups, $flags);
                self::assign_users_to_groups($job, $cohortresult->users, $idmap);
                self::save_stats($job, $cohort, $groupkey);
            }
            self::set_job($job, 'done', true);
            return true;
        }

        self::delete_stats($job);
        foreach ($result as $groupkey => $cohort) {
            if (is_null($cohort)) {
                continue;
            }
            $cohortresult = $cohort->get_result();
            $flags = array(
                "groupal" => (strpos($groupkey, "groupal:1") !== false) ? 1 : 0,
                "random" => (strpos($groupkey, "random:1") !== false) ? 1 : 0,
                "mrandom" => 0,
                "created" => 0,
                "topic" => (strpos($groupkey, "topic:1") !== false) ? 1 : 0,
                "group_key" => $groupkey
            );
            $idmap = self::create_groups($job, $cohortresult->groups, $flags);
            self::assign_users_to_groups($job, $cohortresult->users, $idmap);
            self::save_stats($job, $cohort, $groupkey);
        }

        self::set_job($job, 'done', true);

        return true;
    }

    /**
     * Deletes all stats from previous job runs
     *
     * @param $job
     */
    public static function delete_stats($job) {
        global $DB;

        $DB->delete_records('groupformation_stats', array('groupformationid' => $job->groupformationid));

    }

    /**
     * Saves stats for computed job
     *
     * @param $job
     * @param mod_groupformation_cohort $cohort
     */
    private static function save_stats($job, $cohort = null, $groupkey = null) {
        global $DB;

        $record = new stdClass();

        $record->groupformationid = $job->groupformationid;
        $record->group_key = $groupkey;

        $record->matcher_used = strval($cohort->whichmatcherused);
        $record->count_groups = floatval($cohort->countofgroups);
        $record->performance_index = floatval($cohort->cpi);

        $stats = $cohort->results;

        if (!is_null($stats)) {
            $record->stats_avg_variance = $stats->avgvariance;
            $record->stats_variance = $stats->variance;
            $record->stats_n = $stats->n;
            $record->stats_avg = $stats->avg;
            $record->stats_st_dev = $stats->stddev;
            $record->stats_norm_st_dev = $stats->normstddev;
            $record->stats_performance_index = $stats->performanceindex;
        }

        $DB->insert_record('groupformation_stats', $record);

    }

    /**
     * Creates groups
     *
     * @param $job
     * @param $groups
     * @param $flags
     * @return array
     */
    private static function create_groups($job, $groups, $flags) {
        $groupformationid = $job->groupformationid;
        $groupsmanager = new mod_groupformation_groups_manager($groupformationid);
        $store = new mod_groupformation_storage_manager($groupformationid);

        $groupnameprefix = $store->get_group_name_setting();
        $groupformationname = $store->get_name();
        $i = $store->get_instance_number();
        $groupname = "G" . $i . "_" . $groupnameprefix;

        if (strlen($groupnameprefix) < 1) {
            $groupname = "G" . $i . "_" . substr($groupformationname, 0, 8);
        }

        $topicoptions = null;
        $istopic = (!!$flags['topic']); // fast boolean casting of 0 and 1

        if ($istopic) {
            $xmlcontent = $store->get_knowledge_or_topic_values('topic');
            $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $xmlcontent . ' </OPTIONS>';
            $topicoptions = mod_groupformation_util::xml_to_array($xmlcontent);
        }

        $ids = array();
        foreach ($groups as $groupalid => $group) {
            $name = "";
            if ($istopic) {
                $name = $groupname."_".substr($topicoptions[$groupalid - 1], 0, 5);
            } else {
                $name = $groupname;
            }
            if (count($group['users']) > 0 || $istopic) { // in case of topic groups create as well empty groups
                $name = $name."_".strval($groupalid);
                $dbid = $groupsmanager->create_group($groupalid, $group, $name, $groupformationid, $flags);
                $ids[$groupalid] = $dbid;
            }
        }
        return $ids;
    }

    /**
     * Assign users to groups
     *
     * @param stdClass $job
     * @param unknown $users
     * @param unknown $idmap
     */
    private static function assign_users_to_groups($job, $users, $idmap) {
        $groupformationid = $job->groupformationid;
        $groupsmanager = new mod_groupformation_groups_manager($groupformationid);
        foreach ($users as $userid => $groupalid) {
            $groupsmanager->assign_user_to_group($groupformationid, $userid, $groupalid, $idmap);
        }
    }

    /**
     * Creates job for groupformation instance
     *
     * @param integer $groupformationid
     */
    public static function create_job($groupformationid, $groupingid = 0) {
        global $DB;

        $job = new stdClass ();
        $job->groupformationid = $groupformationid;
        $job->groupingid = $groupingid;
        $job->waiting = 0;
        $job->started = 0;
        $job->aborted = 0;
        $job->done = 0;
        $job->timecreated = 0;
        $job->timestarted = 0;
        $job->timefinished = 0;

        $DB->insert_record('groupformation_jobs', $job);
    }

    /**
     * Updates job record
     *
     * @param unknown $groupformationid
     * @param unknown $groupingid
     */
    public static function update_job($groupformationid, $groupingid) {
        global $DB;
        if ($job = $DB->get_record('groupformation_jobs', array(
            'groupformationid' => $groupformationid))
        ) {
            $job->groupingid = $groupingid;
            $DB->update_record('groupformation_jobs', $job);
        }
    }

    /**
     * Returns job for groupformation
     *
     * @param integer $groupformationid
     * @return stdClass
     */
    public static function get_job($groupformationid) {
        global $DB;
        if ($DB->record_exists('groupformation_jobs', array(
            'groupformationid' => $groupformationid))
        ) {
            return $DB->get_record('groupformation_jobs', array(
                'groupformationid' => $groupformationid));
        } else {
            return null;
        }
    }

    /**
     * Returns job status -> to compare use $data->get_job_status_options()
     *
     * @param stdClass $job
     * @return String
     */
    public static function get_status($job) {
        $statusoptions = array_keys(self::$jobstatusoptions);
        if ($job->waiting) {
            return $statusoptions [1];
        } else if ($job->started) {
            return $statusoptions [2];
        } else if ($job->aborted) {
            return $statusoptions [3];
        } else if ($job->done) {
            return $statusoptions [4];
        } else {
            return $statusoptions [0];
        }
    }

    /**
     * Notifies teacher about terminated groupformation job
     *
     * @param stdClass $job
     * @return NULL
     */
    public static function notify_teacher($job) {
        // Disabled for now

        //global $DB, $CFG;
        //$userid = $job->started_by;
        //$rec = array_pop($DB->get_records('course_modules', array(
        //    'instance' => $job->groupformationid)));
        //$coursemoduleid = $rec->id;
        //$recipient = array_pop($DB->get_records('user', array(
        //    'id' => $userid)));
        //$subject = get_string('groupformation_message_subject', 'groupformation');
        //$message = get_string('groupformation_message', 'groupformation');
        //$contexturl = $CFG->wwwroot;
        //$contexturl .= '/mod/groupformation/grouping_view.php?id=';
        //$contexturl .= $coursemoduleid;
        //$contexturl .= '&do_show=grouping';
        //$contexturlname = get_string('groupformation_message_contexturlname', 'groupformation');
        //groupformation_send_message($recipient, $subject, $message, $contexturl, $contexturlname);

        return null;
    }
}
