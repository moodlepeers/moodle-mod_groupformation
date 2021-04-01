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
 * Analysis info template
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $USER;
?>

<div class="gf_pad_content">
    <div class="col_m_100 bp_align_left-middle">
        <span><?php echo $this->_['info_teacher']; ?></span><br>
        <span><i></i></span>
    </div>
    <div class="col_m_10 bp_align_left-middle">
        <span><b><?php echo get_string('starttime', 'groupformation'); ?>
                : </b><?php echo $this->_['analysis_time_start']; ?></span><br>
        <span><b><?php echo get_string('endtime', 'groupformation'); ?>
                : </b><?php echo $this->_['analysis_time_end']; ?></span><br><br>
        <span><i><?php echo $this->_['analysis_status']; ?></i></span>
        <span><i></i></span>
    </div>

    <div >
        <button type="<?php echo $this->_['button']['type']; ?>"
                name="<?php echo $this->_['button']['name']; ?>"
                value="<?php echo $this->_['button']['value']; ?>"
                class="btn btn-secondary"
                <?php echo $this->_['button']['state']; ?>
        >
            <?php echo $this->_['button']['text']; ?>
        </button>
        <?php if (array_key_exists('reopen_button', $this->_)): ?>
            <button type="<?php echo $this->_['reopen_button']['type']; ?>"
                    name="<?php echo $this->_['reopen_button']['name']; ?>"
                    value="<?php echo $this->_['reopen_button']['value']; ?>"
                    class="btn btn-secondary"
                    <?php echo $this->_['reopen_button']['state']; ?>
            >
                <?php echo $this->_['reopen_button']['text']; ?>
            </button>
        <?php endif; ?>
    </div>
</div>