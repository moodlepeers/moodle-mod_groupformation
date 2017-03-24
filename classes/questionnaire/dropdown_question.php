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
class mod_groupformation_dropdown_question {

    /**
     * Print HTML of drop-down inputs
     *
     * @param $category
     * @param $questionid
     * @param $question
     * @param $options
     * @param $answer
     * @param $highlight
     */
    public function print_html($category, $questionid, $question, $options, $answer, $highlight) {
        $questioncounter = 1;

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

        echo '<td class="center">
                <select class="form-control" name="' . $category . $questionid . '" id="' . $category . $questionid . '">';
        echo '<option value="0"> - </option>';
        foreach ($options as $option) {
            if ($answer == $questioncounter) {
                echo '<option value="' . $questioncounter . '" selected="selected">' . $option . '</option>';
            } else {

                echo '<option value="' . $questioncounter . '">' . $option . '</option>';
            }
            $questioncounter++;
        }

        echo '</select>
            </td>
        </tr>';
    }
}
