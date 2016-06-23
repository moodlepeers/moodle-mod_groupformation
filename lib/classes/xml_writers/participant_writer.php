<?php
// This file is part of PHP implementation of GroupAL
// http://sourceforge.net/projects/groupal/
//
// GroupAL is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// GroupAL implementations are distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with GroupAL. If not, see <http://www.gnu.org/licenses/>.
//
//  This code CAN be used as a code-base in Moodle
// (e.g. for moodle-mod_groupformation). Then put this code in a folder
// <moodle>\lib\groupal
/**
 * This class contains an implementation of an xml writer for participants with criteria
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once($CFG->dirroot . "/lib/groupal/classes/criteria/criterion.php");
require_once($CFG->dirroot . "/lib/groupal/classes/participant.php");

class lib_groupal_participant_writer {

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
     * @param array $criteria_types
     * @param array $participants
     * @return boolean
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

        $writer->startElement('Participants'); // <Participants ..>

        $writer->writeAttribute('version', '1');

        $criteria_types = $participants[0]->getCriteria();

        $this->write_criteria_types($criteria_types);

        $this->write_participants($participants);

        $writer->endElement();    // </Participants>

        $writer->endDocument();

        $writer->flush();

        return true;
    }

    /**
     * Writes XML for an array participants
     *
     * @param array $participants
     */
    private function write_participants($participants) {
        foreach ($participants as $participant) {
            $this->write_participant($participant);
        }
    }

    /**
     * Writes XML for a single participant
     *
     * @param lib_groupal_participant $p
     */
    private function write_participant(lib_groupal_participant $p) {
        $writer = $this->writer;

        $writer->startElement('participant');
        $writer->writeAttribute('id', $p->getID());

        $criteria = $p->getCriteria();

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
     * @param lib_groupal_criterion $c
     */
    private function write_criterion(lib_groupal_criterion $c) {
        $writer = $this->writer;

        $writer->startElement('Criterion');

        $this->write_criterion_attributes($c);

        $values = $c->getValues();

        $this->write_criterion_values($values);

        $writer->endElement();
    }

    /**
     * Writes XML for an array of criterion values
     *
     * @param array $values
     */
    private function write_criterion_values($values) {
        $writer = $this->writer;

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
     * @param lib_groupal_criterion $c
     */
    private function write_criterion_attributes(lib_groupal_criterion $c) {
        $writer = $this->writer;
        $writer->writeAttribute('name', $c->getName());
        $writer->writeAttribute('minValue', $c->getMinValue());
        $writer->writeAttribute('maxValue', $c->getMaxValue());
        $writer->writeAttribute('isHomogeneous', ($c->getIsHomogeneous() == 1) ? "true" : "false");
        $writer->writeAttribute('weight', $c->getWeight());
        $writer->writeAttribute('valueCount', count($c->getValues()));
    }

    /**
     * Writes XML for an array of criterion types
     *
     * @param array $criteria_types
     */
    private function write_criteria_types($criteria_types) {
        $writer = $this->writer;

        $writer->startElement('UsedCriteria');

        foreach ($criteria_types as $c_type) {
            $this->write_criterion_type($c_type);
        }

        $writer->endElement();
    }

    /**
     * Writes XML for a single criterion type
     *
     * @param lib_groupal_criterion $c_type
     */
    private function write_criterion_type(lib_groupal_criterion $c_type) {
        $writer = $this->writer;
        $writer->startElement('Criterion');

        $this->write_criterion_attributes($c_type);

        $writer->endElement();
    }
}