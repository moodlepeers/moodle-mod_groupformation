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
 * @package mod_groupformation
 * @author Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

class mod_groupformation_analysis_view_controller extends mod_groupformation_basic_view_controller {

    /** @var array Template names */
    protected $templatenames = array('analysis_info', 'analysis_statistics', 'analysis_topics');
    /** @var string Title of page */
    protected $title = 'analysis';

    /**
     * mod_groupformation_analysis_view_controller constructor.
     *
     * @param $groupformationid
     * @param $controller
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
}