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
	
	public static function fight($p_fleet1, $p_fleet2){
		$numberSimu = 1;
		
		$outFleet1 = clone $p_fleet1;
		$outFleet2 = clone $p_fleet2;
		
		/*foreach($outFleet1->m_groupArr as $group)
			$group->m_unitArr = array();
		foreach($outFleet2->m_groupArr as $group)
			$group->m_unitArr = array();
		
		for($o = 0; $o < $numberSimu; $o++)
		{
			$simuFleet1 = clone $p_fleet1;
			$simuFleet2 = clone $p_fleet2;
		
			$ended = false;
			//Apply the 6 rounds until destruction / tie
			for($i = 0; $i < 6 && !$ended; $i++)
			{
				$simuFleet1->debug( "Round ".($i+1)."<br/>".PHP_EOL);
				$ended = Fleet::round($simuFleet1, $simuFleet2);
			}
			
			$outFleet1->mergeWith($simuFleet1);
			$outFleet2->mergeWith($simuFleet2);
		}
			
		foreach($outFleet1->m_groupArr as $group)
			$group->m_unitArr = array_slice($group->m_unitArr, 0, count($group->m_unitArr) / $numberSimu);
		foreach($outFleet2->m_groupArr as $group)
			$group->m_unitArr = array_slice($group->m_unitArr, 0, count($group->m_unitArr) / $numberSimu);*/
		
		for($i = 0; $i < 6; $i++)
		{
			$roundFleet1 = clone $outFleet1;
			$roundFleet2 = clone $outFleet2;
			foreach($outFleet1->m_groupArr as $group)
				$group->m_unitArr = array();
			foreach($outFleet2->m_groupArr as $group)
				$group->m_unitArr = array();
				
			$roundFleet1->debug( "<br/>Round ".($i+1)."<br/>".PHP_EOL);
			for($o = 0; $o < $numberSimu; $o++)
			{		
				$simuFleet1 = clone $roundFleet1;
				$simuFleet2 = clone $roundFleet2;
				$ended = Fleet::round($simuFleet1, $simuFleet2);
			
				$outFleet1->mergeWith($simuFleet1);
				$outFleet2->mergeWith($simuFleet2);
			}
		
			foreach($outFleet1->m_groupArr as $group)
			{
				$group->m_unitArr = array_slice($group->m_unitArr, 0, round(count($group->m_unitArr) / $numberSimu));
				$group->debug("Unit left : ".count($group->m_unitArr)."<br/>");
			}
			foreach($outFleet2->m_groupArr as $group)
			{
				$group->m_unitArr = array_slice($group->m_unitArr, 0, round(count($group->m_unitArr) / $numberSimu));
				$group->debug("Unit left : ".count($group->m_unitArr)."<br/>");
			}
		}
		
		//return output fleets
		return array ($outFleet1, $outFleet2);
	}
	
	private static function round(&$p_fleet1, &$p_fleet2){	
		//$p_fleet1->debug( "Alice<br/>".PHP_EOL);
		srand(0);
		$p_fleet1->attackedFrom($p_fleet2);
		
		//$p_fleet2->debug( "Bob<br/>".PHP_EOL);
		srand(0);
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
	
	private function attackedFrom($p_fleet){
		foreach($p_fleet->m_groupArr as $attackingGroup)
		{				
			$amountDefendingUnit = 0;
			foreach($this->m_groupArr as $defendingGroup)
				$amountDefendingUnit += $defendingGroup->amountUnit();
			if($amountDefendingUnit == 0)
				continue;
			
			$shots = $attackingGroup->amountUnit() * $attackingGroup->rapidFire($this->m_groupArr);
			//$this->debug( "Shots : ". $shots ."<br/>");
			foreach($this->m_groupArr as $defendingGroup)
			{			
				$proportion = $defendingGroup->amountUnit()/$amountDefendingUnit;
				$defendingGroup->receiveWave($shots*$proportion, $attackingGroup->m_model->power());
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
	
	private function mergeWith($p_fleet)
	{
		for($i = 0; $i < count($p_fleet->m_groupArr); $i++)
		{
			$start = count($this->m_groupArr[$i]->m_unitArr);
			for($j = 0; $j < count($p_fleet->m_groupArr[$i]->m_unitArr); $j++)
			{
				$this->m_groupArr[$i]->m_unitArr[$j+$start] = $p_fleet->m_groupArr[$i]->m_unitArr[$j];
			}
		}
	}
};

?>
