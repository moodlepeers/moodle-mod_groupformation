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
 * Define file for questionnaires
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

class mod_groupformation_data {

    private $scenarionames = array(
        1 => 'projectteams',
        2 => 'homeworkgroups',
        3 => 'presentationgroups',
    );

    private $criterialabels = array(
        1 => array(
            'topic' => true,
            'knowledge' => true,
            'general' => true,
            'grade' => true,
            'points' => true,
            'big5' => false,
            'team' => true,
            'fam' => true,
        ),
        2 => array(
            'topic' => true,
            'knowledge' => false,
            'general' => true,
            'grade' => false,
            'points' => false,
            'big5' => false,
            'team' => true,
            'learning' => false,
        ),
        3 => array(
            'topic' => true,
//            'general' => true,
        ),
    );

    private $extralabels = array(
        "big5" => array(
            "category" => "character",
            "scenario" => array(1, 2),
            "evaluation" => true,
            "labels" => array(
                "extraversion" => array(
                    "scenario" => array(1=>false,2 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(-1, 6),
                    "significant_id_only" => false,
                ),
                "gewissenhaftigkeit" => array(
                    "scenario" => array(1 => true, 2 => true),
                    "evaluation" => true,
                    "homogeneous" => true,
                    "questionids" => array(-3, 8),
                    "significant_id_only" => false,
                ),
                "vertraeglichkeit" => array(
                    "scenario" => array(1 => true, 2 => true),
                    "evaluation" => true,
                    "homogeneous" => true,
                    "questionids" => array(2, -7, 11),
                    "significant_id_only" => false,
                ),
                "neurotizismus" => array(
                    "scenario" => array(1 => false, 2 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(9, -4),
                    "significant_id_only" => false,
                ),
                "offenheit" => array(
                    "scenario" => array(1 => false, 2 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(10, -5),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "fam" => array(
            "category" => "motivation",
            "scenario" => array(1),
            "evaluation" => true,
            "labels" => array(
                "herausforderung" => array(
                    "scenario" => array(1 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(6, 8, 10, 15, 17),
                    "significant_id_only" => false,
                ),
                "interesse" => array(
                    "scenario" => array(1 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(1, 4, 7, 11),
                    "significant_id_only" => false,
                ),
                "erfolgswahrscheinlichkeit" => array(
                    "scenario" => array(1 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(2, 3, 13, 14),
                    "significant_id_only" => false,
                ),
                "misserfolgsbefuerchtung" => array(
                    "scenario" => array(1 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(5, 9, 12, 16, 18),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "learning" => array(
            "category" => "learning",
            "scenario" => array(2),
            "evaluation" => true,
            "labels" => array(
                "konkreteerfahrung" => array(
                    "scenario" => array(2 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(1, 5, 11, 14, 20, 22),
                    "significant_id_only" => false,
                ),
                "aktivesexperimentieren" => array(
                    "scenario" => array(2 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(2, 8, 10, 16, 17, 23),
                    "significant_id_only" => false,
                ),
                "reflektiertebeobachtung" => array(
                    "scenario" => array(2 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(3, 6, 9, 13, 19, 21),
                    "significant_id_only" => false,
                ),
                "abstraktebegriffsbildung" => array(
                    "scenario" => array(2 => false),
                    "evaluation" => true,
                    "homogeneous" => false,
                    "questionids" => array(4, 7, 12, 15, 18, 24),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "general" => array(
            "category" => "general",
            "scenario" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "language" => array(
                    "scenario" => array(1 => true, 2 => true),
                    "evaluation" => false,
                    "homogeneous" => true,
                    "questionids" => array(1),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "grade" => array(
            "category" => "grade",
            "scenario" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "one" => array(
                    "scenario" => array(1 => true, 2 => false),
                    "evaluation" => false,
                    "homogeneous" => null,
                    "questionids" => array(1, 2, 3),
                    "significant_id_only" => true,
                ),
            ),
        ),
        "points" => array(
            "category" => "points",
            "scenario" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "one" => array(
                    "scenario" => array(1 => true, 2 => false),
                    "evaluation" => false,
                    "homogeneous" => true,
                    "questionids" => array(1, 2, 3),
                    "significant_id_only" => true,
                ),
            ),
        ),
        "team" => array(
            "category" => "team",
            "scenario" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "one" => array(
                    "scenario" => array(1 => true, 2 => true),
                    "evaluation" => false,
                    "homogeneous" => true,
                    "questionids" => array(
                        1, 2, 3, 4, 5, 6, 7, 8, 9,
                        10, 11, 12, 13, 14, 15, 16, 17, 18,
                        19, 20, 21, 22, 23, 24, 25, 26, 27),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "knowledge" => array(
            "category" => "knowledge",
            "scenario" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "one" => array(
                    "scenario" => array(1 => true),
                    "evaluation" => false,
                    "homogeneous" => true,
                    "questionids" => null,
                    "significant_id_only" => false,
                ),
                "two" => array(
                    "scenario" => array(1 => false, 2 => false),
                    "evaluation" => false,
                    "homogeneous" => false,
                    "questionids" => null,
                    "significant_id_only" => false,
                ),
            ),
        ),
    );

    private $categorysets = array(
        '1' => array(
            'topic',
            'knowledge',
            'general',
            'grade',
            'points',
            'team',
            'character',
            'motivation',
        ),
        '2' => array(
            'topic',
            'knowledge',
            'general',
            'grade',
            'points',
            'team',
            'character',
            'learning',
        ),
        '3' => array(
            'topic',
            'general',
        ),
    );

    /**
     * Returns scenario name
     *
     * @param int $scenario
     * @return string
     */
    public function get_scenario_name($scenario) {
        return $this->scenarionames [$scenario];
    }

    /**
     * Returns extra labels for criteria like fam, learning, big5_xxx
     *
     * @param $label
     * @return array
     */
    public function get_extra_labels($label) {
        if (array_key_exists($label, $this->extralabels)) {
            return array_keys($this->extralabels[$label]);
        } else {
            return array();
        }
    }

    /**
     * Returns category set
     *
     * @param int $scenario
     * @return array
     */
    public function get_category_set($scenario) {
        return $this->categorysets [$scenario];
    }

    /**
     * Returns label set
     *
     * @param int $scenario
     * @return string
     */
    public function get_label_set($scenario) {
        return array_keys($this->criterialabels [$scenario]);
    }

    /**
     * Returns homogeneous criteria set
     *
     * @param string $scenario
     * @return array
     */
    public function get_homogeneous_set($scenario) {
        return $this->criterialabels [$scenario];
    }

    /**
     * Returns critetion specification
     *
     * @param $name
     * @return mixed
     */
    public function get_criterion_specification($name = null) {
        if (is_null($name)) {
            return $this->extralabels;
        }
        if (array_key_exists($name, $this->extralabels)) {
            return $this->extralabels[$name];
        } else {
            return null;
        }
    }
}