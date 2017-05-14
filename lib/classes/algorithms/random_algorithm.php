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
 * This class contains an implementation of a random group formation algorithm
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/ialgorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/group.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/cohorts/random_cohort.php');

class mod_groupformation_random_algorithm implements mod_groupformation_ialgorithm {

    /** @var  array This array contains all participants which need to be matched */
    private $participants;

    /** @var  int This is the maximum group size of a computed group */
    private $groupsize;

    /** @var  mod_groupformation_cohort This contains the final result */
    private $cohort;

    /**
     * mod_groupformation_random_algorithm constructor.
     *
     * @param $participants
     * @param $groupsize
     */
    public function __construct($participants, $groupsize) {
        $this->groupsize = $groupsize;
        $this->participants = $participants;
    }

    /**
     * @return mod_groupformation_cohort
     */
    public function do_one_formation() {
        mod_groupformation_group::set_group_members_max_size($this->groupsize);

        $participants = $this->participants;
        $groupsize = $this->groupsize;

        shuffle($participants);

        $n = count($participants);
        $g = $groupsize;

        $completepart = null;
        $incompletepart = null;

        $quotient = intval($n / $g);
        $reminder = $n % $g;

        $t = $g - 1;
        $tt = $t - $reminder;

        if ($n % $g == 0) {
            $completepart = $n;
            $incompletepart = 0;
        } else if ($n > $g * $tt) {
            $completepart = $n - $reminder - $g * $tt;
            $incompletepart = $n - $completepart;
        } else if ($n > $g) {
            $completepart = $quotient * $g;
            $incompletepart = $reminder;
        } else if ($g >= $n) {
            $completepart = $n;
            $incompletepart = 0;
        }

        $completeusers = array();
        $incompleteusers = array();
        if ($completepart < 0 && $incompletepart < 0) {
            $completeusers = $participants;
            $incompleteusers = array();
        } else if ($completepart == 0 && $incompletepart == $n) {
            $completeusers = array();
            $incompleteusers = $participants;
        } else if ($completepart == $n && $incompletepart == 0) {
            $completeusers = $participants;
            $incompleteusers = array();
        } else if ($completepart != 0 && $incompletepart != 0) {
            $completeusers = array();
            $incompleteusers = array();

            for ($i = 0; $i < $n; $i++) {
                if ($i < $completepart) {
                    $completeusers [] = $participants [$i];
                } else {
                    $incompleteusers [] = $participants [$i];
                }
            }
        }
        $groups = array();

        if (count($completeusers) > 0) {
            $position = 0;
            $onegroup = null;

            foreach ($completeusers as $p) {
                if ($position == 0) {
                    $onegroup = new mod_groupformation_group ();
                }

                $onegroup->add_participant($p, true);

                if (($position + 1) == $groupsize) {
                    $position = 0;
                    $groups [] = $onegroup;
                } else {
                    $position++;
                }
            }

            if ($position != 0) {
                $groups [] = $onegroup;
            }
        }
        if (count($incompleteusers) > 0) {
            $position = 0;
            $onegroup = null;

            foreach ($incompleteusers as $p) {
                if ($position == 0) {
                    $onegroup = new mod_groupformation_group ();
                }

                $onegroup->add_participant($p, true);

                if (($position + 1) == ($groupsize - 1)) {
                    $position = 0;
                    $groups [] = $onegroup;
                } else {
                    $position++;
                }
            }

            if ($position != 0) {
                $groups [] = $onegroup;
            }
        }

        $this->cohort = new mod_groupformation_random_cohort (count($groups), $groups);
        $this->cohort->whichmatcherused = get_class($this);
        $this->cohort->countofgroups = $this->cohort->countofgroups;
        $this->cohort->cpi = null;
        return $this->cohort;
    }
}