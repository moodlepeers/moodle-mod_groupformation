<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/lib/groupal/classes/criteria/topic_criterion.php');

class mod_groupformation_criterion_calculator {
    private $store;
    private $user_manager;
    private $data;
    private $groupformationid;

    private $BIG5 = null;

    private $FAM = null;

    private $LEARN = null;

    /**
     *
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->user_manager = new mod_groupformation_user_manager ($groupformationid);
        $this->data = new mod_groupformation_data();
        $this->BIG5 = $this->data->get_criterion_specification('big5');
        $this->LEARN = $this->data->get_criterion_specification('learning');
        $this->FAM = $this->data->get_criterion_specification('fam');
    }

    /**
     * Inverts given answer by considering maximum
     *
     * @param number $questionid
     * @param string $category
     * @param number $answer
     * @return number
     */
    private function invert_answer($questionid, $category, $answer) {
        $max = $this->store->get_max_option_of_catalog_question($questionid, $category);

        // Because internally we start with 0 instead of 1.
        return $max + 1 - $answer;
    }

    /**
     * Determines values in category 'general' chosen by user
     *
     * @param number $userid
     * @return string
     */
    public function get_general_values($userid) {
        $value = $this->user_manager->get_single_answer($userid, 'general', 1);

        // array(x,y) with x = ENGLISH and y = GERMAN.
        if ($value == 1) {
            $values = array(
                1.0, 0.0);
        } else if ($value == 2) {
            $values = array(
                0.0, 1.0);
        } else if ($value == 3) {
            $values = array(
                1.0, 0.5);
        } else if ($value == 4) {
            $values = array(
                0.5, 1.0);
        }

        return $values;
    }

    /**
     * Determines all answers for knowledge given by the user
     *
     * returns an array of arrays with
     * position_0 -> knowledge area
     * position_1 -> answer
     *
     * @param $userid
     * @return array
     */
    public function knowledge_all($userid) {
        $knowledge_values = array();

        $temp = $this->store->get_knowledge_or_topic_values('knowledge');
        $temp = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
        $options = mod_groupformation_util::xml_to_array($temp);

        for ($i = 0; $i < count($options); $i++) {
            $value = floatval($this->user_manager->get_single_answer($userid, 'knowledge', $i));
            $knowledge_values [] = $value / 100.0;
        }

        return $knowledge_values;
    }

    /**
     * Determines the average of the answers of the user in the category knowledge
     *
     * @param int $userid
     * @return float
     */
    public function knowledge_average($userid) {
        $total = 0;
        $answers = $this->user_manager->get_answers($userid, 'knowledge');
        $number_of_questions = count($answers);
        foreach ($answers as $answer) {
            $total = $total + $answer->answer;
        }

        if ($number_of_questions != 0) {
            $temp = floatval($total) / ($number_of_questions);

            return floatval($temp) / 100;
        } else {
            return 0.0;
        }
    }

    /**
     * Returns the answer of the n-th grade question
     *
     * @param int $questionid
     * @param int $userid
     * @return float
     */
    public function get_grade($questionid, $userid) {
        $answer = $this->user_manager->get_single_answer($userid, 'grade', $questionid);

        return floatval($answer / $this->store->get_max_option_of_catalog_question($questionid));
    }

    /**
     * Returns the answer of the n-th grade question
     *
     * @param int $position
     * @param int $userid
     * @return float
     */
    public function get_points($position, $userid) {
        $max = $this->store->get_max_points();
        $answer = $this->user_manager->get_single_answer($userid, 'points', $position);

        return floatval($answer / $max);
    }

    /**
     * Returns the position of the question, which is needed for the grade criterion
     *
     * $users are the ids for the variance calculation
     *
     * @param unknown $users
     * @return number
     */
    public function get_grade_position($users) {
        $variance = 0;
        $position = 1;
        $total = 0;

        // Iterates over three grade questions.
        for ($i = 1; $i <= 3; $i++) {

            // Answers for catalog question in category 'grade'.
            $answers = $this->store->get_answers_to_special_question('grade', $i);

            // Number of options for catalog question.
            $totalOptions = $this->store->get_max_option_of_catalog_question($i, 'grade');

            $dist = $this->get_initial_array($totalOptions);

            // Iterates over answers for grade questions.
            foreach ($answers as $answer) {

                // Checks if answer is relevant for this group of users.
                if (in_array($answer->userid, $users)) {

                    // Increments count for answer option.
                    $dist [($answer->answer) - 1]++;

                    // Increments count for total.
                    if ($i == 1) {
                        $total++;
                    }
                }
            }

            // Computes tempE for later use.
            $tempE = 0;
            $p = 1;
            foreach ($dist as $d) {
                $tempE = $tempE + ($p * ($d / $total));
                $p++;
            }

            // Computes tempV to find maximal variance.
            $temp_variance = 0;
            $p = 1;
            foreach ($dist as $d) {
                $temp_variance = $temp_variance + ((pow(($p - $tempE), 2)) * ($d / $total));
                $p++;
            }

            // Sets position by maximal variance.
            if ($variance < $temp_variance) {
                $variance = $temp_variance;
                $position = $i;
            }
        }

        return $position;
    }

