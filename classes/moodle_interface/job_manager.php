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
 * This file contains the job manager class
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

require_once($CFG->dirroot . '/lib/groupal/classes/criteria/specific_criterion.php');
require_once($CFG->dirroot . '/lib/groupal/classes/participant.php');
require_once($CFG->dirroot . '/lib/groupal/classes/matchers/group_centric_matcher.php');
require_once($CFG->dirroot . '/lib/groupal/classes/algorithms/basic_algorithm.php');
require_once($CFG->dirroot . '/lib/groupal/classes/algorithms/random_algorithm.php');
require_once($CFG->dirroot . '/lib/groupal/classes/algorithms/topic_algorithm.php');
require_once($CFG->dirroot . '/lib/groupal/classes/optimizers/optimizer.php');
require_once($CFG->dirroot . '/lib/groupal/classes/xml_writers/participant_writer.php');
require_once($CFG->dirroot . '/lib/groupal/classes/xml_writers/cohort_writer.php');

/**
 * Job manager class
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_job_manager {

    /** @var array job status options */
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

        foreach ($jobs as $id => $job) {
            if ($job->timecreated != null && ($next == null || $job->timecreated < $next->timecreated)) {
                $next = $job;
            }
        }

        self::set_job($next, "started", true);

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
     *
     * Resets job to "ready"
     *
     * @param stdClass $job
     */
    public static function reset_job($job) {
        self::set_job($job, "ready", false, true);
        groupformation_info(null, $job->groupformationid,
            'groupal job with groupformation id="' . $job->groupformationid . '" resetted');
    }

    /**
     * Sets job to state e.g. 1000
     *
     * @param stdClass $job
     * @param string $state
     * @param bool $settime
     * @param bool $resettime
     * @param null $groupingid
     * @return bool
     */
    public static function set_job($job, $state = "ready", $settime = false, $resettime = false, $groupingid = null) {
        global $DB, $USER;
        $statusoptions = self::$jobstatusoptions;

        if (array_key_exists($state, $statusoptions)) {
            $status = $statusoptions [$state];
        } else {
            $status = $state;
        }
        if (!(preg_match("/[0-1]{4}/", $status) && strlen($status) == 4)) {
            return false;
        }

        $job->waiting = $status [0];
        $job->started = $status [1];
        $job->aborted = $status [2];
        $job->done = $status [3];

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
     * Returns users for job
     *
     * @param stdClass $job
     * @param int $groupformationid
     * @param mod_groupformation_storage_manager $store
     * @return array|null#
     */
    private static function get_users($job, $groupformationid, mod_groupformation_storage_manager $store) {
        $courseid = $store->get_course_id();
        $context = context_course::instance($courseid);

        $enrolledstudents = null;

        if (intval($job->groupingid) != 0) {
            $enrolledstudents = array_keys(groups_get_grouping_members($job->groupingid));
        } else {
            $enrolledstudents = array_keys(get_enrolled_users($context, 'mod/groupformation:onlystudent'));
        }
        if (is_null($enrolledstudents) || count($enrolledstudents) <= 0) {
            return null;
        }

        $usermanager = new mod_groupformation_user_manager ($groupformationid);

        $groupingsetting = $store->get_grouping_setting();

        $allanswers = array();
        $someanswers = array();
        $noorsomeanswers = array();

        foreach ($enrolledstudents as $userid) {
            if ($usermanager->is_completed($userid)) {
                $allanswers [] = $userid;
            } else if ($groupingsetting) {
                $someanswers [] = $userid;
            } else {
                $noorsomeanswers [] = $userid;
            }
        }

        $groupalusers = $allanswers;

        if ($store->get_grouping_setting()) {
            $randomusers = $someanswers;
        } else {
            $randomusers = $noorsomeanswers;
        }

        return array(
            $groupalusers, $randomusers);
    }

    /**
     * Determines the group size for groupal and random algorithm
     *
     * @param array $users
     * @param mod_groupformation_storage_manager $store
     * @param null $groupformationid
     * @return array|null
     */
    private static function determine_group_size($users, mod_groupformation_storage_manager $store,
                                                 $groupformationid = null) {
        if ($store->ask_for_topics()) {
            $groupoption = $store->get_group_option();
            if ($groupoption) {
                $maxgroups = intval($store->get_max_groups());
                $topicvalues = $store->get_knowledge_or_topic_values('topic');
                $topicvalues = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $topicvalues . ' </OPTIONS>';
                $topicsoptions = mod_groupformation_util::xml_to_array($topicvalues);
                $topicscount = count($topicsoptions);

                $userscount0 = count($users [0] + $users [1]);
                $ratio0 = $userscount0 / $maxgroups;

                $basegroupsize = floor($ratio0);

                $covereduserscount = $basegroupsize * $maxgroups;
                $remaininguserscount = $userscount0 - $covereduserscount;

                $usermanager = new mod_groupformation_user_manager ($groupformationid);

                $topics = $usermanager->get_most_common_topics($topicscount);

                $result = array();

                $i = 0;
                foreach ($topics as $key => $topic) {
                    if ($i < $remaininguserscount) {
                        $result [intval($topic ['id']) - 1] = intval(round($basegroupsize + 1));
                    } else {
                        $result [intval($topic ['id']) - 1] = intval(round($basegroupsize));
                    }
                    $i++;
                }

                return $result;
            } else {
                $maxmembers = intval($store->get_max_members());

                return null;
            }

            return $sizearray;
        } else {

            $userscount0 = count($users [0]);
            $userscount1 = count($users [1]);
            $userscount = $userscount0 + $userscount1;

            if ($userscount <= 0) {
                return null;
            }
            $groupoption = $store->get_group_option();
            if ($groupoption) {
                $maxgroups = intval($store->get_max_groups());

                if ($userscount0 == 0) {
                    return array(
                        null, intval(ceil($userscount1 / $maxgroups)));
                } else if ($userscount1 == 0) {
                    return array(
                        intval(ceil($userscount0 / $maxgroups)), null);
                }

                $optimalsize = ceil($userscount / $maxgroups);

                $optimalsize0 = $optimalsize;
                $optimalsize1 = $optimalsize;

                $check0 = false;
                $check1 = false;

                $ratio0 = $userscount0 / $userscount;
                $ratio1 = $userscount1 / $userscount;

                $groupnumber0 = round($ratio0 * $maxgroups);
                $groupnumber1 = round($ratio1 * $maxgroups);

                if ($groupnumber0 + $groupnumber1 > $maxgroups) {
                    if ($userscount0 > $userscount1) {
                        $groupnumber0--;
                    } else {
                        $groupnumber1--;
                    }
                }

                if ($groupnumber0 == 0) {
                    $groupnumber0 = $groupnumber0 + 1;
                    $groupnumber1 = $groupnumber1 - 1;
                } else if ($groupnumber1 == 0) {
                    $groupnumber0 = $groupnumber0 - 1;
                    $groupnumber1 = $groupnumber1 + 1;
                } else if ($maxgroups == 2) {
                    $groupnumber0 = 1;
                    $groupnumber1 = 1;
                }

                do {
                    $cond = ($groupnumber0 * $optimalsize0 > $userscount0) || ($optimalsize0 > $userscount0) ||
                        ($userscount0 % $optimalsize0 == 0);
                    if ($cond) {
                        $check0 = true;
                    } else {
                        $optimalsize0++;
                    }
                } while (!$check0);

                do {
                    $cond = ($groupnumber1 * $optimalsize1 > $userscount1) || ($optimalsize1 > $userscount1) ||
                        ($userscount1 % $optimalsize1 == 0);
                    if ($cond) {
                        $check1 = true;
                    } else {
                        $optimalsize1++;
                    }
                } while (!$check1);

                $basegroupsize = $optimalsize0;
                $groupsize1 = $optimalsize1;

                $cond = $maxgroups < (ceil($userscount0 / $basegroupsize) + ceil($userscount1 / $groupsize1));
                if ($cond) {
                    return null;
                }

                return array(
                    $basegroupsize, $groupsize1);
            } else {
                $maxmembers = intval($store->get_max_members());

                return array(
                    $maxmembers, $maxmembers);
            }
        }
    }

    /**
     * Runs topic-based algorithm
     *
     * @param stdClass $job
     * @param array $users
     * @param mod_groupformation_storage_manager $store
     * @return array
     */
    private static function run_topic_algorithm($job, $users, $store) {
        $groupformationid = $job->groupformationid;

        $groupalcohort = null;
        $randomcohort = null;
        $topiccohort = null;

        $cohorts = array(
            $groupalcohort, $randomcohort, $topiccohort);

        $groupsizes = self::determine_group_size($users, $store, $groupformationid);
        ksort($groupsizes);

        // In $groupsizes is an associative array where the key is 0 - (n-1) [id of topic].
        // And the value is the group size of each topic.

        $topicusers = $users [0];
        $incompleteusers = $users [1];

        // Build participants.
        $pp = new mod_groupformation_participant_parser ($groupformationid);
        $topicparticipants = $pp->build_topic_participants($topicusers);
        $randomparticipants = $pp->build_empty_participants($incompleteusers);
        if (count($topicparticipants) > 0) {
            $starttime = microtime(true);

            lib_groupal_group::setGroupMembersMaxSize(max($groupsizes));

            $gfa = new lib_groupal_topic_algorithm ($groupsizes, $topicparticipants);
            $topiccohort = $gfa->do_one_formation(); // This call takes time.

            $endtime = microtime(true);
            $comptime = $endtime - $starttime;

            groupformation_info(null, $job->groupformationid, 'groupal needed ' . $comptime . 'ms');
        }
        if (!is_null($topiccohort)) {
            // Now we have to add the remaining participants.

            $size = ceil((count($users [0]) + count($users [1])) / count($topiccohort->groups));
            lib_groupal_group::setGroupMembersMaxSize($size);

            $counts = array();
            $max = null;
            foreach ($topiccohort->groups as $group) {
                $value = count($group->getParticipants());
                $groups [] = array(
                    'id' => $group->getID(), 'count' => $value, 'group' => $group, 'participants' => array());
                if ($max == null || $max < $value) {
                    $max = $value;
                }
            }
            usort($groups, function ($a, $b) {
                return $a ['count'] - $b ['count'];
            });
            $groups = array_slice($groups, 0, count($groups));
            for ($i = 0; $i < count($randomparticipants); $i++) {
                usort($groups, function ($a, $b) {
                    return $a ['count'] - $b ['count'];
                });
                $groups = array_slice($groups, 0, count($groups));

                $p = $randomparticipants [$i];
                $groups [0] ['group']->addParticipant($p, true);
                $groups [0] ['count']++;
            }

            usort($groups, function ($a, $b) {
                return $a ['count'] - $b ['count'];
            });
        } else {
            // Pure random groups because no answers.
            $max = max($groupsizes);
            $gfra = new lib_groupal_random_algorithm ($randomparticipants, $max);
            $randomcohort = $gfra->do_one_formation();
        }

        $cohorts = array(
            $groupalcohort, $randomcohort, $topiccohort);

        return $cohorts;
    }

    /**
     * Runs basic groupal-based algorithm
     *
     * @param stdClass $job
     * @param array $users
     * @param mod_groupformation_storage_manager $store
     * @return array
     */
    private static function run_basic_algorithm($job, $users, $store) {

        $groupformationid = $job->groupformationid;

        $groupalcohort = null;
        $randomcohort = null;
        $topiccohort = null;

        $cohorts = array(
            $groupalcohort, $randomcohort, $topiccohort);

        $store = new mod_groupformation_storage_manager ($groupformationid);

        // Determine group sizes.
        $groupsize = self::determine_group_size($users, $store);

        $groupalusers = $users [0];
        $incompleteusers = $users [1];

        // Build participants.
        $pp = new mod_groupformation_participant_parser ($groupformationid);
        $groupalparticipants = $pp->build_participants($groupalusers);
        $randomparticipants = $pp->build_empty_participants($incompleteusers);
        if (count($groupalparticipants) > 0) {

            // Choose matcher.
            $matcher = new lib_groupal_group_centric_matcher ();

            $starttime = microtime(true);
            $gfa = new lib_groupal_basic_algorithm ($groupalparticipants, $matcher, $groupsize [0]);
            $groupalcohort = $gfa->do_one_formation(); // This call takes time.
            $endtime = microtime(true);
            $comptime = $endtime - $starttime;

            groupformation_info(null, $job->groupformationid, 'groupal needed ' . $comptime . 'ms');
        }

        if (count($randomparticipants) > 0) {
            $gfra = new lib_groupal_random_algorithm ($randomparticipants, $groupsize [1]);
            $randomcohort = $gfra->do_one_formation();
        }

        $cohorts = array(
            $groupalcohort, $randomcohort, $topiccohort);

        return $cohorts;
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
        $users = self::get_users($job, $groupformationid, $store);

        if (is_null($users)) {
            return $cohorts;
        }

        if ($store->ask_for_topics()) {
            $cohorts = self::run_topic_algorithm($job, $users, $store);
        } else {
            $cohorts = self::run_basic_algorithm($job, $users, $store);
        }

        return $cohorts;
    }

    /**
     * Saves results
     *
     * @param stdClass $job
     * @param stdClass $result
     * @return boolean
     */
    public static function save_result($job, $result = null) {

        $groupalcohort = $result [0];
        $randomcohort = $result [1];
        $topiccohort = $result [2];

        if (!is_null($groupalcohort)) {

            $result = $groupalcohort->getResult();

            $flags = array(
                "groupal" => 1, "random" => 0, "mrandom" => 0, "created" => 0, "topic" => 0);

            $idmap = self::create_groups($job, $result->groups, $flags);

            self::assign_users_to_groups($job, $result->users, $idmap);

            self::save_stats($job, $groupalcohort);
        }

        if (!is_null($randomcohort)) {
            $result = $randomcohort->getResult();

            $flags = array(
                "groupal" => 0, "random" => 1, "mrandom" => 0, "created" => 0, "topic" => 0);

            $idmap = self::create_groups($job, $result->groups, $flags);

            self::assign_users_to_groups($job, $result->users, $idmap);
        }

        if (!is_null($topiccohort)) {
            $result = $topiccohort->getResult();

            $flags = array(
                "groupal" => 0, "random" => 1, "mrandom" => 0, "created" => 0, "topic" => 1);

            $idmap = self::create_groups($job, $result->groups, $flags);

            self::assign_users_to_groups($job, $result->users, $idmap);
        }

        self::set_job($job, 'done', true);

        groupformation_info(null, $job->groupformationid, 'groupal results saved');

        return true;
    }

    /**
     * Saves stats for computed job
     *
     * @param stdClass $job
     * @param null $groupalcohort
     */
    private static function save_stats($job, &$groupalcohort = null) {
        global $DB;

        $job->matcher_used = strval($groupalcohort->whichMatcherUsed);
        $job->count_groups = floatval($groupalcohort->countOfGroups);
        $job->performance_index = floatval($groupalcohort->cohortPerformanceIndex);

        groupformation_info(null, null, $job->matcher_used . "yay");

        $stats = $groupalcohort->results;

        $job->stats_avg_variance = $stats->averageVariance;
        $job->stats_variance = $stats->variance;
        $job->stats_n = $stats->n;
        $job->stats_avg = $stats->avg;
        $job->stats_st_dev = $stats->stDev;
        $job->stats_norm_st_dev = $stats->normStDev;
        $job->stats_performance_index = $stats->performanceIndex;

        $DB->update_record('groupformation_jobs', $job);
    }

    /**
     * Creates groups
     *
     * @param stdClass$job
     * @param array $groups
     * @param array $flags
     * @return array
     */
    private static function create_groups($job, $groups, $flags) {
        $groupformationid = $job->groupformationid;

        $groupsmanager = new mod_groupformation_groups_manager ($groupformationid);

        $store = new mod_groupformation_storage_manager ($groupformationid);

        $groupnameprefix = $store->get_group_name_setting();
        $groupformationname = $store->get_name();

        $i = $store->get_instance_number();

        $groupname = "G" . $i . "_" . $groupnameprefix . "_";

        if (strlen($groupnameprefix) < 1) {
            $groupname = "G" . $i . "_" . substr($groupformationname, 0, 8) . "_";
        }

        $ids = array();
        foreach ($groups as $groupalid => $group) {
            if (count($group ['users']) > 0) {
                $name = $groupname . strval($groupalid);
                $dbid = $groupsmanager->create_group($groupalid, $group, $name, $groupformationid, $flags);
                $ids [$groupalid] = $dbid;
            }
        }

        return $ids;
    }

    /**
     * Assign users to groups
     *
     * @param stdClass $job
     * @param array $users
     * @param array $idmap
     */
    private static function assign_users_to_groups($job, $users, $idmap) {
        $groupformationid = $job->groupformationid;

        $groupsmanager = new mod_groupformation_groups_manager ($groupformationid);

        foreach ($users as $userid => $groupalid) {
            $groupsmanager->assign_user_to_group($groupformationid, $userid, $groupalid, $idmap);
        }
    }

    /**
     * Creates job for groupformation instance
     *
     * @param int $groupformationid
     * @param int $groupingid
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
     * @param int $groupformationid
     * @param int $groupingid
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
     * @return string
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
     * @return null
     */
    public static function notify_teacher($job) {
        global $DB, $CFG;
        $userid = $job->started_by;
        $rec = array_pop($DB->get_records('course_modules', array(
            'instance' => $job->groupformationid)));
        $coursemoduleid = $rec->id;
        $recipient = array_pop($DB->get_records('user', array(
            'id' => $userid)));
        $subject = get_string('groupformation_message_subject', 'groupformation');
        $message = get_string('groupformation_message', 'groupformation');
        $contexturl =
            $CFG->wwwroot . '/mod/groupformation/grouping_view.php?id=' . $coursemoduleid . '&do_show=grouping';
        $contexturlname = get_string('groupformation_message_contexturlname', 'groupformation');
        groupformation_send_message($recipient, $subject, $message, $contexturl, $contexturlname);

        return null;
    }
}
