<?php
include_once '../include/fight.class.php';

$report = "";

$g_metal = 4;
$g_cristal = 2;
$g_deut = 1;

//Check integrity
$techA = array(0, 0, 0);
if(isset($_POST['weaponA']) && is_numeric($_POST['weaponA']) && $_POST['weaponA'] > 0)
	$techA[0] = $_POST['weaponA'];
if(isset($_POST['shieldA']) && is_numeric($_POST['shieldA']) && $_POST['shieldA'] > 0)
	$techA[1] = $_POST['shieldA'];
if(isset($_POST['hullA']) && is_numeric($_POST['hullA']) && $_POST['hullA'] > 0)
	$techA[2] = $_POST['hullA'];
$groupAArr;
$i = 0;
foreach($short as $s)
	if(array_key_exists($s."A", $_POST) && is_numeric($_POST[$s."A"]) && $_POST[$s."A"] > 0)
		if($_POST[$s."A"] > 0)
			$groupAArr[$s."A"] = new Group($model[$s], $_POST[$s."A"], $techA);
			
$techD = array(0, 0, 0);
if(isset($_POST['weaponD']) && is_numeric($_POST['weaponD']) && $_POST['weaponD'] > 0)
	$techD[0] = $_POST['weaponD'];
if(isset($_POST['shieldD']) && is_numeric($_POST['shieldD']) && $_POST['shieldD'] > 0)
	$techD[1] = $_POST['shieldD'];
if(isset($_POST['hullD']) && is_numeric($_POST['hullD']) && $_POST['hullD'] > 0)
	$techD[2] = $_POST['hullD'];
$groupDArr;
$i = 0;
foreach($short as $s)
	if(array_key_exists($s."D", $_POST) && is_numeric($_POST[$s."D"]) && $_POST[$s."D"] > 0)
		if($_POST[$s."D"] > 0)
			$groupDArr[$s."D"] = new Group($model[$s], $_POST[$s."D"], $techD);

if(!isset($groupAArr) || !isset($groupDArr))
	return;
	
$attFleet = new Fleet($groupAArr, "Attaquant");
$defFleet = new Fleet($groupDArr, "Defenseur");
$fight = new Fight($attFleet, $defFleet);

//Report writing
$fight->encounterInformation();

?>