    /**
     * Returns the position of the question, which is needed for the points criterion
     *
     * $users are the ids for the variance calculation
     *
     * @param unknown $users
     * @return number
     */
    public function get_points_position($users) {
        $variance = 0;
        $position = 1;
        $total = 0;

        // Iterates over three grade questions.
        for ($i = 1; $i <= $this->store->get_number('points'); $i++) {

            // Answers for catalog question in category 'grade'.
            $answers = $this->store->get_answers_to_special_question('points', $i);

            // Number of options for catalog question.
            $totalOptions = $this->store->get_max_option_of_catalog_question($i, 'points');

            $dist = $this->get_initial_array($totalOptions);

            // Iterates over answers for grade questions.
            foreach ($answers as $answer) {

                // Checks if answer is relevant for this group of users.
                if (in_array($answer->userid, $users)) {

                    // Increments count for answer option.
                    $dist [($answer->answer) - 1]++;

                    // Increments count for total.
                    if ($i == 1) {
                        $total++;
                    }
                }
            }

            // Computes tempE for later use.
            $tempE = 0;
            $p = 1;
            foreach ($dist as $d) {
                $tempE = $tempE + ($p * ($d / $total));
                $p++;
            }

            // Computes tempV to find maximal variance.
            $temp_variance = 0;
            $p = 1;
            foreach ($dist as $d) {
                $temp_variance = $temp_variance + ((pow(($p - $tempE), 2)) * ($d / $total));
                $p++;
            }

            // Sets position by maximal variance.
            if ($variance < $temp_variance) {
                $variance = $temp_variance;
                $position = $i;
            }
        }

        return $position;
    }

    /**
     * Returns an array with n = $total fields
     *
     * @param $total
     * @return array
     */
    private function get_initial_array($total) {
        $array = array();
        for ($i = 0; $i < $total; $i++) {
            $array [] = 0;
        }

        return $array;
    }

    /**
     * Returns the Big5 criterion by userid
     *
     * @param $userid
     * @return array
     */
    public function get_big5($userid) {
        $array = array();
        $category = 'character';
        if (!$this->user_manager->has_answered_everything($userid)) {
            return null;
        }
        foreach (array_keys($this->BIG5) as $key) {
            $temp = 0;
            $max_value = 0;
            foreach ($this->BIG5[$key]['questionids'] as $num) {
                $qid = $num;
                if ($num < 0) {
                    $qid = abs($num);
                    if ($this->user_manager->has_answer($userid, $category, $qid)) {
                        $temp = $temp + $this->invert_answer($qid, $category,
                                $this->user_manager->get_single_answer($userid, $category, $qid));
                    }
                } else {
                    if ($this->user_manager->has_answer($userid, $category, $qid)) {
                        $temp = $temp + $this->user_manager->get_single_answer($userid, $category, $qid);
                    }
                }
                $max_value = $max_value + $this->store->get_max_option_of_catalog_question($qid, $category);
            }

            $array [$key] = array('homogeneous' => $this->BIG5[$key]['homogeneous'], "value" => floatval($temp) / ($max_value));
        }

        return $array;
    }

