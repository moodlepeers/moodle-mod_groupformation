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
    private $scenario;

    /**
     *
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->user_manager = new mod_groupformation_user_manager ($groupformationid);
        $this->data = new mod_groupformation_data();

        $this->scenario = $this->store->get_scenario();
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
     * Returns general criterion values
     *
     * @param number $userid
     * @param array $specs
     * @return string
     */
    public function get_general($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification("big5");
        }

        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];
        if (!$this->user_manager->has_answered_everything($userid)) {
            return null;
        }
        foreach ($labels as $key => $spec) {

            $qids = $spec['questionids'];

            $value = 0;
            foreach ($qids as $qid) {
                $value += $this->user_manager->get_single_answer($userid, $category, $qid);
            }

            // array(x,y) with x = ENGLISH and y = GERMAN.
            $values = array(1.0, 0.0);
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

            $tmp = array();
            $tmp["values"] = $values;
            $array[$key] = $tmp;
        }


        return $array;
    }

    /**
     * Returns knowledge criterion values
     *
     * @param $userid
     * @param null $specs
     * @return array
     */
    public function get_knowledge($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification('knowledge');
        }

        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];
        foreach ($labels as $key => $spec) {
            $knowledge_values = array();
            $temp = 0;
            $max_value = 100;
            if ($spec['homogeneous']) {
                $total = 0;
                $answers = $this->user_manager->get_answers($userid, $category);
                $number_of_questions = count($answers);
                foreach ($answers as $answer) {
                    $total = $total + $answer->answer;
                }

                if ($number_of_questions != 0) {
                    $temp = floatval($total) / ($number_of_questions);
                    $knowledge_values = array(floatval($temp) / $max_value);
                } else {
                    $knowledge_values = array(0.0);
                }


            } else {
                if (is_null($spec['questionids'])) {

                    $tmp = $this->store->get_knowledge_or_topic_values('knowledge');
                    $tmp = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $tmp . ' </OPTIONS>';
                    $options = mod_groupformation_util::xml_to_array($tmp);

                    for ($qid = 0; $qid < count($options); $qid++) {
                        $value = floatval($this->user_manager->get_single_answer($userid, $category, $qid));
                        $knowledge_values [] = $value / $max_value;
                    }
                }
            }
            $array[$key] = array('values' => $knowledge_values);
        }

        return $array;
    }

    /**
     * Returns points criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return float
     */
    public function get_points($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification('points');
        }

        $labels = $specs['labels'];
        $answers = array();
        $category = $specs['category'];

        $max = $this->store->get_max_points();
        foreach ($labels as $key => $positions) {
            $answer = 0;
            $max_answer = 0;
            foreach ($positions['questionids'] as $k => $p) {
                $answer += $this->user_manager->get_single_answer($userid, $category, $p);
                $max_answer += $max;
            }
            $answer = floatval($answer / $max_answer);
            $answers[$key] = array("values" => array($answer));
        }

        return $answers;
    }

    /**
     * Returns grade criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return float
     */
    public function get_grade($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification('grade');
        }

        $labels = $specs['labels'];
        $answers = array();
        $category = $specs['category'];

        $max = $this->store->get_max_points();
        foreach ($labels as $key => $positions) {
            $answer = 0;
            $max_answer = 0;
            foreach ($positions['questionids'] as $k => $p) {
                $answer += $this->user_manager->get_single_answer($userid, $category, $p);
                $max_answer += $max;
            }
            $answer = floatval($answer / $max_answer);
            $answers[$key] = array("values" => array($answer));
        }

        return $answers;
    }

    /**
     * Filter criteria specs by erasing useless question ids if not significant enough
     *
     * @param $criteriaspecs
     * @param $users
     * @param bool|false $eval
     * @return array
     */
    public function filter_criteria_specs($criteriaspecs, $users, $eval = false) {
        $filteredspecs = array();
        foreach ($criteriaspecs as $criterion => $spec) {
            $category = $spec['category'];
            $labels = $spec['labels'];
            if (in_array($this->scenario, $spec['scenario']) && (!$eval || $spec['evaluation'])) {
                $positions = array();

                foreach ($labels as $label => $specs) {
                    if (array_key_exists($this->scenario, $specs['scenario']) && (!$eval || $specs['evaluation'])) {
                        if ($specs['significant_id_only']) {
                            $variance = 0;
                            $position = 1;
                            $total = 0;
                            $initial_id = null;
                            foreach ($specs['questionids'] as $id) {
                                if (is_null($initial_id)){
                                    $initial_id = $id;
                                }
                                // Answers for catalog question in category $criterion.
                                $answers = $this->store->get_answers_to_special_question($category, $id);

                                // Number of options for catalog question.
                                $totalOptions = $this->store->get_max_option_of_catalog_question($id, $category);

                                $dist = array_fill(0, $totalOptions, 0);

                                // Iterates over answers for grade questions.
                                foreach ($answers as $answer) {
                                    // Checks if answer is relevant for this group of users.
                                    if (is_null($users) || in_array($answer->userid, $users)) {

                                        // Increments count for answer option.
                                        $dist [($answer->answer) - 1]++;

                                        // Increments count for total.
                                        if ($id == $initial_id) {
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
                                    $position = $id;
                                }

                            }
                            $specs['questionids'] = array($position);
                        }

                        $positions[$label] = $specs;
                    }

                }

                if (count($positions) > 0) {
                    $spec['labels'] = $positions;
                    $filteredspecs[$criterion] = $spec;
                }
            }

        }

        return $filteredspecs;
    }

    /**
     * Filters criterion specs by eval
     *
     * @param $criterion
     * @param $criterionspecs
     * @param null $users
     * @return array
     */
    public function filter_criterion_specs_for_eval($criterion, $criterionspecs, $users = null) {
        $array = array($criterion => $criterionspecs);
        $result = $this->filter_criteria_specs($array, $users, true);
        if (count($result) > 0) {
            return $result[$criterion];
        } else {
            return array();
        }
    }

    /**
     * Returns big5 criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     */
    public function get_big5($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification("big5");
        }

        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];
        if (!$this->user_manager->has_answered_everything($userid)) {
            return null;
        }
        foreach ($labels as $key => $spec) {
            $temp = 0;
            $max_value = 0;
            foreach ($spec['questionids'] as $num) {
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

            $array [$key] = array("values" => array(floatval($temp) / ($max_value)));
        }

        return $array;
    }

    /**
     * Returns fam criterion values
     *
     * @param $userid
     * @param $specs
     * @return array
     */
    public function get_fam($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification("fam");
        }
        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];
        foreach ($labels as $key => $spec) {
            $temp = 0;
            $max_value = 0;
            foreach ($spec['questionids'] as $num) {
                $temp = $temp + $this->user_manager->get_single_answer($userid, $category, $num);
                $max_value = $max_value + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('values' => array(floatval($temp) / ($max_value)));
        }

        return $array;
    }

    /**
     * Returns learning criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     */
    public function get_learning($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification("learning");
        }
        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];

        foreach ($labels as $key => $spec) {
            $temp = 0;
            $max_value = 0;
            foreach ($spec['questionids'] as $num) {
                $temp = $temp + $this->user_manager->get_single_answer($userid, $category, $num);
                $max_value = $max_value + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('values' => array(floatval($temp) / ($max_value)));
        }

        return $array;
    }

    /**
     * Returns team criterion values
     *
     * @param $userid
     * @param $specs
     * @return array
     */
    public function get_team($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification("team");
        }
        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];

        foreach ($labels as $key => $spec) {
            $temp = 0;
            $max_value = 0;
            foreach ($spec['questionids'] as $num) {
                $temp = $temp + $this->user_manager->get_single_answer($userid, $category, $num);
                $max_value = $max_value + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('values' => array(floatval($temp) / ($max_value)));
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

    /**
     * Returns eval data for user
     *
     * @param $userid
     * @param $group_users
     * @param $course_users
     * @return array
     */
    public function get_eval($userid, $group_users, $course_users) {
        $eval = array();
        $criteria = $this->store->get_label_set();

        foreach ($criteria as $criterion) {
            $labels = $this->data->get_criterion_specification($criterion);
            $labels = $this->filter_criterion_specs_for_eval($criterion, $labels);
            if (count($labels) > 0) {
                $eval[$criterion] = $this->get_eval_infos($criterion, $labels, $userid, $group_users, $course_users);
            }
        }

        return $eval;
    }

    /**
     * Returns values for user
     *
     * @param string $criterion
     * @param int $userid
     * @param array $specs
     * @return mixed
     */
    public function get_values_for_user($criterion, $userid, $specs = null) {
        $function = 'get_' . $criterion;

        return $this->$function($userid, $specs);
    }

    /**
     * Returns average values for the users
     *
     * @param $criterion
     * @param $group_users
     * @return null
     */
    public function get_avg_values_for_users($criterion, $group_users) {
        $function = 'get_' . $criterion;
        $avg_values = null;
        $groupsize = count($group_users);
        if ($groupsize > 0) {
            foreach ($group_users as $group_user) {
                $user_values = $this->$function($group_user);
                if (is_null($avg_values)) {
                    $avg_values = $user_values;
                } else {
                    if (!is_null($user_values)) {
                        foreach ($user_values as $key => $user_value) {
                            $avg_values[$key]['value'] += $user_value['value'];
                        }
                    } else {
                        $groupsize = max(1, $groupsize - 1);
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
     * Returns eval values for user, group and course
     *
     * @param $criterion
     * @param $labels
     * @param $userid
     * @param $group_users
     * @param $course_users
     * @return array
     */
    public function get_eval_infos($criterion, $labels, $userid, $group_users, $course_users) {
        $completed_users = array_keys($this->user_manager->get_completed_by_answer_count('userid', 'userid'));
        $group_and_completed = array_intersect($completed_users, $group_users);
        $course_and_completed = array_intersect($completed_users, $course_users);
        $completed = count($course_and_completed);
        $coursesize = count($course_users);
        $setfinaltext = $coursesize > 2;

        $eval_infos = array();

        $user_values = $this->get_values_for_user($criterion, $userid);
        $group_values = $this->get_avg_values_for_users($criterion, $group_and_completed);
        $course_values = $this->get_avg_values_for_users($criterion, $course_and_completed);
        foreach ($labels['labels'] as $label => $spec) {

            $user = $user_values[$label]['values'][0];
            $group = null;
            $course = null;

            if (!(count($group_and_completed) < 3 || is_null($group_values))) {
                $group = $group_values[$label]['value'];
            }
            if (!(count($course_and_completed) < 3 || is_null($course_values))) {
                $course = $course_values[$label]['value'];
            }

            $mode = 1;
            $array = array();
            $array["name"] = $label;
            $array["values"] = array("user" => $user, "group" => $group, "course" => $course);
            $array["range"] = array("min" => 0, "max" => 1);
            $array["mode"] = $mode;
            $array["captions"] = $this->get_captions($mode, $setfinaltext, $completed, $coursesize);
            $eval_infos[] = $array;

        }

        return $eval_infos;
    }

    /**
     * Returns captions for evaluation data
     *
     * @param $mode
     * @param $setfinaltext
     * @param $completed
     * @param $coursesize
     * @return array
     * @throws coding_exception
     */
    private function get_captions($mode, $setfinaltext, $completed, $coursesize) {
        $percent = round($completed / $coursesize * 100, 2);
        $a = new stdClass();
        $a->percent = $percent;
        $a->completed = $completed;
        $a->coursesize = $coursesize;
        $captions = array(
            "maxCaption" => "max caption",
            "maxText" => "max text",
            "finalText" => (($setfinaltext) ? get_string("eval_final_text", "groupformation", $a) : null)
        );
        if ($mode == 2) {
            $captions["mean"] = 0.5;
            $captions["minCaption"] = "min caption";
            $captions["minText"] = "min text";
        }

        return $captions;
    }
}