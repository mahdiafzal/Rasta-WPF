<?php
class Application_Model_Session
{
	static function	clearStorage($mode)
	{
		switch($mode)
		{
			case 'all':	$_SESSION	= array(); break;
		}

	}
}
?>