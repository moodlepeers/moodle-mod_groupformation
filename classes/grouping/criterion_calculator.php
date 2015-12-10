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
    private $usermanager;
    private $data;
    private $groupformationid;
    private $scenario;

    /**
     * mod_groupformation_criterion_calculator constructor.
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
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
            $specs = $this->data->get_criterion_specification("general");
        }

        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];
        if (!$this->usermanager->has_answered_everything($userid)) {
            return null;
        }
        foreach ($labels as $key => $spec) {

            $qids = $spec['questionids'];

            $value = 0;
            foreach ($qids as $qid) {
                $value += $this->usermanager->get_single_answer($userid, $category, $qid);
            }

            // An array(x,y) with x = ENGLISH and y = GERMAN.
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
        $scenario = $this->scenario;
        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];
        foreach ($labels as $key => $spec) {
            $knowledgevalues = array();
            $maxvalue = 100;
            if ($spec['scenarios'][$scenario]) {
                $total = 0;
                $answers = $this->usermanager->get_answers($userid, $category);
                $numberofquestions = count($answers);
                foreach ($answers as $answer) {
                    $total = $total + $answer->answer;
                }

                if ($numberofquestions != 0) {
                    $temp = floatval($total) / ($numberofquestions);
                    $knowledgevalues = array(floatval($temp) / $maxvalue);
                } else {
                    $knowledgevalues = array(0.0);
                }

            } else {
                if (is_null($spec['questionids'])) {

                    $xmlcontent = $this->store->get_knowledge_or_topic_values('knowledge');
                    $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $xmlcontent . ' </OPTIONS>';
                    $options = mod_groupformation_util::xml_to_array($xmlcontent);

                    for ($qid = 0; $qid < count($options); $qid++) {
                        $value = floatval($this->usermanager->get_single_answer($userid, $category, $qid));
                        $knowledgevalues [] = $value / $maxvalue;
                    }
                }
            }
            $array[$key] = array('values' => $knowledgevalues);
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
            $maxanswer = 0;
            foreach ($positions['questionids'] as $k => $p) {
                $answer += $this->usermanager->get_single_answer($userid, $category, $p);
                $maxanswer += $max;
            }
            $answer = floatval($answer / $maxanswer);
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
            $maxanswer = 0;
            foreach ($positions['questionids'] as $k => $p) {
                $answer += $this->usermanager->get_single_answer($userid, $category, $p);
                $maxanswer += $max;
            }
            $answer = floatval($answer / $maxanswer);
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
            if (in_array($this->scenario, $spec['scenarios']) && (!$eval || $spec['evaluation'])) {
                $positions = array();

                foreach ($labels as $label => $specs) {
                    if (array_key_exists($this->scenario, $specs['scenarios']) && (!$eval || $specs['evaluation'])) {
                        if ($specs['significant_id_only']) {
                            $variance = 0;
                            $position = 1;
                            $total = 0;
                            $initialid = null;
                            foreach ($specs['questionids'] as $id) {
                                if (is_null($initialid)) {
                                    $initialid = $id;
                                }
                                // Answers for catalog question in category $criterion.
                                $answers = $this->store->get_answers_to_special_question($category, $id);

                                // Number of options for catalog question.
                                $totaloptions = $this->store->get_max_option_of_catalog_question($id, $category);

                                $dist = array_fill(0, $totaloptions, 0);

                                // Iterates over answers for grade questions.
                                foreach ($answers as $answer) {
                                    // Checks if answer is relevant for this group of users.
                                    if (is_null($users) || in_array($answer->userid, $users)) {

                                        // Increments count for answer option.
                                        $dist [($answer->answer) - 1]++;

                                        // Increments count for total.
                                        if ($id == $initialid) {
                                            $total++;
                                        }
                                    }
                                }

                                // Computes tempexp for later use.
                                $tempexp = 0;
                                $p = 1;
                                foreach ($dist as $d) {
                                    $tempexp = $tempexp + ($p * ($d / $total));
                                    $p++;
                                }

                                // Computes tempvariance to find maximal variance.
                                $tempvariance = 0;
                                $p = 1;
                                foreach ($dist as $d) {
                                    $tempvariance = $tempvariance + ((pow(($p - $tempexp), 2)) * ($d / $total));
                                    $p++;
                                }

                                // Sets position by maximal variance.
                                if ($variance < $tempvariance) {
                                    $variance = $tempvariance;
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
        if (!$this->usermanager->has_answered_everything($userid)) {
            return null;
        }
        foreach ($labels as $key => $spec) {
            $temp = 0;
            $maxvalue = 0;
            foreach ($spec['questionids'] as $num) {
                $qid = $num;
                if ($num < 0) {
                    $qid = abs($num);
                    if ($this->usermanager->has_answer($userid, $category, $qid)) {
                        $temp = $temp + $this->invert_answer($qid, $category,
                                $this->usermanager->get_single_answer($userid, $category, $qid));
                    }
                } else {
                    if ($this->usermanager->has_answer($userid, $category, $qid)) {
                        $temp = $temp + $this->usermanager->get_single_answer($userid, $category, $qid);
                    }
                }
                $maxvalue = $maxvalue + $this->store->get_max_option_of_catalog_question($qid, $category);
            }

            $array [$key] = array("values" => array(floatval($temp) / ($maxvalue)));
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
            $maxvalue = 0;
            foreach ($spec['questionids'] as $num) {
                $temp = $temp + $this->usermanager->get_single_answer($userid, $category, $num);
                $maxvalue = $maxvalue + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('values' => array(floatval($temp) / ($maxvalue)));
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
            $maxvalue = 0;
            foreach ($spec['questionids'] as $num) {
                $temp = $temp + $this->usermanager->get_single_answer($userid, $category, $num);
                $maxvalue = $maxvalue + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('values' => array(floatval($temp) / ($maxvalue)));
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
            $maxvalue = 0;
            foreach ($spec['questionids'] as $num) {
                $temp = $temp + $this->usermanager->get_single_answer($userid, $category, $num);
                $maxvalue = $maxvalue + $this->store->get_max_option_of_catalog_question($num, $category);
            }
            $array [$key] = array('values' => array(floatval($temp) / ($maxvalue)));
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
        $choices = $this->usermanager->get_answers($userid, 'topic', 'questionid', 'answer');

        return new lib_groupal_topic_criterion(array_keys($choices));
    }

    /**
     * Returns eval data for user
     *
     * @param $userid
     * @param $groupusers
     * @param $courseusers
     * @return array
     */
    public function get_eval($userid, $groupusers, $courseusers) {
        $eval = array();
        $criteria = $this->store->get_label_set();

        foreach ($criteria as $criterion) {
            $labels = $this->data->get_criterion_specification($criterion);
            if (!is_null($labels)) {
                $labels = $this->filter_criterion_specs_for_eval($criterion, $labels);
            }
            if (!is_null($labels) && count($labels) > 0) {
                $eval[$criterion] = $this->get_eval_infos($criterion, $labels, $userid, $groupusers, $courseusers);
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
     * @param $groupusers
     * @return null
     */
    public function get_avg_values_for_users($criterion, $groupusers) {
        $function = 'get_' . $criterion;
        $avgvalues = null;
        $groupsize = count($groupusers);
        if ($groupsize > 0) {
            foreach ($groupusers as $groupuser) {
                $uservalues = $this->$function($groupuser);
                if (is_null($avgvalues)) {
                    $avgvalues = $uservalues;
                } else {
                    if (!is_null($uservalues)) {
                        foreach ($uservalues as $key => $uservalue) {
                            $avgvalues[$key]['value'] += $uservalue['value'];
                        }
                    } else {
                        $groupsize = max(1, $groupsize - 1);
                    }
                }
            }
            foreach ($avgvalues as $key => $avgvalue) {
                $avgvalues[$key]['value'] /= $groupsize;
            }
        }

        return $avgvalues;
    }

    /**
     * Returns eval values for user, group and course
     *
     * @param $criterion
     * @param $labels
     * @param $userid
     * @param $groupusers
     * @param $courseusers
     * @return array
     */
    public function get_eval_infos($criterion, $labels, $userid, $groupusers, $courseusers) {
        $completedusers = array_keys($this->usermanager->get_completed_by_answer_count('userid', 'userid'));
        $groupandcompleted = array_intersect($completedusers, $groupusers);
        $courseandcompleted = array_intersect($completedusers, $courseusers);
        $completed = count($courseandcompleted);
        $coursesize = count($courseusers);
        $setfinaltext = $coursesize > 2;

        $evalinfos = array();
        $uservalues = $this->get_values_for_user($criterion, $userid, $labels);
        $groupvalues = $this->get_avg_values_for_users($criterion, $groupandcompleted, $labels);
        $coursevalues = $this->get_avg_values_for_users($criterion, $courseandcompleted, $labels);
        foreach ($labels['labels'] as $label => $spec) {
            $user = $uservalues[$label]['values'][0];
            $group = null;
            $course = null;

            if (!(count($groupandcompleted) < 3 || is_null($groupvalues))) {
                $group = $groupvalues[$label]['value'];
            }
            if (!(count($courseandcompleted) < 3 || is_null($coursevalues))) {
                $course = $coursevalues[$label]['value'];
            }

            $mode = 1;
            $array = array();
            $array["name"] = $label;
            $array["values"] = array("user" => $user, "group" => $group, "course" => $course);
            $array["range"] = array("min" => 0, "max" => 1);
            $array["mode"] = $mode;
            $array["captions"] = $this->get_captions($mode, $setfinaltext, $completed, $coursesize);
            $evalinfos[] = $array;

        }

        return $evalinfos;
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