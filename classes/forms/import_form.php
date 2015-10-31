<?php
require_once ("$CFG->libdir/formslib.php");
class mod_groupformation_import_form extends moodleform {
	// Add elements to form
	public function definition() {
		global $CFG;
		$maxbytes = 2 * 2000000; // *pow(10,8);
		$mform = $this->_form; // Don't forget the underscore!
		$mform->addElement ( 'filepicker', 'userfile', get_string ( 'file' ), null, array (
				'maxbytes' => $maxbytes,
				'accepted_types' => '*.xml' 
		) );
		
		// $mform->addElement('submit', 'btn1', get_string("submit"));
		
		$buttonarray = array ();
		
		$buttonarray [] = & $mform->createElement ( 'submit', 'submit', get_string ( 'submit' ) );
		$buttonarray [] = & $mform->createElement ( 'submit', 'cancel', get_string ( 'cancel' ) );
		
		$mform->addGroup ( $buttonarray, 'buttonar', '', array (
				' ' 
		), false );
		
		$mform->addElement('hidden','cmid',0);
		$mform->setType('cmid',PARAM_TEXT);
		
		$mform->closeHeaderBefore ( 'buttonar' );
	}
	// Custom validation should be added here
	function validation($data, $files) {
		return array ();
	}
	
	/**
	 * Adds error to element
	 * 
	 * @param string $element
	 * @param string $msg
	 */
	function set_error($element,$msg){
		$this->_form->setElementError($element,$msg);
	}
}