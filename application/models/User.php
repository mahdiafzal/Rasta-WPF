<?php
class Application_Model_User
{

	var $session;
	static function ID() 
	{
		if( isset($_SESSION['Zend_Auth']['storage']->id) ) return $_SESSION['Zend_Auth']['storage']->id;
		return false;
	}
	
}	
?>