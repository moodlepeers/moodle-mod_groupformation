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
require_once($CFG->dirroot . '/mod/groupformation/classes/util/statistics.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

class mod_groupformation_scientific_grouping_2 extends mod_groupformation_grouping {

    /** @var int ID of module instance */
    public $groupformationid;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store;

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var mod_groupformation_criterion_calculator The calculator for criteria */
    private $criterioncalculator;

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
    }

    /**
     * Returns equally distributed slices by checking mean and std deviation.
     *
     * @param $users
     * @param $numberofslices
     * @return array
     */
    public function get_optimal_slices($users, $numberofslices, $specs = null) {
        $statistics = new mod_groupformation_statistics();
        // TODO Set cutoff value.
        $cutoff = 0.02;

        // Loop preparation.
        $best = null;
        $bestslices = null;
        $bestfound = false;
        $bestdistmean = null;
        $bestdiststddev = null;
        $i = 0;
        // Loop to determine best slice based on mean and stddev.
        // Idea: all slices should have very similar mean and stddev (distance smaller cutoff).

        $scores = [];

        //var_dump($specs);
        foreach ($users as $user) {
            $scores[] = array($user, $this->usermanager->get_eval_score($user, $specs));
        }

        $cmp = function($a, $b) {
            return $a[1] < $b[1];
        };

        usort($scores, $cmp);

        $best = $this->get_slices($scores, $numberofslices);

        while ($i < 100 && !$bestfound) {
            $i++;
            $slices = $this->get_slices($scores, $numberofslices);
            $func = function($value, $key = 1) {
                return $value[$key];
            };

            // Loop preparation.
            $means = [];
            $stddevs = [];
            // Loop to compute means and stddevs for all slices.
            foreach ($slices as $slice) {
                $values = array_map($func, $slice);
                $means[] = $statistics::mean($values);
                $stddevs[] = $statistics::std_deviation($values);
            }

            // Computes avg/mean of mean values and std deviation values.
            $avgmean = array_sum($means) / count($means);
            $avgstddev = array_sum($stddevs) / count($stddevs);

            if ($avgmean == 0 || $avgstddev == 0) {
                continue;
            }

            // Loop preparation.
            $boolmean = true;
            $boolstddev = true;
            $distsummean = 0.0;
            $distsumstddev = 0.0;

            // Loop to determine if distance is smaller than cutoff value and to compute sum of all distances.
            // Track the following conditions when iterating over all means and stddevs:
            // Condition 1: The distance between all means and the mean of means is smaller than cutoff.
            // Condition 2: The distance between all stddevs and the mean of stddevs is smaller than cutoff.
            for ($k = 0; $k < count($means); $k++) {
                $mean = $means[$k];
                $stddev = $stddevs[$k];
                $distmean = abs($mean - $avgmean) / ($avgmean+0.01);
                $diststddev = abs ($stddev - $avgstddev) / ($avgstddev+0.01);
                $boolmean &= ($distmean < $cutoff);
                $boolstddev &= ($diststddev < $cutoff);
                $distsummean += $distmean;
                $distsumstddev += $diststddev;
            }

            // Check whether both conditions are true:
            // Condition 1: The distance between all means and the mean of means is smaller than cutoff.
            // Condition 2: The distance between all stddevs and the mean of stddevs is smaller than cutoff.
            if ($boolmean && $boolstddev) {
                $bestfound = true;
                $best = $slices;
            } else if ((is_null($bestdistmean) || $distsummean < $bestdistmean) &&
                (is_null($bestdiststddev) || $distsumstddev < $bestdiststddev)) {
                // If the conditions are not fulfilled => keep the best slice based on the summed distances.
                $bestdistmean = $distsummean;
                $bestdiststddev = $distsumstddev;
                $best = $slices;
            }
        }

        $func2 = function($a) {
            $func3 = function($b) {
                return $b[0];
            };
            return array_map($func3, $a);
        };

        return array_map($func2, $best);
    }


    /**
     * Returns weights for criterions
     *
     * @return array
     */
    public function get_weights() {

        return array('big5_extraversion' => 4, 'big5_conscientiousness' => 4, 'knowledge_two' => 2);
    }

    /**
     * Scientific division of users and creation of participants
     *
     * @param $users Two parted array - first part is all groupal users, second part are all random users
     * @return array
     */
    public function run_grouping($users) {

        $groupsizes = $this->store->determine_group_size($users);

        $specification = $this->get_specification();
        list ($configurations, $specs) = $specification;
        $weights = $this->get_weights();

        $numberofslices = count($specification[0]);

        if (count($users[0]) < $numberofslices) {
            return [];
        }

        $slices = $this->get_optimal_slices($users[0], $numberofslices, $specs);

        $cohorts = $this->build_cohorts($slices, $groupsizes[0], $specification, $weights);

        // Handle all users with incomplete or no questionnaire submission.
        $randomkey = "rand:1;mrand:_;ex:_;gh:_";

        $randomparticipants = $this->participantparser->build_empty_participants($users[1]);
        $randomcohort = $this->build_cohort($randomparticipants, $groupsizes[1], $randomkey);

        $cohorts[$randomkey] = $randomcohort;

        return $cohorts;
    }


    /**
     * Computes cohorts by slices and configurations
     *
     * @param $slices
     * @param $groupsize
     * @param $specification
     * @return array
     */
    private function build_cohorts($slices, $groupsize, $specification, $weights = null) {

        // Loop preparation.
        $numberofslices = count($slices);
        list ($configurations, $specs) = $specification;
        $configurationkeys = array_keys($configurations);
        $cohorts = array();
        // Loop to iterate over slices and run GroupAL for each slice.
        for ($i = 0; $i < $numberofslices; $i++) {
            $slice = $slices[$i];

            $configurationkey = $configurationkeys[$i];
            $configuration = $configurations[$configurationkey];

            $rawparticipants = $this->participantparser->build_participants($slice, $specs, $weights);
            $participants = $this->configure_participants($rawparticipants, $configuration);

            $cohorts[$configurationkey] = $this->build_cohort($participants, $groupsize, $configurationkey);
        }

        return $cohorts;
    }

    /**
     * Returns specification for different algorithmic formations
     *
     * @return array
     */
    private function get_specification() {

        $big5specs = mod_groupformation_data::get_criterion_specification('big5');
        $knowledgespecs = mod_groupformation_data::get_criterion_specification('knowledge');

        unset($big5specs['labels']['neuroticism']);
        unset($big5specs['labels']['openness']);
        unset($big5specs['labels']['agreeableness']);
        unset($knowledgespecs['labels']['one']);

        $specs = ["big5" => $big5specs, 'knowledge'=>$knowledgespecs];

        $configurations = array(
            "mrand:0;ex:1;gh:1;vw:0" => array('big5_extraversion' => true, 'big5_conscientiousness' => true, 'knowledge_two' => false),
            "mrand:0;ex:1;gh:0;vw:0" => array('big5_extraversion' => true, 'big5_conscientiousness' => false, 'knowledge_two' => false),
            "mrand:0;ex:0;gh:0;vw:0" => array('big5_extraversion' => false, 'big5_conscientiousness' => false, 'knowledge_two' => false),
            "mrand:0;ex:0;gh:1;vw:0" => array('big5_extraversion' => false, 'big5_conscientiousness' => true, 'knowledge_two' => false),
        );

        return [$configurations, $specs];
    }

    /**
     * Creates evenly distributed slices by using the linearized eval score
     *
     * @param $scores
     * @param $numberofslices
     * @return array
     */
    private function get_slices($scores, $numberofslices) {


        $slices = range(1, $numberofslices);
        $userslices = [];
        foreach ($scores as $tuple) {
            if (count($slices) == 0) {
                $slices = range(1, $numberofslices);
            }

            $user = $tuple[0];
            $score = $tuple[1];

            $assignto = array_rand($slices);

            if (!isset($userslices[$assignto])) {
                $userslices[$assignto] = [];
            }

            $userslices[$assignto] = array_merge([[$user, $score]], $userslices[$assignto]);

            unset($slices[$assignto]);
        }

        return $userslices;
    }

}