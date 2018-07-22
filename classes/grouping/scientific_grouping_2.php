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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/grouping.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/statistics.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

/**
 * Class mod_groupformation_scientific_grouping_2
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * @param int $groupformationid
     * @throws dml_exception
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
     * Determines how many slices are possible
     *
     * @param $numberofusers
     * @param $groupsize
     * @param $numberofslices
     * @return mixed
     */
    public function determine_number_of_slices($numberofusers, $groupsize, $numberofslices) {
        if ($numberofusers == 0) {
            return $numberofusers;
        }
        $minperslice = $groupsize * 3;
        $div = max(1, intval(floor($numberofusers / $minperslice)));
        return min($div, $numberofslices);
    }

    /**
     * Returns scores
     *
     * @param $users
     * @param $specs
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_scores($users, $specs) {
        $scores = [];

        foreach ($users as $user) {
            $scores[] = array($user, $this->usermanager->get_eval_score($user, $specs));
        }

        $cmp = function($a, $b) {
            return $a[1] < $b[1];
        };

        usort($scores, $cmp);

        return $scores;
    }

    /**
     * Returns equally distributed slices by checking mean and std deviation.
     *
     * @param array $users
     * @param int $numberofslices
     * @param null $specs
     * @param int $groupsize
     * @return array
     * @throws Exception
     */
    public function get_optimal_slices($users, $numberofslices, $specs = null, $groupsize = 3) {
        if ($numberofslices == 0) {
            return array();
        }

        if (count($users) <= $groupsize * 3) {
            return array($users);
        }

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

        $scores = $this->get_scores($users, $specs);

        $best = $this->get_slices($scores, $numberofslices, $groupsize);

        while ($i < 100 && !$bestfound) {
            $i++;
            $slices = $this->get_slices($scores, $numberofslices, $groupsize);
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
                $distmean = abs($mean - $avgmean) / ($avgmean + 0.01);
                $diststddev = abs ($stddev - $avgstddev) / ($avgstddev + 0.01);
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

        return array('big5_extraversion' => 4,
                'big5_conscientiousness' => 4,
                'knowledge_two' => 2,
                'fam_challenge' => 2,
                'fam_interest' => 2,
                'fam_successprobability' => 2,
                'fam_lackofconfidence' => 2);
    }

    /**
     * Scientific division of users and creation of participants
     *
     * @param array $users Two parted array - first part is all groupal users, second part are all random users
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function run_grouping($users) {

        $groupsizes = $this->store->determine_group_size($users);

        $specification = $this->get_specification();

        list ($configurations, $specs) = $specification;
        $weights = $this->get_weights();

        $numberofslices = count($configurations);
        $numberofslices = $this->determine_number_of_slices(count($users[0]), $groupsizes[0], $numberofslices);

        $cohorts = array();

        if ($numberofslices > 0) {

            $slices = $this->get_optimal_slices($users[0], $numberofslices, $specs, $groupsizes[0]);

            $cohorts = $this->build_cohorts($slices, $groupsizes[0], $specification, $weights);
        }

        // Handle all users with incomplete or no questionnaire submission.
        $randomkey = "random:1";

        $randomparticipants = $this->participantparser->build_empty_participants($users[1]);
        $randomcohort = $this->build_cohort($randomparticipants, $groupsizes[1], $randomkey);

        $cohorts[$randomkey] = $randomcohort;
        return $cohorts;
    }

    /**
     * Computes cohorts by slices and configurations
     *
     * @param array $slices
     * @param int $groupsize
     * @param array $specification
     * @param null $weights
     * @return array
     * @throws dml_exception
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
    public function get_specification() {

        $big5specs = mod_groupformation_data::get_criterion_specification('big5');
        $knowledgespecs = mod_groupformation_data::get_criterion_specification('knowledge');
        $famspecs = mod_groupformation_data::get_criterion_specification('fam');

        unset($big5specs['labels']['neuroticism']);
        unset($big5specs['labels']['openness']);
        unset($big5specs['labels']['agreeableness']);
        unset($knowledgespecs['labels']['one']);

        $specs = ['big5' => $big5specs, 'knowledge' => $knowledgespecs, 'fam' => $famspecs];

        $configurations = array(
            "groupal:1;ex:1;gh:1;vw:0;fam:0" => array('big5_extraversion' => true,
                    'big5_conscientiousness' => true, 'knowledge_two' => false, 'fam' => false),
            "groupal:1;ex:1;gh:0;vw:0;fam:0" => array('big5_extraversion' => true,
                    'big5_conscientiousness' => false, 'knowledge_two' => false, 'fam' => false),
            "groupal:1;ex:0;gh:0;vw:0;fam:0" => array('big5_extraversion' => false,
                    'big5_conscientiousness' => false, 'knowledge_two' => false, 'fam' => false),
            "groupal:1;ex:0;gh:1;vw:0;fam:0" => array('big5_extraversion' => false,
                    'big5_conscientiousness' => true, 'knowledge_two' => false, 'fam' => false),
        );

        return [$configurations, $specs];
    }

    /**
     * Creates evenly distributed slices by using the linearized eval score
     *
     * @param array $scores
     * @param int $numberofslices
     * @param int $groupsize
     * @return array
     * @throws Exception
     */
    public function get_slices($scores, $numberofslices, $groupsize = 3) {
        if ($numberofslices == 0 || $groupsize == 0) {
            throw new Exception("Groupsize or Number of Slices cannot be 0");
        }

        // Handling suitable number of students.
        // Suitable = number of slices times groupsize.
        // This way, no incomplete groups are going to be formed.
        $usercount = count($scores);
        $divider = $numberofslices * $groupsize;
        $ganzzahldiv = intval(floor($usercount / $divider));
        $numberofremainingusers = $usercount - $divider * $ganzzahldiv;

        $firstscores = array_slice($scores, 0, $ganzzahldiv * $divider);
        $lastscores = array_slice($scores, $ganzzahldiv * $divider);

        // Complete run.
        $userslices = $this->assign_to_slices($firstscores, $numberofslices);

        // Handling remaining students to only allow one incomplete group.
        $ganzzahldiv = intval(floor($numberofremainingusers / $groupsize));
        $currentnumberofslices = min($numberofslices - 1, $ganzzahldiv);

        $firstscores = array_slice($lastscores, 0, $ganzzahldiv * $groupsize);
        $restscores = array_slice($lastscores, $ganzzahldiv * $groupsize);

        // Creating some slices with group-size size.
        // Reminder run.
        $reminderslices = $this->assign_to_slices($firstscores, $currentnumberofslices);
        // Adding rest scores as a slice.
        $reminderslices[] = $restscores;

        // Combining slices from complete run and reminder run.
        // Starting with slice based on groupformation ID.
        $modulo = $this->groupformationid % $numberofslices;
        for ($i = 0; $i < count($reminderslices); $i++) {
            $userslices[$modulo] = array_merge($userslices[$modulo], $reminderslices[$i]);
            $modulo = ($modulo + 1) % $numberofslices;
        }

        return $userslices;
    }

    /**
     * Assignes participants to slices
     *
     * @param $scores
     * @param $numberofslices
     * @return array
     */
    private function assign_to_slices($scores, $numberofslices) {
        $userslices = array_fill(0, $numberofslices, []);
        $slices = range(1, $numberofslices);

        for ($i = 0; $i < count($scores); $i++) {
            list($user, $score) = $scores[$i];

            if (count($slices) == 0) {
                $slices = range(1, $numberofslices);
            }

            $assignto = array_rand($slices);

            if (!isset($userslices[$assignto])) {
                $userslices[$assignto] = [];
            }

            $userslices[$assignto] = array_merge([[$user, $score]], $userslices[$assignto]);

            unset($slices[$assignto]);
        }
        return array_values($userslices);
    }
}