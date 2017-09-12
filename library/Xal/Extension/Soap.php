<?php
/*
	*	
*/

class Xal_Extension_Soap
{

	
	public function	run($argus)
	{
		if(!is_string($argus['soap.ns']))	$argus['soap.ns'] = 'default';
		if(isset($argus['soap.uri']))	return $this->_new($argus);

		
		if(!is_object($this->__[ $argus['soap.ns'] ])) return false;
		
		foreach($argus as $ark=>$argu)
		{
			if(!preg_match("/^soap\./", $ark))
			{
				$result = $this->__[ $argus['soap.ns'] ]->$ark($argu);
				if(is_string($argus['soap.result'])) return $result->$argus['soap.result'];
				else return $result;
			}
		}
	}
	
	protected function	_new($argus)
	{
		if(!isset($argus['soap.type']) or $argus['soap.type']=='WSDL')
		{
			try
			{
				$this->__[ $argus['soap.ns'] ] = new Zend_Soap_Client($argus['soap.uri']);
				$this->__[ $argus['soap.ns'] ]->setSoapVersion(SOAP_1_1);
			}
			catch(Zend_exception $e)
			{
				return false;
			}
		}
		foreach($argus as $ark=>$argu)
		{
			if(!preg_match("/^soap\./", $ark))
			{
				$result = $this->__[ $argus['soap.ns'] ]->$ark($argu);
				if(is_string($argus['soap.result'])) return $result->$argus['soap.result'];
				else return $result;
			}
		}
		return true;
	}
	


}
?>