<?php

include "unitSpec.class.php";
if( isset($_GET['lang']) && file_exists("../include/".$_GET['lang']."/lang.php") )
	include "../include/".$_GET['lang']."/lang.php";
else
	include "../include/fr/lang.php";

//All unit specifications.
//The units' id are stored in th $short global variable
//The units' full name are stored in $name global variable
//Each unit is defined as a class UnitSpec in the $model array with its short name as id

$g_metal = 1;
$g_cristal = 1;
$g_deut = 1;

//Array containing all the UnitSpecs
$model = array();
//Follows all the UnitSpec classes pushed into the $model array

//MILITAIRE

$model[$ligFi] = new UnitSpec(
	$ligFi,
	true,
	true,
	true,
	3000,
	1000,
	0,
	400,
	10,
	50,
	array(
		$probe => 5
	)
);

$model[$heaFi] = new UnitSpec(
	$heaFi,
	true,
	true,
	true,
	6000,
	4000,
	0,
	1000,
	25,
	150,
	array(
		$smCar => 3,
		$probe => 5
	)
);

$model[$cruis] = new UnitSpec(
	$cruis,
	true,
	true,
	true,
	20000,
	7000,
	2000,
	2700,
	50,
	400,
	array(
		$ligFi => 6,
		$probe => 5,
		$roLau => 10
	)
);

$model[$batSh] = new UnitSpec(
	$batSh,
	true,
	true,
	true,
	45000,
	15000,
	0,
	6000,
	200,
	1000,
	array(
		$probe => 5
	)
);

$model[$bombe] = new UnitSpec(
	$bombe,
	true,
	true,
	true,
	50000,
	25000,
	15000,
	7500,
	500,
	1000,
	array(
		$probe => 5,
		$roLau => 20,
		$liLas => 20,
		$heLas => 10,
		$ionCa => 10
  )
);

$model[$destr] = new UnitSpec(
	$destr,
	true,
	true,
	true,
	60000,
	50000,
	15000,
	11000,
	500,
	2000,
	array(
		$batCr => 2,
		$probe => 5,
		$roLau => 1,
		$liLas => 10
	)
);

$model[$death] = new UnitSpec(
	$death,
	true,
	true,
	true,
	5000000,
	4000000,
	1000000,
	900000,
	50000,
	200000,
	array(
		$ligFi => 200,
		$heaFi => 100,
		$cruis => 33,
		$batSh => 30,
		$batCr => 15,
		$bombe => 25,
		$destr => 5,
		$smCar => 250,
		$laCar => 250,
		$probe => 1250,
		$roLau => 200,
		$liLas => 200,
		$heLas => 100,
		$ionCa => 100,
		$gauss => 50
	)
);

$model[$batCr] = new UnitSpec(
	$batCr,
	true,
	true,
	true,
	30000,
	40000,
	15000,
	7000,
	400,
	700,
	array(
		$heaFi => 4,
		$cruis => 4,
		$batSh => 7,
		$smCar => 3,
		$laCar => 3,
		$probe => 5
	)
);

//CIVIL

$model[$smCar] = new UnitSpec(
	$smCar,
	true,
	true,
	true,
	2000,
	2000,
	0,
	400,
	10,
	5,
	array(
		$probe => 5
	)
);

$model[$laCar] = new UnitSpec(
	$laCar,
	true,
	true,
	true,
	6000,
	6000,
	0,
	1200,
	25,
	5,
	array(
		$probe => 5
	)
);

$model[$recyc] = new UnitSpec(
	$recyc,
	true,
	true,
	true,
	10000,
	6000,
	2000,
	1600,
	10,
	1,
	array(
		$probe => 5
	)
);

$model[$colon] = new UnitSpec(
	$colon,
	true,
	true,
	true,
	10000,
	20000,
	10000,
	3000,
	100,
	50,
	array(
		$probe => 5
	)
);

$model[$probe] = new UnitSpec(
	$probe,
	true,
	true,
	true,
	0,
	1000,
	0,
	100,
	0.1,
	0.1,
	array(
	)
);

$model[$solar] = new UnitSpec(
	$solar,
	true,
	false,
	true,
	0,
	1000,
	0,
	100,
	0.1,
	0.1,
	array(
	)
);

//DEFENSE

$model[$roLau] = new UnitSpec(
	$roLau,
	false,
	false,
	true,
	2000,
	0,
	0,
	200,
	20,
	80,
	array(
	)
);

$model[$liLas] = new UnitSpec(
	$liLas,
	false,
	false,
	true,
	1500,
	500,
	0,
	200,
	25,
	100,
	array(
	)
);

$model[$heLas] = new UnitSpec(
	$heLas,
	false,
	false,
	true,
	6000,
	2000,
	0,
	800,
	100,
	250,
	array(
	)
);

$model[$ionCa] = new UnitSpec(
	$ionCa,
	false,
	false,
	true,
	2000,
	6000,
	0,
	800,
	500,
	150,
	array(
	)
);

$model[$gauss] = new UnitSpec(
	$gauss,
	false,
	false,
	true,
	20000,
	15000,
	2000,
	3500,
	200,
 	1100,
	array(
	)
);

$model[$plasm] = new UnitSpec(
	$plasm,
	false,
	false,
	true,
	50000,
	50000,
	30000,
	10000,
	300,
	3000,
	array(
	)
);

$model[$smShi] = new UnitSpec(
	$smShi,
	false,
	false,
	true,
	10000,
	10000,
	0,
	2000,
	2000,
	1,
	array(
	)
);

$model[$laShi] = new UnitSpec(
	$laShi,
	false,
	false,
	true,
	50000,
	50000,
	0,
	10000,
	10000,
	1,
	array(
	)
);

//MISSILE

$model[$misIP] = new UnitSpec(
	$misIP,
	false,
	false,
	false,
	12500,
	2500,
	10000,
	1,
	1,
	12000,
	array(
	)
);

$model[$misIn] = new UnitSpec(
	$misIn,
	false,
	false,
	false,
	8000,
	0,
	2000,
	1,
	1,
	1,
	array(
	)
);

?>
