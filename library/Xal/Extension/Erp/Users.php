<?php
/*
	*	
*/



class Xal_Extension_Erp_Users
{
	
	public function	run($argus)
	{
		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'test': die('TEST');break;
				
				case 'check.login': return $this->_checkLogin($argu); break;
				
				

			}
			
		}
	}
	
	protected function	_checkLogin($argus)
	{
		
		return true;
		
	}
	
																																																														
	public function helper_ignite_XAL($handler='')	
	{
		if( is_object($handler) )	$this->_XAL	= $handler;
		else	$this->_XAL	= new Xal_Servlet('SAFE_MODE');
	}

}

?>