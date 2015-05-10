<?php


class TopicsTable{

	private $category;
	private $qnumber;
	private $question;	
	
	public function __construct($q, $cat, $qnumb){
		$this->question = $q[1];
		$this->optArray = $q[2];
		$this->category = $cat;
		$this->qnumber = $qnumb;
	}
	
	
	
	public function __printHTML($q, $cat, $qnumb, $hasAnswer){
		$this->question = $q[1];
		$this->optArray = $q[2];
		$this->category = $cat;
		$this->qnumber = $qnumb;
		
// 		echo '<li id="'. $this->category . $this->qnumber .'"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' . $this->question . '</li>';
		echo '<li id="'. $this->category . $this->qnumber .'">' . $this->question . '</li>';
		
		if($hasAnswer){
			//$answer ist die position im optionArray von der Antwort
			$answer = $q[3];
		}
		
	}
}
?>


