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
 * This class contains an implementation of a HashMap
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class mod_groupformation_hash_map
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_hash_map {

    /** @var array Hashmap */
    private $hashmap = array();

    /**
     * Adding Pairs of key and value elements and returns $k
     *
     * @param number $k Key
     * @param number $v Value
     * @return number
     */
    public function add($k, $v) {
        $this->hashmap[$k] = $v;
        return $k;
    }

    /**
     * Changing value $v of existing, given $k
     *
     * @param number $k Key
     * @param number $v Value
     * @return boolean: true if change successful, false if $key does not exist
     */
    public function set($k, $v) {
        if ($this->contains_key($k)) {
            $this->hashmap[$k] = $v;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns value for key $k
     *
     * @param number $k key
     * @return mixed
     * @throws Exception if key does not exist
     */
    public function get_value($k) {
        if ($this->contains_key($k)) {
            return $this->hashmap[$k];
        } else {
            throw new Exception("key does not exist");
        }
    }

    /**
     * Remove given key $k and value $v from assoc. array
     *
     * @param number $k Key
     * @return boolean: true if removing successful, false if $key does not exist
     */
    public function remove($k) {
        if ($this->contains_key($k)) {
            unset($this->hashmap[$k]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if key exists in hashMap
     *
     * @param number $k
     * @return bool
     */
    public function contains_key($k) {
        return array_key_exists($k, $this->hashmap);
    }

}