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
 * Internal library of functions for module groupdistribution.
 *
 * Contains the algorithm for the group distribution and some helper functions
 * that wrap useful SQL querys.
 *
 * @package    mod_ratingallocate
 * @subpackage mod_ratingallocate
 * @copyright  2014 M Schulze, C Usener
 * @copyright  based on code by Stefan Koegel copyright (C) 2013 Stefan Koegel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Class allocation: user asociated with group found by algo
 */
class mod_groupformation_topic_allocation {

    public $choiceid;
    public $userid;
    public $groupformationid;

    public function __construct($choiceid, $userid) {
        $this->choiceid = $choiceid;
        $this->userid = $userid;
    }

}


/**
 * Represents an Edge in the graph to have fixed fields instead of array-fields
 */
class mod_groupformation_topic_edge {
    /** @var from int */
    public $from;
    /** @var to int */
    public $to;
    /** @var weight int Cost for this edge (rating of user) */
    public $weight;
    /** @var space int (places left for choices) */
    public $space;

    public function __construct($from, $to, $weight, $space = 0) {
        $this->from = $from;
        $this->to = $to;
        $this->weight = $weight;
        $this->space = $space;
    }

}

/**
 * Template Class for distribution algorithms
 */
class mod_groupformation_topic_distributor {

    /** @var $graph Flow-Graph built */
    protected $graph;


    /**
     * Compute the 'satisfaction' functions that is to be maximized by adding the
     * ratings users gave to their allocated choices
     * @param array $ratings
     * @param array $distribution
     * @return integer
     */
    public static function compute_target_function($ratings, $distribution) {
        $functionvalue = 0;
        foreach ($distribution as $choiceid => $choice) {
            // Choice is now an array of userids.
            foreach ($choice as $userid) {
                // Find out the right rating.
                foreach ($ratings as $rating) {
                    if ($rating->userid == $userid && $rating->choiceid == $choiceid) {
                        $functionvalue += $rating->rating;
                        continue; // Exit the search-loop an continue.
                    }
                }
            }
        }
        return $functionvalue;
    }

    /**
     * Entry-point for the \ratingallocate object to call a topics_solver
     * @param \ratingallocate $ratingallocate
     */
    public function distribute_users($ratings, $topics, $participantscount) {

        // Get data for the algo.
        $choicerecords = $topics;

        // Randomize the order of the enrties to prevent advantages for early entry.
        shuffle($ratings);

        $distributions = $this->compute_distribution($choicerecords, $ratings, $participantscount);

        return $distributions;
    }

    /**
     * Extracts a distribution/allocation from the graph.
     *
     * @param $touserid a map mapping from indexes in the graph to userids
     * @param $tochoiceid a map mapping from indexes in the graph to choiceids
     * @return an array of the form array(groupid => array(userid, ...), ...)
     */
    protected function extract_allocation($touserid, $tochoiceid) {
        $distribution = array();
        foreach ($tochoiceid as $index => $groupid) {
            $group = $this->graph[$index];
            $distribution[$groupid] = array();
            foreach ($group as $assignment) {
                $user = intval($assignment->to);
                if (array_key_exists($user, $touserid)) {
                    $distribution[$groupid][] = $touserid[$user];
                }
            }
        }
        return $distribution;
    }

    /**
     * Setup conversions between ids of users and choices to their node-ids in the graph
     * @param type $usercount
     * @param type $ratings
     * @return array($fromuserid, $touserid, $fromchoiceid, $tochoiceid);
     */
    public static function setup_id_conversions($usercount, $ratings) {
        // These tables convert userids to their index in the graph
        // The range is [1..$usercount].
        $fromuserid = array();
        $touserid = array();
        // These tables convert choiceids to their index in the graph.
        // The range is [$usercount + 1 .. $usercount + $choicecount].
        $fromchoiceid = array();
        $tochoiceid = array();

        // User counter.
        $ui = 1;
        // Group counter.
        $gi = $usercount + 1;

        // Fill the conversion tables for group and user ids.
        foreach ($ratings as $rating) {
            if (!array_key_exists($rating->get_userid(), $fromuserid)) {
                $fromuserid[$rating->get_userid()] = $ui;
                $touserid[$ui] = $rating->get_userid();
                $ui++;
            }
            if (!array_key_exists($rating->get_choiceid(), $fromchoiceid)) {
                $fromchoiceid[$rating->get_choiceid()] = $gi;
                $tochoiceid[$gi] = $rating->get_choiceid();
                $gi++;
            }
        }

        return array($fromuserid, $touserid, $fromchoiceid, $tochoiceid);
    }

    /**
     * Sets up $this->graph
     * @param type $choicecount
     * @param type $usercount
     * @param type $fromuserid
     * @param type $fromchoiceid
     * @param type $ratings
     * @param type $choicedata
     * @param type $source
     * @param type $sink
     */
    protected function setup_graph($choicecount, $usercount, $fromuserid, $fromchoiceid, $ratings, $choicedata,
                                   $source, $sink, $weightmult = 1) {
        // Construct the datastructures for the algorithm
        // A directed weighted bipartite graph.
        // A source is connected to all users with unit cost.
        // The users are connected to their choices with cost equal to their rating.
        // The choices are connected to a sink with 0 cost.
        $this->graph = array();
        // Add source, sink and number of nodes to the graph.
        $this->graph[$source] = array();
        $this->graph[$sink] = array();
        $this->graph['count'] = $choicecount + $usercount + 2;

        // Add users and choices to the graph and connect them to the source and sink.
        foreach ($fromuserid as $id => $user) {
            $this->graph[$user] = array();
            $this->graph[$source][] = new mod_groupformation_topic_edge($source, $user, 0);
        }

        foreach ($fromchoiceid as $id => $choice) {
            $this->graph[$choice] = array();
            $this->graph[$choice][] = new mod_groupformation_topic_edge($choice, $sink, 0, $choicedata[$id]->get_max_size());
        }

        // Add the edges representing the ratings to the graph.
        foreach ($ratings as $id => $rating) {
            $user = $fromuserid[$rating->get_userid()];
            $choice = $fromchoiceid[$rating->get_choiceid()];
            $weight = $rating->get_rating();
            if ($weight > 0) {
                $this->graph[$user][] = new mod_groupformation_topic_edge($user, $choice, $weightmult * $weight);
            }
        }
    }

    /**
     * Augments the flow in the network, i.e. augments the overall 'satisfaction'
     * by distributing users to choices
     * Reverses all edges along $path in $graph
     * @param type $path path from t to s
     */
    protected function augment_flow($path) {
        if (is_null($path) or count($path) < 2) {
            print_error('invalid_path', 'ratingallocate');
        }

        // Walk along the path, from s to t.
        for ($i = count($path) - 1; $i > 0; $i--) {
            $from = $path[$i];
            $to = $path[$i - 1];
            $edge = null;
            $foundedgeid = -1;
            // Find the edge.
            foreach ($this->graph[$from] as $index => &$edge) {
                if ($edge->to == $to) {
                    $foundedgeid = $index;
                    break;
                }
            }
            // The second to last node in a path has to be a choice-node.
            // Reduce its space by one, because one user just got distributed into it.
            if ($i == 1 and $edge->space > 1) {
                $edge->space--;
            } else {
                // Remove the edge.
                array_splice($this->graph[$from], $foundedgeid, 1);
                // Add a new edge in the opposite direction whose weight has an opposite sign.
                $this->graph[$to][] = new mod_groupformation_topic_edge($to, $from, -1 * $edge->weight);
            }
        }
    }

}
