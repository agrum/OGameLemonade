<?php

include 'spec.php';
include 'division.class.php';
include 'debug.class.php';

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
	
	public $m_stableArr;
	public $m_unstable;
	
	public $m_amountWShieldTemp;
	public $m_shieldTemp;
	public $m_stableArrTemp;
	public $m_unstableTemp;
	
	public $m_averageShieldInWave;
	public $m_probaHitShieldInWave;
	public $m_stableArrInWave;
	public $m_unstableInWave;
	
	public function __construct($p_model, $p_amount)
	{
		$this->m_model = $p_model;
		
		$this->m_stableArr[0] = new Division($p_amount);
		for($i = 1; $i < 30; $i++)
			$this->m_stableArr[$i] = new Division(0);
		$this->m_unstable = new Division(0);
	}	
	
	public function __clone()
	{
		$this->m_model = $this->m_model;
		
		for($i = 0; $i < count($this->m_stableArr); $i++)
			$this->m_stableArr[$i] = clone $this->m_stableArr[$i];
		
		$this->m_unstable = clone $this->m_unstable;
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
	
	public function amountUnit(){
		$count = $this->m_unstable->m_amount;
		for($i = 0; $i < count($this->m_stableArr); $i++)
			$count += $this->m_stableArr[$i]->m_amount;
		return $count;
	}
	
	public function amountUnitTemp(){
		$count = $this->m_unstableTemp->m_amount;
		for($i = 0; $i < count($this->m_stableArrTemp); $i++)
			$count += $this->m_stableArrTemp[$i]->m_amount;
		return $count;
	}
	
	public function amountUnitInWave(){
		$count = $this->m_unstableInWave->m_amount;
		for($i = 0; $i < count($this->m_stableArrInWave); $i++)
			$count += $this->m_stableArrInWave[$i]->m_amount;
		return $count;
	}
	
	public function getTopIntegrityForDiv($p_id){
		return 0.3 * (count($this->m_stableArr) - $p_id) / count($this->m_stableArr) + 0.7;
	}
	
	public function getDivIDFromIntegrityNorm($p_integrityNorm){
		return round((count($this->m_stableArr) - 1) * (1.0 - (($p_integrityNorm - 0.7) / 0.3)));
	}
	
	//Store the current stats in temporary variables that can be modified without affecting the class
	public function initRound()
	{
		$this->m_amountWShieldTemp = $this->amountUnit();
		$this->m_shieldTemp = $this->m_model->shield()*$this->amountUnit();
		foreach ($this->m_stableArr as $i => $stable)
			$this->m_stableArrTemp[$i] = clone $stable;
		$this->m_unstableTemp = clone $this->m_unstable;
	}
	
	//Part of a round, which is just a set of fires of different magnitudes
	public function receiveWave($p_amountHit, $p_power)
	{
		if($this->m_model->shield() > 100*$p_power)
		{
			return; //Deflected TODO wrong
		}
		if($this->amountUnitTemp() < 1)
		{
			return; //Deaaad
		}
			
		global $model;
		$amountHit = $p_amountHit;
		//The equation bellow comes from the necessity to know how many different
		//	targets have been hit.
		//When firing 1000 times on a set of 2000 units, the amount of different
		//	units hit is not 1000.
		//This amount comes from this numerical equation :
		//f(n) = f(n-1) + (2000 - f(n))/2000, f(0) = 0 and getting f(1000)
		//However it is not computationally effcicient
		//Hopefully Wolfram exists and gives you the recurrence equation right away
		//f(n) = 2000 * (1 + ((2000-1)/2000)^n)
		//For x units and n shots the equation is then
		//f(n) = x * (1 + ((x-1)/x)^n)
		//Giving the amount of unit hit at least once
		$amountUnit = $this->amountUnit();
		$amountUnitHit = $amountUnit * ( 1 - pow(($amountUnit-1)/$amountUnit, $amountHit));
		
		$this->debug( "Receive wave begin on ".$this->m_model->name()." (".number_format($this->amountUnitTemp(), 2)." units) <br/>
			_Amount of shots : ".number_format($amountHit, 2)." <br/>
			_Proportion managed : ".number_format($this->amountUnitTemp()/$this->amountUnit() ,3)."<br/>
			_Power : $p_power<br/>
			_Amount unit hit : ".number_format($amountUnitHit ,2)."<br/>".PHP_EOL );
			
		//Remove the proportion of units already destroyed during this ongoing round
		$amountUnitHit *= $this->amountUnitTemp()/$this->amountUnit();
		
		//Remember the power of a soloing unit
		$uniquePower = $p_power;
		$combinedPower = $p_power;
		//Because we will combine powers 
		$combined = 1;
		
		//Set the constant variables during the wave
		//We want to recall 
		//-the average shield on each unit
		//-the probabilty to hit a shield
		//-the amount of unstable at the beginning of the wave
		if($this->m_amountWShieldTemp > 0)
			$this->m_averageShieldInWave = $this->m_shieldTemp/$this->m_amountWShieldTemp;
		else
			$this->m_averageShieldInWave = 0;
		$this->m_probaHitShieldInWave = $this->m_amountWShieldTemp/$this->amountUnitTemp();
		foreach ($this->m_stableArrTemp as $i => $stableTemp)
			$this->m_stableArrInWave[$i] = clone $stableTemp;
		$this->m_unstableInWave = clone $this->m_unstableTemp;
		
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
				if($uniquePower == $combinedPower)
					$this->ackImpact($amountUnitHitOnce, $combinedPower, $combined);
				else
				{
					$startCombinedPower = $combinedPower / 2;
					$startCombined = $combined / 2;
					
					for($i = 1; $i <= $startCombined; $i++)
					{
						$this->ackImpact(
							$amountUnitHitOnce / $startCombined,
							$startCombinedPower + $i * $uniquePower, 
							$startCombined + $i);
					}
				}
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

		$this->debug( "Receive wave end with ".number_format($this->amountUnitTemp(), 1)." units left<br/>" );
		for($i = 0; $i < count($this->m_stableArrTemp); $i++)
		{
			$div = $this->m_stableArrTemp[$i];
			if($div->m_amount > 0)
				$this->debug( "_Division ".$i." ".number_format($this->getTopIntegrityForDiv($i), 2)." : ".number_format($div->m_amount, 2)." (Integrity of ".number_format($div->integrityNorm(), 2).")<br/>" );
		}
		if($this->m_unstableTemp->m_amount > 0)
			$this->debug( "_Unstable : ".number_format($this->m_unstableTemp->m_amount, 2)." (Integrity of ".number_format($this->m_unstableTemp->integrityNorm(), 2).")<br/>" );
		$this->debug( "<br/>".PHP_EOL );
	}
	
	//Acknoledge a number of distinct units hit with a certain combined power.
	//Each unit hit in ackImpact in the same wave is distinct. Condition that MUST
	//	be certified in receiveWave.
	private function ackImpact($p_amountHit, $p_power, $p_combined)
	{
		//No use continuing with a group wiped out
		if($this->amountUnitTemp() <= 0)
			return;
			
		$this->debug( "Unit hit ".number_format($p_combined, 1)." times (".number_format($p_amountHit, 2)." units with ".number_format($p_amountHit*$p_combined, 2)." shots)<br/>
			_Power received : ".number_format($p_amountHit * $p_power)." (".number_format($p_power)." each)<br/>".PHP_EOL );	
		
		//Get amount of unit hit but absorbing some damage
		$amountHitOnShield = $p_amountHit*$this->m_probaHitShieldInWave;
		
		
		//Direct hit on target without shield
		if($this->m_probaHitShieldInWave != 1)
		{
			$this->affectIntegrity($p_amountHit - $amountHitOnShield, $p_power, $p_combined);
		}
			
		if($amountHitOnShield > 0.01)
		{
			//Shield effect
			$absorbed = min($this->m_averageShieldInWave, $p_power);
		
			$this->m_shieldTemp -= $amountHitOnShield*$absorbed;
			$this->debug( "_Power deflected : ".number_format(100*$absorbed/$p_power, 2)."% <br/>" );
			$p_power -= $absorbed;
		
			//Direct hit after absorbtion (must trigger even with null power for
			//	explosion)
			$unstableTempBefore = $this->m_unstableTemp->m_amount;
			$this->affectIntegrity($amountHitOnShield, $p_power, $p_combined);
			$unstableTempAfter = $this->m_unstableTemp->m_amount;
		
			//Reduce the number of unit with shield if all has been consumed
			if($absorbed > 0 && $absorbed == $this->m_averageShieldInWave)
				$this->m_amountWShieldTemp -= $amountHitOnShield;
			else
				$this->m_amountWShieldTemp -= $unstableTempBefore - $unstableTempAfter;
		}
	}
	
	//Acknoledge a number of distinct units hit with a certain combined power.
	// Will now affect the integrity and wmount of unit.
	public function affectIntegrity($p_amount, $p_power, $p_combined)
	{				
		//Integrity consumed by the hit
		$consumedIntegrity = $p_power/$this->m_model->hull();
		//if($consumedIntegrity == 0) //TODO remove, not in the rules
		//s	return;
		
		//Exploding	old instables
		if($this->m_unstableInWave->m_amount > 0) //Last div is unstable
		{
			//Get the ratio of unstable in the overall set of unit
			$unstableRatio = $this->m_unstableInWave->m_amount/$this->amountUnitInWave();

			$nonExplodingRatio = 1.0;
			$integrity = $this->m_unstableInWave->integrityNorm();
			if($integrity - $consumedIntegrity > 0)
			{
				for($i = 1; $i <= $p_combined; $i++) //Find a way to get rid of this for loop, time consuming
					$nonExplodingRatio *= $integrity-($consumedIntegrity*$i)/$p_combined;
				if($p_combined % 1.0 != 0.0)
					$nonExplodingRatio *= $integrity-$consumedIntegrity/$p_combined;
			}
			else
				$nonExplodingRatio = 0.0;
			$nonExplodingUnstables = $p_amount*$unstableRatio*$nonExplodingRatio;
			$explodingUnstables = $p_amount*$unstableRatio*(1-$nonExplodingRatio);
		
			$this->m_unstableTemp->m_amount -= $explodingUnstables;
			$this->m_unstableTemp->m_integrity -= $explodingUnstables * $integrity;
			$this->m_unstableTemp->m_integrity -= $nonExplodingUnstables * $consumedIntegrity;
		
			//Remove he processed hit on unstables
			$p_amount *= 1 - $unstableRatio;
			
			$this->debug( "_Unstables exploding : ".number_format($explodingUnstables, 2)."<br/>" );
		}
		
		$amountInWave = $this->amountUnitInWave() - $this->m_unstableInWave->m_amount;
		if($consumedIntegrity == 0 || $amountInWave == 0)
			return;
		
		//Manage stables
		for($i = 0; $i < count($this->m_stableArr); $i++)
		{
			$affected = $p_amount * $this->m_stableArrInWave[$i]->m_amount / $amountInWave;
			if($affected < 0.01)
				continue;
			if(!$this->createNewUnstables($i, $consumedIntegrity, $affected, $p_combined, $p_power))
			{
				$integrityNorm = $this->m_stableArrInWave[$i]->integrityNorm();
				$destDivId = $this->getDivIDFromIntegrityNorm($integrityNorm - $consumedIntegrity);
			
				$this->m_stableArrTemp[$i]->m_amount -= $affected;
				$this->m_stableArrTemp[$i]->m_integrity -= $integrityNorm * $affected;
			
				$this->m_stableArrTemp[$destDivId]->m_amount += $affected;
				$this->m_stableArrTemp[$destDivId]->m_integrity += ($integrityNorm - $consumedIntegrity ) * $affected;
			}
		}
	}
	
	//Modify the group according to the temporary changes made
	public function applyRound()
	{
		foreach ($this->m_stableArrTemp as $i => $stableTemp)
			$this->m_stableArr[$i] = clone $stableTemp;
		
		$this->m_unstable = clone $this->m_unstableTemp;
	}
	
	public function createNewUnstables($p_divID, $p_consumedIntegrity, $p_amount, $p_combined, $p_power)
	{
		$p_integrity = $this->m_stableArrInWave[$p_divID]->integrityNorm();
		if($p_integrity - $p_consumedIntegrity < 0.7)
		{
			$offExplosion = max(0, $p_integrity - 0.7);
			$combined = $p_combined*($p_consumedIntegrity - $offExplosion)/$p_consumedIntegrity;
			$first = $combined - floor($combined);
			$combined = floor($combined);
			
			$nonExplodingRatio = 1.0;
			if($p_integrity-$p_consumedIntegrity > 0)
			{
				if($offExplosion > 0)
					$nonExplodingRatio *= max(0, 0.7 - $first*$p_power/$p_combined/$this->m_model->hull());
				if($combined > 0)
					for($i = 1; $i <= $p_combined; $i++) //Find a way to get rid of this for loop, time consuming
						$nonExplodingRatio *= $p_integrity-($p_consumedIntegrity*$i)/$p_combined;
				if($combined % 1.0 != 0.0)
					$nonExplodingRatio *= $p_integrity-$p_consumedIntegrity/$p_combined;
			}
			else
				$nonExplodingRatio = 0.0;
			$explodingRatio = 1 - $nonExplodingRatio;
				
			$newUnstables = $p_amount * $nonExplodingRatio;
			
			$this->m_stableArrTemp[$p_divID]->m_amount -= $p_amount;
			$this->m_stableArrTemp[$p_divID]->m_integrity -= $p_amount * $p_integrity;
			
			$this->m_unstableTemp->m_amount += $newUnstables;
			$this->m_unstableTemp->m_integrity += $newUnstables * ($p_integrity-$p_consumedIntegrity);
			
			$this->debug( "_New explosions : ".number_format($p_amount * $explodingRatio, 2)." : ".number_format($explodingRatio, 2)."<br/>
				_New unstables : ".number_format($newUnstables, 2)."<br/>" );
			return true;
		}
		return false;
	}
};
?>
