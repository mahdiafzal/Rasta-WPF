<?php
/*
	*	
*/



class Xal_Extension_RayaDars_Activation

{

	

	protected $customer;

	public function	run($argus)
	{
		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';

	
		
		//if(is_string($argus['customer'])) $this->customer = $argus['customer'];
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'reg.request'	: return $this->_newRequest($argu); break;
				case 'auto.request'	: return $this->_newAutoRequest($argu); break;

				

			}
			
		}
	}
	protected function	_isValidCustome($code)
	{
		if(!is_string($code))	return false;
		$argus = strtoupper($code);
		if(!preg_match("/^[1-9A-Z]{5}$/", $code))	return false;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT COUNT(*) FROM `customer_info` WHERE `cu_code`='".$code."' ;";
		if(! $count = $this->DB->fetchOne($sql)) return false;
		if($count==1)	return true;
		return false;
	}
	public function _newAutoRequest($argus)
	{
		if($_POST['passkey']!='anisaisagoodgirl')	return "X";
		if(!is_string($argus['customer']) or !is_string($argus['system']))	return "Z1";
		$data['cu_id'] = addslashes( strtoupper($argus['customer']) );
		$data['sys_id'] = addslashes( strtoupper($argus['system']) );
		$this->customer = $data['cu_id'];
		$data['how_send'] = 0;
		
		if(!preg_match("/^[1-9A-Z]{5}$/", $data['cu_id']))	return "Z2";
		if(!preg_match("/^[1-9A-Z]{25}$/", $data['sys_id']))	return "Z3";
		
		//if(is_numeric($argus['howsend']))	$data['how_send'] = ($data['how_send']==2)?2:1;
		$argus['howsend'] = $data['how_send'];
		$argus['return.code'] =  true;
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if(!$this->DB->insert('activation_request', $data))	return "Z4";
		$result = $this->_sendActivationCode($argus);
		if(is_numeric($result))
			switch($result)
			{
				case 0:	case -1: case -2: case -3: case -4: return "Z5"; break;
				case -7: return "A"; break;
				case -5: return "B"; break;
				case -8: return "C"; break;
			}
		
		return $result;
	}
	public function _newRequest($argus)
	{
		if(!is_string($argus['customer']) or !is_string($argus['system']))	return 0;
		$data['cu_id'] = strtoupper($argus['customer']);
		$data['sys_id'] = strtoupper($argus['system']);
		$this->customer = $data['cu_id'];
		$data['how_send'] = $argus['howsend'];
		
		if(!preg_match("/^[1-9A-Z]{5}$/", $data['cu_id']))	return 0;
		if(!preg_match("/^[1-9A-Z]{25}$/", $data['sys_id']))	return 0;
		if(is_numeric($argus['howsend']))	$data['how_send'] = ($data['how_send']==2)?2:1;
		$argus['howsend'] = $data['how_send'];
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if(!$this->DB->insert('activation_request', $data))	return 0;
		return $this->_sendActivationCode($argus);		
	}
	protected function _sendActivationCode($argus)
	{
		if(!is_string($argus['customer']) or !is_string($argus['system']))	return 0;
		$data['cu_id'] = strtoupper($argus['customer']);
		$data['sys_id'] = strtoupper($argus['system']);
		if(!preg_match("/^[1-9A-Z]{5}$/", $data['cu_id']))	return -1;
		if(!preg_match("/^[1-9A-Z]{25}$/", $data['sys_id']))	return -2;
		
		if(!$this->_isValidCustome($data['cu_id']))	return -7;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT * FROM `customer_devices` WHERE `cu_id`='".$data['cu_id']."';";
		$devices = $this->DB->fetchAll($sql);
		
		if(!is_array($devices))
		{
			if(!$this->DB->insert('customer_devices', $data)) return -3;
			$device = $data['sys_id'];
		}
		else
		{
			$is_reged = false;
			foreach($devices as $device)
				if($device['sys_id']==$data['sys_id'])
				{
					$is_reged = true;
					$device = $device['sys_id'];
					break;
				}
			if(!$is_reged)
			{
				$validCount = 1;
				$sql = "SELECT vc_count FROM `device_valid_count` WHERE `vc_customer`='".$data['cu_id']."';";
				if($dvc = $this->DB->fetchOne($sql)) $validCount = $dvc;
				if(count($devices)<$validCount)
				{
					if(!$this->DB->insert('customer_devices', $data)) return -4;
					$device = $data['sys_id'];
				}
			}
		}
		if(!is_string($device))	return -5;
		
		if(!isset($argus['version']))	$code = $this->ComputeActivationCodeV1($argus);
		elseif($argus['version']==1)	$code = $this->ComputeActivationCodeV1($argus);
		elseif($argus['version']==2)	$code = $this->ComputeActivationCodeV2($argus);
		

		
		
		if(isset($argus['return.code'])) return $code;
		
		$code = substr($code, 0, 5).'-'.substr($code, 5, 5).'-'.substr($code, 10, 5).'-'.substr($code, 15, 5).'-'.substr($code, 20, 5);
		
		if($argus['howsend'] == 2)
		{
			$message = "کدفعالسازی رایادرس شما:\n".$code;
			
			date_default_timezone_set('Asia/Tehran');
			$client = new Zend_Soap_Client('http://n.sms.ir/ws/SendReceive.asmx?wsdl');
			$client->setSoapVersion(SOAP_1_1);
			
			
			$username = '9122575616';
			$password = '959528';
			$lineNumber= '982166121086';
			$phoneNum=$this->_getCustomerCellPhoneNumber($data['cu_id']);
			
			if( $phoneNum < 0 )	return  $phoneNum;
			
			
			$parameters['userName'] = $username;
			$parameters['password'] = $password;
			$parameters['mobileNos'] = array(doubleval(  $phoneNum  ));
			$parameters['messages'] = array($message);
			$parameters['lineNumber'] = $lineNumber;
			$parameters['sendDateTime'] = date("Y-m-d")."T".date("H:i:s");


			$result = $client->SendMessageWithLineNumber($parameters);
			if( isset( $result->SendMessageWithLineNumberResult ) )
			{
				return 1;
			}
			return -7;
			
		
		}
		else
		{
			
			$emailAddr = $this->_getCustomerEmailAddress($data['cu_id']);
			
			if( $emailAddr < 0 )	return  $emailAddr;
			
			$message = '<div style="direction:ltr;">Dear User <br />Here is your activation code: <span style="color:red;">'.$code.'</span></div>';
			if(isset($argus['message']))
				$message = str_replace('#raya-activation-code#', $code, $argus['message']);
			
			$email = new Zend_Mail('UTF-8');
			$email->setBodyHtml($message);
			$email->setFrom('support@rayadars.com', 'rayaDars');
			$email->setSubject("کد فعال سازی");
			
			$email->addTo( $emailAddr , '');
			try
			{	
				$email->send();
				return 1;
			}
			catch (Zend_Exception $e)
			{
				return -6;
				
			}
		}

	}
	
	protected function	_getCustomerCellPhoneNumber($argus)
	{
		if(!is_string($argus))	return -1;
		$argus = strtoupper($argus);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus))	return -1;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT `cu_cellphone` FROM `customer_info` WHERE `cu_code`='".$argus."' ;";
		if(! $phoneNum = $this->DB->fetchOne($sql)) return -1;
		if( preg_match("/^09\d{9}$/", $phoneNum) ) return $phoneNum;
		return -21;
	}
	
	protected function	_getCustomerEmailAddress($argus)
	{
		if(!is_string($argus))	return -1;
		$argus = strtoupper($argus);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus))	return -1;
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT `cu_email` FROM `customer_info` WHERE `cu_code`='".$argus."' ;";
		if(! $emailAddr = $this->DB->fetchOne($sql)) return -1;
		$patt = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9])+[a-zA-Z0-9\_\-]*(\.[a-zA-Z]+)+$/";
		if( preg_match($patt, $emailAddr) ) return $emailAddr;
		return -22;
		
	}
		
	public function ComputeActivationCodeV1($argus)
	{
		if(!is_string($argus['customer']) or !is_string($argus['system']))	return false;
		$argus['customer'] = strtoupper($argus['customer']);
		$argus['system'] = strtoupper($argus['system']);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus['customer']))	return false;			
		if(!preg_match("/^[1-9A-Z]{25}$/", $argus['system']))	return false;
		
		$today = new Zend_Date();
		$ActkeyId = $today->get(Zend_Date::DAY_OF_YEAR)+1;		
		//if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		//$sql = "SELECT `key` FROM `activation_keys_366` WHERE `ak_id`='".$ActkeyId."' ;";
		//if(! $Akey = $this->DB->fetchOne($sql)) return false;		
		if(! $Akey = $this->_getKey366($ActkeyId)) return false;	
		
		$argus['customer'] = $argus['customer'].$argus['customer'].$argus['customer'].$argus['customer'].$argus['customer'];
		$result = '';
		for ($i = 0; $i < 25; $i++)
        {
			$alfa = $this->_num32DigitIndex($argus['customer'][$i]);
            $beta = $this->_num32DigitIndex($Akey[$i]);
            $gama = $this->_num32DigitIndex($argus['system'][$i]);
			$avg  = round(($alfa+$beta+$gama)/3, 0, PHP_ROUND_HALF_EVEN);
			$min  = min($alfa, $beta, $gama);
			$binery  = (($alfa + 1) % 2 != 0) ? "1" : "0";
			$binery .= (($beta + 1) % 2 != 0) ? "1" : "0";
            $binery .= (($gama + 1) % 2 != 0) ? "1" : "0";
            $binery .= (($avg  + 1) % 2 != 0) ? "1" : "0";
            $binery .= (($min  + 1) % 2 != 0) ? "1" : "0";
			$resIndex = bindec($binery);
			$result .= $this->_num32Digit($resIndex);
		}
		if(!preg_match("/^[1-9A-Z]{25}$/", $result)) return false;
		return $result;
	}
	protected function ComputeActivationCodeV2($argus)
	{
		
		if(!is_string($argus['customer']) or !is_string($argus['system']))	return 0;
		$argus['customer'] = strtoupper($argus['customer']);
		$argus['system'] = strtoupper($argus['system']);
		if(!preg_match("/^[1-9A-Z]{5}$/", $argus['customer']))	return -1;			
		if(!preg_match("/^[1-9A-Z]{25}$/", $argus['system']))	return -2;
		
		$today = new Zend_Date();
		$ActkeyId = $today->get(Zend_Date::DAY_OF_YEAR)+1;		
		if(! $Akey = $this->_getKey366($ActkeyId)) return 0;
		
		if(! $ProductKey = $this->ComputeProductKey() )	return -8;
		$argus['customer'] = $argus['customer'].$argus['customer'].$argus['customer'].$argus['customer'].$argus['customer'];
		
		$result = '';

		for ($i = 0; $i < 25; $i++)
        {
			$alfa = $this->_num32DigitIndex($argus['customer'][$i]);
            $beta = $this->_num32DigitIndex($Akey[$i]);
            $gama = $this->_num32DigitIndex($argus['system'][$i]);
			$landa= $this->_num32DigitIndex($ProductKey[$i]);
			
			$avg  = round(($alfa+$beta+$gama+$landa)/4, 0, PHP_ROUND_HALF_EVEN);
			//$min  = min($alfa, $beta, $gama);
			$binery  = (($alfa + 1) % 2 != 0) ? "1" : "0";
			$binery .= (($beta + 1) % 2 != 0) ? "1" : "0";
            $binery .= (($gama + 1) % 2 != 0) ? "1" : "0";
			$binery .= (($landa + 1) % 2 != 0) ? "1" : "0";
            $binery .= (($avg  + 1) % 2 != 0) ? "1" : "0";
            //$binery .= (($min  + 1) % 2 != 0) ? "1" : "0";
			$resIndex = bindec($binery);
			$result .= $this->_num32Digit($resIndex);			
		}
		if(!preg_match("/^[1-9A-Z]{25}$/", $result)) return 0;
		return $result;
	}

	protected function _getKey366($index)
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = "SELECT `key` FROM `activation_keys_366` WHERE `ak_id`='".$index."' ;";
		if(! $key = $this->DB->fetchOne($sql)) return false;
		return $key;
	}
	protected function _num32DigitIndex($cahr)
	{
		$Num32Chars = "123456789BCDEFGHIJKLNOPQSTUVWXYZ";
		$pos = strrpos($Num32Chars, $cahr);
		return $pos;
	}
	protected function _num32Digit($index)
	{
		$Num32Chars = "123456789BCDEFGHIJKLNOPQSTUVWXYZ";
		return $Num32Chars[$index];
	}
    protected function  ComputeProductKey()
    {
        if(!$products = $this->_getProductList()) return false;
        $result = "";
        $i = 0;
        foreach ($products as $product)
        {
            $position = $product['p_position'];
            $pKey = $this->_getKey366($position+1);
            if ($i == 0) $result = $pKey;
            else        $result = $this->_sub2Key($result, $pKey);
            $i++;
        }
        return $result;
    }
    protected function _sub2Key($key1, $key2)
    {
        $result = array();
        for ($i = 0; $i < 25; $i++)
        {
            $a = $this->_num32DigitIndex($key1[$i]);
            $b = $this->_num32DigitIndex($key2[$i]);
            $c = $a - $b;
            if ($c < 0) $c = $c * (-1); //c = 31 + c; // 
            $result[$i] = $this->_num32Digit($c);
        }
        return implode('', $result);
    }
    protected function _getProductList()
    {
        if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sqli = "SELECT sp_product_id FROM sold_products WHERE sp_customer_id='".$this->customer."' AND sp_status=1";
        $sql = "SELECT p_id, p_position FROM products WHERE p_id IN (".$sqli.") ORDER BY p_position";
        if( !$products = $this->DB->fetchAll($sql) ) return false;
        return $products;
    }
	
}

?>