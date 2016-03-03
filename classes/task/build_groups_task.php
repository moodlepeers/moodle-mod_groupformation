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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_groupformation\task;

require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/job_manager.php');

class build_groups_task extends \core\task\scheduled_task {

    /**
     * (non-PHPdoc)
     *
     * @see \core\task\scheduled_task::get_name()
     */
    public function get_name() {
        return get_string('jobget_name', 'groupformation');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \core\task\task_base::execute()
     */
    public function execute() {
        // First reset aborted jobs; user might wanna use it soon.
        $this->reset_aborted_jobs();

        // Look for jobs; select a job; get it done.
        $this->do_job();
    }

    /**
     * Selects a waiting job, runs it and saves results
     *
     * @return boolean
     */
    private function do_job() {
        $saved = false;

        $job = null;

        $job = \mod_groupformation_job_manager::get_next_job();

        if (!is_null($job)) {
            $result = \mod_groupformation_job_manager::do_groupal($job);
            $aborted = \mod_groupformation_job_manager::is_job_aborted($job);
            if (!$aborted) {
                $saved = \mod_groupformation_job_manager::save_result($job, $result);

                // Notify teacher about finished group formation.
                \mod_groupformation_job_manager::notify_teacher($job);
            }else{
                \mod_groupformation_job_manager::reset_job($job);
            }
        }
    }

    /**
     * Resets all aborted jobs which are not currently running
     */
    private function reset_aborted_jobs() {
        $jobs = \mod_groupformation_job_manager::get_aborted_jobs();
        foreach ($jobs as $key => $job) {
            \mod_groupformation_job_manager::reset_job($job);
        }
    }
}
