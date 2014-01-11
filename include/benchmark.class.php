<?php

include '../../connectDB.php';
include 'common/fleet.class.php';

class Benchmark {
	private $m_fleetBenchmarked;

	public function __construct($p_fleetBenchmarked)
	{
		$this->m_fleetBenchmarked = $p_fleetBenchmarked;
	}
	
	public function encounterInformation()
	{
		global $model;
		
		//Get samples
		$mysqli = connectDB("lemonade");

		$fightCells = "";
		$req_build = $mysqli->query('SELECT * FROM build ORDER BY id ASC');
		while ($dns_build = $req_build->fetch_assoc()) 
		{
			$build;
			$compoBuild = array();
			$compoStr = preg_split('#;#', $dns_build['content']);
			$groupArr;
			unset ($groupArr);
			$i = 0;
			foreach($compoStr as $unit)
			{
				$unitSplit = preg_split('#:#', $unit);
				if(
					count($unitSplit) == 2 && 
					isset($model[$unitSplit[0]]) && 
					is_numeric($unitSplit[1]) && 
					$unitSplit[1] > 0)
				{
					$groupArr[$i++] = new Group($model[$unitSplit[0]], $unitSplit[1]);
				}
			}
			
			$encounterFleet = new Fleet($groupArr, $dns_build['name']);
			
			$sideAHasUnmovable = false;
			$sideBHasUnmovable = false;
			foreach($this->m_fleetBenchmarked->m_groupArr as $group)
				if(!$group->m_model->canAttack())
					$sideAHasUnmovable = true;
			foreach($encounterFleet->m_groupArr as $group)
				if(!$group->m_model->canAttack())
					$sideBHasUnmovable = true;
	
			if(!$sideAHasUnmovable || !$sideBHasUnmovable)
			{
				$record = $this->benchmark($this->m_fleetBenchmarked, $encounterFleet, $_POST['ratio']/100);
				$compoStr = $record['compo0Str'] .PHP_EOL. $record['compo1Str'];
			
				$fightCells .= 
				'<tr>'.PHP_EOL.
				$this->getInfoFromFleet($encounterFleet).
				'<td '.
				'style="text-align:right;color:rgb('.round(12*(-$record['grade']+10)).','.round(12*($record['grade']+10)).',50);" '.
				'title="'.$compoStr.'">'.
				$record['grade'].
				'</td></tr>'.PHP_EOL;
			}
			else
				$fightCells .= '<tr>'.$this->getInfoFromFleet($encounterFleet).'</td><td style="text-align:right;">/</td></tr>';
		}
	
		return '<table class="reportTable">'.PHP_EOL.
		'<tr><th>VS Composition</th><th>Note</th></tr>'.PHP_EOL.
		$fightCells.PHP_EOL.
		'</table>'.PHP_EOL;
	}
	
	public function costInformation()
	{
		$amount = 0;
		$value = 0;
		$costM = 0;
		$costC = 0;
		$costD = 0;
		//Get cumlative values
		foreach($this->m_fleetBenchmarked->m_groupArr as $group)
		{
			$amount += $group->amountUnit();
			$value += $group->value();
			$costM += $amount*$group->m_model->metal();
			$costC += $amount*$group->m_model->cristal();
			$costD += $amount*$group->m_model->deut();
		}
		//Use cumulative values
		$proportionGroups = '';
		foreach($this->m_fleetBenchmarked->m_groupArr as $group)
		{
			$proportionAmount = number_format(100*$group->amountUnit()/$amount, 1);
			$proportionCost = number_format(100*$group->value()/$value, 1);
			$proportionGroups .= 
				'<tr><td>' .
				$group->m_model->name() .
				'</td><td>' .
				$proportionAmount .
				'%</td><td>' .
				$proportionCost .
				'%</td></tr>' .
				PHP_EOL;
		}
		$proportionM = number_format(100*$costM/($costM+$costC+$costD), 1);
		$proportionC = number_format(100*$costC/($costM+$costC+$costD), 1);
		$proportionD = number_format(100*$costD/($costM+$costC+$costD), 1);
		
		return '<table class="reportTable">'.PHP_EOL.
			'<tr><th>Unite</th><th>Quantite</th><th>Cout</th></tr>'.PHP_EOL.
			$proportionGroups.PHP_EOL.
			'<tr><td colspan="3"> </td></tr>'.PHP_EOL.
			'<tr><td colspan="2">Proportion de metal</td><td>'.$proportionM.'%</td></tr>'.PHP_EOL.
			'<tr><td colspan="2">Proportion de cristal</td><td>'.$proportionC.'%</td></tr>'.PHP_EOL.
			'<tr><td colspan="2">Proportion de deuterium</td><td>'.$proportionD.'%</td></tr>'.PHP_EOL.
			'</table>'.PHP_EOL;
	}
	
