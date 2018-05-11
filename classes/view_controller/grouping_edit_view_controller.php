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
 * Class mod_groupformation_grouping_edit_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

/**
 * Class mod_groupformation_grouping_edit_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_grouping_edit_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('grouping_edit_header', 'grouping_edit_groups');
    /** @var string Title of page */
    protected $title = 'grouping_edit';

    /**
     * Renders 'grouping_settings' template.
     *
     * @return string
     */
    public function render_grouping_edit_header() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('grouping_edit_header');
        $overviewoptions->assign_multiple($this->controller->load_edit_header());

        return $overviewoptions->load_template();
    }

    /**
     * Renders 'grouping_statistics' template.
     *
     * @return string
     */
    public function render_grouping_edit_groups() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('grouping_edit_groups');
        $overviewoptions->assign_multiple($this->controller->load_edit_groups());

        return $overviewoptions->load_template();
    }
}