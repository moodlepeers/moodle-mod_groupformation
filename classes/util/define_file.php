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
 * Define file for questionnaires
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // / It must be included from a Moodle page
}

class mod_groupformation_data {

    private $SCENARIO_NAMES = array(
        1 => 'projectteams',
        2 => 'homeworkgroups',
        3 => 'presentationgroups'
    );

    private $CRITERIA_LABELS = array(
        '1' => array(
            'topic' => true,
            'knowledge_heterogen' => false,
            'knowledge_homogen' => true,
            'general' => true,
            'grade' => true,
            'points' => true,
            'big5_heterogen' => false,
            'big5_homogen' => true,
            'team' => true,
            'fam' => true
        ),
        '2' => array(
            'topic' => true,
            'knowledge_heterogen' => false,
            'general' => true,
            'grade' => false,
            'points' => true,
            'big5_heterogen' => false,
            'big5_homogen' => true,
            'team' => true,
            'learning' => false
        ),
        '3' => array(
            'topic' => true,
            'general' => true
        )
    );
    private $Big5HomogenExtra_LABEL = array(
        'Gewissenhaftigkeit',
        'Vertraeglichkeit'
    );
    private $Big5HeterogenExtra_LABEL = array(
        'Extraversion',
        'Neurotizismus',
        'Offenheit'
    );
    private $FamExtra_LABEL = array(
        'Herausforderung',
        'Interesse',
        'Erfolg',
        'Misserfolg'
    );
    private $LearnExtra_LABEL = array(
        'KE',
        'AE',
        'RB',
        'AB'
    );
    private $CATEGORY_SETS = array(
        '1' => array(
            'topic',
            'knowledge',
            'general',
            'grade',
            'points',
            'team',
            'character',
            'motivation'
        ),
        '2' => array(
            'topic',
            'knowledge',
            'general',
            'grade',
            'points',
            'team',
            'character',
            'learning'
        ),
        '3' => array(
            'topic',
            'general'
        )
    );

    /**
     * Returns scenario name
     *
     * @param int $scenario
     * @return string
     */
    public function get_scenario_name($scenario) {
        return $this->SCENARIO_NAMES [$scenario];
    }

    /**
     * Returns extra labels for criteria like fam, learning, big5_xxx
     *
     * @param $label
     * @return array
     */
    public function get_extra_label($label) {
        if ($label == 'fam') {
            return $this->FamExtra_LABEL;
        }

        if ($label == 'learning') {
            return $this->LearnExtra_LABEL;
        }

        if ($label == 'big5_homogen') {
            return $this->Big5HomogenExtra_LABEL;
        }

        if ($label == 'big5_heterogen') {
            return $this->Big5HeterogenExtra_LABEL;
        }
    }

    /**
     * Returns category set
     *
     * @param int $scenario
     * @return array
     */
    public function get_category_set($scenario) {
        return $this->CATEGORY_SETS [$scenario];
    }

    /**
     * Returns label set
     *
     * @param int $scenario
     * @return string
     */
    public function get_label_set($scenario) {
        return array_keys($this->CRITERIA_LABELS [$scenario]);
    }

    /**
     * Returns homogeneous criteria set
     *
     * @param string $scenario
     * @return array
     */
    public function get_homogeneous_set($scenario) {
        return $this->CRITERIA_LABELS [$scenario];
    }
}	


