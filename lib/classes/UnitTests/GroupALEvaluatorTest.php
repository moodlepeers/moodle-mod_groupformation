<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 24/03/15
 * Time: 12:09
 */
<<<<<<< HEAD
require_once(__DIR__ . "/../Evaluator/groupal_evaluator.php");
require_once(__DIR__."/../Criteria/criterion_weight.php");
require_once(__DIR__ . "/../Criteria/specific_criterion.php");
=======
require_once(__DIR__ . "/../evaluators/groupal_evaluator.php");
require_once(__DIR__ . "/../criteria/criterion_weight.php");
require_once(__DIR__ . "/../criteria/specific_criterion.php");
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc
require_once(__DIR__ . "/../participant.php");
require_once(__DIR__ . "/../group.php");
require_once(__DIR__ . "/../cohort.php");

class GroupALEvaluatorTest extends PHPUnit_Framework_TestCase {

    public $evaluator; // GroupALEvaluator
    public $c1, $c2; // specific criterions
    public $p1, $p2, $p3, $p4; // participants
    public $g1, $g2; // group
    public $cohort; // cohort

    /**
     * @before
     */
    public function testSettingUpEnvironment() {
        lib_groupal_criterion_weight::init(new lib_groupal_hash_map());
        //$this->evaluator = 'GroupALEvaluator';
        $this->c1 = new lib_groupal_specific_criterion("c12", array(3, 4.5), 2.2, 5.5, true, 3);
        $this->c2 = new lib_groupal_specific_criterion("c23", array(3, 4.5), 2.2, 5.5, true, 3);

        $this->p1 = new lib_groupal_participant($this->c1, 1);
        $this->p2 = new lib_groupal_participant($this->c1, 2);
        $this->p3 = new lib_groupal_participant($this->c1, 3);
        $this->p4 = new lib_groupal_participant($this->c1, 4);

        lib_groupal_group::setGroupMembersMaxSize(5);

        $this->g1 = new lib_groupal_group();
        $this->g1->addParticipant($this->p1);
        $this->g1->addParticipant($this->p2);

        $this->g2 = new lib_groupal_group();
        $this->g2->addParticipant($this->p3);
        $this->g2->addParticipant($this->p4);

        // Cohort with one group
        $coho = array();
        $coho[] = $this->g2;
        $this->cohort = new lib_groupal_cohort(5, $coho);
    }

    public function testNormDistance() {
        //$this->assertEquals(4, $this->evaluator->normalizeDistance($this->c1, $this->c2));
        $this->assertEquals(1, 1);
    }

    public function testSomthing() {
        $a = array(5, 3, 9);
        if (in_array(9, $a)) {
            $i = array_search(9, $a);
            array_splice($a, $i);
        }
        $this->assertEquals(array(5, 3), $a);
    }


    public function testCalcNormalizedPairPerformance() {
    	$evaluator = new lib_groupal_evaluator();
        $this->assertEquals(1.0, $evaluator->calcNormalizedPairPerformance($this->p1, $this->p2));
    }

    public function testEvaluateGroupPerformanceIndex() {
        // GroupALEvaluator::$actualGroup = $this->g1;
        $this->assertEquals(1, $this->g1->getGroupPerformanceIndex());
        $this->g1->calculateGroupPerformanceIndex();
        $this->assertEquals(1, $this->g1->getGroupPerformanceIndex()); // nach der Kalkulierung
        $this->assertEquals(2, lib_groupal_evaluator::$actualGroup->get_participants()->size());
    }

    public function testGetPerformanceIndex() {
        // return empty Statistics Object
        $pi = lib_groupal_evaluator::getPerformanceIndex(array())->performanceIndex;
        $this->assertEquals(0.0, $pi);
    }


    public function testEvaluateCohortPerformanceIndex() {
        //GroupALEvaluator::$actualCohort = $this->cohort;
        //$this->assertEquals(79.4, GroupALEvaluator::evaluateCohortPerformanceIndex());
        $this->cohort->calculateCohortPerformanceIndex();
        $this->assertEquals(79.4, $this->cohort->cohortPerformanceIndex);
    }
}
