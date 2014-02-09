<?php

include_once 'spec.php';
include_once 'division.class.php';
include_once 'debug.class.php';

include_once 'invDistrib.php';

//Here lies the magic 'woulouloouuuu....'

//Core class of the project. A group is a set of unit of the same type, e.g. 100 Light Fighters.
//Thus the class holds as first members a model of unit as well as an amount of this unit.

//At the construction,the integrity is set to 1.0, meaning 100%.
//One rule of the fight in OGame is "If a ship has less than 70% of it's hull (here integrity)
//	each consecutive hit (if not deflected) has a chance to make the unit explode". Since
//	this simulator manage groups instead of stand alone units, this concept must be well managed.
//	The solution here is to acknoledge the instable units and dissociate them from the amount
//	of stable units. The group is then divided in two : stable units ($m_stable) and instable
//	ones ($m_unstable). Since the instables result from heavy weaponery, it would be a mistake
//	to share the integrity of stable units with unstable ones. Each group has its own integrity
//	variable.
//The variable set at each round beginning has been explained. There is two other type of
//	variables in this class :
//-Temp variables. Those variables are highly instable. They are bluntly modified during a round
//	and thus can't be used as reference during a round. At each end of round, they are used
//	to update the non-temp variable.
//-InWave variables. Those variable are stable during a whole wave. In this simulator, each round
//	is divided in waves. A wave correspond to a group attacking an other one, thus a set of same
//	units attacking an other set of same units. InWave variables are stable during a whole wave.

class Group extends Debug{
	public $m_model;
	public $m_divCoverage;
	public $m_amountDivInteg;
	public $m_amountDivShield;
	
	public $m_integArr;
	
	public $m_amountWShieldTemp;
	public $m_shieldTemp;
	public $m_integArrTemp;
	public $m_shieldArrTemp;
	
	public $m_averageShieldInWave;
	public $m_probaHitShieldInWave;
	public $m_integArrInWave;
	public $m_shieldArrInWave;
	
	public function __construct($p_model, $p_amount, $p_tech)
	{
		$this->m_model = clone $p_model;
		$this->m_model->setTech($p_tech[0], $p_tech[1], $p_tech[2]);
		$this->m_divCoverage = 0.5;
		$this->m_amountDivInteg = 1 + ceil(max(0,log($p_model->hull())) / log(2.0));
		$this->m_amountDivShield = 1 + ceil(max(0,log($p_model->shield())) / log(10.0));
		
		$this->m_integArr[0] = new Division($p_amount, 1.0);
		for($i = 1; $i < $this->m_amountDivInteg; $i++)
			$this->m_integArr[$i] = new Division(0, 1.0);
	}	
	
	public function __clone()
	{
		$this->m_model = $this->m_model;
		$this->m_amountDivInteg = $this->m_amountDivInteg;
		$this->m_amountDivShield = $this->m_amountDivShield;
		
		for($i = 0; $i < count($this->m_integArr); $i++)
			$this->m_integArr[$i] = clone $this->m_integArr[$i];
	}
	
	//Gives the theoric rapidfire of the group against a set of groups
	public function rapidFire($p_groupArr)
	{
		global $model;
		$rapidFireProba = 0;
		$totalAmountUnit = 0;
		
		//Get the amount of units on the opposite side
		foreach($p_groupArr as $group)
		{
			$totalAmountUnit += $group->amountUnit();
		}
		
		//Get the rapid fire against each group, 
		//Convert it to a probabilistic rapidfire (p = (r-1)/r)
		//Multuply it to the proportion of unit in the overall package
		foreach($p_groupArr as $group)
		{
			$rapidFireAgainstGroup = $this->m_model->rapidfireAgainst($group->m_model->id());
			$rapidFireAgainstGroupProba = ($rapidFireAgainstGroup-1.0)/$rapidFireAgainstGroup;
			$rapidFireProba += $rapidFireAgainstGroupProba*($group->amountUnit()/$totalAmountUnit);
		}
		
		//Convert back the probabilistic rapidfire to the ogame unit system (r = 1/(1-p))
		$rapidFire = 1/(1-$rapidFireProba);
		
		return $rapidFire;
	}
	
