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
 * Class mod_groupformation_basic_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

/**
 * Class mod_groupformation_questionnaire_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_questionnaire_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('questionnaire_page');

    /** @var string Wrapper view */
    protected $wrappername = 'wrapper_questionnaire_view';

    /** @var string Title of page */
    protected $title = 'import_export';

    /**
     * mod_groupformation_import_export_view_controller constructor.
     *
     * @param int $groupformationid
     * @param mod_groupformation_questionnaire_controller $controller
     * @throws coding_exception
     */
    public function __construct($groupformationid, $controller) {
        parent::__construct($groupformationid, $controller);
        $this->title = null;
    }

    /**
     * Renders page
     *
     * @return string
     */
    public function render_questionnaire_page() {
        $questionnairepage = new mod_groupformation_template_builder();
        $questionnairepage->set_template('questionnaire_page');
        $questionnairepage->assign_multiple($this->controller->load_questionnaire_page());

        return $questionnairepage->load_template();
    }

    /**
     * Renders content
     *
     * @return string|void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function render() {
        $store = $this->store;
        $id = $this->controller->cmid;
        $context = context_module::instance($id);

        if ($store->is_archived() && !has_capability('mod/groupformation:editsettings', $context)) {
            echo '<div class="alert" id="commited_view">';
            $tmp = has_capability('mod/groupformation:editsettings', $context) ? "admin" : "answers";
            echo get_string('archived_activity_' . $tmp, 'groupformation');
            echo '</div>';
        } else {
            echo parent::render();
        }
        if (get_config('core', 'theme') == 'boost') {
            echo '</div></div>';
        }
    }

    /**
     * Handles access
     *
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function handle_access() {
        $id = $this->controller->cmid;
        $context = context_module::instance($id);

        $userid = $this->controller->userid;
        $usermanager = new mod_groupformation_user_manager($this->groupformationid);

        $consent = $usermanager->get_consent($userid);
        $participantcode = $usermanager->has_participant_code($userid) || !mod_groupformation_data::ask_for_participant_code();

        if ((!$consent || !$participantcode) &&
                !has_capability('mod/groupformation:editsettings', $context)) {
            $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
                    'id' => $id, 'giveconsent' => !$consent, 'giveparticipantcode' => !$participantcode));
            redirect($returnurl);
        }
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function handle_actions() {
        /** @var mod_groupformation_questionnaire_controller $controller */
        $controller = $this->controller;
        $id = $this->controller->cmid;
        $context = context_module::instance($id);

        $userid = $this->controller->userid;
        $usermanager = new mod_groupformation_user_manager($this->groupformationid);
        $store = $this->store;

        $direction = $this->controller->direction;
        $go = true;

        if (!has_capability('mod/groupformation:editsettings', $context) &&
                (data_submitted() && confirm_sesskey()) &&
                in_array($store->statemachine->get_state(), array("q_open", "q_reopened"))) {
            $go = $controller->save_answers();
        }

        if ($controller->currentcategory == "") {
            $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
                    'id' => $id, 'back' => '1'));
            redirect($returnurl);
        }

        if (!$go) {
            $controller->not_go_on();
        }

        $next = ($direction == -1 && $this->controller->currentcategory != "") || $controller->has_next();

        if (!$next) {
            if ($usermanager->has_answered_everything($userid)) {
                $usermanager->set_evaluation_values($userid);
                $store->userstatemachine->change_state($userid, "complete");
            }

            $action = optional_param('action', 0, PARAM_BOOL);
            if (isset ($action) && $action == 1) {
                $usermanager->change_status($userid);
            }

            $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
                    'id' => $id, 'do_show' => 'view', 'back' => '1'));
            redirect($returnurl);
        }
    }
}
