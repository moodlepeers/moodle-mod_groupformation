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
class mod_groupformation_range_question {

    /**
     * Print HTML of range inputs
     *
     * @param $category
     * @param $questionid
     * @param $question
     * @param $options
     * @param $answer
     * @param $highlight
     */
    public function print_html($category, $questionid, $question, $options, $answer, $highlight) {

        if ($answer != -1) {
            echo '<tr>';
            echo '<th scope="row">' . $question . '</th>';
        } else if ($highlight) {
            echo '<tr class="noAnswer">';
            echo '<th scope="row">' . $question . '</th>';
        } else {
            echo '<tr>';
            echo '<th scope="row">' . $question . '</th>';
        }

        echo '<td data-title="' . min(array_keys($options)) . ' = ' . $options [min(array_keys($options))] . ', ' .
            max(array_keys($options)) . ' = ' . $options [max(array_keys($options))] . '" class="range">';
        echo '<span class="">' . min(array_keys($options)) . '</span>';
        // echo '<input type="range" name="' . $category . $questionid . '" class="gf_range_inputs" min="0" max="';
        // echo max(array_keys($options)) . '" value="' . $answer . '" />';

        echo '<input class="gf_range_inputs" type="range" name="' . $category . $questionid . '" min="0" max="';
        echo max(array_keys($options)) . '" value="' . $answer . '" />';
                echo '<span class="">' . max(array_keys($options)) . '</span><input type="text" name="' . $category . $questionid;
        echo '_valid" value="' . $answer . '" style="display:none;"/>';
        if ($category == 'points') {
            echo '<label id="text' . $category . $questionid . '">' . ((intval($answer)==-1)?'0':$answer) . '</label>';
        }
        echo '</td>';
        echo '</tr>';
    }
}

