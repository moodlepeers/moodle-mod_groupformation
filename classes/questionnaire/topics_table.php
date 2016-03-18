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
 * This file contains the topics question class
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
 * Prints a particular instance of groupformation questionnaire
 *
 * @package     mod_groupformation
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_topics_table {

    /**
     * Print HTML for topics table
     *
     * @param array $q
     * @param string $category
     * @param int $questionnumber
     */
    public function print_html($q, $category, $questionnumber) {
        $question = $q[1];

        echo '<li id="' . $category . $questionnumber . '"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' .
            $question . '</li>';

    }
}


