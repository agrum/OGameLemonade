<?php

include 'spec.php';
include 'unit.class.php';
include 'debug.class.php';

//Here lies the magic 'woulouloouuuu....'

//Nope, calssic one

class Group extends Debug{
	public $m_model;
	public $m_unitArr;
	
	public function __construct($p_model, $p_amount)
	{
		$this->m_model = $p_model;
		for($i = 0; $i < $p_amount; $i++)
		{
			$this->m_unitArr[$i] = new Unit($this->m_model->id());
		}
	}
	
	public function __clone()
	{
		$this->m_model = $this->m_model;
		for($i = 0; $i < count($this->m_unitArr); $i++)
			$this->m_unitArr[$i] = clone $this->m_unitArr[$i];
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
		return count($this->m_unitArr);
	}
	
	//Part of a round, which is just a set of fires of different magnitudes
	public function receiveWave($p_amountHit, $p_power)
	{
		for($i = 0; $i < $p_amountHit; $i++)
		{
			$id = rand()%$this->amountUnit();
			$this->m_unitArr[$id]->receive($p_power);
		}
	}
	
	//Modify the group according to the temporary changes made
	public function applyRound()
	{
		$standingUnitArr = array();
		$i = 0;
		
		foreach($this->m_unitArr as $unit)
		{
			if($unit->reset()) //Can't reset a dead unit
				$standingUnitArr[$i++] = $unit;
		}
		
		$this->m_unitArr = $standingUnitArr;
	}
};
?>
