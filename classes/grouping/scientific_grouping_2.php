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
 * Scientific grouping interface
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/grouping.php');

class mod_groupformation_scientific_grouping_2 extends mod_groupformation_grouping {

    private $groupformationid;
    private $usermanager;
    private $store;
    private $groupsmanager;
    private $criterioncalculator;
    private $data;

    /**
     * mod_groupformation_scientific_grouping constructor.
     *
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->usermanager = new mod_groupformation_user_manager($groupformationid);
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager($groupformationid);
        $this->criterioncalculator = new mod_groupformation_criterion_calculator($groupformationid);
        $this->participantparser = new mod_groupformation_participant_parser($groupformationid);
        $this->data = new mod_groupformation_data();
    }

    /**
     * Scientific division of users and creation of participants
     *
     * @param $users Two parted array - first part is all groupal users, second part are all random users
     * @return array
     */
    public function run_grouping($users) {

        $big5specs = $this->data->get_criterion_specification('big5');

        unset($big5specs['labels']['neuroticism']);
        unset($big5specs['labels']['openness']);
        unset($big5specs['labels']['agreeableness']);

        $specs = ["big5" => $big5specs];

        $configurations = array(
            "mrand:0;ex:1;gh:1" => array('big5_extraversion' => true, 'big5_conscientiousness' => true),
            "mrand:0;ex:1;gh:0" => array('big5_extraversion' => true, 'big5_conscientiousness' => false),
            "mrand:0;ex:0;gh:0" => array('big5_extraversion' => false, 'big5_conscientiousness' => false),
        );

        $configurationkeys = array_keys($configurations);

        $numberofslices = count($configurationkeys);

        $groupsizes = $this->store->determine_group_size($users);

        if (count($users[0]) < $numberofslices) {
            return [];
        }

        // Divide users into n slices.
        $slices = $this->get_user_slices($users[0], $numberofslices);

        $cohorts = array();

        for ($i = 0; $i < $numberofslices; $i++) {
            $slice = $slices[$i];

            $configurationkey = $configurationkeys[$i];
            $configuration = $configurations[$configurationkey];

            $rawparticipants = $this->participantparser->build_participants($slice, $specs);

            $participants = $this->configure_participants($rawparticipants, $configuration);

            $cohorts[$configurationkey] = $this->build_cohort($participants, $groupsizes[0], $configurationkey);
        }

        // Handle all users with incomplete or no questionnaire submission.
        $randomkey = "rand:1;mrand:_;ex:_;gh:_";

        $randomparticipants = $this->participantparser->build_empty_participants($users[1]);
        $randomcohort = $this->build_cohort($randomparticipants, $groupsizes[1], $randomkey);

        $cohorts[$randomkey] = $randomcohort;

        return $cohorts;
    }

    /**
     * Creates evenly distributed slices by using the linearized eval score
     *
     * @param $users
     * @param $numberofslices
     * @return array
     */
    private function get_user_slices($users, $numberofslices) {
        $scores = [];

        foreach($users as $user){
            $scores[] = array($user, $this->usermanager->get_eval_score($user));
        }

        function cmp($a, $b){
            return $a[1]<$b[1];
        }
        usort($scores, "cmp");

        $slices = range(1,$numberofslices);
        $userslices = [];
        foreach($scores as $tuple) {
            if (count($slices)==0){
                $slices = range(1,$numberofslices);
            }

            $user = $tuple[0];
            $score = $tuple[1];

            $assignto = array_rand($slices);

            if (!isset($userslices[$assignto])) {
                $userslices[$assignto] = [];
            }

            $userslices[$assignto] = array_merge([$user],$userslices[$assignto]);

            unset($slices[$assignto]);
        }

        return $userslices;
    }

}