	public function value()
	{
		return $this->m_model->cost()*$this->amountUnit();
	}
	
	public function setValue($p_coef)
	{
		foreach ($this->m_integArr as &$div)
		{
			$div->m_amount *= $p_coef;
			$div->m_integrity *= $p_coef;
		}
	}
	
	public function amountUnit(){
		$count = 0;
		foreach ($this->m_integArr as &$div)
			$count += $div->m_amount;
		return $count;
	}
	
	public function amountUnitTemp(){
		$count = 0;
		foreach ($this->m_integArrTemp as &$div)
			$count += $div->m_amount;
		return $count;
	}
	
	public function amountUnitInWave(){
		$count = 0;
		foreach ($this->m_integArrInWave as &$div)
			$count += $div->m_amount;
		return $count;
	}
	
	public function getTopIntegrityForDiv($p_id){
		return $this->m_divCoverage * (count($this->m_integArr) - $p_id) / count($this->m_integArr) + (1.0 - $this->m_divCoverage);
	}
	
	public function getDivIDFromIntegrityNorm($p_integrityNorm){
		return round((count($this->m_integArr) - 1) * (1.0 - (max($p_integrityNorm - (1.0 - $this->m_divCoverage), 0.0) / $this->m_divCoverage)));
	}
	
	//Store the current stats in temporary variables that can be modified without affecting the class
	public function initRound()
	{
		//old shield design
		$this->m_amountWShieldTemp = $this->amountUnit();
		$this->m_shieldTemp = $this->m_model->shield()*$this->amountUnit();
		
		//Integrity
		foreach ($this->m_integArr as $i => $div)
			$this->m_integArrTemp[$i] = clone $div;
			
		//Shield
		$this->m_shieldArrTemp[0] = new Division($this->amountUnit());
		for($i = 1; $i < $this->m_amountDivShield; $i++)
			$this->m_shieldArrTemp[$i] = new Division(0);
	}
	
