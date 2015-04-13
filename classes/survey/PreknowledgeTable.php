<?php


class preKnowledge{
	
	private $type, $cat, $token, $header, $legend;
	private $options = array();
	private $content = array();
	
	
	
	public function __construct($tableArray){

	}
	
	
	public function __printHTML(){
		
		echo '<div class="col_100"' .
				' <h4 class="view_on_mobile">' . $this->header  . '</h4>';
		
		echo '<table class="responsive-table">
                    <colgroup>
                        <col class="firstCol">
                        <col width="36%"> 
                    </colgroup>';
				
		echo '<thead>
                      <tr>
                          <th scope="col" class="">' . $this->header . '</th>

                        <th scope="col"><div class="legend">' . $this->legend . '</div></th>
                      </tr>
                    </thead>';
		
		echo '<tbody>';
			$knowledgeCounter = 0;
			foreach($this->content as $knowledge){
				echo' <tr>
				<th scope="row">' . $knowledge . '</th>
				<td data-title="' .$this->legend . '" class="range"><span class="">0</span><input type="range" name="' . $this->token . $knowledgeCounter . '" min="0" max="100" value="0" /><span class="">100</span></td>
				</tr>';
				$knowledgeCounter++;
			}
         echo '</tbody>
         	</table>
         </div>';
				
	}
	
}


?>


<!-- 
//Array Struktur für Likert-Scala
array (size=xx)
    type => string 'scales' 
    cat => string 'Vorwissen'  //categorie
    token => string 'prekn' //categorie kürzel, für ...
    header => string 'Wie sch&auml;tzen Sie Ihr pers&ouml;nliches Vorwissen in folgenden Gebieten ein?'  //Table Header
    legend => stirng '0 = kein Vorwissen, 100 = sehr viel Vorwissen' 
    options =>  // Array mit den möglichen Optionen je Frage
      array(size=xx)    // unnötig, wenn min und max immer 0 und 100 sind
        min => int 0 
        max => int 100 
    content =>  // Array mit Themen
      array(size=xx)
        0 => string 'Gebiet 1' 
        1 => string 'Gebiet 2' 
        2 => string 'Gebiet 3' 
        3 => string 'Gebiet 4' 
        4 => string 'Gebiet 5'

        
 <div class="col_100">query string:<span id="order"></span></div>
                
                <div class="col_100">
                <h4 class="view_on_mobile">3. Wie sch&auml;tzen Sie Ihr pers&ouml;nliches Vorwissen in folgenden Gebieten ein?</h4>
    
                <table class="responsive-table">
                    <colgroup width="" span="">
                        <col class="firstCol">
                        <col width="36%">
                    </colgroup>
                    
                    <thead>
                      <tr>
                          <th scope="col" class="">3. Wie sch&auml;tzen Sie Ihr pers&ouml;nliches Vorwissen in folgenden Gebieten ein?</th>

                        <th scope="col"><div class="legend">0 = kein Vorwissen, 100 = sehr viel Vorwissen</div></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <th scope="row">Beispiel 1</th>
                        <td data-title="0 = kein Vorwissen, 100 = sehr viel Vorwissen " class="range"><span class="">0</span><input type="range" min="0" max="100" value="0" /><span class="">100</span></td>
                      </tr>
                      <tr>
                        <th scope="row">Beispiel 2</th>
                        <td data-title="0 = kein Vorwissen, 100 = sehr viel Vorwissen " class="range"><span class="">0</span><input type="range" min="0" max="100" value="0" /><span class="">100</span></td>
                      </tr>
                      <tr>
                        <th scope="row">Beispiel 3</th>
                        <td data-title="0 = kein Vorwissen, 100 = sehr viel Vorwissen " class="range"><span class="">0</span><input type="range" min="0" max="100" value="0" /><span class="">100</span></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
        
        
        
        
        
        
        
 -->
