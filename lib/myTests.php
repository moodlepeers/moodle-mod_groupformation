<?php
// This file is part of PHP implementation of GroupAL
// http://sourceforge.net/projects/groupal/
//
// GroupAL is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// GroupAL implementations are distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with GroupAL. If not, see <http://www.gnu.org/licenses/>.
//
//  This code CAN be used as a code-base in Moodle 
// (e.g. for moodle-mod_groupformation). Then put this code in a folder
// <moodle>\lib\groupal
/**
 * PHPUnit Tests
<<<<<<< HEAD
 * 
=======
 *
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
<<<<<<< HEAD
 
=======

>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc
require_once 'Criterion/criterion.php';
require_once 'User/user.php';

require_once 'GroupALInputWriter.php';


$criterionsArray = array();

$participantsArray = array();

$participants_ids = array( 123, 234, 345, 456, 567, 678, 789, 890 );

$userRole = "student";

$file = "input.xml";


// 7 example criterions 

$value_name_Bartle = "Bartle";
$value_min_Bartle = 0;
$value_max_Bartle = 1;
$value_isHomogeneous_Bartle = "false";
$value_weight_Bartle = 1;
$value_cnt_Bartle = 4;
$array_Bartle = array (
		'name' => $value_name_Bartle,
		'min' => $value_min_Bartle,
		'max' => $value_max_Bartle,
		'isHomogeneous' => $value_isHomogeneous_Bartle,
		'weight' => $value_weight_Bartle,
		'count' => $value_cnt_Bartle,
		'values' => array(90)
);

$value_name_FeldermanSilver = "FeldermanSilver";
$value_min_FeldermanSilver = 0;
$value_max_FeldermanSilver = 1;
$value_isHomogeneous_FeldermanSilver = "false";
$value_weight_FeldermanSilver = 1;
$value_cnt_FeldermanSilver = 4;
$array_FeldermanSilver = array (
		'name' => $value_name_FeldermanSilver,
		'min' => $value_min_FeldermanSilver,
		'max' => $value_max_FeldermanSilver,
		'isHomogeneous' => $value_isHomogeneous_FeldermanSilver,
		'weight' => $value_weight_FeldermanSilver,
		'count' => $value_cnt_FeldermanSilver,
		'values' => array(90)
);

$value_name_Age = "Age";
$value_min_Age = 0;
$value_max_Age = 1;
$value_isHomogeneous_Age = "true";
$value_weight_Age = 1;
$value_cnt_Age = 1;
$array_Age = array(
		'name' => $value_name_Age,
		'min' => $value_min_Age,
		'max' => $value_max_Age,
		'isHomogeneous' => $value_isHomogeneous_Age,
		'weight' => $value_weight_Age,
		'count' => $value_cnt_Age,
		'values' => array(90)
);

$value_name_Position = "Position";
$value_min_Position = 0;
$value_max_Position = 1;
$value_isHomogeneous_Position = "true";
$value_weight_Position = 1;
$value_cnt_Position = 2;
$array_Position = array (
		'name' => $value_name_Position,
		'min' => $value_min_Position,
		'max' => $value_max_Position,
		'isHomogeneous' => $value_isHomogeneous_Position,
		'weight' => $value_weight_Position,
		'count' => $value_cnt_Position,
		'values' => array(90)
);

$value_name_BigFive = "BigFive";
$value_min_BigFive = 0;
$value_max_BigFive = 1;
$value_isHomogeneous_BigFive = "false";
$value_weight_BigFive = 1;
$value_cnt_BigFive = 5;
$values_BigFive = array(0.2, 0.21, 0.11, 0.2, 0.9);
$array_BigFive = array (
		'name' => $value_name_BigFive,
		'min' => $value_min_BigFive,
		'max' => $value_max_BigFive,
		'isHomogeneous' => $value_isHomogeneous_BigFive,
		'weight' => $value_weight_BigFive,
		'count' => $value_cnt_BigFive,
		'values' => $values_BigFive
);

$value_name_Activity = "Activity";
$value_min_Activity = 0;
$value_max_Activity = 1;
$value_isHomogeneous_Activity = "true";
$value_weight_Activity = 1;
$value_cnt_Activity = 1;
$array_Activity = array (
		'name' => $value_name_Activity,
		'min' => $value_min_Activity,
		'max' => $value_max_Activity,
		'isHomogeneous' => $value_isHomogeneous_Activity,
		'weight' => $value_weight_Activity,
		'count' => $value_cnt_Activity,
		'values' => array(90)
);

$value_name_spellingfailureRate = "spellingfailureRate";
$value_min_spellingfailureRate = 0;
$value_max_spellingfailureRate = 1;
$value_isHomogeneous_spellingfailureRate = "true";
$value_weight_spellingfailureRate = 1;
$value_cnt_spellingfailureRate = 1;
$array_spellingfailureRate = array (
		'name' => $value_name_spellingfailureRate,
		'min' => $value_min_spellingfailureRate,
		'max' => $value_max_spellingfailureRate,
		'isHomogeneous' => $value_isHomogeneous_spellingfailureRate,
		'weight' => $value_weight_spellingfailureRate,
		'count' => $value_cnt_spellingfailureRate,
		'values' => array(90)
);



$criterionsArray = array($array_spellingfailureRate, $array_Activity, $array_BigFive, $array_Position, $array_Age, $array_FeldermanSilver, $array_Bartle);






//test to make participants with all criterions  - successful!
echo 'create participants with IDs: ';
foreach($participants_ids as $key => $value) {
	$participantsArray[$key] = new lib_groupal_user($value, $userRole, $criterionsArray); // Array of Participants
	echo $participantsArray[$key]->getID();
	//echo $participantsArray[$key]->getCriteriaValues();
	echo ",&nbsp;";
}

echo "<br/><br/><br/> Here comes the Participants with their Criterions <br/><br/>";

//test to get all criterions with their values of participants - successful!
$valArr = array();
foreach($participantsArray as $value) {
	echo "Participant with ID ", $value->getID(), '<br/>';

	$valArr = $value->getCriteriaValues();
	foreach($valArr as $key => $value) {
		$tempArray = array();
		$tempArray = $value;
		echo "Criterion:", $key;
		foreach($tempArray as $key => $value) {
			echo "Value", $key, " is ", $value, "\n";
		}
		echo '<br/>';
	}
	echo '<br/><br/>';
}
// $user = new User($pid, $userRole, $criterionsArray);

// $cr = array();
// foreach($criterionsArray as $key => $value) {
// 	$cr[$key] = new Criterion($value);
// 	$cr[$key]->__printArray();
// 	
// }



// $a = "0.275";
// var_dump($a);

// $a = "0.275";
// var_dump($a);



// $testXML = new GroupALInputWriter($file, $participantsArray);

// $testXML->endWriter();

//__printArray($criterionsArray);

// echo $criterionsArray[0]['name'];

echo "<br/> here comes a testArray <br/>";

$testCriterion = new lib_groupal_criterion($array_BigFive);
echo $testCriterion->isHom(), "<br/>";
$ar = array();
$ar = $testCriterion->getValueList();

foreach($ar as $key => $value) {
	echo "Key:", $key, "Value:", $value, "<br />\n";
}

echo "bis Ende gekommen.. ";

?>