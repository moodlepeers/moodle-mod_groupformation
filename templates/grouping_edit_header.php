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
 * Grouping edit header template
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
<div id="sticky-anchor"></div>
<div id="sticky">


    <div class="gf_pad_select_membs">
        <div class="grid">
            <div class="col_m_75">
                <div id="selected_membs">
                    <div id="ux_hint_1">
                        <i>
                            <?php echo get_string('select_info', 'groupformation'); ?>
                        </i>
                    </div>
                    <ul class="selected_memb_list">

                    </ul>
                </div>
                <div class="selected_memb_info">
                <span id="memb_counter">
                    0
                </span>
                    <?php echo get_string('students_selected', 'groupformation'); ?>
                </div>
                <div class="selected_memb_info"><span
                            id="unselect_all"><?php echo get_string('unselect_all', 'groupformation'); ?>
                </span></div>
            </div>
            <div class="col_m_25 bp_align_right-middle">
                <?php foreach ($this->_['buttons'] as $button) { ?>
                    <?php if ($button['type'] == 'submit'): ?>
                        <button id="<?php echo $button['id']; ?>"
                                type="<?php echo $button['type']; ?>"
                                name="<?php echo $button['name']; ?>"
                                value="<?php echo $button['value']; ?>"
                                class="btn btn-secondary"
                                <?php echo $button['state']; ?>
                        >
                            <?php echo $button['text']; ?>
                        </button>
                    <?php endif; ?>
                    <?php if ($button['type'] == 'cancel'): ?>
                        <a href="<?php echo $button['value'] ?>">
                        <span class="btn btn-secondary">
                        <?php echo $button['text']; ?>
                    </span></a>
                    <?php endif; ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <!--        <div class="gf_pad_header_small">-->
    <!--            &Uuml;bersicht gebildeter Gruppen-->
    <!--        </div>-->
    <div class="gf_pad_header_opaque">

    </div>
</div>


