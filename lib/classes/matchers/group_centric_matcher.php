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
 * This class contains an implementation of an matcher interface which handles
 * the group centric matching
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/matchers/imatcher.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/cohorts/cohort.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");

class lib_groupal_group_centric_matcher implements lib_groupal_imatcher {

    /**
     * @param $notYetMatched : list of participants  (referenced!)
     * @param $groups : list of groups (!referenced)
     * @return : list of groups (as given)
     */
    public function matchToGroups(&$notYetMatched, &$groups) {
        $gpi = 0.0; // Float
        $gpi_tmp = 0.0; // Float
        $delta = 0.0; // Float
        $delta_old = -INF;
        $bestParticipant = null; // Participant instance to add.

        // Search the best participant for the group
        foreach ($groups as $g) {

            for ($j = 0; $j <
            lib_groupal_group::getGroupMembersMaxSize(); $j++) {  // Loop for a max of n rounds to fill up
                // If the group is full then go on with the next group.
                if (count($g->getParticipants()) >= lib_groupal_group::getGroupMembersMaxSize()) {
                    break;
                }
                if (count($notYetMatched) == 0) {
                    break;
                }

                $bestParticipant = $notYetMatched[0]; // Start with next best candidate
                // Then loop and find better candidates.
                for ($i = 0; $i < count($notYetMatched); $i++) {

                    if (count($g->getParticipants()) == 0) {
                        $bestParticipant = $notYetMatched[0];  // XXX: THis can be improved by selecting a random element...
                        break; // end search as the group was empty anyway..
                    }

                    // Get the current gpi of the group.
                    $gpi = $g->getGroupPerformanceIndex();
                    // Add an participant to the group.
                    // Calculate new $gpi.
                    $g->addParticipant($notYetMatched[$i]);
                    $gpi_tmp = $g->getGroupPerformanceIndex();
                    // Remove participant from group.
                    $g->removeParticipant($notYetMatched[$i]);
                    // Calculate the delta between gpi of the group and the gpi of the group + 1 participant.
                    $delta = $gpi_tmp - $gpi;
                    // Transform to percentages.
                    if (abs($gpi) > 0.001) {  // Never use !== 0 on floats!
                        $delta = $delta / $gpi;
                    }

                    // If for this group performance increase the most than safe the new candidate.
                    if ($delta > $delta_old) {
                        $bestParticipant = $notYetMatched[$i];
                        $delta_old = $delta;
                    }
                }

                // Now best participant is the participant with the best performance increase for the group.
                $delta_old = -INF;
                $g->addParticipant($bestParticipant);
                // Remove bestParticipant from $notYetMatched-List.
                array_splice($notYetMatched, array_search($bestParticipant, $notYetMatched), 1);
            }
        }
        return $groups;
    }

}