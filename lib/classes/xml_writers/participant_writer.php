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
 * This class contains an implementation of an xml writer for participants with criteria
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/mod/groupformation/lib/classes/criteria/criterion.php");
require_once($CFG->dirroot . "/mod/groupformation/lib/classes/participant.php");

/**
 * Class mod_groupformation_participant_writer
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_participant_writer {
    /** @var XMLWriter Writer */
    private $writer;

    /**
     * Creates instance of participant_writer
     * @param string $uri
     */
    public function __construct($uri = "participants.xml") {
        $this->writer = new XMLWriter ();
        $this->uri = $uri;
    }

    /**
     * Creates XML file with participants
     *
     * @param null $participants
     * @return bool
     * @throws Exception
     */
    public function write($participants = null) {
        if (is_null($participants)) {
            return false;
        }

        if (is_array($participants) && count($participants) <= 0) {
            return false;
        }

        $writer = $this->writer;

        $writer->openUri($this->uri);

        $writer->startDocument('1.0', 'utf-8');
        $writer->setIndent(true);
        $writer->setIndentString("    ");

        $writer->startElement('Participants');

        $writer->writeAttribute('version', '1');

        $criteriatypes = $participants[0]->getCriteria();

        $this->write_criteria_types($criteriatypes);

        $this->write_participants($participants);

        $writer->endElement();

        $writer->endDocument();

        $writer->flush();

        return true;
    }

    /**
     * Writes XML for an array participants
     *
     * @param array $participants
     * @throws Exception
     */
    private function write_participants($participants) {
        foreach ($participants as $participant) {
            $this->write_participant($participant);
        }
    }

    /**
     * Writes XML for a single participant
     *
     * @param mod_groupformation_participant $p
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    private function write_criterion(mod_groupformation_criterion $c) {
        $writer = $this->writer;

        $writer->startElement('Criterion');

        $this->write_criterion_attributes($c);

        $values = $c->get_values();

        $this->write_criterion_values($values);

        $writer->endElement();
    }

    /**
     * Writes XML for an array of criterion values
     *
     * @param array $values
     */
    private function write_criterion_values($values) {

        foreach ($values as $key => $value) {
            $this->write_criterion_value($key, $value);
        }
    }

    /**
     * Writes XML for a single criterion value
     *
     * @param int $key
     * @param float $value
     */
    private function write_criterion_value($key, $value) {
        $writer = $this->writer;
        $writer->startElement('Value');
        $writer->writeAttribute('name', 'value' . $key);
        $writer->writeAttribute('value', $value);
        $writer->endElement();
    }

    /**
     * Writes XML for criterion attributes
     *
     * @param mod_groupformation_criterion $c
     * @throws Exception
     */
    private function write_criterion_attributes(mod_groupformation_criterion $c) {
        $writer = $this->writer;
        $writer->writeAttribute('name', $c->get_name());
        $writer->writeAttribute('minValue', $c->get_min_value());
        $writer->writeAttribute('maxValue', $c->get_max_value());
        $writer->writeAttribute('isHomogeneous', ($c->is_homogeneous() == 1) ? "true" : "false");
        $writer->writeAttribute('weight', $c->get_weight());
        $writer->writeAttribute('valueCount', count($c->get_values()));
    }

    /**
     * Writes XML for an array of criterion types
     *
     * @param array $criteriatypes
     * @throws Exception
     */
    private function write_criteria_types($criteriatypes) {
        $writer = $this->writer;

        $writer->startElement('UsedCriteria');

        foreach ($criteriatypes as $ctype) {
            $this->write_criterion_type($ctype);
        }

        $writer->endElement();
    }

    /**
     * Writes XML for a single criterion type
     *
     * @param mod_groupformation_criterion $ctype
     * @throws Exception
     */
    private function write_criterion_type(mod_groupformation_criterion $ctype) {
        $writer = $this->writer;
        $writer->startElement('Criterion');

        $this->write_criterion_attributes($ctype);

        $writer->endElement();
    }
}