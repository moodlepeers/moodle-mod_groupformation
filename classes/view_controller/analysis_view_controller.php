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
 * Class mod_groupformation_analysis_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

/**
 * Class mod_groupformation_analysis_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_analysis_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('analysis_info', 'analysis_statistics', 'analysis_topics');

    /** @var string Title of page */
    protected $title = 'analysis';

    /**
     * mod_groupformation_analysis_view_controller constructor.
     *
     * @param int $groupformationid
     * @param mod_groupformation_analysis_controller $controller
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct($groupformationid, $controller) {
        parent::__construct($groupformationid, $controller);
        if (!$this->store->ask_for_topics()) {
            $this->templatenames = array_diff($this->templatenames, array('analysis_topics'));
        }
    }

    /**
     * Renders 'analysis_info' template.
     *
     * @return string
     */
    public function render_analysis_info() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('analysis_info');
        $overviewoptions->assign_multiple($this->controller->load_info());

        return $overviewoptions->load_template();
    }

    /**
     * Renders 'analysis_statistics' template.
     *
     * @return string
     */
    public function render_analysis_statistics() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('analysis_statistics');
        $overviewoptions->assign_multiple($this->controller->load_statistics());

        return $overviewoptions->load_template();
    }

    /**
     * Renders 'analysis_topics' template.
     *
     * @return string
     */
    public function render_analysis_topics() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('analysis_topics');
        $overviewoptions->assign_multiple($this->controller->load_topic_statistics());

        return $overviewoptions->load_template();
    }

    /**
     * Handles access to view
     */
    public function handle_access() {
        $id = $this->controller->cmid;
        $context = context_module::instance($id);

        // Check capability.
        if (!has_capability('mod/groupformation:editsettings', $context)) {
            // Redirect.
            $return = new moodle_url ('/mod/groupformation/view.php', array(
                    'id' => $id, 'do_show' => 'view'));
            redirect($return->out());
        }
    }

    /**
     * Handle actions on submit or click
     */
    public function handle_actions() {
        $id = $this->controller->cmid;

        if ((data_submitted()) && confirm_sesskey()) {
            $switcher = optional_param('questionnaire_switcher', null, PARAM_INT);

            if (isset($switcher)) {
                $this->controller->trigger_questionnaire($switcher);
            }
            $return = new moodle_url ('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis'));
            redirect($return->out());
        }
    }

    /**
     * Renders page
     *
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function render() {
        $id = $this->controller->cmid;
        $context = context_module::instance($id);

        $output = "";

        if (groupformation_get_current_questionnaire_version() > $this->store->get_version()) {
            $output .= '<div class="alert alert-warning">';
            $output .= get_string('questionnaire_outdated', 'groupformation');
            $output .= '</div>';
        }

        if ($this->store->is_archived() && has_capability('mod/groupformation:editsettings', $context)) {
            $output .= '<div class="alert alert-warning" id="commited_view">';
            $output .= get_string('archived_activity_admin', 'groupformation');
            $output .= '</div>';
        } else {
            $output .= '<form action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off">';

            $output .= '<input type="hidden" name="id" value="' . $id . '"/>';
            $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

            $output .= parent::render();

            $output .= '</form>';
        }

        return $output;
    }
}