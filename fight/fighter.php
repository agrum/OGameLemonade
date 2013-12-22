<?php
include '../include/fight.class.php';

$report = "";

$g_metal = 4;
$g_cristal = 2;
$g_deut = 1;

//Check integrity
$groupAArr;
$i = 0;
foreach($short as $s)
	if(array_key_exists($s."A", $_POST) && is_numeric($_POST[$s."A"]) && $_POST[$s."A"] > 0)
		if($_POST[$s."A"] > 0)
			$groupAArr[$i++] = new Group($model[$s], $_POST[$s."A"]);
			
$groupDArr;
$i = 0;
foreach($short as $s)
	if(array_key_exists($s."D", $_POST) && is_numeric($_POST[$s."D"]) && $_POST[$s."D"] > 0)
		if($_POST[$s."D"] > 0)
			$groupDArr[$i++] = new Group($model[$s], $_POST[$s."D"]);

if(!isset($groupAArr) || !isset($groupDArr))
	return;
	
$attFleet = new Fleet($groupAArr, "Attaquant");
$defFleet = new Fleet($groupDArr, "Defenseur");
$fight = new Fight($attFleet, $defFleet);

//Report writing
$report = '<table><tr><td>

</td><td valign="top">

<div class="right">
'.nl2br($fight->encounterInformation()).'
</div>

</td></tr></table>';

?>
