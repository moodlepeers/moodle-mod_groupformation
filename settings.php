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
 * mod_groupformation settings
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


$settings->add(new admin_setting_heading('sampleheader',
                                         get_string('settings_header', 'groupformation'),
                                         get_string('settings_description', 'groupformation')));

$settings->add(new admin_setting_configtext('groupformation/archiving_time',
    get_string('settings_archiving_time', 'groupformation'),
    get_string('settings_archiving_time_description', 'groupformation'),
    '365'));

$settings->add(new admin_setting_configcheckbox('groupformation/import_export',
    get_string('settings_import_export', 'groupformation'),
    get_string('settings_import_export_description', 'groupformation'),
    1));

$settings->add(new admin_setting_configcheckbox('groupformation/participant_code',
    get_string('settings_participant_code', 'groupformation'),
    get_string('settings_participant_code_description', 'groupformation'),
    0));