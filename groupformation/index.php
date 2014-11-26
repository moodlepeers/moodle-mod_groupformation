<?php

require_once('../../config.php');

$id = required_param('id', PARAM_INT);           // Course ID

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
	print_error('Course ID is incorrect');
}

?>