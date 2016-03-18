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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

/**
 * This is the data class with all useful definitions and config values
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_data {

    /** @var array scenarios for activity */
    private $scenarios = array(
        1 => 'projectteams',
        2 => 'homeworkgroups',
        3 => 'presentationgroups',
    );

    /** @var array criteria for groupal etc. */
    private $criteria = array(
        "big5" => array(
            "category" => "character",
            "scenarios" => array(1, 2),
            "evaluation" => true,
            "labels" => array(
                "extraversion" => array(
                    "scenarios" => array(1 => false, 2 => false),
                    "evaluation" => true,
                    "questionids" => array(-1, 6),
                    "significant_id_only" => false,
                ),
                "gewissenhaftigkeit" => array(
                    "scenarios" => array(1 => true, 2 => true),
                    "evaluation" => true,
                    "questionids" => array(-3, 8),
                    "significant_id_only" => false,
                ),
                "vertraeglichkeit" => array(
                    "scenarios" => array(1 => true, 2 => true),
                    "evaluation" => true,
                    "questionids" => array(2, -7, 11),
                    "significant_id_only" => false,
                ),
                "neurotizismus" => array(
                    "scenarios" => array(1 => false, 2 => false),
                    "evaluation" => true,
                    "questionids" => array(9, -4),
                    "significant_id_only" => false,
                ),
                "offenheit" => array(
                    "scenarios" => array(1 => false, 2 => false),
                    "evaluation" => true,
                    "questionids" => array(10, -5),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "fam" => array(
            "category" => "motivation",
            "scenarios" => array(1),
            "evaluation" => true,
            "labels" => array(
                "herausforderung" => array(
                    "scenarios" => array(1 => false),
                    "evaluation" => true,
                    "questionids" => array(6, 8, 10, 15, 17),
                    "significant_id_only" => false,
                ),
                "interesse" => array(
                    "scenarios" => array(1 => false),
                    "evaluation" => true,
                    "questionids" => array(1, 4, 7, 11),
                    "significant_id_only" => false,
                ),
                "erfolgswahrscheinlichkeit" => array(
                    "scenarios" => array(1 => false),
                    "evaluation" => true,
                    "questionids" => array(2, 3, 13, 14),
                    "significant_id_only" => false,
                ),
                "misserfolgsbefuerchtung" => array(
                    "scenarios" => array(1 => false),
                    "evaluation" => true,
                    "questionids" => array(5, 9, 12, 16, 18),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "learning" => array(
            "category" => "learning",
            "scenarios" => array(2),
            "evaluation" => false,
            "labels" => array(
                "konkreteerfahrung" => array(
                    "scenarios" => array(2 => false),
                    "evaluation" => false,
                    "questionids" => array(1, 5, 11, 14, 20, 22),
                    "significant_id_only" => false,
                ),
                "aktivesexperimentieren" => array(
                    "scenarios" => array(2 => false),
                    "evaluation" => false,
                    "questionids" => array(2, 8, 10, 16, 17, 23),
                    "significant_id_only" => false,
                ),
                "reflektiertebeobachtung" => array(
                    "scenarios" => array(2 => false),
                    "evaluation" => false,
                    "questionids" => array(3, 6, 9, 13, 19, 21),
                    "significant_id_only" => false,
                ),
                "abstraktebegriffsbildung" => array(
                    "scenarios" => array(2 => false),
                    "evaluation" => false,
                    "questionids" => array(4, 7, 12, 15, 18, 24),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "general" => array(
            "category" => "general",
            "scenarios" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "language" => array(
                    "scenarios" => array(1 => true, 2 => true),
                    "evaluation" => false,
                    "questionids" => array(1),
                    "significant_id_only" => false,
                ),
            ),
        ),
        "grade" => array(
            "category" => "grade",
            "scenarios" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "one" => array(
                    "scenarios" => array(1 => true, 2 => false),
                    "evaluation" => false,
                    "questionids" => array(1, 2, 3),
                    "significant_id_only" => true,
                ),
            ),
        ),
        "points" => array(
            "category" => "points",
            "scenarios" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "one" => array(
                    "scenarios" => array(1 => true, 2 => false),
                    "evaluation" => false,
                    "questionids" => array(1, 2, 3),
                    "significant_id_only" => true,
                ),
            ),
        ),
        "team" => array(
            "category" => "team",
            "scenarios" => array(1, 2),
            "evaluation" => true,
            "labels" => array(
                "one" => array(
                    "scenarios" => array(1 => true, 2 => true),
                    "evaluation" => true,
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
            "scenarios" => array(1, 2),
            "evaluation" => false,
            "labels" => array(
                "one" => array(
                    "scenarios" => array(1 => true),
                    "evaluation" => false,
                    "questionids" => null,
                    "significant_id_only" => false,
                ),
                "two" => array(
                    "scenarios" => array(1 => false, 2 => false),
                    "evaluation" => false,
                    "questionids" => null,
                    "significant_id_only" => false,
                ),
            ),
        ),
    );

    /** @var array category sets for scenarios */
    private $categorysets = array(
        '1' => array(
            'general',
            'grade',
            'points',
            'team',
            'character',
            'motivation',
        ),
        '2' => array(
            'general',
            'grade',
            'points',
            'team',
            'character',
            'learning',
        ),
        '3' => array(
            'topic',
        ),
    );

    /**
     * Returns scenario name
     *
     * @param int $scenario
     * @return string
     */
    public function get_scenario_name($scenario) {
        return $this->scenarios [$scenario];
    }

    /**
     * Returns extra labels for criteria like fam, learning, big5_xxx
     *
     * @param string $label
     * @return array
     */
    public function get_extra_labels($label) {
        if (array_key_exists($label, $this->criteria)) {
            return array_keys($this->criteria[$label]);
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
        $labels = array();
        foreach ($this->criteria as $label => $criterion) {
            $scenarios = $criterion["scenarios"];
            if (in_array($scenario, $scenarios)) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * Returns critetion specification
     *
     * @param string $name
     * @return mixed
     */
    public function get_criterion_specification($name = null) {
        if (is_null($name)) {
            return $this->criteria;
        }
        if (array_key_exists($name, $this->criteria)) {
            return $this->criteria[$name];
        } else {
            return null;
        }
    }
}
