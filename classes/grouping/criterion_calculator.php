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
 * Criterion calculator for grouping
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/criteria/topic_criterion.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/group/lib.php');

/**
 * Class mod_groupformation_criterion_calculator
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_criterion_calculator {

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    /** @var int ID of module instance */
    public $groupformationid;

    /** @var string Scenario of the activity */
    private $scenario;

    /**
     * mod_groupformation_criterion_calculator constructor.
     *
     * @param int $groupformationid
     * @throws dml_exception
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
        $this->scenario = $this->store->get_scenario(true);
    }

    /**
     * Inverts given answer by considering maximum
     *
     * @param number $questionid
     * @param string $category
     * @param number $answer
     * @return number
     * @throws dml_exception
     */
    private function invert_answer($questionid, $category, $answer) {
        $max = $this->store->get_max_option_of_catalog_question($questionid, $category);

        return $max + 1 - $answer;
    }

    /**
     * Filter criteria specs by erasing useless question ids if not significant enough
     *
     * @param array $criteriaspecs
     * @param array $users
     * @param bool $eval
     * @return array
     * @throws dml_exception
     */
    public function filter_criteria_specs($criteriaspecs, $users, $eval = false) {
        $filteredspecs = array();

        foreach ($criteriaspecs as $criterion => $spec) {
            if (!is_null($spec)) {


                $scenarios = $spec['scenarios'];
                $validscenario = in_array($this->scenario, $scenarios);
                $validforeval = !$eval || array_key_exists('evaluation', $spec);

                if ($validscenario && $validforeval) {

                    $category = $spec['category'];
                    $labels = $spec['labels'];

                    $positions = array();

                    // Check for each label of a criterion
                    // Either validscenario or validforeval
                    foreach ($labels as $label => $specs) {


                        $validscenario = array_key_exists($this->scenario, $specs['scenarios']);
                        $validforeval = (!$eval || (array_key_exists('evaluation', $specs) && $specs['evaluation']));


                        if ($validscenario && $validforeval) {
                            if (array_key_exists('significant_id_only', $specs) && $specs['significant_id_only']) {
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

                                    $dist = array_fill(1, $totaloptions, 0);

                                    // Iterates over answers for grade questions.
                                    foreach ($answers as $answer) {
                                        // Checks if answer is relevant for this group of users.
                                        if (is_null($users) || in_array($answer->userid, $users)) {

                                            // Increments count for answer option.
                                            $dist [intval($answer->answer)]++;
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
        }
        return $filteredspecs;
    }

    /**
     * Filters criterion specs by eval
     *
     * @param array $criterion
     * @param array $criterionspecs
     * @param array $users
     * @return array
     * @throws dml_exception
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
     * Computes values for given criterion
     *
     * @param array $criterion
     * @param int $userid
     * @param array $specs
     * @return array|null
     * @throws dml_exception
     */
    public function get_values($criterion, $userid, $specs = null) {

        if (is_null($specs)) {
            $specs = mod_groupformation_data::get_criterion_specification($criterion);
        }

        $labels = $specs['labels'];
        $category = $specs['category'];

        $array = array();

        if (!$this->usermanager->has_answered_everything($userid)) {
            return null;
        }

        foreach ($labels as $key => $spec) {

            $temp = 0;
            $minvalue = 0;
            $maxvalue = 0;

            $questionids = $spec['questionids'];
            if (array_key_exists($this->scenario, $spec['scenarios'])) {

                foreach (array_values($questionids) as $tempquestionid) {
                    $questionid = $tempquestionid;
                    if ($tempquestionid < 0) {
                        $questionid = abs($tempquestionid);
                        if ($this->usermanager->has_answer($userid, $category, $questionid)) {
                            $singleanswer = $this->usermanager->get_single_answer($userid, $category, $questionid);
                            $temp = $temp + $this->invert_answer($questionid, $category,
                                    $singleanswer);
                        }
                    } else {
                        if ($this->usermanager->has_answer($userid, $category, $questionid)) {
                            $temp = $temp + $this->usermanager->get_single_answer($userid, $category, $questionid);
                        }
                    }
                    $minvalue = $minvalue + 1;
                    $maxvalue = $maxvalue + $this->store->get_max_option_of_catalog_question($questionid, $category);
                }
                $array [$key] = array("values" => array(floatval($temp - $minvalue) / ($maxvalue - $minvalue)));
            }
        }
        return $array;
    }

    /**
     * Returns big5 criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     * @throws dml_exception
     */
    public function get_big5($userid, $specs = null) {
        return $this->get_values('big5', $userid, $specs);
    }

    /**
     * Returns fam criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     * @throws dml_exception
     */
    public function get_fam($userid, $specs = null) {
        return $this->get_values('fam', $userid, $specs);
    }

    /**
     * Returns learning criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     * @throws dml_exception
     */
    public function get_learning($userid, $specs = null) {
        return $this->get_values('learning', $userid, $specs);
    }

    /**
     * Returns general criterion values
     *
     * @param number $userid
     * @param array $specs
     * @return string
     * @throws dml_exception
     */
    public function get_general($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = mod_groupformation_data::get_criterion_specification("general");
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
     * @param int $userid
     * @param null $specs
     * @return array
     * @throws dml_exception
     */
    public function get_knowledge($userid, $specs = null) {

        if (is_null($specs)) {
            $specs = mod_groupformation_data::get_criterion_specification('knowledge');
        }
        $scenario = $this->scenario;
        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];

        $answers = $this->usermanager->get_answers($userid, $category);
        $optionscount = $this->store->get_number($category);
        if (count($answers) != $optionscount) {
            return $array;
        }

        // Iterate over labels of criterion.
        foreach ($labels as $key => $spec) {
            $knowledgevalues = array();

            // Max value for answer to knowledge question.
            $maxvalue = 100;

            // Check whether this label is for this scenario.
            if (array_key_exists($scenario, $spec['scenarios'])) {

                // Checks whether the values should be one dimension or separate dimensions.
                if (array_key_exists('separate_dimensions', $spec) && $spec['separate_dimensions']) {

                    // Computes each quotient of answer/maxvalue and adds it as dimension.
                    for ($qid = 1; $qid <= $optionscount; $qid++) {
                        $value = floatval($this->usermanager->get_single_answer($userid, $category, $qid));
                        $knowledgevalues [] = $value / $maxvalue;
                    }

                } else {
                    $total = 0;
                    $answers = $this->usermanager->get_answers($userid, $category);

                    // Computes sum of all answers.
                    foreach ($answers as $answer) {
                        $total = $total + $answer->answer;
                    }

                    // Computes average over all answers and quotient of average/maxvalue.
                    $temp = floatval($total) / ($optionscount);
                    $knowledgevalues = array(floatval($temp) / $maxvalue);
                }
            }

            $array[$key] = array('values' => $knowledgevalues);

        }
        return $array;
    }


    // TODO bisher nur grob. Testen, aufräumen, kommentieren.

    /**
     * Returns binquestion criterion values
     *
     * @param $userid
     * @param null $specs
     * @return array
     * @throws dml_exception
     */
    public function get_binquestion($userid, $specs = null) {

        if (is_null($specs)) {
            $specs = mod_groupformation_data::get_criterion_specification('binquestion');
        }
        $scenario = $this->scenario;
        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];

        $questiontype = $this->usermanager->get_binquestionmultiselect(); // 0 := singlechoice; 1 := multiselect
        $number_of_choices = floatval($this->store->get_number_binchoices());
        $answers = $this->usermanager->get_single_answer($userid, $category,1);
        $answers = str_replace('list:', '', $answers);
        $answer_array = str_getcsv($answers);
        $cur_index_answers = 0;
        $binvalue = '';
        $importance = floatval($this->usermanager->get_binquestionimportance())/10;

        if ($questiontype == 0){
            $answer_array[0] -= 1;
        }

        for ($i = 0; $i < $number_of_choices; $i++) { // Creates an array in a vector-form with 0 and 1 as entries like "0,1,1,0,0"
            if ($i == $answer_array[$cur_index_answers]){
                $binvalue .= '1';
                $cur_index_answers++;
            } else {
                $binvalue .= '0';
            }
            if (($i+1) < $number_of_choices){
                $binvalue .= ',';
            }
        }



        // Iterate over labels of criterion.
        foreach ($labels as $key => $spec) { // maybe later there are more than one binquestion per groupformation
            if (($questiontype == 0 && $key == 'singlechoice') || ($questiontype == 1 && $key == 'multiselect')) {
                $binquestionvalues = array();

                if (array_key_exists($scenario, $spec['scenarios'])) {

                    $binquestionvalues [] = array(
                        'binvalue' => $binvalue,
                        'importance' => $importance
                    );
                }

                $array[$key] = array('values' => $binquestionvalues);
            }

        }
        return $array;
    }

    /**
     * Returns points criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     * @throws dml_exception
     */
    public function get_points($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = mod_groupformation_data::get_criterion_specification('points');
        }

        $scenario = $this->scenario;
        $labels = $specs['labels'];
        $answers = array();
        $category = $specs['category'];

        $maxvalue = $this->store->get_max_points();

        foreach ($labels as $key => $spec) {
            $answer = 0;
            $maxanswer = 0;

            // Check whether this label is for this scenario.
            if (array_key_exists($scenario, $spec['scenarios'])) {

                // Sums up all answers with respect to given questionids.
                foreach (array_values($spec['questionids']) as $questionid) {
                    $answer += $this->usermanager->get_single_answer($userid, $category, $questionid);
                    $maxanswer += $maxvalue;
                }

                // Computes average.
                $answer = floatval($answer / $maxanswer);
                $answers[$key] = array("values" => array($answer));
            }
        }

        return $answers;
    }

    /**
     * Returns grade criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     * @throws dml_exception
     */
    public function get_grade($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = mod_groupformation_data::get_criterion_specification('grade');
        }

        $labels = $specs['labels'];
        $answers = array();
        $category = $specs['category'];

        $max = $this->store->get_max_points();
        foreach ($labels as $key => $positions) {
            $answer = 0;
            $maxanswer = 0;
            foreach (array_values($positions['questionids']) as $p) {
                $answer += $this->usermanager->get_single_answer($userid, $category, $p);
                $maxanswer += $max;
            }
            $answer = floatval($answer / $maxanswer);
            $answers[$key] = array("values" => array($answer));
        }

        return $answers;
    }

    /**
     * Returns team criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     * @throws dml_exception
     */
    public function get_team($userid, $specs = null) {
        return $this->get_values('team', $userid, $specs);
    }

    /**
     * Returns topic answers as a criterion
     *
     * @param number $userid
     * @return mod_groupformation_topic_criterion
     * @throws dml_exception
     */
    public function get_topic($userid) {
        $choices = $this->usermanager->get_answers($userid, 'topic', 'questionid', 'answer');

        return new mod_groupformation_topic_criterion(array_keys($choices));
    }

    /**
     * Computes z score
     *
     * @param array $usersvalues
     * @return mixed
     */
    public function compute_z_score($usersvalues) {

        $mean = null;

        foreach ($usersvalues as $userid => $labels) {
            if (is_null($mean)) {
                $mean = $labels;
            } else {
                foreach ($labels as $label => $labelvalues) {
                    $values = $labelvalues['values'];
                    foreach ($values as $k => $value) {
                        $mean[$label]['values'][$k] += $value;
                    }
                }
            }
        }

        $size = count($usersvalues);

        foreach ($mean as $label => $labelvalues) {
            $values = $labelvalues['values'];
            foreach ($values as $k => $value) {
                $mean[$label]['values'][$k] /= $size;
            }
        }

        $variance = null;
        $i = 1;
        foreach ($usersvalues as $userid => $labels) {
            if (is_null($variance)) {
                $variance = $labels;
            }

            foreach ($labels as $label => $labelvalues) {
                $values = $labelvalues['values'];
                foreach ($values as $k => $value) {
                    $meanvalue = $mean[$label]['values'][$k];
                    if ($i == 1) {
                        $variance[$label]['values'][$k] = 0;
                    }
                    $variance[$label]['values'][$k] += pow($value - $meanvalue, 2) / $size;
                }
            }
            $i += 1;
        }

        $stddeviation = $variance;

        foreach ($stddeviation as $label => $labelvalues) {
            $values = $labelvalues['values'];
            foreach ($values as $k => $value) {
                $stddeviation[$label]['values'][$k] = sqrt($stddeviation[$label]['values'][$k]);
            }
        }

        foreach ($usersvalues as $userid => $labels) {
            foreach ($labels as $label => $labelvalues) {
                $values = $labelvalues['values'];
                foreach ($values as $k => $value) {
                    $meanvalue = $mean[$label]['values'][$k];
                    $stdvvalue = $stddeviation[$label]['values'][$k];
                    $xvalue = $labels[$label]['values'][$k];
                    $diff = $xvalue - $meanvalue;
                    if ($diff == 0) {
                        $zscore = 0;
                    } else if ($stdvvalue == 0) {
                        $zscore = 0;
                    } else {
                        $zscore = ($xvalue - $meanvalue) / $stdvvalue;
                    }
                    $labels[$label]['values'][$k] = $this->lookup_z($zscore);
                }
            }
        }

        return $usersvalues;
    }

    /**
     * Lookup z-score
     *
     * @param number $z
     * @return float|mixed
     */
    private function lookup_z($z) {
        $z = strval(round($z, 2));
        $val = 0.0;
        if (-3.00 <= $z && $z <= 3.00) {
            $val = groupformation_z_lookup_table($z);
        } else if (-3.00 > $z) {
            $val = 0.0;
        } else if (3.00 < $z) {
            $val = 1.0;
        }

        return $val;
    }

    /**
     * Returns eval data for user
     *
     * @param int $userid
     * @param array $groupusers
     * @param array $courseusers
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_eval($userid, $groupusers = array(), $courseusers = array()) {
        $completedusers = array_keys($this->usermanager->get_completed_by_answer_count('userid', 'userid'));
        $groupandcompleted = array_intersect($completedusers, $groupusers);
        $courseandcompleted = array_intersect($completedusers, $courseusers);

        $vals = array('user');
        if (count($groupandcompleted) > 0) {
            $vals[] = 'group';
        }
        if (count($courseandcompleted) > 1) {
            $vals[] = 'course';
        }

        $eval = array(
            array(
                "name" => "first_page",
                "mode" => "text",
                "caption" => get_string("eval_first_page_title", "groupformation"),
                "text" => get_string("eval_first_page_text", "groupformation")
            )
        );
        $criteria = $this->store->get_label_set();
        foreach ($criteria as $criterion) {
            $labels = mod_groupformation_data::get_criterion_specification($criterion);
            if (!is_null($labels)) {
                $labels = $this->filter_criterion_specs_for_eval($criterion, $labels);
            }
            if (!is_null($labels) && count($labels) > 0) {
                $array = $this->get_eval_infos($criterion, $labels, $userid, $groupusers, $courseusers);

                $bars = array();
                $values = array('user' => 1, 'group' => 4, 'course' => 2);

                foreach (array_keys($values) as $key) {
                    $bars[$key] = get_string("eval_caption_" . $key, "groupformation");
                }

                $directions = 1;
                if ($criterion == 'big5') {
                    $directions = 2;
                }

                $eval[] = array(
                    "name" => $criterion,
                    "directions" => $directions,
                    "mode" => "chart",
                    "caption" => get_string('eval_name_' . $criterion, 'groupformation'),
                    "values" => $vals,
                    "bars" => $bars,
                    "criteria" => $array
                );

            }
        }

        return $eval;
    }

    /**
     * Returns eval values for user, group and course
     *
     * @param array $criterion
     * @param array $labels
     * @param int $userid
     * @param array $groupusers
     * @param array $courseusers
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_eval_infos($criterion, $labels, $userid, $groupusers = array(), $courseusers = array()) {
        $completedusers = array_keys($this->usermanager->get_completed_by_answer_count('userid', 'userid'));
        $groupandcompleted = array_intersect($completedusers, $groupusers);
        $courseandcompleted = array_intersect($completedusers, $courseusers);
        $completed = count($courseandcompleted);
        $coursesize = count($courseusers);
        $setfinaltext = $coursesize > 2;

        $evalinfos = array();

        $users = array_merge(array(intval($userid)), $groupandcompleted, $courseandcompleted);
        $users = array_unique($users);

        $uservalues = $this->read_values_for_user($criterion, $userid, $labels);

        $usersvalues = $this->get_values_for_users($criterion, $users);

        $usersvalues = $this->compute_z_score($usersvalues);

        $groupvalues = $this->get_avg_values_for_users($groupandcompleted, $usersvalues);
        $coursevalues = $this->get_avg_values_for_users($courseandcompleted, $usersvalues);

        foreach ($labels['labels'] as $label => $spec) {
            $user = $uservalues[$label]['values'][0];
            $group = null;
            $course = null;

            if (count($groupandcompleted) >= 2 && !is_null($groupvalues)) {
                $group = $groupvalues[$label]['values'][0];
            }

            if (count($courseandcompleted) >= 2 && !is_null($coursevalues)) {
                $course = $coursevalues[$label]['values'][0];
            }

            $mode = 1;
            if ($criterion == "big5") {
                $mode = 2;
            }
            $array = array();
            $array["name"] = $label;
            $array["values"] = array("user" => $user, "group" => $group, "course" => $course);
            $array["range"] = array("min" => 0, "max" => 1);
            $array["mode"] = $mode;
            $array["captions"] = $this->get_captions($label, $mode, $setfinaltext, $completed, $coursesize);
            $array["cutoff"] = $this->get_eval_text($criterion, $label, $spec["cutoffs"], $user);
            $evalinfos[] = $array;
        }

        return $evalinfos;
    }

    /**
     * Returns evaluation text
     *
     * @param string $criterion
     * @param string $label
     * @param array $cutoffs
     * @param array $uservalue
     * @return string
     * @throws coding_exception
     */
    private function get_eval_text($criterion, $label, $cutoffs, $uservalue) {
        if (is_null($cutoffs)) {
            return "eval_text_" . $criterion . "_" . $label;
        } else {

            $i = 1;

            foreach ($cutoffs as $cutoff) {
                if ($uservalue >= $cutoff) {
                    $i += 1;
                }
            }
            return get_string("eval_text_" . $criterion . "_" . $label . "_" . $i, "groupformation");
        }

    }

    /**
     * Returns captions for evaluation data
     *
     * @param string $label
     * @param number $mode
     * @param bool $setfinaltext
     * @param bool $completed
     * @param number $coursesize
     * @return array
     * @throws coding_exception
     */
    private function get_captions($label, $mode, $setfinaltext, $completed, $coursesize) {
        $percent = round($completed / ($coursesize + 1) * 100, 2);
        $a = new stdClass();
        $a->percent = $percent;
        $a->completed = $completed;
        $a->coursesize = $coursesize;
        $captions = array(
            "cutoffCaption" => get_string("eval_cutoff_caption_" . $label, "groupformation"),
            "maxCaption" => get_string("eval_max_caption_" . $label, "groupformation"),
            "maxText" => get_string("eval_max_text_" . $label, "groupformation"),
            "finalText" => (($setfinaltext) ? get_string("eval_final_text", "groupformation", $a) : null)
        );
        if ($mode == 2) {
            $captions["mean"] = 0.5;
            $captions["minCaption"] = get_string("eval_min_caption_" . $label, "groupformation");
            $captions["minText"] = get_string("eval_min_text_" . $label, "groupformation");
        }

        return $captions;
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
     * Reads values from DB
     *
     * @param string $criterion
     * @param int $userid
     * @return array
     * @throws dml_exception
     */
    public function read_values_for_user($criterion, $userid) {
        global $DB;

        $recs = $DB->get_records('groupformation_user_values',
            array('groupformationid' => $this->groupformationid,
                'userid' => $userid,
                'criterion' => $criterion
            )
        );

        $array = array();
        foreach (array_values($recs) as $rec) {
            if (!array_key_exists($rec->label, $array)) {
                $array[$rec->label] = array();
            }
            if (!array_key_exists('values', $array[$rec->label])) {
                $array[$rec->label]['values'] = array();
            }
            $array[$rec->label]['values'][$rec->dimension] = floatval($rec->value);
        }

        return $array;
    }

    /**
     * Returns values for users
     *
     * @param string $criterion
     * @param array $users
     * @return mixed
     * @throws dml_exception
     */
    public function get_values_for_users($criterion, $users) {
        $usersvalues = array();

        foreach (array_values($users) as $userid) {
            $usersvalues[$userid] = $this->read_values_for_user($criterion, $userid);
        }

        return $usersvalues;
    }

    /**
     * Returns average values for the users
     *
     * @param array $groupusers
     * @param array $usersvalues
     * @return null
     */
    public function get_avg_values_for_users($groupusers, $usersvalues) {
        $avgvalues = null;
        $groupsize = count($groupusers);
        if ($groupsize > 0) {
            foreach ($groupusers as $groupuser) {
                $uservalues = $usersvalues[$groupuser];

                if (is_null($avgvalues)) {
                    $avgvalues = $uservalues;
                } else {
                    if (!is_null($uservalues)) {
                        foreach ($uservalues as $key => $uservalue) {
                            foreach ($avgvalues[$key]['values'] as $k => $v) {
                                $avgvalues[$key]['values'][$k] += $uservalue['values'][$k];
                            }
                        }
                    } else {
                        $groupsize = max(1, $groupsize - 1);
                    }
                }
            }
            foreach (array_keys($avgvalues) as $key) {
                foreach ($avgvalues[$key]['values'] as $k => $v) {
                    $avgvalues[$key]['values'][$k] /= $groupsize;
                }
            }
        }

        return $avgvalues;
    }
}
