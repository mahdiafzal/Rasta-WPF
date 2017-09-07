<?php

class Usermanager_Model_Usermanager
{
	var $DB;
	//------------------
	public function Usermanager_Model_Usermanager()
	{
		$registry 		= Zend_Registry::getInstance();
		$this->DB		= $registry['front_db'];
	}
	//------------------
	public function authenticate($username,$password)
	{
		$auth			= Zend_Auth::getInstance(); 
		$authAdapter	= new Zend_Auth_Adapter_DbTable($this->DB);
		$authAdapter	->setTableName			('users')
						->setIdentityColumn		('username')
						->setCredentialColumn	('password');
						
		$select = $authAdapter->getDbSelect();
		$select->where('`wb_user_id`='.WBSiD);
		
		// Set the input credential values
		$uname 			= $username;
		$paswd 			= $password;
		$authAdapter	->setIdentity	($uname);
		$authAdapter	->setCredential	($paswd);
		// Perform the authentication query, saving the result
		$result 		= $auth->authenticate($authAdapter);
		if($result->isValid())
		{
			$data = $authAdapter->getResultRowObject();
			
			/// sub groups start
			$data->all_groups	= $data->user_group;
			if(strlen($data->user_group)>1)
			{
				$u_groups	= preg_replace('/\//', ', ', $data->user_group);
				$sql		= "SELECT subs FROM user_group_allsubs WHERE ".Application_Model_Pubcon::get(1100)." AND `ug_id` IN (".$u_groups.")";
				if($result = $this->DB->fetchAll($sql))
				{
					$allsubs	= array_filter(explode('/', $data->user_group));
					foreach($result as $gsub)	$allsubs	= array_merge($allsubs , array_filter(explode('/',$gsub['subs'])) );
					$data->all_groups	= implode('/', array_unique($allsubs) );
				}
			}
			/// sub groups end

			if ($data->wb_user_id==WBSiD)
			{
				switch($data->is_active)
				{
					case  '1' :	
								$auth->getStorage	()->write($data); 
								//add to session
								$ses				= new Zend_Session_Namespace('MyApp');
								if($data->id=='1')
								{
									$ses->isAdmin = true; // is admin
								}
								else
								{
									$ses->isAdmin = false;	//is not admin						
								}
								$ses->id	= $data->id; //add id to session
								return   1	; //is active
								break;
					case  '0' :	$auth->clearIdentity()    			; return   0	; break;//not active
					case '-1' :	$auth->clearIdentity()	 			; return  -1	; break;//pending
				}
			}
			else
			{
				$auth->clearIdentity();
				return  -3;
			//error
			}
		}
		else
		{
			return  -2 ; //not find
		}
	}
	//------------------
	public function authenticate2($username,$password)
	{
		$auth			= Zend_Auth::getInstance(); 
		$authAdapter	= new Zend_Auth_Adapter_DbTable($this->DB);
		$authAdapter	->setTableName			('users')
						->setIdentityColumn		('username')
						->setCredentialColumn	('password');    
		// Set the input credential values
		$uname 			= $username;
		$paswd 			= $password;
		$authAdapter	->setIdentity($uname);
		//$authAdapter->setCredential(md5($paswd));
		$authAdapter	->setCredential($paswd);
		// Perform the authentication query, saving the result
		$result 		= $auth->authenticate($authAdapter);
		if($result->isValid())
		{
			$data = $authAdapter->getResultRowObject();
			$auth->getStorage()->write($data);
			//add to session
			$ses				= new Zend_Session_Namespace('MyApp');
			if($data->id=='1')
			{
				$ses->isAdmin 	= true; // is admin
			}
			else
			{
				$ses->isAdmin 	= false;	//is not admin						
			}
			$ses->id	= $data->id; //add id to session

			return true;
		}
		else 
		{
			return false;
		}
	}
	//------------------
	public function chkUsername($username)
	{
		//$sql 	= "SELECT * FROM `users` WHERE `username`='".$username."';";
		$sql 	= "SELECT * FROM `users` WHERE `username`='".$username."' and `wb_user_id`=".WBSiD ;
		//echo $sql;
		$result = $this->DB->fetchRow($sql);		
		if ($result)
		{
			return $result['is_active'];
		}
		else
		{
			return -2;
		}
	}
	//------------------
	public function sendEmail($username,$date='')
	{
		if( ! $msg = Application_Model_Messages::message(202) )	return false;
		if( ! $msg = Rasta_Xml_Parser::getArr('<root>'.$msg.'</root>') ) return false;
		
		$date		=  ($date=='') ?  date('Y-m-d') : '----------' ;		
		//temporary
		$data_temp	['userid']		= md5(time().(md5(time().$username)));
		$data_temp	['code']		= md5(md5($username.time()).time());
		$data_temp	['username']	= $username;
		$data_temp	['date']		= $date;
		$data_temp	['sender']		= 'admin';
		try 
		{
			//$this->messages	= Rasta_Application_Configs_Messages::Rasta_Usermanager();		
			$this->DB->insert	('users_temp', $data_temp);
			
			$link	= "http://".$_SERVER['HTTP_HOST']."/admin/user/activation/".$data_temp['userid'].".".$data_temp['code'];
			//$body	= $this->messages['authemail'][1].$link.$this->messages['authemail'][2];
			$body	= str_replace('#rasta-activation-link#', $link, $msg['message']);
			
			//----email
			$mail	= new Zend_Mail('UTF-8');
			$mail	->setBodyHtml($body)
					->setFrom($msg['from']['address'], $msg['from']['name'])
					->addTo($username, $msg['to']['name'])
					->setSubject($msg['subject'])
					->send();	
			
//					->setFrom($this->messages['authemail']['fromemail'], $this->messages['authemail']['fromtitle'])
//					->addTo($username, $this->messages['authemail']['totitle'])
//					->setSubject($this->messages['authemail']['subject'])
					
			if ($mail)	return true;	
			else		return false;
		}
		catch (Zend_Exception $e)
		{
			return false;
		}
	}
	//------------------
//	public function activation($data)
//	{
//		$arr	=explode('/',$data);
//		foreach ($arr as $key=>$val)
//		{
//			if ($val=='activation')
//			{
//				$data=$arr[$key+1];
//				break; 
//			}
//		}
//		$arr	=explode('.',$data);
//		$userid	=$arr[0];
//		$code	=$arr[1];
//		//end of split userid and code from uri
//		$sql 	= "SELECT * FROM `users_temp` WHERE `userid`='".$userid."' and `code`='".$code."' and `sender`='admin';";
//		$result = $this->DB->fetchRow($sql);
//		if($result)
//		{
//			$date=$result['date'];
//			if ($date==date('Y-m-d') or ($date=='----------'))
//			{
//				//update
//				$data		=array('is_active'=>'1');
//				$this->DB->update('users', $data,"username ='".$result['username']."'");	
//				//delete
//				$this->DB->delete('users_temp',"userid='".$userid."' and code='".$code."'");	
//				//fetch user info
//				$sql 			= "SELECT * FROM `users` WHERE `username`='".$result['username']."';";
//				$result 		= $this->DB->fetchRow($sql);
//				if ($result)
//				{
//					$res	= $this->authenticate2($result['username'],$result['password']);
//					if ($res)
//					{
//						return true;	
//					}
//					else
//					{	
//						return false;
//					}
//				}
//				else
//				{
//					return false;
//				}
//			}
//			else
//			{
//				//delete
//				$this->DB->delete('users_temp',"userid='".$userid."' and code='".$code."'");
//				return false;
//			}
//		}
//		else
//		{
//			return false;
//		}				
//	}

