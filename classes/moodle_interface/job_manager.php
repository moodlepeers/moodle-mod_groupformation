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
 * Handles job
 *
 * @package mod_groupformation
 * @author Rene Roepke, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // / It must be included from a Moodle page
}
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');

require_once($CFG->dirroot . '/lib/groupal/classes/Criteria/specific_criterion.php');
require_once($CFG->dirroot . '/lib/groupal/classes/participant.php');
require_once($CFG->dirroot . '/lib/groupal/classes/cohort.php');
require_once($CFG->dirroot . '/lib/groupal/classes/Matcher/group_centric_matcher.php');
require_once($CFG->dirroot . '/lib/groupal/classes/algorithms/basic_algorithm.php');
require_once($CFG->dirroot . '/lib/groupal/classes/algorithms/random_algorithm.php');
require_once($CFG->dirroot . '/lib/groupal/classes/algorithms/topic_algorithm.php');
require_once($CFG->dirroot . '/lib/groupal/classes/Optimizer/optimizer.php');
require_once($CFG->dirroot . '/lib/groupal/classes/xml_writer/participant_writer.php');
require_once($CFG->dirroot . '/lib/groupal/classes/xml_writer/cohort_writer.php');

class mod_groupformation_job_manager {

    /**
     * Selects next job and sets it on "started"
     *
     * @return Ambigous <>
     */
    public static function get_next_job() {
        global $DB;
        $jobs = $DB->get_records('groupformation_jobs', array(
            'waiting' => 1,
            'started' => 0,
            'aborted' => 0,
            'done' => 0
        ));

        if (count($jobs) == 0)
            return null;

        $next = null;

        foreach ($jobs as $id => $job) {
            if ($job->timecreated != null && ($next == null || $job->timecreated < $next->timecreated))
                $next = $job;
        }

        self::set_job($next, "started", true);

        groupformation_info(null, $next->groupformationid, 'groupal job with groupformation id="' . $next->groupformationid . '" selected');

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
            'waiting' => 0,
            'started' => 0,
            'aborted' => 1,
            'done' => 0,
            'timestarted' => 0
        ));

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
        groupformation_info(null, $job->groupformationid, 'groupal job with groupformation id="' . $job->groupformationid . '" resetted');
    }

    /**
     *
     * Sets job to state e.g. 1000
     *
     * @param stdClass $job
     * @param string $state
     */
    public static function set_job($job, $state = "ready", $settime = false, $resettime = false, $groupingid = null) {
        global $DB, $USER;
        $status_options = self::get_status_options();

        if (array_key_exists($state, $status_options))
            $status = $status_options [$state];
        else
            $status = $state;
        if (!(preg_match("/[0-1]{4}/", $status) && strlen($status) == 4))
            return false;

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
     *
     * Checks whether job is aborted or not
     *
     * @param stdClass $job
     * @return boolean
     */
    public static function is_job_aborted($job) {
        global $DB;

        return $DB->get_field('groupformation_jobs', 'aborted', array(
            'id' => $job->id
        )) == '1';
    }

    /**
     * Returns status options placed in define file
     */
    private static function get_status_options() {
        $data = new mod_groupformation_data ();
        return $data->get_job_status_options();
    }

    /**
     *
     * @param unknown $groupformationid
     * @return NULL|multitype:multitype: Ambigous <multitype:, unknown>
     */
    private static function get_users($job, $groupformationid, mod_groupformation_storage_manager $store) {
        global $CM;
        $courseid = $store->get_course_id();
        $context = context_course::instance($courseid);

        $enrolled_students = null;

        if (intval($job->groupingid) != 0) {
            $enrolled_students = array_keys(groups_get_grouping_members($job->groupingid));
            // foreach ( $userids as $userid ) {
            // var_dump ( $userid );
            // }
        } else {
            // TODO all enrolled students later just students of grouping
            $enrolled_students = array_keys(get_enrolled_users($context, 'mod/groupformation:onlystudent'));
        }
        if (is_null($enrolled_students) || count($enrolled_students) <= 0)
            return null;

        $user_manager = new mod_groupformation_user_manager ($groupformationid);

        $grouping_setting = $store->get_grouping_setting();

        $all_answers = array();
        $some_answers = array();
        $no_or_some_answers = array();
        $no_answers = array();

        foreach ($enrolled_students as $userid) {
            if ($user_manager->is_completed($userid)) {
                $all_answers [] = $userid;
            } elseif ($grouping_setting) {
                $some_answers [] = $userid;
            } else {
                $no_or_some_answers [] = $userid;
            }
        }

        // $all_answers = array_keys ( $user_manager->get_completed_by_answer_count ( null, 'userid' ) );

        // $some_answers = array_keys ( $user_manager->get_not_completed_by_answer_count ( null, 'userid' ) );

        // $diff = array_diff ( $enrolled_students, $all_answers );
        // $no_or_some_answers = array_unique ( array_merge ( $diff, $some_answers ) );

        // $no_answers = array_diff ( $no_or_some_answers, $some_answers );

        $groupal_users = $all_answers;

        if ($store->get_grouping_setting()) {
            $random_users = $some_answers;
        } else {
            $random_users = $no_or_some_answers;
        }

        return array(
            $groupal_users,
            $random_users
        );
    }

    /**
     * Determines the group size for groupal and random algorithm
     *
     * @param array $users
     * @param mod_groupformation_storage_manager $store
     * @return multitype:NULL number |NULL|multitype:unknown |multitype:unknown number
     */
    private static function determine_group_size($users, mod_groupformation_storage_manager $store, $groupformationid = null) {
        if ($store->ask_for_topics()) {
            $size_array = array(
                5,
                5
            );
            $group_option = $store->get_group_option();
            if ($group_option) {
                $max_groups = intval($store->get_max_groups());
                $topicvalues = $store->get_knowledge_or_topic_values('topic');
                $topicvalues = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $topicvalues . ' </OPTIONS>';
                $topics_options = mod_groupformation_util::xml_to_array($topicvalues);
                $topics_count = count($topics_options);

                // var_dump ( $topics_options, $topics_count );

                $users_count0 = count($users [0] + $users [1]);
                // var_dump ( $users_count0 );
                $ratio0 = $users_count0 / $max_groups;

                $base_group_size = floor($ratio0);

                // var_dump ( $ratio0, $base_group_size );

                $covered_users_count = $base_group_size * $max_groups;
                $remaining_users_count = $users_count0 - $covered_users_count;
                // var_dump ( $covered_users_count, $remaining_users_count );

                $user_manager = new mod_groupformation_user_manager ($groupformationid);

                $topics = $user_manager->get_most_common_topics($topics_count);

                $result = array();

                $i = 0;
                foreach ($topics as $key => $topic) {
                    if ($i < $remaining_users_count) {
                        $result [intval($topic ['id']) - 1] = intval(round($base_group_size + 1));
                    } else {
                        $result [intval($topic ['id']) - 1] = intval(round($base_group_size));
                    }
                    $i++;
                }

                // var_dump ( $result );
                return $result;
            } else {
                $max_members = intval($store->get_max_members());
                return null;
            }
            return $size_array;
        } else {

            $users_count0 = count($users [0]);
            $users_count1 = count($users [1]);
            $users_count = $users_count0 + $users_count1;

            if ($users_count <= 0) {
                return null;
            }
            $group_option = $store->get_group_option();
            if ($group_option) {
                $max_groups = intval($store->get_max_groups());

                // var_dump("users_count = ".$users_count.", group_number = ".$group_number);
                if ($users_count0 == 0) {
                    return array(
                        null,
                        intval(ceil($users_count1 / $max_groups))
                    );
                } elseif ($users_count1 == 0) {
                    return array(
                        intval(ceil($users_count0 / $max_groups)),
                        null
                    );
                }

                $optimal_size = ceil($users_count / $max_groups);

                $optimal_size0 = $optimal_size;
                $optimal_size1 = $optimal_size;

                $check0 = false;
                $check1 = false;

                $ratio0 = $users_count0 / $users_count;
                $ratio1 = $users_count1 / $users_count;

                $group_number0 = round($ratio0 * $max_groups);
                $group_number1 = round($ratio1 * $max_groups);

                if ($group_number0 + $group_number1 > $max_groups) {
                    if ($users_count0 > $users_count1) {
                        $group_number0--;
                    } else {
                        $group_number1--;
                    }
                }

                if ($group_number0 == 0) {
                    $group_number0 = $group_number0 + 1;
                    $group_number1 = $group_number1 - 1;
                } elseif ($group_number1 == 0) {
                    $group_number0 = $group_number0 - 1;
                    $group_number1 = $group_number1 + 1;
                } elseif ($max_groups == 2) {
                    $group_number0 = 1;
                    $group_number1 = 1;
                }

                do {
                    $cond = ($group_number0 * $optimal_size0 > $users_count0) || ($optimal_size0 > $users_count0) || ($users_count0 % $optimal_size0 == 0);
                    if ($cond) {
                        $check0 = true;
                    } else {
                        $optimal_size0++;
                    }
                } while (!$check0);

                do {
                    $cond = ($group_number1 * $optimal_size1 > $users_count1) || ($optimal_size1 > $users_count1) || ($users_count1 % $optimal_size1 == 0);
                    if ($cond) {
                        $check1 = true;
                    } else {
                        $optimal_size1++;
                    }
                } while (!$check1);

                $base_group_size = $optimal_size0;
                $group_size1 = $optimal_size1;

                $cond = $max_groups < (ceil($users_count0 / $base_group_size) + ceil($users_count1 / $group_size1));
                if ($cond) {
                    return null;
                }
                return array(
                    $base_group_size,
                    $group_size1
                );
            } else {
                $max_members = intval($store->get_max_members());
                return array(
                    $max_members,
                    $max_members
                );
            }
        }
    }

    /**
     * Runs topic-based algorithm
     *
     * @param unknown $job
     * @param unknown $users
     */
    private static function run_topic_algorithm($job, $users, $store) {
        global $CFG;
        $groupformationid = $job->groupformationid;

        $groupal_cohort = null;
        $random_cohort = null;
        $topic_cohort = null;

        $cohorts = array(
            $groupal_cohort,
            $random_cohort,
            $topic_cohort
        );

        $group_sizes = self::determine_group_size($users, $store, $groupformationid);
        ksort($group_sizes);

        // var_dump ( $group_sizes );
        // In $group_sizes is an associative array where the key is 0 - (n-1) [id of topic]
        // and the value is the group size of each topic
        // var_dump ( $group_sizes );

        $topic_users = $users [0];
        $incomplete_users = $users [1];

        // Build participants
        $pp = new mod_groupformation_participant_parser ($groupformationid);
        $topic_participants = $pp->build_topic_participants($topic_users);
        $random_participants = $pp->build_empty_participants($incomplete_users);

        if (count($topic_participants) > 0) {
            $starttime = microtime(true);

            lib_groupal_group::setGroupMembersMaxSize(max($group_sizes));

            $gfa = new lib_groupal_topic_algorithm ($group_sizes, $topic_participants);
            $topic_cohort = $gfa->doOneFormation(); // this call takes time...

            $endtime = microtime(true);
            $comptime = $endtime - $starttime;

            groupformation_info(null, $job->groupformationid, 'groupal needed ' . $comptime . 'ms');
        }
        if (!is_null($topic_cohort)) {
            // var_dump($random_participants);
            // now we have to add the remaining participants

            $size = ceil((count($users [0]) + count($users [1])) / count($topic_cohort->groups));
			// var_dump($size);
			lib_groupal_group::setGroupMembersMaxSize($size);
			
			$counts = array();
			$max = null;
			foreach ($topic_cohort->groups as $group) {
                $value = count($group->getParticipants());
                $groups [] = array(
                    'id' => $group->getID(),
                    'count' => $value,
                    'group' => $group,
                    'participants' => array()
                );
                if ($max == null || $max < $value) {
                    $max = $value;
                }
            }
			usort($groups, function ($a, $b) {
                return $a ['count'] - $b ['count'];
            });
			$groups = array_slice($groups, 0, count($groups));
			for ($i = 0; $i < count($random_participants); $i++) {
                usort($groups, function ($a, $b) {
                    return $a ['count'] - $b ['count'];
                });
                $groups = array_slice($groups, 0, count($groups));

                $p = $random_participants [$i];
                $groups [0] ['group']->addParticipant($p, true);
                $groups [0] ['count']++;
            }
			
			usort($groups, function ($a, $b) {
                return $a ['count'] - $b ['count'];
            });
		} else {
            // pure random groups because no answers
            $max = max($group_sizes);
            $gfra = new lib_groupal_random_algorithm ($random_participants, $max);
            $random_cohort = $gfra->doOneFormation();
        }

        // if (count ( $random_participants ) > 0) {
        // $gfra = new lib_groupal_random_algorithm ( $random_participants, $group_size [1] );
        // $random_cohort = $gfra->doOneFormation ();
        // }

        $cohorts = array(
            $groupal_cohort,
            $random_cohort,
            $topic_cohort
        );

        return $cohorts;
    }

    /**
     * Runs basic groupal-based algorithm
     *
     * @param unknown $job
     * @param unknown $users
     */
    private static function run_basic_algorithm($job, $users, $store) {
        global $CFG;

        $groupformationid = $job->groupformationid;

        $groupal_cohort = null;
        $random_cohort = null;
        $topic_cohort = null;

        $cohorts = array(
            $groupal_cohort,
            $random_cohort,
            $topic_cohort
        );

        $store = new mod_groupformation_storage_manager ($groupformationid);

        // Determine group sizes
        $group_size = self::determine_group_size($users, $store);

        $groupal_users = $users [0];
        $incomplete_users = $users [1];

        // Build participants
        $pp = new mod_groupformation_participant_parser ($groupformationid);
        $groupal_participants = $pp->build_participants($groupal_users);
        $random_participants = $pp->build_empty_participants($incomplete_users);
        if (count($groupal_participants) > 0) {

            // TODO Choose matcher
            $matcher = new lib_groupal_group_centric_matcher ();

            $starttime = microtime(true);

            $gfa = new lib_groupal_basic_algorithm ($groupal_participants, $matcher, $group_size [0]);
            $groupal_cohort = $gfa->doOneFormation(); // this call takes time...

            $endtime = microtime(true);
            $comptime = $endtime - $starttime;

            groupformation_info(null, $job->groupformationid, 'groupal needed ' . $comptime . 'ms');
        }

        if (count($random_participants) > 0) {
            $gfra = new lib_groupal_random_algorithm ($random_participants, $group_size [1]);
            $random_cohort = $gfra->doOneFormation();
        }

        $cohorts = array(
            $groupal_cohort,
            $random_cohort,
            $topic_cohort
        );

        // TODO XML WRITER : einkommentieren falls benötigt
        // $path = $CFG->dirroot . '/mod/groupformation/xml_participants/' . "php_" . $groupformationid;
        // $participant_writer = new lib_groupal_participant_writer ( $path . "_participants.xml" );
        // $participant_writer->write ( $groupal_participants );

        // TODO XML WRITER : einkommentieren falls benötigt
        // $path = $CFG->dirroot . '/mod/groupformation/xml_participants/' . "php_" . $groupformationid;
        // $cohort_writer = new lib_groupal_cohort_writer($path."_cohort.xml");
        // $cohort_writer->write($groupal_cohort);

        return $cohorts;
    }

    /**
     * Runs groupal with job
     *
     * @param stdClass $job
     * @return array with 3 elements: groupal cohorts, random cohort and incomplete random cohort
     */
    public static function do_groupal($job) {
        global $CFG;

        $cohorts = array(
            null,
            null,
            null
        );

        $groupformationid = $job->groupformationid;

        $store = new mod_groupformation_storage_manager ($groupformationid);

        // Assign users
        $users = self::get_users($job, $groupformationid, $store);
        // var_dump ( $users );

        if (is_null($users)) {
            return $cohorts;
        }

        if ($store->ask_for_topics()) {
            // --- topic groupal --- version ---
            $cohorts = self::run_topic_algorithm($job, $users, $store);
        } else {
            // --- basic groupal --- version ---
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
        global $DB;

        $groupal_cohort = $result [0];
        $random_cohort = $result [1];
        $topic_cohort = $result [2];

        if (!is_null($groupal_cohort)) {

            $result = $groupal_cohort->getResult();

            $flags = array(
                "groupal" => 1,
                "random" => 0,
                "mrandom" => 0,
                "created" => 0,
                "topic" => 0
            );

            $idmap = self::create_groups($job, $result->groups, $flags);

            self::assign_users_to_groups($job, $result->users, $idmap);

            self::save_stats($job, $groupal_cohort);
        }

        if (!is_null($random_cohort)) {
            $result = $random_cohort->getResult();

            $flags = array(
                "groupal" => 0,
                "random" => 1,
                "mrandom" => 0,
                "created" => 0,
                "topic" => 0
            );

            $idmap = self::create_groups($job, $result->groups, $flags);

            self::assign_users_to_groups($job, $result->users, $idmap);
        }

        if (!is_null($topic_cohort)) {
            $result = $topic_cohort->getResult();

            $flags = array(
                "groupal" => 0,
                "random" => 1,
                "mrandom" => 0,
                "created" => 0,
                "topic" => 1
            );

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
     * @param unknown $job
     * @param unknown $cohort
     */
    private static function save_stats($job, &$groupal_cohort = null) {
        global $DB;

        $job->matcher_used = strval($groupal_cohort->whichMatcherUsed);
        $job->count_groups = floatval($groupal_cohort->countOfGroups);
        $job->performance_index = floatval($groupal_cohort->cohortPerformanceIndex);

        groupformation_info(null, null, $job->matcher_used . "yay");

        $stats = $groupal_cohort->results;

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
     * Creates groups generated by GroupAL
     *
     * @param stdClass $job
     * @param unknown $groupids
     * @return boolean
     */
    private static function create_groups($job, $groups, $flags) {
        $groupformationid = $job->groupformationid;

        $groups_store = new mod_groupformation_groups_manager ($groupformationid);

        $store = new mod_groupformation_storage_manager ($groupformationid);

        $groupname_prefix = $store->get_group_name_setting();
        $groupformationname = $store->get_name();

        $groupname = "";
        $i = $store->get_instance_number();

        if (strlen($groupname_prefix) < 1) {
            $groupname = "G" . $i . "_" . substr($groupformationname, 0, 8) . "_";
        } else {
            $groupname = "G" . $i . "_" . $groupname_prefix . "_";
        }

        $ids = array();
        foreach ($groups as $groupalid => $group) {
            if (count($group ['users']) > 0) {
                $name = $groupname . strval($groupalid);
                $db_id = $groups_store->create_group($groupalid, $group, $name, $groupformationid, $flags);
                $ids [$groupalid] = $db_id;
            }
        }

        return $ids;
    }

    /**
     *
     * Assign users to groups
     *
     * @param stdClass $job
     * @param unknown $users
     * @param unknown $idmap
     */
    private static function assign_users_to_groups($job, $users, $idmap) {
        $groupformationid = $job->groupformationid;

        $groups_store = new mod_groupformation_groups_manager ($groupformationid);

        foreach ($users as $userid => $groupalid) {
            $groups_store->assign_user_to_group($groupformationid, $userid, $groupalid, $idmap);
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
            'groupformationid' => $groupformationid
        ))
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
            'groupformationid' => $groupformationid
        ))
        ) {
            return $DB->get_record('groupformation_jobs', array(
                'groupformationid' => $groupformationid
            ));
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
        $data = new mod_groupformation_data ();
        $status_options = array_keys($data->get_job_status_options());
        if ($job->waiting) {
            return $status_options [1];
        } elseif ($job->started) {
            return $status_options [2];
        } elseif ($job->aborted) {
            return $status_options [3];
        } elseif ($job->done) {
            return $status_options [4];
        } else {
            return $status_options [0];
        }
    }

    /**
     * Notifies teacher about terminated groupformation job
     *
     * @param stdClass $job
     * @return NULL
     */
    public static function notify_teacher($job) {
        global $DB, $CFG;
        // TODO messaging to person:
        $uID = $job->started_by;
        $rec = array_pop($DB->get_records('course_modules', array(
            'instance' => $job->groupformationid
        )));
        $course_module_id = $rec->id;
        $recipient = array_pop($DB->get_records('user', array(
            'id' => $uID
        )));
        $subject = get_string('groupformation_message_subject', 'groupformation');
        $message = get_string('groupformation_message', 'groupformation');
        $contexturl = $CFG->wwwroot . '/mod/groupformation/grouping_view.php?id=' . $course_module_id . '&do_show=grouping';
        $contexturlname = get_string('groupformation_message_contexturlname', 'groupformation');
        groupformation_send_message($recipient, $subject, $message, $contexturl, $contexturlname);

        return null;
    }
}
