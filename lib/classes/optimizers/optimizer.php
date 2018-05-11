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
 * Optimizer
 *
 * This class contains an implementation of an evaluator interface which handles
 * the evaluation of groups
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/optimizers/ioptimizer.php");

/**
 * Class mod_groupformation_optimizer
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_optimizer implements mod_groupformation_ioptimizer {

    /** @var mod_groupformation_imatcher Matcher */
    public $matcher;

    /**
     * mod_groupformation_optimizer constructor.
     *
     * @param mod_groupformation_imatcher $matcher
     */
    public function __construct(mod_groupformation_imatcher $matcher) {
        $this->matcher = $matcher;
    }

    /**
     * Optimizes cohort
     *
     * @param mod_groupformation_cohort $cohort
     * @return mixed|void
     * @throws Exception
     */
    public function optimize_cohort(mod_groupformation_cohort $cohort) {
        // For each pair of good and bad group try to average them.
        for ($i = 0; $i < count($this->groups / 2); $i++) {
            $goodgroup = $this->groups[$i];
            $badgroup = $this->groups[(count($this->groups) - 1) - $i];
            $this->average_two_groups($goodgroup, $badgroup);
        }
        $cohort->calculate_cpi();
    }

    /**
     * Computes average of two groups
     *
     * @param mod_groupformation_group $goodgroup
     * @param mod_groupformation_group $badgroup
     */
    public function average_two_groups(mod_groupformation_group &$goodgroup, mod_groupformation_group &$badgroup) {

        if (abs($goodgroup->get_gpi() - $badgroup->get_gpi()) < 0.02) {
            return;
        }
        // Dissolve the groups and randomize the position of participant.
        $localngt = array(); // List of Participants.
        foreach ($goodgroup->get_participants() as $p) {
            $localngt[] = $p;
        }
        foreach ($badgroup->get_participants() as $p) {
            $localngt[] = $p;
        }

        // Randomize position of entries.
        $this->shuffle($localngt);

        // Match the groups new.
        $g1 = new mod_groupformation_group();
        $g2 = new mod_groupformation_group();

        $newgroups = array($g1, $g2);
        $this->matcher->match_to_groups($localngt, $newgroups);
        $oldavg = ($goodgroup->get_gpi() - $badgroup->get_gpi()) / 2;
        $newavg = ($g1->get_gpi() - $g2->get_gpi()) / 2;
        $firstcondition = $newavg > $oldavg;
        if ($firstcondition) {
            $goodgroup->set_participants($g1->get_participants());
            $goodgroup->set_gpi($g1->get_gpi());
            $badgroup->set_participants($g2->get_participants());
            $badgroup->set_gpi($g2->get_gpi());
        }
    }

    /**
     * Shuffles
     *
     * @param array $list
     */
    public function shuffle(array &$list) {
        // TODO Randomize Array Entries.
    }

}