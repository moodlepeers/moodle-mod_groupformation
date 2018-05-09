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

class mod_groupformation_likert_question extends mod_groupformation_basic_question {

    /** @var string Type of question */
    protected $type = 'likert';

    /**
     * Print HTML of radio inputs
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

        if ($answer == false) {
            $answer = -1;
        }
        if ($answer == -1 && $highlight) {
            echo '<tr class="noAnswer">';
        } else {
            echo '<tr>';
        }
        echo '<th scope="row">' . $question . '</th>';

        if (count($options) > 2) {
            $radiocount = 1;
            foreach ($options as $option) {
                if (intval($answer) == $radiocount) {
                    echo (($option != "" && $radiocount == 1) ? '<td class="td-extra">' . $option . '</td>' : '');
                    echo '<td title="'.$option.'" data-title="';
                    echo $option;
                    echo '" class="toolt2 radioleft select-area selected_label">';
                    echo '<input type="radio" name="' . $category .
                            $questionid . '" value="' . $radiocount . '" checked="checked"/></td>';
                    echo (($option != "" && $radiocount == count($options)) ? '<td class="td-extra">' . $option . '</td>' : '');
                } else {
                    echo (($option != "" && $radiocount == 1) ? '<td class="td-extra">' . $option . '</td>' : '');
                    echo '<td title="' . $option . '" data-title="' . $option . '" class="toolt2 radioleft select-area">';
                    echo '<input type="radio" name="' . $category . $questionid . '" value="' . $radiocount . '"/>';
                    echo '</td>';
                    echo (($option != "" && $radiocount == count($options)) ? '<td class="td-extra">' . $option . '</td>' : '');
                }
                $radiocount++;
            }
        } else {
            $radiocount = 1;
            foreach ($options as $option) {
                if (intval($answer) == $radiocount) {
                    echo '<td title="'.$option.'" data-title="' . $option .
                            '" class="toolt2 radioleft select-area selected_label"><input type="radio" name="' . $category .
                            $questionid . '" value="' . $radiocount . '" checked="checked"/>'.$option.'</td>';
                } else {
                    echo '<td title="' . $option . '" data-title="' . $option . '" class="toolt2 radioleft select-area">';
                    echo '<input type="radio" name="' . $category . $questionid . '" value="' . $radiocount . '"/>';
                    echo $option;
                    echo '</td>';
                }
                $radiocount++;
            }
        }
        echo '</tr>';

    }

    /**
     * Returns HTML of radio inputs
     *
     * @param $highlight
     * @param $required
     * @return string
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
        if ($answer == -1 && $highlight) {
            $s .= '<tr class="noAnswer">';
        } else {
            $s .= '<tr>';
        }
        $s .= '<th scope="row">' . $question . '</th>';

        if (count($options) > 2) {
            $radiocount = 1;
            foreach ($options as $option) {
                if (intval($answer) == $radiocount) {
                    $s .= (($option != "" && $radiocount == 1) ? '<td class="td-extra">' . $option . '</td>' : '');
                    $s .= '<td title="'.$option.'" data-title="';
                    $s .= $option;
                    $s .= '" class="toolt2 radioleft select-area selected_label">';
                    $s .= '<input type="radio" name="' . $category .
                            $questionid . '" value="' . $radiocount . '" checked="checked"/></td>';
                    $s .= (($option != "" && $radiocount == count($options)) ? '<td class="td-extra">' . $option . '</td>' : '');
                } else {
                    $s .= (($option != "" && $radiocount == 1) ? '<td class="td-extra">' . $option . '</td>' : '');
                    $s .= '<td title="' . $option . '" data-title="' . $option . '" class="toolt2 radioleft select-area">';
                    $s .= '<input type="radio" name="' . $category . $questionid . '" value="' . $radiocount . '"/>';
                    $s .= '</td>';
                    $s .= (($option != "" && $radiocount == count($options)) ? '<td class="td-extra">' . $option . '</td>' : '');
                }
                $radiocount++;
            }
        } else {
            $radiocount = 1;
            foreach ($options as $option) {
                if (intval($answer) == $radiocount) {
                    $s .= '<td title="'.$option.'" data-title="' . $option .
                            '" class="toolt2 radioleft select-area selected_label"><input type="radio" name="' . $category .
                            $questionid . '" value="' . $radiocount . '" checked="checked"/>'.$option.'</td>';
                } else {
                    $s .= '<td title="' . $option . '" data-title="' . $option . '" class="toolt2 radioleft select-area">';
                    $s .= '<input type="radio" name="' . $category . $questionid . '" value="' . $radiocount . '"/>';
                    $s .= $option;
                    $s .= '</td>';
                }
                $radiocount++;
            }
        }
        $s .= '</tr>';
        return $s;
    }

    /**
     * @return array|null
     * @throws coding_exception
     */
    public function read_answer() {
        $parameter = $this->category . $this->questionid;
        $answer = optional_param($parameter, null, PARAM_RAW);
        if (isset($answer)) {
            return array('save', $answer);
        } else {
            return null;
        }
    }

    /**
     * Returns random answer
     *
     * @return int
     */
    public function create_random_answer() {
        return rand(1, count($this->options));
    }
}

