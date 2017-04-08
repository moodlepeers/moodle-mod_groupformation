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
 * @package mod_groupformation
 * @author Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');

abstract class mod_groupformation_basic_view_controller {

    /** @var int ID of the course */
    protected $groupformationid = null;
    /** @var Controller instance for user */
    protected $controller = null;
    /** @var mod_groupformation_storage_manager instance of store */
    protected $store = null;
    /** @var mod_groupformation_template_builder View builder */
    protected $view = null;
    /** @var string File name for wrapper */
    protected $wrappername = 'wrapper_view';
    /** @var array Template names */
    protected $templatenames = array();
    /** @var string Title of page */
    protected $title = '<title>';

    /**
     * block_pseudolearner_basic_controller constructor.
     *
     * @param $courseid
     * @param $controller
     */
    public function __construct($groupformationid, $controller) {
        $this->groupformationid = $groupformationid;
        $this->controller = $controller;
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->view = new mod_groupformation_template_builder();
        $this->view->set_template('wrapper_view');
        $this->title = get_string('page_title_' . $this->title, 'mod_groupformation');
    }

    /**
     * Renders content.
     *
     * @return string
     */
    public function render() {

        $this->view->assign('title', $this->title);

        $templates = array();

        // Set templates.
        foreach ($this->templatenames as $templatename) {
            $call = 'render_' . $templatename;
            $template = $this->$call();
            $templates[$templatename . '_template'] = $template;
        }

        $this->view->assign('templates', $templates);

        return $this->view->load_template();
    }

    /**
     * Renders options.
     *
     * @return string
     */
    public function render_options() {
        $overviewoptions = new block_pseudolearner_template_builder();
        $overviewoptions->set_template('options');
        $overviewoptions->assign('id', $this->groupformationid);

        $buttons = $this->get_option_buttons();

        $overviewoptions->assign('buttons', $buttons);

        return $overviewoptions->load_template();
    }

    /**
     * Returns all option buttons.
     *
     * @return array
     */
    public abstract function get_option_buttons();
}
