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
 * declaring of web services
 *
 * @package     mod_groupformation
 * @author      Rene Roepke, Stefan Jung
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *  webservice
 *
 * @params groups description
 */
$services = array(
        'groupformation_delete_answers_service' => array(                                                // the name of the web service
                'functions' => array('mod_groupformation_delete_answers'), // web service functions of this service
                'requiredcapability' => '',                // if set, the web service user need this capability to access
            // any function of this service. For example: 'some/capability:specified'
                'restrictedusers' => 0, // if enabled, the Moodle administrator must link some user to this service
            // into the administration
                'enabled' => 1, // if enabled, the service can be reachable on a default installation
                'shortname' => 'delete_answers',       // optional – but needed if restrictedusers is set so as to allow logins.
                'downloadfiles' => 0,    // allow file downloads.
                'uploadfiles' => 0      // allow file uploads.
        ),
        'groupformation_exclude_user_service' => array(                                                // the name of the web service
                'functions' => array('mod_groupformation_exclude_users'), // web service functions of this service
                'requiredcapability' => '',                // if set, the web service user need this capability to access
            // any function of this service. For example: 'some/capability:specified'
                'restrictedusers' => 0, // if enabled, the Moodle administrator must link some user to this service
            // into the administration
                'enabled' => 1, // if enabled, the service can be reachable on a default installation
                'shortname' => 'exclude_user',       // optional – but needed if restrictedusers is set so as to allow logins.
                'downloadfiles' => 0,    // allow file downloads.
                'uploadfiles' => 0      // allow file uploads.
        )
);
/**
 * webservice functions
 *
 * @params groups description
 */
$functions = array(
        'mod_groupformation_delete_answers' => array(         //web service function name
                'classname' => 'mod_groupformation_external',
            //class containing the external function OR namespaced class in classes/external/XXXX.php
                'methodname' => 'delete_answers',          //external function name
                'classpath' => 'mod/groupformation/webservicelib.php',
            //file containing the class/external function - not required if using namespaced auto-loading classes.
            // defaults to the service's externalib.php
                'description' => 'Delete answers of user.',    //human readable description of the web service function
                'type' => 'write',                  //database rights of the web service function (read, write)
                'ajax' => true,        // is the service available to 'internal' ajax calls.
                'capabilities' => array(),   // capabilities required by the function.
        ),
        'mod_groupformation_exclude_users' => array(         //web service function name
                'classname' => 'mod_groupformation_external',
            //class containing the external function OR namespaced class in classes/external/XXXX.php
                'methodname' => 'exclude_users',          //external function name
                'classpath' => 'mod/groupformation/webservicelib.php',
            //file containing the class/external function - not required if using namespaced auto-loading classes.
            // defaults to the service's externalib.php
                'description' => 'Exclude users.',    //human readable description of the web service function
                'type' => 'write',                  //database rights of the web service function (read, write)
                'ajax' => true,        // is the service available to 'internal' ajax calls.
                'capabilities' => array(),   // capabilities required by the function.
        ),
);