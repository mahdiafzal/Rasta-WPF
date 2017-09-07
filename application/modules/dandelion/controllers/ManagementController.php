<?php
 
class Dandelion_ManagementController extends Zend_Controller_Action 
{

	public function init() 
	{
		$this->_helper->_layout->setLayout('dashboard');
	}
	public function frmregisterAction()
	{
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('a')); 
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/dandelion/management/crt');
		
		$id	=$this->_getParam('id');
		if (is_numeric($id)) $this->getDandelionForEdit($id);
	}
    public function frmlistAction()
    {
    	$this->DB			= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('b')); 

		$st		= $this->_getParam('st');
		$start	= (is_numeric($st))?$st:0;
		$limit	= 25;
		$pubcon	= Application_Model_Pubcon::get();
		
		$sql	= 'SELECT * FROM `wbs_dandelions` WHERE '.$pubcon.' ORDER BY `dn_id` DESC LIMIT '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		$count	= $this->DB->fetchOne('SELECT count(*) as `cnt` FROM `wbs_dandelions` WHERE '.$pubcon);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$id	= $this->_getParam('id');
		if(is_numeric($id))
			if( $result	= $this->DB->delete('wbs_dandelions', Application_Model_Pubcon::get(1001).' and dn_id="'. addslashes($id) .'"') )
				$result	= $this->DB->delete('wbs_dandelion_actions', Application_Model_Pubcon::get(1000).' and da_dn_id="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $this->translate->_('f');
		else			$msg[] = $this->translate->_('i');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dandelion/management/frmlist#fragment-3');
    }
    public function editAction()
    {
		$this->params	= $this->_getAllParams();

		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dandelion/management/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration();
		$this->updateDandelion($data);
    }
    public function crtAction()
    {
		$this->params	= $this->_getAllParams();
		$data			= $this->prepareRegistration();
		$this->insertNewDandelion($data);
    }
	public function delactAction()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
    	$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');

		$params	= $this->_getAllParams();
		
		if(!is_numeric($params['did']) or !is_numeric($params['daid']) )
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$translate->_('ae') ) );
		if(! $this->DB->fetchAll("SELECT `dn_id` FROM `wbs_dandelions` WHERE ".Application_Model_Pubcon::get(1001)." AND `dn_id`='".addslashes($params['did'])."'") )
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$translate->_('ae') ) );

		$result	= $this->DB->delete('wbs_dandelion_actions', Application_Model_Pubcon::get(1000).' and `da_id`='.addslashes($params['daid']).' AND `da_dn_id`='.addslashes($params['did']));	
		if ($result)
			$this->_helper->json->sendJson(array('state'=>'true', 'msg'=>$translate->_('af'), 'daid'=>$params['daid'] ) );
		else
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$translate->_('ag') ) );

    }
		
	
