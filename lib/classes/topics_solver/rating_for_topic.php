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
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
class mod_groupformation_rating_for_topic {
    private $userid;
    private $choiceid;
    private $rating;

    public function __construct($choiceid, $userid, $rating) {
        $this->choiceid = $choiceid;
        $this->userid = $userid;
        $this->rating = $rating;
    }

    /**
     * @return mixed
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * @return mixed
     */
    public function get_choiceid() {
        return $this->choiceid;
    }

    /**
     * @return mixed
     */
    public function get_rating() {
        return $this->rating;
    }


}