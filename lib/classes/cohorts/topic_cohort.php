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
 * This class contains the results of a group formation as a cohort consisting
 * of groups filled with participants
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/evaluators/groupal_evaluator.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/cohorts/cohort.php");

class lib_groupal_topic_cohort extends lib_groupal_cohort{

    /**
     * lib_groupal_topic_cohort constructor.
     * @param $numberofgroups
     * @param null $groups
     */
    public function __construct($numberofgroups, $groups = null) {
        $this->groups = array();
        if ($groups != null) {
            for ($i = 0; $i < count($groups); $i++) {
                $this->add_group($groups[$i]);
            }
        }
        $this->countofgroups = $numberofgroups;
    }

    /**
     * Adds a Group to this Cohort if not already a member.
     *
     * @param $g lib_groupal_group
     * @return boolean
     */
    public function add_group(lib_groupal_group $g) {
        if (in_array($g, $this->groups)) {
            return false;
        }

        $this->groups[] = $g;
        return true;
    }
}