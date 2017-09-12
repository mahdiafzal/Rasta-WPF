<?php
/*
	*	
*/



class Xal_Extension_RayaDars_LiveChat
{
	
	public function	run($argus)
	{
		/*if( !is_object($this->ses) )$this->ses = new Zend_Session_Namespace('LiveChat');
		$this->ses->user = 1;
		$this->ses->userLogin = true;*/


		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
		//$this->isOnlineChat = true;
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'send'			: return $this->_send($argu); break;
				case 'receive'		: return $this->_receive(); break;
				case 'is.online'	: return $this->_isAnyUserOnline(); break;
				case 'user.get.lines'	: return $this->_getUserLines(); break;
				case 'user.login'	: return $this->_loginChatUser($argu); break;
				case 'user.send'	: return $this->_sendUserResponse($argu); break;
				case 'user.checkup'	: return $this->_checkForNewEvents(); break;


			}
			
		}
	}
	
	protected function  _sayIamOnline($Iid)
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$where = 'co_user='.$Iid.' AND co_last > DATE_SUB(NOW(), INTERVAL 15 MINUTE)';
		if( $this->DB->update('chat_online', array('co_user'=>$Iid), $where ) ) return true;
		$data['co_user'] = $Iid;
		$data['co_start'] = new Zend_Db_Expr('NOW()');
		if(!$this->DB->insert('chat_online', $data)) return false;
		return true;
	}
	protected function helper_who_is_responsible()
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = 'SELECT co_user FROM `chat_online` WHERE co_last > DATE_SUB(NOW(), INTERVAL 15 MINUTE)';
		if(! $result = $this->DB->fetchAll($sql)) return false;
		return true;
	}
	protected function	_newLine($session)
	{
		if(!$responsible = $this->helper_who_is_responsible()) return false;
		$data['li_session']= $session;
		$data['li_responsible']= $responsible;
		//$this->isOnlineChat = $this->_isAnyUserOnline();
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if($this->DB->insert('chat_line', $data)) return $this->DB->lastInsertId('chat_line');
		return false;
	}
	protected function	_getLine()
	{
		$ses_id = session_id();
		if(empty($ses_id))
		{
			session_start();
			$ses_id = session_id();
		}
		//date_default_timezone_set('GMT');
		//$date = new Zend_Date();
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = 'SELECT * FROM `chat_line` WHERE li_session="'.$ses_id.'" AND `li_start` > DATE_SUB(NOW(), INTERVAL 5 HOUR) AND `li_end`="0000-00-00 00:00:00" LIMIT 0,1;';
		if(! $result = $this->DB->fetchAll($sql))
		{
			//$result = array( array('li_id' => $this->_newLine($argus, $ses_id )));
			return $this->_newLine($ses_id);
		}
		return $result[0]['li_id'];
	}	
	protected function	_send($argus)
	{
		$line = $this->_getLine();
		if(!is_numeric($line)) return false;
		$data['cm_message'] = $argus;//['message'];
		$data['cm_line'] = $line;
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if( !$this->DB->insert('chat_message', $data)) return false;
		//$this->_sayIamOnline(0, $line);
		return true;
	}
	protected function	_receive()
	{
		$line = $this->_getLine();
		if(!is_numeric($line)) return array();
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		//cm_type: visitor message=1, user response=2
		//cm_status: as read=1, as unread=0
		$where = ' `cm_type`=2 AND `cm_status`=0 AND `cm_line`='.$line;
		$sql = 'SELECT cm_time, cm_message FROM `chat_message` WHERE '.$where.' ORDER BY `cm_id` DESC;';
		if(! $result = $this->DB->fetchAll($sql)) return array();
		if( !$this->DB->update('chat_message', array('cm_status'=>'1'), $where ) ) return false;
		//$this->_sayIamOnline(0, $line);
		return $result;
		//return array('time'=>$result[0]['cm_time'] , 'message'=>$result[0]['cm_message'] );
	}
	protected function	_isAnyUserOnline()
	{
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = 'SELECT co_id FROM `chat_online` WHERE co_last > DATE_SUB(NOW(), INTERVAL 15 MINUTE)';
		if(! $result = $this->DB->fetchAll($sql)) return false;
		return true;
	}
	protected function _loginChatUser($argus)
	{
		if( !is_object($this->ses) )$this->ses = new Zend_Session_Namespace('LiveChat');
		$this->ses->user = 0;
		$this->ses->userLogin = false;
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = 'SELECT * FROM `chat_users` WHERE cu_status=1 AND cu_username="'.addslashes($argus['username']).'" AND cu_password="'.md5(addslashes($argus['password'])).'"';
		if(! $result = $this->DB->fetchAll($sql)) return false;
		if(count($result)!=1) return false;
		$this->ses->user = $result[0]['cu_id'];
		$this->ses->userLogin = true;
		
		$data['co_user'] = $result[0]['cu_id'];
		$data['co_start'] = new Zend_Db_Expr('NOW()');
		if(!$this->DB->insert('chat_online', $data)) return false;
		return true;
	}
	protected function helper_check_login()
	{
		if( !is_object($this->ses) )$this->ses = new Zend_Session_Namespace('LiveChat');
		if(	is_bool($this->ses->userLogin) && $this->ses->userLogin==true &&
			is_numeric($this->ses->user) && $this->ses->user>0 )
			return true;
		return false;
	}
	protected function _getUserLines()
	{
		if(!$this->helper_check_login()) return array('status'=>-1);
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sql = 'SELECT li_id FROM `chat_line` WHERE li_responsible="'.$this->ses->user.'" AND `li_start` > DATE_SUB(NOW(), INTERVAL 5 HOUR) AND `li_end`="0000-00-00 00:00:00" ;';
		if(! $result = $this->DB->fetchAll($sql)) return array('status'=>-2);
		$lines = array();
		foreach($result as $line)	$lines[] = $line['li_id'];
		sort($lines);
		$this->ses->userLines = $lines;
		return $lines;
	}
	protected function _sendUserResponse($argus)
	{
		if(!$this->helper_check_login()) return -1;
		if(!is_numeric($argus['line']) || !is_string($argus['message'])) return false;
		if(!isset($this->ses->userLines) || !in_array($argus['line'], $this->ses->userLines)) return false;
		$data['cm_message'] = $argus['message'];
		$data['cm_line'] = $argus['line'];
		$data['cm_type'] = 2;
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		if( !$this->DB->insert('chat_message', $data)) return false;
		$this->_sayIamOnline($this->ses->user);
		return true;
	}
	protected function _disruptTheLine($argus)
	{
		if(!$this->helper_check_login()) return -1;
		if(!is_numeric($argus)) return false;
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$where = 'li_responsible='.$this->ses->user.' AND li_id='.$argus;
		if( $this->DB->update('chat_line', array('li_end'=> new Zend_Db_Expr('NOW()') ), $where ) ) return true;
	}
	protected function _checkForNewEvents()
	{
		$_return['login'] = false;
		if(!$this->helper_check_login()) return $_return;
		$_return['login'] = true;
		$oldLines = (isset($this->ses->userLines))?$this->ses->userLines:array();
		$newLines = $this->_getUserLines();
		$_return['lines'] = array();
		if(count($newLines)==0)	return $_return;
		$_return['lines'] = $newLines;
		$_return['hasNewLine'] = count( array_diff($newLines, $oldLines) );
		$_return['newMessages'] = array();
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		//cm_type: visitor message=1, user response=2
		//cm_status: as read=1, as unread=0
		$where = ' `cm_type`=1 AND `cm_status`=0 AND `cm_line` IN ('.implode(',', $newLines).')';
		$sql = 'SELECT cm_time, cm_message, cm_line FROM `chat_message` WHERE '.$where.' ORDER BY `cm_id` DESC;';
		if(! $result = $this->DB->fetchAll($sql)) return $_return;
		if( !$this->DB->update('chat_message', array('cm_status'=>'1'), $where ) ) return $_return;
		$_return['newMessages'] = $result;
		$this->_sayIamOnline($this->ses->user);
		return $_return;
	}



	
}

?>