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
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/both_bin_types_distance.php");
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
class mod_groupformation_evaluator implements mod_groupformation_ievaluator {

    /**
     * mod_groupformation_evaluator constructor.
     */
    public function __construct() {
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
    public function evaluate_gpi(mod_groupformation_group $group) {
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
//        $participants = $test->create_manually_participant();
//        $participantcount = count($participants);
//
//        if ($participantcount == 0) {
//            return 0;
//        }
        ###################################################


        //TODO check last participant -> last added is the comparable one for the group


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
            if (strpos($c->get_distance(), 'both_bin_types')) {
                $npi = $this->calc_normalized_once_against_group_performance($p, $group);
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
    function evaluate_cpi(mod_groupformation_cohort $cohort) {
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
    static function get_performance_index($performanceindices) {
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
    function calc_normalized_pair_performance(mod_groupformation_participant $p1, mod_groupformation_participant $p2) {
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

        return $this->calc_npi_with_weight($p1, $hetval, $homval);
    }

    /**
     * Calculates normalized once against group performance
     *
     * @param mod_groupformation_participant $p1
     * @param mod_groupformation_group $group
     * @return float|int
     */
    public
    function calc_normalized_once_against_group_performance(mod_groupformation_participant $p1, mod_groupformation_group $group) {
        // The summed distances of all hommogeneous values.
        $homval = 0.0; // float
        // The summed distances of all heterogeneous values.
        $hetval = 0.0; // float

        foreach ($p1->get_criteria() as $c1) {
            $temp_group = $group;
            $distance_name = "mod_groupformation_" . $c1->get_distance();
            $temp_distance = new $distance_name;

            if (strpos($distance_name, 'both_bin_types')) {
                // delete $p1 from temp_group otherwise they will check the same participant and returns always distance of 0.0
                unset($temp_group->get_participants()[array_search($p1, $temp_group->get_participants())]);

                $d = $temp_distance->normalized_distance($c1, $temp_group);

                $wd = $d * $c1->get_weight();

                if ($c1->is_homogeneous()) {
                    $homval += $wd;
                } else {
                    $hetval += $wd;
                }
            }
        }
        return $this->calc_npi_with_weight($p1, $hetval, $homval);
    }

    /**
     * calculate npi value depends of the criterion weight
     *
     * @param $p1
     * @param $hetval
     * @param $homval
     * @return float|int
     */
    public
    function calc_npi_with_weight($p1, $hetval, $homval) {
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
}