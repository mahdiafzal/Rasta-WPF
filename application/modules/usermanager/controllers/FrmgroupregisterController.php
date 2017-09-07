<?php

class Usermanager_FrmgroupregisterController extends Zend_Controller_Action
{

    public function indexAction()
    {
		$this->params		= $this->getRequest()->getParams();
    	$this->translate	= Zend_registry::get('translate');
		$flashMsg			= $this->_helper->flashMessenger->getMessages();
		$this->newUriParams	= $this->setUriParams();

		$this->view->assign('translate'		, $this->translate ); 	
		$this->view->assign('title_site'	, $this->translate->_('a') ); 
		$this->view->assign('newUriParams', $this->newUriParams);
		$this->view->assign('formAction', '/usermanager/groupregister/crt'.$this->newUriParams);
		
		$id	= (is_numeric($this->params['id']))?$this->params['id']:false;
		$this->view->assign('grp_subs',$this->getSiteUsergroups($id));


		if( !empty( $flashMsg[0]) ) $this->view->assign('errormsg'	, $flashMsg[0]);
		if( !empty( $flashMsg[1]) ) 
		{
			$this->view->assign('data'	, $flashMsg[1]);
			return true;
		}
		if( is_numeric($this->params['id']) ) $this->getUsergroupforedit($this->params['id']);

    }
//// Helper Methods
	public function getUsergroupforedit($id)
	{
    	$this->DB			= Zend_registry::get('front_db');
		$sql1		= 'select * from `user_groups` where `wbs_id` = '.WBSiD.'  and id ='.addslashes($id);
		$result1	= $this->DB->fetchAll($sql1);
		if(count($result1)!=1) $error[]	= $this->translate->_('c');
		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_redirect('/usermanager/frmgrouplist/index'.$this->newUriParams);
			return false;
		}
			
		$groupParams['g_title']			= $result1[0]['title'];
		$groupParams['g_permission']	= $result1[0]['permissions'];
		$groupParams['first_subs']	= $result1[0]['first_subs'];

		$this->view->assign('title_site'	, $this->translate->_('b') );
		$this->view->assign('formAction', '/usermanager/groupregister/edit'.$this->newUriParams);
		$this->view->assign('data'	, $groupParams);
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
	protected function	getSiteUsergroups($except=false)
	{
    	if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
		$wherest	= '';
		if($except)	$wherest	= ' AND `id`!='.$except;
		$sql			= "SELECT id, title  FROM `user_groups` WHERE ".Application_Model_Pubcon::get(1110).$wherest." ORDER BY `id` DESC;";
		$result			= $this->DB->fetchAll($sql);
		return 	$result;
	}



}

?>