<?php

//No black magic here, just a container for all the information related to a unit in OGame.

class UnitSpec {
	private $m_id;
	private $m_isShip;
	private $m_canAttack;
	private $m_canDefend;
	private $m_metal;
	private $m_cristal;
	private $m_deut;
	private $m_hull;
	private $m_shield;
	private $m_power;
	private $m_rapidfireArray;

	public function __construct(
			$p_id,
			$p_isShip,
			$p_canAttack,
			$p_canDefend,
			$p_metal,
			$p_cristal,
			$p_deut,
			$p_hull,
			$p_shield,
			$p_power,
			$p_rapidfireArray){
		$this->m_id = $p_id;
		$this->m_isShip = $p_isShip;
		$this->m_canAttack = $p_canAttack;
		$this->m_canDefend = $p_canDefend;
		$this->m_metal = $p_metal;
		$this->m_cristal = $p_cristal;
		$this->m_deut = $p_deut;
		$this->m_hull = $p_hull;
		$this->m_shield = $p_shield;
		$this->m_power = $p_power;
		$this->m_rapidfireArray = $p_rapidfireArray;
	}
	
	public function id() { 
		return $this->m_id; 
	}
	public function name() { 
		global $name;
		return $name[$this->m_id]; 
	}
	public function isShip() { 
		return $this->m_isShip; 
	}
	public function canAttack() { 
		return $this->m_canAttack; 
	}
	public function canDefend() { 
		return $this->m_canDefend; 
	}
	public function metal() { 
		return $this->m_metal; 
	}
	public function cristal() { 
		return $this->m_cristal; 
	}
	public function deut() { 
		return $this->m_deut; 
	}
	public function cost() { 
		global $g_metal, $g_cristal, $g_deut;
		return $this->m_metal*$g_deut/$g_metal + $this->m_cristal*$g_deut/$g_cristal + $this->m_deut;
	}
	public function hull() { 
		return $this->m_hull; 
	}
	public function shield() { 
		return $this->m_shield; 
	}
	public function power() { 
		return $this->m_power; 
	}
	public function rapidfireAgainst($p_unitShortName) {
		if(isset($this->m_rapidfireArray[$p_unitShortName]))
			return $this->m_rapidfireArray[$p_unitShortName];
		return 1; 
	}
}

?>
