<?php

class Unit {
	public $m_hull;
	public $m_shield;
	
	public function __construct(
			$p_id){		
		global $model;
		
		$this->m_id = $p_id;
		$this->m_hull = $model[$p_id]->hull();
		$this->m_shield = $model[$p_id]->shield();
	}

	public function __clone(){
		$this->m_id = $this->m_id;
		$this->m_hull = $this->m_hull;
		$this->m_shield = $this->m_shield;
	}

	public function reset(){		
		global $model;
		
		$this->m_shield = $model[$this->m_id]->shield();
			
		return ($this->m_hull > 0);
	}
	
	public function receive($p_power){	
		global $model;
		
		if($this->m_hull == 0)
			return;
		
		$absorbed = min($p_power, $this->m_shield);
		$p_power -= $absorbed;
		
		$this->m_shield -= $absorbed;
		$this->m_hull -= $p_power;
		
		$integrity = $this->m_hull / $model[$this->m_id]->hull();
		if($this->m_shield == 0 && 
			$integrity <= 0.7 &&
			(rand()%100) >= $integrity*100)
			$this->m_hull = 0;
	}
};

?>
