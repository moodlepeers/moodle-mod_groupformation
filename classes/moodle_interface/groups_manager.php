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
 * Interface betweeen DB and Plugin
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');

/**
 * Groups manager class
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_groups_manager {

    /** @var int id of groupformation*/
    private $groupformationid;

    /** @var mod_groupformation_storage_manager Storage manager */
    private $store = null;

    /**
     * Constructs storage manager for a specific groupformation
     *
     * @param int $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
    }

    /**
     * Creats user-group instance in DB
     *
     * @param $groupformationid
     * @param $userid
     * @param $groupalid
     * @param $idmap
     * @return bool|int
     */
    public function assign_user_to_group($groupformationid, $userid, $groupalid, $idmap) {
        global $DB;

        $record = new stdClass ();
        $record->groupformation = $groupformationid;
        $record->userid = $userid;
        $record->groupid = $idmap [$groupalid];

        return $DB->insert_record('groupformation_group_users', $record);
    }

    /**
     * Returns topic name
     *
     * @param $id
     * @return mixed
     */
    public function get_topic_name($id) {
        if (is_null($this->store)) {
            $this->store = new mod_groupformation_storage_manager ($this->groupformationid);
        }
        $temp = $this->store->get_knowledge_or_topic_values('topic');
        $temp = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
        $options = mod_groupformation_util::xml_to_array($temp);

        return $options [$id - 1];
    }

    /**
     * Creates group instance in DB
     *
     * @param $groupalid
     * @param $group
     * @param $name
     * @param $groupformationid
     * @param $flags
     * @return bool|int
     */
    public function create_group($groupalid, $group, $name, $groupformationid, $flags) {
        global $DB;

        $record = new stdClass ();
        $record->groupformation = $groupformationid;
        $record->moodlegroupid = null;
        $record->groupname = $name;
        $record->performance_index = $group ['gpi'];
        $record->groupal = $flags ['groupal'];
        $record->random = $flags ['random'];
        $record->mrandom = $flags ['random'];
        $record->created = $flags ['created'];
        $record->group_size = count($group['users']);
        if ($flags ['topic']) {
            $record->topic_id = $groupalid;
            $record->topic_name = $this->get_topic_name($groupalid);
        }
        $id = $DB->insert_record('groupformation_groups', $record);

        return $id;
    }

    /**
     * Returns whether groups are created in moodle or not
     *
     * @return bool
     */
    public function groups_created() {
        global $DB;
        $records = $DB->get_records('groupformation_groups', array(
            'groupformation' => $this->groupformationid));

        foreach ($records as $key => $record) {
            if ($record->created == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resets moodle groupids
     *
     * @param int $moodlegroupid
     * @return boolean
     */
    public function delete_moodlegroup_id($moodlegroupid) {
        global $DB;

        $record = $DB->get_record('groupformation_groups', array(
            'groupformation' => $this->groupformationid, 'moodlegroupid' => $moodlegroupid));

        $record->moodlegroupid = null;
        $record->created = 0;

        return $DB->update_record('groupformation_groups', $record);
    }

    /**
     * Saves moodlegroupid in database
     *
     * @param int $groupid
     * @param int $moodlegroupid
     * @return bool
     */
    public function save_moodlegroup_id($groupid, $moodlegroupid) {
        global $DB;

        $record = $DB->get_record('groupformation_groups', array(
            'groupformation' => $this->groupformationid, 'id' => $groupid));
        $record->moodlegroupid = $moodlegroupid;
        $record->created = 1;

        return $DB->update_record('groupformation_groups', $record);
    }

    /**
     * Returns groupname
     *
     * @param int $userid
     * @return mixed
     */
    public function get_group_name($userid) {
        global $DB;
        $groupid = $DB->get_field('groupformation_group_users', 'groupid', array(
            'groupformation' => $this->groupformationid, 'userid' => $userid));

        return $DB->get_field('groupformation_groups', 'groupname', array(
            'groupformation' => $this->groupformationid, 'id' => $groupid));
    }

    /**
     * Returns members (userids) of group of user
     *
     * @param int $userid
     * @return array
     */
    public function get_group_members($userid) {
        global $DB;

        $array = array();
        $groupid = $this->get_group_id($userid);
        $records = $DB->get_records('groupformation_group_users', array(
            'groupformation' => $this->groupformationid, 'groupid' => $groupid));
        foreach ($records as $record) {
            $id = $record->userid;
            if ($id != $userid) {
                $array [] = $id;
            }
        }

        return $array;
    }

    /**
     * Returns whether user has a group or not
     *
     * @param int $userid
     * @param bool $moodlegroup
     * @return bool
     */
    public function has_group($userid, $moodlegroup = false) {
        global $DB;
        $count = $DB->count_records('groupformation_group_users', array(
            'groupformation' => $this->groupformationid, 'userid' => $userid));

        return ($count == 1);
    }

    /**
     * Returns group id for user
     *
     * @param int $userid
     * @return mixed
     */
    public function get_group_id($userid) {
        global $DB;

        return $DB->get_field('groupformation_group_users', 'groupid', array(
            'groupformation' => $this->groupformationid, 'userid' => $userid));
    }

    /**
     * Returns whether groups are build in moodle or just generated by GroupAL
     *
     * @return boolean
     */
    public function is_build() {
        global $DB;
        $table = 'groupformation_groups';
        $count = $DB->count_records($table, array(
            'groupformation' => $this->groupformationid, 'created' => 1));

        return $count > 0;
    }

    /**
     * Returns max group size
     *
     * @return int
     */
    public function get_max_groups_size() {
        global $DB;
        $groups = $this->get_generated_groups('id', 'id,group_size');
        $max = null;
        foreach ($groups as $group) {
            if (is_null($max) || $max < $group->group_size) {
                $max = $group->group_size;
            }
        }

        return $max;
    }

    /**
     * Returns groups which are generated by groupal
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     */
    public function get_generated_groups($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_groups', array(
            'groupformation' => $this->groupformationid), $sortedby, $fieldset);
    }

    /**
     * Returns groups which are generated by groupal
     *
     * @param null $sortedby
     * @param string $fieldset
     * @return array
     */
    public function get_group_users($sortedby = null, $fieldset = '*') {
        global $DB;

        return $DB->get_records('groupformation_group_users', array(
            'groupformation' => $this->groupformationid), $sortedby, $fieldset);
    }

    /**
     * Returns all users from groups which are generated by groupal
     *
     * @param $groupid
     * @return array
     */
    public function get_users_for_generated_group($groupid) {
        global $DB;

        return $DB->get_records('groupformation_group_users', array(
            'groupformation' => $this->groupformationid, 'groupid' => $groupid), null, 'userid');
    }

    /**
     * Deletes all generated group
     */
    public function delete_generated_groups() {
        global $DB;

        $records = $DB->get_records('groupformation_groups', array(
            'groupformation' => $this->groupformationid));

        foreach ($records as $key => $record) {
            if ($record->created == 1) {
                groups_delete_group($record->moodlegroupid);
            }
        }
        $DB->delete_records('groupformation_groups', array(
            'groupformation' => $this->groupformationid));
        $DB->delete_records('groupformation_group_users', array(
            'groupformation' => $this->groupformationid));
    }

    /**
     * Assigns user to group A or group B (creates those if they do not exist)
     *
     * @param int $userid
     * @deprecated only Mathevorkurs
     */
    public function assign_to_groups_a_and_b($userid) {
        global $DB, $COURSE;
        $completed = 1;

        if (!$DB->record_exists('groups', array(
            'courseid' => $COURSE->id, 'name' => 'Gruppe A'))
        ) {
            $record = new stdClass ();
            $record->courseid = $COURSE->id;
            $record->name = "Gruppe A";
            $record->timecreated = time();

            $a = groups_create_group($record);
        }
        if (!$DB->record_exists('groups', array(
            'courseid' => $COURSE->id, 'name' => 'Gruppe B'))
        ) {
            $record = new stdClass ();
            $record->courseid = $COURSE->id;
            $record->name = "Gruppe B";
            $record->timecreated = time();

            $b = groups_create_group($record);
        }

        $records = $DB->get_records('groupformation_started', array(
            'groupformation' => $this->groupformationid, 'completed' => $completed), 'timecompleted',
            'id, userid, timecompleted');

        if (count($records) > 0) {
            $i = 0;
            foreach ($records as $id => $record) {
                if ($record->userid == $userid) {
                    break;
                }
                $i++;
            }

            $a = $DB->get_field('groups', 'id', array(
                'courseid' => $COURSE->id, 'name' => 'Gruppe A'));
            $b = $DB->get_field('groups', 'id', array(
                'courseid' => $COURSE->id, 'name' => 'Gruppe B'));

            if ($i % 2 == 0) {
                // Sort to group A.
                groups_add_member($a, $userid);
                $DB->set_field('groupformation_started', 'groupid', $a, array(
                    'groupformation' => $this->groupformationid, 'completed' => $completed, 'userid' => $userid));
            }

            if ($i % 2 == 1) {
                // Sort to group B.
                groups_add_member($b, $userid);
                $DB->set_field('groupformation_started', 'groupid', $b, array(
                    'groupformation' => $this->groupformationid, 'completed' => $completed, 'userid' => $userid));
            }
        }
    }
}