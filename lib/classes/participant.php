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
 * This class contains an implementation of an ListItem as an Participant
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");

class lib_groupal_participant {

    /** @var int This is the static ID count which assigns ids if they are not given */
    private static $IDcount = 0;

    /** @var array This array contains all criteria of a participant */
    public $criteria = array();

    /** @var int This will contain the group id of the group of the participant */
    public $actualGroup = null;

    /** @var int This is the participants id */
    public $ID = 0; // int

    /**
     * lib_groupal_participant constructor.
     * @param $criteria  array of criteria (will be copied/iterated)
     * @param $id to be set as id of participant (otherwise generated automatically), but pay attention not to
     * mix given and non-given IDs!
     */
    public function __construct($criteria = array(), $id = null) {

        if (is_null($id)) {
            $this->ID = static::$IDcount;
            static::$IDcount++;
        } else {
            $this->ID = $id;
        }

        foreach ($criteria as $criterion) {
            $this->addCriterion($criterion);
        }
    }

    /**
     * Returns the criteria of the participant
     *
     * @return array
     */
    public function getCriteria() {
        return $this->criteria;
    }

    /**
     * Add criterion without checking for duplicates
     *
     * @param lib_groupal_criterion $c
     */
    public function addCriterion(lib_groupal_criterion $c) {
        $this->criteria[] = $c;
    }

    /**
     * Returns the id of the participant
     *
     * @return int|null
     */
    public function getID() {
        return $this->ID;
    }
}