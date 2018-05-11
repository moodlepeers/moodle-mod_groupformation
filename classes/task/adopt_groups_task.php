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
 * Scheduled Task for building groups and releasing aborted job requests
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_groupformation\task;

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/advanced_job_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/group_generator.php');

/**
 * Class adopt_groups_task
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adopt_groups_task extends \core\task\scheduled_task {

    /**
     * (non-PHPdoc)
     *
     * @see \core\task\scheduled_task::get_name()
     */
    public function get_name() {
        return get_string('adopt_groups_task', 'groupformation');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \core\task\task_base::execute()
     */
    public function execute() {

        // Look for jobs; select a job; get it done.
        $this->do_job();
    }

    /**
     * Selects a waiting job, runs it and saves results
     *
     * @return void
     * @throws \dml_exception
     */
    private function do_job() {

        $ajm = new \mod_groupformation_advanced_job_manager();

        $job = null;

        $job = $ajm::get_next_job('waiting_groups');

        if (!is_null($job)) {
            \mod_groupformation_group_generator::generate_moodle_groups($job->groupformationid);

            $ajm::set_job($job, 'done_groups');
        }
    }
}
