<?php

$timestart=microtime(true);

include 'fighter.php';

$timeend=microtime(true);
$time=$timeend-$timestart;
$page_load_time = number_format($time, 3);

$tableRows = "";

foreach($model as $m)
{
	if($model[$m->id()]->canAttack() || $model[$m->id()]->canDefend())
	{
		$tableRows .= '	<tr><td>'.$m->name().'</td><td> : </td><td>';
		if($model[$m->id()]->canAttack())
			$tableRows .= '<input name="'.$m->id().'A" value="'.@$_POST[$m->id().'A'].'"/>';
		$tableRows .= '</td><td>';
		if($model[$m->id()]->canDefend())
			$tableRows .= '<input name="'.$m->id().'D" value="'.@$_POST[$m->id().'D'].'"/></td></tr>'.PHP_EOL;
	}
}

$compositionTable =
'<table><tr><td>

<table class="composition">
<form action="" method="post">
	<tr><th>Unite</th><th></th><th>Attaquant</th><th>Defenseur</th></tr>
'.$tableRows.'
	<tr><td></td><td></td><td></td><td></td></tr>
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
	<title>Lemonade - Fight</title>
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
	
	<div>
		<?php if(isset($_GET['debug'])) echo $debug ?>
	</div>
</body>
</html> 
