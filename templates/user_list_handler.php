<?php

require_once($CFG->dirroot . '/mod/groupformation/classes/view_controller/basic_view_controller.php');

//Retrieve the string, which was sent via the POST parameter "user"
$user = $_POST['data'];

//Decode the JSON string and convert it into a PHP associative array.
$decoded = json_decode($user, true);

//var_dump the array so that we can view it's structure.
var_dump($decoded);
//
$usermanager = new mod_groupformation_user_manager($decoded[0]['groupformation']);
$usermanager->delete_answers($decoded[1]['id']);

return 1;

?>