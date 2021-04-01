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
 * Grouping settings template
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

<div class="gf_pad_content bp_align_left-middle">

    <?php if ($this->_['onlyactivestudents']): ?>
        <div class="grid">
            <?php $url = new moodle_url("/course/modedit.php?update=" . $this->_['cmid'] . "&return=1");
            $a = new stdClass();
            $a->url = '<a href="' . $url->out() . '">' . get_string('settings') . '</a>'; ?>
            <div class="col_m_100 alert"><?php echo get_string('onlyactivestudents_info', 'groupformation', $a); ?></div>
        </div>
    <?php endif; ?>

    <div class="grid row_highlight">
        <div class="col_m_100"><b><?php echo $this->_['statistics_available_optimized']; ?></b>
            <?php echo ' ' ?>
            <?php if ($this->_['statistics_available_optimized'] == 1): ?>
                <?php echo get_string('students_available_grouping_optimized_single', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('students_available_grouping_optimized_multiple', 'groupformation'); ?>
            <?php endif; ?>

            <b> <?php echo $this->_['statistics_available_random']; ?> </b>
            <?php echo ' ' ?>
            <?php if ($this->_['statistics_available_random'] == 1): ?>
                <?php echo get_string('students_available_grouping_random_single', 'groupformation'); ?>
            <?php else: ?>
                <?php echo get_string('students_available_grouping_random_multiple', 'groupformation'); ?>
            <?php endif; ?>
        </div>
    </div>

    <?php foreach ($this->_['buttons'] as $button) { ?>

        <button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>"
                value="<?php echo $button['value']; ?>"
                class="btn btn-secondary" <?php echo $button['state']; ?>>
            <?php echo $button['text']; ?></button>

    <?php } ?>

    <div>
        <div style="
            margin-left: 4px;
        <?php if ($this->_['status'][1] == 1): ?>
            color: red
        <?php endif; ?>
            ">
            <i>
                <?php echo $this->_['status'][0]; ?>

                <?php if (false && isset($this->_['emailnotifications']) && $this->_['emailnotifications']): ?>

                    <?php echo " " . get_string('emailnotifications_info', 'groupformation'); ?>

                <?php endif; ?>
            </i>
        </div>
    </div>

</div>