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
 * Load the xml-based questions
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');

class mod_groupformation_xml_loader {

    /** @var mod_groupformation_storage_manager */
    private $store;

    /**
     * mod_groupformation_xml_loader constructor.
     * @param mod_groupformation_storage_manager|null $store
     */
    public function __construct(mod_groupformation_storage_manager $store = null) {
        $this->store = $store;
    }

    /**
     * Deletes old questions and updates with new questions
     *
     * @param $category
     * @return array
     */
    public function save_data($category) {
        $array = array();
        $init = $this->store->catalog_table_not_set($category);
        if (!$init) {
            $this->store->delete_all_catalog_questions($category);
        }
        $array[] = $this->save($category, 'en');
        $array[] = $this->save($category, 'de');

        return $array;
    }

    /**
     * Checks whether the version is old and needs to be updated and updates the version
     *
     * @param $category
     */
    public function latest_version($category) {
        global $CFG;

        $xmlfile = $CFG->dirroot . '/mod/groupformation/xml_question/question_de_' . $category . '.xml';

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            $version = trim($xml->QUESTIONS['VERSION']);
            if (!$this->store->latest_version($category, $version)) {
                $array = $this->save_data($category);
                $number = $array[0][1];

                if ($array[1][1] > $number) {
                    $number = $array[1][1];
                }

                $this->store->add_catalog_version($category, $number, $version, false);
            }
        } else {
            exit("The file $xmlfile cannot be opened or found.");
        }
    }

    /**
     * Returns an array with version and number of answers
     *
     * @param $category
     * @param $lang
     * @return array
     */
    private function save($category, $lang) {
        global $CFG;
        $xmlfile = $CFG->dirroot . '/mod/groupformation/xml_question/' . $lang . '_' . $category . '.xml';

        $return = array();

        if (file_exists($xmlfile)) {
            $xml = simplexml_load_file($xmlfile);

            $return[] = trim($xml->QUESTIONS['VERSION']);
            $numbers = 0;

            foreach ($xml->QUESTIONS->QUESTION as $question) {
                $options = $question->OPTIONS;
                $optionsarray = array();

                foreach ($options->OPTION as $option) {
                    $optionsarray[] = trim($option);
                }

                $numbers++;

                $array = array('type' => trim($question['TYPE']),
                    'question' => trim($question->QUESTIONTEXT),
                    'options' => $optionsarray,
                    'position' => $numbers,
                    'questionid' => trim($question['ID']),
                );

                $this->store->add_catalog_question($array, $lang, $category);
            }

            $return[] = $numbers;

            return $return;

        } else {
            exit("The file $xmlfile cannot be opened or found.");
        }

    }
}
