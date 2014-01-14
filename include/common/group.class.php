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
	public $m_amountDiv;
	public $m_divCoverage;
	
	public $m_divArr;
	
	public $m_amountWShieldTemp;
	public $m_shieldTemp;
	public $m_divArrTemp;
	
	public $m_averageShieldInWave;
	public $m_probaHitShieldInWave;
	public $m_divArrInWave;
	
	public function __construct($p_model, $p_amount)
	{
		$this->m_model = $p_model;
		$this->m_amountDiv = 30;
		$this->m_divCoverage = 0.5;
		
		$this->m_divArr[0] = new Division($p_amount);
		for($i = 1; $i < $this->m_amountDiv; $i++)
			$this->m_divArr[$i] = new Division(0);
	}	
	
	public function __clone()
	{
		$this->m_model = $this->m_model;
		
		for($i = 0; $i < count($this->m_divArr); $i++)
			$this->m_divArr[$i] = clone $this->m_divArr[$i];
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
		foreach ($this->m_divArr as &$div)
		{
			$div->m_amount *= $p_coef;
			$div->m_integrity *= $p_coef;
		}
	}
	
	public function amountUnit(){
		$count = 0;
		foreach ($this->m_divArr as &$div)
			$count += $div->m_amount;
		return $count;
	}
	
	public function amountUnitTemp(){
		$count = 0;
		foreach ($this->m_divArrTemp as &$div)
			$count += $div->m_amount;
		return $count;
	}
	
	public function amountUnitInWave(){
		$count = 0;
		foreach ($this->m_divArrInWave as &$div)
			$count += $div->m_amount;
		return $count;
	}
	
	public function getTopIntegrityForDiv($p_id){
		return $this->m_divCoverage * (count($this->m_divArr) - $p_id) / count($this->m_divArr) + (1.0 - $this->m_divCoverage);
	}
	
	public function getDivIDFromIntegrityNorm($p_integrityNorm){
		return round((count($this->m_divArr) - 1) * (1.0 - (max($p_integrityNorm - (1.0 - $this->m_divCoverage), 0.0) / $this->m_divCoverage)));
	}
	
	//Store the current stats in temporary variables that can be modified without affecting the class
	public function initRound()
	{
		$this->m_amountWShieldTemp = $this->amountUnit();
		$this->m_shieldTemp = $this->m_model->shield()*$this->amountUnit();
		foreach ($this->m_divArr as $i => $stable)
			$this->m_divArrTemp[$i] = clone $stable;
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
		$amountHit = $p_amountHit*$this->amountUnitTemp()/$this->amountUnit();
		$amountUnit = $this->amountUnitTemp();
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
		$amountUnitHit = $amountUnit * ( 1 - pow(($amountUnit-1)/$amountUnit, $amountHit));
		
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
		foreach ($this->m_divArrTemp as $i => $stableTemp)
			$this->m_divArrInWave[$i] = clone $stableTemp;
	
		if($amountHit / $amountUnit >= 10)
		{
			$r = $amountHit / $amountUnit;
			$gaussiaSpan = 25.0 * sqrt($r / 50.0);
			$distributionArr = array(-0.38, -0.16, 0.0, 0.16, 0.38);
			foreach($distributionArr as $d)
			{
				$this->ackImpact(
					$amountUnit / 5.0, 
					$p_power * ($r + $gaussiaSpan*$d), 
					$r + $gaussiaSpan*$d);
			}
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
		for($i = 0; $i < count($this->m_divArrTemp); $i++)
		{
			$div = $this->m_divArrTemp[$i];
			if($div->m_amount > 0)
				$this->debug( "_Division ".$i." ".number_format($this->getTopIntegrityForDiv($i), 2)." : ".number_format($div->m_amount, 3)." (Integrity of ".number_format($div->integrityNorm(), 2).")<br/>" );
		}
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
			$unstableTempBefore = $this->m_divArrTemp[count($this->m_divArrTemp)-1]->m_amount;
			$this->affectIntegrity($amountHitOnShield, $p_power, $p_combined);
			$unstableTempAfter =  $this->m_divArrTemp[count($this->m_divArrTemp)-1]->m_amount;
		
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
		
		$amountInWave = $this->amountUnitInWave();
		if($amountInWave == 0)
			return;
		
		//Manage stables
		for($i = 0; $i < count($this->m_divArr); $i++)
		{
			if($this->m_divArrInWave[$i]->m_amount / $amountInWave > 0.01)
			{
				$affected = $p_amount * $this->m_divArrInWave[$i]->m_amount / $amountInWave;

				$integrityNorm = $this->m_divArrInWave[$i]->integrityNorm();
		
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
				$this->debug( "_Dest : ".$destDivId." (".$i.") (".$integrityNorm.")<br/>" );
		
				$this->m_divArrTemp[$i]->m_amount -= $affected;
				$this->m_divArrTemp[$i]->m_integrity -= $affected * $integrityNorm;
		
				$this->m_divArrTemp[$destDivId]->m_amount += $nonExploding;
				$this->m_divArrTemp[$destDivId]->m_integrity += $nonExploding * ($integrityLeft);
		
				if($affected * $explodingRatio > 0)
					$this->debug( "_Explosions : ".number_format($affected * $explodingRatio, 2)." : ".number_format($explodingRatio, 2)."<br/>" );
			}
		}
	}
	
	//Modify the group according to the temporary changes made
	public function applyRound()
	{
		foreach ($this->m_divArrTemp as $i => $stableTemp)
			$this->m_divArr[$i] = clone $stableTemp;
	}
};
?>
