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
 * This abstract class contains method signatures and field variables for organizing
 * values based on users answers
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
abstract class lib_groupal_criterion {


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

    /**
     * @return String
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @param String $n
     */
    public function set_name($n) {
        $this->name = $n;
    }

    /**
     * @param $i int index
     * @return float value at index i
     */
    public function get_value($i) {
        return $this->value[$i];
    }


    /**
     * @return float[]
     */
    public function get_values() {
        return $this->value;
    }


    /**
     * Adds or replaces value at index. Checks for min/max value conformity
     *
     * @param $i
     * @param $v
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
     * @param float[] $v iterates and copies each value
     */
    public function set_values(&$v) {
        for ($i = 0; $i < count($v); $i++) {
            $this->set_value($i, $v[$i]);
        }
    }


    /**
     * @return float
     */
    public function get_max_value() {
        return $this->maxvalue;
    }


    /**
     * @param $number
     * @throws Exception
     */
    public function set_max_value($number) {
        if ($this->get_min_value() > $number) {
            throw new Exception("maxVal cannot be lower than minVal (" . $this->get_min_value() . ")");
        }
        $this->maxvalue = $number;
    }


    /**
     * @return float
     */
    function get_min_value() {
        return $this->minvalue;
    }


    /**
     * @param float $number
     */
    function set_min_value($number) {
        $this->minvalue = $number;
    }


    /**
     * @return bool
     */
    public function is_homogeneous() {
        return $this->homogeneous;
    }

    /**
     * @param bool $bool
     */
    public function set_homogeneous($bool) {
        $this->homogeneous = $bool;
    }


    /**
     * @return float
     * @throws Exception if Criterion (this) not valid
     */
    public function get_weight() {
        return lib_groupal_criterion_weight::get_weight($this->name);
    }

}