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

        console.log(user)

        // show alert window
        if (confirm('<?php echo get_string("user_list_delete_answers_msg", "groupformation"); ?>')) {

            // set spinner
            let spinner = document.getElementById(`spinner-${user.userid}`);
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
                            args: {users: [{userid: user.userid, groupformation: user.groupformation}]}
                        }
                    ]);

                    promises[0].done(function (response) {
                        // if deleting answers were successfully

                        spinner.className = "";
                        handleStyleOfTable(user, true)

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
                                }]
                            }
                        }
                    ]);

                    promises[0].done(function (result) {

                        // disable spinner
                        spinner.className = "";

                        // user object from return of webservice
                        let user = result[0];
                        // get excluded button
                        let excludeButton = document.getElementById(`exclude-button-${user.userid}`);
                        // set the new data to button
                        excludeButton.setAttribute('onclick', `excludeUser(
                        ${JSON.stringify(
                            {
                                userid: user.userid,
                                groupformation: user.groupformation,
                                excluded: user.excluded
                            }
                        )})`);

                        // set new style of excluded or included user
                        handleStyleOfTable(result[0])
                    }).fail(function (ex) {
                        console.log(ex)
                    });
                });
        } else {
            console.log("else")
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

