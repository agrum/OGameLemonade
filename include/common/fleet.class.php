<?php

include 'group.class.php';

class Fleet extends Debug {
	public $m_groupArr;
	public $m_name;
	
	public function __construct($p_groupArr, $p_name){
		$this->m_groupArr = $p_groupArr;
		$this->m_name = $p_name;
	}
	
	public function __clone()
	{
		for($i = 0; $i < count($this->m_groupArr); $i++)
			$this->m_groupArr[$i] = clone $this->m_groupArr[$i];
	}
	
	public function rapidFireFrom($p_model)
	{
		$group = new Group($p_model, 1);
		
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
		
		for($i = 0; $i < count($this->m_groupArr); $i++)
		{
			$value += $this->m_groupArr[$i]->value();
		}
		
		return $value;
	}
	
	public function setValue($p_newValue)
	{
		$changeCoef = $p_newValue / $this->value();
		
		for($i = 0; $i < count($this->m_groupArr); $i++)
		{
			$this->m_groupArr[$i]->m_stable *= $changeCoef;
			$this->m_groupArr[$i]->m_unstable *= $changeCoef;
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
		for($i = 0; $i < count($this->m_groupArr); $i++)
		{
			if($this->m_groupArr[$i]->amountUnit() > 0)
				return false;
		}
		
		return true;
	}
	
	private function initRound()
	{
		for($i = 0; $i < count($this->m_groupArr); $i++)
		{
			$this->m_groupArr[$i]->initRound();
		}
	}
	
	private function attackedFrom($p_fleet){
		for($j = 0; $j < count($p_fleet->m_groupArr); $j++)
		{
			if($p_fleet->m_groupArr[$j]->amountUnitTemp() == 0)
				continue;
				
			$amountDefendingUnit = 0;
			for($i = 0; $i < count($this->m_groupArr); $i++)
				$amountDefendingUnit += $this->m_groupArr[$i]->amountUnit();
			
			$shots = $p_fleet->m_groupArr[$j]->amountUnit() * $p_fleet->m_groupArr[$j]->rapidFire($this->m_groupArr);
			for($i = 0; $i < count($this->m_groupArr); $i++)
			{
				if($this->m_groupArr[$i]->amountUnitTemp() < 0.5)
				{
					continue;
				}
					
				$proportion = $this->m_groupArr[$i]->amountUnit()/$amountDefendingUnit;
				$this->m_groupArr[$i]->receiveWave($shots*$proportion, $p_fleet->m_groupArr[$j]->m_model->power());
				
				if($this->m_groupArr[$i]->amountUnitTemp() < 1)
				{
					$this->m_groupArr[$i]->m_stableTemp = 0;
					$this->m_groupArr[$i]->m_unstableTemp = 0;
				}
			}
		}
	}
	
	private function applyRound()
	{
		for($i = 0; $i < count($this->m_groupArr); $i++)
		{
			$this->m_groupArr[$i]->applyRound();
		}
	}
};

?>
