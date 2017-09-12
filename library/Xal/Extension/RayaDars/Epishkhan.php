<?php
/*
	*	
*/



class Xal_Extension_RayaDars_Epishkhan
{
	
	public function	run($argus)
	{
		
	
		if(!is_string($argus['ep.ns']))	$argus['ep.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'login'	: return $this->_login($argu); break;
				case 'test'		: return $this->_webServiceTest($argu); break;

			}
			
		}
	}
	
	
	
	
	
	
	protected function	_login($argus)
	{
		
		
		require 'openid.php';
		try {
		    $openid = new LightOpenID;
		    if(!$openid->mode) {
			// test url : index.php?office=71111001
		        if(isset($_GET['office'])) {
					$_SESSION['rayadars'] = array('temp'=> array('ver_code'=> $_GET['ver_code'] ));
				    //identity 
		            $openid->identity = "http://auth.epishkhan.ir/identity/".(int)$_GET['office'];
					$openid->required = array('userkey');
		            header('Location: ' . $openid->authUrl());
		        }
		
		    } elseif($openid->mode == 'cancel') {
		        echo 'User has canceled authentication!';
		    } else {
		        // if validate by openId get 
				if($openid->validate())
				{
					$res=$openid->getAttributes();
					return array('office'=> array('userkey'=> $res['userkey'], 'ver_code'=> $_SESSION['rayadars']['temp']['ver_code'] )  );
					
					//echo 'User ' .$_GET['office'].' has logged in.<br>';
					//echo 'Here is the provided info: ';
					//$res=$openid->getAttributes();
					//echo "userkey=".$res['userkey'];
					//$_SESSION['rayadars'] = array('operator'=> array('type'=>'epishkhan', 'userkey'=> $res['userkey'])  );
					//header('Location: /rtc/خرید_رایادرس_-_قدم_اول');
					//print_r($_SESSION);
				}else{
					echo 'User hos not loggedin.';
				}
		    }
		} catch(ErrorException $e) {
		    echo $e->getMessage();
		}
	}
	
	protected function	_webServiceTest($argus)
	{

		/*$client = new Zend_Soap_Client("https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL");
		$client->setSoapVersion(SOAP_1_1);
		$result1 = $client->verifyTransaction('1PMNINLui5Dem+Jr0KeimEJujEZwWp', "10206688" );
		print_r($result1);
		die();*/
		
		try{
			
		//$client = new Zend_Soap_Client('http://dev.epishkhan.ir/nwsv2/?wsdl');
		ini_set("soap.wsdl_cache_enabled", 0);
		$client = new Zend_Soap_Client('http://nwsv2.epishkhan.ir/?wsdl', array('cache_wsdl' => WSDL_CACHE_NONE) );
		
		$client->setSoapVersion(SOAP_1_1);
		
		echo file_get_contents('http://nwsv2.epishkhan.ir/?wsdl');
		//http://nwsv2.epishkhan.ir/?wsdl
		
		
		$parameters['pass'] = 'ray*pyx~shnebs+';
		$parameters['domain'] = 'rayadars';
		$parameters['ver_code'] = 'c4fdf907227b89610bda5968e89706c93011414';
		$parameters['user_key'] = (int) 2100;
		$parameters['service_id'] = (int) 2101;
		//$parameters['office_amount'] = 100000*0.1;
		$parameters['offic_amount'] = (int) 11000;
		$parameters['customer_amount'] = (int) 100000;


		$result = $client->servicePay($parameters);
		//$result = $client->servicePay('ray*pyx~shnebs+', 'rayadars', $_GET['ver_code'], 2100, 2101, 11000 , 100000 );
		
		print_r($result);
		
		} catch(ErrorException $e) {
		    echo $e->getMessage();
		}
	}	
	
	
	
	
	
	


}

?>