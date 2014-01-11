<?php

$debug = "";

class Debug {
	protected function debug($p_string)
	{
		global $debug;
		$debug .= $p_string;
	}
}

?>
