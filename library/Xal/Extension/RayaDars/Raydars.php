<?php
/*
	*	
*/



class Xal_Extension_RayaDars_Raydars
{
	

	var $servURL = 'http://46.224.3.98';
	
	public function	run($argus)
	{

		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'client' : return $this->_client($argu); break;

			}
			
		}
	}
	
	
	
	

	
	protected function _client($argus)
	{
		$params = array();
		$params[]= "form_id=45c48cce2e2d7fbdea1afc51c7c6ad26";
		$params[]= "path=".$_POST['path'];
		$params[]= "relation=".$_POST['relation'];
		
		$url= 'http://46.224.3.98/dandelion?'.implode("&",$params);
		$json = file_get_contents($url);
		if(empty($json)) $json = "[]";
		//$json = "DDDDDDDDDDDDDDDDD";
		die($json);
		
		
		
		/*$client = new Zend_Http_Client('http://46.224.3.98/dandelion');
		
		// Yet another way of preforming a POST request
		$client->setMethod(Zend_Http_Client::POST);
		
		$_REQUEST['form_id']= "1679091c5a880faf6fb5e6087eb1b2dc";

		// Setting several POST parameters, one of them with several values
		$client->setParameterPost($_REQUEST);
		
		$response = $client->request();

		//print_r($response);
		
		return $response->body;*/
	}
	
	
	
	
	
	


}

?>