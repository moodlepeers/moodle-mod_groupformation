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
 * This class contains ratings which where made by participants to each topic
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Class mod_groupformation_rating_for_topic
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_rating_for_topic {
    /** @var int */
    private $userid;
    /** @var int */
    private $choiceid;
    /** @var int */
    private $rating;

    /**
     * mod_groupformation_rating_for_topic constructor.
     *
     * @param int $choiceid
     * @param int $userid
     * @param int $rating
     */
    public function __construct($choiceid, $userid, $rating) {
        $this->choiceid = $choiceid;
        $this->userid = $userid;
        $this->rating = $rating;
    }

    /**
     * Returns user ID
     *
     * @return mixed
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * Returns Choice ID
     *
     * @return mixed
     */
    public function get_choiceid() {
        return $this->choiceid;
    }

    /**
     * Returns Rating
     *
     * @return mixed
     */
    public function get_rating() {
        return $this->rating;
    }


}