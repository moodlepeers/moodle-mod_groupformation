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
 * Scientific grouping interface
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/groupformation/lib.php');
require_once($CFG->dirroot . '/mod/groupformation/locallib.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/grouping/grouping.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/define_file.php');

/**
 * Class mod_groupformation_scientific_grouping
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_scientific_grouping extends mod_groupformation_grouping {

    /** @var int ID of module instance */
    public $groupformationid;

    /** @var mod_groupformation_user_manager The manager of user data */
    private $usermanager;

    /** @var mod_groupformation_storage_manager The manager of activity data */
    private $store;

    /** @var mod_groupformation_groups_manager The manager of groups data */
    private $groupsmanager;

    /** @var mod_groupformation_criterion_calculator The calculator for criteria */
    private $criterioncalculator;

    /**
     * mod_groupformation_scientific_grouping constructor.
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
     * Scientific division of users and creation of participants
     *
     * @param array $users Two parted array - first part is all groupal users, second part are all random users
     * @return array
     * @throws dml_exception
     */
    public function run_grouping($users) {

        $big5specs = mod_groupformation_data::get_criterion_specification('big5');

        unset($big5specs['labels']['neuroticism']);
        unset($big5specs['labels']['openness']);
        unset($big5specs['labels']['agreeableness']);

        $specs = array(
            "big5" => $big5specs
        );

        $configurations = array(
            "mrand:0;ex:1;gh:1" => array('big5_extraversion' => true, 'big5_conscientiousness' => true),
            "mrand:0;ex:1;gh:0" => array('big5_extraversion' => true, 'big5_conscientiousness' => false),
            "mrand:0;ex:0;gh:0" => array('big5_extraversion' => false, 'big5_conscientiousness' => false),
            "mrand:0;ex:0;gh:1" => array('big5_extraversion' => false, 'big5_conscientiousness' => true),
            "mrand:0;ex:1;gh:_" => array('big5_extraversion' => true, 'big5_conscientiousness' => null),
            "mrand:0;ex:0;gh:_" => array('big5_extraversion' => false, 'big5_conscientiousness' => null),
            "mrand:0;ex:_;gh:1" => array('big5_extraversion' => null, 'big5_conscientiousness' => true),
            "mrand:0;ex:_;gh:0" => array('big5_extraversion' => null, 'big5_conscientiousness' => false),
            "mrand:1;ex:_;gh:_" => array(),
        );

        $configurationkeys = array_keys($configurations);

        $numberofslices = count($configurationkeys);

        $groupsizes = $this->store->determine_group_size($users);

        if (count($users[0]) < $numberofslices) {
            return array();
        }

        // Divide users into n slices.
        $slices = $this->slicing($users[0], $numberofslices);

        $cohorts = array();

        for ($i = 0; $i < $numberofslices; $i++) {
            $slice = $slices[$i];

            $configurationkey = $configurationkeys[$i];
            $configuration = $configurations[$configurationkey];

            $rawparticipants = $this->participantparser->build_participants($slice, $specs);

            $participants = $this->configure_participants($rawparticipants, $configuration);

            $cohorts[$configurationkey] = $this->build_cohort($participants, $groupsizes[0], $configurationkey);
        }

        // Handle all users with incomplete or no questionnaire submission.
        $randomkey = "rand:1;mrand:_;ex:_;gh:_";

        $randomparticipants = $this->participantparser->build_empty_participants($users[1]);
        $randomcohort = $this->build_cohort($randomparticipants, $groupsizes[1], $randomkey);

        $cohorts[$randomkey] = $randomcohort;

        return $cohorts;
    }

}