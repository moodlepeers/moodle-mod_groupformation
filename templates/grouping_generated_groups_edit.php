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
    <form>
        <?php foreach ($this->_['buttons'] as $button) { ?>

            <button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>"
                    value="<?php echo $button['value']; ?>"
                    class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>

        <?php } ?>
        <div style="display:none;">
            <textarea id="groups_string" name="groups_string" cols="35"
                      rows="<?php echo count($this->_) - 2 ?>">
                <?php echo $this->_['groups_string']; ?></textarea>
        </div>
    </form>

    <div class="grid">
        <div class="col_m_87-5"><span
                id="howmany_selected">0</span><?php echo ' ' . get_string('students_selected', 'groupformation') . ' (<a id="unselect">' . get_string('drop_selection', 'groupformation') . '</a>)' ?>
        </div>
    </div>

<?php foreach ($this->_ as $key => $entry) { ?>
    <?php //var_dump($key,$entry)?>
    <?php if (is_array($entry) && !array_key_exists('button1', $entry) && !($key == 'groups_string')): ?>
        <div class="grid bottom_stripe">
            <div class="col_s_50"><?php echo get_string('name_by_group', 'groupformation'); ?>
                <b><?php echo $entry['groupname']; ?></b></div>
            <ul id="group_id_<?php echo $entry['id'];?>" class="col_s_100 gf_group_links">
                <?php foreach ($entry['group_members'] as $user) { ?>
                    <?php echo '<li class="gf_button gf_button_pill gf_button_small" id="'.$user['id'].'">' . $user['name'] . '</li>'; ?>
                <?php } ?>
            </ul>
        </div>
    <?php endif; ?>
<?php } ?>