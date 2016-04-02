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
 * Grouping settings view for template builder
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, René Röpke, Neora Wester, Ahmed Zukic
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>
<?php if ($this->_['onlyactivestudents']):?>
    <div class="grid">

        <?php $url = new moodle_url("/course/modedit.php?update=".$this->_['cmid']."&return=1"); ?>
        <?php $a = new stdClass(); ?>
        <?php $a->url = '<a href="'.$url->out().'">'.get_string('settings').'</a>';?>

        <div class="col_m_87-5 alert">
            <?php echo get_string('onlyactivestudents_info', 'groupformation', $a);?>
        </div>
    </div>
<?php endif;?>

<div class="grid">
    <div class="col_m_87-5">
        <?php echo get_string('are', 'groupformation');?>
        <b>
            <?php echo $this->_['student_count']; ?>
        </b>
        <?php if ($this->_['student_count'] == 1): ?>
            <?php echo get_string('students_grouping_single', 'groupformation');?>
        <?php else: ?>
            <?php echo get_string('students_grouping_multiple', 'groupformation');?>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($this->_['buttons'] as $button) { ?>

    <button type="<?php echo $button['type']; ?>"
            name="<?php echo $button['name']; ?>"
            value="<?php echo $button['value']; ?>"
            class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>>
        <?php echo $button['text']; ?>
    </button>

<?php } ?>

<div>
    <?php $opacity = ($this->_['status'][1] == 0) ? "0.5" : "1.0"?>
    <?php $red = ($this->_['status'][1] == 1) ? "color: red;" : "";?>
	<div style="margin-left: 4px;opacity:<?php echo $opacity.";".$red;?>">
        <i>
            <?php echo $this->_['status'][0];?>
            <?php if (isset($this->_['emailnotifications']) && $this->_['emailnotifications']):?>
                <?php echo get_string('emailnotifications_info', 'groupformation');?>
            <?php endif;?>
        </i>
    </div>
</div>