    /**
     * Returns the FAM (motivation criterion) of the user specified by userId
     *
     * @param $userid
     * @return array
     */
    public function get_fam($userid) {
        $array = array();
        $category = 'motivation';
        foreach (array_keys($this->FAM) as $key) {
            $temp = 0;
            $max_value = 0;
            foreach ($this->FAM [$key]['questionids'] as $num) {
                $temp = $temp + $this->user_manager->get_single_answer($userid, $category, $num);
                $max_value = $max_value + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('homogeneous' => $this->FAM[$key]['homogeneous'], 'value' => floatval($temp) / ($max_value));
        }

        return $array;
    }

    /**
     * Returns the learning criterion of the user specified by userId
     *
     * @param $userid
     * @return array
     */
    public function get_learning($userid) {
        $array = array();
        $category = 'learning';

        foreach (array_keys($this->LEARN) as $key) {
            $temp = 0;
            $max_value = 0;
            foreach ($this->LEARN [$key]['questionids'] as $num) {
                $temp = $temp + $this->user_manager->get_single_answer($userid, $category, $num);
                $max_value = $max_value + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('homogeneous' => $this->LEARN[$key]['homogeneous'], 'value' => floatval($temp) / ($max_value));
        }

        return $array;
    }

    /**
     * Returns the team criterion of the user specified by userid
     *
     * @param $userid
     * @return array
     */
    public function get_team($userid) {
        $total = 0.0;
        $max_value = 0.0;
        $array = array();
        $answers = $this->user_manager->get_answers($userid, 'team');
        $number_of_answers = count($answers);
        foreach ($answers as $answer) {
            $total = $total + $answer->answer;
            $max_value = $max_value + $this->store->get_max_option_of_catalog_question($number_of_answers, 'team');
        }

        if ($number_of_answers != 0) {
            $temp = $total / $number_of_answers;
            $temp_total = $max_value / $number_of_answers;
            $array [] = floatval($temp / $temp_total);
        } else {
            $array [] = 0.0;
        }

        return $array;
    }

    /**
     * Returns topic answers as a criterion
     *
     * @param number $userid
     * @return TopicCriterion
     */
    public function get_topic($userid) {
        $choices = $this->user_manager->get_answers($userid, 'topic', 'questionid', 'answer');

        return new lib_groupal_topic_criterion(array_keys($choices));
    }

    public function get_eval($userid, $group_users, $course_users) {
        $eval = array();
        $criteria = $this->store->get_eval_labels();
        foreach ($criteria as $criterion) {
            var_dump($criterion);
            $eval[$criterion] = $this->get_eval_infos($criterion, $userid, $group_users, $course_users);
            var_dump($eval[$criterion]);
        }

        return $eval;
    }

    public function get_values_for_user($criterion, $userid) {
        $function = 'get_' . $criterion;

        return $this->$function($userid);
    }

    public function get_avg_values_for_users($criterion, $group_users) {
        $function = 'get_' . $criterion;
        $avg_values = null;
        $groupsize = count($group_users);
        if ($groupsize > 0) {
            foreach ($group_users as $group_user) {
                $user_values = $this->$function($group_user);
                if (is_null($avg_values)) {
                    $avg_values = $user_values;
                } else{
                    if (!is_null($user_values)) {
                        foreach ($user_values as $key => $user_value) {
                            $avg_values[$key]['value'] += $user_value['value'];
                        }
                    } else {
                        $groupsize = max(1,$groupsize-1);
                    }
                }
            }
            foreach ($avg_values as $key => $avg_value) {
                $avg_values[$key]['value'] /= $groupsize;
            }
        }
        return $avg_values;
    }

    /**
     * Returns big5 values for user, group and course
     *
     * @param $userid
     * @param $group_user
     * @param $course_users
     * @return array
     */
    public function get_eval_infos($criterion, $userid, $group_users, $course_users) {
        $completed_users = array_keys($this->user_manager->get_completed_by_answer_count('userid','userid'));
        $group_and_completed = array_intersect($completed_users,$group_users);
        $course_and_completed = array_intersect($completed_users,$course_users);
        $completed = count($course_and_completed);
        $groupsize = count($group_and_completed);
        $coursesize = count($course_users);
        var_dump($group_and_completed,$groupsize,$course_and_completed,$coursesize);

        $labels = $this->data->get_extra_labels($criterion);

        $eval_infos = array();
        $user_values = $this->get_values_for_user($criterion, $userid);
        $group_values = $this->get_avg_values_for_users($criterion, $group_and_completed);
        $course_values = $this->get_avg_values_for_users($criterion, $course_and_completed);

        foreach ($labels as $label) {
            $user = $user_values[$label]['value'];
            $group = null;
            $course = null;

            if (!(count($group_and_completed)<3 || is_null($group_values))){
                $group = $group_values[$label]['value'];
            }
            if (!(count($course_and_completed)<3 || is_null($course_values))){
                $course = $course_values[$label]['value'];
            }

            $mode = 1;
            $array = array();
            $array["values"] = array("user" => $user, "group" => $group, "course" => $course);
            $array["range"] = array("min" => 0, "max" => 1);
            $array["mode"] = $mode;
            $array["captions"] = $this->get_captions($mode,$completed,$coursesize);
            $eval_infos[$label] = $array;
        }

        return $eval_infos;
    }

    private function get_captions($mode,$completed,$coursesize){
        $percent = round($completed/$coursesize*100,2);
        $a = new stdClass();
        $a->percent = $percent;
        $a->completed = $completed;
        $a->coursesize = $coursesize;
        $captions = array(
            "maxCaption" => "max caption",
            "maxText" => "max text",
            "finalText" => get_string("eval_final_text","groupformation",$a)
        );
        if ($mode == 2){
            $captions["mean"]=0.5;
            $captions["minCaption"] = "min caption";
            $captions["minText"] = "min text";
        }
        return $captions;
    }
}