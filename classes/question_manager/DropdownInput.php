<?php


class DropdownInput {
	
	
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
	
	
	
	public function __printHTML($q, $cat, $qnumb, $hasAnswer){
		$this->question = $q[1];
		$this->optArray = $q[2];
		$this->category = $cat;
		$this->qnumber = $qnumb;
		
		
		echo '<tr>';
		echo '<th scope="row">' . $this->question . '</th>';
		echo '<td class="center">
				<select name="grade'. $this->qnumber  .'" id="grade'. $this->qnumber  .'">';
		
		foreach ($this->optArray as $option){
			echo '<option value="'. $option .'">'. $option .'</option>';
		}
		
		echo '</select>
			</td>
		</tr>';
		
		if($hasAnswer){
			//$answer ist die position im optionArray von der Antwort
			$answer = $q[3];
		}
	}
}

?>



<!--					 <tr> -->
<!--                         <th scope="row">Welche Note m&ouml;chten Sie erreichen?</th> -->

<!--                         <td class="center"> -->
<!--                             <select name="gradeA" id="gradeA"> -->
<!--                                 <option value="1.0">1,0</option> -->
<!--                                 <option value="1.3">1,3</option> -->
<!--                                 <option value="1.6">1,7</option> -->
<!--                                 <option value="2.0">2,0</option> -->
<!--                                 <option value="2.3">2,3</option> -->
<!--                                 <option value="2.6">2,7</option> -->
<!--                                 <option value="3.0">3,0</option> -->
<!--                                 <option value="3.3">3,3</option> -->
<!--                                 <option value="3.6">3,7</option> -->
<!--                                 <option value="4.0">4,0</option> -->
<!--                             </select> -->
<!--                             </td> -->
<!--                       </tr> -->