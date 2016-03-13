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
<div class="gf_pad_header_small"> <?php echo get_string('options', 'groupformation'); ?> </div>
<div class="gf_pad_content" style="">
    <div class="grid">
        <div class="col_m_100">
<!--            <form action="--><?php //echo htmlspecialchars ( $_SERVER ["PHP_SELF"] ) ; ?><!--" method="post" autocomplete="off">-->
<!--                <input type="hidden" name="questions" value="1"/>-->
                <input type="hidden" name="id" value="<?php echo $this->_['cmid']; ?>"/>
                <div style="padding-bottom: 10px;">
                    <div>
                        <p>
                            <b>
                                <?php echo get_string('consent_opt_in','groupformation');?>
                            </b>
                        </p>
                    </div>
                    <div>
                        <p>
                            <?php echo get_string('consent_message','groupformation');?>
                        </p>
                    </div>
                    <div>
                        <p style="margin-left: 10px;">
                            <input type="checkbox" name="consent"
                                <?php echo ($this->_['consentvalue'])?'checked disabled':''?>
                                   value="<?php echo $this->_['consentvalue'];?>"/>
                            <?php echo ' '.get_string('consent_agree','groupformation');?>
                        </p>
                    </div>
                </div>
                <p><?php echo $this->_['buttons_infos']; ?></p>
                <?php foreach ($this->_['buttons'] as $button) { ?>
                    <button type="<?php echo $button['type']; ?>" name="<?php echo $button['name']; ?>"
                            value="<?php echo $button['value']; ?>"
                            class="gf_button gf_button_pill gf_button_small" <?php echo $button['state']; ?>><?php echo $button['text']; ?></button>
                <?php } ?>
<!--            </form>-->
        </div>
    </div>
</div>