<?php

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/name/lib.php');

class mod_groupformation_mod_form extends moodleform_mod {

	function definition() {
		global $CFG, $DB, $OUTPUT;

		$mform =& $this->_form;
		//...
		
	}
}

?>