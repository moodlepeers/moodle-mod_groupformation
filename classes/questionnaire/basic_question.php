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

/**
 * Class mod_groupformation_basic_question
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class mod_groupformation_basic_question {

    /** @var string Category of question */
    protected $category;

    /** @var int ID of question */
    protected $questionid;

    /** @var string Text of question  */
    protected $question;
    /** @var array Options of question */
    protected $options;

    /** @var mixed Answer of question */
    protected $answer;

    /** @var string Type of question */
    protected $type = 'basic';

    /**
     * mod_groupformation_dropdown_question constructor.
     *
     * @param string $category
     * @param int $questionid
     * @param null $question
     * @param null $options
     * @param null $answer
     */
    public function __construct($category, $questionid, $question = null, $options = null, $answer = null) {
        $this->category = $category;
        $this->questionid = $questionid;
        $this->question = $question;
        $this->options = $this->convert_options($options);
        $this->answer = $answer;
    }

    /**
     * Print HTML of drop-down inputs
     *
     * @param bool $highlight
     * @param bool $required
     */
    public abstract function print_html($highlight, $required);

    /**
     * Returns HTML of drop-down inputs
     *
     * @param bool $highlight
     * @param bool $required
     */
    public abstract function get_html($highlight, $required);

    /**
     * Returns type
     *
     * @return string
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Returns options
     *
     * @return mixed
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * Reads answer from POST request
     * @return mixed
     */
    public abstract function read_answer();

    /**
     * Creates random answer
     *
     * @return mixed
     */
    public abstract function create_random_answer();

    /**
     * Converts options if string
     *
     * @param string $options
     * @return array
     */
    protected function convert_options($options) {
        if (!is_null($options) && is_string($options)) {
            $temp = '<?xml version="1.0" encoding="UTF-8" ?> <OPTIONS> ' . $options . ' </OPTIONS>';
            $options = mod_groupformation_util::xml_to_array($temp);
        }

        return $options;
    }
}