	public function sensibilityInformation()
	{
		global $model, $cruis, $batCr, $bombe, $destr, $death, $probe, $misIP, $misIn;
		//We only acknoledge the rapidfire of 5 units
		$rapidfireAck = array($cruis, $batCr, $bombe, $destr, $death);
		$rapidfireCells = "";
		
		for($i = 0; $i < count($rapidfireAck); $i++)
		{
			$rapidFireReceived = number_format($this->m_fleetBenchmarked->rapidFireFrom($model[$rapidfireAck[$i]]), 1);
			$rapidfireCells .= 
				'<tr><td>' . 
				$model[$rapidfireAck[$i]]->name() . 
				' : </td><td>' . 
				$rapidFireReceived . 
				'</td></tr>'.PHP_EOL;
		}

		//Surcharge indice, from 0 to 10, define the swarmness of a composition
		//	0 being the lowest level (full deathstar) and 10 the highest (full probe)
		$amount = 0;
		$base = 0;
		foreach($this->m_fleetBenchmarked->m_groupArr as $group)
			$amount += $group->amountUnit();
		foreach($this->m_fleetBenchmarked->m_groupArr as $group){
			$base += $group->value()/$amount;
		}
		$surchargeMax = number_format(log($model[$death]->cost()/$model[$probe]->cost(), 2), 1);
		$surcharge = number_format(log($model[$death]->cost()/$base, 2) *10.0/$surchargeMax, 1);
	
		//Sensibility to MIPs
		$mipSensibility = "/"; //By default deactivated
		$costDefense = 0;
		$hullDefense = 0;
		foreach($this->m_fleetBenchmarked->m_groupArr as $group)
		{
			if(!$group->m_model->isShip())
			{
				$costDefense += $group->value();
				$hullDefense += $group->m_model->hull()*$group->amountUnit();
			}
		}
		$amountMIP = $hullDefense / $model[$misIP]->power();
		$costMIP = $model[$misIP]->cost() * $amountMIP;
		$amountMInt = ($costDefense-$costMIP)/($model[$misIP]->cost() - $model[$misIn]->cost());
		if($amountMIP > 0)
			$mipSensibility = number_format(100 * $amountMInt / ($amountMInt + $amountMIP), 1) . "%";

		return '<table class="reportTable">'.PHP_EOL.
		'<tr><th>Unite</th><th>RapidFire</th></tr>'.PHP_EOL.
		$rapidfireCells.
		'<tr><td colspan="2"> </td></tr>'.PHP_EOL.
		'<tr><td colspan="2">Indice de surcharge : '.($surcharge).'</td></tr>'.PHP_EOL.
		'<tr><td colspan="2"> </td></tr>'.PHP_EOL.
		'<tr><td colspan="2">MIPs a intercepter : '.($mipSensibility).'</td></tr>'.PHP_EOL.
		'</table>'.PHP_EOL;
	}
	
	private function getInfoFromFleet($p_fleet){
		//When clicking on a preset, it is used as input for a new benchmark
		$form = '<form action="" method="post">'.PHP_EOL.
			'<input name="metal" type="hidden" value="3"/>'.PHP_EOL.
			'<input name="cristal" type="hidden" value="2"/>'.PHP_EOL.
			'<input name="deut" type="hidden" value="1"/>'.PHP_EOL.
			'<input name="ratio" type="hidden" value="100"/>'.PHP_EOL;
		$min = INF;
		$title = "";

		foreach($p_fleet->m_groupArr as $group)
			if($group->amountUnit() < $min)
				$min = $group->amountUnit();
	
		foreach($p_fleet->m_groupArr as $group)
		{
			$title .= $group->m_model->name() . ' : ' . number_format($group->amountUnit()/$min, 1) . PHP_EOL;
			$form .= '<input name="'.$group->m_model->id().'" type="hidden" value="'.$group->amountUnit()/$min.'"/> '. PHP_EOL;
		}

		$form .= '<input type="submit" class="buildSubmit" value="'.$p_fleet->m_name.'" /></form>'.PHP_EOL;
		
		return '<td title="'.$title.'">'.$form.'</td>';
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

	private function benchmark($p_fleetA, $p_fleetB, $powerRatio){
		global $model, $death;
	
		$trialValue = 1000000.0 * $model[$death]->cost(); //Trial on fleets equivalent to 1 000 000 RIPs
	
		//Balance fleets
		$p_fleetA->setValue($trialValue*$powerRatio);
		$p_fleetB->setValue($trialValue);

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
			$record['compo'.$o.'Str'] = $p_fleetA->m_name;
			for($i = 0; $i < count($fleetInitial[$o]->m_groupArr); $i++)
			{
				$record['compo'.$o.'Str'] .= 
					PHP_EOL .
					$fleetInitial[$o]->m_groupArr[$i]->m_model->name() . 
					' : ' . 
					number_format($fleetAfterFight[$o]->m_groupArr[$i]->amountUnit()) .
					' /  ' .
					number_format($fleetInitial[$o]->m_groupArr[$i]->amountUnit());
			}
		}
		
		return $record;
	}
}

?>
