<?php
 
class Skiner_GalleryController extends Zend_Controller_Action 
{
	public function init() 
	{
		$this->_helper->_layout->setLayout('dashboard');
	}
    public function indexAction()
    {
		$this->_redirect('/skiner/gallery/frmlist#fragment-4');
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
		$sql	= 'select * from `wbs_gallery_template` where `wbs_id` = '. WBSiD .' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		$count	= $this->DB->fetchAll('select count(*) as `cnt` from `wbs_gallery_template` where `wbs_id` = '. WBSiD);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		//$skins	= $this->getWbsGalleryTemplates();
		//foreach($skins as $sk)	$gskins[$sk['id']]	= $sk['title'];
		//$this->view->assign('gskins', $gskins);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
	public function frmregisterAction()
	{
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('a')); 
		//$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		//$this->view->assign('gallerySkins', $this->getWbsGalleryTemplates());

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/skiner/gallery/crt');
		
		$id	=$this->_getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getGalleryTempForEdit($id);
	}
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$id	= $this->_getParam('id');
		$result	= false;
		if (!empty($id) and preg_match('/^\d+$/', $id))
			$result	= $this->DB->delete('wbs_gallery_template','wbs_id="'.WBSiD.'" and `id`="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $this->translate->_('f');
		else			$msg[] = $this->translate->_('i');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/skiner/gallery/frmlist#fragment-4');
    }
    public function editAction()
    {
		$this->params		= $this->_getAllParams();

		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/gallery/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration();
		$this->updateGalleryTemp($data);
    }
    public function crtAction()
    {
		$this->params	= $this->_getAllParams();
		$data			= $this->prepareRegistration();
		$this->insertNewGalleryTemp($data);
    }
	
	
/// Helper Method for Actions -------------------------------------------------------------------*********
    public function getGalleryTempForEdit($id)
    {	
		$this->view->assign('form_action', '/skiner/gallery/edit/id/'.$id);

		$sql	= "SELECT * FROM `wbs_gallery_template` WHERE `wbs_id` ='".WBSiD."' AND `id`='".addslashes($id)."'";
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			$this->view->assign('data', $this->arrayParams($result[0]) );
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/skiner/gallery/frmlist#fragment-4');
		}
    }	

	public function prepareRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$this->setUriParams();
		//$this->getUserGroups();
		$this->validate();
		
		$data	= $this->arrayDbData();
		return $data;
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';
		if ( preg_match('/^\d+$/', $this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-4';
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'title'		=> $this->params['title'],
						'fblock'	=> $this->params['fblock']
			 		 );
		
		$rule=array	(
						'title'		=>'notNull',
						'fblock'		=>'notNull'
					);

		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('title')	== false) $error[]	= $this->translate->_('u'); 
		if($frmValidator->getResult('fblock')	== false) $error[]	= $this->translate->_('s'); 
		
		$sysError	= ''; 
		if($this->params['gid']!=$this->params['id'])										$sysError	= $this->translate->_('h');
			if(!empty($this->params['gid']) and !is_numeric($this->params['gid']) )  		$sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;


		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/gallery/frmregister'.$this->newUriParams);
			return false;
		}
		return true;
	}
	public function arrayParams($result)
	{
		$data	= array();
		$data['gid']		= $result['id'];
		$data['title']		= $result['title'];
		$data['hfiles']		= $result['files'];
		$data['fblock']		= $result['block_fix'];
		$data['rblock']		= $result['block_rep'];
		$data['max']		= $result['rep_max'];
		$data['jsoptions']	= $result['options'];
		return $data;
	}
	public function arrayDbData()
	{
		$data['title']		= $this->params['title'];
		$data['files']		= $this->params['hfiles'];
		$data['block_fix']	= $this->params['fblock'];
		$data['block_rep']	= $this->params['rblock'];
		$data['rep_max']	= (!preg_match('/^\d+$/', $this->params['max']))?'0':$this->params['max'];
		$data['options']	= $this->params['jsoptions'];
		$data['title']		= $this->params['title'];
		return $data;
	}
	public function insertNewGalleryTemp($data)
	{
		try
		{
			$data['wbs_id']		= WBSiD;
			$this->DB->insert('wbs_gallery_template', $data);

			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/skiner/gallery/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('v') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/gallery/frmregister'.$this->newUriParams);
		}
	}
	public function updateGalleryTemp($data)
	{
		try
		{
			$this->DB->update('wbs_gallery_template', $data, '`wbs_id` = '.WBSiD.' and `id` ='.addslashes($this->params['id']) );

			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/skiner/gallery/frmlist'.$this->newUriParams );

		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/gallery/frmregister'.$this->newUriParams);
		}
	}
}
