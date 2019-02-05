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
 * no one alone in bin
 *
 * This class contains an implementation of an distance interface which is based
 * no one alone in bin distance with the attribute type both bin types
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/idistance_group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/one_of_bin_criterion.php");

/**
 * Class mod_groupformation_both_bin_types_no_one_alone_distance
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic, Stefan Jung
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_both_bin_types_no_one_alone_distance implements mod_groupformation_idistance_group {

    /**
     * normes distance for each dimension (INTERNAL method)
     * return max value is number of dimensions.
     *
     * @param mod_groupformation_criterion $cr1
     * @param mod_groupformation_criterion $group
     * @return float|number
     */
    private function get_distance(mod_groupformation_criterion $cr1, mod_groupformation_group $group) {

        $distance = 1.0;

        // no one alone
        // check each participant until they matched
        // get list of participants from group
        foreach ($group->get_participants() as $p) {
            // get values of cr1
            foreach ($cr1->get_values() as $p1) {
                // get criteria's of participants
                foreach ($p->get_criteria() as $c) {
                    // get values of each criteria
                    foreach ($c->get_values() as $p2) {
                        // get the distance between these two values
                        $d = strcmp($p1, $p2);
                        // if the values are equals return distance of 0.0
                        if ($d == 0) {
                            return 0.0;
                        }
                    }
                }
            }
        }

        return $distance;
    }

    /**
     * Both given crtieria must be of same type and same number of values.
     *
     * @param mod_groupformation_criterion $c1
     * @param mod_groupformation_criterion $c2
     * @return float 1 or 0
     */
    public function normalized_distance(mod_groupformation_criterion $c1, mod_groupformation_group $c2) {
        return $this->get_distance($c1, $c2);
    }

}