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
 * This class contains an implementation of a random group formation algorithm
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/ialgorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/group.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/cohorts/random_cohort.php');

class lib_groupal_random_algorithm implements lib_groupal_ialgorithm {

    /** @var  array This array contains all participants which need to be matched */
    private $participants;

    /** @var  int This is the maximum group size of a computed group */
    private $groupsize;

    /** @var  lib_groupal_cohort This contains the final result */
    private $cohort;

    /**
     * lib_groupal_random_algorithm constructor.
     *
     * @param $participants
     * @param $groupsize
     */
    public function __construct($participants, $groupsize) {
        $this->groupsize = $groupsize;
        $this->participants = $participants;
    }

    /**
     * @return lib_groupal_cohort
     */
    public function do_one_formation() {
        lib_groupal_group::setGroupMembersMaxSize($this->groupsize);

        $participants = $this->participants;
        $groupsize = $this->groupsize; // 6;

        // Komplexere Variante mit seeds.

        // // hier später die groupformationid
        // $seed = 2;
        // $randomArray = array ();
        // for($i = 0; $i < count ( $this->participants ); $i ++) {
        // $randomArray [$i] = mt_srand ( $seed );
        // }

        // // sortiere
        // asort ( $randomArray );

        // $participants = array ();
        // foreach ( $randomArray as $key => $value ) {
        // $participants [] = $this->participants [$key];
        // }

        shuffle($participants);

        // MORE EQUAL DIVIDED GROUPS WITH THIS!
        // Example: 189 is not divisible by 6,
        // but 186 is: 186/6=31
        // the 32nd group would have 3 of 6 members
        // better would be to have multiple 5er-Gruppen

        $n = count($participants);
        $g = $groupsize; // 6;

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

        // NOW WE HAVE
        // $completepart = number of participants which form complete groups
        // and
        // $incompletepart = number of participants which form incomplete groups with equal sizes
        // example: n = 189, g = 6
        // $completepart = 174 --> 29x 6er-Gruppe
        // $incompletepart = 15 --> 3x 5er-Gruppe.

        $completeusers = array();
        $incompleteusers = array();
        if ($completepart < 0 && $incompletepart < 0) {
            $completeusers = $participants;
            $incompleteusers = array();
        } elseif ($completepart == 0 && $incompletepart == $n) {
            $completeusers = array();
            $incompleteusers = $participants;
        } elseif ($completepart == $n && $incompletepart == 0) {
            $completeusers = $participants;
            $incompleteusers = array();
        } elseif ($completepart != 0 && $incompletepart != 0) {
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
                    $onegroup = new lib_groupal_group ();
                }

                $onegroup->addParticipant($p, true);

                if (($position + 1) == $groupsize) {
                    $position = 0;
                    $groups [] = $onegroup;
                } else {
                    $position++;
                }
            }

            if ($position != 0)
                $groups [] = $onegroup;
        }
        if (count($incompleteusers) > 0) {
            $position = 0;
            $onegroup = null;

            foreach ($incompleteusers as $p) {
                if ($position == 0) {
                    $onegroup = new lib_groupal_group ();
                }

                $onegroup->addParticipant($p, true);

                if (($position + 1) == ($groupsize - 1)) {
                    $position = 0;
                    $groups [] = $onegroup;
                } else {
                    $position++;
                }
            }

            if ($position != 0)
                $groups [] = $onegroup;
        }

        $this->cohort = new lib_groupal_random_cohort (count($groups), $groups);
        $this->cohort->whichMatcherUsed = get_class($this);
        $this->cohort->countOfGroups = $this->cohort->countOfGroups;
        $this->cohort->cpi = null;
        return $this->cohort;
    }
}