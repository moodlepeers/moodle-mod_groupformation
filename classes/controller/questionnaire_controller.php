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
 * Question controller
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/radio_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/topics_table.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/range_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/dropdown_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/question_table.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

class mod_groupformation_questionnaire_controller {
    private $status;
    private $numbers = array();
    private $categories = array();
    private $groupformationid;
    private $store;
    private $data;
    private $usermanager;
    private $scenario;
    private $lang;
    private $userid;
    private $cmid;
    private $context;
    private $currentcategoryposition = 0;
    private $numberofcategory;
    private $hasanswer;
    private $currentcategory;
    private $highlight = false;

    /**
     * mod_groupformation_questionnaire_controller constructor.
     * @param $groupformationid
     * @param $lang
     * @param $userid
     * @param $oldcategory
     * @param $cmid
     */
    public function __construct($groupformationid, $lang, $userid, $oldcategory, $cmid) {
        $this->groupformationid = $groupformationid;
        $this->lang = $lang;
        $this->userid = $userid;
        $this->cmid = $cmid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->data = new mod_groupformation_data();
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
        $this->scenario = $this->store->get_scenario();
        $this->categories = $this->store->get_categories();
        $this->numberofcategory = count($this->categories);
        $this->init($userid);
        $this->set_internal_number($oldcategory);
        $this->context = context_module::instance($this->cmid);
    }

    /**
     * Triggers going a category page back
     */
    public function go_back() {
        $this->currentcategoryposition = max($this->currentcategoryposition - 2, 0);
    }

    /**
     * Regulates not going on and highlighting missing answers
     */
    public function not_go_on() {
        $this->currentcategoryposition = max($this->currentcategoryposition - 1, 0);
        $this->highlight = true;
    }

    /**
     * Returns percent of progress in questionnaire
     *
     * @param string $category
     * @return number
     */
    public function get_percent($category = null) {
        if (!is_null($category)) {
            $categories = $this->store->get_categories();
            $pos = array_search($category, $categories);
            return 100.0 * ((1.0 * $pos) / count($categories));
        }

        $total = 0;
        $sub = 0;
        $temp = 0;

        foreach ($this->numbers as $num) {
            if ($num != 0) {
                $total++;
                if ($temp < $this->currentcategoryposition) {
                    $sub++;
                }
            }
            $temp++;
        }

        return ($sub / $total) * 100;
    }

    /**
     * Handles initialization
     *
     * @param unknown $userid
     */
    private function init($userid) {
        if (!$this->store->catalog_table_not_set()) {
            $this->numbers = $this->store->get_numbers($this->categories);
        }
        $this->status = $this->usermanager->get_answering_status($userid);
    }

    /**
     * Sets internal page number
     *
     * @param unknown $category
     */
    private function set_internal_number($category) {
        if ($category != "") {
            $this->currentcategoryposition = $this->store->get_position($category);
            $this->currentcategoryposition++;
        }
    }

    /**
     * Returns whether there is a next category or not
     *
     * @return boolean
     */
    public function has_next() {
        return ($this->currentcategoryposition != -1 && $this->currentcategoryposition < $this->numberofcategory);
    }

    /**
     * Returns question in current language or possible default language
     *
     * @param int $i
     * @param int $version
     * @return stdClass
     */
    public function get_question($i, $version) {
        $record = $this->store->get_catalog_question($i, $this->currentcategory, $this->lang, $version);

        if (empty ($record)) {
            if ($this->lang != 'en') {
                $record = $this->store->get_catalog_question($i, $this->currentcategory, $this->lang, $version);
            } else {
                $lang = $this->store->get_possible_language($this->currentcategory);
                $record = $this->store->get_catalog_question($i, $this->currentcategory, $lang, $version);
            }
        }

        return $record;
    }

    /**
     * Returns whether current category is 'topic' or not
     *
     * @return boolean
     */
    public function is_topics() {
        return $this->currentcategoryposition == $this->store->get_position('topic');
    }

    /**
     * Returns whether current category is 'knowledge' or not
     *
     * @return boolean
     */
    public function is_knowledge() {
        return $this->currentcategoryposition == $this->store->get_position('knowledge');
    }

    /**
     * Returns whether current category is 'points' or not
     *
     * @return boolean
     */
    public function is_points() {
        return $this->currentcategoryposition == $this->store->get_position('points');
    }

