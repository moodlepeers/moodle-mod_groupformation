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
 * Handles jobs for cron (groupformation jobs)
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

class mod_groupformation_advanced_job_manager {

    static private $jobstates = array(
            'ready' =>
                    array('waiting' => 0, 'started' => 0, 'aborted' => 0, 'done' => 0, 'groups_generated' => 0,
                            'groups_adopted' => 0),
            'waiting' =>
                    array('waiting' => 1, 'started' => 0, 'aborted' => 0, 'done' => 0, 'groups_generated' => 0,
                            'groups_adopted' => 0),
            'started' =>
                    array('waiting' => 0, 'started' => 1, 'aborted' => 0, 'done' => 0, 'groups_generated' => 0,
                            'groups_adopted' => 0),
            'aborted' =>
                    array('waiting' => 0, 'started' => 0, 'aborted' => 1, 'done' => 0, 'groups_generated' => 0,
                            'groups_adopted' => 0),
            'done' =>
                    array('waiting' => 0, 'started' => 0, 'aborted' => 0, 'done' => 1, 'groups_generated' => 1,
                            'groups_adopted' => 0),
            'waiting_groups' =>
                    array('waiting' => 1, 'started' => 0, 'aborted' => 0, 'done' => 0, 'groups_generated' => 1,
                            'groups_adopted' => 0),
            'started_groups' =>
                    array('waiting' => 1, 'started' => 0, 'aborted' => 0, 'done' => 0, 'groups_generated' => 1,
                            'groups_adopted' => 0),
            'aborted_groups' =>
                    array('waiting' => 0, 'started' => 0, 'aborted' => 1, 'done' => 0, 'groups_generated' => 1,
                            'groups_adopted' => 0),
            'done_groups' =>
                    array('waiting' => 0, 'started' => 0, 'aborted' => 0, 'done' => 1, 'groups_generated' => 1,
                            'groups_adopted' => 1),
    );

    static private $timesstatesmap = array(
            'waiting' => 'timecreated',
            'started' => 'timestarted',
            'aborted' => 'timefinished',
            'done' => 'timefinished',
            'waiting_groups' => 'timecreated',
            'started_groups' => 'timestarted',
            'aborted_groups' => 'timefinished',
            'done_groups' => 'timefinished'
    );

    /**
     * Returns job
     *
     * @param $groupformationid
     * @return stdClass|null
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
     * Returns the next job
     *
     * @param string $state
     * @return stdClass|null
     */
    public static function get_next_job($state = 'waiting') {
        global $DB;

        $jobs = self::get_jobs($state);

        if (count($jobs) == 0) {
            return null;
        }

        $next = null;

        foreach (array_values($jobs) as $job) {

            if ($job->timecreated != null && ($next == null || $job->timecreated < $next->timecreated)) {
                $next = $job;
            }

        }

        return $next;
    }

    /**
     * Returns jobs with a certain state
     *
     * @param string $state
     * @return array
     */
    public static function get_jobs($state = 'waiting') {
        global $DB;

        $jobs = $DB->get_records('groupformation_jobs', self::$jobstates[$state]);

        return $jobs;
    }

    /**
     * Sets job state
     *
     * @param $job
     * @param string $state
     * @return bool
     */
    public static function set_job($job, $state = 'ready', $settime = false, $resettime = false) {
        global $DB, $USER;

        if ($state == 'ready') {
            self::reset_job($job);
            return true;
        }

        foreach (self::$jobstates[$state] as $key => $value) {
            $job->$key = $value;

            if ($value && $settime) {
                $timekey = self::$timesstatesmap[$state];
                $job->$timekey = time();
            }

            if (!$value && $resettime) {
                $timekey = self::$timesstatesmap[$state];
                $job->$timekey = 0;
            }
        }

        if ($job->waiting == 1) {
            $job->started_by = $USER->id;
        }

        if ($job->done == 1) {
            $job->groups_generated = 1;
        }

        return $DB->update_record('groupformation_jobs', $job);
    }

    /**
     * Resets job
     *
     * @param $job
     * @return bool
     */
    public static function reset_job($job) {
        global $DB;

        $DB->delete_records('groupformation_jobs',
                array('groupformationid' => $job->groupformationid)
        );

        return true;
    }

    /**
     * Returns state of job
     *
     * @param $job
     * @return int|string
     */
    public static function get_state($job) {
        foreach (self::$jobstates as $state => $values) {
            $bool = true;
            foreach ($values as $key => $value) {
                $bool &= ($job->$key == $value);
            }
            if ($bool) {
                return $state;
            }
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

        $DB->insert_record('groupformation_jobs', $job);
    }

    /**
     * Updates job record
     *
     * @param integer $groupformationid
     * @param integer $groupingid
     */
    public static function update_job($job, $groupingid) {
        global $DB;

        $job->groupingid = $groupingid;

        $DB->update_record('groupformation_jobs', $job);
    }

    /**
     * Checks whether a job is in the expected state
     *
     * @param $job
     * @param $expectedstate
     * @return bool
     */
    public static function check_state($job, $expectedstate) {
        $state = self::get_state($job);

        return $state == $expectedstate;
    }

    /**
     * Notifies teacher about terminated job
     *
     * @param $job
     * @return null
     */
    public static function notify_teacher($job) {
        global $DB, $CFG;

        if (false) {
            $userid = $job->started_by;
            $rec = array_pop($DB->get_records('course_modules', array(
                    'instance' => $job->groupformationid)));
            $coursemoduleid = $rec->id;
            $recipient = array_pop($DB->get_records('user', array(
                    'id' => $userid)));
            $subject = get_string('groupformation_message_subject', 'groupformation');
            $message = get_string('groupformation_message', 'groupformation');
            $contexturl = $CFG->wwwroot;
            $contexturl .= '/mod/groupformation/grouping_view.php?id=';
            $contexturl .= $coursemoduleid;
            $contexturl .= '&do_show=grouping';
            $contexturlname = get_string('groupformation_message_contexturlname', 'groupformation');
            groupformation_send_message($recipient, $subject, $message, $contexturl, $contexturlname);
        }

        return null;
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
        $users = $store->get_users_for_grouping($job);
        if (is_null($users)) {
            return $cohorts;
        }

        if ($store->is_math_prep_course_mode()) {
            $sg = new mod_groupformation_scientific_grouping($groupformationid);
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
    public static function save_result($job, $result = null) {

        $store = new mod_groupformation_storage_manager($job->groupformationid);
        $groupsmanager = new mod_groupformation_groups_manager($job->groupformationid);

        $store->delete_statistics();

        $mathprepcourse = $store->is_math_prep_course_mode();

        foreach ($result as $groupkey => $cohort) {
            if (is_null($cohort)) {
                continue;
            }
            $cohortresult = $cohort->get_result();

            $flags = array('group_key' => $groupkey);

            if (!$mathprepcourse) {
                $flags = array(
                        "groupal" => (strpos($groupkey, "groupal:1") !== false) ? 1 : 0,
                        "random" => (strpos($groupkey, "random:1") !== false) ? 1 : 0,
                        "mrandom" => 0,
                        "created" => 0,
                        "topic" => (strpos($groupkey, "topic:1") !== false) ? 1 : 0,
                        "group_key" => $groupkey
                );
            }

            $groups = $cohortresult->groups;
            $idmap = $groupsmanager->create_groups($groups, $flags);
            $users = $cohortresult->users;
            $groupsmanager->assign_users_to_groups($users, $idmap);
            $store->save_statistics($groupkey, $cohort);
        }

        self::set_job($job, 'done', true);

        return true;
    }
}
