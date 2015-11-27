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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
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

    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->user_manager = new mod_groupformation_user_manager ($groupformationid);
        $this->criterion_calculator = new mod_groupformation_criterion_calculator ($groupformationid);
    }

    /**
     * Parses infos to Participants
     *
     * @param unknown $users
     * @param unknown $labels
     * @param unknown $groupsize
     * @return multitype:lib_groupal_participant
     */
    private function parse($users, $labels) {
        $participants = array();
        foreach ($users as $user) {
            $position = 0;
            $participant = null;

            foreach ($labels as $label) {
                $value = $user->$label;
                $count = count($value);
                $homogen = $value ["homogen"];
                unset ($value ["homogen"]);
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

        $scenario = $this->store->get_scenario();

        $data = new mod_groupformation_data ();

        $labels = $this->store->get_label_set();
        $homogen = $this->store->get_homogeneous_set();


        $gradeP = -1;
        // Determines the question position with maximal variance (if grade is in questionnaire).
        if (in_array('grade', $labels)) {
            $gradeP = $this->criterion_calculator->get_grade_position($users);
        }

        $pointsP = -1;
        // Determines the question position with maximal variance (if grade is in questionnaire).
        if (in_array('points', $labels)) {
            $pointsP = $this->criterion_calculator->get_points_position($users);
        }

        $array = array();
        $totalLabel = array();
        $userPosition = 0;


        // Building the labels for kategories.

        // Iterates over set of users.
        foreach ($users as $user) {

            // Precomputes values and generates and object which can be parsed into participants with criteria.
            $object = new stdClass ();
            $object->id = $user;

            // Computes BIG5 if in labels (first part is heterogen, second part is homogen).
            $big5 = array();
            if (in_array('big5_homogen', $labels) || in_array('big5_heterogen', $labels)) {
                $big5 = $this->criterion_calculator->get_big_5($user);
            }

            $labelPosition = 0;
            foreach ($labels as $label) {

                $value = array();

                // Handles the 'general'.
                if ($label == 'general') {
                    $values = $this->criterion_calculator->get_general_values($user);
                    foreach ($values as $v) {
                        $value [] = $v;
                    }
                    $value ["homogen"] = $homogen [$label];

                    $object->$label = $value;
                    if ($userPosition == 0) {
                        $totalLabel [] = $label;
                    }
                }

                // Hanles Topics.
                if ($label == 'topic') {
                    // TODO @Rene: What ist this for?
                }

                // Handles preknowledge (colects preknowledge values in array).
                if ($label == 'knowledge_heterogen') {
                    $value = $this->criterion_calculator->knowledge_all($user);
                    $value ["homogen"] = $homogen [$label];
                    $object->$label = $value;
                    if ($userPosition == 0) {
                        $totalLabel [] = $label;
                    }
                }

                // Handles the preknowledge ( avarage values )
                if ($label == 'knowledge_homogen') {
                    $value [] = $this->criterion_calculator->knowledge_average($user);
                    $value ["homogen"] = $homogen [$label];
                    $object->$label = $value;
                    // With the first "Users" the names of the labels will be safed.
                    if ($userPosition == 0) {
                        $totalLabel [] = $label;
                    }
                }
                // TODO @Nora - Ich hab bei Bewertungsmethode nach "Just Pass" gearbeitet,
                // sprich die Fragebogenseite "Grade" wird nicht angezeigt,
                // keine Antwort vom Studenten gespeichert und somit hier keine Antwort gefunden!
                // Bitte eine Abstraktion von get_label_set und get_homogen_set in store bauen,
                // die die Fälle von grade, points, just pass, no method löst
                // Wegen der Abstraktion gehören solche Methoden meiner Meinung nach nicht in Data.

                // Handles the grades.
                if ($label == 'grade') {
                    // If a variance was calculated.
                    if ($gradeP != -1) {
                        $value [] = $this->criterion_calculator->get_grade($gradeP, $user);
                        $value ["homogen"] = $homogen [$label];
                        $object->$label = $value;
                        if ($userPosition == 0) {
                            $totalLabel [] = $label;
                        }
                    }
                }

                if ($label == 'points') {
                    // If a variance was calculated.
                    if ($pointsP != -1) {
                        $value [] = $this->criterion_calculator->get_points($pointsP, $user);
                        $value ["homogen"] = $homogen [$label];
                        $object->$label = $value;
                        if ($userPosition == 0) {
                            $totalLabel [] = $label;
                        }
                    }
                }

                // Handling the big5 heterogen.
                if ($label == 'big5_heterogen') {
                    $bigTemp = $big5 [0]; // See init of $big5.
                    $l = $data->get_extra_label($label, $scenario);
                    $p = 0;
                    $h = $homogen [$label];
                    // Create the detailed labels of differen big5's.
                    foreach ($bigTemp as $ls) {
                        $value = array();
                        $name = $label . '_' . $l [$p];
                        if ($userPosition == 0) {
                            $totalLabel [] = $name;
                        }
                        $value [] = $ls;
                        $value ["homogen"] = $h;
                        $object->$name = $value;
                        $p++;
                    }
                }

                // Handling the big5 homogen.
                if ($label == 'big5_homogen') {
                    $bigTemp = $big5 [1]; // See init von $Big5.

                    $l = $data->get_extra_label($label);
                    $p = 0;
                    $h = $homogen [$label];
                    // Create the detailed labels of differen big5's.
                    foreach ($bigTemp as $ls) {
                        $value = array();
                        $name = $label . '_' . $l [$p];
                        if ($userPosition == 0) {
                            $totalLabel [] = $name;
                        }
                        $value [] = $ls;
                        $value ["homogen"] = $h;
                        $object->$name = $value;
                        $p++;
                    }
                }

                // Handles the FAM.
                if ($label == 'fam') {
                    $famTemp = $this->criterion_calculator->get_fam($user);
                    $l = $data->get_extra_label($label);
                    $p = 0;
                    $h = $homogen [$label];
                    foreach ($l as $ls) {
                        $value = array();
                        $name = $label . '_' . $ls;
                        if ($userPosition == 0) {
                            $totalLabel [] = $name;
                        }
                        $value [] = $famTemp [$p];
                        $value ["homogen"] = $h;
                        $object->$name = $value;
                        $p++;
                    }
                }

                // Handles the learning
                if ($label == 'learning') {
                    $learnTemp = $this->criterion_calculator->get_learn($user);
                    $l = $data->get_extra_label($label);
                    $p = 0;
                    $h = $homogen [$label];
                    foreach ($l as $ls) {
                        $value = array();
                        $name = $label . '_' . $ls;
                        if ($userPosition == 0) {
                            $totalLabel [] = $name;
                        }
                        $value [] = $learnTemp [$p];
                        $value ["homogen"] = $h;
                        $object->$name = $value;
                        $p++;
                    }
                }

                // Handles teamorientation
                if ($label == 'team') {
                    $value = $this->criterion_calculator->get_team($user);
                    $value ["homogen"] = $homogen [$label];
                    $object->$label = $value;
                    if ($userPosition == 0) {
                        $totalLabel [] = $label;
                    }
                }

                $labelPosition++;
            }
            $array [] = $object;
            $userPosition++;
        }

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