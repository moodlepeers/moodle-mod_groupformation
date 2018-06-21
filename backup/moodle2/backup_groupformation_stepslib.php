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
 * Define all the backup steps that will be used by the backup_groupformation_activity_task
 *
 * @package     mod_groupformation
 * @category    backup
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete groupformation structure for backup, with file and id annotations
 *
 * @package     mod_groupformation
 * @category    backup
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_groupformation_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     * @throws base_element_struct_exception
     * @throws base_step_exception
     */
    protected function define_structure() {
        // Define the root element describing the groupformation instance.
        $groupformation = new backup_nested_element('groupformation',
                array('id'),
                array(
                        'name',
                        'intro',
                        'introformat',
                        'timecreated',
                        'timemodified',
                        'timeopen',
                        'timeclose',
                        'grade',
                        'szenario',
                        'knowledge',
                        'knowledgelines',
                        'knowledgevalues',
                        'knowledgenumber',
                        'topics',
                        'topiclines',
                        'topicvalues',
                        'topicnumber',
                        'groupoption',
                        'maxmembers',
                        'maxgroups',
                        'groupname',
                        'evaluationmethod',
                        'maxpoints',
                        'onlyactivestudents',
                        'emailnotifications',
                        'version',
                        'allanswersrequired'
                )
        );

        // Define data sources.
        $groupformation->set_source_table('groupformation', array('id' => backup::VAR_ACTIVITYID));
        // If we were referring to other tables, we would annotate the relation
        // with the element's annotate_ids() method.

        return $this->prepare_activity_structure($groupformation);
    }
}
