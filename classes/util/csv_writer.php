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
 * This is a csv writer for exporting DB data
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');

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
    private $replaceuserids = false;

    /**
     * mod_groupformation_csv_writer constructor.
     *
     * @param $cm
     * @param $groupformationid
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
     * @param $type
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
     * @param $record
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
     * @param $records
     * @return string
     */
    public function records_to_csv($records) {
        $csv = null;
        foreach ($records as $id => $record) {
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

    public function get_userids() {

        $users = $this->usermanager->get_users_started('userid', 'userid');
        $groupusers = $this->groupsmanager->get_group_users('userid', 'userid,groupid,groupformation');

        $us = array_values(array_keys($users));
        $gs = array_values(array_keys($groupusers));

        $users = array_values(array_merge($us, $gs));

        $userids = array();
        foreach ($users as $u) {
            $userids[$u] = 1;
        }

        $userscurrent = array_keys($userids);

        if ($this->groupformationid == 3 || $this->groupformationid == 4) {
            $gidorig = $this->groupformationid - 2;

            $umorig = new mod_groupformation_user_manager($gidorig);
            $gmorig = new mod_groupformation_groups_manager($gidorig);

            $users = $umorig->get_users_started('userid', 'userid');
            $groupusers = $gmorig->get_group_users('userid', 'userid,groupid,groupformation');

            $us = array_values(array_keys($users));
            $gs = array_values(array_keys($groupusers));

            $users = array_values(array_merge($us, $gs));

            $userids = array();
            foreach ($users as $u) {
                $userids[$u] = 1;
            }

            $usersorig = array_keys($userids);

            $onlycurrent = array();
            $bothcurrent = array();
            foreach ($userscurrent as $usercurrent) {
                if (!in_array($usercurrent, $usersorig)) {
                    $onlycurrent[] = $usercurrent;
                }
                if (in_array($usercurrent, $usersorig)) {
                    $bothcurrent[] = $usercurrent;
                }
            }

            $onlyorig = array();
            $bothorig = array();

            foreach ($usersorig as $us2u) {
                if (!in_array($us2u, $userscurrent)) {
                    $onlyorig[] = $us2u;
                }
                if (in_array($us2u, $userscurrent)) {
                    $bothorig[] = $us2u;
                }
            }

            return $onlycurrent;
        }

        return $userscurrent;
    }

    public function get_users() {
        global $DB;

        $us = $this->get_userids();

        $categories = $this->store->get_categories();

        $unknown = array();

        $userdata = array();
        foreach ($us as $userid) {
            $userdata[$userid] = array();
            $userdata[$userid]['code'] = $this->usermanager->get_participant_code($userid);
            $userdata[$userid]['groupformation'] = $this->groupformationid;

            $userdata[$userid]['groupid'] = null;
            $userdata[$userid]['groupid'] = null;
            $userdata[$userid]['groupname'] = null;
            $userdata[$userid]['performance_index'] = null;
            $userdata[$userid]['groupkey'] = null;
            $userdata[$userid]['rand'] = null;
            $userdata[$userid]['mrand'] = null;
            $userdata[$userid]['ex'] = null;
            $userdata[$userid]['gh'] = null;

            if ($this->groupsmanager->has_group($userid)) {
                $groupid = $this->groupsmanager->get_group_id($userid);
                $userdata[$userid]['groupid'] = $this->groupsmanager->get_moodle_group_id($groupid) - 394;
                $userdata[$userid]['groupname'] = str_replace("G1_", "", $this->groupsmanager->get_group_name($userid));
                $userdata[$userid]['performance_index'] = $this->groupsmanager->get_performance_index($groupid);

                $groupkey = str_replace(';', '-', $this->groupsmanager->get_group_key($groupid));
                $groupkey = str_replace('mrand', 'manual', $groupkey);

                $userdata[$userid]['groupkey'] = $groupkey;

                $manual = (strpos($groupkey, 'manual:'));
                $random = (strpos($groupkey, 'rand:'));

                $mrand = 0;
                if ($manual !== false) {
                    $mrand = substr($groupkey, strpos($groupkey, 'manual:') + 7, 1);
                }

                $rand = 0;
                if ($random !== false) {
                    $rand = substr($groupkey, strpos($groupkey, 'rand:') + 5, 1);
                    $mrand = 0;
                }

                if ($rand == 1 || $mrand == 1) {
                    $userdata[$userid]['performance_index'] = null;
                }

                $ex = substr($groupkey, strpos($groupkey, 'ex:') + 3, 1);
                $gh = substr($groupkey, strpos($groupkey, 'gh:') + 3, 1);

                $ex = str_replace('1', 1, $ex);
                $ex = str_replace('_', 2, $ex);
                $ex = str_replace('0', 3, $ex);

                $gh = str_replace('1', 1, $gh);
                $gh = str_replace('_', 2, $gh);
                $gh = str_replace('0', 3, $gh);

                $userdata[$userid]['rand'] = $rand;
                $userdata[$userid]['mrand'] = $mrand;
                $userdata[$userid]['ex'] = $ex;
                $userdata[$userid]['gh'] = $gh;
            } else {
                $result = $DB->record_exists('groups_members', array('userid' => $userid));
                if ($result) {
                    $groupid = $DB->get_field('groups_members', 'groupid', array('userid' => $userid));
                    $courseid = $DB->get_field('groups', 'courseid', array('id' => $groupid));
                    if ($courseid == $this->groupformationid || $courseid + 2 == $this->groupformationid) {
                        // var_dump($userid . " in group ".($groupid - 394));
                        $members = $DB->get_records('groups_members', array('groupid' => $groupid), 'userid', 'userid');
                        $unknown = array_merge($unknown, array_keys($members));
                        // var_dump(implode(', ',$unknown));
                        $userdata[$userid]['groupid'] = $groupid - 394;
                        $userdata[$userid]['random'] = 2;
                    }
                }
            }

            foreach ($categories as $category) {
                $userdata[$userid][$category] = array();

                $answers = $this->usermanager->get_answers($userid, $category, null, 'questionid, answer');
                foreach ($answers as $answer) {
                    $questionid = $answer->questionid;
                    $userdata[$userid][$category][$questionid] = $answer->answer;
                }
            }
        }

        $unknown = array_values(array_unique($unknown));

        foreach ($unknown as $userid) {
            if (!array_key_exists($userid, $userdata)) {
                $userdata[$userid] = array();
                $groupid = $DB->get_field('groups_members', 'groupid', array('userid' => $userid));
                $userdata[$userid]['groupid'] = $groupid - 394;
                $userdata[$userid]['code'] = $this->usermanager->get_participant_code($userid);
                $userdata[$userid]['groupformation'] = $this->groupformationid;
                $userdata[$userid]['groupname'] = null;
                $userdata[$userid]['performance_index'] = null;
                $userdata[$userid]['groupkey'] = null;
                $userdata[$userid]['rand'] = null;
                $userdata[$userid]['mrand'] = null;
                $userdata[$userid]['ex'] = null;
                $userdata[$userid]['gh'] = null;
                foreach ($categories as $category) {
                    $userdata[$userid][$category] = array();
                    $answers = $this->usermanager->get_answers($userid, $category, null, 'questionid, answer');
                    foreach ($answers as $answer) {
                        $questionid = $answer->questionid;
                        $userdata[$userid][$category][$questionid] = $answer->answer;
                    }
                }
            }
        }

        $csv = "";

        // var_dump($us);
        $us = array_values(array_unique(array_merge(array_values($us), array_values($unknown))));
        // var_dump($us);
        for ($j = 0; $j < count($us); $j++) {

            if ($j == 0) {
                $csv .= "userid,participantcode,groupformationid,groupid,groupname,performance_index,";
                // $csv .= "groupkey,";
                $csv .= "random,manual_random,criterion_extraversion,criterion_gewissenhaftigkeit,";
                foreach ($categories as $category) {
                    if ($category == "knowledge" || $category == "topic") {
                        $temp = $this->store->get_knowledge_or_topic_values($category);
                        $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
                        $options = mod_groupformation_util::xml_to_array($xmlcontent);
                        $csv .= implode(",", $options) . ",";
                    } else {
                        $questions = $this->store->get_questions($category);
                        $questionids = array();
                        foreach ($questions as $question) {
                            $questionids[] = $question->questionid;
                        }

                        $csv .= implode('_' . $category . ",", $questionids) .
                                '_' . $category . ",";
                    }
                }
                $csv = rtrim($csv, ",");
                $csv .= "\n";
            }

            $userid = $us[$j];

            $line = "";
            $line .= $userid . ",";
            $line .= $userdata[$userid]['code'] . ",";
            $line .= $userdata[$userid]['groupformation'] . ",";
            $line .= $userdata[$userid]['groupid'] . ",";
            $line .= $userdata[$userid]['groupname'] . ",";
            $line .= $userdata[$userid]['performance_index'] . ",";
            // $line .= $userdata[$userid]['groupkey'] . ",";
            $line .= $userdata[$userid]['rand'] . ",";
            $line .= $userdata[$userid]['mrand'] . ",";
            $line .= $userdata[$userid]['ex'] . ",";
            $line .= $userdata[$userid]['gh'] . ",";

            foreach ($categories as $category) {

                if ($category == "knowledge" || $category == "topic") {
                    $optionscount = $this->store->get_number($category);

                    for ($i = 1; $i <= $optionscount; $i++) {
                        $line .= $this->usermanager->get_single_answer($userid, $category, $i) . ",";
                    }
                } else {
                    $questions = $this->store->get_questions($category);

                    foreach ($questions as $question) {
                        $i = $question->questionid;
                        if (array_key_exists($i, $userdata[$userid][$category])) {
                            $line .= $userdata[$userid][$category][$i] . ",";
                        } else {
                            $line .= ",";
                        }
                    }
                }
            }

            $csv .= $line;
            $csv = rtrim($csv, ",");
            $csv .= "\n";
        }

        return $csv;
    }
}