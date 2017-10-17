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
 * This class contains an implementation of an distance interface which is based
 * manhattan distance
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/idistance.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");

class mod_groupformation_manhattan_distance implements mod_groupformation_idistance {

    /**
     * normes distance for each dimension (INTERNAL method)
     * return max value is number of dimensions.
     *
     * @param mod_groupformation_criterion $cr1
     * @param mod_groupformation_criterion $cr2
     * @return float|number
     */
    private function get_distance(mod_groupformation_criterion $cr1, mod_groupformation_criterion $cr2) {
        $distance = 0.0;
        for ($i = 0; $i < count($cr1->get_values()); $i++) {
            $distance += abs(($cr1->get_value($i) - $cr2->get_value($i)) / $cr1->get_max_value());
        }
        return $distance;
    }

    /**
     * Both given crtieria must be of same type and same number of values.
     *
     * @param mod_groupformation_criterion $c1
     * @param mod_groupformation_criterion $c2
     * @return float  in [0,1] normalized distance (divided by number of criteria values and value interval space)
     */
    public function normalized_distance(mod_groupformation_criterion $c1, mod_groupformation_criterion $c2) {

        $result = ($this->get_distance($c1, $c2) / count($c1->get_values()));
        return $result;
    }

}