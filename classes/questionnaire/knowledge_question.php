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

require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/range_question.php');

/**
 * Class mod_groupformation_knowledge_question
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_knowledge_question extends mod_groupformation_range_question {

    /**
     * Returns answer
     *
     * @return int|mixed|null
     */
    public function get_answer() {
        $answer = $this->answer;
        return $answer;
    }


    /**
     * Print HTML of range inputs
     *
     * @param bool $highlight
     * @param bool $required
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

        echo '<input class="form-control" type="number" name="' . $category . $questionid . '" min="0" max="100"  value="'.intval($answer).'"/>';

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
     * Print HTML of range inputs
     *
     * @param bool $highlight
     * @param bool $required
     * @return string
     */


    public function get_html($highlight, $required) {

        $s = "";
        $category = $this->category;
        $questionid = $this->questionid;
        $question = $this->question;
        $options = $this->options;

        $answer = $this->get_answer();

        if ($answer != -1) {
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

        $s .= '<td colspan="100%" data-title="';
        $s .= min(array_keys($options)) . ' = ' . $options [min(array_keys($options))];
        $s .= ', ' . max(array_keys($options)) . ' = ' . $options [max(array_keys($options))];
        $s .= '" class="range">';
        $s .= '<input class="form-control" type="number" name="' . $category . $questionid . '" min="0" max="100" value="'.intval($answer).'"/>';
        $s .= '<input type="text" name="' . $category . $questionid;
        $s .= '_valid" value="' . intval($answer) . '" style="display:none;"/>';
        if ($category == 'points') {
            $s .= '<br>';
            $s .= '<label id="text' . $category . $questionid . '">';
            $s .= ((intval($answer) == -1) ? '0' : intval($answer));
            $s .= '</label>';
        }
        $s .= '</td>';
        $s .= '</tr>';

        return $s;
    }

    public function read_answer() {
        $parameter = $this->category . $this->questionid;
        $answer = optional_param($parameter, null, PARAM_RAW);
        if (isset($answer) && $answer != 0) {
            return array('save', $answer);
        } else {
            return array('delete', null);
        }
    }

}

