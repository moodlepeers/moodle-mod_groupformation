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
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_groupformation\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use \core_privacy\local\metadata\provider as metadataprovider;
use core_privacy\local\request\context;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\plugin\provider as pluginprovider;
use \core_privacy\local\request\user_preference_provider as preference_provider;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\transform;
use \core_privacy\local\request\helper;
use \core_privacy\manager;

class provider implements metadataprovider, pluginprovider{

    /**
     * Returns meta data about this system.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        // TODO: Implement get_metadata() method.
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
                        'moodlegroupid' => 'privacy:metadata:groupformation_groups:moodlegroupid',
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
                'groupformation_started',
                [
                        'groupformation' => 'privacy:metadata:groupformation_started:groupformation',
                        'userid' => 'privacy:metadata:groupformation_started:userid',
                        'completed' => 'privacy:metadata:groupformation_started:completed',
                        'timecompleted' => 'privacy:metadata:groupformation_started:timecompleted',
                        'groupid' => 'privacy:metadata:groupformation_started:groupid',
                        'answer_count' => 'privacy:metadata:groupformation_started:answer_count',
                        'consent' => 'privacy:metadata:groupformation_started:consent',
                        'participantcode' => 'privacy:metadata:groupformation_started:participantcode',
                        'filtered' => 'privacy:metadata:groupformation_started:filtered'
                ],
                'privacy:metadata:groupformation_started'
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

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // TODO: Implement get_contexts_for_userid() method.
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        // TODO: Implement export_user_data() method.
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        // TODO: Implement delete_data_for_all_users_in_context() method.
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // TODO: Implement delete_data_for_user() method.
    }
}