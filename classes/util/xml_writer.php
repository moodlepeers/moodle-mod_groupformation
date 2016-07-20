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
 * An XML Writer for student
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_xml_writer {

    /** @var XMLWriter This is the writer instance used to create xml */
    private $writer;

    /**
     * mod_groupformation_xml_writer constructor.
     */
    public function __construct() {
        $this->writer = new XMLWriter ();
        $this->writer->openMemory();
    }

    /**
     * Creates XML file with answers
     *
     * @param null $userid
     * @param null $groupformationid
     * @param null $categories
     * @return bool|string
     */
    public function write($userid = null, $groupformationid = null, $categories = null) {
        if (is_null($userid) || is_null($groupformationid) || is_null($categories)) {
            return false;
        }

        $writer = $this->writer;

        $usermanager = new mod_groupformation_user_manager ($groupformationid);

        $writer->openMemory();

        $writer->startDocument('1.0', 'utf-8');
        $writer->setIndent(true);
        $writer->setIndentString("    ");

        $writer->startElement('answers');

        $writer->writeAttribute('userid', '' . $userid);

        $writer->startElement('categories');

        foreach ($categories as $category) {
            $writer->startElement('category');
            $writer->writeAttribute('name', $category);

            $answers = $usermanager->get_answers($userid, $category);

            $this->write_answers($answers);

            $writer->endElement();
        }

        $writer->endElement();

        $writer->endElement();

        $writer->endDocument();

        $content = $writer->outputMemory(false);

        return $content;
    }

    /**
     * Writes answers in xml format
     *
     * @param array $answers
     */
    private function write_answers($answers) {
        foreach ($answers as $answer) {

            $this->write_answer($answer);
        }
    }

    /**
     * Writes an answer in xml format
     *
     * @param stdClass $answer
     */
    private function write_answer($answer) {
        $writer = $this->writer;

        $writer->startElement('answer');
        $writer->writeAttribute('questionid', $answer->questionid);
        $writer->writeAttribute('value', $answer->answer);
        $writer->endElement();
    }

}