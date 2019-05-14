<?php


class mod_groupformation_fake_participants
{
    public function create()
    {
        $this->create_criterion();
    }

    private function call_normalized_function($participants)
    {
        // All Normalized paar performance indices of a Group
        $npis = array(); // Generic List: float.

        $evaluator = new mod_groupformation_evaluator();
        try {
            // Calculate npi for every pair of entries in the  group g (but not double and not compare with oneself!)
            for ($i = 0; $i < count($participants) - 1; $i++) {
                for ($j = $i + 1; $j < count($participants); $j++) {
                    // Calulate normlizedPaarperformance index.
                    $npi = $evaluator->calc_normalized_pair_performance($participants[$i], $participants[$j]);
                    $npis[] = $npi;
                }
            }
            return $npis;

        } catch (Exception $e) {
            die("Exception: " . $e);
        }
    }

    private function create_criterion()
    {
        try {
            $this->one_of_bin_test_1();
            $this->one_of_bin_test_2();
            $this->one_of_bin_test_3();
            $this->one_of_bin_test_4();
            $this->one_of_bin_test_5();
            $this->one_of_bin_test_6();
            $this->one_of_bin_test_7();
        } catch (Exception $e) {
            die("Exception: " . $e);
        }
    }


    private function create_one_of_bin($valueArray)
    {
        try {
            $participants = array();

            for ($i = 0; count($participants) < count($valueArray); $i++) {
                $participant = new mod_groupformation_participant();
                array_push($participants, $participant);
            }


            for ($i = 0; $i < count($participants); $i++) {
                $c = new mod_groupformation_one_of_bin_criterion(
                    "one_of_bin", array(), 0, 1, true, 0.3);
                $c->set_values($valueArray[$i]);
                $participants[$i]->add_criterion($c);
            }

            return $participants;

        } catch (Exception $e) {
            die("Exception: " . $e);
        }
    }

    private function print_result($name, $values, $expected, $participants)
    {
        print_r("#### " . $name . " ####");
        echo '<br/>';
        for ($i = 0; $i < count($values); $i++) {
            print_r(json_encode($values[$i]));
            echo '<br/>';
        }
        print_r("expected result: " . $expected);
        echo '<br/>';
        print_r("result npi: " . json_encode($this->call_normalized_function($participants)));
        echo '<br/>';
    }

    private function one_of_bin_test_1()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 1, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 1, $participants);
    }

    private function one_of_bin_test_2()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [1, 0, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }

    private function one_of_bin_test_3()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [1, 0, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }

    private function one_of_bin_test_4()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 0, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }

    private function one_of_bin_test_5()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 0, 0, 0]);
        array_push($valueArray, [1, 0, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }

    private function one_of_bin_test_6()
    {
        $valueArray = array();
        array_push($valueArray, [0, 0, 1, 0]);
        array_push($valueArray, [0, 0, 1, 0]);
        array_push($valueArray, [0, 0, 1, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }


    private function one_of_bin_test_7()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 1, 0, 0]);
        array_push($valueArray, [0, 0, 1, 0]);
        array_push($valueArray, [0, 0, 0, 1]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 1, $participants);
        die();
    }

    private function create_many_of_bins()
    {
        //#### MANY OF BIN ####

        $valueArray = array();
        array_push($valueArray, [1, 0, 0]);
        array_push($valueArray, [1, 0, 0]);
        array_push($valueArray, [0, 0, 1]);

        try {
            for ($i = 0; $i < count($this->get_participants()); $i++) {
                $c = new mod_groupformation_many_of_bin_criterion(
                    "many_of_bin", array(), 0, 1, 0.3);
                $c->set_values($valueArray[$i]);
                $this->participants[$i]->add_criterion($c);
            }

        } catch (Exception $e) {
            die("Exception: " . $e);
        }

//            $valueArray1 = array(1, 1, 1, 0);
//            $valueArray2 = array(0, 0, 0, 1);
//            $valueArray3 = array(0, 0, 0, 0);
//
//            if ($i == 0) {
//                $c = new mod_groupformation_many_of_bin_criterion(
//                    "many_of_bin", array(), 0, 1, false, 0.3);
//                $c->set_values($valueArray1);
//                return $c;
//            } else if ($i == 1) {
//                $c = new mod_groupformation_many_of_bin_criterion(
//                    "many_of_bin", array(), 0, 1, false, 0.3);
//                $c->set_values($valueArray2);
//                return $c;
//            } else if ($i == 2) {
//                $c = new mod_groupformation_many_of_bin_criterion(
//                    "many_of_bin", array(), 0, 1, false, 0.3);
//                $c->set_values($valueArray3);
//                return $c;
//            }
//        } catch (Exception $e) {
//            die("Exception: " . $e);
//        }
    }

    private function create_group_of_participants()
    {
        try {
            $group = $this->get_participants();
            // delete last item of array
            array_pop($group);

            $valueArray1 = array(1, 1, 1, 0);
            $valueArray2 = array(0, 0, 0, 1);
            $valueArray3 = array(0, 0, 0, 0);


            return $group;
        } catch (Exception $e) {

        }
    }


    private function create_bins_covered_distance()
    {
        // both_bin_types_bins_covered_distance
        try {
            $c = new mod_groupformation_both_bin_types_bins_covered_criterion(
                "both_bin_types_bins_covered_distance", array(), 0, 1, false, 0.3);
            $c->set_values($valueArray1);
            return $c;
        } catch (Exception $e) {
        }
    }
}