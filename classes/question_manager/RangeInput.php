<?php


class RangeInput{
	
	private $category;
	private $qnumber;
	private $question;	
	
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
		echo '<td data-title="0 = kein Vorwissen, 100 = sehr viel Vorwissen " class="range">
					<span class="">0</span>
					<input type="range" name="'. $this->category . $this->qnumber .'" min="0" max="100" value="0" />
					<span class="">100</span>
					</td>';
		echo '</tr>';
		
	}
}


?>
