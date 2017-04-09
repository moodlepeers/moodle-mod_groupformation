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
 * @package block_pseudolearner
 * @author Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $USER;
?>

<div class="gf_pad_header_small">
    <?php echo get_string('topics_statistics', 'groupformation'); ?>
</div>

<div class="gf_pad_content">
    <p>
        <?php echo get_string('topics_statistics_description', 'groupformation'); ?>
    </p>
    <p>
        <div>
            <table class="table table-sm table-striped table-responsive">
                <thead>
                <tr>
                    <th>#</th>
                    <th class="col-md-2"><?php echo get_string('topics_dummy', 'groupformation');?></th>
                    <th><?php echo get_string('topics_statistics_score', 'groupformation')?></th>
                </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach($this->_['topics'] as $topic): ?>
                        <tr>
                            <th scope="row"><?php echo $i; ?></th>
                            <td>
                                <?php echo $topic->name; ?>
                            </td>
                            <td>
                                <?php echo round($topic->score,2); ?>
                            </td>
                        </tr>
                        <?php $i+=1; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </p>
</div>