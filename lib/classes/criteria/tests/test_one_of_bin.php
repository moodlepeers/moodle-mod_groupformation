<?php
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/one_of_bin_criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/many_of_bin_criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/specific_criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");

/**
 * Class test_one_of_bin - test's for the one-of-bin criterion implementation
 */
class test_one_of_bin {

    /**
     * use created participants and change these with a criterion
     *
     * @param $participants
     * @return array
     */
    public function create_participants($participants) {
        $participantslist = array();
        try {
            for ($i = 0; $i < count($participants) - 1; $i++) {
                $p = $participants[$i];
                $criteria = $p->get_criteria();
                $list = array();

                for ($j = 0; $j < count($criteria) - 1; $j++) {

                    $c = $criteria[$j];
                    $label = $c->get_name();
                    $value = $c->get_values();
                    $minval = $c->get_min_value();
                    $maxval = $c->get_max_value();
                    $homogen = $c->is_homogeneous();
                    $weight = $c->get_weight();

                    // set random criterion to participant
                    $rand = rand(0, 1);
                    if ($rand) {
                        $list[] = new mod_groupformation_one_of_bin_criterion("one_of_bin", $value, $minval, $maxval, $homogen,
                                $weight);
                        // $list[] = new mod_groupformation_many_of_bin_criterion("many_of_bin", array(1, 0.4, 1), $minval, $maxval, $homogen, $weight);
                    } else {
                        // $list[] = new mod_groupformation_both_bin_types_bins_covered_criterion("both_bin_types", $value, $minval, $maxval, $homogen, $weight);
                        // $list[] = new mod_groupformation_both_bin_types_bins_covered_criterion("both_bin_types", $value, $minval, $maxval, $homogen, $weight);
                        $list[] = new mod_groupformation_many_of_bin_criterion("many_of_bin", array(0.4, 1, 1), $minval, $maxval,
                                $homogen, $weight);
                    }

                }
                $participantslist[] = new mod_groupformation_participant($list);
            }

            return $participantslist;
        } catch (Exception $e) {
            // creating participants failed
        }
    }

    public function test_result() {

    }

    /**
     * create manually a participant
     *
     * @return array
     */
    public function create_manually_participant() {
        try {
            $criteria_one_of_bin = new mod_groupformation_one_of_bin_criterion("one_of_bin", array(0.4), 0, 1, false, 1);
            $criteria_many_of_bin = new mod_groupformation_one_of_bin_criterion("many_of_bin", array(0.4), 0, 1, false, 1);

            $list = array();
            $list[] = new mod_groupformation_participant($criteria_many_of_bin);
            $list[] = new mod_groupformation_participant($criteria_one_of_bin);
            $list[] = new mod_groupformation_participant($criteria_one_of_bin);
            $list[] = new mod_groupformation_participant($criteria_one_of_bin);

            return $list;
        } catch (Exception $e) {
        }
    }

    /**
     * log criteria name
     *
     * @param $label
     * @param $criteria
     */
    public function log_criteria($label, $criteria) {
        $this->debug_to_console($label, $criteria);
    }

    /**
     * log performance index
     *
     * @param $label
     * @param $pi
     */
    public function log_performance_index($label, $pi) {
        $this->debug_to_console($label, $pi);

    }

    /**
     * logging in console
     *
     * @param $label
     * @param $data
     */
    function debug_to_console($label, $data) {
        $output = $data;
        //print_r("<script>console.log( 'Debug Objects: " . $label . $output . "' );</script>");
    }

}