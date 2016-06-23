<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 18/05/15
 * Time: 19:23
 */
class D
{
    public $g1, $g2, $ggg;
    public $p1, $p2, $p3, $p4;
    public $c1, $c2;
    public $participants;

    /**
     * @before
     */
    public function testSettingUpTestData()
    {
        // init lib_groupal_criterion_weight
        lib_groupal_criterion_weight::init(new lib_groupal_hash_map);

        // Criterion
        $c1 = new lib_groupal_specific_criterion("oneCrit", array(1, 2, 3), 1, 3, true, 3);

        // participants
        $this->p1 = new lib_groupal_participant($c1, 1);
        $this->p2 = new lib_groupal_participant($c1, 2);
        $this->p3 = new lib_groupal_participant($c1, 3);
        $this->p4 = new lib_groupal_participant($c1, 4);
        $this->p5 = new lib_groupal_participant($c1, 5);
        $this->p6 = new lib_groupal_participant($c1, 6);
        $this->p7 = new lib_groupal_participant($c1, 7);
        $this->p8 = new lib_groupal_participant($c1, 8);
        $this->p9 = new lib_groupal_participant($c1, 9);
        $this->p10 = new lib_groupal_participant($c1, 10);
        $this->p11 = new lib_groupal_participant($c1, 11);
        $this->p12 = new lib_groupal_participant($c1, 12);

        // adding participants to participants-list
        $this->participants = array($this->p1, $this->p2, $this->p3, $this->p4);

        // Groups
        $this->g1 = new lib_groupal_group();
        $this->g2 = new lib_groupal_group();

        $this->ggg = array($this->g1, $this->g2);

    }
}

$die = new D();

$die->testSettingUpTestData();
$gcm = new lib_groupal_group_centric_matcher();
$gcm->matchToGroups($die->participants, $die->ggg);

echo var_dump($die->g1->get_participants());