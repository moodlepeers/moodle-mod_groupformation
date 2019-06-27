<?php


class mod_groupformation_fake_participants
{
    /**
     * calling the different test cases
     */
    public function create()
    {
        try {
            $this->one_of_bin_test_1();
            $this->one_of_bin_test_2();
            $this->one_of_bin_test_3();
            $this->one_of_bin_test_4();
            $this->one_of_bin_test_5();
            $this->one_of_bin_test_6();
            die();
        } catch (Exception $e) {
            die("Exception: " . $e);
        }
    }

    /**
     * normalized participants
     * @param $participants
     * @return array
     */
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

    /**
     *  create participants and add the one of bin criterion
     * @param $valueArray
     * @return array
     */
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

    // print the result of the test cases
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
        echo '<br/>';
    }


    //different test cases

    /**
     * test case 1 compare two different participants
     */
    private function one_of_bin_test_1()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 1, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 1, $participants);
    }

    /**
     * test case 2 compare two different participants
     */
    private function one_of_bin_test_2()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [1, 0, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }


    /**
     * test case 3 compare three different participants
     */
    private function one_of_bin_test_3()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 0, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }

    /**
     * test case 4 compare three different participants
     */
    private function one_of_bin_test_4()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 0, 0, 0]);
        array_push($valueArray, [1, 0, 0, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }

    /**
     * test case 5 compare three different participants
     */
    private function one_of_bin_test_5()
    {
        $valueArray = array();
        array_push($valueArray, [0, 0, 1, 0]);
        array_push($valueArray, [0, 0, 1, 0]);
        array_push($valueArray, [0, 0, 1, 0]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 0, $participants);
    }


    /**
     * test case 6 compare four different participants
     */
    private function one_of_bin_test_6()
    {
        $valueArray = array();
        array_push($valueArray, [1, 0, 0, 0]);
        array_push($valueArray, [0, 1, 0, 0]);
        array_push($valueArray, [0, 0, 1, 0]);
        array_push($valueArray, [0, 0, 0, 1]);

        $participants = $this->create_one_of_bin($valueArray);
        $this->print_result(__FUNCTION__, $valueArray, 1, $participants);
    }
}