    /**
     * Returns questions
     *
     * @return array
     */
    public function get_next_questions() {
        $lang = $this->lang;
        if ($this->currentcategoryposition != -1) {

            $questions = array();

            $this->hasanswer = $this->usermanager->has_answers($this->userid, $this->currentcategory);

            if ($this->is_knowledge() || $this->is_topics()) {
                $temp = $this->store->get_knowledge_or_topic_values($this->currentcategory);
                $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
                $values = mod_groupformation_util::xml_to_array($xmlcontent);
                $text = '';

                $type = 'range';

                if ($this->is_topics()) {
                    $type = 'topics';
                }

                $options = $options = array(
                    100 => get_string('excellent', 'groupformation'), 0 => get_string('none', 'groupformation'));

                $position = 1;
                $questionsfirst = array();
                $answerposition = array();

                $i = 1;
                foreach ($values as $value) {
                    $question = array();
                    $question ['type'] = $type;
                    $question ['question'] = $text . $value;
                    $question ['options'] = $options;

                    if ($this->hasanswer) {
                        $answer = $this->usermanager->get_single_answer(
                            $this->userid, $this->currentcategory, $position);
                        if ($answer != false) {
                            $question ['answer'] = $answer;
                        } else {
                            $question ['answer'] = -1;
                        }
                        $answerposition[$answer] = $position - 1;
                        $position++;
                    } else {
                        $question ['answer'] = -1;
                    }
                    $question ['questionid'] = $i;

                    $questionsfirst[] = $question;
                    $i++;
                }

                $l = count($answerposition);

                if ($l > 0 && $this->currentcategoryposition == $this->store->get_position('topic')) {
                    // topics are rated by users as: the topmost=most wanted=rating value highest number
                    // therefore here we sort them accordingly top downwards by rating
                    for ($k = $l; $k >= 1; $k--) {
                        $h = $questionsfirst[$answerposition[$k]];
                        $h['answer'] = $answerposition[$k];
                        $questions[] = $h;
                    }
                } else {
                    $questions = $questionsfirst;
                }
            } else if ($this->is_points()) {
                $records = $this->store->get_questions_randomized_for_user($this->currentcategory, $this->userid, $lang);

                foreach ($records as $record) {

                    $question = array();

                    if (count($record) == 0) {
                        echo '<div class="alert">';
                        echo 'This questionnaire site is neither available in your favorite language nor in english!';
                        echo '</div>';

                        return null;
                    } else {

                        $question ['type'] = 'range';
                        $question ['question'] = $record->question;
                        $question ['questionid'] = $record->questionid;
                        $question ['options'] = array(
                            $this->store->get_max_points() => get_string('excellent', 'groupformation'),
                            0 => get_string('bad', 'groupformation'));

                        $answer = $this->usermanager->get_single_answer($this->userid, $this->currentcategory, $record->questionid);
                        if ($answer != false) {
                            $question ['answer'] = $answer;
                        } else {
                            $question ['answer'] = -1;
                        }
                    }

                    $questions [] = $question;
                }

            } else {
                $records = $this->store->get_questions_randomized_for_user($this->currentcategory, $this->userid, $lang);

                foreach ($records as $record) {
                    $question = $this->prepare_question($record);

                    $questions [] = $question;
                }

            }

            return $questions;
        }
    }

    /**
     * Returns question array constructed by question record
     *
     *
     * @param unknown $record
     * @return multitype:number NULL multitype:string Ambigous <number, mixed>
     */
    public function prepare_question($record) {
        $question = array();
        if (count($record) == 0) {
            echo '<div class="alert">This questionnaire site is neither available in your favorite language nor in english!</div>';

            return null;
        } else {

            $question ['type'] = $record->type;
            $question ['question'] = $record->question;
            $question ['questionid'] = $record->questionid;

            $temp = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $record->options . ' </OPTIONS>';
            $question ['options'] = mod_groupformation_util::xml_to_array($temp);

            $answer = $this->usermanager->get_single_answer($this->userid, $this->currentcategory, $record->questionid);

            if ($answer != false) {
                $question ['answer'] = intval($answer);
            } else {
                $question ['answer'] = -1;
            }

        }
        return $question;
    }

