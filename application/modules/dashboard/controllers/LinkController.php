<?php
 
class Dashboard_LinkController extends Zend_Controller_Action 
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
		$st	= $this->_getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st))){$start	= $st;}else{$start	= 0;}
		$limit	= 25;
		$sql	= 'select * from `wbs_links` where '.Application_Model_Pubcon::get().' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from wbs_links where '.Application_Model_Pubcon::get());
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		$message = $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	, $message[0]);	}
    }
    public function frmcrtAction()
    {
    	$this->DB		= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$this->view->assign('title_site', $translate->_('a'));
		$this->view->assign('translate', $translate); 
		
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	, $message[0]);}			
		if(!empty($message[1])){$this->view->assign('data'	, $message[1]);}
    }	
    public function crtAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		
		$this->params	= $this->_getAllParams();
		//$request		= $this->getRequest();
		$data['title']	= $this->params['title'];
		$data['url']	= (empty($this->params['chk_seprator']))?$this->params['url']:'#';
//		if ($request->getPost('chk_seprator'))		$data['url']='#';
//		else										$data['url']=$request->getParam('url');
		$data['wbs_id']	= WBSiD;
		
		$this->getUserGroups();
		$data['user_group']		= $this->params['user_group'];
		
		if (strlen(trim($data['title']))==0)
		{
			$msg[]= $translate->_('a');
			$this->_helper->FlashMessenger($msg);
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/dashboard/link/frmcrt#fragment-2');
		}
		$DB->insert('wbs_links',$data);	
		$msg[]= $translate->_('b');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/link/frmlist#fragment-2');
    }		
    public function frmeditAction()
    {	
    	$this->DB		= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$this->view->assign('title_site', $translate->_('a'));
		$this->view->assign('translate', $translate); 
		
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		
		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if (empty($message[1]))
		{
			$id	=$this->getRequest()->getParam	('id');
			if (empty($id))
			{
				$msg[]= $translate->_('b'); 
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/dashboard/link/frmlist#fragment-2');
			}
			$sql	= "select * from `wbs_links` where `wbs_id` ='".WBSiD."' and `id`='".$id."'";
			$result	= $this->DB->fetchAll($sql);
			if (count($result)==1)
			{
				$this->view->assign('data', $result[0]);
			}
			else
			{
				$msg[]= $translate->_('c');
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/dashboard/link/frmlist#fragment-2');
			}
		}
    }	
    public function editAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		
		$this->params	= $this->_getAllParams();
		//$request		= $this->getRequest();
		$data['title']	= $this->params['title'];
		$data['url']	= (empty($this->params['chk_seprator']))?$this->params['url']:'#';
//		if ($request->getPost('chk_seprator')){$data['url']='#';}else{$data['url']=$request->getParam('url');}
		
		$this->getUserGroups();
		$data['user_group']		= $this->params['user_group'];
		
		$id				= $this->params['id'];
		if (!preg_match('/^[0-9]+$/',$id))
		{
			$msg[]= $translate->_('a');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dashboard/link/frmlist#fragment-2');
		}
		if (strlen(trim($data['title']))==0)
		{
			$msg[]= $translate->_('b');
			$this->_helper->FlashMessenger($msg);
			$data['id'] = $id;
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/dashboard/link/frmedit/id/'.$id.'#fragment-2');
		}
		$DB->update('wbs_links',$data,'wbs_id="'.WBSiD.'" and id="'.$id.'"');	
		$msg[]= $translate->_('c');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/link/frmlist#fragment-2');
    }		
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$id	= $this->_getParam('id');
		$result	= $DB->delete('wbs_links','wbs_id="'.WBSiD.'" and id="'.$id.'"');	
		if ($result)	$msg[] = $translate->_('a');
		else			$msg[] = $translate->_('b');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/link/frmlist#fragment-2');
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
