<?php

include 'common/fleet.class.php';

class Fight {
	private $m_fleetA;
	private $m_fleetB;

	public function __construct($p_fleetA, $p_fleetB)
	{
		$this->m_fleetA = $p_fleetA;
		$this->m_fleetB = $p_fleetB;
	}
	
	public function encounterInformation()
	{
		$fightCells = "";
			
		$sideAHasUnmovable = false;
		$sideBHasUnmovable = false;
		foreach($this->m_fleetA->m_groupArr as $group)
			if(!$group->m_model->canAttack())
				$sideAHasUnmovable = true;
		foreach($this->m_fleetB->m_groupArr as $group)
			if(!$group->m_model->canAttack())
				$sideBHasUnmovable = true;

		if(!$sideAHasUnmovable || !$sideBHasUnmovable)
		{
			$record = $this->fight($this->m_fleetA, $this->m_fleetB);
			$compoStr = $record['compo0Str'] .PHP_EOL. $record['compo1Str'];
		
			$fightCells .= 
			'<tr>'.PHP_EOL.
			'<td>'.PHP_EOL.
			$record['compo0Str']. PHP_EOL.PHP_EOL.
			$record['compo1Str']. PHP_EOL.
			'</td>'.PHP_EOL.
			'<td '.
			'style="text-align:right;color:rgb('.round(12*(-$record['grade']+10)).','.round(12*($record['grade']+10)).',50);" '.
			'title="'.$compoStr.'">'.
			$record['grade'].
			'</td></tr>'.PHP_EOL;
		}
		else
			$fightCells .= '<tr>'.$this->getInfoFromFleet($encounterFleet).'</td><td style="text-align:right;">/</td></tr>';
	
		return '<table class="reportTable">'.PHP_EOL.
		'<tr><th>VS Composition</th><th>Note</th></tr>'.PHP_EOL.
		$fightCells.PHP_EOL.
		'</table>'.PHP_EOL;
	}

	private function grade($p_fleetInitial, $p_fleetAfterFight){
		$valueBefore = 0;
		$valueAfter = 0;
	
		for($i = 0; $i < count($p_fleetInitial->m_groupArr); $i++)
		{
			$valueBefore += $p_fleetInitial->m_groupArr[$i]->value();
			$valueAfter += $p_fleetAfterFight->m_groupArr[$i]->value();
			$valueAfter += ($p_fleetInitial->m_groupArr[$i]->value() - $p_fleetAfterFight->m_groupArr[$i]->value())*($p_fleetInitial->m_groupArr[$i]->m_model->isShip() ? 0.3 : 0.7);
		}
		return 0.000035 + $valueAfter/$valueBefore;
	}

	private function fight($p_fleetA, $p_fleetB){
		global $model, $death;

		//Store intial fleets
		$fleetInitial[0] = $p_fleetA;
		$fleetInitial[1] = $p_fleetB;
	
		//Make dem fight
		$fleetAfterFight = Fleet::fight(clone $p_fleetA, clone $p_fleetB);
	
		//Compose benchmark
		$record;
	
		//The grade is a value in between -10 and 10. 
		$gradeRaw = log($this->grade($fleetInitial[0], $fleetAfterFight[0])/$this->grade($fleetInitial[1], $fleetAfterFight[1]))/10;
		$record['grade'] = number_format( 10*($gradeRaw > 0 ? 1 : -1)*pow(abs($gradeRaw), 1/3), 1 );
	
		for($o = 0; $o < count($fleetInitial); $o++){
			$record['compo'.$o] = $fleetAfterFight[$o]->m_groupArr;
			$record['compo'.$o.'Str'] = $fleetAfterFight[$o]->m_name;
			for($i = 0; $i < count($fleetInitial[$o]->m_groupArr); $i++)
			{
				$record['compo'.$o.'Str'] .= 
					PHP_EOL .
					$fleetInitial[$o]->m_groupArr[$i]->m_model->name() . 
					' : ' . 
					number_format($fleetAfterFight[$o]->m_groupArr[$i]->amountUnit(), 1) .
					' /  ' .
					number_format($fleetInitial[$o]->m_groupArr[$i]->amountUnit(), 1);
			}
		}
		
		return $record;
	}
}

?>
