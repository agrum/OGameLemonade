<?php

include 'spec.php';
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
	
	public $m_untouched;
	public $m_stable;
	public $m_stableIntegrity;
	public $m_unstable;
	public $m_unstableIntegrity;
	
	public $m_amountWShieldTemp;
	public $m_shieldTemp;
	public $m_untouchedTemp;
	public $m_stableTemp;
	public $m_stableIntegrityTemp;
	public $m_unstableTemp;
	public $m_unstableIntegrityTemp;
	
	public $m_averageShieldInWave;
	public $m_probaHitShieldInWave;
	public $m_untouchedInWave;
	public $m_stableInWave;
	public $m_stableIntegrityInWave;
	public $m_unstableInWave;
	public $m_unstableIntegrityInWave;
	
	public function __construct($p_model, $p_amount)
	{
		$this->m_model = $p_model;
		$this->m_untouched = $p_amount;
		$this->m_stable = 0;
		$this->m_stableIntegrity = 0.0;
		$this->m_unstable = 0;
		$this->m_unstableIntegrity = 0.0;
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
		return $this->m_untouched + $this->m_stable + $this->m_unstable;
	}
	
	public function amountUnitTemp(){
		return $this->m_untouchedTemp + $this->m_stableTemp + $this->m_unstableTemp;
	}
	
	public function amountUnitInWave(){
		return $this->m_untouchedInWave + $this->m_stableInWave + $this->m_unstableInWave;
	}
	
	//Store the current stats in temporary variables that can be modified without affecting the class
	public function initRound()
	{
		$this->m_amountWShieldTemp = $this->amountUnit();
		$this->m_shieldTemp = $this->m_model->shield()*$this->amountUnit();
		
		$this->m_untouchedTemp = $this->m_untouched;
		
		$this->m_stableTemp = $this->m_stable;
		$this->m_stableIntegrityTemp = $this->m_stableIntegrity;
		
		$this->m_unstableTemp = $this->m_unstable;
		$this->m_unstableIntegrityTemp = $this->m_unstableIntegrity;
	}
	
	//Part of a round, which is just a set of fires of different magnitudes
	public function receiveWave($p_amountHit, $p_power)
	{
		if($this->m_model->shield() > 100*$p_power)
		{
			return; //Deflected
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
		$this->m_averageShieldInWave = $this->m_shieldTemp/$this->m_amountWShieldTemp;
		$this->m_probaHitShieldInWave = $this->m_amountWShieldTemp/$this->amountUnitTemp();
		$this->m_untouchedInWave = $this->m_untouchedTemp;
		$this->m_stableInWave = $this->m_stableTemp;
		$this->m_stableIntegrityInWave = $this->m_stableIntegrityTemp;
		$this->m_unstableInWave = $this->m_unstableTemp;
		$this->m_unstableIntegrityInWave = $this->m_unstableIntegrityTemp;
		
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
			$amountUnitHitTwiceOrMore = $amountUnitHit * ( 1 - pow(($amountUnitHit-1)/$amountUnitHit, $amountHit-$amountUnitHit));
			$amountUnitHitOnce = $amountUnitHit - $amountUnitHitTwiceOrMore;
			
			//We treat the units hit once
			if($amountUnitHitOnce > 1)
				$this->ackImpact($amountUnitHitOnce, $combinedPower, $combined);
			elseif($amountUnitHitTwiceOrMore <= 1)
				break;
			
			$amountHit -= $amountUnitHitOnce;
			//And increase the power by combining the shots for the next loop iteration
			/*
			//+1 Combination
			$amountHit /= ($combinedPower + $uniquePower)/$combinedPower;
			$amountUnitHit = $amountUnitHitTwiceOrMore;
			$combinedPower += $uniquePower;
			$combined++;*/
			
			//*2 Combination (way faster, less precise)
			$amountHit /= 2;
			$amountUnitHit = $amountUnitHitTwiceOrMore;
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
		$this->debug( "_Untouched : ".number_format($this->m_untouchedTemp, 2)."<br/>" );
		if($this->m_stableTemp > 0)
			$this->debug( "_Stables : ".number_format($this->m_stableTemp, 2)." (Integrity of ".number_format($this->m_stableIntegrityTemp/$this->m_stableTemp, 2).")<br/>" );
		if($this->m_unstableTemp > 0)
			$this->debug( "_Unstables : ".number_format($this->m_unstableTemp, 2)." (Integrity of ".number_format($this->m_unstableIntegrityTemp/$this->m_unstableTemp, 2).")<br/><br/>".PHP_EOL );
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
			
		//Shield effect
		$absorptionPotential = $amountHitOnShield*$this->m_averageShieldInWave;
		$absorbed = min($absorptionPotential, $amountHitOnShield*$p_power);
		$this->m_shieldTemp -= $absorbed;
		$p_power -= $absorbed/$amountHitOnShield;
		$this->debug( "_Power deflected : ".number_format($absorbed)." <br/>" );
		
		//Direct hit after absorbtion (must trigger even with null power for
		//	explosion)
		$this->affectIntegrity($amountHitOnShield, $p_power, $p_combined);
		//Reduce the number of unit with shield if all has been consumed
		if($absorbed == $absorptionPotential)
			$this->m_amountWShieldTemp -= $amountHitOnShield;
	}
	
	//Acknoledge a number of distinct units hit with a certain combined power.
	// Will now affect the integrity and wmount of unit.
	public function affectIntegrity($p_amount, $p_power, $p_combined)
	{				
		//Integrity consumed by the hit
		$consumedIntegrity = $p_power/$this->m_model->hull();
		if($consumedIntegrity == 0)
			return;
		
		//Exploding	old instables
		if($this->m_unstableInWave > 0)
		{
			//Get the ratio of unstable in the overall set of unit
			$unstableRatio = $this->m_unstableInWave/$this->amountUnitInWave();

			$nonExplodingRatio = 1.0;
			$integrity = $this->m_unstableIntegrityInWave/$this->m_unstableInWave;
			if($integrity - $consumedIntegrity > 0)
			{
				for($i = 1; $i <= $p_combined; $i++) //Find a way to get rid of this for loop, time consuming
					$nonExplodingRatio *= $integrity-($consumedIntegrity*$i)/$p_combined;
				if($p_combined % 1.0 != 0.0)
					$nonExplodingRatio *= $integrity-$consumedIntegrity/$p_combined;
			}
			else
				$nonExplodingRatio = 0.0;
			$explodingUnstables = $p_amount*$unstableRatio*(1-$nonExplodingRatio);
		
			$this->m_unstableTemp -= $explodingUnstables;
			$this->m_unstableIntegrityTemp -= $explodingUnstables * $integrity;
		
			//Remove he processed hit on unstables
			$p_amount *= 1 - $unstableRatio;
			
			$this->debug( "_Unstables exploding : ".number_format($explodingUnstables, 2)."<br/>" );
		}
		
		$affectedUntouched = $p_amount * $this->m_untouchedInWave / ($this->m_untouchedInWave+$this->m_stableInWave);
		$affectedStable = $p_amount * $this->m_stableInWave / ($this->m_untouchedInWave+$this->m_stableInWave);
		
		//Create new unstables from untouched group
		if($this->createNewUnstables(1.0, $consumedIntegrity, $affectedUntouched, $p_combined, $p_power))
		{
			$this->m_untouchedTemp -= $affectedUntouched;
			$p_amount -= $affectedUntouched;
		}
		
		//Create new unstables from stable group
		if($this->m_stable > 0 && 
			$this->createNewUnstables($this->m_stableIntegrityInWave/$this->m_stableInWave, $consumedIntegrity, $affectedStable, $p_combined, $p_power))
		{
			$this->m_stableTemp -= $affectedStable;
			$this->m_stableIntegrityTemp -= $affectedStable * $this->m_stableIntegrityInWave/$this->m_stableInWave;
			$p_amount -= $affectedStable;
		}
		
		//change integrity for stables
		if($p_amount > 0 && $this->m_untouchedTemp > 0)
		{
			$newStables = $p_amount * $this->m_untouchedInWave / ($this->m_untouchedInWave+$this->m_stableInWave);
			
			$this->m_stableIntegrityTemp += (1.0 - $consumedIntegrity ) * $newStables;
			if($this->m_stableInWave > 0)
				$this->m_stableIntegrityTemp -= $consumedIntegrity * ($p_amount - $newStables);
			$this->m_untouchedTemp -= $newStables;
			$this->m_stableTemp += $newStables;
			$this->debug( "_New stables : ".number_format($newStables, 2)."<br/>" );
			/*if($this->m_stable > 0)
				$this->debug( "_New stables Int : ".number_format($this->m_stableIntegrityTemp/$this->m_stableTemp, 2)."<br/>" );*/
		}
	}
	
	public function createNewUnstables($p_integrity, $p_consumedIntegrity, $p_amount, $p_combined, $p_power)
	{
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
			$this->m_unstableIntegrityTemp += $newUnstables * ($p_integrity-$p_consumedIntegrity);
			$this->m_unstableTemp += $newUnstables;
			
			$this->debug( "_New explosions : ".number_format($p_amount * $explodingRatio, 2)." : ".number_format($explodingRatio, 2)."<br/>
				_New unstables : ".number_format($newUnstables, 2)."<br/>" );
			return true;
		}
		return false;
	}
	
	//Modify the group according to the temporary changes made
	public function applyRound()
	{
		$this->m_untouched = $this->m_untouchedTemp;
		
		$this->m_stable = $this->m_stableTemp;
		$this->m_stableIntegrity = $this->m_stableIntegrityTemp;
		
		$this->m_unstable = $this->m_unstableTemp;
		$this->m_unstableIntegrity = $this->m_unstableIntegrityTemp;
	}
};
?>
