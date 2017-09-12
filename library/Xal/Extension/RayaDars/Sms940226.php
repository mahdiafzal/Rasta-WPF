<?php
/*
	*	
*/



class Xal_Extension_RayaDars_Sms
{
	
	var $username = '9122575616';
	var $password = '9595285';
	var $lineNumber= '982166120121';
	var $servURL = 'http://n.sms.ir/ws/SendReceive.asmx?wsdl';
	
	public function	run($argus)
	{
		if(!is_string($argus['sms.ns']))	$argus['sms.ns'] = 'default';
		
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'send'		: return $this->_send($argu); break;

			}
			
		}
	}
	
	
	
	
	protected function connect()
	{
		date_default_timezone_set('Asia/Tehran');
		$this->client = new Zend_Soap_Client($this->servURL);
		$this->client->setSoapVersion(SOAP_1_1);
	}
	
	protected function	_send($argus)
	{

		if(!is_object($this->client)) $this->connect();
		
		
		
		$parameters['userName'] = $this->username;
		$parameters['password'] = $this->password;
		$parameters['mobileNos'] = array(doubleval($argus['to']));
		$parameters['messages'] = array($argus['text']);
		$parameters['lineNumber'] = $this->lineNumber;
		$parameters['sendDateTime'] = date("Y-m-d")."T".date("H:i:s");


		$result = $this->client->SendMessageWithLineNumber($parameters);
		//$result = $this->client->GetSmsLines($parameters);
		
		print_r($argus);
		print_r($result); die('DDDDDDDDD');
		return $result;
		
		
	}
	
	
	
	
	
	


}

?>