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
require_once($CFG->dirroot . "/lib/groupal/classes/optimizers/ioptimizer.php");

class lib_groupal_optimizer implements lib_groupal_ioptimizer {

    public $matcher;

    public function __construct(lib_groupal_imatcher $matcher) {
        $this->matcher = $matcher;
    }

    public function optimizeCohort(lib_groupal_cohort $cohort) {
        $groups = $cohort->groups;
        // Throw new NotImplementedException("optimize if it really optimizes, not else
        // sort cohort by group performance index.
        try {
            // TODO wie genau sortieren.
        } catch (Exception $e) {
            throw new Exception("DefaultOptimizer optimizeCohort: something seems wrong with sorting groups by their performance index value" +
                $e->getTrace());
        }

        // For each pair of good and bad group try to average them.
        for ($i = 0; $i < count($this->groups / 2); $i++) {
            $goodGroup = $this->groups[$i];
            $badGroup = $this->groups[(count($this->groups) - 1) - $i];
            $this->averageTwoGroups($goodGroup, $badGroup);
        }
        $cohort->calculateCohortPerformanceIndex();
    }


    public function averageTwoGroups(lib_groupal_group &$goodgroup, lib_groupal_group &$badgroup) {
        if (abs($goodgroup->getGroupPerformanceIndex() - $badgroup->getGroupPerformanceIndex()) < 0.02) {
            return;
        }
        // Dissolve the groups and randomize the position of participant.
        $localNGT = array(); // List of Participants.
        foreach ($goodgroup->get_participants() as $p) {
            $localNGT[] = $p;
        }
        foreach ($badgroup->get_participants() as $p) {
            $localNGT[] = $p;
        }

        // Randomize position of entries.
        $this->shuffle($localNGT);

        // match the groups new
        $g1 = new lib_groupal_group();
        $g2 = new lib_groupal_group();

        $newGroups = array($g1, $g2);
        $this->matcher->matchToGroups($localNGT, $newGroups);
        // First condition for a better PerformanceIndex: the AVGGroupPerformanceIndex raises.
        $oldAvg = ($goodgroup->getGroupPerformanceIndex() - $badgroup->getGroupPerformanceIndex()) / 2;
        $newAvg = ($g1->getGroupPerformanceIndex() - $g2->getGroupPerformanceIndex()) / 2;
        $firstCondition = $newAvg > $oldAvg;
        // Second condition for a better PerformanceIndex: the stdDiaviation gets smaller so
        // the AVGGroupPerformanceIndex becomes more equal
        // on the other hand the average gets just.
        $oldStd = abs($goodgroup->getGroupPerformanceIndex() - $badgroup->getGroupPerformanceIndex());
        $newStd = abs($g1->getGroupPerformanceIndex() - $g2->getGroupPerformanceIndex());
        $secondCondition = $newStd < $oldStd && abs($newAvg - $oldAvg) < 0.01;
        if ($firstCondition) {
            $goodgroup->set_participants($g1->get_participants());
            $goodgroup->setGroupPerformanceIndex($g1->getGroupPerformanceIndex());
            $badgroup->set_participants($g2->get_participants());
            $badgroup->setGroupPerformanceIndex($g2->getGroupPerformanceIndex());
        }
    }

    // Shuffle a List of entries.
    public function shuffle(array &$list) {
        // TODO Randomize Array Entries.
    }

}