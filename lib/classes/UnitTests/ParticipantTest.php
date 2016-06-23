<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 24/03/15
 * Time: 12:21
 */
require_once(__DIR__ . "/../participant.php");
<<<<<<< HEAD
require_once(__DIR__ . "/../Criteria/specific_criterion.php");

require_once(__DIR__."/../Criteria/criterion_weight.php");
=======
require_once(__DIR__ . "/../criteria/specific_criterion.php");

require_once(__DIR__ . "/../criteria/criterion_weight.php");
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc

class ParticipantTest extends PHPUnit_Framework_TestCase {

    protected $p1, $p2, $p3;
    protected $c1, $c2, $c3;

    /**
     * @before
     */
    public function testSettingUpEnvironment() {
        lib_groupal_criterion_weight::init(new lib_groupal_hash_map());
        // Participant::initIdCounter();

        $this->c1 = new lib_groupal_specific_criterion("c1", array(3, 4.5), 2.2, 5.5, true, 3);
        $this->c2 = new lib_groupal_specific_criterion("c2", array(3, 4.5), 2.2, 5.5, true, 3);
        $this->c3 = new lib_groupal_specific_criterion("c3", array(3, 4.5), 2.2, 5.5, true, 3);

        $this->p1 = new lib_groupal_participant($this->c1, 1);
        $this->p2 = new lib_groupal_participant($this->c2, 2);
        $this->p3 = new lib_groupal_participant($this->c3, 3);
    }

    /**
     * test ID Counter for individual id's for each Participant
     */
    public function testIdCounter() {
        $this->assertEquals(1, $this->p1->getID());
    }

    public function testSomeMore() {
        /*
         * Test add and get Criteria
         */
        $this->p1->addCriterion($this->c2);
        $this->assertEquals(2, $this->p1->getCriteria()->size());

        $this->assertEquals("c1", $this->p1->getCriteria()->first()->getName());

        /**
         * test clone participant
         */
    }

    // public $pc;
    public function testCloningParticipant() {
        $this->pc = clone $this->p3;

        $this->assertNotSame($this->p3, $this->pc);
        $this->assertEquals($this->p3->getCriteria(), $this->pc->getCriteria());
        $this->assertEquals($this->p3->getID(), $this->pc->getID());

    }

}
