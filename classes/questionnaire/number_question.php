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

require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/input_question.php');

/**
 * Class mod_groupformation_number_question
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_number_question extends mod_groupformation_input_question {

    /**
     * Returns input of question
     *
     * @return string
     */
    public function get_input() {
        $category = $this->category;
        $questionid = $this->questionid;
        $options = $this->options;
        $answer = $this->answer;

        $input = "";

        $input .= '<input style="height:35px" class="freetext-textarea form-control" type="number" min="';
        $input .= $options[0] . '" max="' . $options[1] . '" value="' . ((is_number($answer)) ? intval($answer) : "") . '" name="';
        $input .= $category;
        $input .= $questionid;
        $input .= '">';

        return $input;
    }

    /**
     * Returns random answer
     *
     * @return int
     */
    public function create_random_answer() {
        $options = $this->options;
        return rand($options[0], $options[1]);
    }
}

