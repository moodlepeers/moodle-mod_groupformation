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
 * This class contains an implementation of an distance interface which is based
 * manhattan distance
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/idistance.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");

class lib_groupal_manhattan_distance implements lib_groupal_idistance {

    /**
     * normes distance for each dimension (INTERNAL method)
     * return max value is number of dimensions
     * @params Criterion $c1
     * @params Criterion $c1
     */
    private function getDistance(lib_groupal_criterion $cr1, lib_groupal_criterion $cr2) {
        $distance = 0.0; // float
        for ($i = 0; $i < count($cr1->getValues()); $i++) {
            $distance += abs(($cr1->getValue($i) - $cr2->getValue($i)) / $cr1->getMaxValue());
        }
        return $distance;
    }

    /** Both given crtieria must be of same type and same number of values
     * @param lib_groupal_criterion $c1
     * @param lib_groupal_criterion $c2
     * @return float  in [0,1] normalized distance (divided by number of criteria values and value interval space)
     */
    public function normalizedDistance(lib_groupal_criterion $c1, lib_groupal_criterion $c2) {

        $result = ($this->getDistance($c1, $c2) / count($c1->getValues()));
        return $result;
    }

}