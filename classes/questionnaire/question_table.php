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
 * Prints a particular instance of groupformation questionnaire
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

class mod_groupformation_question_table {

    /** @var string Category of questionnaire page */
    private $category;

    /**
     * mod_groupformation_question_table constructor.
     *
     * @param $category
     */
    public function __construct($category) {
        $this->category = $category;
    }

    /**
     * Print HTML of table header
     */
    public function print_header() {

        if ($this->category == 'topic') {
            echo '<div id="topicshead">';
            echo get_string('topics_question', 'groupformation');
            echo '</div>';
            echo '<ul class="sortable_topics">';

        } else {
            echo '<table class="responsive-table">';
            echo '<colgroup><col class="firstCol"></colgroup>';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">';
            echo get_string('tabletitle_' . $this->category, 'groupformation');
            echo '</th>';
            echo '<th scope="col" colspan="100%">';
            echo '</th>';

            echo '</tr></thead><tbody>';
        }
    }

    /**
     * Print HTML for table footer
     */
    public function print_footer() {
        // Closing the table or unordered list.
        if ($this->category == 'topics') {
            // Close unordered list.
            echo '</ul>';

            echo '<div id="invisible_topics_inputs">
                            </div>';
        } else {
            // Close tablebody and close table.
            echo ' </tbody>
                          </table>';
        }
    }
}

