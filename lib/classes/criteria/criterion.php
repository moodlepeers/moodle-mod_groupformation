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
 * Criterion
 *
 * This abstract class contains method signatures and field variables for organizing
 * values based on users answers
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class mod_groupformation_criterion
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class mod_groupformation_criterion {

    /** @var string The name of an Criterion e.g. "learner style after Silvermann & Felder" */
    protected $name = "";

    /** @var array Value or values of an criterion (as floats) */
    protected $value = array();

    /** @var float Max valid value of an criterion */
    protected $maxvalue = 1.0;

    /** @var float Min valid value of an criterion */
    protected $minvalue = 0.0;

    /** @var bool  flag to mark Criterion as homogeneous or as not homogeneous (heterogeneous) */
    protected $homogeneous = false;

    /** @var string distance name */
    protected $distance = "manhattan_distance";

    /** @var string  */
    protected $property = "";

    /**
     * get property
     *
     * @return string
     */
    public function get_property() {
        return $this->property;
    }

    /**
     * set property
     *
     * @param string $property
     */
    public function set_property($property) {
        $this->property = $property;
    }

    /**
     * get the number of bins
     *
     * @return int
     */
    public function get_number_of_bins() {
        $count = count($this->get_values());
        return $count;
    }

    /**
     * get the distance
     *
     * @return string
     */
    public function get_distance() {
        return $this->distance;
    }

    /**
     * Sets distance
     *
     * @param string $distance
     */
    public function set_distance($distance) {
        $this->distance = $distance;
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Sets name
     *
     * @param string $n
     */
    public function set_name($n) {
        $this->name = $n;
    }

    /**
     * Returns value
     *
     * @param int $i index
     * @return float value at index i
     */
    public function get_value($i) {
        return $this->value[$i];
    }

    /**
     * Returns values
     *
     * @return array
     */
    public function get_values() {
        return $this->value;
    }

    /**
     * Adds or replaces value at index. Checks for min/max value conformity
     *
     * @param number $i
     * @param number $v
     * @throws Exception
     */
    public function set_value($i, $v) {
        if ($v < $this->get_min_value() || $v > $this->get_max_value()) {
            throw new Exception("value (" . $v . ") is out of min/max value area! (" . $this->get_min_value() . " to " .
                    $this->get_max_value() . ") [" . $this->get_name() . "]");
        }
        $this->value[$i] = $v;
    }

    /**
     * Sets values
     *
     * @param array $v iterates and copies each value
     * @throws Exception
     */
    public function set_values(&$v) {
        for ($i = 0; $i < count($v); $i++) {
            $this->set_value($i, $v[$i]);
        }
    }

    /**
     * Returns max value
     *
     * @return float
     */
    public function get_max_value() {
        return $this->maxvalue;
    }

    /**
     * Sets max value
     *
     * @param number $number
     * @throws Exception
     */
    public function set_max_value($number) {
        if ($this->get_min_value() > $number) {
            throw new Exception("maxVal cannot be lower than minVal (" . $this->get_min_value() . ")");
        }
        $this->maxvalue = $number;
    }

    /**
     * Returns min value
     *
     * @return float
     */
    public function get_min_value() {
        return $this->minvalue;
    }

    /**
     * Sets min value
     *
     * @param float $number
     */
    public function set_min_value($number) {
        $this->minvalue = $number;
    }

    /**
     * Returns whether criterion is homogeneous or not
     *
     * @return bool
     */
    public function is_homogeneous() {
        return $this->homogeneous;
    }

    /**
     * Sets whether criterion is homogeneous
     *
     * @param bool $bool
     */
    public function set_homogeneous($bool) {
        $this->homogeneous = $bool;
    }

    /**
     * Returns weight
     *
     * @return float
     * @throws Exception if Criterion (this) not valid
     */
    public function get_weight() {
        return mod_groupformation_criterion_weight::get_weight($this->name);
    }

}