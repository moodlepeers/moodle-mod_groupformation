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
 * This class contains a static array of criteria weights
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/util/hash_map.php");

class lib_groupal_criterion_weight {

    /** @var lib_groupal_hash_map This hash map contains all criterion weights */
    public static $criterionWeights;


    /**
     * Initializes the weights
     */
    public static function init() {
        if (self::$criterionWeights == null) {
            self::$criterionWeights = new lib_groupal_hash_map();
        }
    }

    /**
     * Adds criterion weight to static collection of all weights
     *
     * @param $k String
     * @param $v float
     * @throws Exception if key already exists
     * @return true
     */
    public static function addCriterionWeight($k, $v) {
        self::init();
        if (self::$criterionWeights->containsKey($k)) {
            throw new Exception("key already in use");
        }
        static::$criterionWeights->add($k, $v);
        return true;
    }


    /**
     * Changing CriterionWeights; only allowed if keys are equal to existing ones and the sum of all weights is 1 (normed)
     *
     * @param CriterionWeights as HashMap
     * @return bool
     */
    public static function changeWeights(lib_groupal_hash_map $newWeights) {
        $isSameKeySet = true;
        foreach ($newWeights->keys as $s) {
            $isSameKeySet &= self::$criterionWeights->containsKey($s);
        }
        // TODO foreach loop on values and calculate the sum of values.
        $sumOfValues = 1;
        if ($sumOfValues == 1 && isSameKeySet) {
            self::$criterionWeights = $newWeights;
            return true;
        }
        return false;
    }
    // TODO wieso muss Summer der Values 1 ergeben? warum diese überprüfung? (JK: Normalisierung prüfen).

    /**
     * @param String $criterionName
     * @return float
     * @throws Exception if $criterionName does not exist
     */
    public static function getWeight($criterionName) {
        try {
            return self::$criterionWeights->getValue($criterionName);
        } catch (Exception $e) {
            throw new Exception("lib_groupal_criterion_weight does not contain the CriterionName you are looking for!", $e);
        }
    }

    /**
     * Adds a key, value pair if not already there. allows repetitive calls as long as key AND value are same.
     *
     * @param String $name
     * @param float $weight
     * @throws Exception if key exists already with different value
     * @return true
     */
    public static function addIfNotAllreadyExist($name, $weight) {
        self::init();
        if (self::$criterionWeights->containsKey($name) && self::$criterionWeights->getValue($name) != $weight
        ) {
            throw new Exception("lib_groupal_criterion_weight: the given CriterionName has already an other weight");
        }
        // Do not call addCriterionWeight as it throws exceptions on repetive calls.
        self::$criterionWeights->add($name, $weight);
        return true;
    }
    // TODO Was soll diese Funktion machen??? was ist daran anders als an addCriterionWeights?
    // Die function kann mehrfach aufgerufen werden mit den gleichen key/values ohne exceptions zu werfen.
}