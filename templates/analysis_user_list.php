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
 * Overview info template
 *
 * @package     mod_groupformation
 * @author      Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic, Stefan Jung
 * @copyright   2015 MoodlePeers
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//if (!defined('MOODLE_INTERNAL')) {
//    die ('Direct access to this script is forbidden.');
//}

//Retrieve the string, which was sent via the POST parameter "user"
$user = $_POST['data'];

//Decode the JSON string and convert it into a PHP associative array.
$decoded = json_decode($user, true);

if($decoded["function"]){
    test($decoded);
}
//var_dump the array so that we can view it's structure.
var_dump($decoded);
//


function test($decoded){
    echo("success");

    $usermanager = new mod_groupformation_user_manager($this->groupformationid);
    $usermanager->delete_answers($decoded[1]['id']);
}

?>

<div class="gf_pad_header_small">
    <?php //echo get_string('user_list', 'groupformation'); ?>
    <?php echo "List of users"; ?>
</div>


<div class="gf_pad_content">
    <script id="data"><?php echo json_encode($this->_['users']); ?></script>
    <div id="table_content">

    </div>


    <nav aria-label="...">
        <ul class="pagination" id="pagination">

        </ul>
    </nav>
</div>
