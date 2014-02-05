<?php

include 'invDistrib.php';

$debug = "";

function debug($p_string)
{
	global $debug;
	$debug .= $p_string;
}
	
//1.33 | 1.43
//1.99 | 2.08 
//2.6 | 2.67
//3.4 | 3.33
//4.6 | 4.35

	
//for($prop = 2; $prop < 1000; $prop++)
//$prop = 2.8214;
//$prop = 282.14;
$prop = 1.8;
{
	$proportionTouchedOnceOrMore = 1 - 1/exp($prop);
	$proportion = ($prop - $proportionTouchedOnceOrMore) / $proportionTouchedOnceOrMore;
	$proportionNotTouchedMore = 1/exp($proportion);
		
	$startInvDist = $proportionNotTouchedMore;
	$startCumGauss = $g_invDistribution[floor($startInvDist*$g_countInvDistribution)];
	$span = 2.0*pow(5.0 * $proportion, 0.5);
	$leftSpan = min($span/2, $proportion);
	$startCombined = $proportion - $leftSpan;
	debug("__prop : " . $prop . "__<br/>");
	debug("__proportion : " . $proportion . "__<br/>");
	debug("__span : " . $span . "__<br/>");
	debug("__startCombined : " . $startCombined . "__<br/>");
	debug("__startInvDist : " . $startInvDist . "__<br/>");
	debug("__proportionTouchedOnceOrMore : " . $proportionTouchedOnceOrMore . "__<br/>");
	debug("__proportionNotTouchedMore : " . $proportionNotTouchedMore . "__<br/>");

	$div = 5;
	$propOnce = $proportionTouchedOnceOrMore * $proportionNotTouchedMore / $prop;
	$propMore = 0;
	$addOn = array();
	
	for($i = 0; $i < $div; $i++)
	{
		$halfPartCoverage = (1.0 - $startInvDist)/$div/2.0;
		$cumGauss = $g_invDistribution[floor(((1+2*$i)*$halfPartCoverage + $startInvDist)*$g_countInvDistribution)];
		$addOn[$i] = 1.0 + $startCombined + $span*($cumGauss - $startCumGauss)/(1.0 - $startCumGauss);
		$propMore += (0.0 + $addOn[$i]) * ($proportionTouchedOnceOrMore * (1 - $proportionNotTouchedMore) / $div) / $prop;
	}
	debug("__PropOnce : " . $propOnce . "__<br/>");
	debug("__PropMore : " . $propMore . "__<br/>");
	$balance = (1 - $propOnce) / $propMore;
	debug("__Balance : " . $balance . "__<br/>");
	for($i = 0; $i < $div; $i++)
	{
		$addOn[$i] *= $balance;
	}
	
	$total = $proportionTouchedOnceOrMore * $proportionNotTouchedMore;
	//debug("__Proportion hit : " . $proportionTouchedOnceOrMore . "__<br/>");
	//debug("__Proportion hit more in set : " . (1-$proportionNotTouchedMore) . "__<br/>");
	//debug("__Proportion : " . $proportion . "__<br/>");
	//debug("__Touched 1 time : " . ($proportionTouchedOnceOrMore * $proportionNotTouchedMore) . "__<br/>");
	for($i = 0; $i < $div; $i++)
	{
		//$halfPartCoverage = (1.0 - $startInvDist)/$div/2.0;
		//debug("___Perc ".((1+2*$i)*$halfPartCoverage + $startInvDist)."<br/>");
		//$cumGauss = $g_invDistribution[floor(((1+2*$i)*$halfPartCoverage + $startInvDist)*$g_countInvDistribution)];
		//debug("___CumGauss $cumGauss __<br/>");
		//$addOn = $startCombined + $span*($cumGauss - $startCumGauss)/(1.0 - $startCumGauss);
		//debug("___PercOfSpan ".($cumGauss - $startCumGauss)/(1.0 - $startCumGauss)." __<br/>");

		//debug("__Touched ". (0 + $addOn[$i]) ." times : " . ($proportionTouchedOnceOrMore * (1 - $proportionNotTouchedMore) / $div) . "__<br/>");
	
		$total += (0.0 + $addOn[$i]) * ($proportionTouchedOnceOrMore * (1 - $proportionNotTouchedMore) / $div);
	}
	debug("__TOTAL : " . ($total / $prop) . "__<br/>");
}

echo $debug;

?>
