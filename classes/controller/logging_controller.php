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
 * Logging controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

/**
 * Controller for logging
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_logging_controller {

    /**
     * Handles data and tries logging it
     *
     * @param $userid
     * @param $groupformationid
     * @param $message
     * @param $level
     * @return bool
     */
    public function handle($userid, $groupformationid, $message, $level) {
        if (!is_null($message) && is_string($message)) {
            $this->create_log_entry($userid, $groupformationid, $message);
        } else {
            return false;
        }
    }

    /**
     * Creates log entry in database
     *
     * @param $userid
     * @param $groupformationid
     * @param $message
     */
    private function create_log_entry($userid, $groupformationid, $message) {
        global $DB;
        $timestamp = microtime(true);

        $logentry = new stdClass ();
        $logentry->timestamp = $timestamp;
        $logentry->userid = $userid;
        $logentry->groupformationid = $groupformationid;
        $logentry->message = $message;

        $DB->insert_record("groupformation_logging", $logentry);
    }
}
