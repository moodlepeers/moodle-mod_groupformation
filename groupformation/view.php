<?php
require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);    // Course Module ID

if(!$cm = get_coursemodule_from_id('groupformation', $id, 0, false, MUST_EXIST)) {
//if (!$cm = get_coursemodule_from_id('groupformation', $id)) {
	print_error('Course Module ID was incorrect'); // NOTE this is invalid use of print_error, must be a lang string id
}

if(!$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST)) {
//if (!$course = $DB->get_record('course', array('id'=> $cm->course))) {
	print_error('course is misconfigured');  // NOTE As above
}

if (!$groupformation = $DB->get_record('groupformation', array('id'=> $cm->instance), '*', MUST_EXIST)) {
	print_error('course module is incorrect'); // NOTE As above
}

require_login($course, true, $cm);
$PAGE->set_url('/mod/groupformation/view.php', array('id' => $cm->id));
$PAGE->set_title(get_string('title', 'groupformation'));
$PAGE->set_heading(get_string('header', 'groupformation'));

echo $OUTPUT->header();


$mform = new form_dummy();
//Form processing and displaying is done here
if ($mform->is_cancelled()) {
	//Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
	//In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
	// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
	// or on the first display of the form.

	//Set default data (if any)
	$mform->set_data($toform);
	//displays the form
	$mform->display();
}

echo $OUTPUT->footer();

?>