<?php

class Godpanel_Model_User_User
{
	
	protected $DB;
	public function	__construct()
	{
		$this->DB	= Zend_Registry::get('front_db');
	}
	static function initUser()
	{
		self::_initUser();
	}
	static function authenticate($username, $password)
	{
		return self::_authenticate($username, $password);
	}
	static function checkUsername($username)
	{
		return self::_checkUsername($username);
	}
	static function sendEmail($username, $data)
	{
		return self::_sendEmail($username, $data);
	}
	static function activation($data)
	{
		return self::_activation($data);
	}


	protected function _initUser()
    {
		$auth		= Zend_Auth::getInstance(); 
		if(!$auth->hasIdentity()) return false;
		$user		= $auth->getIdentity();
		//if(isset($user->wb_user_id)) return false;
		define('USRiD'	, $user->id);
		define('USRnAME', $user->username);
    }
	
	public function _authenticate($username, $password)
	{
		$DB				= (isset($this))?$this->DB:Zend_Registry::get('front_db');
		$username		= addslashes($username);
		$password		= addslashes($password);
		
		$auth			= Zend_Auth::getInstance(); 
		$authAdapter	= new Zend_Auth_Adapter_DbTable( $DB );
		$authAdapter	->setTableName			('host_users')
						->setIdentityColumn		('username')
						->setCredentialColumn	('password');

		// Set the input credential values
		$authAdapter	->setIdentity	($username);
		$authAdapter	->setCredential	($password);
		// Perform the authentication query, saving the result
		$result 		= $auth->authenticate($authAdapter);
		
		if($result->isValid())
		{
			$user = $authAdapter->getResultRowObject();
			switch($user->is_active)
			{
				case  '1' :	
							if($user->id==='1') $user->godAdmin	= 'e0a209539d1e74ab9fe46b9e01a19a97'; //md5('2085');
							$auth->getStorage()->write($user); 
							return   1	; //is active
							break;
				case  '0' :	$auth->clearIdentity()    			; return   0	; break;//not active
				case '-1' :	$auth->clearIdentity()	 			; return  -1	; break;//pending
			}
		}
		else
		{
			return  -2 ; //not found
		}
	
	}
	public function _checkUsername($username)
	{
		$DB		= (isset($this))?$this->DB:Zend_Registry::get('front_db');
		$sql 	= "SELECT * FROM `host_users` WHERE `username`='".addslashes($username)."';";
		$result = $DB->fetchRow($sql);		
		if ($result)	return $result['is_active'];
		return -2;
	}
	public function _sendEmail($username,$data)
	{
		$DB		= (isset($this))?$this->DB:Zend_Registry::get('front_db');
		$date		=  date('Y-m-d');		
		//temporary
		$data_temp	['userid']		= md5(time().(md5(time().$username)));
		$data_temp	['code']		= md5(md5($username.time()).time());
		$data_temp	['username']	= $username;
		$data_temp	['date']		= $date;
		$data_temp	['sender']		= 'cp';
		$username					= 'ali.ramezani62@gmail.com';
		try 
		{
			$DB->insert	('users_temp', $data_temp);
			$config			= Zend_Registry::get('config');
			$link			= "http://".$config->base->portal."/utopia/user/activation/".$data_temp['userid'].".".$data_temp['code'];		
			$sysParams[]	= '#system-protected-activationlink#';
			$paramValue[]	= $link;
			$sysParams[]	= '#config-base-portal#';
			$paramValue[]	= @$config->base->portal;
			$sysParams[]	= '#config-base-title#';
			$paramValue[]	= @$config->base->title;
			
			
			$data	= str_replace($sysParams, $paramValue, $data);
//			print_r($data); die('sdfsd');

			$mail	= new Zend_Mail('UTF-8');
			$mail	->setBodyHtml($data['msg'])
					->setFrom('admin@'.$config->base->portal, @$config->base->title)
					->addTo($username, 'User')
					->setSubject($data['subject'])
					//->send();
					;	die($data['msg']);
			if ($mail)	return true;	
			else		return false;
		}
		catch (Zend_Exception $e)
		{
//			die($e->getMessage());
			return false;
		}
	}
	public function _activation($data)
	{
		$DB				= (isset($this))?$this->DB:Zend_Registry::get('front_db');
		//split userid and code from uri
		$arr	=explode('/',$data);
		foreach ($arr as $key=>$val)	if ($val=='activation')	break; $data=$arr[$key+1];
		
		$arr	=explode('.',$data);
		$userid	=$arr[0];
		$code	=$arr[1];
		
		$sql 	= "SELECT * FROM `users_temp` WHERE `userid`='".$userid."' and `code`='".$code."' and `sender`='cp';";
		$result = $DB->fetchRow($sql);
		if(!$result)	return '1';

		$date	= $result['date'];
		if ($date!=date('Y-m-d'))
		{
			//delete
			$DB->delete('users_temp',"userid='".$userid."' and code='".$code."'");
			return '2';
		}
		
		$res = $DB->fetchRow("SELECT * FROM `host_users` WHERE `username`='".$result['username']."';");
		if($res['is_active']=='-1')	return '3';
		
		//update
		$data	= array('is_active'=>'1');
		$DB->update('host_users', $data,"username ='".$result['username']."'");	
		$DB->update('users', $data,"username ='".$result['username']."' AND is_admin=1");	
		//delete
		$DB->delete('users_temp',"userid='".$userid."' and code='".$code."'");	
		//fetch user info
		$result 		= $DB->fetchRow("SELECT * FROM `host_users` WHERE `username`='".$result['username']."';");
		if (!$result)	return '4';

		$res	= (isset($this))?$this->_authenticate($result['username'],$result['password']):self::_authenticate($result['username'], $result['password']);
		if ($res==-2)	return '5';
		return '6';

			

	}
}