	//Part of a round, which is just a set of fires of different magnitudes
	public function receiveWave($p_amountHit, $p_power)
	{
		if($this->m_model->shield() > 100*$p_power)
		{
			return; //Deflected TODO wrong
		}
			
		global $model;
		$amountHit = $p_amountHit*$this->amountUnitTemp()/$this->amountUnit();
		$amountUnit = $this->amountUnitTemp();
		$amountUnitHit = $amountUnit * ( 1 - 1/exp($amountHit/$amountUnit)); //Magiiiic
		if($amountUnitHit == 0)
			return;
		
		$this->debug( "Receive wave begin on ".$this->m_model->name()." (".number_format($this->amountUnitTemp(), 2)." units) <br/>
			_Amount of shots : ".number_format($amountHit, 2)." <br/>
			_Proportion managed : ".number_format($this->amountUnitTemp()/$this->amountUnit() ,3)."<br/>
			_Power : $p_power<br/>
			_Amount unit hit : ".number_format($amountUnitHit ,2)."<br/>".PHP_EOL );
		
		//Set the constant variables during the wave
		//We want to recall 
		//-the average shield on each unit
		//-the probabilty to hit a shield
		//-the amount of unit in each division at the beginning of the wave
		if($this->m_amountWShieldTemp > 0)
			$this->m_averageShieldInWave = $this->m_shieldTemp/$this->m_amountWShieldTemp;
		else
			$this->m_averageShieldInWave = 0;
		$this->m_probaHitShieldInWave = $this->m_amountWShieldTemp/$this->amountUnitTemp();
		foreach ($this->m_integArrTemp as $i => $divTemp)
			$this->m_integArrInWave[$i] = clone $divTemp;
		foreach ($this->m_shieldArrTemp as $i => $divTemp)
			$this->m_shieldArrInWave[$i] = clone $divTemp;
	
		$prop = $amountHit / $amountUnit;
		$amountHitLeft = $amountHit - $amountUnitHit;
		if($amountHitLeft / $amountUnitHit >= 1)
		{
			global $g_invDistribution;
			global $g_countInvDistribution;
			
			$proportionTouchedOnceOrMore = 1 - 1/exp($prop);
			$proportion = $amountHitLeft/$amountUnitHit;
			$proportionNotTouchedMore = 1/exp($proportion);
			
			$this->ackImpact(
					$amountUnitHit * $proportionNotTouchedMore, 
					$p_power, 
					1.0);
					
			$startInvDist = $proportionNotTouchedMore;
			$startCumGauss = $g_invDistribution[floor($startInvDist*$g_countInvDistribution)];
			$span = 2.84*pow(5.0 * $proportion, 0.5);
			$leftSpan = min($span/2, $proportion);
			$startCombined = $proportion - $leftSpan;
			//$this->debug("__prop : " . $prop . "__<br/>");
			//$this->debug("__proportion : " . $proportion . "__<br/>");
			//$this->debug("__span : " . $span . "__<br/>");
			//$this->debug("__startCombined : " . $startCombined . "__<br/>");
			//$this->debug("__startInvDist : " . $startInvDist . "__<br/>");
			//$this->debug("__startCumGauss : " . $startCumGauss . "__<br/>");
			//$this->debug("__proportionTouchedOnceOrMore : " . $proportionTouchedOnceOrMore . "__<br/>");
			//$this->debug("__proportionNotTouchedMore : " . $proportionNotTouchedMore . "__<br/>");
			
			$div = 4;
			
			$propOnce = $proportionTouchedOnceOrMore * $proportionNotTouchedMore / $prop;
			$propMore = 0;
			$addOn = array();
	
			//manage gaussian
			$coverageFractal = 3;
			$covered = 0;
			$coverage = array();
			for($i = $div-1; $i >= 0; $i--)
			{
				$newCoverage = 1.0 / pow($coverageFractal, $i);
				$halfPoint = (1.0 - $covered)/2.0 + (1.0 - $newCoverage)/2.0;
				$cumGauss = $g_invDistribution[floor(($halfPoint * (1.0 - $startInvDist) + $startInvDist)*$g_countInvDistribution)];
				$addOn[$i] = 1.0 + $startCombined + $span*($cumGauss - $startCumGauss)/(1.0 - $startCumGauss);
				$coverage[$i] = ($newCoverage - $covered);
				//$this->debug("__Coverage : " . $coverage[$i] . "__<br/>");
				//$this->debug("__HalfPoint : " . $halfPoint . "__<br/>");
				//$this->debug("__AddOn : " . $addOn[$i] . "__<br/>");
				$propMore += $addOn[$i] * ($proportionTouchedOnceOrMore * (1 - $proportionNotTouchedMore) * ($newCoverage - $covered)) / $prop;
				$covered = $newCoverage;
			}
			
			
			//$this->debug("__PropOnce : " . $propOnce . "__<br/>");
			//$this->debug("__PropMore : " . $propMore . "__<br/>");
			$balance = (1 - $propOnce) / $propMore;
			//$this->debug("__Balance : " . $balance . "__<br/>");
			for($i = 0; $i < $div; $i++)
			{
				$addOn[$i] *= $balance;
			}
			//$subtBalance = (1 - $propOnce) - $propMore;
			//$coverageFirstAddOn = 1.0 - 1.0 / $coverageFractal;
			//$addOn[0] += $subtBalance / (($proportionTouchedOnceOrMore * (1 - $proportionNotTouchedMore) * $coverageFirstAddOn) / $prop);
			
			$total = 0;
			for($i = 0; $i < $div; $i++)
			{
				//$this->debug("__" . floor(((1+2*$i)*$halfPartCoverage + $proportionNotTouchedMore)*$g_countInvDistribution) . "__<br/>");
				
				$total += $addOn[$i] * ($amountUnitHit * (1.0 - $proportionNotTouchedMore) * $coverage[$i]);
				$this->ackImpact(
					$amountUnitHit * (1.0 - $proportionNotTouchedMore) * $coverage[$i], 
					$p_power * $addOn[$i], 
					$addOn[$i]);
			}
			$this->debug("__TOTAL : " . $total . "__<br/>");
		}
		else
		{
			//Remember the power of a soloing unit
			$uniquePower = $p_power;
			$combinedPower = $p_power;
			//Because we will combine powers 
			$combined = 1;
			
			//Combination of power loop
			//At the beginning we have a certain amount of different units hit. However, there is
			//	more single shots than units hit. Thus, some units receive more than one hit. This
			//	loop combine the shots one by one until no more shot/unit is left unmanaged
			while($amountUnitHit > 1)
			{		
				//If the combined power outcast the shield + hull of the unit
				//	just send the shots and stop the loop
				if($combinedPower >= $this->m_model->hull() + $this->m_model->shield())
				{
					$this->ackImpact($amountUnitHit, $combinedPower*$amountHit/$amountUnitHit, $combined*$amountHit/$amountUnitHit);
					$amountUnitHit = 0;
					$amountHit = 0;
					break;
				}
			
				//Same fantastic equation to know how many units have been hit more than once
				$amountUnitHitOnce = $amountUnitHit * pow(($amountUnitHit-1)/$amountUnitHit, $amountHit-$amountUnitHit);
				$amountUnitHitMoreThanOnce = $amountUnitHit - $amountUnitHitOnce;
			
				//We treat the units hit once
				if($amountUnitHitOnce > 1.0)
				{
					$this->ackImpact($amountUnitHitOnce, $combinedPower, $combined);
					$amountHit -= $amountUnitHitOnce;
				}
				elseif($amountUnitHitMoreThanOnce <= 1)
					break;
				else
					$amountUnitHitMoreThanOnce += $amountUnitHitOnce;
			
				//And increase the power by combining the shots for the next loop iteration
				$amountHit /= 2;
				$amountUnitHit = $amountUnitHitMoreThanOnce;
				$combinedPower *= 2;
				$combined *= 2;
			}
		
			//Process residuals
			//Being here means there is less than one unit left unmanaged but some 
			//	fire power left. We manage it.
			if($amountHit > 0 && $amountUnitHit > 0)
			{
				$combinedPower *= $amountHit/$amountUnitHit;
				$combined *= $amountHit/$amountUnitHit;
				$this->ackImpact($amountUnitHit, $combinedPower, $combined);
			}	
		}

		$this->debug( "Receive wave end with ".number_format($this->amountUnitTemp(), 1)." units left<br/>" );
		for($i = 0; $i < count($this->m_integArrTemp); $i++)
		{
			$div = $this->m_integArrTemp[$i];
			if($div->m_amount > 0)
				$this->debug( "_Division ".$i." ".number_format($this->getTopIntegrityForDiv($i), 2)." : ".number_format($div->m_amount, 3)." (Integrity of ".number_format($div->integrityNorm(), 2).")<br/>" );
		}
		$this->debug( "<br/>".PHP_EOL );
	}
	
	private function ackImpact($p_amount, $p_power, $p_combined)
	{
		//No use continuing with a group wiped out
		if($this->amountUnitTemp() <= 0)
			return;
			
		$this->debug( "Unit hit ".number_format($p_combined, 1)." times (".number_format($p_amount, 2)." units with ".number_format($p_amount*$p_combined, 2)." shots)<br/>
			_Power received : ".number_format($p_amount * $p_power)." (".number_format($p_power)." each)<br/>".PHP_EOL );	
		
		$amountInWave = $this->amountUnitInWave();
		if($amountInWave == 0)
			return;
		
		for($i = 0; $i < count($this->m_shieldArrTemp); $i++)
		{
			if($this->m_shieldArrInWave[$i]->m_amount / $amountInWave > 0)
			{
				$affected = $p_amount * $this->m_shieldArrInWave[$i]->m_amount / $amountInWave;
				$shieldNorm = $this->m_shieldArrInWave[$i]->integrityNorm();
				//$this->debug( "_Shield norm : ".number_format($shieldNorm, 2)." <br/>" );
		
				//Shield effect
				$consumedShield = min($shieldNorm, $p_power/$this->m_model->shield());
				$power = $p_power - $consumedShield * $this->m_model->shield();
				$this->debug( "_Power left : ".number_format($power, 2)." <br/>" );
			
				//Displace data structure
				$shieldLeft = max(0.0, $shieldNorm - $consumedShield);
				$destDivId = round((count($this->m_shieldArrTemp) - 1) * (1.0 - $shieldLeft));
				//$this->debug( "_Shield div destination : ".$destDivId."<br/>" );
	
				//Direct hit after absorbtion (must trigger even with null power for explosion)
				$unitLost = $this->affectIntegrity($affected, $power, $p_combined);
	
				$this->m_shieldArrTemp[$i]->m_amount -= $affected;
				$this->m_shieldArrTemp[$i]->m_integrity -= $affected * $shieldNorm;
	
				$this->m_shieldArrTemp[$destDivId]->m_amount += ($affected - $unitLost);
				$this->m_shieldArrTemp[$destDivId]->m_integrity += ($affected - $unitLost) * ($shieldLeft);
			}
		}
	}
	
	//Acknoledge a number of distinct units hit with a certain combined power.
	// Will now affect the integrity and wmount of unit.
	public function affectIntegrity($p_amount, $p_power, $p_combined)
	{				
		$unitDestroyed = 0;
		//Integrity consumed by the hit
		$consumedIntegrity = $p_power/$this->m_model->hull();
		
		$amountInWave = $this->amountUnitInWave();
		if($amountInWave == 0)
			return $unitDestroyed;
		
		//Manage stables
		for($i = 0; $i < count($this->m_integArr); $i++)
		{
			if($this->m_integArrInWave[$i]->m_amount / $amountInWave > 0)
			{
				$affected = $p_amount * $this->m_integArrInWave[$i]->m_amount / $amountInWave;

				$integrityNorm = $this->m_integArrInWave[$i]->integrityNorm();
		
				$offExplosion = max(0, $integrityNorm - 0.7);
				$combined = $p_combined;
				if($consumedIntegrity > 0)
					$combined = $p_combined*($consumedIntegrity - $offExplosion)/$consumedIntegrity;
				$first = $combined - floor($combined);
				$combined = floor($combined);
		
				$nonExplodingRatio = 1.0;
				if($integrityNorm-$consumedIntegrity <= 0)
					$nonExplodingRatio = 0.0;
				elseif($integrityNorm-$consumedIntegrity <= 0.7)
				{
					if($offExplosion > 0)
						$nonExplodingRatio *= max(0, 0.7 - $first*$p_power/$p_combined/$this->m_model->hull());
					if($combined > 0)
						for($j = 1; $j <= $p_combined; $j++) //Find a way to get rid of this for loop, time consuming
							$nonExplodingRatio *= $integrityNorm-($consumedIntegrity*$j)/$p_combined;
					if($first % 1.0 != 0.0)
						$nonExplodingRatio *= $integrityNorm-$consumedIntegrity/$p_combined; //TODO remove p_combined from equation I think
				}
				$nonExplodingRatio = max(0.0, $nonExplodingRatio);
		
				$explodingRatio = 1 - $nonExplodingRatio;
			
				$nonExploding = $affected * $nonExplodingRatio;
		
				$integrityLeft = max(0.0, $integrityNorm - $consumedIntegrity);
				$destDivId = $this->getDivIDFromIntegrityNorm($integrityLeft);
		
				$this->m_integArrTemp[$i]->m_amount -= max(0.0, $affected);
				$this->m_integArrTemp[$i]->m_integrity -= max(0.0, $affected * $integrityNorm);
				if($this->m_integArrTemp[$i]->m_amount < 0.001)
				{
					$this->m_integArrTemp[$i]->m_amount = 0.0;
					$this->m_integArrTemp[$i]->m_integrity = 0.0;
				}
		
				$this->m_integArrTemp[$destDivId]->m_amount += max(0.0, $nonExploding);
				$this->m_integArrTemp[$destDivId]->m_integrity += max(0.0, $nonExploding * ($integrityLeft));
		
				if($affected * $explodingRatio > 0)
				{
					$unitDestroyed += $affected * $explodingRatio;
					//$this->debug( "_Explosions : ".number_format($affected * $explodingRatio, 2)." : ".number_format($explodingRatio, 2)."<br/>" );
				}
			}
		}
		
		return $unitDestroyed;
	}
	
	//Modify the group according to the temporary changes made
	public function applyRound()
	{
		foreach ($this->m_integArrTemp as $i => $stableTemp)
			$this->m_integArr[$i] = clone $stableTemp;
	}
};
?>
