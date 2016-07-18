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
    private $group_size;

    /** @var  lib_groupal_cohort This contains the final result */
    private $cohort;

    /**
     * lib_groupal_random_algorithm constructor.
     *
     * @param $participants
     * @param $group_size
     */
    public function __construct($participants, $group_size) {
        $this->group_size = $group_size;
        $this->participants = $participants;
    }

    /**
     * @return lib_groupal_cohort
     */
    public function do_one_formation() {
        lib_groupal_group::setGroupMembersMaxSize($this->group_size);

        $participants = $this->participants;
        $groupsize = $this->group_size; // 6;

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

        $complete_part = null;
        $incomplete_part = null;

        $quotient = intval($n / $g);
        $reminder = $n % $g;

        $t = $g - 1;
        $tt = $t - $reminder;

        if ($n % $g == 0) {
            $complete_part = $n;
            $incomplete_part = 0;
        } else if ($n > $g * $tt) {
            $complete_part = $n - $reminder - $g * $tt;
            $incomplete_part = $n - $complete_part;
        } else if ($n > $g) {
            $complete_part = $quotient * $g;
            $incomplete_part = $reminder;
        } else if ($g >= $n) {
            $complete_part = $n;
            $incomplete_part = 0;
        }

        // var_dump ( $complete_part, $incomplete_part );

        // NOW WE HAVE
        // $complete_part = number of participants which form complete groups
        // and
        // $incomplete_part = number of participants which form incomplete groups with equal sizes
        // example: n = 189, g = 6
        // $complete_part = 174 --> 29x 6er-Gruppe
        // $incomplete_part = 15 --> 3x 5er-Gruppe.

        $complete_participants = array();
        $incomplete_participants = array();
        if ($complete_part < 0 && $incomplete_part < 0) {
            $complete_participants = $participants;
            $incomplete_participants = array();
        } elseif ($complete_part == 0 && $incomplete_part == $n) {
            $complete_participants = array();
            $incomplete_participants = $participants;
        } elseif ($complete_part == $n && $incomplete_part == 0) {
            $complete_participants = $participants;
            $incomplete_participants = array();
        } elseif ($complete_part != 0 && $incomplete_part != 0) {
            $complete_participants = array();
            $incomplete_participants = array();

            for ($i = 0; $i < $n; $i++) {
                if ($i < $complete_part) {
                    $complete_participants [] = $participants [$i];
                } else {
                    $incomplete_participants [] = $participants [$i];
                }
            }
        }
        $groups = array();

        if (count($complete_participants) > 0) {
            $position = 0;
            $oneGroup = null;

            foreach ($complete_participants as $p) {
                if ($position == 0) {
                    $oneGroup = new lib_groupal_group ();
                }

                $oneGroup->addParticipant($p, true);

                if (($position + 1) == $groupsize) {
                    $position = 0;
                    $groups [] = $oneGroup;
                } else {
                    $position++;
                }
            }

            if ($position != 0)
                $groups [] = $oneGroup;
        }
        if (count($incomplete_participants) > 0) {
            $position = 0;
            $oneGroup = null;

            foreach ($incomplete_participants as $p) {
                if ($position == 0) {
                    $oneGroup = new lib_groupal_group ();
                }

                $oneGroup->addParticipant($p, true);

                if (($position + 1) == ($groupsize - 1)) {
                    $position = 0;
                    $groups [] = $oneGroup;
                } else {
                    $position++;
                }
            }

            if ($position != 0)
                $groups [] = $oneGroup;
        }

        $this->cohort = new lib_groupal_random_cohort (count($groups), $groups);
        $this->cohort->whichMatcherUsed = self::class;
        $this->cohort->countOfGroups = $this->cohort->countOfGroups;
        $this->cohort->cohortPerformanceIndex = null;
        return $this->cohort;
    }
}