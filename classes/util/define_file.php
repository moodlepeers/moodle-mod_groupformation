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
        3 => 'presentationgroups'
    );

    private $criterialabels = array(
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

    private $extralabels = array(
        'big5_homogen' => array('Gewissenhaftigkeit',
            'Vertraeglichkeit'),
        'big5_heterogen' => array('Extraversion',
            'Neurotizismus',
            'Offenheit'),
        'fam' => array('Herausforderung',
            'Interesse',
            'Erfolg',
            'Misserfolg'),
        'learning' => array('KE',
            'AE',
            'RB',
            'AB')
    );

    private $categorysets = array(
        '1' => array(
            'topic',
            'knowledge',
//            'general',
            'grade',
            'points',
            'team',
            'character',
            'motivation'
        ),
        '2' => array(
            'topic',
            'knowledge',
//            'general',
            'grade',
            'points',
            'team',
            'character',
            'learning'
        ),
        '3' => array(
            'topic',
//            'general'
        )
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
    public function get_extra_label($label) {
        if (array_key_exists($label, $this->extralabels)) {
            return $this->extralabels[$label];
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
}