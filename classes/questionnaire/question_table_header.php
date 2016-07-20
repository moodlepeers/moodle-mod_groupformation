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
class mod_groupformation_question_table_header {

    /**
     * Print HTML of table header
     *
     * @param $category
     * @param $tabletype
     * @param $headeroptarray
     */
    public function print_html($category, $tabletype, $headeroptarray) {

        if ($tabletype == 'type_topics') {
            echo '<div id="topicshead">' . get_string('topics_question', 'groupformation') . '</div><ul class="sortable_topics">';

        } else {
            echo '<table class="responsive-table">' . '<colgroup><col class="firstCol"><colgroup>';

            echo '<thead><tr><th scope="col">' .
                (($tabletype == 'type_knowledge') ? get_string('knowledge_question', 'groupformation') :
                    get_string('category_' . $category, 'groupformation')) . '</th>';
            if ($tabletype == 'radio') {
                $headersize = count($headeroptarray);

                echo '<th scope="col" colspan="' . $headersize . '"><span style="float:left">' . $headeroptarray[0] . '</span>
																						<span style="float:right">' .
                    $headeroptarray[$headersize - 1] . '</span></th>';
            } else if ($tabletype == 'type_knowledge') {
                echo '<th scope="col"><div class="legend">' . get_string('knowledge_scale', 'groupformation') .
                    '</div></th>';
            } else {
                echo '<th scope="col"></th>';
            }

            echo '</tr></thead><tbody>';
        }
    }
}

