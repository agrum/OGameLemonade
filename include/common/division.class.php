<?php

class Division {
	public $m_amount;
	public $m_integrity;
	
	public function __construct($p_amount)
	{
		$this->m_amount = $p_amount;
		$this->m_integrity = 1.0 * $p_amount;
	}
	
	public function __clone()
	{
		$this->m_amount = $this->m_amount;
		$this->m_integrity = $this->m_integrity;
	}
	
	public function integrityNorm(){
		return $this->m_integrity / $this->m_amount;
	}
};

?>
