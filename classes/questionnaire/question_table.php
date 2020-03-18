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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class mod_groupformation_question_table
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_question_table {

    /** @var string Category of questionnaire page */
    private $category;

    /**
     * mod_groupformation_question_table constructor.
     *
     * @param string $category
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
     * Returns HTML of table header
     *
     * @param string $addon
     * @return string
     */
    public function get_header($addon = '') {
        $s = "";

        if ($this->category == 'topic') {
            $s .= '<div id="topicshead">';
            $s .= get_string('topics_question', 'groupformation');
            $s .= '</div>';
            $s .= '<ul class="sortable_topics">';

        } else {
            $s .= '<table class="responsive-table">';
            $s .= '<colgroup><col class="firstCol"></colgroup>';
            $s .= '<thead>';
            $s .= '<tr>';
            $s .= '<th scope="col">';
            if ($this->category == 'knowledge') {
                $s .= 'Wie ist dein Ergebnis im Eingangstest?';
            } else {
                $s .= get_string('tabletitle_' . $this->category . $addon, 'groupformation');
            }
            // TODO if(category == 'knowledge')... else...
            // TODO wieder hierdurch ersetzen $s .=get_string('tabletitle_' . $this->category . $addon, 'groupformation');
            $s .= '</th>';
            $s .= '<th scope="col" colspan="100%">';
            $s .= '</th>';

            $s .= '</tr></thead><tbody>';
        }
        return $s;
    }

    /**
     * Print HTML for table footer
     */
    public function print_footer() {
        // Closing the table or unordered list.
        if ($this->category == 'topic') {
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

    /**
     * Returns HTML for table footer
     */
    public function get_footer() {
        $s = "";
        // Closing the table or unordered list.
        if ($this->category == 'topic') {
            // Close unordered list.
            $s .= '</ul>';

            $s .= '<div id="invisible_topics_inputs">
                            </div>';
        } else {
            // Close tablebody and close table.
            $s .= ' </tbody>
                          </table>';
        }
        return $s;
    }
}