/// Helper Method for Actions -------------------------------------------------------------------*********
    protected function getDandelionForEdit($id)
    {	
		$this->view->assign('title_site', $this->translate->_('d')); 
		$this->view->assign('form_action', '/dandelion/management/edit/id/'.$id);

		$sql	= "SELECT * FROM `wbs_dandelions` WHERE ".Application_Model_Pubcon::get(1001)." AND `dn_id`='".addslashes($id)."'";
		
		if(!$result	= $this->DB->fetchAll($sql))	$msg[]= $this->translate->_('j');
		else
		{
			$sql	= "SELECT * FROM `wbs_dandelion_actions` WHERE `wbs_id`=".$result[0]['wbs_id']." AND `da_dn_id`='".addslashes($id)."'";
			if(!$actions = $this->DB->fetchAll($sql))	$actions	= array();
			else
				foreach($actions as $key=>$value)
					$actions[$key]['da_xal']	= stripslashes($value['da_xal']);
			$this->view->assign('data', $this->arrayParams($result[0], $actions) );
		}
		if (is_array($msg) and count($msg)>0)
		{
			//$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dandelion/management/frmlist#fragment-3');
		}
    }
	protected function prepareRegistration()
	{
		
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$this->setUriParams();
		$this->getUserGroups();
		$this->validate();
		$data	= $this->arrayDbData();
		return $data;
		
		print_r($this->params);
		die();
	}
	protected function arrayParams($result, $acts)
	{
		$data	= array();
		$data['title']		= $result['dn_title'];
		$data['user_group']	= $result['user_group'];
		$data['did']		= $result['dn_id'];
		$data['status']		= $result['dn_status'];
		$data['default']	= $result['dn_default'];
		foreach($acts as $act)
			$data['acts'][]	= array(
				'daid'=>$act['da_id'],
				'name'=>$act['da_name'],
				'type'=>$act['da_type'],
				'code'=>$act['da_xal'],
				'success'=>$act['da_success'],
				'failure'=>$act['da_failure']
				);
		return $data;
	}
	protected function arrayDbData()
	{
		$data1['dn_title']	= $this->params['title'];
		$data1['dn_status']	= $this->params['status'];
		$data1['dn_default']	= $this->params['default'];
		$data1['user_group']= $this->params['user_group'];

		$i=0;
		$act_wbs_id	= WBSiD;
		if(WBSiD==='1' and is_numeric($this->params['did']))
			$act_wbs_id	= $this->DB->fetchOne( 'SELECT `wbs_id` FROM `wbs_dandelions` WHERE '.Application_Model_Pubcon::get().' AND `dn_id`='.addslashes($this->params['did']) );
		foreach($this->params['acts'] as $act)
		{
			$data2[$i]	= array(
				'wbs_id'=>$act_wbs_id,
				'da_dn_id'=>$this->params['did'],
				'da_name'=>$act['name'],
				'da_type'=>$act['type'],
				'da_xal'=>$act['code'],
				'da_success'=>$act['success'],
				'da_failure'=>$act['failure']
				);
		if(is_numeric($act['daid']))	$data2[$i]['da_id']	= $act['daid'];
		$i++;
		}
		return array($data1, $data2);
	}
	
	protected function validate()
	{
		if( strlen( trim($this->params['title']) )<3 )		$error[]	= $this->translate->_('u'); 
		if( strlen( trim($this->params['default']) )<3 )	$error[]	= $this->translate->_('v'); 
		foreach($this->params['acts'] as $key=>$value)
		{
			$this->params['acts'][$key]	= $value = array_map(trim, $value);
			if( empty($value['name']) )	$er1	= $this->translate->_('w');
		}
		if(isset($er1))	$error[]	= $er1; 
		
		if($this->params['did']!=$this->params['id'])									$sysError	= $this->translate->_('h');
		if(!empty($this->params['did']) and !is_numeric($this->params['did']) )  		$sysError	= $this->translate->_('h');
		if(isset($sysError)) $error[]	= $sysError;

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dandelion/management/frmregister'.$this->newUriParams);
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
	protected function getUserGroups()
	{
		$this->params['user_group']	= '0';
		if(!is_array($this->params['ugroup']) || count($this->params['ugroup'])==0) return false;
		sort($this->params['ugroup']);
		$ugroup		= '/'. implode('/', $this->params['ugroup']).'/';
		$this->params['user_group']	= $ugroup;
		return true;
	}
	protected function setUriParams()
	{
		$this->newUriParams =	'';
		if ( is_numeric($this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-3';
	}
	
	
	protected function insertNewDandelion($data)
	{
		//print_r($data); die();
		try
		{
			$data[0]['wbs_id']		= WBSiD;
			$this->DB->insert('wbs_dandelions', $data[0]);
			$lastID	= $this->DB->lastInsertId();
			if(!empty($lastID))
			{
				$this->DB->update('wbs_dandelions'	, array('dn_md_id'=> md5($lastID)) , Application_Model_Pubcon::get(1000).' and `dn_id` ='.$lastID );
				foreach($data[1] as $indata)
				{
					$indata['da_dn_id']	= $lastID;
					$this->DB->insert('wbs_dandelion_actions', $indata);
				}

					//$this->DB->insert('wbs_dandelion_actions', $data[1]);
				
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			}
			else
			{
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('ab') ));
			}
			$this->_redirect('/dandelion/management/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dandelion/management/frmregister'.$this->newUriParams);
		}
	}
	protected function updateDandelion($data)
	{
		try
		{
			$this->DB->update('wbs_dandelions'	, $data[0] , Application_Model_Pubcon::get(1000).' and `dn_id` ='.addslashes($this->params['id']) );
			foreach($data[1] as $indata)
				if(isset($indata['da_id']) and is_numeric($indata['da_id']))
				{
					$daid	= $indata['da_id'];
					unset($indata['da_id']);
					$this->DB->update('wbs_dandelion_actions', $indata,
					 Application_Model_Pubcon::get(1000).' AND `da_id` ='.addslashes($daid).' AND `da_dn_id` ='.addslashes($this->params['id']));
				}
				elseif(!isset($indata['da_id']))
					$this->DB->insert('wbs_dandelion_actions', $indata);

			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/dandelion/management/frmlist'.$this->newUriParams );

		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dandelion/management/frmregister'.$this->newUriParams);
		}
	}
	
	/*public function formgeneratorAction()
	{
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('ae')); 

		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id))
		{
			$skin	= $this->getDandelionForFormEdit($id);
			preg_match_all('/\#rasta\-dandelion\-[^\#]+\#/', $skin, $danvars);
			$this->view->assign('danvars', $danvars);
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dandelion/management/frmlist#fragment-3');
		}
//		print_r($danvars);
//		die($skin);
	}*/
    /*public function getDandelionForFormEdit($id)
    {	
		$sql	= "SELECT `skin` FROM `wbs_dandelions` WHERE `wbs_id` ='".WBSiD."' AND `dn_id`='".addslashes($id)."'";
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			return stripslashes($result[0]['skin']);
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dandelion/management/frmlist#fragment-3');
		}
    }*/	
}
