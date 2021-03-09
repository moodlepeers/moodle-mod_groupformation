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
 * Interface for user-related activity data in DB
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright 2018 MoodlePeers
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_groupformation\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\approved_contextlist;

/**
 * Class provider
 *
 * @package mod_groupformation
 * @copyright 2018 MoodlePeers
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider {

    // This trait must be included to provide the relevant polyfill for the metadata provider.
    use \core_privacy\local\legacy_polyfill;

    /**
     * Returns meta data about this system.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
                'groupformation_answer',
                [
                        'groupformation' => 'privacy:metadata:groupformation_answer:groupformation',
                        'category' => 'privacy:metadata:groupformation_answer:category',
                        'questionid' => 'privacy:metadata:groupformation_answer:questionid',
                        'userid' => 'privacy:metadata:groupformation_answer:userid',
                        'answer' => 'privacy:metadata:groupformation_answer:answer',
                        'timestamp' => 'privacy:metadata:groupformation_answer:timestamp'
                ],
                'privacy:metadata:groupformation_answer'
        );
        $collection->add_database_table(
                'groupformation_groups',
                [
                        'groupformation' => 'privacy:metadata:groupformation_groups:groupformation',
                        'groupname' => 'privacy:metadata:groupformation_groups:groupname',
                        'group_size' => 'privacy:metadata:groupformation_groups:group_size',
                ],
                'privacy:metadata:groupformation_groups'
        );
        $collection->add_database_table(
                'groupformation_group_users',
                [
                        'groupformation' => 'privacy:metadata:groupformation_group_users:groupformation',
                        'userid' => 'privacy:metadata:groupformation_group_users:userid',
                        'groupid' => 'privacy:metadata:groupformation_group_users:groupid'
                ],
                'privacy:metadata:groupformation_group_users'
        );
        $collection->add_database_table(
                'groupformation_users',
                [
                        'groupformation' => 'privacy:metadata:groupformation_users:groupformation',
                        'userid' => 'privacy:metadata:groupformation_users:userid',
                        'completed' => 'privacy:metadata:groupformation_users:completed',
                        'timecompleted' => 'privacy:metadata:groupformation_users:timecompleted',
                        'consent' => 'privacy:metadata:groupformation_users:consent',
                        'participantcode' => 'privacy:metadata:groupformation_users:participantcode'
                ],
                'privacy:metadata:groupformation_users'
        );
        $collection->add_database_table(
                'groupformation_user_values',
                [
                        'groupformationid' => 'privacy:metadata:groupformation_user_values:groupformationid',
                        'userid' => 'privacy:metadata:groupformation_user_values:userid',
                        'criterion' => 'privacy:metadata:groupformation_user_values:criterion',
                        'label' => 'privacy:metadata:groupformation_user_values:label',
                        'dimension' => 'privacy:metadata:groupformation_user_values:dimension',
                        'value' => 'privacy:metadata:groupformation_user_values:value'
                ],
                'privacy:metadata:groupformation_user_values'
        );
        $collection->add_database_table(
            'groupformation_logging',
            [
                'timestamp' => 'privacy:metadata:groupformation_logging:timestamp',
                'userid' => 'privacy:metadata:groupformation_logging:userid',
                'groupformationid' => 'privacy:metadata:groupformation_logging:groupformationid',
                'message' => 'privacy:metadata:groupformation_logging:message',
            ],
            'privacy:metadata:groupformation_logging'
        );
        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $params = [
                'modname' => 'groupformation',
                'contextlevel' => CONTEXT_MODULE,
                'userid' => $userid
        ];

        $sql = "SELECT DISTINCT c.id
                FROM {context} c
                INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                INNER JOIN {groupformation} g ON g.id = cm.instance
                LEFT JOIN {groupformation_users} u ON u.groupformation = g.id
                    WHERE (
                    u.userid = :userid
                    )";

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Returns user values of user
     *
     * @param int $userid
     * @param int $groupformationid
     * @return array
     * @throws \dml_exception
     */
    private static function get_user_values(int $userid, int $groupformationid) {
        global $DB;

        $uservalues = $DB->get_records('groupformation_user_values',
                array(
                        "groupformationid" => $groupformationid,
                        "userid" => $userid,
                ),
                "id",
                "id, criterion, label, dimension, value"
        );

        return array_values($uservalues);

    }

    /**
     * Returns user data of user
     *
     * @param int $userid
     * @param int $groupformationid
     * @return mixed
     * @throws \dml_exception
     */
    private static function get_user_data(int $userid, int $groupformationid) {
        global $DB;

        $userdata = $DB->get_record('groupformation_users', array(
                "userid" => $userid,
                "groupformation" => $groupformationid,
        ), "completed, timecompleted, consent, participantcode"
        );

        $userdata->timecompleted = date('Y-m-d H:i:s', $userdata->timecompleted);

        return $userdata;

    }

    /**
     * Returns group data of user
     *
     * @param int $userid
     * @param int $groupformationid
     * @return \stdClass
     * @throws \dml_exception
     */
    private static function get_group(int $userid, int $groupformationid) {
        global $DB;

        if ($groupid = $DB->get_field(
                'groupformation_group_users',
                'groupid',
                array('groupformation' => $groupformationid, 'userid' => $userid)
        )) {

            $group = $DB->get_record('groupformation_groups',
                    array("id" => $groupid),
                    "id, groupname, group_size, topic_name"
            );

            if (is_null($group->topic_name)) {
                unset($group->topic_name);
            }
            return $group;
        } else {
            return null;
        }
    }

    /**
     * Returns answers of user
     *
     * @param int $userid
     * @param int $groupformationid
     * @return array
     * @throws \dml_exception
     */
    private static function get_answers(int $userid, int $groupformationid) {
        global $DB;

        $sql = 'SELECT
                  q.id as id, a.category as category, a.questionid as qid, q.question as question, a.answer as answer, a.timestamp
                FROM {groupformation_answers} a
                JOIN
                  (SELECT * FROM {groupformation_questions} qe WHERE qe.language = "en") AS q
                  ON
                        a.category = q.category
                    AND
                        a.questionid = q.questionid
                WHERE (
                        a.userid = :userid
                    AND
                        a.groupformation = :groupformationid
                )
            ';

        $params = array(
                'userid' => $userid,
                "groupformationid" => $groupformationid
        );

        $myanswers = $DB->get_records_sql($sql, $params);

        return array_values($myanswers);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT
                    c.id AS contextid,
                    cm.id AS cmid,
                    g.id AS gid,
                    g.name AS name
                FROM {context} c
                JOIN {course_modules} cm ON cm.id = c.instanceid
                JOIN {groupformation} g ON g.id = cm.instance
                WHERE (
                    c.id {$contextsql}
                )
        ";

        $mappings = [];

        $groupformations = $DB->get_recordset_sql($sql, $contextparams);
        foreach ($groupformations as $groupformation) {

            $groupformationid = $groupformation->gid;
            $mappings[$groupformationid] = $groupformation->contextid;

            $context = \context::instance_by_id($mappings[$groupformationid]);

            $groupformation->answers = self::get_answers($userid, $groupformationid);
            $groupformation->user_data = self::get_user_data($userid, $groupformationid);
            $groupformation->group = self::get_group($userid, $groupformationid);
            $groupformation->user_values = self::get_user_values($userid, $groupformationid);

            writer::with_context($context)->export_data([], $groupformation);
        }
        $groupformations->close();
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != COURSE_MODULE) {
            return;
        }

        $groupformationid = $context->instanceid;

        $cm = get_coursemodule_from_id('groupformation', $groupformationid);
        if (!$cm) {
            return;
        }

        $DB->delete_records('groupformation_users', ['groupformation' => $groupformationid]);
        $DB->delete_records('groupformation_user_values', ['groupformationid' => $groupformationid]);
        $DB->delete_records('groupformation_answers', ['groupformation' => $groupformationid]);
        $DB->delete_records('groupformation_groups', ['groupformation' => $groupformationid]);
        $DB->delete_records('groupformation_group_users', ['groupformation' => $groupformationid]);
        $DB->delete_records('groupformation_jobs', ['groupformationid' => $groupformationid]);
        $DB->delete_records('groupformation_stats', ['groupformationid' => $groupformationid]);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws \dml_exception
     */
    public static function delete_data_for_users(approved_userlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {

            $groupformationid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIT);

            $DB->delete_records('groupformation_users', ['groupformation' => $groupformationid, 'userid' => $userid]);
            $DB->delete_records('groupformation_user_values', ['groupformationid' => $groupformationid, 'userid' => $userid]);
            $DB->delete_records('groupformation_answers', ['groupformation' => $groupformationid, 'userid' => $userid]);
            $DB->delete_records('groupformation_logging', ['groupformationid' => $groupformationid, 'userid' => $userid]);

            if ($groupid = $DB->get_field(
                    'groupformation_group_users',
                    'groupid',
                    array(
                            'groupformation' => $groupformationid,
                            'userid' => $userid
                    )
            )) {

                $DB->delete_records('groupformation_group_users', ['groupformation' => $groupformationid, 'userid' => $userid]);
                $group = $DB->get_record('groupformation_groups', ['id' => $groupid]);

                $group->group_size = max($group->group_size - 1, 0);

                if ($group->group_size == 0) {
                    $DB->delete_records('groupformation_groups', ['id' => $groupid]);
                } else {
                    $DB->update_record('groupformation_groups', $group);
                }
            }
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $params = [
            'modname' => 'groupformation',
            'contextid' => $context->id,
            'contextuser' => CONTEXT_USER,
        ];

        $sql = "SELECT DISTINCT u.userid
                  FROM {context} c
                  JOIN {course_modules} cm
                  JOIN {modules} m
                  JOIN {groupformation} g
             LEFT JOIN {groupformation_users} u
                 WHERE cm.id = c.instanceid
                   AND c.contextlevel = :contextlevel
                   AND m.id = cm.module
                   AND m.name = :modname
                   AND g.id = cm.instance
                   AND u.groupformation = g.id
                   AND c.id = :contextid";

        $userlist->add_from_sql('userid', $sql, $params);
    }
}
