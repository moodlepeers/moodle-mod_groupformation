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
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>
<div class="gf_settings_pad">
    <div class="gf_pad_header">
        <?php echo get_string('group_building', 'groupformation') . ' - '; ?><?php echo $this->_['grouping_title']; ?>
    </div>
    <div class="gf_pad_content bp_align_left-middle">
        <?php echo $this->_['grouping_settings']; ?>
    </div>

    <div class="gf_pad_header_small">
        <?php echo get_string('evaluation', 'groupformation'); ?>
    </div>
    <div class="gf_pad_content">
        <?php echo $this->_['grouping_statistics']; ?>
    </div>

    <div class="maxgroupsizenotreached_header gf_pad_header_small">
        <?php echo get_string('max_group_size_not_reached', 'groupformation'); ?>
    </div>
    <div class="maxgroupsizenotreached_body gf_pad_content">
        <?php echo $this->_['grouping_incomplete_groups']; ?>
    </div>

    <div class="groupsbuilt_header gf_pad_header_small">
        <?php echo get_string('group_overview', 'groupformation'); ?>
    </div>
    <div class="groupsbuilt_body gf_pad_content">
        <?php echo $this->_['grouping_generated_groups']; ?>
    </div>
</div>

