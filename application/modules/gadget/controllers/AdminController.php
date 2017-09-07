<?php
 
class Gadget_AdminController extends Zend_Controller_Action 
{
	public function init() 
	{ 
		$this->_helper->_layout->setLayout('dashboard');
	}
	public function indexAction() 
    {
		$this->_redirect('/gadget/admin/frmlist/env/dsh#fragment-3');
    }
    public function frmlistAction()
    {
    	$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('b')); 

		$st	= $this->_getParam('st');
		if ((!empty($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 25;
		$sql	= 'SELECT `id`,`wbs_id`,`title` FROM `wbs_gadget` WHERE `id` IN (SELECT `gad_id` FROM `wbs_gadget_options` WHERE `wbs_id` = '. WBSiD .') OR `wbs_id` ='. WBSiD .' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		$count	= $this->DB->fetchAll('SELECT COUNT(`gad_id`) as cnt FROM `wbs_gadget_options` WHERE `wbs_id` = '. WBSiD );
		
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

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/gadget/admin/crt');
		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getGadForEdit($id);
	}
	public function frmconfigAction()
	{
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('d')); 
		$this->view->assign('WbsPages', $this->getWbsPages()); 
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id))
		{
			$this->getGadForConfig($id);
			$this->view->assign('form_action', '/gadget/admin/configure/id/'.$id);
			return;
		}
		
