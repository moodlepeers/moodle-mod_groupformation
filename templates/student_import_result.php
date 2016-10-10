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
    <div class="gf_pad_header"><?php echo get_string('import', 'groupformation'); ?></div>
    <div class="gf_pad_content">
        <?php if ($this->_['successful']): ?>
            <p><?php echo get_string('successful_import', 'groupformation') ?></p>
            <p>
                <a href="<?php echo $this->_['import_export_url']; ?>">
                <span class="gf_button gf_button_pill gf_button_small">
                    <?php echo get_string('tab_overview', 'groupformation'); ?>
                   </span>
                </a>
            </p>
        <?php else: ?>
            <p><?php echo get_string('failed_import', 'groupformation'); ?></p>
            <p>
                <a href="<?php echo $this->_['import_form']; ?>">
                <span class="gf_button gf_button_pill gf_button_small">
                    <?php echo get_string('back'); ?>
                   </span>
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>