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
 * This file contains the radio question class
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

/**
 * Prints a particular instance of groupformation questionnaire
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_radio_question {

    /**
     * Prints radio question
     *
     * @param array $q
     * @param string $category
     * @param int $questionnumber
     * @param bool $hasanswer
     */
    public function print_html($q, $category, $questionnumber, $hasanswer) {
        $question = $q[1];
        $options = $q[2];

        $answer = -1;
        if ($hasanswer) {
            $answer = $q[3];
        }

        if ($answer == -1) {
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
                    $questionnumber . '" value="' . $radiocount . '" checked="checked"/></td>';
            } else {
                echo '<td data-title="' . $option . '" class="radioleft select-area"><input type="radio" name="' .
                    $category . $questionnumber . '" value="' . $radiocount . '"/></td>';
            }
            $radiocount++;
        }
        echo '</tr>';

    }


}