    /**
     * Prints action buttons for questionnaire page
     */
    public function print_action_buttons() {
        echo '<div class="grid">
                        <div class="col_m_100 questionaire_button_row">
                            <button type="submit" name="direction" value="0" class="gf_button gf_button_pill gf_button_small">' .
            get_string('previous') . '</button>
                            <button type="submit" name="direction" value="1" class="gf_button gf_button_pill gf_button_small">' .
            get_string('next') . '</button>
                        </div>
                        </div>';
    }

    /**
     * Prints navigation bar
     *
     * @param string $activecategory
     */
    public function print_navbar($activecategory = null) {
        $tempcategories = $this->store->get_categories();
        $categories = array();
        foreach ($tempcategories as $category) {
            if ($this->store->get_number($category) > 0) {
                $categories [] = $category;
            }
        }
        echo '<div id="questionaire_navbar">';
        echo '<ul id="accordion">';
        $prevcomplete = !$this->data->all_answers_required();
        foreach ($categories as $category) {
            $url = new moodle_url ('questionnaire_view.php', array(
                'id' => $this->cmid, 'category' => $category));
            $positionactivecategory = $this->store->get_position($activecategory);
            $positioncategory = $this->store->get_position($category);

            $beforeactive = ($positioncategory <= $positionactivecategory);
            $class = 'no-active';
            if (has_capability('mod/groupformation:editsettings', $this->context) || $beforeactive || $prevcomplete) {
                $class = '';
            }
            echo '<li class="' . (($activecategory == $category) ? 'current' : 'accord_li') . '">';
            echo '<span>' . ($positioncategory + 1) . '</span><a class="' . $class . '"  href="' . $url . '">' .
                get_string('category_' . $category, 'groupformation') . '</a>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    /**
     * Prints final page of questionnaire
     */
    public function print_final_page() {
        echo '<div class="col_m_100"><h4>' . get_string('questionnaire_no_more_questions', 'groupformation') .
            '</h></div>';
        echo '    <form action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off" class="groupformation_questionnaire">';

        echo '        <input type="hidden" name="category" value="no"/>';
        echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

        $activityid = optional_param('id', $this->groupformationid, PARAM_INT);
        echo '    <input type="hidden" name="id" value="' . $activityid . '"/>';

        if (has_capability('mod/groupformation:editsettings', $this->context)) {
            echo '<div class="alert col_m_100 questionaire_hint">' .
                get_string('questionnaire_submit_disabled_teacher', 'groupformation') . '</div>';
        }

        $url = new moodle_url ('/mod/groupformation/view.php', array(
            'id' => $this->cmid, 'do_show' => 'view'));

        echo '<div class="grid">';
        echo '    <div class="questionaire_button_text">' .
            get_string('questionnaire_press_beginning_submit', 'groupformation') . '</div>';
        echo '    <div class="col_m_100 questionaire_button_row">';
        echo '        <a href=' . $url->out() . '><span class="gf_button gf_button_pill gf_button_small">' .
            get_string('questionnaire_go_to_start', 'groupformation') . '</span></a>';
        echo '    </div>';
        echo '</div>';

        echo '</form>';
    }

    /**
     * Prints questionnaire page
     */
    public function print_page() {
        if ($this->has_next()) {
            $this->currentcategory = $this->categories[$this->currentcategoryposition];
            $isteacher = has_capability('mod/groupformation:editsettings', $this->context);

            if ($isteacher) {
                echo '<div class="alert">' . get_string('questionnaire_preview', 'groupformation') . '</div>';
            }

            if ($this->usermanager->is_completed($this->userid) || !$this->store->is_questionnaire_available()) {
                echo '<div class="alert" id="commited_view">' . get_string('questionnaire_committed', 'groupformation') .
                    '</div>';
            }

            $category = $this->currentcategory;

            $percent = $this->get_percent($category);

            if ($this->store->ask_for_participant_code() && !$isteacher) {
                $this->print_participant_code();
            }

            $this->print_navbar($category);

            $this->print_progressbar($percent);

            $questions = $this->get_next_questions();

            $this->print_questions($questions, $percent);

            // Log access to page.
            groupformation_info($this->userid, $this->groupformationid,
                '<view_questionnaire_category_' . $category . '>');
        } else {

            $this->print_final_page();

            if ($this->usermanager->has_answered_everything($this->userid)) {
                $this->usermanager->set_evaluation_values($this->userid);
            }

            // Log access to page.
            groupformation_info($this->userid, $this->groupformationid, '<view_questionnaire_final_page>');
        }
    }

    /**
     * Prints table with questions
     *
     * @param array $questions
     * @param unknown $percent
     */
    public function print_questions($questions, $percent) {

        echo '<form style="width:100%; float:left;" action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) .
            '" method="post" autocomplete="off" class="groupformation_questionnaire">';

        if (!is_null($questions) && count($questions) != 0) {

            $tabletype = $questions [0]['type'];
            $headeroptarray = $questions [0] ['options'];
            $category = $this->currentcategory;
            $table = new mod_groupformation_question_table ();
            $topics = new mod_groupformation_topics_table ();

            // Here is the actual category and groupformationid is sent hidden.
            echo '<input type="hidden" name="category" value="' . $category . '"/>';

            echo '<input type="hidden" name="percent" value="' . $percent . '"/>';

            echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

            $activityid = optional_param('id', $this->groupformationid, PARAM_INT);

            echo '<input type="hidden" name="id" value="' . $activityid . '"/>';

            echo ' <h4 class="view_on_mobile">' . get_string('category_' . $category, 'groupformation') . '</h4>';

            // Print the header of a table or unordered list.
            $table->print_header($category, $tabletype, $headeroptarray);

            foreach ($questions as $q) {
                $questionid = $q['questionid'];
                $question = $q ['question'];
                $options = $q ['options'];
                $answer = $q ['answer'];
                $type = $q['type'];

                if ($type == 'topics') {
                    $topics->print_html($category, $questionid, $question);
                } else {
                    $name = 'mod_groupformation_' . $type . '_question';
                    $object = new $name();
                    $object->print_html($category, $questionid, $question, $options, $answer, $this->highlight);
                }
            }

            // Print the footer of a table or unordered list.
            $table->print_footer($tabletype);
        }

        $this->print_action_buttons();

        echo '</form>';
    }

    /**
     * Prints progress bar
     *
     * @param unknown $percent
     */
    public function print_progressbar($percent) {
        echo '<div class="progress">';

        echo '    <div class="questionaire_progress-bar" role="progressbar" aria-valuenow="' . $percent .
            '" aria-valuemin="0" aria-valuemax="100" style="width:' . $percent . '%"></div>';

        echo '</div>';
    }

    /**
     * Prints participant code for user
     */
    public function print_participant_code() {
        echo '<div class="participantcode">';

        echo get_string('participant_code_footer', 'groupformation');
        echo ': ';
        echo $this->usermanager->get_participant_code($this->userid);

        echo '</div>';
    }

    /**
     * Saves answers for user
     *
     * @param $category
     * @return bool
     */
    public function save_answers($category) {
        $go = true;
        $number = $this->store->get_number($category);
        $lang = $this->lang;
        if ($category == 'topic') {
            for ($i = 1; $i <= $number; $i++) {
                $temp = $category . $i;
                $paratemp = optional_param($temp, null, PARAM_ALPHANUM);
                if (isset ($paratemp)) {
                    $this->usermanager->save_answer($this->userid, $category, $paratemp, $i);
                }
            }
        } else if ($category == 'knowledge') {
            for ($i = 1; $i <= $number; $i++) {
                $tempvalidaterangevalue = $category . $i . '_valid';
                $temp = $category . $i;
                $paravalidaterange = optional_param($tempvalidaterangevalue, null, PARAM_ALPHANUM);
                $paratemp = optional_param($temp, null, PARAM_ALPHANUM);

                if (isset ($paratemp) && $paravalidaterange == '1') {
                    $this->usermanager->save_answer($this->userid, $category, $paratemp, $i);
                }
            }
        } else {
            $questions = $this->store->get_questions_randomized_for_user($category, $this->userid, $lang);

            foreach ($questions as $question) {
                $temp = $category . $question->questionid;
                $paratemp = optional_param($temp, null, PARAM_ALPHANUM);
                if (isset($paratemp)) {
                    $this->usermanager->save_answer($this->userid, $category, $paratemp, $question->questionid);
                }
            }
        }
        if ($this->data->all_answers_required() && $this->usermanager->get_number_of_answers(
                $this->userid, $category) != $number) {
            $go = false;
        }
        return $go;
    }
}