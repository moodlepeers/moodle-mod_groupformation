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
 * Class mod_groupformation_evaluation_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

/**
 * Class mod_groupformation_evaluation_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_evaluation_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('evaluation_info');
    /** @var string Title of page */
    protected $title = 'evaluation';

    /**
     * Renders 'evaluation_info' template.
     *
     * @return string
     */
    public function render_evaluation_info() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('evaluation_info');
        $overviewoptions->assign_multiple($this->controller->load_info());

        return $overviewoptions->load_template();
    }

    /**
     * Handles access
     *
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function handle_access() {
        $id = $this->controller->cmid;
        $context = context_module::instance($id);

        if (has_capability('mod/groupformation:editsettings', $context)) {
            $returnurl = new moodle_url('/mod/groupformation/analysis_view.php', array('id' => $id, 'do_show' => 'analysis'));
            redirect($returnurl);
        }
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

        $output = "";
        if (groupformation_get_current_questionnaire_version() > $store->get_version()) {
            $output .= '<div class="alert">'.get_string('questionnaire_outdated', 'groupformation') . '</div>';
        }
        if ($store->is_archived()) {
            $output .= '<div class="alert" id="commited_view">'.get_string('archived_activity_answers', 'groupformation') . '</div>';
        } else {
            $output .= '<div class="alert" id="commited_view">Diese Ansicht ist im Rahmen der Studie deaktiviert.</div>';
            //parent::render();
        }

        return $output;
    }
}