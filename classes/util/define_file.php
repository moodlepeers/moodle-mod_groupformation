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
 * Class mod_groupformation_data
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_data {

    /** @var array Containing all criteria specifications */
    private static $criteria = array(
            "big5" => array(
                    "category" => "character",
                    "scenarios" => array(1, 2),
                    "evaluation" => true,
                    "labels" => array(
                            // TODO discuss with Henrik which questions to use how.
                            "extraversion" => array(
                                    "scenarios" => array(1 => false, 2 => false),  // False = heterogeneous, True = homogeneous.
                                    "evaluation" => true,  // Use for displaying it to user (to compare to group and course).
                                    "questionids" => array(-1, 6), // Inverse questions = negative.
                                    "significant_id_only" => false,
                                // True = only use the one questionid with most significant differences between users.
                                    "cutoffs" => array(0.313169217, 0.776242547),
                            ),
                            "conscientiousness" => array(
                                    "scenarios" => array(1 => true, 2 => true),
                                    "evaluation" => true,
                                    "questionids" => array(-3, 8),
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.456596974, 0.831246163),
                            ),
                            "agreeableness" => array(
                                    "scenarios" => array(1 => true, 2 => true),
                                    "evaluation" => true,
                                    "questionids" => array(2, -7, 11),
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.492136484, 0.799889659),
                            ),
                            "neuroticism" => array(
                                    "scenarios" => array(1 => false, 2 => false),
                                    "evaluation" => true,
                                    "questionids" => array(-4, 9),
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.195135503, 0.602511556),
                            ),
                            "openness" => array(
                                    "scenarios" => array(1 => false, 2 => false),
                                    "evaluation" => true,
                                    "questionids" => array(-5, 10),
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.348454964, 0.829192095),
                            ),
                    ),
            ),
            "fam" => array(
                    "category" => "motivation",
                    "scenarios" => array(1),
                    "evaluation" => true,
                    "labels" => array(
                            "challenge" => array(
                                    // TODO: support scenariocriteriontypes and allow "none" to not use it for grouping.
                                   // Added for displaying feedback to user; no true/false asignment to prevent algorithmic usage.
                                    "scenarios" => array(1 => false),
                                    "evaluation" => true,
                                    "questionids" => array(6, 8, 10, 15, 17),
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.518934813, 0.830866774),
                            ),
                            "interest" => array(
                                    "scenarios" => array(1 => false),
                                    "evaluation" => true,
                                    "questionids" => array(1, 7, 11, 17),
                                // Replaced 4 with 17 (because scient. work question does not suit so well generally).
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.439861739, 0.751249372),
                            ),
                            "successprobability" => array(
                                    "scenarios" => array(1 => false),
                                    "evaluation" => true, // Not used for matching, only displayed.
                                    "questionids" => array(2, 3, 13, 14),
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.314297404, 0.511297834),
                            ),
                            "lackofconfidence" => array(
                                    "scenarios" => array(1 => false),
                                    "evaluation" => true,
                                    "questionids" => array(5, 9, 12, 16, 18),
                                    "significant_id_only" => false,
                                    "cutoffs" => array(0.186185044, 0.601275274),
                            ),
                    ),
            ),
            "team" => array(
                    "category" => "team",
                    "scenarios" => array(1, 2),
                    "evaluation" => false,
                    "labels" => array(
                            "teamorientation" => array(
                                    "scenarios" => array(1 => true, 2 => true),
                                    "evaluation" => false,
                                    "questionids" => array(14, 15, 16),
                                    "significant_id_only" => false,
                                    "cutoffs" => null,
                            ),
                    ),
            ),
            "learning" => array(
                    "category" => "learning",
                    "scenarios" => array(),  // Empty = not used in any scenario.
                    "evaluation" => false,
                    "labels" => array(
                            "konkreteerfahrung" => array(
                                    "scenarios" => array(),
                                    "evaluation" => false,
                                    "questionids" => array(1, 5, 11, 14, 20, 22),
                                    "significant_id_only" => false,
                                    "cutoffs" => null,
                            ),
                            "aktivesexperimentieren" => array(
                                    "scenarios" => array(),
                                    "evaluation" => false,
                                    "questionids" => array(2, 8, 10, 16, 17, 23),
                                    "significant_id_only" => false,
                                    "cutoffs" => null,
                            ),
                            "reflektiertebeobachtung" => array(
                                    "scenarios" => array(),
                                    "evaluation" => false,
                                    "questionids" => array(3, 6, 9, 13, 19, 21),
                                    "significant_id_only" => false,
                                    "cutoffs" => null,
                            ),
                            "abstraktebegriffsbildung" => array(
                                    "scenarios" => array(),
                                    "evaluation" => false,
                                    "questionids" => array(4, 7, 12, 15, 18, 24),
                                    "significant_id_only" => false,
                                    "cutoffs" => null,
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
                                    "cutoffs" => null,
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
                                    "cutoffs" => null,
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
                                    "cutoffs" => null,
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
                                    "separate_dimensions" => false,  // For all questions the mean is used.
                                    "cutoffs" => null,
                            ),
                            "two" => array(
                                    "scenarios" => array(1 => false, 2 => false),
                                    "evaluation" => false,
                                    "questionids" => null,
                                    "significant_id_only" => false,
                                    "separate_dimensions" => true,  // All questions remain seperate.
                                    "cutoffs" => null,
                            ),
                    ),
            ),
    );

    /** @var bool Special mode booleans (can be ignored in normal use cases). */
    private static $mathprepcoursemode = true;

    /**
     * Returns whether this instance is running in math prep course mode;
     * default should be false, since math prep course mode is only for research
     *
     * @return bool
     */
    public static function is_math_prep_course_mode() {
        return self::$mathprepcoursemode;
    }

    /**
     * Returns label set
     *
     * @param int $scenario
     * @return string
     */
    public static function get_label_set($scenario) {
        $labels = array();
        foreach (self::$criteria as $label => $criterion) {
            $scenarios = $criterion["scenarios"];
            if (in_array($scenario, $scenarios)) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * Returns criterion specification
     *
     * @param string $name
     * @return mixed
     */
    public static function get_criterion_specification($name = null) {
        if (is_null($name)) {
            return self::$criteria;
        }
        if (array_key_exists($name, self::$criteria)) {
            return self::$criteria[$name];
        } else {
            return null;
        }
    }

    /**
     * Returns whether a participant code is required or not
     *
     * @return bool
     * @throws dml_exception
     */
    public static function ask_for_participant_code() {
        $configvalue = get_config('groupformation', 'participant_code');
        if (!is_null($configvalue)) {
            return $configvalue;
        }
        return false;
    }

    /**
     * Returns whether import export is enabled or not
     *
     * @return bool
     * @throws dml_exception
     */
    public static function import_export_enabled() {
        $configvalue = get_config('groupformation', 'import_export');
        if (!is_null($configvalue)) {
            return $configvalue;
        }
        return true;
    }
}
