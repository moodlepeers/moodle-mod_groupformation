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
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Class mod_groupformation_xml_writer
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_groupformation_xml_writer {

    /** @var XMLWriter This is the writer instance used to create xml */
    private $writer;

    /** @var mod_groupformation_user_manager User manager instance */
    private $usermanager;

    /** @var mod_groupformation_groups_manager Groups manager instance */
    private $groupsmanager;

    /** @var mod_groupformation_storage_manager Storage manager instance */
    private $store;

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
     * @throws dml_exception
     */
    public function write($userid = null, $groupformationid = null, $categories = null) {
        if (is_null($userid) || is_null($groupformationid) || is_null($categories)) {
            return false;
        }

        $writer = $this->writer;

        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);

        $writer->openMemory();

        $writer->startDocument('1.0', 'utf-8');
        $writer->setIndent(true);
        $writer->setIndentString("    ");

        $writer->startElement('answers');

        $writer->writeAttribute('userid', '' . $userid);

        $writer->startElement('categories');

        $this->write_categories($categories, $userid);

        $writer->endElement();

        $writer->endElement();

        $writer->endDocument();

        $content = $writer->outputMemory(false);

        return $content;
    }

    /**
     * Creates XML for categories
     *
     * @param null $categories
     * @param null $userid
     * @throws dml_exception
     */
    private function write_categories($categories = null, $userid = null) {
        if (is_null($categories)) {
            $categories = $this->store->get_categories();
        }

        foreach ($categories as $category) {
            $this->write_category($category, $userid);
        }
    }

    /**
     * Creates XML with category and answers
     *
     * @param string $category
     * @param int $userid
     * @throws dml_exception
     */
    private function write_category($category, $userid) {
        $writer = $this->writer;

        $writer->startElement('category');
        $writer->writeAttribute('name', $category);

        $answers = $this->usermanager->get_answers($userid, $category);

        $this->write_answers($answers);
        $writer->endElement();
    }

    /**
     * Creates XML with all user related data
     *
     * @param int $userid
     * @param int $groupformationid
     * @param bool $allinstances
     * @return string
     * @throws dml_exception
     */
    public function write_all_data($userid, $groupformationid, $allinstances = false) {
        $this->store = new mod_groupformation_storage_manager($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);

        $writer = $this->writer;

        $writer->openMemory();

        $writer->startDocument('1.0', 'utf-8');
        $writer->setIndent(true);
        $writer->setIndentString("    ");

        $writer->startElement('groupformations');

        if ($allinstances) {
            $groupformations = $this->store->get_all_instances_with_user($userid);
        } else {
            $groupformations = array($this->store->get_instance($groupformationid));
        }

        foreach ($groupformations as $groupformation) {
            $this->write_groupformation($groupformation, $userid);
        }

        $writer->endElement();

        $writer->endDocument();

        $content = $writer->outputMemory(false);

        return $content;

    }

    /**
     * Creates XML for groupformation instance and user data
     * @param stdClass $gf
     * @param int $userid
     * @throws dml_exception
     */
    private function write_groupformation($gf, $userid) {
        $writer = $this->writer;

        $this->store = new mod_groupformation_storage_manager($gf->id);
        $this->usermanager = new mod_groupformation_user_manager ($gf->id);
        $this->groupsmanager = new mod_groupformation_groups_manager ($gf->id);

        $writer->startElement('groupformation');
        $writer->writeAttribute('id', $gf->id);
        $writer->writeAttribute('course', $gf->course);
        $writer->writeAttribute('name', $gf->name);

        $this->write_status($userid);

        $writer->startElement('answers');
        $this->write_categories(null, $userid);
        $writer->endElement();

        $writer->startElement('criteria');
        $this->write_user_values($userid);
        $writer->endElement();

        $writer->endElement();
    }

    /**
     * Creates XML for group
     *
     * @param int $userid
     */
    private function write_group($userid) {
        $writer = $this->writer;

        $groupid = $this->groupsmanager->get_group_id($userid);
        $moodlegroupid = $this->groupsmanager->get_moodle_group_id($groupid);
        $name = $this->groupsmanager->get_group_name($userid);
        $writer->startElement('group');
        $writer->writeAttribute('id', $groupid);
        $writer->writeAttribute('moodlegroupid', $moodlegroupid);
        $writer->writeAttribute('name', $name);
        $writer->endElement();

    }

    /**
     * Creates XML about user values
     *
     * @param int $userid
     * @throws dml_exception
     */
    private function write_user_values($userid) {
        $writer = $this->writer;

        $uservalues = $this->usermanager->get_user_values($userid);

        foreach ($uservalues as $uservalue) {
            $writer->startElement('criterion');
            $writer->writeAttribute('name', $uservalue->criterion.'_'.$uservalue->label);
            $writer->writeAttribute('dimension', $uservalue->dimension);
            $writer->writeAttribute('value', $uservalue->value);
            $writer->endElement();
        }
    }

    /**
     * Creates XML about started record
     *
     * @param int $userid
     * @throws dml_exception
     */
    private function write_status($userid) {
        $writer = $this->writer;

        $started = $this->usermanager->get_instance($userid);

        $writer->startElement('status');

        if (!is_bool($started) && $started) {
            $writer->startElement('consent');
            $writer->writeAttribute('value', $started->consent);
            $writer->endElement();
            $writer->startElement('participantcode');
            $writer->writeAttribute('value', $started->participantcode);
            $writer->endElement();
            $writer->startElement('completed');
            $writer->writeAttribute('value', $started->completed);
            $writer->writeAttribute('timestamp', $started->timecompleted);
            $writer->endElement();
            $writer->startElement('answers');
            $writer->writeAttribute('count', $started->answer_count);
            $writer->endElement();
            $writer->startElement('filtered');
            $writer->writeAttribute('value', $started->filtered);
            $writer->endElement();

            if (!is_null($started->groupid)) {
                $writer->startElement('groupAB');
                $writer->writeAttribute('id', $started->filtered);
                $writer->endElement();
            }
        }
        $this->write_group($userid);

        $writer->endElement();
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