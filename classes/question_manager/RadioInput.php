<?php

class RadioInput {

	private $category;
	private $qnumber;
	private $question;
	private $optArray = array();
	
	
	public function __construct($q, $cat, $qnumb){
		$this->question = $q[1];
		$this->optArray = $q[2];
		$this->category = $cat;
		$this->qnumber = $qnumb;
	}
	
	
	
	public function __printHTML($q, $cat, $qnumb){
		$this->question = $q[1];
		$this->optArray = $q[2];
		$this->category = $cat;
		$this->qnumber = $qnumb;
		
		echo '<tr>';
		echo '<th scope="row">' . $this->question . '</th>';

		$radioCounter = 0;
		foreach ($this->optArray as $option){
			echo '<td data-title="' . $option .
				'" class="radioleft select-area"><input type="radio" name="' .
				$this->category . $this->qnumber .
				'" value="' . $radioCounter . '"/></td>';
			$radioCounter++;
		}
		echo '</tr>';
	}
	
	
}	
	
?>