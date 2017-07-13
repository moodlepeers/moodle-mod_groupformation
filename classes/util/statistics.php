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
 * This is a csv writer for exporting DB data
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

class mod_groupformation_statistics {

    /**
     * Returns mean of values
     *
     * @param array $values
     * @return float
     */
    public static function mean($values) {
        $sum = array_sum($values);

        return floatval($sum) / count($values);
    }

    /**
     * Returns variance for values
     *
     * @param array $values
     * @param null $mean
     * @return float
     */
    public static function variance($values, $mean = null) {
        if (is_null($mean)) {
            $mean = self::mean($values);
        }
        $temp = 0;

        foreach($values as $user){
            $temp += pow($user - $mean, 2);
        }
        return floatval($temp) / count($values);
    }

    /**
     * Returns standard deviation for values
     *
     * @param array $values
     * @param null $variance
     * @return float
     */
    public static function std_deviation($values, $variance = null) {
        if (is_null($variance)) {
            $variance = self::variance($values);
        }
        return sqrt($variance);
    }

}