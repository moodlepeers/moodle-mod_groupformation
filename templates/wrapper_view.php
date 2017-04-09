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
 * @author Rene Roepke
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

?>

<link href="styles.css" rel="stylesheet">
<div class="gf_settings_pad">

    <?php if (!is_null($this->_['title'])): ?>
        <div class="gf_pad_header">
            <?php echo $this->_['title'] . (array_key_exists('title_append',$this->_)?$this->_['title_append']:""); ?>
        </div>
    <?php endif; ?>

    <?php foreach ($this->_['templates'] as $template): ?>
        <?php echo $template; ?>
    <?php endforeach; ?>
</div>