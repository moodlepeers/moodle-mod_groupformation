<?php

class question_table_header {
	
	private $tableType;
	private $headerOptArray;
	
	public function __printHTML($category, $tableType, $headerOptArray){
		
		$this->tableType = $tableType;
		$this->headerOptArray = $headerOptArray;
		
		if($tableType == 'type_topics'){
			// HTML unordered list element - <ul>
			echo '<div id="topicshead">'.get_string('topics_question','groupformation').'</div>
								<ul class="sortable_topics">';
		
		}else{
			// HTML table element - <table>
			echo '<table class="responsive-table">' .
					'<colgroup>
											<col class="firstCol">
										<colgroup>';
				
			// table - Header
			echo '<thead>
			                      <tr>
			                        <th scope="col">'. (($tableType=='type_knowledge')?get_string('knowledge_question','groupformation'):get_string('category_'.$category,'groupformation')) . '</th>';
			if($tableType == 'radio'){
				$headerSize = count($this->headerOptArray);
		
				echo '<th scope="col" colspan="'. $headerSize .'"><span style="float:left">'. $headerOptArray[0] .'</span>
																						<span style="float:right">'. $headerOptArray[$headerSize - 1] .'</span></th>';
			}
			else if($tableType == 'type_knowledge'){
				echo '<th scope="col"><div class="legend">'.get_string('knowledge_scale','groupformation').'</div></th>';
			}else{
				echo    '<th scope="col"></th>';
			}
				
		
			echo '</tr>
			                    </thead>
			                    <tbody>';
		}
	}
}

?>