<?php

/**
 * An XML Writer for student
 *
 * @author Rene Roepke
 *
 */
class mod_groupformation_xml_writer {
	private $writer;
	
	/**
	 * Creates instance of participant_writer
	 * 
	 * @param string $uri        	
	 */
	public function __construct() {
		$this->writer = new XMLWriter ();
		$this->writer->openMemory();
	}
	
	/**
	 * Creates XML file with answers
	 * 
	 * @param int $userid
	 * @param int $groupformationid
	 * @return boolean
	 */
	public function write($userid = null, $groupformationid = null, $categories = null) {
		global $USER;
		if (is_null ( $userid ) || is_null ( $groupformationid )|| is_null ( $categories )) {
			return false;
		}
		
		$writer = $this->writer;
		
		$user_manager = new mod_groupformation_user_manager ( $groupformationid );
		
		$writer->openMemory ();
		
		$writer->startDocument ( '1.0', 'utf-8' );
		$writer->setIndent ( true );
		$writer->setIndentString ( "    " );
		
		$writer->startElement ( 'answers' ); // <answers ..>
		
		$writer->writeAttribute ( 'userid', '' . $userid );
		
		$writer->startElement ( 'categories' ); // <categories ..>
		
		foreach ( $categories as $category ) {
			$writer->startElement ( 'category' );
			$writer->writeAttribute ( 'name', $category );
			
			$answers = $user_manager->get_answers ( $userid, $category );
			
			$this->write_answers ( $answers );
			
			$writer->endElement (); // </category>
		}
		
		$writer->endElement (); // </categories>
		
		$writer->endElement (); // </answers>
		
		$writer->endDocument ();

		$content = $writer->outputMemory(false);
		
		return $content;
	}
	
	/**
	 * Writes answers in xml format
	 * 
	 * @param unknown $answers
	 */
	private function write_answers($answers) {
		foreach ( $answers as $answer ) {
			
			$this->write_answer ( $answer );
		}
	}
	
	/**
	 * Writes an answer in xml format
	 * 
	 * @param stdClass $answer
	 */
	private function write_answer($answer) {
		$writer = $this->writer;
		
		$writer->startElement ( 'answer' );
		$writer->writeAttribute ( 'questionid', $answer->questionid );
		$writer->writeAttribute ( 'value', $answer->answer );
		$writer->endElement ();
	}
	
}