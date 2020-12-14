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
 * Import Export info template
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.');
}

?>
<div class="gf_settings_pad">
    <div class="gf_pad_header"><?php echo get_string('import', 'groupformation'); ?></div>
    <div class="gf_pad_content">
        <p><?php echo $this->_['import_description']; ?></p>
        <p><?php echo get_string('consent_header_import', 'groupformation'); ?><p>
        <p><?php echo $this->_['consenttext']; ?></p>

        <p>
            <?php if (!$this->_['import_button']): ?>
                <button class="btn btn-secondary" disabled>
                    <?php echo get_string('import', 'groupformation'); ?>
                </button>
            <?php else: ?>
                <a href="<?php echo $this->_['import_form']; ?>">
                <span class="btn btn-secondary">
                    <?php echo get_string('import', 'groupformation'); ?>
                   </span></a>
            <?php endif; ?>
        </p>
    </div>
    <div class="gf_pad_header"><?php echo get_string('export', 'groupformation'); ?></div>
    <div class="gf_pad_content">
        <p><?php echo $this->_['export_description']; ?></p>

        <p>
            <?php if (!$this->_['export_button']): ?>
                <button class="btn btn-secondary" disabled>
                    <?php echo get_string('export', 'groupformation'); ?>
                </button>
            <?php else: ?>
                <a href="<?php echo $this->_['export_url']; ?>" target="_blank">
                <span class="btn btn-secondary">
                    <?php echo get_string('export', 'groupformation'); ?>
                   </span></a>
            <?php endif; ?>
        </p>
    </div>
    <div class="gf_pad_header"><?php echo get_string('export_all', 'groupformation'); ?></div>
    <div class="gf_pad_content">
        <p><?php echo $this->_['export_all_description']; ?></p>
        <p>
            <div>
                <input type="checkbox" value="1" id="all_data_check">
                <label for="all_data_check">
                    <?php echo $this->_['export_all_data_check'];?>
                </label>
            </div>
        </p>
        <p>
            <div id="all_data_false">
                <a href="<?php echo $this->_['export_all_data_url_false']; ?>" target="_blank">
                    <span class="btn btn-secondary">
                        <?php echo get_string('export', 'groupformation'); ?>
                    </span>
                </a>
            </div>
            <div id="all_data_true" class="gf_hidden">
                <a href="<?php echo $this->_['export_all_data_url_true']; ?>" target="_blank">
                    <span class="btn btn-secondary">
                        <?php echo get_string('export', 'groupformation'); ?>
                    </span>
                </a>
            </div>
        </p>
    </div>
</div>