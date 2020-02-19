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
 * This class contains an implementation of an matcher interface which handles the group centric matching
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/matchers/imatcher.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/cohorts/cohort.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");

/**
 * Class mod_groupformation_group_centric_matcher
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_group_centric_matcher implements mod_groupformation_imatcher {

    /**
     * Match to groups
     *
     * @param array $notyetmatched
     * @param array $groups
     * @return array
     */
    public function match_to_groups(&$notyetmatched, &$groups) {
        $deltaold = -INF;
        $bestparticipant = null; // Participant instance to add.
        
        // Search the best participant for the group.
        foreach ($groups as $g) {

            for ($j = 0; $j < mod_groupformation_group::get_group_members_max_size(); $j++) {
                // Loop for a max of n rounds to fill up.
                // If the group is full then go on with the next group.
                if (count($g->get_participants()) >= mod_groupformation_group::get_group_members_max_size()) {
                    break;
                }
                if (count($notyetmatched) == 0) {
                    break;
                }

                $bestparticipant = $notyetmatched[0]; // Start with next best candidate
                // Then loop and find better candidates.
                for ($i = 0; $i < count($notyetmatched); $i++) {

                    if (count($g->get_participants()) == 0) {
                        $bestparticipant = $notyetmatched[0];  // XXX: THis can be improved by selecting a random element...
                        break; // end search as the group was empty anyway..
                    }

                    // Get the current gpi of the group.
                    $gpi = $g->get_gpi();
                    // Add an participant to the group.
                    // Calculate new $gpi.
                    $g->add_participant($notyetmatched[$i]);
                    $gpitmp = $g->get_gpi();
                    // Remove participant from group.
                    $g->remove_participant($notyetmatched[$i]);
                    // Calculate the delta between gpi of the group and the gpi of the group + 1 participant.
                    $delta = $gpitmp - $gpi;
                    // Transform to percentages.
                    if (abs($gpi) > 0.001) {  // Never use !== 0 on floats!
                        $delta = $delta / $gpi;
                    }

                    // If for this group performance increase the most than safe the new candidate.
                    if ($delta > $deltaold) {
                        $bestparticipant = $notyetmatched[$i];
                        $deltaold = $delta;
                    }
                }

                // Now best participant is the participant with the best performance increase for the group.
                $deltaold = -INF;
                $g->add_participant($bestparticipant);
                // Remove bestparticipant from $notYetMatched-List.
                array_splice($notyetmatched, array_search($bestparticipant, $notyetmatched), 1);
            }
        }
        return $groups;
    }

}