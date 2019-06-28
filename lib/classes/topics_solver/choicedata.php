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
 * This class contains data for topics to build groups for.
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class mod_groupformation_choicedata
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_choicedata {

    /** @var int */
    private $id;

    /** @var number */
    private $maxsize;

    /**
     * mod_groupformation_choicedata constructor.
     *
     * @param int $id
     * @param number $size
     */
    public function __construct($id, $size) {
        $this->id = $id;
        $this->maxsize = $size;
    }

    /**
     * Returns max size
     *
     * @return mixed
     */
    public function get_max_size() {
        return $this->maxsize;
    }

    /**
     * Returns ID
     *
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }


}