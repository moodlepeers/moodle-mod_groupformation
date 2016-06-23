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
 * This class contains data for topics to build groups for.
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
class choicedata {

    private $id;
    private $maxsize;

    public function __construct($id, $size) {
        $this->id = $id;
        $this->maxsize = $size;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }


    /**
     * @param mixed $maxsize
     */
    public function setMaxsize($maxsize) {
        $this->maxsize = $maxsize;
    }


    /**
     * @return mixed
     */
    public function getMaxsize() {
        return $this->maxsize;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }


}