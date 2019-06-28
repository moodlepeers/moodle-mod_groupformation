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
 * Prints a particular instance of groupformation questionnaire
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/basic_question.php');

/**
 * Class mod_groupformation_multiselect_question
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_multiselect_question extends mod_groupformation_basic_question {

    /**
     * Prints HTML of a multiselect question
     *
     * @param bool $highlight
     * @param bool $required
     * @throws coding_exception
     */
    public function print_html($highlight, $required) {

        $category = $this->category;
        $questionid = $this->questionid;
        $question = $this->question;
        $options = $this->options;
        $answer = $this->answer;

        if ($answer == false) {
            $answer = -1;
        }

        if ($answer != "") {
            echo '<tr>';
            echo '<th scope="row">' . $question . '</th>';
        } else {
            if ($highlight) {
                echo '<tr class="noAnswer">';
                echo '<th scope="row">' . $question . '</th>';
            } else {
                echo '<tr>';
                echo '<th scope="row">' . $question . '</th>';
            }
        }

        $answers = array();
        if ($answer != -1) {
            $answer = substr($answer, 5);
            $answers = explode(",", $answer);
        }

        echo '<td colspan="100%" class="freetext">';
        echo '<div class="form-group">';
        echo '<select multiple class="freetext-textarea form-control" name="';
        echo $category . $questionid . '[]" style="width: 80%;">';
        foreach ($options as $key => $option) {
            echo '<option value="' . $key . '" ' . ((in_array($key, $answers)) ? 'selected' : '') . '>' . $option . '</option>';
        }
        echo '  </select>';
        echo '</div>';
        if (!$required) {
            echo '<br>';
            echo '<div class="form-check">';
            echo '    <label class="form-check-label">';
            echo '        <input class="freetext-checkbox" type="checkbox" name="'.$category.$questionid.'_noanswer"/>';
            echo get_string('freetext_noanswer', 'groupformation');
            echo '    </label>';
            echo '</div>';
        }

        echo '</td>';

        echo '</tr>';
    }

    /**
     * Returns HTML of a multiselect question
     *
     * @param bool $highlight
     * @param bool $required
     * @return string
     * @throws coding_exception
     */
    public function get_html($highlight, $required) {

        $s = "";
        $category = $this->category;
        $questionid = $this->questionid;
        $question = $this->question;
        $options = $this->options;
        $answer = $this->answer;

        if ($answer == false) {
            $answer = -1;
        }

        if ($answer != "") {
            $s .= '<tr>';
            $s .= '<th scope="row">' . $question . '</th>';
        } else {
            if ($highlight) {
                $s .= '<tr class="noAnswer">';
                $s .= '<th scope="row">' . $question . '</th>';
            } else {
                $s .= '<tr>';
                $s .= '<th scope="row">' . $question . '</th>';
            }
        }

        $answers = array();
        if ($answer != -1) {
            $answer = substr($answer, 5);
            $answers = explode(",", $answer);
        }

        $s .= '<td colspan="100%" class="freetext">';
        $s .= '<div class="form-group">';
        $s .= '<select multiple class="freetext-textarea form-control" name="';
        $s .= $category . $questionid . '[]" style="width: 80%;">';
        foreach ($options as $key => $option) {
            $s .= '<option value="' . $key . '" ' . ((in_array($key, $answers)) ? 'selected' : '') . '>' . $option . '</option>';
        }
        $s .= '  </select>';
        $s .= '</div>';
        if (!$required) {
            $s .= '<br>';
            $s .= '<div class="form-check">';
            $s .= '    <label class="form-check-label">';
            $s .= '        <input class="freetext-checkbox" type="checkbox" name="'.$category.$questionid.'_noanswer"/>';
            $s .= get_string('freetext_noanswer', 'groupformation');
            $s .= '    </label>';
            $s .= '</div>';
        }

        $s .= '</td>';

        $s .= '</tr>';

        return $s;
    }

    /**
     * Reads answer
     *
     * @return array|null
     * @throws coding_exception
     */
    public function read_answer() {

        $parameter = $this->category . $this->questionid;

        $answer = optional_param_array($parameter, array(), PARAM_RAW);
        $answer = 'list:' . implode(",", $answer);

        if (isset($answer) && $answer == 'list:') {
            return array('delete', null);
        } else if (isset($answer) && $answer != 'list:') {
            return array('save', $answer);
        }
        return null;
    }

    /**
     * Returns random answer (hardcoded)
     *
     * @return mixed|string
     */
    public function create_random_answer() {
        return "1,2";
    }
}

