<?php
/*
	*	
*/



class Xal_Extension_RayaDars_Customer
{
	
	public function	run($argus)
	{
		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'new'			: return $this->_new($argu); break;
				case 'get'			: return $this->_get($argu); break;
				case 'get.list'		: return $this->_getList($argu); break;
				case 'get.properties': return $this->_getProperties($argu); break;
				case 'edit.properties': return $this->_editProperties($argu); break;
				case 'get.code'		: return $this->_getCode(); break;
				case 'get.email'	: return $this->_getEmailAddress($argu); break;
				case 'is.valid'		: return $this->_isValid($argu); break;
				case 'get.discount'	: return $this->_getDiscount($argu); break;
				case 'reg.order'	: return $this->_regOrder($argu); break;
				case 'pay.order'	: return $this->_payOrder($argu); break;
				case 'get.order.list'	: return $this->_getOrderList($argu); break;
				case 'quick.order'	: return $this->_quickOrder($argu); break;
				case 'get.cities'	: return $this->_getCities($argu); break;
				case 'get.rayadars'	: return $this->_downloadRayadarsOnline($argu); break;
				case 'transaction.verify' : return $this->_verifyTransaction($argu); break;
				
				
				case 'verify.order'			: return $this->_manualOrderVerification($argu); break;
				
				case 'form.callme'			: return $this->_formCallMe($argu); break;
				
				

			}
			
		}
	}
	
	protected function	_formCallMe($argus)
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		
		$data["rf_name"]			= (isset($argus["name"]))?$argus["name"]:'';
		$data["rf_email"]			= (isset($argus["email"]))?$argus["email"]:'';
		$data["rf_phone"]			= (isset($argus["phone"]))?$argus["phone"]:'';
		$data["rf_type"]			= (isset($argus["ftype"]))?$argus["ftype"]:'';
		$data["rf_extra_data"]		= (isset($argus["extra"]))?$argus["extra"]:'';
		
		if($this->DB->insert('request_forms', $data))	return true;

		return false;
		
	}
	
	protected function	_getOrderList($argus)
	{	
		$start = (!is_numeric($argus['start']))?0:$argus['start'];
		$count = (!is_numeric($argus['count']))?100:$argus['count'];
		$status= (!is_numeric($argus['status']))?' `co_status`=1':' `co_status`='.$argus['status'];
		$type  = (!is_numeric($argus['type']))?'':' AND `co_ispayed`='.$argus['type'];
		$sortType = '  DESC';
		if(is_numeric($argus['sortType']))
			if($argus['sortType']==0) $sortType = ' ASC';
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `customer_order` WHERE ".$status.$type." ORDER BY `co_id`".$sortType." LIMIT ".$start.",".$count;
		if(! $result = $this->DB->fetchAll($sql)) return false;
		$list = array();
		foreach($result as $order)
		{
			$data = array();	 	
			$data["id"]			= $order["co_id"];
			$data["datetime"]	= $order["co_datetime"];
			$data["customer"]	= $order["co_customer"];
			$data["discode"]	= $order["co_discount_code"];
			$data["discount"]	= $order["co_discount"];
			$data["paymode"]	= $order["co_paymode"];
			$data["costsum"]	= $order["co_price"]+$order["co_tax"]+$order["co_costs"];
			$data["ispayed"]	= $order["co_ispayed"];

			$list[] = $data;	
		}
		return $list;
	}		
	
	protected function _manualOrderVerification($argus)
	{
		$data['co_ispayed'] = 1;
		$data['co_refnum'] = 'CASH';
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$this->DB->update('customer_order', $data, "co_id=".$argus);
		return $this->_regSoldProducts($argus);
	}
	
	protected function	_new($argus)
	{

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT `cu_code`, `cu_code_id` FROM `customer_code` WHERE `cu_code_id` NOT IN ( SELECT `cu_code_id` FROM `customer_info` ) LIMIT 0, 1;";
		if(! $result = $this->DB->fetchAll($sql)) return false;
		if(strlen($result[0]['cu_code'])!=5) return false;		
		
		//Validation
		if(!is_string($argus['fname']) or strlen($argus['fname'])<=3)	return false;
		if(!is_string($argus['lname']) or strlen($argus['lname'])<=3)	return false;
		//$patern_email = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9])+[a-zA-Z0-9\_\-]*(\.[a-zA-Z]+)+$/";
		//if(!is_string($argus['email']) or !preg_match($patern_email, $argus['email']) )	return false;
		$patern_cellphone = "/^09[0-9]{9}$/";
		if(!is_string($argus['cellphone']) or !preg_match($patern_cellphone, $argus['cellphone']) )	return false;
		//$patern_phone = "/^0[0-9]+$/";
		//if(!is_string($argus['phone']) or !preg_match($patern_phone, $argus['phone']) )	return false;
		//if(!is_numeric($argus['state_id']) )	return false;		
		//if(!is_numeric($argus['city_id']) )	return false;		
		//if(!is_string($argus['address']) or strlen($argus['address'])<=5 )	return false;
		//if(!is_numeric($argus['postalcode']) or strlen($argus['postalcode'])<10 )	return false;
		//if(!is_numeric($argus['national_code']) )	$argus['national_code'] = "";

		$data["cu_code"]			= $result[0]['cu_code'];
		$data["cu_code_id"]			= $result[0]['cu_code_id'];
		$data["cu_fname"]			= $argus["fname"];
		$data["cu_lname"]			= $argus["lname"];
		//$data["cu_gender"]			= $argus["gender"];
		//$data["cu_national_code"]	= $argus["national_code"];
		$data["cu_email"]			= $argus["email"];
		$data["cu_cellphone"]		= $argus["cellphone"];
		$data["cu_phone"]			= $argus["phone"];
		//$data["cu_state_id"]		= $argus["state_id"];
		//$data["cu_city_id"]			= $argus["city_id"];
		$data["cu_address"]			= $argus["address"];
		$data["cu_postalcode"]		= $argus["postalcode"];
		$this->code = "";
		if($this->DB->insert('customer_info', $data))
		{
			$this->code = $result[0]['cu_code'];
			
			$SMSObj = new Xal_Extension_RayaDars_Sms();
			$sms_data = array();
			$sms_data['text']= "مشتری گرامی\nباسلام\nشناسه شما در رایادرس:\n".$this->code."\nمی باشد\nrayadars.com";
			$sms_data['to'] = $argus["cellphone"];
			$SMSObj->_send($sms_data);
			
			return true;
		}
		return false;
	}
	protected function	_editProperties($argus)
	{
		//return array('status'=>false);
		if(!is_numeric($argus['field'])) return array('status'=>false);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus['record']))	return array('status'=>false);
		$fields = array('cu_fname', 'cu_lname', 'cu_gender', 'cu_national_code', 'cu_email', 'cu_cellphone',
		 'cu_phone', 'cu_state_id', 'cu_city_id', 'cu_address', 'cu_postalcode', 'cu_type');
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$data[ $fields[ (int)$argus['field'] ] ] = $argus['value'];
		//return array("cu_code=".$argus['record']);
		$result = $this->DB->update('customer_info', $data, "cu_code='".$argus['record']."'" );
		if($result) return array('status'=>true);
		return array('status'=>false);
	}
	protected function	_getProperties($argus)
	{	
		if(!is_string($argus))	return false;
		$argus = strtoupper($argus);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus))	return false;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `customer_info` WHERE `cu_code`='".$argus."' ;";
		if(! $result = $this->DB->fetchAll($sql)) return false;
		$result = $result[0];
		$data['code'] = $result["cu_code"];
		$data['total'] = 13;
		$data["rows"] = array();
		//$data["rows"][] = array("name":"نام","value":$result["cu_fname"],"group":"اطلاعات شخصی","editor":"text");
		
		$data["rows"][] = array("name"=>"نام","value"=>$result["cu_fname"],"group"=>"اطلاعات شخصی","editor"=>"text");
		$data["rows"][] = array("name"=>"نام خانوادگی","value"=>$result["cu_lname"],"group"=>"اطلاعات شخصی","editor"=>"text");
		$data["rows"][] = array("name"=>"جنسیت","value"=>$result["cu_gender"],"group"=>"اطلاعات شخصی","editor"=>"text");
		$data["rows"][] = array("name"=>"کدملی","value"=>$result["cu_national_code"],"group"=>"اطلاعات شخصی","editor"=>"text");
		
		$data["rows"][] = array("name"=>"پست الکترونیک","value"=>$result["cu_email"],"group"=>"اطلاعات تماس","editor"=> array(
									"type"=>"validatebox",
									"options"=> array("validType"=>"email")
									));
		$data["rows"][] = array("name"=>"شماره تلفن همراه","value"=>$result["cu_cellphone"],"group"=>"اطلاعات تماس","editor"=>"text");
		$data["rows"][] = array("name"=>"شماره تلفن ثابت","value"=>$result["cu_phone"],"group"=>"اطلاعات تماس","editor"=>"text");
		$data["rows"][] = array("name"=>"استان","value"=>$result["cu_state_id"],"group"=>"اطلاعات پستی","editor"=>"text");
		$data["rows"][] = array("name"=>"شهر","value"=>$result["cu_city_id"],"group"=>"اطلاعات پستی","editor"=>"text");
		$data["rows"][] = array("name"=>"آدرس","value"=>$result["cu_address"],"group"=>"اطلاعات پستی","editor"=>"text");
		$data["rows"][] = array("name"=>"کدپستی","value"=>$result["cu_postalcode"],"group"=>"اطلاعات پستی","editor"=>"text");
		$data["rows"][] = array("name"=>"نوع","value"=>$result["cu_type"],"group"=>"مدیریت","editor"=>"text");
		$data["rows"][] = array("name"=>"شناسه","value"=>$result["cu_code"],"group"=>"مدیریت");

		

		
		return $data;
	}
	protected function	_getList($argus)
	{	
		$start = (!is_numeric($argus['start']))?0:$argus['start'];
		$count = (!is_numeric($argus['count']))?100:$argus['count'];
		$type  = (!is_numeric($argus['type']))?' `cu_type`=1':' `cu_type`='.$argus['type'];
		$status= (!is_numeric($argus['status']))?'':' AND `cu_status`='.$argus['status'];
		$sortType = '  DESC';
		if(is_numeric($argus['sortType']))
			if($argus['sortType']==0) $sortType = ' ASC';
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `customer_info` WHERE ".$type.$status." ORDER BY `cu_code_id`".$sortType." LIMIT ".$start.",".$count;
		if(! $result = $this->DB->fetchAll($sql)) return false;
		$list = array();
		foreach($result as $customer)
		{
			$data = array();
			$data["code"]			= $customer["cu_code"];
			$data["codeId"]			= $customer["cu_code_id"];
			$data["datetime"]		= $customer["cu_datetime"];
			$data["fname"]			= $customer["cu_fname"];
			$data["lname"]			= $customer["cu_lname"];
			$data["gender"]			= $customer["cu_gender"];
			$data["natioCode"]		= $customer["cu_national_code"];
			$data["email"]			= $customer["cu_email"];
			$data["cellphone"]		= $customer["cu_cellphone"];
			$data["phone"]			= $customer["cu_phone"];
			$data["state"]			= $customer["cu_state_id"];
			$data["city"]			= $customer["cu_city_id"];
			$data["address"]		= $customer["cu_address"];
			$data["postCode"]		= $customer["cu_postalcode"];
			$data["status"]			= $customer["cu_status"];
			$data["type"]			= $customer["cu_type"];
			$list[] = $data;	
		}
		return $list;
	}	
	protected function	_get($argus)
	{
		if(!is_string($argus))	return false;
		$argus = strtoupper($argus);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus))	return false;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `customer_info` WHERE `cu_code`='".$argus."' ;";
		if(! $result = $this->DB->fetchAll($sql)) return false;
		$result = $result[0];
		$data["fname"]			= $result["cu_fname"];
		$data["lname"]			= $result["cu_lname"];
		$data["gender"]			= $result["cu_gender"];
		$data["national_code"]	= $result["cu_national_code"];
		$data["email"]			= $result["cu_email"];
		$data["cellphone"]		= $result["cu_cellphone"];
		$data["phone"]			= $result["cu_phone"];
		$data["state_id"]		= $result["cu_state_id"];
		$data["city_id"]		= $result["cu_city_id"];
		$data["address"]		= $result["cu_address"];
		$data["postalcode"]		= $result["cu_postalcode"];
		
		return $data;
	}
	protected function	_getCode()
	{
		if(strlen($this->code)==5) return $this->code;
		return false;
	}
	protected function	_getEmailAddress($argus)
	{
		if(!is_string($argus))	return false;
		$argus = strtoupper($argus);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus))	return false;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT `cu_email` FROM `customer_info` WHERE `cu_code`='".$argus."' ;";
		if(! $emailAddr = $this->DB->fetchOne($sql)) return false;
		return $emailAddr;
	}
	protected function	_isValid($argus)
	{
		if(!is_string($argus))	return false;
		$argus = strtoupper($argus);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus))	return false;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT COUNT(*) FROM `customer_info` WHERE `cu_code`='".$argus."' ;";
		if(! $count = $this->DB->fetchOne($sql)) return false;
		if($count==1)
		{
			$this->code = $argus;
			return true;
		}
		return false;
	}
	protected function	_quickOrder($argus)
	{
		if(!is_array($argus['order']))	return false;
		$data['name'] = $argus['fname'];
		$data['email'] = $argus['email'];
		$data['cellphone'] = $argus['cellphone'];
		
		$order = '<var:order><tree>';
		foreach($argus['order'] as $product)
			if($product['value']==1)
				$order .= "\n\t<item>\n\t\t<tree>\n\t\t\t<item:name>".$product['name']."</item:name>"
						. "\n\t\t\t<item:value>".$product['value']."</item:value>"
						. "\n\t\t</tree>\n\t</item>";
		$order .= "\n</tree></var:order>";
		$data['order'] = $order;
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if($this->DB->insert('quick_order', $data))	return true;
		return false;

	}
	protected function	_payOrder($argus)
	{
		if(!is_numeric($argus))	return false;
		//		die( 'FFFFFFFFF' );

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `customer_order` WHERE co_id =".$argus." AND co_ispayed!=1;";
		
		if(! $result = $this->DB->fetchAll($sql)) return false;

		return $this->_preTransaction($result[0]);
	}
	protected function	_regOrder($argus)
	{
		$_return = array('status'=>false, 'message'=>'خطا در ثبت سفارش؛ با رایادرس تماس بگیرید.');
		if(!is_array($argus['order']))	return $_return;
		if(!$this->_isValid($argus['customer']))	return $_return;
		
		$data['co_customer']		= strtoupper($argus['customer']);
		$data['co_discount_code']	= strtoupper($argus['discount']);
		
		$taxRate = 0.08;
		
		$data['co_discount']		= $this->_getDiscount($argus['discount']);
		if($data['co_discount']<0)	$data['co_discount'] = 0;

		/*$order = '<var:order><tree>';
		foreach($argus['order'] as $product)
			$order .= '<item><tree><item:id>'.$product['id'].'</item:id>'
					. '<item:title>'.$product['title'].'</item:title>'
					. '<item:amount>'.$product['amount'].'</item:amount>'
					. '<item:price>'.$product['price'].'</item:price></tree></item>';
		$order .= '</tree></var:order>'; 
		$data['co_order']			= $order;*/
		$orderIDs = array();
		$orderAmount = array();
		foreach($argus['order'] as $key=>$product)
		{
			if(!is_numeric($product['id'])) continue;
			$orderIDs[] = $product['id'];
			$orderAmount[$product['id']] = $product['amount'];
		}
		if(count($orderIDs)<1) return $_return;
		
		if(! $orderData = $this->_getProductData($orderIDs) ) return $_return;
		
		$sum = 0;
		$order = '<var:order><tree>';
		foreach($orderData as $value)
		{
			$order .= '<item><tree><item:id>'.$value['p_id'].'</item:id>'
					. '<item:title>'.$value['p_title'].'</item:title>'
					. '<item:amount>'.$orderAmount[$value['p_id']].'</item:amount>'
					. '<item:price>'.$value['p_price'].'</item:price></tree></item>';
			$sum += $orderAmount[$value['p_id']]*$value['p_price'];
		}
		$order .= '</tree></var:order>'; 
		$data['co_order']			= $order;
		$data['co_price'] = $sum;
		$data['co_tax'] = $sum*$taxRate;
		$data['co_costs'] = 0;

		
			
		if(is_numeric($argus['paymode']))	$data['co_paymode']	= $argus['paymode'];

		if($data['co_paymode']==5)
		{
			if(!is_array($argus['pishkhan'])) 
				return array('status'=>false, 'message'=>'خطا در ثبت سفارش؛ دفتر پیشخوان دوباره وارد سیستم شوید.');
			$data['co_pishkhan_id'] = $argus['pishkhan']['userkey'];
		}

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if($this->DB->insert('customer_order', $data))
		{
			$data['co_id'] = $this->DB->lastInsertId();

			if($data['co_discount']>0)	$this->regDiscodeUse($argus['discount']);
			if(!is_numeric($data['co_id'])) return $_return;
			if($data['co_paymode']==4)	return $this->_preTransaction($data);
			if($data['co_paymode']==5)	return $this->_prePishkhanTransaction($data, $argus['pishkhan']);
			return array('status'=>true, 'attach'=>array('orderID'=>$data['co_id'], 'orderDate'=> $this->helper_get_now_persian_date() ));
		}
		return $_return;
	}
	protected function _getProductData($orderIDs)
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `products` WHERE p_id IN (".implode(',',$orderIDs).") AND p_price>0;";
		$result = $this->DB->fetchAll($sql);
		return $result;
	}
	
	
	protected function _prePishkhanTransaction($order, $pishkhan)
	{
		$data['t_customer'] = $order['co_customer'];
				
		$discount_prim = 1-($order['co_discount']/100);
		if($discount_prim>1 | $discount_prim<0.1) $discount_prim = 1;
		$data['t_amount'] = (($order['co_price'] + $order['co_tax'])*$discount_prim )+ $order['co_costs'] ;
		
		$data['t_order'] = $order['co_id'];
		$data['t_portal'] = 2;  // Pishkhan Payment Portal
		
		//print_r($data);
		//die();
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if(!$this->DB->insert('transactions', $data)) return array('status'=>false, 'message'=>'خطا در ثبت سفارش؛ اطلاعات تراکنش مالی ثبت نشد.');
		$t_id = $this->DB->lastInsertId();
		
		ini_set("soap.wsdl_cache_enabled", 0);
		//$client = new Zend_Soap_Client('http://dev.epishkhan.ir/nwsv2/?wsdl');
		$client = new Zend_Soap_Client('http://nwsv2.epishkhan.ir/?wsdl');
		
		$client->setSoapVersion(SOAP_1_1);
		
		//$data['t_amount'] = 2000;
		
		$parameters['pass'] = 'ray*pyx~shnebs+';
		$parameters['domain'] = 'rayadars';
		$parameters['ver_code'] = $pishkhan['ver_code'];
		$parameters['user_key'] = $pishkhan['userkey'];
		$parameters['service_id'] = 2101;
		//$parameters['office_amount'] = $data['t_amount']*0.1;
		$parameters['offic_amount'] = $data['t_amount']*0.89;
		
		$parameters['customer_amount'] = $data['t_amount'];


		$result = $client->servicePay($parameters);
		if(!is_object($result))
			return array('status'=>false, 'message'=>'خطا در ارتباط با وبسرویس پیشخوان');
		//return array('status'=>false, 'message'=> 'خطا در ');	
		$data = array();
		$data['t_cid'] = $result->serial;
		if(!$this->DB->update('transactions', $data, 't_id='. $t_id ))
				return array('status'=>false, 'message'=> 'خطا در ذخیره سریال تراکنش');
		
		if($result->serial > 0)
		{
			$parameters = array();
			$parameters['pass'] = 'ray*pyx~shnebs+';
			$parameters['domain'] = 'rayadars';
			$parameters['ver_code'] = $pishkhan['ver_code'];
			$parameters['user_key'] = $pishkhan['userkey'];
			$parameters['service_id'] = 2101;
			$parameters['serial'] = $result->serial;
			$parameters['refnum'] = $order['co_id'];
			$result2 = $client->verify($parameters);
			if(!is_object($result2))
				return array('status'=>false, 'message'=>'خطا در ارتباط با وبسرویس پیشخوان');
			$data = array();
			if($result2->pkhref < 0 )
				return array('status'=>false, 'message'=> 'Ver:'.$result2->pkhref); //'خطا در تأیید پرداخت');
			$data['t_refnum'] = $result2->pkhref;
			
			if(!$this->DB->update('transactions', $data, 't_id='. $t_id ))
				return array('status'=>false, 'message'=> 'خطا در ثبت کد پیگیری');
			
			return array('status'=>true, 'message'=>'پس از پایان تراکنش مالی، تأیید کنید تا فعالسازی انجام شود.', 'tcode'=>$t_id);
			
		}
			
		return array('status'=>false, 'message'=> $result->serial); //'خطا در ارتباط با وبسرویس پیشخوان');
		
	}
	protected function _preTransaction($order)
	{
		
		$data['t_customer'] = $order['co_customer'];
		
		
		
		$discount_prim = 1-($order['co_discount']/100);
		if($discount_prim>1 | $discount_prim<0.1) $discount_prim = 1;
		$data['t_amount'] = (($order['co_price'] + $order['co_tax'])*$discount_prim )+ $order['co_costs'] ;
		
		$data['t_order'] = $order['co_id'];
		
		//print_r($data);
		//die();
		
		//$data['t_amount'] = 1000;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if(!$this->DB->insert('transactions', $data)) return array('status'=>false, 'message'=>'خطا در ارتباط با وبسرویس پیشخوان');;
		//$data['t_id'] = $this->DB->lastInsertId();
		//if(!is_numeric($data['t_id'])) return false;
		
		$_return = '<form method="post" action="https://sep.shaparak.ir/Payment.aspx" name="akld45d64f5hah">'
				.  '<input type="hidden" name="Amount" value="'.$data['t_amount'].'" />'
				.  '<input type="hidden" name="MID" value="10206688" />'
				.  '<input type="hidden" name="ResNum" value="'.$data['t_order'].'" />'
				.  '<input type="hidden" name="RedirectURL" value="http://rayadars.com/dandelion?form_id=98f13708210194c475687be6106a3b84&ref=samanpayment" />'
				.  '</form>';
		//$_return = false;
		return array('status'=>true, 'attach'=>$_return);
		//return $this->_redirectToSmanBankPayment($data);
		
	}
	protected function _verifyTransaction($argus)
	{
		$validPayment = false;
		$finalize=false;
		$_return['message'] = '';
		if($_POST['State']=='OK' and !empty($_POST['RefNum']))
		{
			if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
			$sql = "SELECT * FROM `transactions` WHERE t_refnum='".$_POST['RefNum']."'";
			if($result = $this->DB->fetchAll($sql))
			{
				$_return['message'] = 'رسید دیجیتالی پیش تر استفاده شده است.';
			}
			else
			{
				$client = new Zend_Soap_Client("https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL");
				$client->setSoapVersion(SOAP_1_1);
				$result1 = $client->verifyTransaction($_POST['RefNum'], "10206688" );
				if($result1<=0)
				{
					$_return['message'] = 'خطا در تکمیل فرایند پرداخت.';
				}
				else
				{
					$validPayment = true;
					
					$sql = "SELECT * FROM `transactions` WHERE t_order=".$_POST['ResNum']." ORDER BY `t_id` DESC LIMIT 0 , 1";
					if($result2 = $this->DB->fetchAll($sql))
					{
						if($result1==$result2[0]['t_amount'])
						{
							$data['t_refnum'] = $_POST['RefNum'];
							$data['t_status'] = 2;
							$data['t_verify_code'] = $result1;
							$data['t_cid'] = $_POST['CID'];
							if($this->DB->update('transactions', $data, "t_id=".$result2[0]['t_id']))
							{
								$finalize=true;
								$_return['message'] = 'پرداخت با موفقیت انجام گرفت.';	
								$data2['co_ispayed'] = 1;
								$data2['co_refnum'] = $_POST['RefNum'];
								$this->DB->update('customer_order', $data2, "co_id=".$result2[0]['t_order']);
								$this->_regSoldProducts($result2[0]['t_order']);
							}
						}
						else
						{
							$data['t_refnum'] = '';
							$data['t_status'] = 3;
							$data['t_verify_code'] = $result1;
							$data['t_cid'] = $_POST['CID'];
							$this->DB->update('transactions', $data, "t_id=".$result2[0]['t_id']);
						}
						
					}
					if(!$finalize)
					{
						$_return['message'] = 'خطا در تکمیل فرایند پرداخت. مبلغ پرداخت شده برگشت داده شد.';
						$validPayment = false;
						$result3 = $client->reverseTransaction($_POST['RefNum'], "10206688", "10206688", "4623516" );
						if($result3!=1)
							$result3 = $client->reverseTransaction($_POST['RefNum'], "10206688", "10206688", "4623516" );
						
					}
					
				}
			}
			
			
			
			
			/*double verifyTransaction (
			String RefNum,
			String MerchantID)*/
		}
		else
		{
			$_return['message'] = $_POST['State'];
		}
		print '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		print '<style>body{display:none;}</style>';
		$_return['status'] = $finalize;
		//$_return['CID'] = $_POST['CID'];
		$_return['RefNum'] = $_POST['RefNum'];
		$_return['ResNum'] = $_POST['ResNum'];

		$url	= "/rtc/تکمیل-فرایند-پرداخت";
		$method	= 'POST';
		Zend_OpenId::redirect($url, $_return, null, $method);
		
		//die('<h3 style="text-align:center;direction:rtl;">'.$_return['message'].'</h3>');
		/*$_POST['State'] => 
			Canceled By User
			Invalid Amount
			Invalid Transaction
			Expired Card Pick Up
			Allowable PIN Tries Exceeded Pick Up
			Incorrect PIN
			Transaction Cannot Be Completed
			Timeout 
			Response Received Too Late
			Suspected Fraud Pick Up
			No Sufficient Funds
			Issuer Down Slm
			TME Error		
	    $_POST['ResNum'] => 69
	    $_POST['MID'] => 10206688
	    $_POST['RefNum'] => 
	    $_POST['CID'] =>
		
		
		double verifyTransaction (
			String RefNum,
			String MerchantID)
			
		double reverseTransaction(
			String RefNum,
			String MID,
			String Username,
			String Password)
		*/
		//die('<h3 style="text-align:center;direction:rtl;">خطا در تکمیل فرایند خرید. مبلغ کسر شده حداکثر ظرف 48 ساعت به حساب شما باز خواهد گشت.</h3>');
	}
	
	
	/*protected function _redirectToSmanBankPayment($data)
	{
		$url	= "https://sep.shaparak.ir/Payment.aspx";
		$method	= 'POST';
		$params	= array();
		$params['Amount'] = $data['t_amount'];
		$params['MID'] = '10206688';
		$params['ResNum'] = $data['t_order'];
		$params['RedirectURL'] = 'http://rayadars.com/dandelion?form_id=98f13708210194c475687be6106a3b84&ref=samanpayment';
		Zend_OpenId::redirect($url, $params, null, $method);
		return true;
	}*/
	protected function _regSoldProducts($order)
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `customer_order` WHERE co_id=".$order;
		
		if(! $result = $this->DB->fetchAll($sql)) return false;
		if( !is_object($this->_XAL) )	$this->helper_ignite_XAL();
		
		$ext_data	= '<execution>'.$result[0]['co_order'].'</execution>';
		$details	= $this->_XAL->run($ext_data);
	
		if(!is_array($details))	return false;
		if(!is_array($details['var:order']))	return false;
		foreach($details['var:order'] as $product)
		{
			$product_list = array();
			$product_list[] = $product['id'];
			
			if($product['id']>90000)
			{
				$sql = "SELECT p_description FROM `products` WHERE p_id=".$product['id'];
				if($p_description = $this->DB->fetchOne($sql))
				{
					$product_list = explode(',',$p_description);
				}
			}
			
			foreach($product_list as $proid)
			{
				 $sql = "SELECT sp_id FROM `sold_products` WHERE sp_product_id=".$proid." AND sp_customer_id='".$result[0]['co_customer']."'";
				 
				 if(! $sp_id = $this->DB->fetchOne($sql))
				 {
				 		
					 	$data = array();
						$data['sp_product_id'] = $proid;
						$data['sp_customer_id'] = $result[0]['co_customer'];
						$data['sp_order'] = $order;
						$this->DB->insert('sold_products', $data);
				 }
				 elseif(is_numeric($sp_id))
				 {
				 	
				 	$this->DB->update('sold_products', array('sp_status'=>1, 'sp_order'=>$order), "sp_id=".$sp_id );
				 }
			}
		}
		
		//<var:order><tree><item><tree><item:id>13211</item:id><item:title>شیمی 2</item:title><item:amount>1</item:amount><item:price>577000</item:price></tree></item></tree></var:order>
		
	}
	protected function	regDiscodeUse($discode)
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT COUNT(*) FROM (SELECT `co_id` FROM `customer_order` WHERE `co_discount_code`='".addslashes($discode)."' GROUP BY `co_customer` ) AS `sub`";
		if(! $count = $this->DB->fetchOne($sql)) return false;
		$sql = "SELECT `dc_code_id` FROM `discount_code` WHERE `dc_code`='".addslashes($discode)."';";
		if(! $dc_id = $this->DB->fetchOne($sql)) return false;
		$this->DB->update('discount_info', array('used_count'=>$count), "dc_id=".$dc_id );
	}
	protected function helper_get_now_persian_date()
	{
		$pdate	= new Rasta_Pdate;	
		return $pdate->gregorian_to_persian(date("Y"), date("m"), date("d"));
	}
	protected function	_getDiscount($argus)
	{	
		if(!is_string($argus))	return -1; // System Erorr
		$argus = strtoupper($argus);
		if(!preg_match("/^[1-9A-Z]{3}$/", $argus))	return -1;

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `discount_code` AS cod Inner Join `discount_info` AS inf ON cod.dc_code_id = inf.dc_id "
			 . " WHERE cod.dc_code = '".$argus."' AND inf.status=1";
		if(! $result = $this->DB->fetchAll($sql)) return -2; // Not Exist
		
		if($result[0]['valid_count']!=0)
			if($result[0]['valid_count']<=$result[0]['used_count'])	return -3; // Count Limit
		
		date_default_timezone_set('Asia/Tehran');
		$pdate	= new Rasta_Pdate;	
		$now 	= new Zend_Date( implode('-', $pdate->gregorian_to_persian(date("Y"), date("m"), date("d")) )." ".date("H:i:s"), Zend_Date::ISO_8601 );
		$start	= new Zend_Date($result[0]['start_time'], Zend_Date::ISO_8601 );
		$end	= new Zend_Date($result[0]['end_time'], Zend_Date::ISO_8601 );
		if($now->isEarlier($start) or $now->isLater($end))	return -4; // Out of Date
		
		$start->addHour($result[0]['step1_hour']);
		if($now->isEarlier($start))	return $result[0]['step1_discount'];
		$start->addHour($result[0]['step2_hour']);
		if($now->isEarlier($start))	return $result[0]['step2_discount'];
		$start->addHour($result[0]['step3_hour']);
		if($now->isEarlier($start))	return $result[0]['step3_discount'];
		
		return $result[0]['customer_share']; // customer_share
	}
	protected function	_getCities($argus)
	{	
		if(!is_numeric($argus))	return -1; // System Erorr

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `post_cities` WHERE ci_state = '".$argus."' ;";
		if(! $result = $this->DB->fetchAll($sql)) return -2; // Not Exist
		foreach($result as $city)
			$cities[] = array('Code'=>$city['ci_id'] , 'Name'=>$city['ci_title']);
			
		return array('City'=>$cities);

	}
	protected function _downloadRayadarsOnline($argus)
	{
		if(preg_match("/^http\:\/\/46\.224\.3\.98\:1010/", $argus))
		{
			$this->force_download($argus);
		}
		else
		{
			$base = '/var/www/clients/client2/web3/web/flsimgs/rayadars/1/files/rayadars.online';
			$this->force_download($base.$argus);	
		}		
	}
	protected function force_download($file)
	{
		$ext = explode(".", $file);
		//echo( file_exists($file)."nnnnnn".__FILE__ );
		//die();
		switch($ext[sizeof($ext)-1])
		{
			case 'jar': $mime = "application/java-archive"; break;
			case 'zip': $mime = "application/zip"; break;
			case 'jpeg': $mime = "image/jpeg"; break;
			case 'jpg': $mime = "image/jpg"; break;
			case 'jad': $mime = "text/vnd.sun.j2me.app-descriptor"; break;
			case "gif": $mime = "image/gif"; break;
			case "png": $mime = "image/png"; break;
			case "pdf": $mime = "application/pdf"; break;
			case "txt": $mime = "text/plain"; break;
			case "doc": $mime = "application/msword"; break;
			case "ppt": $mime = "application/vnd.ms-powerpoint"; break;
			case "wbmp": $mime = "image/vnd.wap.wbmp"; break;
			case "wmlc": $mime = "application/vnd.wap.wmlc"; break;
			case "mp4s": $mime = "application/mp4"; break;
			case "ogg": $mime = "application/ogg"; break;
			case "pls": $mime = "application/pls+xml"; break;
			case "asf": $mime = "application/vnd.ms-asf"; break;
			case "swf": $mime = "application/x-shockwave-flash"; break;
			case "mp4": $mime = "video/mp4"; break;
			case "m4a": $mime = "audio/mp4"; break;
			case "m4p": $mime = "audio/mp4"; break;
			case "mp4a": $mime = "audio/mp4"; break;
			case "mp3": $mime = "audio/mpeg"; break;
			case "m3a": $mime = "audio/mpeg"; break;
			case "m2a": $mime = "audio/mpeg"; break;
			case "mp2a": $mime = "audio/mpeg"; break;
			case "mp2": $mime = "audio/mpeg"; break;
			case "mpga": $mime = "audio/mpeg"; break;
			case "wav": $mime = "audio/wav"; break;
			case "m3u": $mime = "audio/x-mpegurl"; break;
			case "bmp": $mime = "image/bmp"; break;
			case "ico": $mime = "image/x-icon"; break;
			case "3gp": $mime = "video/3gpp"; break;
			case "3g2": $mime = "video/3gpp2"; break;
			case "mp4v": $mime = "video/mp4"; break;
			case "mpg4": $mime = "video/mp4"; break;
			case "m2v": $mime = "video/mpeg"; break;
			case "m1v": $mime = "video/mpeg"; break;
			case "mpe": $mime = "video/mpeg"; break;
			case "mpeg": $mime = "video/mpeg"; break;
			case "mpg": $mime = "video/mpeg"; break;
			case "mov": $mime = "video/quicktime"; break;
			case "qt": $mime = "video/quicktime"; break;
			case "avi": $mime = "video/x-msvideo"; break;
			case "midi": $mime = "audio/midi"; break;
			case "mid": $mime = "audio/mid"; break;
			case "amr": $mime = "audio/amr"; break;
			default: $mime = "application/force-download";
		}
		header('Content-Description: File Transfer');
		header('Content-Type: '.$mime);
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();
		
		readfile($file);
	}																																																																
	public function helper_ignite_XAL($handler='')	
	{
		if( is_object($handler) )	$this->_XAL	= $handler;
		else	$this->_XAL	= new Xal_Servlet('SAFE_MODE');
	}

}

?>