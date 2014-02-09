<?php

include_once 'group.class.php';

class Fleet extends Debug {
	public $m_groupArr;
	public $m_name;
	
	public function __construct($p_groupArr, $p_name){
		$this->m_groupArr = $p_groupArr;
		$this->m_name = $p_name;
	}
	
	public function __clone()
	{
		foreach($this->m_groupArr as $s => $group)
			$this->m_groupArr[$s] = clone $group;
	}
	
	public function rapidFireFrom($p_model)
	{
		$tech = array(0, 0, 0);
		$group = new Group($p_model, 1, $tech);
		
		return $group->rapidFire($this->m_groupArr);
	}
	
	public function name()
	{
		$this->m_name;
	}
	
	public function composition()
	{
		return $this->m_groupArr;
	}
	
	public function value()
	{
		$value = 0;
		
		foreach($this->m_groupArr as &$group)
		{
			$value += $group->value();
		}
		
		return $value;
	}
	
	public function setValue($p_newValue)
	{
		$changeCoef = $p_newValue / $this->value();
		
		foreach($this->m_groupArr as &$group)
		{
			$group->setValue($changeCoef);
		}
	}
	
	public static function fight($p_fleet1, $p_fleet2){
		$ended = false;
		//Apply the 6 rounds until destruction / tie
		for($i = 0; $i < 6 && !$ended; $i++)
		{
			$p_fleet1->debug( "Round ".($i+1)."<br/>".PHP_EOL);
			$ended = Fleet::round($p_fleet1, $p_fleet2);
		}
			
		//return output fleets
		return array ($p_fleet1, $p_fleet2);
	}
	
	private static function round(&$p_fleet1, &$p_fleet2){		
		$p_fleet1->initRound();
		$p_fleet2->initRound();

		$p_fleet1->debug( "Alice<br/>".PHP_EOL);
		$p_fleet1->attackedFrom($p_fleet2);
		
		$p_fleet2->debug( "Bob<br/>".PHP_EOL);
		$p_fleet2->attackedFrom($p_fleet1);

		$p_fleet1->applyRound();
		$p_fleet2->applyRound();
		
		return ($p_fleet1->isDestroyed() || $p_fleet2->isDestroyed());
	}
		
	private function isDestroyed(){
	foreach($this->m_groupArr as $group)
		{
			if($group->amountUnit() > 0)
				return false;
		}
		
		return true;
	}
	
	private function initRound()
	{
		foreach($this->m_groupArr as $group)
		{
			$group->initRound();
		}
	}
	
	private function attackedFrom($p_fleet){
		foreach($p_fleet->m_groupArr as $attackingGroup)
		{		
			if($attackingGroup->amountUnitTemp() <= 0.0)
				continue;
				
			$amountDefendingUnit = 0;
			foreach($this->m_groupArr as $defendingGroup)
				$amountDefendingUnit += $defendingGroup->amountUnit();
			
			$shots = $attackingGroup->amountUnit() * $attackingGroup->rapidFire($this->m_groupArr);
			foreach($this->m_groupArr as $defendingGroup)
			{			
				if($defendingGroup->amountUnitTemp() <= 0.0)
					continue;
			
				$proportion = $defendingGroup->amountUnit()/$amountDefendingUnit;
				$defendingGroup->receiveWave($shots*$proportion, $attackingGroup->m_model->power());
			}
		}
	}
	
	private function applyRound()
	{
		foreach($this->m_groupArr as $group)
		{
			$group->applyRound();
		}
	}
};

?>
