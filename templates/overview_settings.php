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
 * Overview settings template
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
<?php if (count($this->_['buttons']) > 0): ?>
    <div class="gf_pad_header_small"> <?php echo get_string('options', 'groupformation'); ?> </div>
    <div class="gf_pad_content" style="">
        <div class="grid">
            <div class="col_m_100">

                <input type="hidden" name="id" value="<?php echo $this->_['cmid']; ?>"/>
                <?php if (array_key_exists('consentvalue', $this->_)): ?>
                    <div style="padding-bottom: 10px;">
                        <div>
                            <p>
                                <b>
                                    <?php echo get_string('consent_opt_in', 'groupformation'); ?>
                                </b>
                            </p>
                        </div>
                        <div>
                            <p>
                                <?php echo get_string('consent_header', 'groupformation'); ?>
                            </p>
                            <p>
                                <?php echo $this->_['consenttext']; ?>
                            </p>
                        </div>
                        <div>
                            <p style="margin-left: 10px;">
                                <input type="checkbox" name="consent"
                                        <?php echo ($this->_['consentvalue']) ? 'checked disabled' : '' ?>
                                       value="<?php echo $this->_['consentvalue']; ?>"/>
                                <?php echo ' ' . get_string('consent_agree', 'groupformation'); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (array_key_exists('participant_code', $this->_) && $this->_['participant_code']): ?>
                    <div style="padding-bottom: 10px;">
                        <div>
                            <p>
                                <b>
                                    <?php echo get_string('participant_code_title', 'groupformation'); ?>
                                </b>
                            </p>
                        </div>

                        <div>
                                <p>
                                    <?php echo get_string('participant_code_header_study', 'groupformation'); ?>
                                </p>
                                <p>
                                    <?php echo get_string('participant_code_rules_study', 'groupformation'); ?>
                                </p>
                                <p>
                                    <?php echo get_string('participant_code_example_study', 'groupformation'); ?>
                                </p>
                            <p>
                                <?php echo get_string('participant_code_footer', 'groupformation'); ?>
                            </p>
                        </div>

                        <div>
                            <p style="margin-left: 10px;">
                                <input type="text"
                                        <?php echo ($this->_['participant_code_user'] != '') ? 'checked disabled' : '' ?>
                                       name="participantcode"
                                       value="<?php echo $this->_['participant_code_user']; ?>"/>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                <p><?php echo $this->_['buttons_infos']; ?></p>



                <?php foreach ($this->_['buttons'] as $button) { ?>

                    <?php if (array_key_exists('modal-id', $button)): ?>
                        <button type="button" class="gf_button gf_button_pill gf_button_small"
                                data-toggle="modal" data-target="#<?php echo $button['modal-id']; ?>"
                                <?php echo $button['state']; ?>
                        >
                            <?php echo $button['text']; ?>
                        </button>
                        <!-- Modal -->
                        <div class="modal fade" id="<?php echo $button['modal-id']; ?>"
                             tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                        <h4 class="modal-title" id="myModalLabel"><?php echo $button['modal-title']; ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo $button['modal-text']; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">
                                            <?php echo get_string('cancel');?>
                                        </button>
                                        <button type="<?php echo $button['type']; ?>"
                                                name="<?php echo $button['name']; ?>"
                                                value="<?php echo $button['value']; ?>"
                                                class="btn btn-primary"><?php echo $button['text']; ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <button type="<?php echo $button['type']; ?>"
                                name="<?php echo $button['name']; ?>"
                                value="<?php echo $button['value']; ?>"
                                class="gf_button gf_button_pill gf_button_small"
                            <?php echo $button['state']; ?>
                        >
                            <?php echo $button['text']; ?>
                        </button>
                    <?php endif; ?>
                <?php } ?>

                <!--            </form>-->
            </div>
        </div>
    </div>
<?php endif;