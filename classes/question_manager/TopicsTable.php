<?php


class TopicsTable{

private $type, $cat, $token, $header, $legend;
private $content = array();

public function __construct($tableArray){

}



		public function __printHTML(){
			
			echo '<div class="col_100"' .
				' <h4 class="view_on_mobile">' . $this->header  . '</h4>';
			echo '<p id="topicshead">' . $this->header . '</p>';
			echo '<ul id="sortable_topics">';
			
			$topicCounter = 0;
			foreach ($this->content as $topic){
            	echo '<li id="' . $this->token . $topicCounter . '"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>' . $topic . '</li>';
				$topicCounter++;
			}
			echo '</ul>
                </div>';
		}
		

}
?>




<!-- 
//Array Struktur für Topics
array (size=xx)
    type => string 'topics' (length=5)
    cat => string 'Themenwahl' (length=xx) //categorie
    token => string 'topic' (length=xx) //categorie kürzel, für unic ids
    header => string 'Bitte sortieren Sie die zur Wahl stehenden Themen entsprechend Ihrer Pr&auml;ferenz, beginnend mit Ihrem bevorzugten Thema.'  //Table Header
    legend => stirng 'none' (length=xx)
    content =>  // Array mit Themen
        array(size=xx)
          0 => string 'Thema 1' 
          1 => string 'Thema 2' 
          2 => string 'Thema 3' 
          3 => string 'Thema 4' 
          4 => string 'Thema 5' 
          
          
          
          <div class="col_100">    
                    <h4 class="view_on_mobile">2. Themenwahl</h4>
                    <p id="topicshead">2. Bitte sortieren Sie die zur Wahl stehenden Themen entsprechend Ihrer Pr&auml;ferenz, beginnend mit Ihrem bevorzugten Thema. </p>
                    <ul id="sortable_topics">
                      <li id="topic_1" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 1</li>
                      <li id="topic_2" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 2</li>
                      <li id="topic_3" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 3</li>
                      <li id="topic_4" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 4</li>
                      <li id="topic_5" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 5</li>
                      <li id="topic_6" class=""><span class="ui-icon ui-icon-arrowthick-2-n-s"></span>Thema 6</li>
                    </ul>
                </div>
                
  -->