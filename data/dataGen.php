<?php

include 'distrib.php';

$fullInter = array();
$refA = 0.0;
$refB = 0.0;

foreach($distrib as $a => $b)
{	
	$startIndex = count($fullInter);
	for($i = $startIndex; $i < $b; $i++)
	{
		$coef = ($i - $refB)/($b - $refB);
		$fullInter[$i] = (1-$coef)*$refA/$dL + $coef*$a/$dL;
		echo "$i => $fullInter[$i],<br/>";
	}
	
	$refA = $a;
	$refB = $b;
}

print_r($fullInter);

?>
