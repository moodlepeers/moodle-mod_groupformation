<?php

require_once("$CFG->libdir/externallib.php");

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
        global $CFG, $DB;
        //require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');
      
        $params = self::validate_parameters(self::delete_answers_parameters(), array('users'=>$users));

        foreach ($params['users'] as $user) {
            $user = (object) $user;


            //TODO call delete_answers function
            //$usermanager = new mod_groupformation_user_manager($userObject->groupformation);
            //$usermanager->delete_answers($userObject->userid);

            $groupformationid = $user->groupformation;
            $userid = $user->userid;

            $DB->delete_records('groupformation_users', array('groupformation' => $groupformationid, 'userid' => $userid));
            $DB->delete_records('groupformation_answers', array('groupformation' => $groupformationid, 'userid' => $userid));
            $DB->delete_records('groupformation_user_values',
                    array('groupformationid' => $groupformationid, 'userid' => $userid));
            

            $test = array("users" => array("id" => "20", "userid" => $user->userid, "groupformation" => $user->groupformation));
            return $test;

        }
    }

}