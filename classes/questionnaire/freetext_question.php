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

require_once($CFG->dirroot . '/mod/groupformation/classes/questionnaire/input_question.php');

class mod_groupformation_freetext_question extends mod_groupformation_input_question {

    /**
     * Returns input of question
     *
     * @return string
     */
    public function get_input() {
        $category = $this->category;
        $questionid = $this->questionid;
        $answer = $this->answer;

        $input = "";

        $input .= '<textarea maxlength="255" class="freetext-textarea form-control" rows="5" name="';
        $input .= $category . $questionid;
        $input .= '" style="width: 100%;">';
        $input .= ((intval($answer) == -1) ? '' : $answer);
        $input .= '</textarea>';

        return $input;
    }

    /**
     * Reads answer
     *
     * @return array|null
     * @throws coding_exception
     */
    public function read_answer() {

        $answerparameter = $this->category . $this->questionid;
        $noanswerparameter = $answerparameter.'_noanswer';

        $answer = optional_param($answerparameter, null, PARAM_RAW);
        $noanswer = optional_param($noanswerparameter, null, PARAM_RAW);

        if ((isset($noanswer) && $noanswer == "on") || $answer == "") {
            return array('delete', null);
        } else if (isset($answer) && $answer != "") {
            return array('save', $answer);
        }
        return null;
    }

    /**
     * Creates random answer
     */
    public function create_random_answer() {
        return str_shuffle ('ABCDEFGH');
    }

    /**
     * Converts options if string
     *
     * @param $options
     * @return array
     */
    protected function convert_options($options) {
        return null;
    }
}

