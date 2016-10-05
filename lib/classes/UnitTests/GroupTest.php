<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 24/03/15
 * Time: 12:15
 */

require_once(__DIR__ . "/../group.php");
require_once(__DIR__ . "/../participant.php");
<<<<<<< HEAD
require_once(__DIR__."/../Criteria/criterion_weight.php");
require_once(__DIR__ . "/../Criteria/specific_criterion.php");
=======
require_once(__DIR__ . "/../criteria/criterion_weight.php");
require_once(__DIR__ . "/../criteria/specific_criterion.php");
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc

class GroupTest extends PHPUnit_Framework_TestCase {

    protected $g1, $g2, $g3;
    protected $p1, $p2, $p3;
    protected $c1, $c2, $c3;

    /**
     * @before
     */
    public function testSettingUpEnvironment() {
        lib_groupal_group::init();
        lib_groupal_group::setGroupMembersMaxSize(3);
        $this->g1 = new lib_groupal_group();
        $this->g2 = new lib_groupal_group();
        $this->g3 = new lib_groupal_group();

        lib_groupal_criterion_weight::init(new lib_groupal_hash_map());

        $this->c1 = new lib_groupal_specific_criterion("c1", array(3, 4.5), 2.2, 5.5, true, 3);
        $this->c2 = new lib_groupal_specific_criterion("c2", array(3, 4.5), 2.2, 5.5, true, 3);
        $this->c3 = new lib_groupal_specific_criterion("c3", array(3, 4.5), 2.2, 5.5, true, 3);

        $this->p1 = new lib_groupal_participant($this->c1, 1);
        $this->p2 = new lib_groupal_participant($this->c1, 2);
        $this->p3 = new lib_groupal_participant($this->c1, 3);

        // nur für calculateGroupPerformance
        // ACHTUNG: eine Gruppe enthält nur Participants mit selben Kriterien
        $this->g3->addParticipant($this->p1);
        $this->g3->addParticipant($this->p2);
        $this->g3->addParticipant($this->p3);

    }

    public function testSomeStd() {
        // ID-Counter
        $this->assertEquals(1, $this->g1->getID());
        $this->assertEquals(2, $this->g2->getID());

        // set and get participants to group
//        $this->g1->set_participants(array($this->p1, $this->p2));
//        $this->g2->set_participants(array($this->p3));

        $this->assertEquals(0, $this->g1->get_participants()->size());
        $this->assertEquals(0, $this->g2->get_participants()->size());

        // Group Performance Index
        $this->g1->setGroupPerformanceIndex(5);
        $this->g2->setGroupPerformanceIndex(9);
        $this->assertEquals(5, $this->g1->getGroupPerformanceIndex());
        $this->assertEquals(9, $this->g2->getGroupPerformanceIndex());

        // Memberss Max Size
        lib_groupal_group::setGroupMembersMaxSize(9);
        $this->assertEquals(9, lib_groupal_group::getGroupMembersMaxSize());

        // Time before reset
        lib_groupal_group::setTimeBeforeRefreshGroup(4);
        $this->assertEquals(4, lib_groupal_group::getTimeBeforeRefreshGroup());

        // Clear Group
        $this->g1->clear();
        $this->assertEquals(0, $this->g1->get_participants()->size());
        $this->assertEquals(0, $this->g1->getGroupPerformanceIndex());

        // Remove participants
        $this->assertEquals(0, $this->g2->get_participants()->size());
        $this->assertTrue($this->g3->removeParticipant($this->p3));
        $this->assertEquals(2, $this->g3->get_participants()->size());

        // Add participants
        $this->g2->addParticipant($this->p3);
        $this->assertEquals(1, $this->g2->get_participants()->size());

    }

    /**
     * @expectedException Exception
     */
    public function testMaxSizeError() {
        $this->g1->setGroupMembersMaxSize(2);

        $this->g1->addParticipant($this->p1);
        $this->g1->addParticipant($this->p2);
        $this->g1->addParticipant($this->p3);
    }

    public function testCalculateGroupPerformanceIndex() {
        $this->assertEquals(null, $this->g1->getGroupPerformanceIndex());
        $this->assertEquals(1, $this->g3->getGroupPerformanceIndex());
        $this->g1->removeParticipant($this->p2);
        $this->assertEquals(1, $this->g3->getGroupPerformanceIndex());
    }

    public function testEmptyGroupOnCalculateGroupPerformanceIndex() {
        $g5 = new lib_groupal_group();

    }

}
