<?php
 
class Dashboard_MenuController extends Zend_Controller_Action 
{

    public function frmlistAction()
    {
    	$this->DB			= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('b')); 

		$st	= $this->getRequest()->getParam('st');
		if ((!empty($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 25;
		$pubcon	= Application_Model_Pubcon::get();
		$sql	= 'select * from `wbs_menu` where '.$pubcon.' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		$count	= $this->DB->fetchAll('select count(*) as `cnt` from `wbs_menu` where '.$pubcon);
		
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
		
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('a')); 
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/dashboard/menu/crt');
		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getMenuForEdit($id);
	}
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$id	= $this->getRequest()->getParam	('id');
		$result	= $this->DB->delete('wbs_menu', Application_Model_Pubcon::get(1001).'" and id="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $this->translate->_('f');
		else			$msg[] = $this->translate->_('i');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/menu/frmlist#fragment-2');
    }
    public function editAction()
    {
		$this->params		= $this->getRequest()->getParams();

		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/menu/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration();
		$this->updateMenu($data);
    }
    public function crtAction()
    {
		$this->params	= $this->getRequest()->getParams();
		$data			= $this->prepareRegistration();
		$this->insertNewMenu($data);
    }
	
	
/// Helper Method for Actions -------------------------------------------------------------------*********
    public function getMenuForEdit($id)
    {	
		$this->view->assign('form_action', '/dashboard/menu/edit/id/'.$id);

		$sql	= "SELECT * FROM `wbs_menu` WHERE ".Application_Model_Pubcon::get(1001)." AND `id`='".$id."'";
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			$this->view->assign('data', $this->arrayParams($result[0]) );
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dashboard/menu/frmlist#fragment-2');
		}
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
		$this->newUriParams .= '#fragment-2';
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'title'		=> $this->params['title']
			 		 );
		
		$rule=array	(
						'title'		=>'notNull'
					);

		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('title')	== false) $error[]	= $this->translate->_('u'); 
		
		$sysError	= ''; 
		if($this->params['mid']!=$this->params['id'])										$sysError	= $this->translate->_('h');
			if(!empty($this->params['mid']) and !is_numeric($this->params['mid']) )  		$sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;


		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/menu/frmregister'.$this->newUriParams);
			return false;
		}
		return true;
	}
	public function arrayParams($result)
	{
		$data	= array();
		$data['title']		= $result['menu_title'];
		$data['user_group']	= $result['user_group'];
		$data['mid']		= $result['id'];
		return $data;
	}
	public function arrayDbData()
	{
		$data1['menu_title']	= $this->params['title'];
		$data1['user_group']	= $this->params['user_group'];
		return $data1;
	}
	public function insertNewMenu($data)
	{
		try
		{
			$data['wbs_id']		= WBSiD;
			$this->DB->insert('wbs_menu', $data);
			$lastID	= $this->DB->lastInsertId();
				
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/dashboard/menu/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('v') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/menu/frmregister'.$this->newUriParams);
		}
	}
	public function updateMenu($data)
	{
		try
		{
			$this->DB->update('wbs_menu'	, $data , Application_Model_Pubcon::get(1001).' and `id` ='.addslashes($this->params['id']) );
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/dashboard/menu/frmlist'.$this->newUriParams );

		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/menu/frmregister'.$this->newUriParams);
		}
	}
	public function getWbsUserGroups()
	{
		$sql		= "SELECT * FROM `user_groups` WHERE ".Application_Model_Pubcon::get(1110);
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	public function getUserGroups()
	{
		$this->params['user_group']	= '0';
		if(!is_array($this->params['ugroup']) || count($this->params['ugroup'])==0) return false;
		sort($this->params['ugroup']);
		$ugroup		= '/'. implode('/', $this->params['ugroup']).'/';
		$this->params['user_group']	= $ugroup;
		return true;
	}
}
