<?php

$timestart=microtime(true);

include_once 'fighter.php';
include_once 'parseJSVariable.php';

$timeend=microtime(true);
$time=$timeend-$timestart;
$page_load_time = number_format($time, 3);

$cellA = '<table class="unitCell">';
$cellD = '<table class="unitCell">';
$cellDStatic = '<table class="unitCell">';
$unitInputSpec = 'autocomplete="off" type="number" size="9" maxlength="9"';

foreach($model as $m)
{
	if($model[$m->id()]->canAttack() || $model[$m->id()]->canDefend())
	{		
		if($model[$m->id()]->canAttack())
		{
			$id = $m->id().'A';
			$cellA .= '<tr>'.PHP_EOL;
			$cellA .= '<td><label for="'.$id.'">'.$m->name().' </label></td>'.PHP_EOL;
			$cellA .= '<td><input id="'.$id.'" name="'.$id.'" value="'.@$_POST[$m->id().'A'].'" '.$unitInputSpec.' /></td>'.PHP_EOL;
			if(isset($fight->m_fleetAAfter->m_groupArr[$id]))
				$cellA .= '<td>'.number_format(max(0, $fight->m_fleetAAfter->m_groupArr[$id]->amountUnit()), 1).'</td>'.PHP_EOL;
			else
				$cellA .= '<td></td>'.PHP_EOL;
			$cellA .= '</tr>'.PHP_EOL;
		}
		if($model[$m->id()]->canAttack())
		{
			$id = $m->id().'D';
			$cellD .= '<tr>'.PHP_EOL;
			$cellD .= '<td><label for="'.$id.'">'.$m->name().' </label></td>'.PHP_EOL;
			$cellD .= '<td><input id="'.$id.'" name="'.$id.'" value="'.@$_POST[$m->id().'D'].'" '.$unitInputSpec.' /></td>'.PHP_EOL;
			if(isset($fight->m_fleetBAfter->m_groupArr[$id]))
				$cellD .= '<td>'.number_format(max(0, $fight->m_fleetBAfter->m_groupArr[$id]->amountUnit()), 1).'</td>'.PHP_EOL;
			else
				$cellD .= '<td></td>'.PHP_EOL;
			$cellD .= '</tr>'.PHP_EOL;
		}
		if(!$model[$m->id()]->canAttack() && $model[$m->id()]->canDefend())
		{
			$id = $m->id().'D';
			$cellDStatic .= '<tr>'.PHP_EOL;
			$cellDStatic .= '<td><label for="'.$id.'">'.$m->name().' </label></td>'.PHP_EOL;
			$cellDStatic .= '<td><input id="'.$id.'" name="'.$id.'" value="'.@$_POST[$m->id().'D'].'" autocomplete="off" type="number" size=8 /></td>'.PHP_EOL;
			if(isset($fight->m_fleetBAfter->m_groupArr[$id]))
				$cellDStatic .= '<td>'.number_format(max(0, $fight->m_fleetBAfter->m_groupArr[$id]->amountUnit()), 1).'</td>'.PHP_EOL;
			else
				$cellDStatic .= '<td></td>'.PHP_EOL;
			$cellDStatic .= '</tr>'.PHP_EOL;
		}
	}
}

$cellA .= "</table>";
$cellD .= "</table>";
$cellDStatic .= "</table>";

$techInputSpec = 'autocomplete="off" type="number" size="2" maxlength="2"';
$techA = '
<input name="weaponA" name="weaponA" value="'.@$_POST['weaponA'].'" '.$techInputSpec.' /><label for="weaponA"> : Armes</label><br/>
<input name="shieldA" id="shieldA" value="'.@$_POST['shieldA'].'" '.$techInputSpec.' /><label for="shieldA"> : Bouclier</label><br/>
<input name="hullA" id="hullA" value="'.@$_POST['hullA'].'" '.$techInputSpec.' /><label for="hullA"> : Coque</label>';

$techD = '
<input name="weaponD" id="weaponD" value="'.@$_POST['weaponD'].'" '.$techInputSpec.' /><label for="weaponD"> : Armes</label><br/>
<input name="shieldD" id="shieldD" value="'.@$_POST['shieldD'].'" '.$techInputSpec.' /><label for="shieldD"> : Bouclier</label><br/>
<input name="hullD" id="hullD" value="'.@$_POST['hullD'].'" '.$techInputSpec.' /><label for="hullD"> : Coque</label>';

$parser = '
<label for="parser">Parse report</label><br/>
<textarea name="parser" id="parser"></textarea>';

$compositionTable = '<table class="composition">
<form action="" method="post">
	<tr><th>Options</th><th>Attaquant</th><th>Defenseur</th><th></th></tr>
	<tr><td>'.$parser.'</td><td>'.$techA.'</td><td>'.$techD.'</td><td></td></tr>
	<tr><td></td><td>'.$cellA.'</td><td>'.$cellD.'</td><td>'.$cellDStatic.'</td></tr>
	<tr><td><input type="submit" value="Mesurer" /> <input type="reset" value="Nettoyer" onclick="window.location=\'./\';" /></td><td></td><td></td><td></td></tr>
</form>
</table>';
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
		<?php echo $compositionTable; ?>
	</div>
	
	<script>
		<?php echo $js; ?>
	</script>

	<div class='footer'>
		<?php echo "temps d execution " . $page_load_time . " sec"; ?> // <a href="http://agrum.in">agrum - 2014</a>
	</div>
	
	<div>
		<?php if(isset($_GET['debug'])) echo $debug; ?>
	</div>
</body>
</html> 
