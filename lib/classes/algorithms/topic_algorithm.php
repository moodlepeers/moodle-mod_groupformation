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
 * This class contains an implementation of a topic-based group formation algorithm
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/algorithms/ialgorithm.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/cohorts/topic_cohort.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/topics_solver/choicedata.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/topics_solver/rating_for_topic.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/topics_solver/edmonds-karp.php");


class lib_groupal_topic_algorithm implements lib_groupal_ialgorithm {

    /** @var array This array contains the ratings of all participants */
    public $ratings = array();

    /** @var array This array contains all topics */
    public $topics = array();

    /** @var array This array contains all participants which need to be matched */
    public $participants = array();

    /** @var int This is the number of participants */
    public $participants_numb;

    /** @var lib_groupal_cohort This is the resulting cohort */
    public $cohort;

    /**
     * lib_groupal_topic_algorithm constructor.
     * @param $_topics
     * @param $_participants
     */
    public function __construct($_topics, $_participants) {

        foreach ($_participants as $p) {
            $this->participants[$p->ID] = clone($p);
        }

        $this->participants_numb = count($_participants);

        foreach ($_topics as $key => $value) {
            $this->topics[] = new choicedata($key, $value);
        }

        $this->ratings = $this->get_ratings_from_participants();

    }

    /**
     * The main method to call for getting a formation "run" (this takes a while)
     * Uses the global set matcher to assign evry not yet matched participant to a group
     *
     * @return lib_groupal_cohort
     */
    public function do_one_formation() {
        // Run algorithm.
        $distributor = new groupformation_solver_edmonds_karp();
        $results = $distributor->distribute_users($this->ratings, $this->topics, $this->participants_numb);
        $groups = array();
        foreach (array_values($results) as $participants_ids) {
            $group = new lib_groupal_group();
            foreach ($participants_ids as $id) {
                $p = $this->participants[$id];
                $group->addParticipant($p, true);
            }
            $groups[] = $group;
        }

        $this->cohort = new lib_groupal_topic_cohort(count($groups), $groups);
        $this->cohort->whichMatcherUsed = get_class($this);
        return $this->cohort;
    }

    /**
     * Returns all ratings for active choices
     *
     * @return array
     */
    private function get_ratings_from_participants() {

        $ratings_array = array();

        // TODO Participant with just topics as criterions? can the values of criterion be empty?
        foreach (array_values($this->participants) as $user) {
            $current_user_id = $user->ID;
            foreach ($user->criteria as $cr) {
                if ($cr->getName() == 'topic') {
                    $ratings = $cr->getValues();
                    foreach ($ratings as $choice_id => $rating) {
                        $ratings_array[] = new rating_for_topic($choice_id, $current_user_id, $rating);
                    }
                }
            }
        }

        return $ratings_array;  // Array with all ratings.

    }


}