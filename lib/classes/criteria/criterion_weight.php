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
 * This class contains a static array of criteria weights.
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/util/hash_map.php");

/**
 * Class mod_groupformation_criterion_weight
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_criterion_weight {

    /** @var mod_groupformation_hash_map This hash map contains all criterion weights */
    public static $criterionweights;

    /**
     * Initializes the weights.
     */
    public static function init() {
        if (static::$criterionweights == null) {
            static::$criterionweights = new mod_groupformation_hash_map();
        }
    }

    /**
     * Adds criterion weight to static collection of all weights.
     *
     * @param string $k
     * @param number $v
     * @throws Exception if key already exists
     * @return true
     */
    public static function add_criterion_weight($k, $v) {
        self::init();
        if (static::$criterionweights->contains_key($k)) {
            throw new Exception("key already in use");
        }
        static::$criterionweights->add($k, $v);
        return true;
    }

    /**
     * Changing CriterionWeights; only allowed if keys are equal to existing ones and the sum of all weights is 1.
     *
     * @param mod_groupformation_hash_map $newweights
     * @return bool
     */
    public static function change_weights(mod_groupformation_hash_map $newweights) {
        $issamekeyset = true;
        foreach ($newweights->keys as $s) {
            $issamekeyset &= self::$criterionweights->contains_key($s);
        }
        // TODO foreach loop on values and calculate the sum of values.
        $sumofvalues = 1;
        if ($sumofvalues == 1 && $issamekeyset) {
            static::$criterionweights = $newweights;
            return true;
        }
        return false;
    }
    // TODO wieso muss Summer der Values 1 ergeben? warum diese überprüfung? (JK: Normalisierung prüfen).

    /**
     * Returns weight
     *
     * @param string $criterionname
     * @return float
     * @throws Exception if $criterionName does not exist
     */
    public static function get_weight($criterionname) {
        try {
            return self::$criterionweights->get_value($criterionname);
        } catch (Exception $e) {
            throw new Exception("mod_groupformation_criterion_weight does not contain the CriterionName you are looking for!", $e);
        }
    }

    /**
     * Adds a key, value pair if not already there. allows repetitive calls as long as key AND value are same.
     *
     * @param string $name
     * @param float $weight
     * @throws Exception if key exists already with different value
     * @return true
     */
    public static function add_if_not_allready_exist($name, $weight) {
        self::init();
        if (self::$criterionweights->contains_key($name) && self::$criterionweights->get_value($name) != $weight
        ) {
            throw new Exception("mod_groupformation_criterion_weight: the given CriterionName has already an other weight");
        }
        // Do not call addCriterionWeight as it throws exceptions on repetive calls.
        self::$criterionweights->add($name, $weight);
        return true;
    }
}