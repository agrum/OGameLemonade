<?php

class Debug {
	protected function debug($p_string)
	{
		if(isset($_GET['debug']))
			echo $p_string;
	}
}

?>
