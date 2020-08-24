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
 * This class contains an implementation of an matcher interface which handles the group stepwise matching
 *
 * @package     mod_groupformation
 * @author      Johannes Konert, Rene Roepke
 * @copyright   2020 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/matchers/imatcher.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/cohorts/cohort.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");

/**
 * Class mod_groupformation_group_stepwise_matcher
 *
 * @package     mod_groupformation
 * @author      Johannes Konert, Rene Roepke
 * @copyright   2020 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_group_stepwise_matcher implements mod_groupformation_imatcher {

    private function array_unset(&$array, $index) {
        unset($array[$index]);
        $array = array_values($array);
    }

    /**
     * Match to groups
     *
     * @param array $notyetmatched
     * @param array $groups
     * @return array
     */
    public function match_to_groups(&$participants, &$groups) {        
        // fill groups stepwise
        // in each iteration
        // take a group and find the best student for it
        // shuffle or better permute groups to definitely start with other group (modulo?)

        do {
            // shuffle groups to add users not every time in the same order
            shuffle($groups);
            
            // fill groups stepwise
            for ($i = 0; $i < count($groups); $i++) {
                
                // get current group
                $group = $groups[$i];
                // get score of current group
                // $gpi = rand(0,100);
                $gpi = $group->get_gpi();
                // find best participant
                $bestparticipant = null;
                $bestdelta = null;
                $bestparticipantindex = null;
                $bestgpi = null;
                for ($j = 0; $j < count($participants); $j++) { 
                    if (count($group->get_participants())==0) {
                        $bestparticipantindex = $j;
                        break;
                    } else {
                        // get score of current group
                        // $gpi = rand(0,100);
                        $gpi = $group->get_gpi();

                        // add participant to current group
                        // $group .= $currentparticipant;
                        $group->add_participant($participants[$j]);
                        
                        // get score of new group
                        //$gpitmp = $gpi+rand(1,3);
                        $gpitmp = $group->get_gpi();

                        // compute gpi delta
                        $delta = $gpitmp - $gpi;
                        // remove
                        // $group = substr($group, 0, -1);
                        $group->remove_participant_by_id($participants[$j]->get_id());
                        
                        // decide whether delta is better than bestdelta so far
                        if (is_null($bestdelta) || $delta > $bestdelta) {
                            $bestparticipant = $participants[$j];
                            $bestdelta = $delta;
                            $bestparticipantindex = $j;
                            $bestgpi = $gpitmp;
                        }

                        if ($bestdelta == 1) {
                            break;
                        }
                    }                        
                }
                
                // add participant to group
                // $group .= $bestparticipant;
                $group->add_participant($participants[$bestparticipantindex]);
                
                $groups[$i] = $group;
                $gpi = $group->get_gpi();
                
                // remove participant from list
                $this->array_unset($participants, $bestparticipantindex);
                
                // terminate loop when no more participants are available
                if (count($participants) == 0) {
                    break;
                }
            }
        } while (count($participants) > 0);
    }
}