<?php

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class form_dummy extends moodleform {
	//Add elements to form
	public function definition() {
		global $CFG;

		$mform = $this->_form; // Don't forget the underscore!
	}
}

?>