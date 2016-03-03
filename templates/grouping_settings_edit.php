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
<?php if ($this->_['onlyactivestudents']):?>
<div class="grid">
<?php $url = new moodle_url("/course/modedit.php?update=".$this->_['cmid']."&return=1"); $a = new stdClass(); $a->url = '<a href="'.$url->out().'">'.get_string('settings').'</a>';?>
    <div class="col_m_87-5 alert"><?php echo get_string('onlyactivestudents_info', 'groupformation',$a);?></div>
</div>
<?php endif;?>

<?php foreach($this->_['buttons'] as $button) { ?>

<button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>" value="<?php echo $button['value']; ?>" class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>

<?php } ?>

<div class="grid">
    <div class="col_m_87-5"> <span id="howmany_selected">0</span><?php echo ' '.get_string('students_selected','groupformation').' (<a id="unselect">'.get_string('drop_selection','groupformation').'</a>)'?></div>
</div>

<!--<p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>-->