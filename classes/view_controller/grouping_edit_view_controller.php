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

    /**
     * Renders content
     *
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function render() {
        $store = $this->store;
        $id = $this->controller->cm->id;
        $context = context_module::instance($id);

        $output = "";

        if (groupformation_get_current_questionnaire_version() > $store->get_version()) {
            $output .= '<div class="alert alert-warning">' . get_string('questionnaire_outdated', 'groupformation') . '</div>';
        }

        if ($store->is_archived() && has_capability('mod/groupformation:editsettings', $context)) {
            $output .= '<div class="alert alert-warning" id="commited_view">';
            $output .= get_string('archived_activity_admin', 'groupformation');
            $output .= '</div>';
        } else {
            $output .= '<form id="edit_groups_form" action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off">';
            $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
            $output .= '<input type="hidden" name="id" value="' . $id . '"/>';

            $output .= parent::render();

            $output .= '</form>';
        }

        return $output;
    }

    /**
     * Handles access
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function handle_access() {
        $id = $this->controller->cm->id;
        $context = context_module::instance($id);

        if (!has_capability('mod/groupformation:editsettings', $context)) {
            $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
                    'id' => $id, 'do_show' => 'view'));
            redirect($returnurl);
        }
    }

    /**
     * Handles actions
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function handle_actions() {
        $controller = $this->controller;
        $id = $this->controller->cmid;

        if ((data_submitted()) && confirm_sesskey()) {
            $saveedit = optional_param('save_edit', null, PARAM_BOOL);
            if (true || (isset ($saveedit) && $saveedit == 1)) {
                $groupsstring = optional_param('groups_string', null, PARAM_TEXT);
                $controller->save_edit($groupsstring);
            }
            $returnurl = new moodle_url ('/mod/groupformation/grouping_view.php', array(
                    'id' => $id, 'do_show' => 'grouping'));
            redirect($returnurl);
        }
    }
}