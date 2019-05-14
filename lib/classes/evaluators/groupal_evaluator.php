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
 * GroupAL evaluator
 *
 * This class contains an implementation of an evaluator interface which handles
 * the evaluation of groups
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/manhattan_distance.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/bin_distance.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/many_bin_distance.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/both_bin_types_no_one_alone_distance.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/ievaluator.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/criteria/tests/test_one_of_bin.php');

/**
 * Class mod_groupformation_evaluator
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_evaluator implements mod_groupformation_ievaluator
{

    /**
     * mod_groupformation_evaluator constructor.
     */
    public function __construct()
    {
    }

    /**
     * Evaluates GPI
     *
     * homogeneous criteria->subtract values-> the smaller the better
     * heterogeneous criteria->subtract values->the bigger the better
     * the difference: heterogeneous value - homogeneous is the return value the biger the better
     * normalize by the best possible GroupPerformanceIndex
     *      (the difference of a perfect homogeneous pair of values is 0)
     *      (the difference of a perfect heterogeneous pair of value is 1)
     *      (the besst possible (non realistic) GroupPerformanceIndex, with resct to these rules, is
     *      the sum (of 1 to nummber of groupmembers count) * (the count of heterogen criterion) *
     *          (the sum of the count of each criterions values)
     *      e.g. for a Group of 3 persons with 2 heterogen Criterion each with 4 values
     *      the best posible GroupPerformanceIndex would be (3+2+1)*2*4
     *
     * @param mod_groupformation_group $group
     * @return float|int
     * @throws Exception
     */
    public function evaluate_gpi(mod_groupformation_group $group)
    {
        // All Normalized paar performance indices of a Group
        $npis = array(); // Generic List: float.

        // One Normalized paar performance index of a minimal Group (two entries)
        $npi = 0.0; // float.
        $gpi = 0.0; // float.
        $participants = $group->get_participants();
        $participantcount = count($participants);

        if ($participantcount == 0) {
            return 0;
        }

        //TODO delete if is not running in test mode
        ###################### TEST #######################
//        $test = new test_one_of_bin();
//        $participants = $test->create_participants($participants);
//        $participantcount = count($participants);
//
//        if ($participantcount == 0) {
//            return 0;
//        }
        ###################################################


        // Calculate npi for every pair of entries in the  group g (but not double and not compare with oneself!)
        for ($i = 0;
             $i < $participantcount - 1;
             $i++) {
            for ($j = $i + 1;
                 $j < $participantcount;
                 $j++) {
                // Calulate normlizedPaarperformance index.
                $npi = $this->calc_normalized_pair_performance($participants[$i], $participants[$j]);
                $npis[] = $npi;
            }
        }

        // calculate the npi for once against group functions
        // get the last participants and compare it against the group
        $p = $participants[$participantcount - 1];
        foreach ($p->get_criteria() as $c) {
            // all once against group function starts with both_bin_types
            // check if a function is available in this criterion
            if (strpos($c->get_distance(), 'both_bin_types') === 0) {
                $npi = $this->calc_normalized_one_against_group_performance($c, $group);
                $npis[] = $npi;
            }
        }

        //once against all
        $group->results = $this->get_performance_index($npis);
        $gpi = $group->results->performanceindex;
        $group->set_gpi($gpi);
        return $gpi;
// TODO Test functionality  (yes!); Issue #3.
    }

    /**
     * Evaluate CPI
     *
     * @param mod_groupformation_cohort $cohort
     * @return double
     */
    public
    function evaluate_cpi(mod_groupformation_cohort $cohort)
    {
        if (count($cohort->groups) == 0) {
            return 0;
        }
        $gpis = array(); // Double list.
        for ($i = 0; $i < count($cohort->groups); $i++) {
            $cohort->groups[$i]->calculate_gpi();
            $gpis[] = $cohort->groups[$i]->get_gpi();
        }
        $results = $this->get_performance_index($gpis);
        $cohort->results = $results;
        return $results->performanceindex;

    }

    /**
     * Returns performance index
     *
     * @param array $performanceindices (generic List)
     * @return mod_groupformation_stats
     */
    public
    static function get_performance_index($performanceindices)
    {
        if (count($performanceindices) < 1) {
            return new mod_groupformation_stats();
        }

        // Calculate avergae of NPIs
        $avg = ((float)array_sum($performanceindices)) / count($performanceindices); // float.

        // Calculate standard deviation   (which is all diffs of elements and avg squared and finally summed up.
        $sumquadraticerrors = 0.0; // Double.
        foreach ($performanceindices as $pi) {
            $diff = $pi - $avg;
            $sumquadraticerrors += pow($diff, 2);
        }

        $stddev = (float)0.0;

        // Standard deaviation of all npi values (NPIs) in one Groups.
        if (count($performanceindices) != 1) {
            $stddev = sqrt($sumquadraticerrors) / (count($performanceindices) - 1); // Float.
        }

        // Normalize stdNPIs
        $nstddev = 1 / (1 + $stddev); // float.
        $performanceindex = count($performanceindices) < 2 ? $avg : $avg * $nstddev;
        $s = new mod_groupformation_stats();

        $s->n = count($performanceindices);
        $s->avg = $avg;
        $s->stddev = $stddev;
        $s->normstddev = $nstddev;
        $s->performanceindex = $performanceindex;

        return $s;

    }


    /**
     *  Calculates normalized pair performance
     *
     *  homogeneous criteria->subtract values-> the smaller the better
     *  heterogeneous criteria->subtract values->the biger the better
     *  the difference: heterogeneous value - homogeneous is the return value the biger the better
     *  normalize by the best possible GroupPerformanceIndex
     *              (the difference of a perfect homogeneous pair of values is 0)
     *              (the difference of a perfect heterogeneous pair of value is 1)
     *              (the besst possible (non realistic) GroupPerformanceIndex, with resct to these rules,
     *              is the sum (of 1 to nummber of groupmembers count) * (the count of heterogen criterion) *
     *                  (the sum of the count of each criterions values)
     *              e.g. for a Group of 3 persons with 2 heterogen Criterion each with 4 values the best
     *              posible GroupPerformanceIndex would be (3+2+1)*2*4
     *
     * @param mod_groupformation_participant $p1
     * @param mod_groupformation_participant $p2
     * @return float
     * @throws Exception
     */
    public
    function calc_normalized_pair_performance(mod_groupformation_participant $p1, mod_groupformation_participant $p2)
    {
        // The summed distances of all hommogeneous values.
        $homval = 0.0; // float
        // The summed distances of all heterogeneous values.
        $hetval = 0.0; // float
        // Not normalized pairperformance index (hetval - homval).
        $pairperformanceindex = 0; // float
        $c2 = null; // Criterion for comparison
        // Distance between two Criteria.
        $d = 0.0; // float
        // weighted distance.
        $wd = 0.0; // float
        // Normlized pair performance index.
        $npi = 0.0; // float.

        if (count($p1->get_criteria()) !== count($p2->get_criteria())) {
            throw new Exception("calcPairPerformance: the entries have different count of criteria!!!");
        }

        foreach ($p1->get_criteria() as $c1) {

            // if (strpos($c1->get_distance(), 'both_bin_types_bins_covered_distance') === 1) {

            // Get the same Criterion of the other participant (first criterion of $p2, that matches condition same as ).
            $c2 = null;
            foreach ($p2->get_criteria() as $cc) {
                if ($c1->get_name() == $cc->get_name()) {
                    $c2 = $cc;
                    break;
                }
            }
            if ($c2 === null) {
                throw new Exception("code error; unreachable state reached.");
            }

            // Calculate the specific distance
            // and normalize the distanze over the maximal amount of dimensions so every criterion gets a value between 0 and 1.
            // (otherwise the criterion will be unthought weighted ).
            $distance_name = "mod_groupformation_" . $c1->get_distance();
            $temp_distance = new $distance_name;

            //TODO delete if is not running in test mode
            ###################### TEST #######################
            //$test = new test_one_of_bin();
            //$test->log_criteria("criteria: ", $c1->get_name());
            ###################################################

            $d = $temp_distance->normalized_distance($c1, $c2);

            //  $test->log_performance_index("normalized_distance: ", $d);
            $wd = $d * $c1->get_weight();

            if ($c1->is_homogeneous()) {
                $homval += $wd;
            } else {
                $hetval += $wd;
            }

        }

        $pairperformanceindex = $hetval - $homval;
        $maxdist = 0.0; // Float.
        // Worst case Heterogen criteria is 0 and hom is 1 than the value for pairperformanceindex < 0.
        // therfore the worst possible value for hom criteria is added to the pairperformanceindex: and the target.
        // set lies between 0 and 1.
        $hommaxdist = 0.0; // Float.
        // Beacuse i normalize each distance of two criterions over their highest possible value.
        // here i neede to normalize pairperformanceindex by the count of the Criterions multiplied by its weight.
        foreach ($p1->get_criteria() as $c) {
            if ($c->is_homogeneous()) {
                $hommaxdist += 1 * $c->get_weight();
            }
            $maxdist += 1 * $c->get_weight();
        }

        return ($pairperformanceindex + $hommaxdist) / $maxdist;
    }

    /**
     * Calculates normalized once against group performance
     *
     * @param mod_groupformation_criterion $c1
     * @param mod_groupformation_group $group
     * @return float|int
     * @throws Exception
     */
    public
    function calc_normalized_one_against_group_performance(mod_groupformation_criterion $c1, mod_groupformation_group $group)
    {
        // The summed distances of all hommogeneous values.
        $homval = 0.0; // float
        // The summed distances of all heterogeneous values.
        $hetval = 0.0; // float

        $type = $c1->get_type();
        $property = $c1->get_property();


        //Example array for a criterion $c1 = [0,0,1,0]

        $group_value = array(); // array for values of all group criteria

        // get for each participant the criterion and add the values
        // to the group value array to get a result matrix with all values
        foreach ($group->get_participants() as $p) {
            foreach ($p->get_criterion() as $c) {
                if ($c->get_name() == $c1->get_name()) {
                    for ($i = 0; $i < count($c1->get_values()); $i++) {
                        $group_value[$i] += $c[$i];
                    }
                }
            }
        }


        switch ($c1->get_distance()) { //get_distance is the fitness function of a criterion example: as few bins as possible
            case "both_bin_types_bins_covered_distance":
                $t = $c1->get_t(); // target bin coverage
                $d = $c1->get_d(); //is between 0 and 1 example: 0.5
                $M = count($c1->get_values()); // maximum number of bins that could be covered

                // get the number of bins who is covered. covered means at least one student has chosen
                $b = 0;
                for ($i = 0; $i < count($group_value); $i++) {
                    if ($group_value[$i] < 0) {
                        $b += 1;
                    }
                }
                // at most t bins covered
                if ($property == "most") {
                    if (0 <= $b && $b < $t) {
                        $distance = 1 - (1 - d) * ($b / $t);
                    } else if ($t <= $b && $b <= $M) {
                        $distance = $d * (($M - $b) / ($M - $t));
                    }

                    // at least t bins covered
                } else if ($property == "least") {
                    if (0 <= $b && $b < $t) {
                        $distance = $d * ($b / $t);
                    } else if ($t <= $b && $b <= $M) {
                        $distance = $d + (1 - $d) * (($b - $t) / ($M - $t));
                    }
                }
            case "both_bin_types_bins_as_possible":

                $M = count($c1->get_values()); // maximum number of bins that could be covered

                // get the number of bins who is covered. covered means at least one student has chosen
                $b = 0;
                for ($i = 0; $i < count($group_value); $i++) {
                    if ($group_value[$i] < 0) {
                        $b += 1;
                    }
                }

                // as many bins as possible
                if ($property == "many") {
                    $distance = $b / $M;
                    // as few bins as possible
                } else if ($property == "few") {
                    $distance = 1 - ($b / $M);
                }
            case "both_bin_types_members_in_bin":


        }

        $wd = $distance * $c1->get_weight();

        if ($c1->is_homogeneous()) {
            $homval += $wd;
        } else {
            $hetval += $wd;
        }

        return $this->calc_npi_with_weight($c1, $hetval, $homval);
    }


    /**
     * calculate npi value depends of the criterion weight for groups
     *
     * @param $c
     * @param $hetval
     * @param $homval
     * @return float|int
     */
    public
    function calc_npi_with_weight($c, $hetval, $homval)
    {
        $pairperformanceindex = $hetval - $homval;
        $maxdist = 0.0; // Float.
        // Worst case Heterogen criteria is 0 and hom is 1 than the value for pairperformanceindex < 0.
        // therfore the worst possible value for hom criteria is added to the pairperformanceindex: and the target.
        // set lies between 0 and 1.
        $hommaxdist = 0.0; // Float.
        // Beacuse i normalize each distance of two criterions over their highest possible value.
        // here i neede to normalize pairperformanceindex by the count of the Criterions multiplied by its weight.
        if ($c->is_homogeneous()) {
            $hommaxdist += 1 * $c->get_weight();
        }
        $maxdist += 1 * $c->get_weight();


        return ($pairperformanceindex + $hommaxdist) / $maxdist;
    }
}