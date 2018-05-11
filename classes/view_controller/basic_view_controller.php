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

require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');

/**
 * Class mod_groupformation_basic_view_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * mod_groupformation_basic_controller constructor.
     *
     * @param int $groupformationid
     * @param Controller $controller
     * @throws coding_exception
     */
    public function __construct($groupformationid, $controller) {
        $this->groupformationid = $groupformationid;
        $this->controller = $controller;
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->view = new mod_groupformation_template_builder();
        $this->view->set_template($this->wrappername);
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
}
