<?php

if( isset($_GET['lang']) && file_exists("../include/".$_GET['lang']."/lang.php") )
	include_once "../include/".$_GET['lang']."/lang.php";
else
	include_once "../include/fr/lang.php";

$js = "";
$js .= "function NameParse(p_name, p_dest){
	this.name = p_name;
	this.dest = p_dest;
}".PHP_EOL;

$js .= PHP_EOL;

$js .= "var nameParseArr = new Array();".PHP_EOL;
$count = 0;
foreach($nameScanArr as $shortName => $nameScan)
	$js .= "nameParseArr[".$count++."] = new NameParse('$nameScan', '".$shortName."D');".PHP_EOL;
	
$js .= PHP_EOL;

$js .= "document.getElementById('parser').onkeyup = function() {
	var parserContent = document.getElementById('parser').value;
	var pattern;
	var newValue = '';
	var i;
	for(i = 0; i < nameParseArr.length; i++)
	{
		newValue = '';
		pattern = new RegExp(nameParseArr[i].name + '\\t[0-9]+','m');
		
		if(pattern.test(parserContent))
		{
			newValue = pattern.exec(parserContent)[0];
			newValue = Number(newValue.replace(/[^0-9]+/g, ''));
		}
		if(document.getElementById(nameParseArr[i].dest))
			document.getElementById(nameParseArr[i].dest).value = newValue;
	}
	
	//Technologie Armes	XX
	pattern = new RegExp('Technologie Armes\\t[0-9]+','m');
	if(pattern.test(parserContent))
	{
		newValue = pattern.exec(parserContent)[0];
		newValue = Number(newValue.replace(/[^0-9]+/g, ''));
		document.getElementById('weaponD').value = newValue;
	}
	//Technologie Bouclier	XX
	pattern = new RegExp('Technologie Bouclier\\t[0-9]+','m');
	if(pattern.test(parserContent))
	{
		newValue = pattern.exec(parserContent)[0];
		newValue = Number(newValue.replace(/[^0-9]+/g, ''));
		document.getElementById('shieldD').value = newValue;
	}
	//Technologie Protection des vaisseaux spatiaux	XX
	pattern = new RegExp('Technologie Protection des vaisseaux spatiaux\\t[0-9]+','m');
	if(pattern.test(parserContent))
	{
		newValue = pattern.exec(parserContent)[0];
		newValue = Number(newValue.replace(/[^0-9]+/g, ''));
		document.getElementById('hullD').value = newValue;
	}
	
	
}".PHP_EOL;

$js .= "document.getElementById('parser').onclick = function() {
	document.getElementById('parser').value = '';
}".PHP_EOL;

?>
