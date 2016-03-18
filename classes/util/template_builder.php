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
 * Class mod_groupformation_template_builder
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
 * Class mod_groupformation_template_builder
 *
 * @@package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_template_builder {

    /** @var string This is the path for the template file which is used */
    private $path;

    /** @var string This is the template name */
    private $template = 'default';

    /** @var array This array contains all assigned variables used in a template */
    private $_ = array();

    /**
     * mod_groupformation_template_builder constructor.
     */
    public function __construct() {
        global $CFG;
        $this->path = $CFG->dirroot . '/mod/groupformation/templates';
    }

    /**
     * Assigns a value to a key
     *
     * @param String $key
     * @param String $value
     */
    public function assign($key, $value) {
        $this->_[$key] = $value;
    }


    /**
     * Sets template name
     *
     * @param String $template Name des Templates.
     */
    public function set_template($template = 'default') {
        $this->template = $template;
    }

    /**
     * Loads prior defined template
     *
     * @return string
     */
    public function load_template() {
        $file = $this->path . DIRECTORY_SEPARATOR . $this->template . '.php';
        $exists = file_exists($file);

        if ($exists) {

            ob_start();

            require($file);

            $output = ob_get_contents();

            ob_end_clean();

            return $output;

        } else {
            throw new InvalidArgumentException('could not find template');
        }
    }

}