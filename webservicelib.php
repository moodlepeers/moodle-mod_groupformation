<?php

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

class mod_groupformation_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function delete_answers_parameters() {
        return new external_function_parameters(
                array(
                        'users' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'userid' => new external_value(PARAM_INT, 'id of user'),
                                                'groupformation' => new external_value(PARAM_TEXT,
                                                        'id of groupformation')
                                        )
                                )
                        )
                )
        );
    }

    public static function delete_answers_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'id' => new external_value(PARAM_INT, 'user id'),
                                'userid' => new external_value(PARAM_INT, 'id of user'),
                                'groupformation' => new external_value(PARAM_INT, 'id of groupformation')
                        )
                )
        );
    }

    /**
     * Create groups
     *
     * @param array $user array of group description arrays (with keys groupname and courseid)
     * @return array of newly created groups
     */
    public static function delete_answers($users) { //Don't forget to set it as static
        $params = self::validate_parameters(self::delete_answers_parameters(), array('users' => $users));

        foreach ($params['users'] as $user) {
            $user = (object) $user;

            $groupformationid = $user->groupformation;
            $userid = $user->userid;

            $usermanager = new mod_groupformation_user_manager($groupformationid);
            $usermanager->delete_answers($userid, true);
            // set new answer count
            $usermanager->set_answer_count($userid);
            // set completed to false because answered were deleted
            $usermanager->set_complete($userid, 0);

            $test = array("users" => array("id" => "20", "userid" => $user->userid, "groupformation" => $user->groupformation));
            return $test;

        }
    }

}