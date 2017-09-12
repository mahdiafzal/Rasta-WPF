<?php
/*
	*	
*/



class Xal_Extension_RayaDars_PostSoap
{
	var $username = 'm14rayadars';
	var $password = 'RAYADARS@m14';
	public function	run($argus)
	{
		
		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';

	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'new'			: return $this->_new($argu); break;
				case 'get'			: return $this->_get($argu); break;
				case 'get.code'		: return $this->_getCode(); break;
				case 'get.email'	: return $this->_getEmailAddress($argu); break;
				case 'is.valid'		: return $this->_isValid($argu); break;
				case 'get.discount'	: return $this->_getDiscount($argu); break;
				case 'reg.order'	: return $this->_regOrder($argu); break;
				case 'get.cities'	: return $this->_getCities($argu); break;
				case 'get.rayadars'	: return $this->_downloadRayadarsOnline($argu); break;

			}
			
		}
	}
	protected function connect()
	{
		$this->client = new Zend_Soap_Client("http://svc.ebazaar-post.ir/EshopService.svc?wsdl");
		$this->client->setSoapVersion(SOAP_1_1);
	}
	public function _getStates()
	{
		if(!is_object($this->client)) $this->connect();
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$result = $this->client->GetStates($params);
		return $result->GetStatesResult->State;
		//if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		//if($result)
		//	foreach($result->GetStatesResult->State as $state)
		//		$this->DB->insert('post_states2', array('ps_id'=>$state->Code , 'ps_title'=>$state->Name));
		//print_r();
		
	}
	public function _getCities($state)
	{
		if(!is_object($this->client)) $this->connect();
		$params['username'] = $this->username;
		$params['password'] = $this->password;
		$params['stateId'] = $state;
		
		$result = $this->client->GetCities($params);
		return $result->GetCitiesResult->City;
		//if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		//if($result)
		//	foreach($result->GetCitiesResult->City as $city)
		//		$this->DB->insert('post_cities2', array('ci_id'=>$city->Code , 'ci_title'=>$city->Name, 'ci_state'=>$state));
		
		//print_r($result);
		
	}
	public function _addParcel($argus)
	{
		if(!is_object($this->client)) $this->connect();
		
		$params['username']				= $this->username;
		$params['password']				= $this->password;
		
		foreach($argus['products'] as $product)
		{
			$prductTmp = new ProductTemplate();
			$prductTmp->Id = $product['id'];
			$prductTmp->Count = $product['count'];
			$prductTmp->DisCount = $product['discount'];
			$params['productsId'][] = $prductTmp;
		}
		$params['cityCode']				= $argus['city'];
		$params['serviceType']			= $argus['service'];
		$params['payType']				= $argus['pay'];
		$params['registerFirstName']	= $argus['fname'];
		$params['registerLastName']		= $argus['lname'];
		$params['registerAddress']		= $argus['address'];
		$params['registerPhoneNumber']	= $argus['phone'];
		$params['registerMobile']		= $argus['mobile'];
		$params['registerPostalCode']	= $argus['postalcode'];
		
		$result = $this->client->AddParcel($params);
		print_r($result);
		
		//if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		//return $result->GetCitiesResult->City;
		//if($result)
		//	foreach($result->GetCitiesResult->City as $city)
		//		$this->DB->insert('post_cities2', array('ci_id'=>$city->Code , 'ci_title'=>$city->Name, 'ci_state'=>$state));
		
		//print_r($result);
		
	}
	

	
	
	
	
	
	
	


	
}
class ProductTemplate
{
	public $Count ;
    public $DisCount;
    public $Id ;
    public $ExtensionData = null ;

}


?>