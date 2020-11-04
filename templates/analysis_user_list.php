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

    /**
     * send ajax call to moodle web service and updates the table
     * @param user - user element
     */
    function deleteAnswers(user) {
        // prevent of reloading the page
        event.preventDefault();

        // show alert window
        if (confirm('<?php echo get_string("user_list_delete_answers_msg", "groupformation"); ?>')) {
            require(['core/ajax'],
                /**
                 * test
                 * @param ajax
                 */
                function (ajax) {
                    let promises = ajax.call([
                        {
                            methodname: 'mod_groupformation_delete_answers',
                            args: {users: [{userid: user[0].userid, groupformation: user[0].groupformation}]}
                        }
                    ]);

                    promises[0].done(function (response) {
                        // if deleting answers were successfully

                        // get dataset
                        let userData = document.getElementById("data").innerText;
                        let data = JSON.parse(userData);

                        // find specific user in dataset
                        let index = data.findIndex(e => e[0].userid === user[0].userid);


                        // delete answers array from dataset
                        data[index].splice(0, 1);

                        // set new dataset back to the data element
                        (document.getElementById("data")).innerHTML = JSON.stringify(data);


                        // get all table elements
                        let elements = document.getElementsByTagName('TD');

                        for (let item of elements) {
                            // find element from selected user
                            if (JSON.parse(item.getAttribute("data")) === user[0].userid) {

                                // get name of element
                                let name = JSON.parse(item.getAttribute("name"));

                                // set the new value and updating the table
                                switch (name) {
                                    case "questionaire":
                                        item.style.width = "0%";
                                        item.innerHTML = 0;
                                        break;
                                    case "completed":
                                        item.innerHTML = renderXIcon();
                                        break;
                                    default:
                                }
                            }
                        }

                        // find buttons from user table
                        let buttons = document.getElementsByClassName('table-button');
                        for (let item of buttons) {
                            if ((JSON.parse(item.getAttribute("data")))[0].userid === user[0].userid) {
                                // disable button
                                item.disabled = true;
                            }
                        }

                    }).fail(function (ex) {
                        console.log(ex)
                    });
                });
        } else {
            // Do nothing!

        }

    }

</script>


<script type="text/javascript">

    /**
     * send ajax call to moodle web service and updates the table
     * @param user - user element
     */
    function excludeUser(user) {

        // prevent of reloading the page
        event.preventDefault();

        // show alert window
        if (confirm(user[0].excluded == 0
            ? '<?php echo get_string("user_list_exclude_user_msg", "groupformation"); ?>'
            : '<?php echo get_string("user_list_include_user_msg", "groupformation"); ?>'
        )) {

            require(['core/ajax'],
                /**
                 * ajax call to exclude user
                 * @param ajax
                 */
                function (ajax) {
                    let promises = ajax.call([
                        {
                            methodname: 'mod_groupformation_exclude_users',
                            args: {
                                users: [{
                                    userid: user[0].userid,
                                    groupformation: user[0].groupformation,
                                    excluded: user[0].excluded == 0 ? 1 : 0
                                }]
                            }
                        }
                    ]);

                    promises[0].done(function (result) {
                        // if excluding user was successfully

                        // get dataset
                        let userData = document.getElementById("data").innerText;
                        let data = JSON.parse(userData);

                        // find specific user in dataset
                        let index = data.findIndex(e => e[0].userid === user[0].userid);

                        // delete answers array from dataset
                        data[index].splice(0, 1);

                        // set new dataset back to the data element
                        (document.getElementById("data")).innerHTML = JSON.stringify(data);

                        // get all table elements
                        let elements = document.getElementsByTagName('TR');

                        for (let item of elements) {
                            // find element from selected user
                            if (JSON.parse(item.getAttribute("data")) == user[0].userid) {
                                // get name of element
                                item.style.backgroundColor = result[0].excluded === 1 ? "lightgrey" : null;
                            }
                        }


                        // disable button
                        let excludeButtons = document.getElementById(`exclude-button-${user[0].userid}`);
                        excludeButtons.disabled = true;

                        // set color to grey if the user gets excluded or black if the user gets included
                        let number = document.getElementById(`number-${user[0].userid}`);
                        number.style.color = result[0].excluded === 1 ? "darkgrey" : null;

                        let firstname = document.getElementById(`firstname-${user[0].userid}`);
                        firstname.style.color = result[0].excluded === 1 ? "darkgrey" : null;

                        let lastname = document.getElementById(`lastname-${user[0].userid}`);
                        lastname.style.color = result[0].excluded === 1 ? "darkgrey" : null;


                    }).fail(function (ex) {
                        console.log(ex)
                    });
                });
        } else {
            // Do nothing!

        }

    }

</script>

<!-- create object with all strings to use in js file-->
<?php

// table column names
$table_column_names = array(
        "#",
        get_string('user_list_firstname', 'groupformation'),
        get_string('user_list_lastname', 'groupformation'),
        get_string('user_list_consent', 'groupformation'),
        get_string('user_list_progress', 'groupformation'),
        get_string('user_list_submitted', 'groupformation'),
        get_string('user_list_actions', 'groupformation'));

// delete button string
$delete_answers_string = get_string('user_list_delete_answers', 'groupformation');

$exclude_user_string = get_string('user_list_exclude_user', 'groupformation');

$include_user_string = get_string('user_list_include_user', 'groupformation');

$actions_string = get_string('user_list_actions', 'groupformation');

// wrap everything up in an object
$strings = (object) array(
        'table_columns_names' => $table_column_names,
        'delete_answers' => $delete_answers_string,
        'actions' => $actions_string,
        'exclude_user' => $exclude_user_string,
        'include_user' => $include_user_string);

?>

<div class="gf_pad_header_small">
    <?php echo get_string("user_list_headline", "groupformation"); ?>
</div>

<div class="gf_pad_content">
    <!-- saves the user id for the action button -->
    <script id="user" data-uid=""></script>
    <!-- push the data of all users in json format to js file -->
    <script id="data"><?php echo json_encode($this->_['users']); ?></script>
    <!-- push all needed strings to js file in json format -->
    <script id="strings"><?php echo json_encode($strings); ?></script>
    <!-- table content get added in js file -->
    <div id="table_content">
    </div>


    <!-- add nav field -->
    <nav>
        <!-- pagination -->
        <ul class="pagination" id="pagination"></ul>
        <!-- change the amount of users per page  -->
        <ul class="table_size" id="table_size"></ul>
    </nav>
</div>
