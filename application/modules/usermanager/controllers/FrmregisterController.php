<?php

class Usermanager_FrmregisterController extends Zend_Controller_Action
{

    public function indexAction()
    {
		$this->params		= $this->getRequest()->getParams();
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		$flashMsg			= $this->_helper->flashMessenger->getMessages();
		$this->newUriParams	= $this->setUriParams();

		$this->view->assign('translate'		, $this->translate ); 	
		$this->view->assign('title_site'	, $this->translate->_('a') );
		//$this->view->assign('title'	, $this->translate->_('a') );
		$this->view->assign('newUriParams', $this->newUriParams);
		$this->view->assign('formAction', '/usermanager/register/crt'.$this->newUriParams);
		$this->view->assign('userGroups', $this->getUserGroups());
		
		if( !empty( $flashMsg[0]) ) $this->view->assign('errormsg'	, $flashMsg[0]);
		if( !empty( $flashMsg[1]) ) 
		{
			$this->view->assign('userParams'	, $flashMsg[1]);
			return true;
		}
		if ( @preg_match('/^\d+$/', $this->params['id']) ) $this->getUserforedit($this->params['id']);

    }
	public function getUserGroups()
	{
		$sql		= "SELECT * FROM `user_groups` WHERE `wbs_id`='".WBSiD."'";
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	public function getUserforedit($id)
	{
		$sql1		= 'select * from `users` where `wb_user_id` = '.WBSiD.'  AND id ='.addslashes($id).' AND `is_admin`=0';
		$result1	= $this->DB->fetchAll($sql1);
		if(count($result1)!=1) $error[]	= $this->translate->_('c');
		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_redirect('/usermanager/frmlist/index'.$this->newUriParams);
			return false;
		}
			
		$userParams['id']			= $result1[0]['id'];
		$userParams['crt_date']		= $result1[0]['crt_date'];
		$userParams['f_name']		= $result1[0]['first_name'];
		$userParams['l_name']		= $result1[0]['last_name'];
		$userParams['u_name']		= $result1[0]['username'];
		$userParams['u_status']		= $result1[0]['is_active'];
		$userParams['u_group']		= $result1[0]['user_group'];

		$this->view->assign('title_site'	, $this->translate->_('b') );
		$this->view->assign('formAction', '/usermanager/register/edit'.$this->newUriParams);
		$this->view->assign('userParams'	, $userParams);
	}
	public function setUriParams()
	{
		$newUriParams =	'';
		if (@preg_match('/^\d+$/', $this->params['id']) )
			$newUriParams .=	'/id/'.$this->params['id'];
		if (@$this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$newUriParams .=	'/env/dsh#fragment-1';
		}
		
		return $newUriParams;
	}
}

?>