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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/radio_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/topics_table.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/range_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/dropdown_question.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/question_table_header.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Controller for questionnaire view
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_questionnaire_controller {

    /** @var array Categories of questionnaire */
    private $categories = array();

    /** @var int This is the id of the activity */
    private $groupformationid;

    /** @var mod_groupformation_storage_manager */
    private $store;

    /** @var mod_groupformation_user_manager */
    private $usermanager;

    /** @var int id of user */
    private $userid;

    /** @var int id of course module */
    private $cmid;

    /** @var context_module Context of this activity */
    private $context;

    /** @var int current category position */
    private $currentcategoryposition = 0;

    /** @var string current category */
    private $currentcategory;

    /**
     * Constructor of questionnaire controller
     *
     * @param $groupformationid
     * @param $userid
     * @param $oldcategory
     * @param $cmid
     */
    public function __construct($groupformationid, $userid, $oldcategory, $cmid) {
        $this->groupformationid = $groupformationid;
        $this->userid = $userid;
        $this->cmid = $cmid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
        $this->categories = $this->store->get_categories();
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

        $numbers = $this->store->get_numbers($this->categories);

        foreach ($numbers as $num) {
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
        return ($this->currentcategoryposition != -1 && $this->currentcategoryposition < count($this->categories));
    }

    /**
     * Returns question in current language or possible default language
     *
     * @param int $i
     * @return stdClass
     */
    public function get_question($i) {
        $record = $this->store->get_catalog_question($i, $this->currentcategory, get_string('language',
            'groupformation'));

        if (empty ($record)) {
            if (get_string('language', 'groupformation') != 'en') {
                $record = $this->store->get_catalog_question($i, $this->currentcategory, 'en');
            } else {
                $lang = $this->store->get_possible_language($this->currentcategory);
                $record = $this->store->get_catalog_question($i, $this->currentcategory, $lang);
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

        $numbers = $this->store->get_numbers($this->categories);

        if ($this->currentcategoryposition != -1) {

            $questions = array();

            $hasanswers = $this->usermanager->has_answers($this->userid, $this->currentcategory);

            if ($this->is_knowledge() || $this->is_topics()) {
                // ---------------------------------------------------------------------------------------------------------
                $temp = $this->store->get_knowledge_or_topic_values($this->currentcategory);
                $xmlcontent = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $temp . ' </OPTIONS>';
                $values = mod_groupformation_util::xml_to_array($xmlcontent);

                $text = '';

                $type = 'type_knowledge';

                if ($this->is_topics()) {
                    $type = 'type_topics';
                }

                $options = $options = array(
                    100 => get_string('excellent', 'groupformation'), 0 => get_string('none', 'groupformation'));

                $position = 1;
                $questionsfirst = array();
                $answerposition = array();

                foreach ($values as $value) {
                    $question = array();
                    $question [] = $type;
                    $question [] = $text . $value;
                    $question [] = $options;
                    if ($hasanswers) {
                        $answer = $this->usermanager->get_single_answer($this->userid,
                            $this->currentcategory, $position);
                        if ($answer != false) {
                            $question [] = $answer;
                        } else {
                            $question [] = -1;
                        }
                        $answerposition [$answer] = $position - 1;
                        $position++;
                    }

                    $questionsfirst [] = $question;
                }

                $l = count($answerposition);

                if ($l > 0 && $this->currentcategoryposition == $this->store->get_position('topic')) {
                    for ($k = 1; $k <= $l; $k++) {
                        $h = $questionsfirst [$answerposition [$k]];
                        $h [] = $answerposition [$k];
                        $questions [] = $h;
                    }
                } else {
                    $questions = $questionsfirst;
                }
                // ---------------------------------------------------------------------------------------------------------
            } else if ($this->is_points()) {
                // ---------------------------------------------------------------------------------------------------------
                for ($i = 1; $i <= $numbers [$this->currentcategoryposition]; $i++) {
                    $record = $this->get_question($i);

                    $question = array();

                    if (count($record) == 0) {
                        echo '<div class="alert">';
                        echo 'This questionnaire site is neither available in your favorite language nor in english!';
                        echo '</div>';

                        return null;
                    } else {

                        $question [] = 'type_points';
                        $question [] = $record->question;
                        $question [] = $options = $options = array(
                            $this->store->get_max_points() => get_string('excellent', 'groupformation'),
                            0 => get_string('bad', 'groupformation'));
                        if ($hasanswers) {
                            $answer = $this->usermanager->get_single_answer($this->userid,
                                $this->currentcategory, $i);
                            if ($answer != false) {
                                $question [] = $answer;
                            } else {
                                $question [] = -1;
                            }
                        }
                    }

                    $questions [] = $question;
                }
                // ---------------------------------------------------------------------------------------------------------
            } else {
                // ---------------------------------------------------------------------------------------------------------
                for ($i = 1; $i <= $numbers [$this->currentcategoryposition]; $i++) {
                    $record = $this->get_question($i);

                    $question = $this->prepare_question($i, $record, $hasanswers);

                    $questions [] = $question;
                }
                // ---------------------------------------------------------------------------------------------------------
            }

            return $questions;
        }
    }

    /**
     * Returns question array constructed by question record
     *
     * @param $i
     * @param $record
     * @param $hasanswers
     * @return array|null
     */
    public function prepare_question($i, $record, $hasanswers) {
        $question = array();
        if (count($record) == 0) {
            echo '<div class="alert">This questionnaire site is neither available in your favorite language nor in english!</div>';

            return null;
        } else {

            $question [] = $record->type;
            $question [] = $record->question;
            $temp = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $record->options . ' </OPTIONS>';
            $question [] = mod_groupformation_util::xml_to_array($temp);

            if ($hasanswers) {
                $answer = $this->usermanager->get_single_answer($this->userid, $this->currentcategory, $i);
                if ($answer != false) {
                    $question [] = $answer;
                } else {
                    $question [] = -1;
                }
            }
        }

        return $question;
    }

    /**
     * Prints action buttons for questionaire page
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
        $prevcomplete = true;
        foreach ($categories as $category) {
            $url = new moodle_url ('questionnaire_view.php', array(
                'id' => $this->cmid, 'category' => $category));
            $positionactivecategory = $this->store->get_position($activecategory);
            $positioncategory = $this->store->get_position($category);

            $beforeactive = ($positioncategory <= $positionactivecategory);
            $class = (has_capability('mod/groupformation:editsettings',
                    $this->context) || $beforeactive || $prevcomplete) ? '' : 'no-active';
            echo '<li class="' . (($activecategory == $category) ? 'current' : 'accord_li') . '">';
            echo '<span>' . ($positioncategory + 1) . '</span><a class="' . $class . '"  href="' . $url . '">' .
                get_string('category_' . $category, 'groupformation') . '</a>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }

    /**
     * Prints final page of questionaire
     */
    public function print_final_page() {
        echo '<div class="col_m_100"><h4>' . get_string('questionnaire_no_more_questions', 'groupformation') .
            '</h></div>';
        echo '	<form action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off">';

        echo '		<input type="hidden" name="category" value="no"/>';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';

        $activityid = optional_param('id', false, PARAM_INT);
        if ($activityid) {
            echo '	<input type="hidden" name="id" value="' . $activityid . '"/>';
        } else {
            echo '	<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
        }

        if (has_capability('mod/groupformation:editsettings', $this->context)) {
            echo '<div class="alert col_m_100 questionaire_hint">' .
                get_string('questionnaire_submit_disabled_teacher', 'groupformation') . '</div>';
        }

        $url = new moodle_url ('/mod/groupformation/view.php', array(
            'id' => $this->cmid, 'do_show' => 'view'));

        echo '<div class="grid">';
        echo '	<div class="questionaire_button_text">' .
            get_string('questionnaire_press_beginning_submit', 'groupformation') . '</div>';
        echo '	<div class="col_m_100 questionaire_button_row">';
        echo '		<a href=' . $url->out() . '><span class="gf_button gf_button_pill gf_button_small">' .
            get_string('questionnaire_go_to_start', 'groupformation') . '</span></a>';
        echo '	</div>';
        echo '</div>';

        echo '</form>';
    }

    /**
     * Prints questionaire page
     */
    public function print_page() {
        if ($this->has_next()) {
            $this->currentcategory = $this->categories [$this->currentcategoryposition];
            $isteacher = has_capability('mod/groupformation:editsettings', $this->context);

            if ($isteacher) {
                echo '<div class="alert">' . get_string('questionnaire_preview', 'groupformation') . '</div>';
            }

            if ($this->usermanager->is_completed($this->userid) || !$this->store->is_questionnaire_available()) {
                echo '<div class="alert" id="commited_view">' . get_string('questionnaire_commited', 'groupformation') .
                    '</div>';
            }

            $category = $this->currentcategory;

            $percent = $this->get_percent($category);

            $this->print_navbar($category);

            $this->print_progressbar($percent);

            $questions = $this->get_next_questions();

            $this->print_questions($questions, $percent);

            // Log access to page.
            groupformation_info($this->userid, $this->groupformationid,
                '<view_questionnaire_category_' . $category . '>');
        } else {

            $this->print_final_page();

            // Log access to page.
            groupformation_info($this->userid, $this->groupformationid, '<view_questionnaire_final_page>');
        }
    }

    /**
     * Prints table with questions
     *
     * @param unknown $questions
     * @param unknown $percent
     */
    public function print_questions($questions, $percent) {
        $tabletype = $questions [0] [0];
        $headeroptarray = $questions [0] [2];
        $category = $this->currentcategory;
        $header = new mod_groupformation_question_table_header ();
        $range = new mod_groupformation_range_question ();
        $radio = new mod_groupformation_radio_question ();
        $dropdown = new mod_groupformation_dropdown_question ();
        $topics = new mod_groupformation_topics_table ();

        echo '<form style="width:100%; float:left;" action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) .
            '" method="post" autocomplete="off">';

        if (!is_null($questions) && count($questions) != 0) {

            // Here is the actual category and groupformationid is sent hidden.
            echo '<input type="hidden" name="category" value="' . $category . '"/>';

            echo '<input type="hidden" name="percent" value="' . $percent . '"/>';

            echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';

            $activityid = optional_param('id', false, PARAM_INT);

            if ($activityid) {
                echo '<input type="hidden" name="id" value="' . $activityid . '"/>';
            } else {
                echo '<input type="hidden" name="id" value="' . $this->groupformationid . '"/>';
            }

            echo ' <h4 class="view_on_mobile">' . get_string('category_' . $category, 'groupformation') . '</h4>';

            // Print the Header of a table or unordered list.
            $header->print_html($category, $tabletype, $headeroptarray);

            $hasanswer = count($questions [0]) == 4;
            $hastopicnumbers = count($questions [0]) == 5;

            // Each question with inputs.
            $i = 1;

            foreach ($questions as $q) {
                if ($q [0] == 'dropdown') {
                    $dropdown->print_html($q, $category, $i, $hasanswer);
                }

                if ($q [0] == 'radio') {
                    $radio->print_html($q, $category, $i, $hasanswer);
                }

                if ($q [0] == 'type_topics') {
                    if ($hastopicnumbers) {
                        $topics->print_html($q, $category, $q [4] + 1);
                    } else {
                        $topics->print_html($q, $category, $i);
                    }
                }

                if ($q [0] == 'type_knowledge') {
                    $range->print_html($q, $category, $i, $hasanswer);
                }

                if ($q [0] == 'type_points') {
                    $range->print_html($q, $category, $i, $hasanswer);
                }

                if ($q [0] == 'range') {
                    $range->print_html($q, $category, $i, $hasanswer);
                }
                $i++;
            }

            // Closing the table or unordered list.
            if ($tabletype == 'type_topics') {
                // Close unordered list.
                echo '</ul>';

                echo '<div id="invisible_topics_inputs">
                            </div>';
            } else {
                // Close tablebody and close table.
                echo ' </tbody>
		                  </table>';
            }

            // Reset the Question Number, so each HTML table starts with 0.
            $i = 1;
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

        echo '	<div class="questionaire_progress-bar" role="progressbar" aria-valuenow="' . $percent .
            '" aria-valuemin="0" aria-valuemax="100" style="width:' . $percent . '%"></div>';

        echo '</div>';
    }
}