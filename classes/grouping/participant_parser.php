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
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/lib/groupal/classes/criteria/specific_criterion.php');
require_once($CFG->dirroot . '/lib/groupal/classes/participant.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/criterion_calculator.php');

class mod_groupformation_participant_parser {
    private $groupformationid;
    private $user_manager;
    private $criterion_calculator;
    private $store;
    private $data;

    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->user_manager = new mod_groupformation_user_manager ($groupformationid);
        $this->criterion_calculator = new mod_groupformation_criterion_calculator ($groupformationid);
        $this->data = new mod_groupformation_data();
    }

    /**
     * Parses infos to Participants
     *
     * @param $users
     * @param $labels
     * @return array
     */
    private function parse($users, $labels) {
        $participants = array();
        foreach ($users as $user) {
            $position = 0;
            $participant = null;

            foreach ($labels as $label) {
                $value = $user->$label;
                $count = count($value);
                $homogen = $value ["homogeneous"];
                unset ($value ["homogeneous"]);
                $minVal = 0.0;
                $maxVal = 1.0;

                /*
                 * Sprache: Es soll ein 2-dim Vektor rauskommen. (HOMOGEN zu matchen)
                 * Wert1: Englisch = 1 wenn Englisch ausgewählt
                 * Wert2: Deutsch = 1 wenn Deutsch ausgewählt
                 *
                 * Wurde Deutsch bevorzugt ausgewählt, wird Englisch nicht auf 0 sondern
                 * auf 0.5 gesetzt. Ebenso bei Englisch bevorzugt wird Deutsch auf 0.5 gesetzt.
                 *
                 * Gewichtung: Bei Übergabe an GroupAL folgender PseudoCode:
                 * var numCrit = Anzahl aller Kriterien die aktuell an GroupAL übergeben
                 * werden sollen (die haben standardmäßig ein Gewicht von 1 (weight=1).
                 * var weightLanguage = (numCrit-1)/2
                 *
                 * ((Dieser Code sollte sicher nach dem Code stehen, der entscheided ob die
                 * Noten/Punkte-Angaben überhaupt als Kriterium an GroupAL übergeben werden oder
                 * ignoriert werden.))
                 *
                 */

                $weight = 1;

                if ($label == 'general') {
                    $weight = (count($labels) - 1) / 2;
                }

                $criterion = new lib_groupal_specific_criterion ($label, $value, $minVal, $maxVal, $homogen, $weight);
                if ($position == 0) {
                    $participant = new lib_groupal_participant (array(
                        $criterion), $user->id);
                } else {
                    $participant->addCriterion($criterion);
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
     * @return multitype:|multitype:multitype:
     */
    public function build_topic_participants($users) {
        if (count($users) == 0) {
            return array();
        }

        $starttime = microtime(true);

        // ----------------------------------------------------------------------------------------

        $participants = array();

        foreach ($users as $userid) {

            $criterion = $this->criterion_calculator->get_topic($userid);

            $participant = new lib_groupal_participant (array(
                $criterion), $userid);

            $participants [$userid] = $participant;
        }

        // ----------------------------------------------------------------------------------------

        $endtime = microtime(true);
        $comptime = $endtime - $starttime;

        groupformation_info(null, $this->groupformationid, 'building topic participants needed ' . $comptime . 'ms');

        return $participants;
    }

    /**
     * Builds Participants array using a parser (at the end)
     *
     * @param unknown $users
     * @return multitype:lib_groupal_participant
     */
    public function build_participants($users) {
        if (count($users) == 0) {
            return array();
        }

        $starttime = microtime(true);

        $labels = $this->store->get_label_set();
        $criteriaspecs = array();
        foreach($labels as $label) {
            $criteriaspecs[$label] = $this->data->get_criterion_specification($label);
        }

        $scenario = $this->store->get_scenario();

        $pointsP = null;


        $gradeP = null;
        $criteriaspecs = $this->criterion_calculator->filter_criteria_specs($criteriaspecs, $users);

        $array = array();
        $totalLabel = array();

        // Iterates over set of users.
        foreach ($users as $user) {

            // Pre-computes values and generates and object which can be parsed into participants with criteria.
            $object = new stdClass ();
            $object->id = $user;

            foreach ($criteriaspecs as $criterion => $spec) {

                if (in_array($scenario, $spec['scenario'])) {
                    $points = $this->criterion_calculator->get_values_for_user($criterion, $user, $spec);
                    foreach ($spec['labels'] as $label => $lspec) {
                        $value = array();
                        $vs = $points[$label]["values"];
                        foreach ($vs as $v) {
                            $value[] = $v;
                        }
                        $value ["homogeneous"] = $lspec['homogeneous'];
                        $name = $criterion . '_' . $label;
                        $object->$name = $value;
                        $totalLabel [] = $name;
                    }
                }

            }

            $array [] = $object;
        }
        $totalLabel = array_unique($totalLabel);
        $res = $this->parse($array, $totalLabel);

        $endtime = microtime(true);
        $comptime = $endtime - $starttime;
        groupformation_info(null, $this->groupformationid, 'building groupal participants needed ' . $comptime . 'ms');

        return $res;
    }

    /**
     * Generates participants without criterions
     *
     * @param array $users
     */
    public function build_empty_participants($users) {
        $starttime = microtime(true);
        $participants = array();
        foreach ($users as $userid) {
            $participant = new lib_groupal_participant (array(), $userid);
            $participants [] = $participant;
        }
        $endtime = microtime(true);
        $comptime = $endtime - $starttime;
        groupformation_info(null, $this->groupformationid, 'building empty participants needed ' . $comptime . 'ms');

        return $participants;
    }
}