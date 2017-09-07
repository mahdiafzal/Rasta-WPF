<?php
 
class Dashboard_PageController extends Zend_Controller_Action 
{

	public function init() 
	{
		$this->registry	= Zend_registry::getInstance();
	}
    public function frmlistAction()
    {
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$this->view->assign('title_site', $translate->_('a')); 
		$this->view->assign('translate', $translate); 
		$st	= $this->getRequest()->getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st))){$start	= $st;}else{$start	= 0;}
		$limit	= 25;
		$sql	= 'select * from `wbs_pages` where '.Application_Model_Pubcon::get().' ORDER BY `local_id` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from wbs_pages where '.Application_Model_Pubcon::get());
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
    public function frmcrtAction()
    {
		$translate 		= $this->registry['translate'];
		$this->DB		= $this->registry['front_db'];

		$this->view->assign('title_site', $translate->_('a'));
		$this->view->assign('translate', $translate); 
		
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	, $message[0]);	}		
		if(!empty($message[1])){$this->view->assign('data'	, $message[1]); }
    }	
    public function crtAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$this->params	= $this->_getAllParams();
		$data['wb_page_title']	= $this->params['s_title'];
		$data['name']			= $this->params['s_name'];
		$data['skin_id']		= $this->params['s_skin'];
		$data['wb_page_slogan']	= $this->params['s_slogan'];
		$data['authors']		= $this->params['s_authors'];
		$data['description']	= $this->params['s_description'];
		$data['keywords']		= $this->params['s_keywords'];
		$data['wb_xml']			= $this->params['s_xml'];
		$data['wbs_id']			= WBSiD;
		
		$this->getUserGroups();
		$data['user_group']		= $this->params['user_group'];
		
		$state					= $this->params['ddown_page_state'];
		if ($state=='1'){$data['page_state']='1';}else if ($state=='0'){$data['page_state']='0';} else if ($state=='2'){$data['page_state']='2';} 
		else {$data['page_state']='1';}
		$sql1 		= "SELECT MAX(local_id)+1 as pagenum FROM `wbs_pages` WHERE `wbs_id` ='".WBSiD."'";
		$pagenum	= $DB->fetchone($sql1);
		
		$data['local_id']		= $pagenum;//new Zend_DB_expr('MAX(local_id)+1100');
		
		if (strlen(trim($data['wb_page_title']))==0)
		{
			$msg[]	= $translate->_('a'); 
			$this->_helper->FlashMessenger($msg);
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/dashboard/page/frmcrt#fragment-2');
		}
		$DB->insert('wbs_pages',$data);	
		$msg[]	= $translate->_('b'); 
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/page/frmlist#fragment-2');
    }		
    public function frmeditAction()
    {	
		$translate 		= $this->registry['translate'];
		$this->DB		= $this->registry['front_db'];
		
		$this->view->assign('title_site', $translate->_('a')); 
		$this->view->assign('translate', $translate); 
		
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);			
		if(!empty($message[1]))	$this->view->assign('data'	, $message[1]);			
		//if(empty($message[1]))
		else
		{
			$id	= $this->_getParam('id');
			if (empty($id)){$msg[]= $translate->_('u'); $this->_helper->FlashMessenger($msg);$this->_redirect('/dashboard/page/frmlist#fragment-2');}
			$sql	= "select * from `wbs_pages` where `wbs_id` ='".WBSiD."' and `local_id`=".addslashes($id);
			$result	= $this->DB->fetchAll($sql);
			if (count($result)==1)
			{
				$this->view->assign('data', $result[0]);
			}
			else
			{
				$msg[]= $translate->_('v'); 
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/dashboard/page/frmlist#fragment-2');
			}
		}
    }	
    public function editAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$this->params	= $this->_getAllParams();
		$data['wb_page_title']	= $this->params['s_title'];
		$data['name']			= $this->params['s_name'];
		$data['skin_id']		= $this->params['s_skin'];
		$data['wb_page_slogan']	= $this->params['s_slogan'];
		$data['authors']		= $this->params['s_authors'];
		$data['description']	= $this->params['s_description'];
		$data['keywords']		= $this->params['s_keywords'];
		$data['wb_xml']			= $this->params['s_xml'];
		$id						= $this->params['id'];
		
		$this->getUserGroups();
		$data['user_group']		= $this->params['user_group'];
		
		$state					= $this->params['ddown_page_state'];
		if ($state=='1'){$data['page_state']='1';}else if ($state=='0'){$data['page_state']='0';} else if ($state=='2'){$data['page_state']='2';} 
		else {$data['page_state']='1';}
		if (strlen(trim($data['wb_page_title']))==0)
		{
			$msg[]	= $translate->_('a');
			$this->_helper->FlashMessenger($msg);
			$data['local_id'] = $id;
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/dashboard/page/frmedit/id/'.$id.'#fragment-2');
		}
		$DB->update('wbs_pages',$data,'wbs_id="'.WBSiD.'" and local_id="'.$id.'"');	
		$msg[]	= $translate->_('b');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/page/frmlist#fragment-2');
    }		
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$id	= $this->_getParam('id');
		if($id < 13) 
		{
			$msg[]= $translate->_('a'); 
			$this->_helper->FlashMessenger($msg);$this->_redirect('/dashboard/page/frmlist#fragment-2');
		}
		$result	= $DB->delete('wbs_pages','wbs_id="'.WBSiD.'" and local_id="'.$id.'"');	
		if ($result)$msg[]= $translate->_('b');
		else		$msg[]= $translate->_('c');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/page/frmlist#fragment-2');
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
}
