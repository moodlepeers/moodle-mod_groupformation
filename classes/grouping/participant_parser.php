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
 * Participant parser for grouping
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/lib/classes/criteria/specific_criterion.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/participant.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/criterion_calculator.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

/**
 * Class mod_groupformation_participant_parser
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_participant_parser {

    /** @var int ID of module instance */
    public $groupformationid;

    /** @var mod_groupformation_criterion_calculator The calculator for criteria */
    private $criterioncalculator;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    /**
     * mod_groupformation_participant_parser constructor.
     *
     * @param int $groupformationid
     * @throws dml_exception
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager($this->groupformationid);
        $this->criterioncalculator = new mod_groupformation_criterion_calculator ($groupformationid);
    }

    /**
     * Parses infos to Participants
     *
     * @param array $users
     * @param array $labels
     * @param array $weights
     *
     * @return array
     * @throws Exception
     */
    private function parse($users, $labels, $weights = null) {

        $participants = array();
        foreach ($users as $user) {
            $position = 0;
            $participant = null;

            foreach ($labels as $label) {
                $value = $user->$label;
                $homogen = $value["homogeneous"];
                unset ($value["homogeneous"]);
                $value = array_map('abs', $value);
                $minval = 0.0;
                $maxval = 1.0;

                if (!is_null($weights)) {
                    $weight = $weights[$label];
                } else {
                    $weight = 1;

                    if ($label == 'general') {
                        $weight = (count($labels) - 1) / 2;
                    }
                }

                $criterion = new mod_groupformation_specific_criterion ($label, $value, $minval, $maxval, $homogen, $weight);
                if ($position == 0) {
                    $participant = new mod_groupformation_participant (array(
                        $criterion), $user->id);
                } else {
                    $participant->add_criterion($criterion);
                }
                $position++;
            }
            $participants [] = $participant;
        }
        return $participants;
    }

    /**
     * Builds all participants wrt topic choices
     *
     * @param array $users
     * @return array
     * @throws dml_exception
     */
    public function build_topic_participants($users) {
        if (count($users) == 0) {
            return array();
        }

        $starttime = microtime(true);

        // ----------------------------------------------------------------------------------------

        $participants = array();

        foreach ($users as $userid) {

            $criterion = $this->criterioncalculator->get_topic($userid);
            $participant = new mod_groupformation_participant (array(
                $criterion), $userid);

            $participants [$userid] = $participant;
        }

        // ----------------------------------------------------------------------------------------

        $endtime = microtime(true);
        $comptime = $endtime - $starttime;

        return $participants;
    }

    /**
     * Builds Participants array using a parser (at the end)
     *
     * @param array $users
     * @param array $specs
     * @param null $weights
     * @return array
     * @throws dml_exception
     */
    public function build_participants($users, $specs = null, $weights = null) {
        if (count($users) == 0) {
            return array();
        }

        $scenario = $this->store->get_scenario(true);

        $starttime = microtime(true);

        $criteriaspecs = array();

        if (is_null($specs)) {
            $labels = $this->store->get_label_set();
            foreach ($labels as $label) {
                $criteriaspecs[$label] = mod_groupformation_data::get_criterion_specification($label);
            }
        } else {
            $criteriaspecs = $specs;
        }

        // var_dump(array_keys($criteriaspecs));
        $criteriaspecs = $this->criterioncalculator->filter_criteria_specs($criteriaspecs, $users);

        $array = array();
        $totallabel = array();

        // Iterates over set of users.
        foreach ($users as $user) {

            // Pre-computes values and generates and object which can be parsed into participants with criteria.
            $object = new stdClass ();
            $object->id = $user;
            foreach ($criteriaspecs as $criterion => $spec) {

                if (in_array($scenario, $spec['scenarios'])) {
                    $points = array();
                    $answeredeverything = $this->usermanager->has_answered_everything($user);
                    if ($answeredeverything) {
                        $points = $this->criterioncalculator->read_values_for_user($criterion, $user, $spec);
                    }

                    // var_dump($points);
                    foreach ($spec['labels'] as $label => $lspec) {
                        $value = array();
                        $vs = $points[$label]["values"];
                        foreach ($vs as $v) {
                            $value[] = $v;
                        }
                        $value ["homogeneous"] = $lspec['scenarios'][$scenario];
                        $name = $criterion . '_' . $label;
                        $object->$name = $value;
                        $totallabel [] = $name;
                    }
                }
            }

            $array [] = $object;
        }
        $totallabel = array_unique($totallabel);
        $res = $this->parse($array, $totallabel, $weights);
        $endtime = microtime(true);
        $comptime = $endtime - $starttime;
        return $res;
    }

    /**
     * Generates participants without criterions
     *
     * @param array $users
     * @return array
     */
    public function build_empty_participants($users) {
        $starttime = microtime(true);
        $participants = array();
        foreach ($users as $userid) {
            $participant = new mod_groupformation_participant (array(), $userid);
            $participants [] = $participant;
        }
        $endtime = microtime(true);
        $comptime = $endtime - $starttime;

        return $participants;
    }
}