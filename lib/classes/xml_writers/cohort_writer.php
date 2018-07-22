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
 * XML Writer for Cohorts
 *
 * This class contains an implementation of xml_writer based on a cohort object in order
 * to export the result of the group formation
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/cohorts/cohort.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/group.php");

/**
 * Class mod_groupformation_cohort_writer
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_cohort_writer {
    /** @var XMLWriter Writer */
    private $writer;

    /** @var string URI */
    private $uri;

    /**
     * Creates instance of cohort_writer
     * @param string $uri
     */
    public function __construct($uri = "cohort.xml") {
        $this->writer = new XMLWriter ();
        $this->uri = $uri;
    }

    /**
     * Creates XML file with participants
     *
     * @param null $cohort
     * @return bool
     * @throws Exception
     */
    public function write($cohort = null) {
        if (is_null($cohort)) {
            return false;
        }

        if (is_array($cohort->groups) && count($cohort->groups) <= 0) {
            return false;
        }

        $writer = $this->writer;

        $writer->openUri($this->uri);

        $writer->startDocument('1.0', 'utf-8');
        $writer->setIndent(true);
        $writer->setIndentString("    ");

        $writer->startElement('Instance');

        $writer->writeAttribute('id', '2015');

        $this->write_groups($cohort);

        $writer->endElement();

        $writer->endDocument();

        $writer->flush();

        return true;
    }

    /**
     * Writes XML for an array participants
     *
     * @param mod_groupformation_cohort $cohort
     * @throws Exception
     */
    private function write_groups(mod_groupformation_cohort $cohort) {
        $writer = $this->writer;

        $writer->startElement('Groups');
        $writer->writeAttribute('usedMatcher', $cohort->whichmatcherused);
        $writer->writeAttribute('CohortPerformanceIndex', $cohort->calculate_cpi());
        $writer->writeAttribute('CohortAveragePerformanceIndex', "-");
        $writer->writeAttribute('CohortNormStDev', "-");

        foreach ($cohort->groups as $g) {
            $this->write_group($g);
        }

        $writer->endElement();
    }

    /**
     * Writes XML for a single group
     *
     * @param mod_groupformation_group $group
     */
    private function write_group(mod_groupformation_group $group) {
        $writer = $this->writer;

        $writer->startElement('Group');
        $writer->writeAttribute('id', $group->get_id());
        $writer->writeAttribute('groupPerformanceIndex', $group->get_gpi());
        $writer->writeAttribute('groupAverage', '-');
        $writer->writeAttribute('normalizedStDev', '-');

        foreach ($group->get_participants() as $p) {
            $this->write_participant($p);
        }

        $writer->endElement();
    }

    /**
     * Writes XML for a single participant
     *
     * @param mod_groupformation_participant $p
     */
    private function write_participant(mod_groupformation_participant $p) {
        $writer = $this->writer;

        $writer->startElement('participant');
        $writer->writeAttribute('id', $p->get_id());

        $criteria = $p->get_criteria();

        $this->write_criteria($criteria);

        $writer->endElement();
    }

    /**
     * Writes XML for criteria
     *
     * @param array $criteria
     */
    private function write_criteria($criteria) {
        foreach ($criteria as $criterion) {
            $this->write_criterion($criterion);
        }
    }

    /**
     * Writes XML for a single criterion
     *
     * @param mod_groupformation_criterion $c
     */
    private function write_criterion(mod_groupformation_criterion $c) {
        $writer = $this->writer;

        $writer->startElement('Criterion');

        $this->write_criterion_attributes($c);

        $writer->endElement();
    }

    /**
     * Writes XML for criterion attributes
     *
     * @param mod_groupformation_criterion $c
     */
    private function write_criterion_attributes(mod_groupformation_criterion $c) {
        $writer = $this->writer;
        $writer->writeAttribute('name', $c->get_name());
        $writer->writeAttribute('isHomogeneous', $c->is_homogeneous());
        $writer->writeAttribute('minValue', $c->get_min_value());
        $writer->writeAttribute('maxValue', $c->get_max_value());
        $writer->writeAttribute('value0', array_sum($c->get_values()) / count($c->get_values()));
    }
}