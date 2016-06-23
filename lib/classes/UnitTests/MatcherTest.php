<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 07/04/15
 * Time: 09:01
 */

<<<<<<< HEAD
require_once(__DIR__ . "/../Matcher/group_centric_matcher.php");
require_once(__DIR__ . "/../Matcher/participant_centric_matcher.php");
require_once(__DIR__ . "/../participant.php");
require_once(__DIR__ . "/../cohort.php");
require_once(__DIR__."/../Criteria/criterion_weight.php");
require_once(__DIR__ . "/../Criteria/specific_criterion.php");
=======
require_once(__DIR__ . "/../matchers/group_centric_matcher.php");
require_once(__DIR__ . "/../matchers/participant_centric_matcher.php");
require_once(__DIR__ . "/../participant.php");
require_once(__DIR__ . "/../cohort.php");
require_once(__DIR__ . "/../criteria/criterion_weight.php");
require_once(__DIR__ . "/../criteria/specific_criterion.php");
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc

class MatcherTest extends PHPUnit_Framework_TestCase {

    public $g1, $g2, $ggg, $ggg2;
    protected $p1, $p2, $p3, $p4;
    protected $c_vorwissen, $c_note, $c_persoenlichkeit, $c_motivation, $c_lernstil, $c_teamorientierung;
    protected $participants;

    /**
     * @before
     */
    public function testSettingUpTestData() {
        // init lib_groupal_criterion_weight
        // lib_groupal_criterion_weight::init(new HashMap);

        // Criterions
        $this->c_vorwissen = new lib_groupal_specific_criterion("vorwissen", array(0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $this->c_note = new lib_groupal_specific_criterion("note", array(0.4), 0, 1, true, 1);
        $this->c_persoenlichkeit = new lib_groupal_specific_criterion("persoenlichkeit", array(0.4, 0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $this->c_motivation = new lib_groupal_specific_criterion("motivation", array(0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $this->c_lernstil = new lib_groupal_specific_criterion("lernstil", array(0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);
        $this->c_teamorientierung = new lib_groupal_specific_criterion("teamorientierung", array(0.4, 0.4, 0.4, 0.4, 0.4, 0.4), 0, 1, true, 1);


        // Groups
        $this->g1 = new lib_groupal_group();
        $this->g2 = new lib_groupal_group();

        $this->ggg = new lib_groupal_cohort(10, array($this->g1, $this->g2));


    }

    public function testGroupCentricMatcher2() {
        lib_groupal_group::setGroupMembersMaxSize(3);
        // init
        $gcm = new lib_groupal_group_centric_matcher();

        for ($i = 0; $i < 30; $i++) {
            $users[] = new lib_groupal_participant(array($this->c_vorwissen, $this->c_motivation,
                $this->c_note, $this->c_persoenlichkeit, $this->c_lernstil, $this->c_teamorientierung), $i);
        }
        // matching
        $gcm->matchToGroups($users, $this->ggg->groups);
        // testing
        foreach($this->ggg->groups as $g) {
            echo $g->toString()."\n";
        }

//        $this->assertEquals(array($this->participants), $this->g2->get_participants());
    }

    /**
     * Test Participant Centric matchers
     */
    public function testParticipantMatcher() {
        lib_groupal_group::setGroupMembersMaxSize(3);
        // init
        $gcm = new lib_groupal_participant_centric_matcher();

        for ($i = 0; $i < 30; $i++) {
            $users[] = new lib_groupal_participant(array($this->c_vorwissen, $this->c_motivation,
                $this->c_note, $this->c_persoenlichkeit, $this->c_lernstil, $this->c_teamorientierung), $i);
        }
        // matching
        $gcm->matchToGroups($users, $this->ggg->groups);
        // testing
        foreach($this->ggg->groups as $g) {
            echo $g->toString()."\n";
        }
    }

}
