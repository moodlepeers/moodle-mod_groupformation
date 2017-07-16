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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/basic_question.php');

class mod_groupformation_range_question extends mod_groupformation_basic_question {

    public function get_answer() {
        $answer = $this->answer;
        if ($answer == false) {
            $answer = -1;
        }
        return $answer;
    }

    /**
     * Print HTML of range inputs
     *
     * @param $highlight
     * @param $required
     */
    public function print_html($highlight, $required) {

        $category = $this->category;
        $questionid = $this->questionid;
        $question = $this->question;
        $options = $this->options;

        $answer = $this->get_answer();

        if ($answer != -1) {
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

        echo '<td colspan="100%" data-title="';
        echo min(array_keys($options)) . ' = ' . $options [min(array_keys($options))];
        echo ', ' . max(array_keys($options)) . ' = ' . $options [max(array_keys($options))];
        echo '" class="range">';
        echo '<span class="">' . min(array_keys($options)) . '</span>';

        echo '<input class="gf_range_inputs" type="range" name="' . $category . $questionid . '" min="0" max="';
        echo max(array_keys($options)) . '" value="' . intval($answer) . '" />';
        echo '<span class="">';
        echo max(array_keys($options));
        echo '</span>';
        echo '<input type="text" name="' . $category . $questionid;
        echo '_valid" value="' . intval($answer) . '" style="display:none;"/>';
        if ($category == 'points') {
            echo '<br>';
            echo '<label id="text' . $category . $questionid . '">';
            echo ((intval($answer) == -1) ? '0' : intval($answer));
            echo '</label>';
        }
        echo '</td>';
        echo '</tr>';
    }

    /**
     * @return array|null
     */
    public function read_answer() {
        $answerparameter = $this->category . $this->questionid;
        $validityparameter = $answerparameter . '_valid';

        $answer = optional_param($answerparameter, null, PARAM_ALPHANUM);
        $answervalidity = optional_param($validityparameter, null, PARAM_ALPHANUM);

        if (isset ($answer) && $answervalidity == '1') {
            return array("save", $answer);
        }
    }

    /**
     * Creates random answer
     *
     * @return int
     */
    public function create_random_answer() {
        return rand(1, max(array_keys($this->options)));
    }



    /**
     * Converts options if string
     *
     * @param $options
     * @return array
     */
    protected function convert_options($options) {

        if (!is_null($options) && is_string($options)) {
            $options = array(
                    100 => get_string('excellent', 'groupformation'),
                    0 => get_string('bad', 'groupformation'));
        }

        return $options;
    }
}

