
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
 * @package   mod_groupformation
 * @copyright 2014, Nora Wester
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();
$plugin->version   = 2015091100; 
$plugin->requires  = 2014050800; //value taken from mod "feedback"
$plugin->cron      = 0;//1*60; // seconds
$plugin->component = 'mod_groupformation';
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = 'v1.1';
$plugin->dependencies = array();
 
// $plugin->dependencies = array(
//     'mod_forum' => ANY_VERSION,
//     'mod_data'  => TODO
// );