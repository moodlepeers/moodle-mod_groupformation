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
 * This class contains an implementation of a HashMap
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
class lib_groupal_hash_map {

    private $hashMap = array();

    /**
     * Adding Pairs of key and value elements and returns $k
     *
     * @param $k Key
     * @param $v Value
     * @return $k Key
     */
    public function add($k, $v) {
        $this->hashMap[$k] = $v;
        return $k;
    }

    /**
     * Changing value $v of existing, given $k
     *
     * @param $k Key
     * @param $v Value
     * @return boolean: true if change successful, false if $key does not exist
     */
    public function set($k, $v) {
        if ($this->containsKey($k)) {
            $this->hashMap[$k] = $v;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns value for key $k
     *
     * @param $k key
     * @return Value
     * @throws Exception if key does not exist
     */
    public function getValue($k) {
        if ($this->containsKey($k)) {
            return $this->hashMap[$k];
        } else {
            throw new Exception("key does not exist");
        }
    }

    /**
     * Remove given key $k and value $v from assoc. array
     *
     * @param $k Key
     * @return boolean: true if removing successful, false if $key does not exist
     */
    public function remove($k) {
        if ($this->containsKey($k)) {
            unset($this->hashMap[$k]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if key exists in hashMap
     *
     * @param $k
     * @return bool
     */
    public function containsKey($k) {
        return array_key_exists($k, $this->hashMap);
    }

}