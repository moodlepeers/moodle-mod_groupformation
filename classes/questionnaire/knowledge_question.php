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

require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/range_question.php');

class mod_groupformation_knowledge_question extends mod_groupformation_range_question {

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
        $answer = $this->answer;

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

        echo '<td data-title="' . min(array_keys($options)) . ' = ' . $options [min(array_keys($options))] . ', ' .
                max(array_keys($options)) . ' = ' . $options [max(array_keys($options))] . '" class="range">';
        echo '<span class="">' . min(array_keys($options)) . '</span>';

        echo '<input class="gf_range_inputs" type="range" name="' . $category . $questionid . '" min="0" max="';
        echo max(array_keys($options)) . '" value="' . intval($answer) . '" />';
        echo '<span class="">';
        echo max(array_keys($options));
        echo '</span>';
        echo '<input type="text" name="' . $category . $questionid;
        echo '_valid" value="' . intval($answer) . '" style="display:none;"/>';
        if ($category == 'points') {
            echo '<br><label id="text' . $category . $questionid . '">' . ((intval($answer) == -1) ? '0' : intval($answer)) . '</label>';
        }
        echo '</td>';
        echo '</tr>';
    }
}

