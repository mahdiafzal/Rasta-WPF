<?php

class Rtcmanager_RtctypeController extends Zend_Controller_Action
{
//	public function init() 
//	{
//		//$this->_helper->_layout->setLayout('dashboard');
//	}
    public function frmlistAction()
    {
		$this->_helper->_layout->setLayout('dashboard');
		$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('c')); 
		$this->params	= $this->_getAllParams();
		$start	= (is_numeric($this->params['st']))?$this->params['st']:0;
		$limit	= 25;
		$pubcon1= Application_Model_Pubcon::get(1110, 'ct');
		$pubcon2= Application_Model_Pubcon::get(1001, 'ts');
		$sql1	= 'SELECT ct.ct_title, ct.wbs_id AS ct_wbs, ts.* FROM wbs_content_type AS ct LEFT JOIN wbs_content_type_setting AS ts ON ts.ts_ct_id = ct.ct_id '
				. ' WHERE '.$pubcon1.' AND '.$pubcon2.' ORDER BY ct.ct_id DESC LIMIT '.$start.','.$limit; 
		$sql2	= 'SELECT count(ct.ct_id) FROM wbs_content_type AS ct LEFT JOIN wbs_content_type_setting AS ts ON ts.ts_ct_id = ct.ct_id '
				. ' WHERE '.$pubcon1.' AND '.$pubcon2; 
		$result	= $this->DB->fetchAll($sql1);
		$count	= $this->DB->fetchOne($sql2);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg', $message[0]);	
    }
    public function frmplistAction()
    {
		$this->_helper->_layout->setLayout('dashboard');
		$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('b')); 
		$this->params	= $this->_getAllParams();
		$start	= (is_numeric($this->params['st']))?$this->params['st']:0;
		$limit	= 25;
		$pubcon1= Application_Model_Pubcon::get(1010);
		$pubcon2= Application_Model_Pubcon::get(1000);
		$sql1	= 'SELECT * FROM wbs_content_type WHERE '.$pubcon1
				. ' AND ct_id NOT IN (SELECT ts_ct_id FROM wbs_content_type_setting WHERE '.$pubcon2.')'
				. ' ORDER BY ct_id DESC LIMIT '.$start.','.$limit; 
		$sql2	= 'SELECT count(ct_id) FROM wbs_content_type WHERE '.$pubcon1
				. ' AND ct_id NOT IN (SELECT ts_ct_id FROM wbs_content_type_setting WHERE '.$pubcon2.')';
		$result	= $this->DB->fetchAll($sql1);
		$count	= $this->DB->fetchOne($sql2);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg', $message[0]);	
    }
	public function frmregisterAction()
	{
		$this->_helper->_layout->setLayout('dashboard');
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('g')); 
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		$this->view->assign('wbstaxoterms', $this->getWbsTaxTerms());
		$this->view->assign('wbsLangs', $this->getWbsLangs());

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/rtcmanager/rtctype/crt');
		
		$id	= $this->_getParam('id');
		if (is_numeric($id)) $this->getRtctypeForEdit($id);
	}
	public function installAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		$id	= $this->_getParam('id');
		try
		{
			if(is_numeric($id))
			{
				$sql	= 'SELECT count(ct_id) FROM wbs_content_type WHERE '.Application_Model_Pubcon::get(1010)
						. ' AND ct_id NOT IN (SELECT ts_ct_id FROM wbs_content_type_setting WHERE '.Application_Model_Pubcon::get(1000).')'
						. ' AND ct_id='.addslashes($id); 
				$count	= $this->DB->fetchOne($sql);
				if($count==1)
				{
					$data['wbs_id']		= WBSiD;
					$data['ts_ct_id']	= $id;
					$this->DB->insert('wbs_content_type_setting', $data);
					$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
					$this->_redirect('/rtcmanager/rtctype/frmplist'.$this->newUriParams);
					return;
				}
			}
		}
		catch(Zend_exception $e)
		{
			//$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			//$this->_redirect('/rtcmanager/rtctype/frmplist'.$this->newUriParams);
		}
		$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
		$this->_redirect('/rtcmanager/rtctype/frmplist'.$this->newUriParams);
    }
    public function editAction()
    {
		$this->params	= $this->_getAllParams();
		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/rtcmanager/rtctype/frmregister'.$this->newUriParams);
		}
		$data	= $this->prepareRegistration();
		$this->updateRtctype($data);
    }
    public function crtAction()
    {
		$this->params	= $this->_getAllParams();
		$data			= $this->prepareRegistration();
		$this->insertNewRtctype($data);
    }
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$id	= $this->_getParam('id');
		if(is_numeric($id)) 
			if( $result	= $this->DB->delete('wbs_content_type_setting', Application_Model_Pubcon::get(1001).' AND ts_ct_id="'. addslashes($id) .'"') )	
				$result2= $this->DB->delete('wbs_content_type', Application_Model_Pubcon::get(1000).' AND ct_id="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $this->translate->_('p');
		else			$msg[] = $this->translate->_('q');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/rtcmanager/rtctype/frmlist#fragment-1');
    }

