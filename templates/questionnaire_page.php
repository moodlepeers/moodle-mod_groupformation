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
 * Questionnaire page template
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $USER;
?>

<div>
    <?php if (isset($this->_['archive_alert'])): ?>
        <?php echo $this->_['archive_alert']; ?>
    <?php endif; ?>
    <?php if (isset($this->_['preview_alert'])): ?>
        <?php echo $this->_['preview_alert']; ?>
    <?php endif; ?>
    <?php if (isset($this->_['committed_alert'])): ?>
        <?php echo $this->_['committed_alert']; ?>
    <?php endif; ?>
    <?php if (isset($this->_['participant_code'])): ?>
        <?php echo $this->_['participant_code']; ?>
    <?php endif; ?>
    <?php echo $this->_['navbar']; ?>
    <?php echo $this->_['progressbar']; ?>
    <?php echo $this->_['questions']; ?>
</div>