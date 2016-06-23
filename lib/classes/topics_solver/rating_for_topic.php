<?php
// This file is part of PHP implementation of GroupAL
// http://sourceforge.net/projects/groupal/
//
// GroupAL is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// GroupAL implementations are distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with GroupAL. If not, see <http://www.gnu.org/licenses/>.
//
//  This code CAN be used as a code-base in Moodle
// (e.g. for moodle-mod_groupformation). Then put this code in a folder
// <moodle>\lib\groupal
/**
 * This class contains ratings which where made by participants to each topic
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
class rating_for_topic {
    private $userid;
    private $choiceid;
    private $rating;

    public function __construct($choice_id, $user_id, $rating) {
        $this->choiceid = $choice_id;
        $this->userid = $user_id;
        $this->rating = $rating;
    }

    /**
     * @param mixed $userid
     */
    public function setUserid($userid) {
        $this->userid = $userid;
    }

    /**
     * @param mixed $choiceid
     */
    public function setChoiceid($choiceid) {
        $this->choiceid = $choiceid;
    }

    /**
     * @param mixed $rating
     */
    public function setRating($rating) {
        $this->rating = $rating;
    }


    /**
     * @return mixed
     */
    public function getUserid() {
        return $this->userid;
    }

    /**
     * @return mixed
     */
    public function getChoiceid() {
        return $this->choiceid;
    }

    /**
     * @return mixed
     */
    public function getRating() {
        return $this->rating;
    }


}