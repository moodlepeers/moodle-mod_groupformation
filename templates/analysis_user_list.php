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
?>

<script type="text/javascript">

    function deleteAnswers(user) {
        event.preventDefault();

        if (confirm('Wollen Sie die Antworten von ' + user[1].firstname + " " + user[1].lastname + " löschen?")) {
            require(['core/ajax'],
                /**
                 * test
                 * @param ajax
                 */
                function (ajax) {
                    let promises = ajax.call([
                        {
                            methodname: 'local_groupformation_delete_answers',
                            args: {users: [{userid: user[0].userid, groupformation: user[0].groupformation}]}
                        }
                    ]);

                    promises[0].done(function (response) {
                        // do something with the response -> reload table
                        console.log(response);
                    }).fail(function (ex) {
                        console.log(ex)
                    });
                });
        } else {
            // Do nothing!

        }

    };

</script>


<div class="gf_pad_header_small">
    <?php echo "List of users"; ?>
</div>


<div class="gf_pad_content">
    <script id="user" data-uid=""></script>
    <script id="data"><?php echo json_encode($this->_['users']); ?></script>
    <div id="table_content">

    </div>


    <nav aria-label="...">
        <ul class="pagination" id="pagination">

        </ul>
    </nav>
</div>
