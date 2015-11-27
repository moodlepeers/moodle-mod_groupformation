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

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Create and allocate users to groups
 * This code is extracted out of /group/autogroup.php
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');

class mod_groupformation_group_generator {

    /**
     * Generates moodle groups and sets ids in groupal generated groups
     *
     * @param int $groupformationID
     * @return boolean
     */
    public static function generate_moodle_groups($groupformationid) {
        global $COURSE;

        $groups_store = new mod_groupformation_groups_manager ($groupformationid);
        $groupal_groups = $groups_store->get_generated_groups('id', 'id, groupname,performance_index,moodlegroupid');

        if ($groups_store->groups_created()) {
            return false;
        }

        $position = 0;
        $created_moodle_groups = array();

        $error = '';
        $failed = false;

        // Allocate the users.
        foreach ($groupal_groups as $groupal_group) {

            $groupid = $groupal_group->id;
            $groupname = $groupal_group->groupname;

            $groupal_users = $groups_store->get_users_for_generated_group($groupal_group->id);

            $parsed_groupname = groups_parse_name($groupname, $position);

            if (groups_get_group_by_name($COURSE->id, $parsed_groupname)) {
                $error = get_string('groupnameexists', 'groupformation', $parsed_groupname);
                $failed = true;
                break;
            }

            // Create group.
            $new_moodlegroup = new stdClass ();
            $new_moodlegroup->courseid = $COURSE->id;
            $new_moodlegroup->name = $parsed_groupname;
            $new_moodlegroup->timecreated = time();

            $moodlegroupid = groups_create_group($new_moodlegroup);

            $created_moodle_groups [] = $moodlegroupid;
            // Put user into group.
            foreach ($groupal_users as $user) {
                groups_add_member($moodlegroupid, $user->userid);
            }

            $groups_store->save_moodlegroup_id($groupid, $moodlegroupid);

            // Invalidate the course groups cache seeing as we've changed it.
            cache_helper::invalidate_by_definition('core', 'groupdata', array(), array(
                $COURSE->id));

            if ($failed) {
                foreach ($created_moodle_groups as $groupid => $moodlegroupid) {

                    groups_delete_group($moodlegroupid);

                    $groups_store->delete_moodlegroup_id($moodlegroupid);
                }
            }
        }
    }
}

