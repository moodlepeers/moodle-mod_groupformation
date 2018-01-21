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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');

class mod_groupformation_groups_manager {

    /** @var int ID of module instance */
    private $groupformationid;

    /**
     * Constructs storage manager for a specific groupformation
     *
     * @param unknown $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
    }

    /**
     * Assigns user to group A or group B (creates those if they do not exist)
     * (uses order of user submissions and assigns one %2 to A or B)
     *
     * @param int $userid
     */
    public function assign_to_groups_a_and_b($userid) {
        global $DB, $COURSE;
        $completed = 1;

        if (!$DB->record_exists('groups', array('courseid' => $COURSE->id, 'name' => 'Gruppe A'))) {
            $record = new stdClass ();
            $record->courseid = $COURSE->id;
            $record->name = "Gruppe A";
            $record->timecreated = time();

            $a = groups_create_group($record);
        }
        if (!$DB->record_exists('groups', array('courseid' => $COURSE->id, 'name' => 'Gruppe B'))) {
            $record = new stdClass ();
            $record->courseid = $COURSE->id;
            $record->name = "Gruppe B";
            $record->timecreated = time();

            $b = groups_create_group($record);
        }

        $records = $DB->get_records('groupformation_started', array(
                'groupformation' => $this->groupformationid, 'completed' => $completed), 'timecompleted',
                'id, userid, timecompleted');
        $field = $DB->get_field('groupformation_started', 'groupid', array(
                'groupformation' => $this->groupformationid, 'completed' => $completed, 'userid' => $userid));

        if (is_null($field) && count($records) > 0) {
            $i = 0;
            foreach ($records as $record) {
                if ($record->userid == $userid) {
                    break;
                }
                $i += 1;
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