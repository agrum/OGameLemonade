<?php

include '../include/benchmark.class.php';

$report = "";

if(@!$_POST['metal'])
	return;
//Supprimer taux nul ou negatif
if($_POST['metal'] <= 0)
	$_POST['metal'] = 1;
if($_POST['cristal'] <= 0)
	$_POST['cristal'] = 1;
if($_POST['deut'] <= 0)
	$_POST['deut'] = 1;
$g_metal = $_POST['metal'];
$g_cristal = $_POST['cristal'];
$g_deut = $_POST['deut'];

$tech = array(0, 0, 0);

$groupArr;
$i = 0;
foreach($short as $s)
	if(array_key_exists($s, $_POST) && is_numeric($_POST[$s]) && $_POST[$s] > 0)
		$groupArr[$s] = new Group($model[$s], $_POST[$s], $tech);
$benchmarkedFleet = new Fleet($groupArr, "Tested");
$benchmark = new Benchmark($benchmarkedFleet);

//Report writing
$report = '<table><tr><td>

<div class="left">'.PHP_EOL.
$benchmark->costInformation().
$benchmark->sensibilityInformation().
'</div>

</td><td valign="top">

<div class="right">'.PHP_EOL.
$benchmark->encounterInformation().
'</div>

</td></tr></table>';

?>
