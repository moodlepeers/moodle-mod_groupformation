<?php


class ValuationTable {
	
	
	private $type, $cat, $token, $header, $legend;
	private $content = array();
	private $options;
	
	public function __construct($tableArray){
		
		
	}
	
	
	public function __printHTML(){
		
		
		
	}
	
	
}




?>

<!-- 
//Array Struktur für Dropdowns
array (size=xx)
    type => string 'dropdown' 
    cat => string 'Bewertung' //categorie
    token => string 'mark' //categorie kürzel, für ...
    header => string 'Bewertung'  //Table Header
    legend => stirng 'none' 
    grades =>                  // Array mit den möglichen Optionen je Frage
                                // Fallunterscheidung erforderlich ob Noten, Punkte oder nur bestehen
      array(size=xx)
        1 => float 1,0
        2 => float 1,3
        3 => float 1,7
        4 => float 2,0
        5 => float 2,3 
        6 => float 2,7
        7 => float 3,0
        8 => float 3,3
        9 => float 3,7
        10 => float 4,0

    content =>  // Array mit Abfragen/Fragen
      array(size=xx)
        0 => string 'Welche Note möchten Sie erreichen?' 
        1 => string 'Welche Note halten Sie für realistisch?' 
        2 => string 'Ab welcher Note wären Sie unzufrieden?' 
        


<div class="col_100">
                <h4 class="view_on_mobile">4. Noten</h4>

                <table class="responsive-table">
                    <colgroup width="" span="">
                        <col class="firstCol">
                        <col width="36%">
                    </colgroup>
                    
                    <thead>
                      <tr>
                        <th scope="col" class="">4. Noten</th>
                        <th scope="col"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">Welche Note m&ouml;chten Sie erreichen?</th>
                        <td data-title="" class="center">
                            <select name="gradeA" id="gradeA">
                                <option value="1.0">1,0</option>
                                <option value="1.3">1,3</option>
                                <option value="1.6">1,7</option>
                                <option value="2.0">2,0</option>
                                <option value="2.3">2,3</option>
                                <option value="2.6">2,7</option>
                                <option value="3.0">3,0</option>
                                <option value="3.3">3,3</option>
                                <option value="3.6">3,7</option>
                                <option value="4.0">4,0</option>
                            </select>
                            </td>
                      </tr>
                      <tr>
                        <th scope="row">Welche Note halten Sie f&uuml;r realistisch?</th>
                        <td data-title="" class="center">
                        	<select name="gradeB" id="gradeB">
                                <option value="1.0">1,0</option>
                                <option value="1.3">1,3</option>
                                <option value="1.6">1,7</option>
                                <option value="2.0">2,0</option>
                                <option value="2.3">2,3</option>
                                <option value="2.6">2,7</option>
                                <option value="3.0">3,0</option>
                                <option value="3.3">3,3</option>
                                <option value="3.6">3,7</option>
                                <option value="4.0">4,0</option>
                            </select>
                          </td>
                      </tr>
                      <tr>
                        <th scope="row">Ab welcher Note w&auml;ren Sie unzufrieden?</th>
                        <td data-title="" class="center">
                        	<select name="gradeC" id="gradeC">
                                <option value="1.0">1,0</option>
                                <option value="1.3">1,3</option>
                                <option value="1.6">1,7</option>
                                <option value="2.0">2,0</option>
                                <option value="2.3">2,3</option>
                                <option value="2.6">2,7</option>
                                <option value="3.0">3,0</option>
                                <option value="3.3">3,3</option>
                                <option value="3.6">3,7</option>
                                <option value="4.0">4,0</option>
                            </select>
                          </td>
                      </tr>

                    </tbody>
                  </table>
                </div>
                
                
  -->