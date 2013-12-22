<?php

$timestart=microtime(true);

include 'benchmarker.php';

$timeend=microtime(true);
$time=$timeend-$timestart;
$page_load_time = number_format($time, 3);

$metalDisp = isset($_POST['metal']) ? $_POST['metal'] : 3;
$cristalDisp = isset($_POST['cristal']) ? $_POST['cristal'] : 2;
$deutDisp = isset($_POST['deut']) ? $_POST['deut'] : 1;
$ratioDisp = isset($_POST['ratio']) ? $_POST['ratio'] : 100;

$compositionTable =
'<table><tr><td>

<table class="composition">
<form action="" method="post">
	<tr><th>Unite</th><th></th><th>Composition</th></tr>
	<tr><td>'.$name[$ligFi].'</td><td> : </td><td><input name="'.$ligFi.'" value="'.@$_POST[$ligFi].'"/></td></tr>
	<tr><td>'.$name[$heaFi].'</td><td> : </td><td><input name="'.$heaFi.'" value="'.@$_POST[$heaFi].'"/></td></tr>
	<tr><td>'.$name[$cruis].'</td><td> : </td><td><input name="'.$cruis.'" value="'.@$_POST[$cruis].'"/></td></tr>
	<tr><td>'.$name[$batSh].'</td><td> : </td><td><input name="'.$batSh.'" value="'.@$_POST[$batSh].'"/></td></tr>
	<tr><td>'.$name[$batCr].'</td><td> : </td><td><input name="'.$batCr.'" value="'.@$_POST[$batCr].'"/></td></tr>
	<tr><td>'.$name[$bombe].'</td><td> : </td><td><input name="'.$bombe.'" value="'.@$_POST[$bombe].'"/></td></tr>
	<tr><td>'.$name[$destr].'</td><td> : </td><td><input name="'.$destr.'" value="'.@$_POST[$destr].'"/></td></tr>
	<tr><td>'.$name[$death].'</td><td> : </td><td><input name="'.$death.'" value="'.@$_POST[$death].'"/></td></tr>
	<tr><td colspan="4">|</td></tr>
	<tr><td>'.$name[$probe].'</td><td> : </td><td><input name="'.$probe.'" value="'.@$_POST[$probe].'"/></td></tr>
	<tr><td>'.$name[$smCar].'</td><td> : </td><td><input name="'.$smCar.'" value="'.@$_POST[$smCar].'"/></td></tr>
	<tr><td>'.$name[$laCar].'</td><td> : </td><td><input name="'.$laCar.'" value="'.@$_POST[$laCar].'"/></td></tr>
	<tr><td colspan="4">|</td></tr>
	<tr><td>'.$name[$roLau].'</td><td> : </td><td><input name="'.$roLau.'" value="'.@$_POST[$roLau].'"/></td></tr>
	<tr><td>'.$name[$liLas].'</td><td> : </td><td><input name="'.$liLas.'" value="'.@$_POST[$liLas].'"/></td></tr>
	<tr><td>'.$name[$heLas].'</td><td> : </td><td><input name="'.$heLas.'" value="'.@$_POST[$heLas].'"/></td></tr>
	<tr><td>'.$name[$ionCa].'</td><td> : </td><td><input name="'.$ionCa.'" value="'.@$_POST[$ionCa].'"/></td></tr>
	<tr><td>'.$name[$gauss].'</td><td> : </td><td><input name="'.$gauss.'" value="'.@$_POST[$gauss].'"/></td></tr>
	<tr><td>'.$name[$plasm].'</td><td> : </td><td><input name="'.$plasm.'" value="'.@$_POST[$plasm].'"/></td></tr>
	<tr><td></td><td></td><td></td><td></td></tr>
	<tr><td>Taux</td><td> : </td><td><input name="metal" size="1" value="'.$metalDisp.'"/> / <input name="cristal" size="1" value="'.$cristalDisp.'"/> / <input name="deut" size="1" value="'.$deutDisp.'"/></td></tr>
	<tr><td>Moyens ennemis</td><td> : </td><td><input name="ratio" size="3" value="'.$ratioDisp.'"/> %</td></tr>
	<tr><td></td><td></td><td><input type="submit" value="Mesurer" /> <input type="reset" value="Nettoyer" onclick="window.location=\'./\';" /></td><td></td></tr>
</form>
</table>

</td><td valign="top">

<div class="report">
	'.$report.'
</div>

</td></tr></table>';
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta name="author" lang="fr" content="Thomas LE GUERROUE"/>
	<meta name="copyright" content="Thomas LE GUERROUE"/>
	<meta name="keywords" content="ogame, efficiency, efficient, simualtor, fight, ratio, defense, fleet, composition"/>
	<meta name="description" content="Providing the best fleet and defense composition"/>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<title>Lemonade - Benchmark</title>
	<link rel="stylesheet" media="screen" type="text/css" href="../style.css" />
	<link rel="shortcut icon" href="../icon.ico" />
</head>

<body>
	<div class="main">
		<?php echo $compositionTable ?>
	</div>

	<div class='footer'>
		<?php echo "temps d execution " . $page_load_time . " sec"; ?> // <a href="http://agrum.in">agrum - 2013</a>
	</div>
</body>
</html> 
