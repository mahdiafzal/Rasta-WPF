<?php
 
class Dashboard_GalleryController extends Zend_Controller_Action 
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
		$sql	= 'select * from `wbs_gallery` where '.Application_Model_Pubcon::get().' ORDER BY `gallery_id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		$count	= $this->DB->fetchAll('select count(*) as `cnt` from `wbs_gallery` where '.Application_Model_Pubcon::get());
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$skins	= $this->getWbsGalleryTemplates();
		foreach($skins as $sk)	$gskins[$sk['id']]	= $sk['title'];
		$this->view->assign('gskins', $gskins);
		
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
		$this->view->assign('gallerySkins', $this->getWbsGalleryTemplates());

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/dashboard/gallery/crt');
		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getGalleryForEdit($id);
	}
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$id	= $this->getRequest()->getParam	('id');
		$result	= $this->DB->delete('wbs_gallery','wbs_id="'.WBSiD.'" and `gallery_id`="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $this->translate->_('f');
		else			$msg[] = $this->translate->_('i');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/gallery/frmlist#fragment-2');
    }
    public function editAction()
    {
		$this->params		= $this->_getAllParams();

		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/gallery/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration();
		$this->updateGallery($data);
    }
    public function crtAction()
    {
		$this->params	= $this->_getAllParams();
		$data			= $this->prepareRegistration();
		$this->insertNewGallery($data);
    }
	
	
/// Helper Method for Actions -------------------------------------------------------------------*********
    public function getGalleryForEdit($id)
    {	
		$this->view->assign('form_action', '/dashboard/gallery/edit/id/'.$id);

		$sql	= "SELECT * FROM `wbs_gallery` WHERE `wbs_id` ='".WBSiD."' AND `gallery_id`='".$id."'";
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			$this->view->assign('data', $this->arrayParams($result[0]) );
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dashboard/gallery/frmlist#fragment-2');
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
		if($this->params['gid']!=$this->params['id'])										$sysError	= $this->translate->_('h');
			if(!empty($this->params['gid']) and !is_numeric($this->params['gid']) )  		$sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;


		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/gallery/frmregister'.$this->newUriParams);
			return false;
		}
		return true;
	}
	public function arrayParams($result)
	{
		$data	= array();
		$data['gid']		= $result['gallery_id'];
		$data['status']		= $result['status'];
		$data['title']		= $result['gallery_title'];
		$data['skin']		= $result['tem_id'];
		$data['user_group']	= $result['user_group'];
		$data['jsoptions']	= $result['options'];
		return $data;
	}
	public function arrayDbData()
	{
		$data['gallery_title']	= $this->params['title'];
		$data['status']			= ($this->params['status']=='1')?'1':'0';
		$data['tem_id']	= (is_numeric($this->params['skin']))?$this->params['skin']:'1';
		$data['user_group']	= $this->params['user_group'];
		$data['options']	= $this->params['jsoptions'];
		return $data;
	}
	public function insertNewGallery($data)
	{
		try
		{
			$data['wbs_id']		= WBSiD;
			$this->DB->insert('wbs_gallery', $data);

			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/dashboard/gallery/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('v') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/gallery/frmregister'.$this->newUriParams);
		}
	}
	public function updateGallery($data)
	{
		try
		{
			$this->DB->update('wbs_gallery'	, $data ,'`wbs_id` = '.WBSiD.' and `gallery_id` ='.addslashes($this->params['id']) );
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/dashboard/gallery/frmlist'.$this->newUriParams );

		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/dashboard/gallery/frmregister'.$this->newUriParams);
		}
	}
	protected function getWbsUserGroups()
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
	public function getWbsGalleryTemplates()
	{
		$sql		= "SELECT `id`, `wbs_id`, `title`, `options` FROM `wbs_gallery_template` WHERE `wbs_id` IN (".WBSiD.", 0)";
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
}