/// Helper Method for Actions -------------------------------------------------------------------*********
    protected function getRtctypeForEdit($id)
    {	
		$this->view->assign('title_site', $this->translate->_('f')); 
		$this->view->assign('form_action', '/rtcmanager/rtctype/edit/id/'.$id);
		$sql	= 'SELECT ct.wbs_id AS ct_wbs, ct.lang, ct.ct_title, ct.ct_editor, ts.*'
				. ' FROM wbs_content_type AS ct INNER JOIN wbs_content_type_setting AS ts ON ts.ts_ct_id = ct.ct_id '
				. ' WHERE '.Application_Model_Pubcon::get(1110, 'ct')
				. ' AND '.Application_Model_Pubcon::get(1001, 'ts')
				. ' AND ct.ct_id='.addslashes($id); 
		if(!$result	= $this->DB->fetchAll($sql))
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/rtcmanager/rtctype/frmlist#fragment-1');
		}
		$this->view->assign('data', $this->arrayParams($result[0]) );
    }
	protected function arrayParams($result)
	{
		$data	= array();
		$data['title']		= $result['ct_title'];
		$data['user_group']	= $result['user_group'];
		$data['cid']		= $result['ts_ct_id'];
		$data['status']		= $result['ts_status'];
		$data['c_scens']	= $result['ts_data_sc'];
		$data['c_usrgs']	= $result['ts_data_ug'];
		$data['singpage']	= $result['ts_single'];
		$data['ct_wbs']		= $result['ct_wbs'];
		if($result['ct_wbs']==WBSiD or  WBSiD==='1')
		{
			$data['lang']	= $result['lang'];
			$data['editor']	= $result['ct_editor'];
		}
		return $data;
	}
	protected function prepareRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		$this->setUriParams();
		$this->params['user_group']	= $this->_genUgScString('ugroup');
		$this->params['c_usrgs']	= $this->_genUgScString('cusrgs');
		$this->params['c_scens']	= $this->_genUgScString('cscens');
		$this->validate();
		$data	= $this->arrayDbData();
		return $data;
	}
	protected function arrayDbData()
	{
		$data1['ts_status']	= $this->params['status'];
		$data1['ts_data_sc']= $this->params['c_scens'];
		$data1['ts_data_ug']= $this->params['c_usrgs'];
		$data1['user_group']= $this->params['user_group'];
		$data1['ts_single']= (is_numeric($this->params['singpage']) and $this->params['singpage']>10)?$this->params['singpage']:'12';
		$data2	= false;
		if(!isset($this->params['lang']))	return array($data1, $data2);
		$data2['ct_title']	= $this->params['title'];
		$data2['lang']		= $this->params['lang'];
		$data2['ct_editor']	= $this->params['editor'];
		return array($data1, $data2);
	}
	protected function validate()
	{
		if( strlen( trim($this->params['title']) )<3 )		$error[]	= $this->translate->_('u'); 
		if(is_numeric($this->params['id']))
			if(!is_numeric($this->params['cid']) or $this->params['cid']!=$this->params['id'])	$error[]	= $this->translate->_('n');
		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/rtcmanager/rtctype/frmregister'.$this->newUriParams);
			return false;
		}
		return true;
	}
	protected function getWbsUserGroups()
	{
		$sql		= "SELECT id, title FROM `user_groups` WHERE ".Application_Model_Pubcon::get(1110);
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	protected function getWbsTaxTerms()
	{
		$sql		= "SELECT id, title FROM `wbs_taxonomy_terms` WHERE ".Application_Model_Pubcon::get(1110);
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	protected function getWbsLangs()
	{
		$sql		= "SELECT la_code, la_title FROM `wbs_langs` WHERE ".Application_Model_Pubcon::get(1110);
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	protected function _genUgScString($key)
	{
		if(!is_array($this->params[$key]) || count($this->params[$key])==0) return '0';
		if(in_array(0,$this->params[$key]))	return '/0/';
		sort($this->params[$key]);
		return '/'. implode('/', $this->params[$key]).'/';
	}
	protected function setUriParams()
	{
		$this->newUriParams =	'';
		if ( is_numeric($this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-1';
	}
//	protected function getUserGroups()
//	{
//		$this->params['user_group']	= '0';
//		if(!is_array($this->params['ugroup']) || count($this->params['ugroup'])==0) return false;
//		sort($this->params['ugroup']);
//		$ugroup		= '/'. implode('/', $this->params['ugroup']).'/';
//		$this->params['user_group']	= $ugroup;
//		return true;
//	}
	protected function insertNewRtctype($data)
	{
		$this->DB->beginTransaction();
		try
		{
			$data[1]['wbs_id']	= $data[0]['wbs_id']	= WBSiD;
			$this->DB->insert('wbs_content_type', $data[1]);
			$lastID	= $this->DB->lastInsertId();
			$data[0]['ts_ct_id']	= $lastID;
			$this->DB->insert('wbs_content_type_setting', $data[0]);
			$this->DB->commit();
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/rtcmanager/rtctype/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->DB->rollBack();
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/rtcmanager/rtctype/frmregister'.$this->newUriParams);
		}
	}
	protected function updateRtctype($data)
	{
		$this->DB->beginTransaction();
		try
		{
			$sql	= 'SELECT `ts_id` FROM `wbs_content_type_setting` WHERE '.Application_Model_Pubcon::get(1001).' and `ts_ct_id` ='.addslashes($this->params['id']);
			if( $this->DB->fetchAll($sql) )
			{
				$this->DB->update('wbs_content_type_setting', $data[0], Application_Model_Pubcon::get(1001).' and `ts_ct_id` ='.addslashes($this->params['id']));
				if( $data[1] )
					$this->DB->update('wbs_content_type'	, $data[1] , Application_Model_Pubcon::get(1000).' and `ct_id` ='.addslashes($this->params['id']) );
			}
			$this->DB->commit();
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/rtcmanager/rtctype/frmlist'.$this->newUriParams );

		}
		catch(Zend_exception $e)
		{
			$this->DB->rollBack();
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/rtcmanager/rtctype/frmregister'.$this->newUriParams);
		}
	}

}

?>