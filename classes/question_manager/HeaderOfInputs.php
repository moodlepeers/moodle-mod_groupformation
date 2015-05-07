<?php

class HeaderOfInput {
	
	private $tableType;
	private $headerOptArray;
	
	public function __printHTML($category, $tableType, $headerOptArray){
		
		$this->tableType = $tableType;
		$this->headerOptArray = $headerOptArray;
		
		if($tableType == 'typThema'){
			// HTML unordered list element - <ul>
			echo '<p id="topicshead">Bitte sortieren Sie die zur Wahl stehenden Themen entsprechend Ihrer Pr&auml;ferenz, beginnend mit Ihrem bevorzugten Thema. </p>
								<ul id="sortable_topics">';
		
		}else{
			// HTML table element - <table>
			echo '<table class="responsive-table">' .
					'<colgroup>
											<col class="firstCol">
										<colgroup>';
				
			// table - Header
			echo '<thead>
			                      <tr>
			                        <th scope="col">'. $category . '</th>';
			if($tableType == 'radio'){
				$headerSize = count($this->headerOptArray);
		
				echo '<th scope="col" colspan="'. $headerSize .'"><span style="float:left">'. $headerOptArray[0] .'</span>
																						<span style="float:right">'. $headerOptArray[$headerSize - 1] .'</span></th>';
			}
			else if($tableType == 'typVorwissen'){
				echo '<th scope="col"><div class="legend">0 = kein Vorwissen, 100 = sehr viel Vorwissen</div></th>';
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