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
 * This class contains an implementation of an matcher interface which handles the participant centric matching
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/matchers/imatcher.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");

/**
 * Class mod_groupformation_participant_centric_matcher
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_participant_centric_matcher implements mod_groupformation_imatcher {
    /**
     * Match to groups
     *
     * @param array $notyetmatched
     * @param array $groups
     * @return array
     */
    public function match_to_groups(&$notyetmatched, &$groups) {
        $deltaold = -INF;
        $bestgroup = $groups[0];
        $p = null;
        $hasprogress = true;

        // Set one pivot-element for each group.
        foreach ($groups as $g) {
            if (count($g->getParticipants()) === 0 && count($notyetmatched) > 0) {
                $g->addParticipant($notyetmatched[0]);
                array_splice($notyetmatched, 0, 1);
            }
        }

        // Search the best group for one participant.
        while (count($notyetmatched) > 0 && $hasprogress === true) {
            $p = $notyetmatched[0];
            $hasprogress = false; // Indicate that at least one group got a new member.

            foreach ($groups as $g) {
                if (count($g->getParticipants()) >= mod_groupformation_group::get_group_members_max_size()) {
                    continue;
                }

                // Get the current gpi of the group.
                $gpi = $g->getGroupPerformanceIndex();
                // Add an participant to the group.
                // Calculate new $gpi.
                $g->addParticipant($p);
                $gpitmp = $g->getGroupPerformanceIndex();
                // Remove participant from group.
                $g->removeParticipant($p);
                // Calculate the delta between gpi of the group and the gpi of the group + 1 participant.
                $delta = $gpitmp - $gpi;
                // Convert to percentages.
                if ($gpi > 0.001) {
                    $delta = $delta / $gpi;
                }

                // If for this group performance increase the most than safe the group as an candidate.
                if ($delta > $deltaold) {
                    $bestgroup = $g;
                    $deltaold = $delta;
                    $hasprogress = true;
                }
            }

            // Now best group is the candidate  with the best performance increase. with the current participant $p as participant.
            if ($hasprogress === true) {
                $deltaold = 0.0;
                $bestgroup->addParticipant($p);
                // Remove $p from $notYetMatched-List.
                array_splice($notyetmatched, array_search($p, $notyetmatched), 1);
            } // Do not set hasprogress= false, this happens in while above.
        }
        return $groups;
    }
}