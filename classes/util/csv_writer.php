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
 * This file contains a csv writer class
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');

/**
 * This is a csv writer for exporting DB data
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_csv_writer {

    /** @var cm_info */
    private $cm = null;

    /** @var int This is the id of the activity */
    private $groupformationid = null;

    /** @var mod_groupformation_storage_manager */
    private $store = null;

    /** @var mod_groupformation_user_manager */
    private $usermanager = null;

    /** @var mod_groupformation_groups_manager */
    private $groupsmanager = null;

    /** @var array This is the user_to_new_id mapping */
    private $usermap = array();

    /** @var bool This determines whether the userids are replaced or not */
    private $replaceuserids = true;

    /**
     * mod_groupformation_csv_writer constructor.
     *
     * @param cm_info $cm
     * @param int $groupformationid
     */
    public function __construct($cm, $groupformationid) {
        $this->cm = $cm;
        $this->groupformationid = $groupformationid;

        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager($groupformationid);

    }

    /**
     * Returns data by type
     *
     * @param string $type
     * @return string
     */
    public function get_data($type) {
        switch ($type) {
            case 'answers':
                return $this->get_answers();
            case 'users':
                return $this->get_users();
            case 'groups':
                return $this->get_groups();
            case 'group_users':
                return $this->get_group_users();
            case 'logging':
                return $this->get_logging_data();
        }
    }

    /**
     * Returns a csv-formatted string of a record
     *
     * @param stdClass $record
     * @param bool|false $title
     * @return string
     */
    public function record_to_csv($record, $title = false) {
        $array = get_object_vars($record);
        unset($array['id']);

        if ($title) {
            return implode(",", array_keys($array));
        } else {
            return implode(",", array_values($array));
        }
    }

    /**
     * Returns a csv-formatted string of all records
     *
     * @param array $records
     * @return string
     */
    public function records_to_csv($records) {
        $csv = null;
        foreach (array_values($records) as $record) {
            if (is_null($csv)) {
                $csv = $this->record_to_csv($record, true) . "\n";
            }
            if (isset($record->userid) && $this->replaceuserids) {
                $origuserid = $record->userid;
                if (array_key_exists($origuserid, $this->usermap)) {
                    $record->userid = $this->usermap[$origuserid];
                } else {
                    $next = count($this->usermap);
                    $this->usermap[$origuserid] = $next;
                    $record->userid = $next;
                }
            }
            if (isset($record->timestamp)) {
                $record->timestamp = date('d/m/Y H:i:s', $record->timestamp);
            }
            $csv .= $this->record_to_csv($record) . "\n";
        }

        return $csv;
    }

    /**
     * Returns csv-formatted answers with anonymous user ids
     *
     * @return string
     */
    public function get_answers() {

        $answers = $this->usermanager->get_answers(null, null, 'id', 'id,groupformation,userid,category,questionid,answer');

        $csv = $this->records_to_csv($answers);

        return $csv;
    }

    /**
     * Returns csv-formatted groups with anonymous user ids
     *
     * @return string
     */
    public function get_groups() {
        $groups = $this->groupsmanager->get_generated_groups(null,
            'id,groupformation,groupname,group_size,performance_index,groupal,random,mrandom,created');

        $csv = $this->records_to_csv($groups);

        return $csv;
    }

    /**
     * Returns csv-formatted group-users with anonymous user ids
     *
     * @return string
     */
    public function get_group_users() {
        $groups = $this->groupsmanager->get_group_users(null, 'id,groupformation,userid,groupid');

        $csv = $this->records_to_csv($groups);

        return $csv;
    }

    /**
     * Returns csv-formatted answers with anonymous user ids
     *
     * @return string
     */
    public function get_logging_data() {
        $groups = $this->store->get_logging_data('timestamp');

        $csv = $this->records_to_csv($groups);

        return $csv;
    }

    /**
     * Returns users for activity
     *
     * @return string
     */
    public function get_users() {
        $groupusers = $this->groupsmanager->get_group_users('userid', 'userid,groupid,groupformation');
        $categories = $this->store->get_categories();
        $users = array_keys($groupusers);

        $userdata = array();
        foreach ($users as $userid) {
            $userdata[$userid] = array();
            $userdata[$userid]['groupformation'] = $this->groupformationid;
            $userdata[$userid]['groupid'] = $groupusers[$userid]->groupid;
            foreach ($categories as $category) {
                $userdata[$userid][$category] = array();
                $answers = $this->usermanager->get_answers($userid, $category, 'questionid', 'questionid,answer');
                foreach ($answers as $answer) {
                    $questionid = $answer->questionid;
                    $userdata[$userid][$category][$questionid] = $answer->answer;
                }
            }
        }

        $csv = "";

        for ($j = 0; $j < count($users); $j++) {
            if ($j == 0) {
                $csv .= "groupformationid,userid,groupid,";
                foreach ($categories as $category) {
                    if ($category == "knowledge" || $category == "topic") {
                        $temp = $this->store->get_knowledge_or_topic_values($category);
                        $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
                        $options = mod_groupformation_util::xml_to_array($xmlcontent);
                        $csv .= implode(",", $options) . ",";
                    } else {
                        $csv .= implode('_' . $category . ',', range(1, $this->store->get_number($category))) .
                            '_' . $category . ",";
                    }
                }
                $csv = rtrim($csv, ",");
                $csv .= "\n";
            }
            $origuserid = $users[$j];
            $userid = null;
            if ($this->replaceuserids) {
                $origuserid = $users[$j];
                if (array_key_exists($origuserid, $this->usermap)) {
                    $userid = $this->usermap[$origuserid];
                } else {
                    $next = count($this->usermap);
                    $this->usermap[$origuserid] = $next;
                    $userid = $next;
                }
            } else {
                $userid = $users[$j];
            }
            $csv .= $userdata[$origuserid]['groupformation'] . ",";
            $csv .= $userid . ",";
            $csv .= $userdata[$origuserid]['groupid'] . ",";
            foreach ($categories as $category) {
                $numberofquestions = $this->store->get_number($category);
                $answers = $userdata[$origuserid][$category];
                for ($i = 1; $i <= $numberofquestions; $i++) {
                    if (array_key_exists($i, $answers)) {
                        $csv .= $answers[$i] . ",";
                    } else {
                        $csv .= ",";
                    }
                }
            }
            $csv = rtrim($csv, ",");
            $csv .= "\n";
        }

        return $csv;
    }
}