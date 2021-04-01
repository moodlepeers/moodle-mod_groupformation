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

            // set spinner
            let spinner = document.getElementById(`spinner-${user.id}`);
            spinner.className = "spinner-border spinner-border-sm";

            require(['core/ajax'],
                /**
                 * test
                 * @param ajax
                 */
                function (ajax) {
                    let promises = ajax.call([
                        {
                            methodname: 'mod_groupformation_delete_answers',
                            args: {users: [{userid: user.id, groupformation: user.current_groupformation}]}
                        }
                    ]);

                    promises[0].done(function (response) {
                        // if deleting answers were successfully
                        spinner.className = "";
                        handleStyleOfTable(user, true)

                        // get dataset
                        let userData = document.getElementById("data").innerText;
                        let data = JSON.parse(userData);

                        // find specific user in dataset
                        let index = data.findIndex(e => e.id == user.id);

                        // change answer count to 0
                        data[index].answer_count = 0;

                        // set new dataset back to the data element
                        (document.getElementById("data")).innerHTML = JSON.stringify(data);

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
        if (confirm(user.excluded == 0
            ? '<?php echo get_string("user_list_exclude_user_msg", "groupformation"); ?>'
            : '<?php echo get_string("user_list_include_user_msg", "groupformation"); ?>'
        )) {

            // set spinner
            let spinner = document.getElementById(`spinner-${user.userid}`);
            spinner.className = "spinner-border spinner-border-sm";

            console.log("exclude", user)
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
                                    userid: user.userid,
                                    groupformation: user.groupformation,
                                    excluded: user.excluded == 0 ? 1 : 0
                                }]
                            }
                        }
                    ]);

                    promises[0].done(function (result) {

                        console.log("test")
                        console.log("result", result);

                        // disable spinner
                        spinner.className = "";

                        // user object from return of webservice
                        let resultUser = result[0];
                        // get excluded button
                        let excludeButton = document.getElementById(`exclude-button-${resultUser.userid}`);
                        // set the new data to button
                        excludeButton.setAttribute('onclick', `excludeUser(
                        ${JSON.stringify(
                            {
                                userid: resultUser.userid,
                                groupformation: resultUser.groupformation,
                                excluded: resultUser.excluded,
                                completed: user.completed,
                                answer_count: user.answer_count,
                                max_answer_count: user.max_answer_count,
                                consent: user.consent,
                            }
                        )})`);


                        user.excluded = resultUser.excluded

                        // set new style of excluded or included user
                        handleStyleOfTable({
                            id: resultUser.userid,
                            current_groupformation: user.groupformation,
                            groupformations: [user]
                        })

                        // get dataset
                        let userData = document.getElementById("data").innerText;
                        let data = JSON.parse(userData);

                        // find specific user in dataset
                        let index = data.findIndex(e => e.id == resultUser.userid);

                        // change status of excluded
                        data[index].excluded = resultUser.excluded;

                        // set new dataset back to the data element
                        (document.getElementById("data")).innerHTML = JSON.stringify(data);
                    }).fail(function (ex) {
                        console.log(ex)
                    });
                });
        } else {
            //
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
        "E-Mail",
        get_string('user_list_consent', 'groupformation'),
        get_string('user_list_progress', 'groupformation'),
        get_string('user_list_submitted', 'groupformation'),
        get_string('user_list_actions', 'groupformation'));

// delete button string
$delete_answers_string = get_string('user_list_delete_answers', 'groupformation');

$exclude_user_string = get_string('user_list_exclude_user', 'groupformation');

$include_user_string = get_string('user_list_include_user', 'groupformation');

$actions_string = get_string('user_list_actions', 'groupformation');

$email_address_message = get_string('user_list_email_copied_message', 'groupformation');

$no_participants_message = get_string('user_list_no_participants_message', 'groupformation');

// wrap everything up in an object
$strings = array(
        'table_columns_names' => $table_column_names,
        'delete_answers' => $delete_answers_string,
        'actions' => $actions_string,
        'exclude_user' => $exclude_user_string,
        'include_user' => $include_user_string,
        'email_message' => $email_address_message,
        'no_participants_message' => $no_participants_message);

?>

<div class="gf_pad_header_small">
    <?php echo get_string("user_list_headline", "groupformation"); ?>
</div>

<div class="gf_pad_content" id="content">
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
    <nav id="table-nav">
        <!-- pagination -->
        <ul class="pagination" id="pagination"></ul>
        <!-- change the amount of users per page  -->
        <div>
            <a style="font-size: x-small margin-bottom: 10px"><?php echo get_string('user_list_user_per_page',
                        'groupformation'); ?></a>
            <div class="table_size" id="table_size"></div>
        </div>
    </nav>
</div>

