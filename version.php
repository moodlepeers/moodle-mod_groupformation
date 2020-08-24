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
 * Version information
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2020081900;
$plugin->requires = 2016052300; // Could be set to Moodle 3.1 2016052300; Best use Moodle 3.2 (2016120500) due to new bootstrap 4.
$plugin->cron = 0;
$plugin->component = 'mod_groupformation';
$plugin->maturity = MATURITY_STABLE;
$plugin->release = 'v1.6.2'; // We do not use the Moodle versions as prefix; we use semantic versioning (http://semver.org/).
$plugin->dependencies = array();
