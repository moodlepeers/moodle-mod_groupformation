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
}
