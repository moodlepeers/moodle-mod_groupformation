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
 * the particiüant centric matching
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/matchers/imatcher.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");

class lib_groupal_participant_centric_matcher implements lib_groupal_imatcher {
    /**
     * @param $notYetMatched : array of participants
     * @param $groups : array of groups
     * @return : list of groups
     */
    public function matchToGroups(&$notYetMatched, &$groups) {
        $gpi = 0.0; // Float
        $gpi_tmp = 0.0; // Float
        $delta = 0.0; // Float
        $delta_old = -INF;
        $bestGroup = $groups[0];
        $p = null; // Participant.
        $hasProgress = true;

        // Set one pivot-element for each group.
        foreach ($groups as $g) {
            if (count($g->getParticipants()) === 0 && count($notYetMatched) > 0) {
                $g->addParticipant($notYetMatched[0]);
                array_splice($notYetMatched, 0, 1);
            }
        }

        // Search the best group for one participant.
        while (count($notYetMatched) > 0 && hasProgress === true) {
            $p = $notYetMatched[0];
            $hasProgress = false; // Indicate that at least one group got a new member.

            foreach ($groups as $g) {
                if (count($g->getParticipants()) >= lib_groupal_group::getGroupMembersMaxSize()) {
                    continue;
                }

                // Get the current gpi of the group.
                $gpi = $g->getGroupPerformanceIndex();
                // Add an participant to the group.
                // Calculate new $gpi.
                $g->addParticipant($p);
                $gpi_tmp = $g->getGroupPerformanceIndex();
                // Remove participant from group.
                $g->removeParticipant($p);
                // Calculate the delta between gpi of the group and the gpi of the group + 1 participant.
                $delta = $gpi_tmp - $gpi;
                // Convert to percentages.
                if ($gpi > 0.001) {  // never use !== 0 on floats
                    $delta = $delta / $gpi;
                }

                // If for this group performance increase the most than safe the group as an candidate.
                if ($delta > $delta_old) {
                    $bestGroup = $g;
                    $delta_old = $delta;
                    $hasProgress = true;
                }
            }

            // Now best group is the candidate  with the best performance increase. with the current participant $p as participant.
            if ($hasProgress === true) {
                $delta_old = 0.0; // TODO float.MinValue.
                $bestGroup->addParticipant($p);
                // 	Remove $p from $notYetMatched-List.
                array_splice($notYetMatched, array_search($p, $notYetMatched), 1);
            } // Do not set hasProgress= false, this happens in while above.
        }
        return $groups;
    }
}