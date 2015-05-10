

<?php

class RadioTable {
	
// 	private $categorie;
// 	private $token;					//categorie kürzel, für radiobutton name property, um radiobuttons je Frage zu gruppieren
// 	private $optArray = array();	// Array mit den möglichen Optionen je Frage. Jede Frage hat gleiche Optionen
// 	private $optNumb;
	
// 	private $questionsArray = array();

	private $category;
	private $qnumber;
	private $question;
	private $optArray = array();
	
	
	public function __construct($q){
// 		$this->question = $q[1];
// 		$this->optArray = $q[2];

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
	
	
	
	
	
	
	//$tableArray Bsp. siehe unten
// 	public function __construct($tableArray){
// 		$this->categorie = $tableArray[0][1];
// 		$this->token = $tableArray[0][2];
// 		$this->optArray = $tableArray[0][3];
// 		$this->optNumb = count($tableArray[0][3]);
		
// 		$this->questionsArray = $tableArray[1];
// 	}

	
	
// 	public function __printHTML(){
// 		echo '<div class="col_100"' .
// 				' <h4 class="view_on_mobile">' . $this->categorie . '</h4>';
		
// 		echo '<table class="responsive-table">' . 	//TODO @EG : CSS clase "firstCol" fehlt, width wird auf Desktop/Mobile reagieren
// 													//TODO @Nora || EG : 	je nach Anzahl($optNumb) werden die entsprechenden widths in % angefügt
// 													// 						in diesem Fall: 2-7 collumn sind jeweils 36%/6, 1 collumn hätte keine width sondern nur die classe="firstCol"
// 					'<colgroup>				
// 						<col width="64%" class="firstCol">
//                         <col width="6%">
//                         <col width="6%">
//                         <col width="6%">
//                         <col width="6%">
//                         <col width="6%">
//                         <col width="6%">
// 					</colgroup>' .
// 					'<thead>
// 						<tr>
// 							<th scope="col">' . $this->categorie . '</th>';
			
// 		foreach ($this->optArray as $option){
// 			echo '<th scope="col">' . $option . '</th>';
// 		}
			
// 		echo '</tr>
//             	</thead>
//             	<tbody>';
			
// 		$questionCounter = 0;
// 		foreach ($this->questionsArray as $question){
// 			echo '<tr>';
// 			echo '<th scope="row">' . $question . '</th>';
			
// 			$radioCounter = 0;
// 			foreach ($this->optArray as $option){
// 				echo '<td data-title="' . $option . 
// 					'" class="radioleft select-area"><input type="radio" name="' . 
// 					$this->categorie . $questionCounter . 
// 					'" value="' . $radioCounter . '"/></td>';
// 				$radioCounter++;
// 			}
// 			$questionCounter++;
// 			echo '</tr>';
// 		}
			
// 		echo '</tbody>
//                     </table>
//                 </div>'; // /.col_100
		
// 	}
}

?>


<!-- 


//Neue Struktur $question // $question ist eigentlich eine Kategorie (Bsp.: Persönlichkeik) mit allen dazugehörigen Fragen

array (size=xx)
  0 => 
  	array (size=3)
  		0 => string 'radio' (length=5)
      	1 => string 'persönlichkeit' (length=xx) //categorie
      	2 => string 'pers' (length=xx) //categorie kürzel, für radiobutton name property, um radiobuttons zu gruppieren
      	3 =>  // Array mit den möglichen Optionen je Frage
      		array(size=xx)
      			0 => string '1' (length=1)
          		1 => string '2' (length=1)
		        2 => string '3' (length=1)
		        3 => string '4' (length=1)
		        4 => string '5' (length=1)
		        5 => string '6' (length=1)
  1 => 
  	array (size=xx)
  		0 => string 'I am satisfied at the most with my performance when I don't have to count with the help of others.' (length=98)
		1 => string 'I obtain the best results when I work alone.' (length=44)
  		2 => string 'At work, it is important to me not to agree constantly with others.' (length=67)
		3 => string 'I preffer to work alone.' (length=24)
		4 => string 'I am convinced that almost all current problems can only be overcome in teams.' (length=78)
		5 => string 'If one wants to perform a task optimally, he better does it alone.' (length=66)
  		6 => string 'At almost all tasks working in teams requires more time than it is neccessary.' (length=78)
		7 => string 'My friends say I am solitary fighter.' (length=37)
		8 => string 'I can best develop my skills when working together with others.' (length=63)
		9 => string 'When I work on a task I like to do as much as possible without getting help from others.' (length=88)
  		10 => string 'When I plan something, I first think about who else could be in the team.' (length=73)
		11 => string 'Constantly have to agree with others goes against my working style.' (length=67)
		
 		
  	

// Ursprüngliche Struktur $question

array (size=27)
  0 => 
    array (size=3)
      0 => string 'radio' (length=5)
      1 => string 'I am satisfied at the most with my performance when I don't have to count with the help of others.' (length=98)
      2 => 
        array (size=6)
          0 => string 'disagree' (length=8)
          1 => string '' (length=0)
          2 => string '' (length=0)
          3 => string '' (length=0)
          4 => string '' (length=0)
          5 => string 'agree' (length=5)
  1 => 
    array (size=3)
      0 => string 'radio' (length=5)
      1 => string 'I obtain the best results when I work alone.' (length=44)
      2 => 
        array (size=6)
          0 => string 'disagree' (length=8)
          1 => string '' (length=0)
          2 => string '' (length=0)
          3 => string '' (length=0)
          4 => string '' (length=0)
          5 => string 'agree' (length=5)
  
  --> 

