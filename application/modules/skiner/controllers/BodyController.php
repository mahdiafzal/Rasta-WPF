<?php
 
class Skiner_BodyController extends Zend_Controller_Action 
{
   public function init() 
    {
		$this->_helper->_layout->setLayout('dashboard');
    }
    public function indexAction()
    {
		$this->_redirect('/skiner/body/frmlist#fragment-4');
    }
    public function frmlistAction()
    {
    	$DB			= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('a')); 

		$st	= $this->getRequest()->getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 25;
		$pubcon	= Application_Model_Pubcon::get(1000);
		$sql	= 'select * from `wbs_skin_body` where '. $pubcon .' ORDER BY `body_id` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from `wbs_skin_body` where '. $pubcon);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
	public function frmregisterAction()
	{
		$this->translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('g')); 

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/skiner/body/crt');
		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getBodyForEdit($id);
	
	}
    public function editAction()
    {
		$this->params		= $this->getRequest()->getParams();

		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/body/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration();
		$this->updateBody($data);
    }		
    public function crtAction()
    {
		$this->params		= $this->getRequest()->getParams();
		$data	= $this->prepareRegistration();
		$this->insertNewSkin($data);
    }		
	public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');

		$id	= $this->getRequest()->getParam('id');
		if(!is_numeric($id)) return false;
		$result	= $this->DB->delete('wbs_skin_body', Application_Model_Pubcon::get(1000).' and `body_id`="'.$id.'"');	
		if ($result)	$msg[] = $this->translate->_('ac');
		else			$msg[] = $this->translate->_('ad');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/skiner/body/frmlist#fragment-4');
    }		


/// Helper Method for Actions -------------------------------------------------------------------*********
    public function getBodyForEdit($id)
    {	
    	$DB			= Zend_Registry::get('front_db');
		$this->view->assign('form_action', '/skiner/body/edit/id/'.$id);
		$sql	= "select * from `wbs_skin_body` where ". Application_Model_Pubcon::get(1000). " and `body_id`='".$id."'";
		$result	= $DB->fetchAll($sql);
		if (count($result)==1)
		{
			$this->view->assign('data', $this->arrayParams($result[0]) );
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/skiner/body/frmlist#fragment-4');
		}
    }	
	public function insertNewSkin($data)
	{
		try
		{
			$data['wbs_id']	= WBSiD;
			$this->DB->insert('wbs_skin_body', $data);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/skiner/body/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/body/frmregister'.$this->newUriParams);
		}
	}
	public function updateBody($data)
	{
		try
		{
			$this->DB->update('wbs_skin_body'	, $data , Application_Model_Pubcon::get(1000).' and `body_id` ='.addslashes($this->params['bid']));
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/skiner/body/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/body/frmregister'.$this->newUriParams);
		}
	}
	public function arrayParams($result)
	{
		$data	= array();
		$data['bid']		= $result['body_id'];
		$data['title']		= $result['body_title'];
		$data['body']		= $result['body'];
		return $data;
	}
	public function prepareRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$this->setUriParams();
		$this->validate();
		
		$data				= $this->arrayDbData();
		return $data;
	}
	public function arrayDbData()
	{
		//$data['body_id']	= $this->params['bid'];
		$data['body_title']	= $this->params['title'];
		$data['body']		= $this->params['body'];
		return $data;
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'title'		=> $this->params['title'],
						'body'		=> $this->params['body']
			 		 );
		$rule=array	(
						'title'		=>'notNull',
						'body'		=>'notNull'
					);

		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('title')	== false) $error[]	= $this->translate->_('u'); 
		if($frmValidator->getResult('body')		== false) $error[]	= $this->translate->_('w'); 
		
		$sysError	= ''; 
		if($this->params['bid']!=$this->params['id'])							$sysError	= $this->translate->_('h');
		if(!empty($this->params['bid']) and !is_numeric($this->params['bid']) ) $sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/body/frmregister'.$this->newUriParams);
			return false;
		}
		return true;
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';
		if ( preg_match('/^\d+$/', $this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-4';
	}

}
