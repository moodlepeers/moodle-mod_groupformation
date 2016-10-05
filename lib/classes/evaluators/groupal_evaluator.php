<?php
// This file is part of PHP implementation of GroupAL
// http://sourceforge.net/projects/groupal/
//
// GroupAL is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// GroupAL implementations are distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with GroupAL. If not, see <http://www.gnu.org/licenses/>.
//
//  This code CAN be used as a code-base in Moodle
// (e.g. for moodle-mod_groupformation). Then put this code in a folder
// <moodle>\lib\groupal
/**
 * This class contains an implementation of an evaluator interface which handles
 * the evaluation of groups
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/manhattan_distance.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/ievaluator.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");

class lib_groupal_evaluator implements lib_groupal_ievaluator {


    private $distanceFunction; // Object which implements IDistance.

    public function __construct() {
        $this->distanceFunction = new lib_groupal_manhattan_distance();
    }


    /**
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
     * @return float
     */

    public function evaluateGroupPerformanceIndex(lib_groupal_group $group) {
        // All Normalized paar performance indices of a Group
        $NPIs = array(); // Generic List: float.

        // One Normalized paar performance index of a minimal Group (two entries)
        $npi = 0.0; // float.
        $gpi = 0.0; // float.
        $participants = $group->getParticipants();
        $numParticipants = count($participants);
        if ($numParticipants == 0) {
            return 0;
        }

        // Calculate npi for every pair of entries in the  group g (but not double and not compare with oneself!)
        for ($i = 0; $i < $numParticipants - 1; $i++) {
            for ($j = $i + 1; $j < $numParticipants; $j++) {
                // Calulate normlizedPaarperformance index.
                $npi = $this->calcNormalizedPairPerformance($participants[$i], $participants[$j]);
                $NPIs[] = $npi;
            }
        }

        $group->results = $this->getPerformanceIndex($NPIs);
        $gpi = $group->results->performanceIndex;
        $group->setGroupPerformanceIndex($gpi);
        return $gpi;
        // TODO Test functionality  (yes!); Issue #3.
    }


    /**
     * @param lib_groupal_cohort $cohort
     * @return double
     */
    public function evaluateCohortPerformanceIndex(lib_groupal_cohort $cohort) {
        if (count($cohort->groups) == 0) {
            return 0;
        }
        $GPIs = array(); // Double list.
        for ($i = 0; $i < count($cohort->groups); $i++) {
            $cohort->groups[$i]->calculateGroupPerformanceIndex();
            $GPIs[] = $cohort->groups[$i]->getGroupPerformanceIndex();
        }
        $results = $this->getPerformanceIndex($GPIs);
        $cohort->results = $results;
        return $results->performanceIndex;

    }

    /**
     * @param float[] $arrayOfPerformanceIndices (generic List)
     * @return lib_groupal_statistics
     */
    public static function getPerformanceIndex($arrayOfPerformanceIndices) {
        if (count($arrayOfPerformanceIndices) < 1) {
            return new lib_groupal_statistics();
        }

        // Calculate avergae of NPIs
        $avg = ((float) array_sum($arrayOfPerformanceIndices)) / count($arrayOfPerformanceIndices); // float.


        // Calculate standard deviation   (which is all diffs of elements and avg squared and finally summed up.
        $sumOfQuadErrors = 0.0; // Double.
        foreach ($arrayOfPerformanceIndices as $pi) {
            $diff = $pi - $avg;
            $sumOfQuadErrors += pow($diff, 2);
        }

        $stdDev = (float) 0.0;

        // Standard deaviation of all npi values (NPIs) in one Groups.
        if (count($arrayOfPerformanceIndices) != 1) {
            $stdDev = sqrt($sumOfQuadErrors) / (count($arrayOfPerformanceIndices) - 1); // Float.
        }

        // Normalize stdNPIs
        $nStd = 1 / (1 + $stdDev); // float.
        $performanceIndex = count($arrayOfPerformanceIndices) < 2 ? $avg : $avg * $nStd;
        $s = new lib_groupal_statistics();

        $s->n = count($arrayOfPerformanceIndices);
        $s->avg = $avg;
        $s->stDev = $stdDev;
        $s->normStDev = $nStd;
        $s->performanceIndex = $performanceIndex;

        return $s;

    }


    /**
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
     * @param lib_groupal_participant $p1
     * @param lib_groupal_participant $p2
     * @return float
     */

    public function calcNormalizedPairPerformance(lib_groupal_participant $p1, lib_groupal_participant $p2) {
        // The summed distances of all hommogeneous values
        $homVal = 0.0; // float
        // The summed distances of all heterogeneous values
        $hetVal = 0.0; // float
        // Not normalized pairperformance index (hetVal - homVal)
        $pairPerformanceIndex = 0; // float
        $c2 = null; // Criterion for comparison
        // Distance between two Criteria
        $d = 0.0; // float
        // weighted distance
        $wd = 0.0; // float
        // Normlized pair performance index
        $npi = 0.0; // float.

        if (count($p1->getCriteria()) !== count($p2->getCriteria())) {
            throw new Exception("calcPairPerformance: the entries have different count of criteria!!!");
        }

        foreach ($p1->getCriteria() as $c1) {
            // Get the same Criterion of the other participant (first criterion of $p2, that matches condition same as ).
            $c2 = null;
            foreach ($p2->getCriteria() as $cc) {
                if ($c1->getName() == $cc->getName()) {
                    $c2 = $cc;
                    break;
                }
            }
            if ($c2 === null) {
                throw new Exception("code error; unreachable state reached.");
            }

            // Calculate Manhattan distanze for both Criteria
            // and normalize the distanze over the maximal amount of dimensions so evry criterion gets a value between 0 and 1
            // (otherwise the criterion will be unthought weighted ).

            $d = $this->distanceFunction->normalizedDistance($c1, $c2);

            $wd = $d * $c1->getWeight();
            if ($c1->getIsHomogeneous()) {
                $homVal += $wd;
            }
            else {
                $hetVal += $wd;
            }
        }
        $pairPerformanceIndex = $hetVal - $homVal;
        $maxDist = 0.0; // Float.
        // Worst case Heterogen criteria is 0 and hom is 1 than the value for pairPerformanceIndex < 0.
        // therfore the worst possible value for hom criteria is added to the pairPerformanceIndex: and the target
        // set lies between 0 and 1
        $homMaxDist = 0.0; // Float.
        // Beacuse i normalize each distance of two criterions over their highest possible value
        // here i neede to normalize pairPerformanceIndex by the count of the Criterions multiplied by its weight.
        foreach ($p1->getCriteria() as $c) {
            if ($c->getIsHomogeneous()) {
                $homMaxDist += 1 * $c->getWeight();
            }
            $maxDist += 1 * $c->getWeight();
        }

        $npi = ($pairPerformanceIndex + $homMaxDist) / $maxDist;

        return $npi;

        // TODO test functionality (oh yes!); Issue #3.
    }

}