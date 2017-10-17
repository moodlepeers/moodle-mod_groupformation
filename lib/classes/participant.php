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
 * This class contains an implementation of an ListItem as an Participant
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");

class mod_groupformation_participant {

    /** @var int This is the static ID count which assigns ids if they are not given */
    private static $idcount = 0;

    /** @var array This array contains all criteria of a participant */
    public $criteria = array();

    /** @var int This will contain the group id of the group of the participant */
    public $actualgroup = null;

    /** @var int This is the participants id */
    public $id = 0;

    /**
     * mod_groupformation_participant constructor.
     * @param $criteria  array of criteria (will be copied/iterated)
     * @param $id to be set as id of participant (otherwise generated automatically), but pay attention not to
     * mix given and non-given IDs!
     */
    public function __construct($criteria = array(), $id = null) {

        if (is_null($id)) {
            $this->id = static::$idcount;
            static::$idcount++;
        } else {
            $this->id = $id;
        }

        foreach ($criteria as $criterion) {
            $this->add_criterion($criterion);
        }
    }

    /**
     * Returns the criteria of the participant
     *
     * @return array
     */
    public function get_criteria() {
        return $this->criteria;
    }

    /**
     * Add criterion without checking for duplicates
     *
     * @param mod_groupformation_criterion $c
     */
    public function add_criterion(mod_groupformation_criterion $c) {
        $this->criteria[] = $c;
    }

    /**
     * Returns the id of the participant
     *
     * @return int|null
     */
    public function get_id() {
        return $this->id;
    }
}