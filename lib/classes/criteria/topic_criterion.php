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
 * This class contains values based on users characteristics and skills.
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion_weight.php");

/**
 * Class mod_groupformation_topic_criterion
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_topic_criterion extends mod_groupformation_criterion {

    /**
     * mod_groupformation_topic_criterion constructor.
     *
     * @param array $valuearray The ratings for each topic
     * @throws Exception
     */
    public function __construct($valuearray) {
        $this->set_name('topic');
        $this->set_min_value(1);
        $this->set_max_value(count($valuearray));
        $this->set_values($valuearray);
        $this->set_homogeneous(true);
        mod_groupformation_criterion_weight::add_if_not_allready_exist('topics', 1);
    }
}