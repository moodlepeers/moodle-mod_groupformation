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

class mod_groupformation_topic_question extends mod_groupformation_basic_question {

    protected $type = 'topics';

    /**
     * Print HTML for topics table
     *
     * @param $highlight
     * @param $required
     */
    public function print_html($highlight, $required) {

        $category = $this->category;
        $questionid = $this->questionid;
        $question = $this->question;

        echo '<li id="' . $category . $questionid . '"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' .
            $question . '</li>';

    }

    /**
     * Sets answer
     *
     * @param $answer
     */
    public function set_answer($answer){
        $this->answer = $answer;
    }

    public function read_answer() {

        $parameter = $this->category . $this->questionid;
        $answer = optional_param($parameter, null, PARAM_RAW);

        if (isset($answer)) {
            return array('save', $answer);
        } else {
            return null;
        }
    }

    public function create_random_answer() {
        return null;
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


