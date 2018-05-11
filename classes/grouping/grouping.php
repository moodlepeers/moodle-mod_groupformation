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
 * Basic grouping interface
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/participant_parser.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/groups_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');

require_once($CFG->dirroot . '/mod/groupformation/lib/classes/criteria/specific_criterion.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/participant.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/matchers/group_centric_matcher.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/basic_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/random_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/algorithms/topic_algorithm.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/optimizers/optimizer.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/xml_writers/participant_writer.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/xml_writers/cohort_writer.php');

/**
 * Class mod_groupformation_grouping
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_grouping {

    /** @var int ID of module instance */
    public $groupformationid;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store;

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    /** @var mod_groupformation_criterion_calculator The calculator for criteria */
    private $criterioncalculator;

    /**
     * mod_groupformation_grouping constructor.
     *
     * @param int $groupformationid
     * @throws dml_exception
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->usermanager = new mod_groupformation_user_manager($groupformationid);
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->groupsmanager = new mod_groupformation_groups_manager($groupformationid);
        $this->criterioncalculator = new mod_groupformation_criterion_calculator($groupformationid);
        $this->participantparser = new mod_groupformation_participant_parser($groupformationid);
    }

    /**
     * Returns configured participants with either two (GH and EX), one (GH or EX) or no criteria
     *
     * @param array $participants
     * @param array $configuration
     * @return mixed
     */
    public function configure_participants($participants, $configuration) {

        foreach ($participants as $participant) {
            $criteria = $participant->criteria;
            $configuredcriteria = array();
            if (count($configuration) == 0) {
                $configuredcriteria = null;
            } else {
                for ($j = 0; $j < count($criteria); $j++) {
                    $criterion = $criteria[$j];
                    if (array_key_exists($criterion->get_name(), $configuration)) {
                        $mode = $configuration[$criterion->get_name()];
                        if (is_null($mode)) {
                            $criterion = null;
                        } else {
                            $criterion->set_homogeneous($mode);
                        }
                    } else {
                        $criterion = null;
                    }

                    if (!is_null($criterion)) {
                        $configuredcriteria[] = $criterion;
                    }
                }
            }
            $participant->criteria = $configuredcriteria;
        }

        return $participants;
    }

    /**
     * Handles grouping based on algorithms determined by configuration key
     *
     * @param array $users
     * @param int $groupsize
     * @param string $configurationkey
     * @return mod_groupformation_cohort
     * @throws Exception
     */
    public function build_cohort($users, $groupsize, $configurationkey) {
        if (strpos($configurationkey, "rand:1") !== false) {
            // Random.
            return $this->build_random_cohort($users, $groupsize);
        } else if (strpos($configurationkey, "rand:0") !== false || strpos($configurationkey, "groupal:1") !== false) {
            // Not Random.
            return $this->build_groupal_cohort($users, $groupsize);
        } else if (strpos($configurationkey, "rand:0") !== false || strpos($configurationkey, "topic:1") !== false) {
            // Not Random.
            return $this->build_topic_cohort($users, $groupsize);
        } else {
            // Random.
            return $this->build_random_cohort($users, $groupsize);
        }
    }

    /**
     * Handles grouping based on the random algorithm
     *
     * @param array $users
     * @param int $groupsize
     * @return mod_groupformation_cohort
     */
    public function build_random_cohort($users, $groupsize) {
        $gfra = new mod_groupformation_random_algorithm ($users, $groupsize);

        return $gfra->do_one_formation();
    }

    /**
     * Handles grouping based on the groupal algorithm
     *
     * @param array $users
     * @param int $groupsize
     * @return mod_groupformation_cohort
     * @throws Exception
     */
    public function build_groupal_cohort($users, $groupsize) {
        // Choose matcher.
        $matcher = new mod_groupformation_group_centric_matcher();
        $gfa = new mod_groupformation_basic_algorithm($users, $matcher, $groupsize);
        return $gfa->do_one_formation();
    }

    /**
     * Handles grouping based on the groupal algorithm
     *
     * @param array $users
     * @param int $groupsizes
     * @return mod_groupformation_cohort
     */
    public function build_topic_cohort($users, $groupsizes) {
        $gfa = new mod_groupformation_topic_algorithm($groupsizes, $users);
        return $gfa->do_one_formation();
    }

    /**
     * Slices the array of users into a specific number of almost even sized arrays of users
     *
     * @param array $users
     * @param int $numberofslices
     * @return array
     */
    public function slicing($users, $numberofslices) {
        shuffle($users);
        $slices = array();

        if ($numberofslices == 1) {
            return array($users);
        }

        for ($i = 0; $i < count($users); $i++) {
            if ($i < $numberofslices) {
                $slices[$i] = array();
            }
            $m = $i % $numberofslices;
            $slices[$m][] = $users[$i];
        }

        return $slices;
    }

    /**
     * Scientific division of users and creation of participants
     *
     * @param array $users Two parted array - first part is all groupal users, second part are all random users
     * @return array
     */
    public function run_grouping($users) {
        return null;
    }

}