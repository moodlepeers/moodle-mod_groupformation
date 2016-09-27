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
<div class="grid">
    <div class="col_m_100 bp_align_left-middle">
        <span><?php echo $this->_['info_teacher']; ?></span><br>
        <span><i></i></span>
    </div>
    <div class="col_m_66 bp_align_left-middle">
        <span><b><?php echo get_string('starttime', 'groupformation'); ?>
                : </b><?php echo $this->_['analysis_time_start']; ?></span><br>
        <span><b><?php echo get_string('endtime', 'groupformation'); ?>
                : </b><?php echo $this->_['analysis_time_end']; ?></span><br><br>
        <span><i><?php echo $this->_['analysis_status_info']; ?></i></span>
        <span><i></i></span>
    </div>

    <div class="col_m_33 bp_align_right-middle">
        <!--<span class="toolt" tooltip="Aktivität stoppen, um Gruppen zu bilden." style="margin-right:0.7em;"></span>-->
        <button type="<?php echo $this->_['button']['type']; ?>" name="<?php echo $this->_['button']['name']; ?>"
                value="<?php echo $this->_['button']['value']; ?>"
                class="gf_button gf_button_pill gf_button_small"<?php echo $this->_['button']['state']; ?> ><?php echo $this->_['button']['text']; ?></button>
    </div>
</div>
