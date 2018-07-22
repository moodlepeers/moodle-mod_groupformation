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
 * Class mod_groupformation_group_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

/**
 * Class mod_groupformation_group_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_group_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('group_info');

    /** @var string Title of page */
    protected $title = 'group';

    /**
     * mod_groupformation_group_view_controller constructor.
     *
     * @param int $groupformationid
     * @param mod_groupformation_group_controller $controller
     * @throws coding_exception
     * @throws dml_exception
     */
    public function __construct($groupformationid, $controller) {
        parent::__construct($groupformationid, $controller);
        if (!$this->store->ask_for_topics()) {
            $this->templatenames = array_diff($this->templatenames, array('group_topics'));
        }
    }

    /**
     * Renders 'group_info' template.
     *
     * @return string
     */
    public function render_group_info() {
        $overviewoptions = new mod_groupformation_template_builder();
        $overviewoptions->set_template('group_info');
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
        if ($store->is_archived()) {
            $output .= '<div class="alert alert-warning" id="commited_view">' . get_string('archived_activity_answers', 'groupformation') . '</div>';
        } else {
            $output .= parent::render();
        }

        return $output;
    }
}