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
 * Create and allocate users to groups
 * This code is extracted out of /group/autogroup.php
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');

class mod_groupformation_group_generator {

    /**
     * Generates moodle groups and sets ids in groupal generated groups
     *
     * @param int $groupformationid
     * @return boolean
     */
    public static function generate_moodle_groups($groupformationid) {
        global $DB;

        $courseid = $DB->get_field('groupformation', 'course', array(
                'id' => $groupformationid)
        );

        $groupsmanager = new mod_groupformation_groups_manager ($groupformationid);
        $groupalgroups = $groupsmanager->get_generated_groups('id', 'id, groupname,performance_index,moodlegroupid');

        if ($groupsmanager->groups_created()) {
            return false;
        }

        $position = 0;
        $createdmoodlegroups = array();

        $error = '';
        $failed = false;

        // Allocate the users.
        foreach ($groupalgroups as $groupalgroup) {

            $groupid = $groupalgroup->id;
            $groupname = $groupalgroup->groupname;

            $groupalusers = $groupsmanager->get_users_for_generated_group($groupalgroup->id);

            $parsedgroupname = groups_parse_name($groupname, $position);

            if (groups_get_group_by_name($courseid, $parsedgroupname)) {
                $error = get_string('groupnameexists', 'groupformation', $parsedgroupname);
                $failed = true;
                break;
            }

            // Create group.
            $newmoodlegroup = new stdClass ();
            $newmoodlegroup->courseid = $courseid;
            $newmoodlegroup->name = $parsedgroupname;
            $newmoodlegroup->timecreated = time();

            $moodlegroupid = groups_create_group($newmoodlegroup);

            $createdmoodlegroups [] = $moodlegroupid;
            // Put user into group.
            foreach ($groupalusers as $user) {
                groups_add_member($moodlegroupid, $user->userid);
            }

            $groupsmanager->save_moodlegroup_id($groupid, $moodlegroupid);

            // Invalidate the course groups cache seeing as we've changed it.
            cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($courseid));

            if ($failed) {
                foreach ($createdmoodlegroups as $groupid => $moodlegroupid) {

                    groups_delete_group($moodlegroupid);

                    $groupsmanager->delete_moodlegroup_id($moodlegroupid);
                }
            }
        }
    }
}

