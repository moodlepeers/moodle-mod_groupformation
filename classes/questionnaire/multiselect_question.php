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

class mod_groupformation_multiselect_question {

    /**
     * Prints HTML of a multiselect question
     *
     * @param $category
     * @param $questionid
     * @param $question
     * @param $options
     * @param $answer
     * @param $highlight
     */
    public function print_html($category, $questionid, $question, $options, $answer, $highlight, $required) {

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
            $answer = substr($answer,5);
            $answers = explode(",",$answer);
        }

        echo '<td class="freetext">';
        echo '<div class="form-group">';
        echo '  <select multiple class="freetext-textarea form-control" name="' . $category . $questionid . '[]" style="width: 80%;">';
        foreach ($options as $key => $option) {
            echo '      <option value="' . $key . '" '.((in_array($key,$answers))?'selected':'').'>'.$option.'</option>';
        }
        echo '  </select>';
        echo '</div>';
        if (!$required) {
            echo '<br>';
            echo '<div class="form-check">';
            echo '    <label class="form-check-label">';
            echo '        <input class="freetext-checkbox" type="checkbox" name="'.$category.$questionid.'_noanswer"/>';
            echo get_string('freetext_noanswer','groupformation');
            echo '    </label>';
            echo '</div>';
        }

        echo '</td>';

        echo '</tr>';
    }
}

