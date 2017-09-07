<?php
 
class Dashboard_ManualController extends Zend_Controller_Action 
{
	public function indexAction() 
    {
		$ses 	= new Zend_Session_Namespace('MyApp');
		$mdi	= $this->_getParam('mdi');
		if(empty($mdi)) $mdi	= 1;
		$ses->mdi	= $mdi;
		$this->_redirect('/');
    }
    public function frmlistAction()
    {
    	$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('b')); 

		$st	= $this->getRequest()->getParam('st');
		if ((!empty($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 25;
		$sql	= 'select * from `wbs_manual_dashboard` where '.Application_Model_Pubcon::get().' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		$count	= $this->DB->fetchAll('select count(*) as `cnt` from `wbs_manual_dashboard` where '.Application_Model_Pubcon::get());
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
	public function frmregisterAction()
	{
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		$wbsMenus	= $this->getWbsMenus();
		if(empty($wbsMenus))
		{
			$msg[]= $this->translate->_('d');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dashboard/manual/frmlist#fragment-3');
		}
		$this->view->assign('wbsMenus', $wbsMenus);
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('a')); 
		
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		
		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/dashboard/manual/crt');
		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getMDashForEdit($id);
	}
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$id	= $this->getRequest()->getParam	('id');
		$result	= $this->DB->delete('wbs_manual_dashboard','wbs_id="'.WBSiD.'" and id="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $this->translate->_('f');
		else			$msg[] = $this->translate->_('i');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/manual/frmlist#fragment-3');
    }
    public function editAction()
    {
		$this->params		= $this->_getAllParams();

		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/manual/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration();
		$this->updateMDash($data);
    }
    public function crtAction()
    {
		$this->params	= $this->getRequest()->getParams();
		$data			= $this->prepareRegistration();
		$this->insertNewMDash($data);
    }



/// Helper Method for Actions -------------------------------------------------------------------*********
	protected function getWbsUserGroups()
	{
		$sql		= "SELECT * FROM `user_groups` WHERE ".Application_Model_Pubcon::get(1110);
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
    public function getMDashForEdit($id)
    {	
		$this->view->assign('form_action', '/dashboard/manual/edit/id/'.$id);

		$sql	= "SELECT * FROM `wbs_manual_dashboard` WHERE `wbs_id` ='".WBSiD."' AND `id`=".addslashes($id);
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			$data	= $this->arrayParams($result[0]);
			$this->view->assign('data', $data);
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dashboard/manual/frmlist#fragment-3');
		}
    }	
	public function getWbsMenus()
	{
		$sql		= "SELECT `id`,`menu_title` FROM `wbs_menu` WHERE `wbs_id`='".WBSiD."'";
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	public function prepareRegistration()
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
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';
		if ( preg_match('/^\d+$/', $this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-3';
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'title'	=> $this->params['title'],
						'menu'	=> $this->params['menu']
			 		 );
		
		$rule=array	(
						'title'	=>'notNull',
						'menu'	=>'isNumber'
					);

		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('title')== false) $error[]	= $this->translate->_('u'); 
		if($frmValidator->getResult('menu')	== false) $error[]	= $this->translate->_('w');
		
		$sysError	= ''; 
		if($this->params['did']!=$this->params['id'])										$sysError	= $this->translate->_('h');
			if(!empty($this->params['did']) and !is_numeric($this->params['did']) )  		$sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/manual/frmregister'.$this->newUriParams);
			return false;
		}
		return true;
	}


	public function arrayParams($result)
	{
		$data	= array();
		$data['did']	= $result['id'];
		$data['title']	= $result['title'];
		$data['menu']	= $result['menu_id'];
		$data['user_group']	= $result['user_group'];
		return $data;
	}
	public function arrayDbData()
	{
		$data['title']		= $this->params['title'];
		$data['menu_id']	= $this->params['menu'];
		$data['user_group']	= $this->params['user_group'];
		return $data;
	}
	public function insertNewMDash($data)
	{
		try
		{
			$data['wbs_id']		= WBSiD;
			$result	= $this->DB->insert('wbs_manual_dashboard', $data);
			if($result)
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			else
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('ab') ));
			$this->_redirect('/dashboard/manual/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/manual/frmregister'.$this->newUriParams);
		}
	}
	public function updateMDash($data)
	{
		try
		{
			$this->DB->update('wbs_manual_dashboard', $data ,'`wbs_id` = '.WBSiD.' and `id` ='.addslashes($this->params['id']) );
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/dashboard/manual/frmlist'.$this->newUriParams );

		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/manual/frmregister'.$this->newUriParams);
		}
	}
}
