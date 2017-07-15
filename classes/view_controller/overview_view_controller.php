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
 * Class mod_groupformation_overview_view_controller
 *
 * @package mod_groupformation
 * @author Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

class mod_groupformation_overview_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('overview_info', 'overview_statistics', 'overview_settings');
    /** @var string Title of page */
    protected $title = 'overview';

    /**
     * mod_groupformation_overview_view_controller constructor.
     *
     * @param $groupformationid
     * @param $controller
     */
    public function __construct($groupformationid, $controller) {
        parent::__construct($groupformationid, $controller);
        $this->view->assign('title_append', " - ".$this->store->get_name());
    }

    /**
     * Renders 'overview_info' template.
     *
     * @return string
     */
    public function render_overview_info() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('overview_info');
        $overviewoptions->assign_multiple($this->controller->load_info());

        return $overviewoptions->load_template();
    }

    /**
     * Renders 'overview_statistics' template.
     *
     * @return string
     */
    public function render_overview_statistics() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('overview_statistics');
        $overviewoptions->assign_multiple($this->controller->load_statistics());

        return $overviewoptions->load_template();
    }

    /**
     * Renders 'overview_settings' template.
     *
     * @return string
     */
    public function render_overview_settings() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('overview_settings');
        $overviewoptions->assign_multiple($this->controller->load_settings());

        return $overviewoptions->load_template();
    }

    /**
     * Handle actions on submit or click
     */
    public function handle_actions() {
        if (data_submitted() && confirm_sesskey()) {
            // Initialize useful entities.
            $usermanager = new mod_groupformation_user_manager($this->groupformationid);
            $groupsmanager = new mod_groupformation_groups_manager ($this->groupformationid);
            $id = $this->controller->cmid;
            $userid = $this->controller->userid;

            // Read URL parameters.
            $back = optional_param('back', 0, PARAM_INT);
            $consent = optional_param('consent', null, PARAM_BOOL);
            $begin = optional_param('begin', 1, PARAM_INT);
            $questions = optional_param('questions', null, PARAM_BOOL);
            $participantcode = optional_param('participantcode', '', PARAM_TEXT);

            if ($begin == 1 && isset($questions) && $questions == 1 && !$back) {
                // If consent was given, set internal.
                if (isset($consent)) {
                    $usermanager->set_consent($userid, true);
                }

                // If participant code was given, validate and set internal.
                if (isset($participantcode) && $participantcode !== '') {
                    if ($usermanager->validate_participant_code($participantcode)) {
                        $usermanager->register_participant_code($userid, $participantcode);
                    }
                }

                // Redirect.
                $returnurl = new moodle_url ('/mod/groupformation/questionnaire_view.php', array(
                        'id' => $id));
                redirect($returnurl);
            } else if ($begin == -1) {
                // Delete answers due to consent removal.
                $usermanager->delete_answers($userid);

                // Redirect.
                $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
                        'id' => $id));
                redirect($returnurl);
            } else if ($begin == 0) {
                if ($usermanager->is_completed($userid)) {
                    // If completed, unset internal completion status.
                    $usermanager->set_complete($userid, 0);
                } else {
                    // If not completed, set internal completion status.
                    $usermanager->change_status($userid, 1);
                    // Also, set activity completion.
                    groupformation_set_activity_completion($id, $userid);

                    // If math prep course, divide participants into A/B sampling groups.
                    if (mod_groupformation_data::is_math_prep_course_mode()) {
                        // TODO scientific studies A/B sampling groups
                        $groupsmanager->assign_to_groups_a_and_b($userid);
                    }

                    // Redirect.
                    $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
                            'id' => $id));
                    redirect($returnurl);
                }
            }
        }
    }

    /**
     * Handles access to view
     */
    public function handle_access() {
        $id = $this->controller->cmid;
        $context = context_module::instance($id);

        // Check capability for access
        if (has_capability('mod/groupformation:editsettings', $context)) {
            // Redirect.
            $returnurl = new moodle_url ('/mod/groupformation/analysis_view.php', array(
                    'id' => $id, 'do_show' => 'analysis'));
            redirect($returnurl->out());
        }
    }

    /**
     * Renders content.
     *
     * @return string
     */
    public function render() {
        $output = "";

        // User params in URL.
        $missingconsent = optional_param('giveconsent', false, PARAM_BOOL);
        $missingparticipantcode = optional_param('giveparticipantcode', false, PARAM_BOOL);

        // If consent is missing, display message.
        if ($missingconsent) {
            $output .= '<div class="alert alert-danger">' . get_string('consent_alert_message', 'groupformation') .
                    '</div>';
        }

        // If participant code is missing, display message.
        if ($missingparticipantcode) {
            $output .= '<div class="alert alert-danger">' . get_string('participant_code_alert_message', 'groupformation') .
                    '</div>';
        }

        // If version is outdated, display message.
        if (groupformation_get_current_questionnaire_version() > $this->store->get_version()) {
            $output .= '<div class="alert">' . get_string('questionnaire_outdated', 'groupformation') . '</div>';
        }

        // If activity is archived, display message, else content.
        if ($this->store->is_archived()) {
            $output .= '<div class="alert" id="commited_view">' . get_string('archived_activity_answers', 'groupformation') .
                    '</div>';
        } else {
            // Form to catch user submissions and clicks.
            $output .= '<form action="' . htmlspecialchars($_SERVER ["PHP_SELF"]) . '" method="post" autocomplete="off">';
            $output .= '<input type="hidden" name="questions" value="1"/>';
            $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

            $output .= parent::render();

            $output .= '</form>';
        }

        return $output;
    }
}