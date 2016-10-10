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
class mod_groupformation_radio_question {

    /**
     * Print HTML of radio inputs
     *
     * @param $category
     * @param $questionid
     * @param $question
     * @param $options
     * @param $answer
     * @param $highlight
     */
    public function print_html($category, $questionid, $question, $options, $answer, $highlight) {
        if ($answer == -1 && $highlight) {
            echo '<tr class="noAnswer">';
        } else {
            echo '<tr>';
        }
        echo '<th scope="row">' . $question . '</th>';

        $radiocount = 1;
        foreach ($options as $option) {
            if ($answer == $radiocount) {
                echo '<td data-title="' . $option .
                    '" class="radioleft select-area selected_label"><input type="radio" name="' . $category .
                    $questionid . '" value="' . $radiocount . '" checked="checked"/></td>';
            } else {
                echo '<td data-title="' . $option . '" class="radioleft select-area"><input type="radio" name="' .
                    $category . $questionid . '" value="' . $radiocount . '"/></td>';
            }
            $radiocount++;
        }
        echo '</tr>';

    }


}