	public function activation($data)
	{
		$arr	=explode('/',$data);
		foreach ($arr as $key=>$val)
		{
			if ($val=='activation')
			{
				$data=$arr[$key+1];
				break; 
			}
		}
		$arr	=explode('.',$data);
		$userid	=$arr[0];
		$code	=$arr[1];
		//end of split userid and code from uri
		$sql 	= "SELECT * FROM `users_temp` WHERE `userid`='".$userid."' and `code`='".$code."' and `sender`='admin';";
		$result = $this->DB->fetchRow($sql);
		if($result)
		{
			$date=$result['date'];
			if ($date==date('Y-m-d') or ($date=='----------'))
			{
				$res = $this->DB->fetchRow("SELECT * FROM `users` WHERE `username`='".$result['username']."';");
				if($res['is_active']!='-1')
				{
					//update
					$data		=array('is_active'=>'1');
					$this->DB->update('users', $data,"username ='".$result['username']."'");	
					//delete
					$this->DB->delete('users_temp',"userid='".$userid."' and code='".$code."'");	
					//fetch user info
					$sql 			= "SELECT * FROM `users` WHERE `username`='".$result['username']."';";
					$result 		= $this->DB->fetchRow($sql);
					if ($result)
					{
						$res	= $this->authenticate2($result['username'],$result['password']);
						if ($res)
						{
							return '6';
						}
						else
						{	
							return '5';
						}
					}
					else
					{
						return '4';
					}
				}
				else
				{
					return '3';
				}
			}
			else
			{
				//delete
				$this->DB->delete('users_temp',"userid='".$userid."' and code='".$code."'");
				return '2';
			}
		}
		else
		{
			return '1';
		}				
	}
	//------------------
	public function listing()
	{
		$this->DB->setFetchMode(Zend_Db::FETCH_OBJ);
		$sql 	= "SELECT * FROM `users` ORDER BY id ASC";
		$result = $this->DB->fetchAssoc($sql);
		return 	$result;
	}
	//------------------
	public function doAct($id,$typ)
	{
		switch ($typ)
		{
		case  1 :
			$data['is_active']=	1 ;
			$this->DB->update('users', $data ,"id =".$id );
			break;
		case  0 :
			$data['is_active']=	0 ;
			$sql 	= "SELECT `username` FROM `users` where `id`=".addslashes($id);
			$result = $this->DB->fetchRow($sql);
			if ($result)
			{
				if ($this->sendEmail($result['username'], '----------')==true)
				{ 
					$this->DB->update('users', $data ,"id =".$id );
				}
			}
			break;
		case -1 :
			$data['is_active']=	-1 ;
			$this->DB->update('users', $data ,"id =".$id );
			break;
		}
	}
	//------------------
}