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
 * Overview statistics template
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

<?php if (array_key_exists('questionnaire_answer_stats', $this->_)): ?>
    <div class="gf_pad_header_small">
        <?php echo $this->_['questionnaire_answer_stats']; ?>
    </div>
    <div class="gf_pad_content">
        <p>
            <?php if(!$this->_['ask_for_topics']): ?>
                <?php echo get_string('answers_for_eval_text', 'groupformation'); ?>
            <?php endif; ?>
        </p>
        <?php foreach ($this->_['survey_states'] as $row) { ?>
            <div class="grid row_highlight">
                <div class="col_m_100"><?php echo $row; ?></div>
            </div>
        <?php } ?>
    </div>
<?php endif;