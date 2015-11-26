<?php

/**
 * An XML Writer for student
 *
 * @author Rene Roepke
 *
 */
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');

class mod_groupformation_cvs_writer {

    /** @var cm_info */
    private $cm = null;

    /** @var int This is the id of the activity */
    private $groupformationid = null;

    /** @var mod_groupformation_storage_manager */
    private $store = null;

    /** @var mod_groupformation_user_manager */
    private $user_manager = null;

    /** @var mod_groupformation_groups_manager */
    private $groups_manager = null;

    /** @var array This is the user_to_new_id mapping */
    private $user_map = array();

    private $replace_userids = false;

    /**
     * mod_groupformation_cvs_writer constructor.
     * @param $cm
     * @param $groupformationid
     */
    public function __construct($cm, $groupformationid) {
        $this->cm = $cm;
        $this->groupformationid = $groupformationid;

        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->user_manager = new mod_groupformation_user_manager($groupformationid);
        $this->groups_manager = new mod_groupformation_groups_manager($groupformationid);
    }

    /**
     * Returns data by type
     * @param $type
     * @return string
     */
    public function get_data($type){
        switch($type){
            case 'answers': return $this->get_answers();
            case 'groups': return $this->get_groups();
            case 'group_users': return $this->get_group_users();
            case 'logging': return $this->get_logging_data();
        }
    }

    /**
     * Returns a cvs-formatted string of a record
     * @param $record
     * @param bool|false $title
     * @return string
     */
    public function record_to_cvs($record, $title = false) {
        $array = get_object_vars($record);
        unset($array['id']);
        if ($title)
            return implode(",", array_keys($array));
        else
            return implode(",", array_values($array));
    }

    /**
     * Returns a cvs-formatted string of all records
     *
     * @param $records
     * @return string
     */
    public function records_to_cvs($records) {
        $cvs = null;
        foreach ($records as $id => $record) {
            if (is_null($cvs)) {
                $cvs = $this->record_to_cvs($record, true) . "\n";
            }
            if (isset($record->userid) && $this->replace_userids) {
                $orig_userid = $record->userid;
                if (array_key_exists($orig_userid, $this->user_map)) {
                    $record->userid = $this->user_map[$orig_userid];
                } else {
                    $next = count($this->user_map);
                    $this->user_map[$orig_userid] = $next;
                    $record->userid = $next;
                }
            }
            $cvs .= $this->record_to_cvs($record) . "\n";
        }
        return $cvs;
    }

    /**
     * Returns cvs-formatted answers with anonymous user ids
     *
     * @return string
     */
    public function get_answers() {

        $answers = $this->user_manager->get_answers(null, null, 'id', 'id,userid,category,questionid,answer');

        $cvs = $this->records_to_cvs($answers);

        // var_dump($cvs);

        return $cvs;
    }

    /**
     * Returns cvs-formatted groups with anonymous user ids
     *
     * @return string
     */
    public function get_groups() {
        $groups = $this->groups_manager->get_generated_groups(null, 'id,groupname,group_size,performance_index,groupal,random,mrandom,created');

        $cvs = $this->records_to_cvs($groups);

        // var_dump($cvs);

        return $cvs;
    }

    /**
     * Returns cvs-formatted group-users with anonymous user ids
     *
     * @return string
     */
    public function get_group_users() {
        $groups = $this->groups_manager->get_group_users(null, 'id,userid,groupid');

        $cvs = $this->records_to_cvs($groups);

        // var_dump($cvs);

        return $cvs;
    }

    /**
     * Returns cvs-formatted answers with anonymous user ids
     *
     * @return string
     */
    public function get_logging_data() {
        $groups = mod_groupformation_util::get_logging_data($this->groupformationid,'userid');

        $cvs = $this->records_to_cvs($groups);

        // var_dump($cvs);

        return $cvs;
    }
}