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
 * Controller for evaluation view
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/xml_writer.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/csv_writer.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/template_builder.php');

/**
 * Class mod_groupformation_export_controller
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_export_controller {

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store = null;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager = null;

    /** @var int ID of module instance */
    private $groupformationid = null;

    /** @var int ID of course module*/
    public $cmid = null;

    /**
     * Constructs instance of import export controller
     *
     * @param int $groupformationid
     * @param int $cmid
     */
    public function __construct($groupformationid, $cmid) {
        $this->groupformationid = $groupformationid;
        $this->cmid = $cmid;

        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
    }

    /**
     * Returns elements for template
     *
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    public function load_info() {
        $assigns = array();

        $exportusers = get_string('export_users', 'groupformation');
        $exportusersurl = $this->generate_export_url('users');

        $assigns['export_users'] = $exportusers;
        $assigns['export_users_url'] = $exportusersurl;

        return $assigns;
    }

    /**
     * Generates export url for csv file
     *
     * @param string $type
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    private function generate_export_url($type = 'answers') {
        $csvwriter = new mod_groupformation_csv_writer ($this->groupformationid);

        // Generate content for answer file for export.
        $content = $csvwriter->get_data($type);

        $timestamp = time();
        $filename = 'archived_' . $type . '_'.$timestamp.'.csv';

        $context = context_module::instance($this->cmid);

        $fileinfo = array(
                'contextid' => $context->id, 'component' => 'mod_groupformation', 'filearea' => 'groupformation_answers',
                'itemid' => $this->groupformationid, 'filepath' => '/', 'filename' => $filename);

        return groupformation_get_url($fileinfo, $content);
    }
}