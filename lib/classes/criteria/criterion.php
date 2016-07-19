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
    protected $maxValue = 1.0;

    /** @var float Min valid value of an criterion */
    protected $minValue = 0.0;

    /** @var bool  flag to mark Criterion as homogeneous or as not homogeneous (heterogeneous) */
    protected $isHomogeneous = false;

    /**
     * @return String
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param String $n
     */
    public function setName($n) {
        $this->name = $n;
    }

    /**
     * @param $i int index
     * @return float value at index i
     */
    public function getValue($i) {
        return $this->value[$i];
    }


    /**
     * @return float[]
     */
    public function getValues() {
        return $this->value;
    }


    /**
     * Adds or replaces value at index. Checks for min/max value conformity
     * @param int $i index
     * @param float $v value
     */
    public function setValue($i, $v) {
        if ($v < $this->getMinValue() || $v > $this->getMaxValue()) {
            throw new Exception("value (" . $v . ") is out of min/max value area! (" . $this->getMinValue() . " to " .
                $this->getMaxValue() . ") [" . $this->getName() . "]");
        }
        $this->value[$i] = $v;
    }


    /**
     * @param float[] $v iterates and copies each value
     */
    public function setValues(&$v) {
        for ($i = 0; $i < count($v); $i++) {
            $this->setValue($i, $v[$i]);
        }
    }


    /**
     * @return float
     */
    public function getMaxValue() {
        return $this->maxValue;
    }


    /**
     * @param float $number
     */
    public function setMaxValue($number) {
        if ($this->getMinValue() > $number) {
            throw new Exception("maxVal cannot be lower than minVal (" . $this->getMinValue() . ")");
        }
        $this->maxValue = $number;
    }


    /**
     * @return float
     */
    function getMinValue() {
        return $this->minValue;
    }


    /**
     * @param float $number
     */
    function setMinValue($number) {
        $this->minValue = $number;
    }


    /**
     * @return bool
     */
    public function getIsHomogeneous() {
        return $this->isHomogeneous;
    }

    /**
     * @param bool $bool
     */
    public function setIsHomogeneous($bool) {
        $this->isHomogeneous = $bool;
    }


    /**
     * @return float
     * @throws Exception if Criterion (this) not valid
     */
    public function getWeight() {
        return lib_groupal_criterion_weight::getWeight($this->name);
    }

}