		$this->_helper->FlashMessenger(array($this->translate->_('h')));
		$this->_redirect('/gadget/admin/frmlist#fragment-3');
	}
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$id	= $this->getRequest()->getParam	('id');
		$this->DB->delete('wbs_gadget','wbs_id="'.WBSiD.'" and id="'. addslashes($id) .'"');	
		$result	= $this->DB->delete('wbs_gadget_options','wbs_id="'.WBSiD.'" and gad_id="'. addslashes($id) .'"');	
		$this->DB->delete('wbs_gadget_data','wbs_id="'.WBSiD.'" and gad_id="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $this->translate->_('af');
		else			$msg[] = $this->translate->_('ag');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/gadget/admin/frmlist#fragment-3');
    }
    public function editAction()
    {
		$this->params		= $this->_getAllParams();

		if(!is_numeric($this->params['id']))
		{
			$this->setUriParams();
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/gadget/admin/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration('main');
		$this->updateGadget($data);
    }
    public function crtAction()
    {
		$this->params	= $this->_getAllParams();
		$data			= $this->prepareRegistration('main');
		$this->insertNewGadget($data);
    }
    public function configureAction()
    {
		$this->params	= $this->_getAllParams();
		$this->getUserGroups();
		$data			= $this->prepareRegistration('options');
		try
		{
			$sql	= "SELECT COUNT(`gad_id`) FROM `wbs_gadget_options` WHERE `wbs_id` =".WBSiD." AND `gad_id`='".addslashes($this->params['id'])."'";
			$result	= $this->DB->fetchOne($sql);
			//print_r($data); die();
			if($result==0)
			{
				$data['wbs_id']		= WBSiD;
				$result	= $this->DB->insert('wbs_gadget_options', $data);
				if($result)
					$this->_helper->flashMessenger->addMessage(array( $this->translate->_('ab') ));
				else
					$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			}
			elseif($result==1)
			{
				$this->DB->update('wbs_gadget_options', $data ,'`wbs_id` = '.WBSiD.' and `gad_id` ='.addslashes($this->params['id']) );
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('ab') ));
			}
			else
			{
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('ac') ));
			}

			$this->_redirect('/gadget/admin/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/gadget/admin/frmconfig'.$this->newUriParams);
		}
    }
    public function publicgadlistAction()
    {
    	$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('aq')); 

		$st	= $this->_getParam('st');
		if ((!empty($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 25;
		$sql	= 'SELECT `id`,`wbs_id`,`title` FROM `wbs_gadget` WHERE `id` NOT IN (SELECT `gad_id` FROM `wbs_gadget_options` WHERE `wbs_id` = '. WBSiD .') AND `wbs_id` =0 ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		 
		$count	= $this->DB->fetchAll('SELECT COUNT(`id`) as cnt FROM `wbs_gadget` WHERE `id` NOT IN (SELECT `gad_id` FROM `wbs_gadget_options` WHERE `wbs_id` = '. WBSiD .') AND `wbs_id` =0' );
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }

/// Helper Method for Actions -------------------------------------------------------------------*********
    public function getGadForConfig($id)
    {	
		$sql	= "SELECT `wbs_gadget`.`title`, `wbs_gadget`.`id`, `wbs_gadget_options`.*"
				. " FROM `wbs_gadget` LEFT JOIN `wbs_gadget_options` ON `wbs_gadget`.`id`=`wbs_gadget_options`.`gad_id`"
				. " WHERE `wbs_gadget`.`wbs_id` IN (".WBSiD.",0) AND `wbs_gadget`.`id`='".addslashes($id)."'";
		$result	= $this->DB->fetchAll($sql);
			
		if (is_array($result) and count($result)==1)
		{
			$data	= $this->arrayParams($result[0], 'config');
			$this->view->assign('data', $data);
		}
		else
		{
			$msg[]= $this->translate->_('h');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/gadget/admin/frmlist#fragment-3');
		}
    }	
    public function getGadForEdit($id)
    {	
		$this->view->assign('form_action', '/gadget/admin/edit/id/'.$id);

		$sql	= "SELECT * FROM `wbs_gadget` WHERE `wbs_id` ='".WBSiD."' AND `id`='".addslashes($id)."'";
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			$data	= $this->arrayParams($result[0], 'edit');
			$this->view->assign('data', $data);
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/gadget/admin/frmlist#fragment-3');
		}
    }	
	public function getWbsPages()
	{
		$sql		= "SELECT `local_id` as `id`,`wb_page_title` as `title` FROM `wbs_pages` WHERE `wbs_id`='".WBSiD."'";
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	public function prepareRegistration($target)
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$this->setUriParams();
		$this->validate($target);
		
		$data	= $this->arrayDbData($target);
		return $data;
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';
		if ( preg_match('/^\d+$/', $this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-3';
	}
	public function validate($target)
	{
		if($target=='main')
		{
			$frmValidator	= new Application_Model_Validator;
			$data = array(	
							'title'		=> $this->params['title'],
							'script'	=> $this->params['script'],
							'listskin'	=> $this->params['listskin']
						 );
			
			$rule=array	(
							'title'		=>'notNull',
							'script'	=>'notNull',
							'listskin'	=>'notNull'
						);
			$frmValidator->validate($data,$rule);
			$error	= array();
			if($frmValidator->getResult('title')	== false) $error[]	= $this->translate->_('q'); 
			if($frmValidator->getResult('script')	== false) $error[]	= $this->translate->_('p');
			if($frmValidator->getResult('listskin')	== false) $error[]	= $this->translate->_('o');
			$redPath	= '/gadget/admin/frmregister'.$this->newUriParams;
		}
		elseif($target=='options')
		{
			$redPath	= '/gadget/admin/frmconfig'.$this->newUriParams;
		}
		$sysError	= ''; 
		if($this->params['gid']!=$this->params['id'])										$sysError	= $this->translate->_('h');
			if(!empty($this->params['gid']) and !is_numeric($this->params['gid']) )  		$sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect($redPath);
			return false;
		}
		return true;
	}


	public function arrayParams($result, $target)
	{
		$data	= array();
		if($target=='edit')
		{
			$data['gid']	= $result['id'];
			$data['title']	= $result['title'];
			$data['lang']	= $result['lang'];
			$data['scdata']	= ($result['scen_data']=='1')?'on':'';
			$data['ugdata']	= ($result['ugroup_data']=='1')?'on':'';
			$data['script']	= $result['text'];
			$data['listskin']= $result['list_skin'];
		}
		elseif($target=='config')
		{
			$data['gid']	= $result['id'];
			$data['title']	= $result['title'];
			$data['page']	= $result['page_id'];
			$data['lock']	= ($result['rtc_lock']=='1')?'on':'';
			$data['ugroup']	= $result['user_group'];
		}
		return $data;
	}
	public function arrayDbData($target)
	{
		if($target=='main')
		{
			//$data['id']			= $result['gid'];
			$data['title']		= $this->params['title'];
			$data['lang']		= 'fa';
			$data['scen_data']	= ($this->params['scdata']=='on')?'1':'0';
			$data['ugroup_data']= ($this->params['ugdata']=='on')?'1':'0';
			$data['text']		= $this->params['script'];
			$data['list_skin']	= $this->params['listskin'];
		}
		elseif($target=='options')
		{
			$data['gad_id']		= $this->params['gid'];
			//$data['title']		= $this->params['title'];
			$data['page_id']	= $this->params['page'];
			$data['rtc_lock']	= ($this->params['lock']=='on')?'1':'0';
			$data['user_group']	= $this->params['ugroup'];
		}
		return $data;
	}


	public function insertNewGadget($data)
	{
		try
		{
			$data['wbs_id']		= WBSiD;
			$result	= $this->DB->insert('wbs_gadget', $data);
			if($result)
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('n') ));
			else
				$this->_helper->flashMessenger->addMessage(array( $this->translate->_('m') ));
			$this->_redirect('/gadget/admin/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('m') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/gadget/admin/frmregister'.$this->newUriParams);
		}
	}
	public function updateGadget($data)
	{
		try
		{
			$this->DB->update('wbs_gadget', $data ,'`wbs_id` = '.WBSiD.' and `id` ='.addslashes($this->params['id']) );
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('l') ));
			$this->_redirect('/gadget/admin/frmlist'.$this->newUriParams );

		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('k') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/gadget/admin/frmregister'.$this->newUriParams);
		}
	}
	public function getWbsUserGroups()
	{
		$sql		= "SELECT `id`,`title` FROM `user_groups` WHERE `wbs_id`='".WBSiD."'";
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	public function getUserGroups()
	{
		$this->params['ugroup']	= '0';
		if(!is_array($this->params['usrgroup']) || count($this->params['usrgroup'])==0) return false;
		sort($this->params['usrgroup']);
		$ugroup		= '/'. implode('/', $this->params['usrgroup']).'/';
		$this->params['ugroup']	= $ugroup;
		return true;
	}
}
