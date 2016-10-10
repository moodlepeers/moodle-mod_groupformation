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
<div style="display:none;">
            <textarea id="groups_string" name="groups_string" cols="35"
                      rows="<?php echo count($this->_) - 2 ?>">
                <?php echo $this->_['groups_string']; ?></textarea>
</div>


<?php $i = 0; ?>
<div class="gf_pad_content">
    <?php foreach ($this->_['generated_groups'] as $key => $entry) { ?>

        <div id="<?php echo $i == 0 ? 'first_group' : ''; ?>" class="grid bottom_stripe">
            <?php $i++; ?>
            <div class="col_s_100">
                <div class="group_params"><b><?php echo get_string('name_by_group', 'groupformation'); ?></b>
                    <?php echo $entry['groupname']; ?></div>
                <div class="group_params"><b><?php echo get_string('groupsize', 'groupformation'); ?></b><span
                        class="g_memb_counter"> </span></div>
            </div>
            <div class="col_s_100 gf_group_links">
                <div class="add_membs_block">
                    <span class="add_membs_to_g">+</span>
                </div>
                <ul id="<?php echo $entry['id']; ?>" class="memb_list">
                    <?php foreach ($entry['group_members'] as $user) { ?>
                        <?php echo '<li id="' . $user['id'] . '">' . $user['name'] . '</li>'; ?>
                    <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
</div>
