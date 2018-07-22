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
 * Class mod_groupformation_import_export_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

/**
 * Class mod_groupformation_import_export_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_import_export_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('import_export_info');
    /** @var string Title of page */
    protected $title = 'import_export';

    /**
     * mod_groupformation_import_export_view_controller constructor.
     *
     * @param int $groupformationid
     * @param mod_groupformation_import_export_controller $controller
     * @throws coding_exception
     */
    public function __construct($groupformationid, $controller) {
        parent::__construct($groupformationid, $controller);
        $this->title = null;
    }

    /**
     * Renders 'import_export_info' template.
     *
     * @return string
     */
    public function render_import_export_info() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('import_export_info');
        $overviewoptions->assign_multiple($this->controller->load_info());

        return $overviewoptions->load_template();
    }

    /**
     * Renders 'import_export_statistics' template.
     *
     * @return string
     */
    public function render_import_export_statistics() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('import_export_statistics');
        $overviewoptions->assign_multiple($this->controller->load_statistics());

        return $overviewoptions->load_template();
    }

    /**
     * Renders 'import_export_topics' template.
     *
     * @return string
     */
    public function render_import_export_topics() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('import_export_topics');
        $overviewoptions->assign_multiple($this->controller->load_statistics());

        return $overviewoptions->load_template();
    }

    /**
     * Handles access
     */
    public function handle_access() {
        $id = $this->controller->cm->id;

        if (!mod_groupformation_data::import_export_enabled()) {
            $returnurl = new moodle_url ('/mod/groupformation/view.php', array(
                    'id' => $id));
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
            $output .= '<div class="alert">' . get_string('questionnaire_outdated', 'groupformation') . '</div>';
        }
        if ($store->is_archived()) {
            $output .= '<div class="alert" id="commited_view">' . get_string('archived_activity_answers', 'groupformation') . '</div>';
        } else {
            $output .= parent::render();
        }

        return $output;
    }
}