<?php

require_once($CFG->dirroot . "/mod/groupformation/classes/grouping/grouping.php");
require_once $CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/groupal_evaluator.php";
require_once $CFG->dirroot . "/mod/groupformation/lib/classes/group.php";

class mod_groupformation_fake_group
{
    private $evaluator = null;

    /**
     * create test cases
     */
    public function create()
    {
        mod_groupformation_group::set_group_members_max_size(100);
        $this->evaluator = new mod_groupformation_evaluator();
//        $this->create_equals_group();
        $this->create_differently_group();
        die();
    }

    /**
     * create participants with one of bin criterion based on $values
     * @param $values
     * @return mod_groupformation_participant
     */
    private function create_participant($values)
    {
        try {
            $participant = new mod_groupformation_participant();
            $c = new mod_groupformation_one_of_bin_criterion(
                "one_of_bin", array(), 0, 1, true, 0);
            $c->set_values($values);
            $participant->add_criterion($c);
            return $participant;
        } catch (Exception $e) {
        }
    }

    /**
     * create a group with equals group member
     */
    private function create_equals_group()
    {
        try {
            $values = [1, 0, 0, 0];
            $participants = array();

            $group = new mod_groupformation_group();
            $groupsize = 3;

            for ($i = 0; $i < $groupsize; $i++) {
                $p = $this->create_participant($values);
                array_push($participants, $p);
                $group->add_participant($p, true);
            }


            $grouping = new mod_groupformation_grouping(1);

            // get gpi of group
            $gpi = $this->evaluator->evaluate_gpi($group);

            // call build_groupal_cohort and get result of participants (performance index...)
            $result = $grouping->build_groupal_cohort($participants, $groupsize);


            print_r("Equals Group");
            echo('<br/>');
            for ($i = 0; $i < count($result->groups); $i++) {
                print_r("Group " . $i . ":");
                echo('<br/>');
                $p = $result->groups[$i]->get_participants();
                for ($j = 0; $j < count($p); $j++) {
                    $c = $p[$j]->get_criteria();
                    print_r(json_encode($c[0]->get_values()));
                }
                echo('<br/>');
            }

            print_r("test evaluate_group() - gpi result:" . $gpi);
            echo('<br/>');
            print_r("testing the method build_groupal_cohort() - Performance Index result:" . $result->results->performanceindex);
            echo('<br/>');

        } catch (Exception $e) {
        }
    }

    /**
     * create group with different group members with different values
     */
    private function create_differently_group()
    {
        try {
            $participants = array();

            $group = new mod_groupformation_group();

            $groupsize = 40;

            for ($i = 0; $i < $groupsize; $i++) {
                $values = [1, 0, 0, 0];
                if ($i == 0) {
                    $values = [0, 1, 0, 0];
                }

                $p = $this->create_participant($values);
                array_push($participants, $p);
                $group->add_participant($p, true);
            }

            // create new grouping object
            $grouping = new mod_groupformation_grouping(1);

            // get gpi of group
            $gpi = $this->evaluator->evaluate_gpi($group);

            // call build_groupal_cohort method with participants and groupsize
            $result = $grouping->build_groupal_cohort($participants, $groupsize);


            echo('<br/>');
            print_r("different participants values");
            echo('<br/>');

            // print group
            for ($i = 0; $i < count($result->groups); $i++) {
                print_r("Group " . $i . ":");
                echo('<br/>');
                $p = $result->groups[$i]->get_participants();
                for ($j = 0; $j < count($p); $j++) {
                    $c = $p[$j]->get_criteria();
                    print_r(json_encode($c[0]->get_values()));
                }
                echo('<br/>');
            }

            // print results of gpi and cohort performance index
            print_r("test evaluate_group() - gpi result:" . $gpi);
            echo('<br/>');
            print_r("testing the method build_groupal_cohort() - Performance Index: " . $result->results->performanceindex);
            echo('<br/>');
        } catch (Exception $e) {
        }
    }
}