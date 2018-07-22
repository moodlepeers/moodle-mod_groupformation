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
 * Grouping incomplete groups template
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
<div class="maxgroupsizenotreached_header gf_pad_header_small">
    <?php echo get_string('max_group_size_not_reached', 'groupformation'); ?>
</div>
<div class="maxgroupsizenotreached_body gf_pad_content">
    <?php foreach ($this->_ as $entry):?>
        <div class="grid row_highlight">
            <div class="col_m_75"><?php echo $entry['groupname']; ?>
                - <?php echo get_string('number_member', 'groupformation') . ' ' ?>
                <b><?php echo $entry['groupsize']; ?></b></div>


            <div class="col_m_25 bp_align_right-middle">
                <a href="<?php echo $entry['grouplink'][0]; ?>">
                    <?php if (!$entry['grouplink'][1] == 'disabled') { ?>
                        <span class="btn btn-primary btn-sm" gf_button gf_button_pill gf_button_tiny>
                <?php echo get_string('go_to_group_view', 'groupformation'); ?>
                </span>
                    <?php } else { ?>
                        <button class="btn btn-primary btn-sm" gf_button gf_button_pill gf_button_tiny disabled>
                            <?php echo get_string('go_to_group_view', 'groupformation'); ?>
                        </button>
                    <?php } ?>
                </a>
            </div>

        </div>
    <?php endforeach;?>
</div>
