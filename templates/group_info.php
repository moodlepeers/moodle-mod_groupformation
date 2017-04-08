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

<div class="gf_pad_content">
    <?php if (array_key_exists('group_name',$this->_)): ?>
        <p>
            <?php echo get_string('your_group', 'groupformation'); ?>
            <?php echo "<b>". $this->_['group_name'] ."</b>"; ?>
        </p>
    <?php endif; ?>
    <?php if (array_key_exists('topic_info',$this->_)): ?>
        <p>
            <?php echo $this->_['topic_info']; ?>
        </p>
    <?php endif; ?>
    <?php if (array_key_exists('group_info',$this->_)): ?>
        <p>
            <?php echo $this->_['group_info']; ?>
        </p>
    <?php endif; ?>
    <?php if (array_key_exists('members',$this->_)): ?>

        <?php foreach ($this->_['members'] as $row) { ?>
            <p>
                <b>
                    <?php echo $row; ?>
                </b>
            </p>
        <?php } ?>

    <?php endif; ?>
</div>