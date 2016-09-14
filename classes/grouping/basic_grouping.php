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
 * Basic grouping interface
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/grouping.php');

require_once($CFG->dirroot . '/mod/groupformation/lib/classes/criteria/specific_criterion.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/participant.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/matchers/group_centric_matcher.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/basic_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/random_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/topic_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/optimizers/optimizer.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/xml_writers/participant_writer.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/xml_writers/cohort_writer.php');

class mod_groupformation_basic_grouping extends mod_groupformation_grouping{

    private $groupformationid;
    private $usermanager;
    private $store;
    private $groupsmanager;
    private $criterioncalculator;

    /**
     * mod_groupformation_job_manager constructor.
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->usermanager = new mod_groupformation_user_manager($groupformationid);
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager($groupformationid);
        $this->criterioncalculator = new mod_groupformation_criterion_calculator($groupformationid);
        $this->participantparser = new mod_groupformation_participant_parser($groupformationid);
    }

    /**
     * Scientific division of users and creation of participants
     *
     * @param $users Two parted array - first part is all groupal users, second part are all random users
     * @return array
     */
    public function run_grouping($users) {
        $configurations = array(
            "groupal:1" => array(),
        );

        $configurationkeys = array_keys($configurations);

        $numberofslices = count($configurationkeys);

        $groupsizes = $this->store->determine_group_size($users);

        if (count($users[0]) < $numberofslices) {
            return array();
        }
        // divide users into n slices
        $slices = $this->slicing($users[0], $numberofslices);

        $cohorts = array();

        for ($i = 0; $i < $numberofslices; $i++) {
            $slice = $slices[$i];

            $configurationkey = $configurationkeys[$i];

            $raw_participants = $this->participantparser->build_participants($slice);

            $participants = $raw_participants;

            $cohorts[$configurationkey] = $this->build_cohort($participants, $groupsizes[0], $configurationkey);
        }

        // Handle all users with incomplete or no questionnaire submission
        $randomparticipantskey = "random:1";
        $randomcohort = null;

        if (count($users[1])>0){

            $randomparticipants = $this->participantparser->build_empty_participants($users[1]);
            $randomcohort = $this->build_cohort($randomparticipants, $groupsizes[1], $randomparticipantskey);

        }

        $cohorts[$randomparticipantskey] = $randomcohort;

        return $cohorts;
    }

}