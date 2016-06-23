<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 23/03/15
 * Time: 12:02
 */
<<<<<<< HEAD
include(__DIR__."/../Criteria/criterion_weight.php");
=======
include(__DIR__ . "/../criteria/criterion_weight.php");
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc


class CriterionWeightTest extends PHPUnit_Framework_TestCase {

    /**
     * Jeder Test startet mit diesen setUp()-Daten. Nach jedem Test sieht lib_groupal_criterion_weight wieder genau so aus
     * @before
     */
    public function setUp() {
        lib_groupal_criterion_weight::init(new lib_groupal_hash_map);

        lib_groupal_criterion_weight::addCriterionWeight("test1", 10);
        lib_groupal_criterion_weight::addCriterionWeight("test2", 20);
        lib_groupal_criterion_weight::addCriterionWeight("test3", 30);

        lib_groupal_criterion_weight::addCriterionWeight("test7", NULL);
        lib_groupal_criterion_weight::addCriterionWeight("test8", NULL);
    }

    public function testAddCriterionWeights() {
        // adding regular content
        $this->assertTrue(lib_groupal_criterion_weight::addCriterionWeight("test4", 4));
        $this->assertEquals(4, lib_groupal_criterion_weight::getWeight("test4"));

        lib_groupal_criterion_weight::addCriterionWeight("test5", 5);
        $this->assertEquals(30, lib_groupal_criterion_weight::getWeight("test3"));
    }

    /**
     *
     */
    public function testAddCriterionWeightsException() {
        $this->assertFalse(lib_groupal_criterion_weight::addCriterionWeight("test3", 55));
    }

    public function testGetWeight() {
        $this->assertEquals(30, lib_groupal_criterion_weight::getWeight("test3"));
    }


}
