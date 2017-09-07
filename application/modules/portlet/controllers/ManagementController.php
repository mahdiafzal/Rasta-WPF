<?php


class Portlet_ManagementController extends Zend_Controller_Action 
{
   public function init() 
    {
		$this->_helper->_layout->setLayout('dashboard');
    }
    public function indexAction()
    {

    }
	
    public function frmlistAction()
    {
    	$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('portlets list')); 

		$st	= $this->_getParam('st');
		$start	= ( is_numeric($st) )?$st:0;
		$limit	= 20;
		$result	= $this->_fetchWbsPortletss($start, $limit);
	
		$this->view->assign('data'	, $result[0]);
		$this->view->assign('count'	, $result[1]);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
	
	public function frmportletAction()
	{
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('portlet script')); 
		
		
		$this->view->assign('wbs_layouts', $this->getWbsLayouts()); 
		//$this->view->assign('wbs_blocks', $this->getWbsBlocks()); 
		

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/portlet/management/crtportlet');
		
		$id	=$this->_getParam('id');
		if (is_numeric($id)) $this->getPortletForEdit($id);
	}
    public function crtportletAction()
    {
		$this->params	= $this->_getAllParams();
		$this->setUriParams();
		
		$data			= $this->preparePortletRegistration();
		try
		{
			$data['wbs_id']	= WBSiD;
			$this->DB->insert('wbs_portlets', $data);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/portlet/management/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmportlet'.$this->newUriParams);
		}
    }
    public function editportletAction()
    {
		$this->params	= $this->_getAllParams();
		$this->setUriParams();
		$data	= $this->preparePortletRegistration();

		if(!is_numeric($this->params['id']) or $this->params['id']!=$this->params['pid'])
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmportlet'.$this->newUriParams);
		}
		
		try
		{
			$this->DB->update('wbs_portlets', $data ,'`wbs_id` = '.WBSiD.' and `pr_id` ='.addslashes($this->params['pid']) );
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/portlet/management/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmportlet'.$this->newUriParams);
		}
    }	
	public function delportletAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$this->DB			= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');

		$id	= $this->_getParam('id');
		if(!is_numeric($id))
		{
			$this->_helper->FlashMessenger(array($translate->_('h')));
			$this->_redirect('/portlet/management/frmlist#fragment-3');
		}
		if(!$this->hasChildren($id, 'pr'))
		{
			$result	= $this->DB->delete('wbs_portlets'	,'wbs_id="'.WBSiD.'" and `pr_id`="'.addslashes($id).'"');	
			if ($result)	$msg[] = $translate->_('ac');
			else			$msg[] = $translate->_('ad');
		}
		else
			$msg[] = $translate->_('ae');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/portlet/management/frmlist#fragment-3');
    }
		
	public function frmcontrollerAction()
	{
		$params	= $this->_getAllParams();
		$this->translate	= Zend_Registry::get('translate');
		if( !is_numeric($params['prid']) )
		{
			$msg[0]= $this->translate->_('h');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/portlet/management/frmlist#fragment-3');
		}
		
    	$this->DB			= Zend_Registry::get('front_db');
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('portlet controller')); 
		
		
		$this->view->assign('wbs_layouts', $this->getWbsLayouts()); 
		
		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;
		
		if( !isset($params['id']) )
		{
			if( !$this->isExistPortlet($params['prid']) )
			{
				$msg[0]= $this->translate->_('h');
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/portlet/management/frmlist#fragment-3');
			}
			else
			{
				$this->view->assign('data'	, array('pid'=>$params['prid']) );
				$this->view->assign('form_action', '/portlet/management/crtcontroller');
			}
		}
		else
		{
			$this->getControllerForEdit($params['id'], $params['prid']);
			$this->view->assign('form2_action', '/portlet/management/crtaction/crid/'.$params['id']);
		}
	}
    public function crtcontrollerAction()
    {
		$this->params	= $this->_getAllParams();

		$this->setUriParams();
		
		$data			= $this->prepareControllerRegistration();
		if( !is_numeric($this->params['pid']) )
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmcontroller'.$this->newUriParams);
		}
		try
		{
			$data['wbs_id']	= WBSiD;
			$data['cr_pr_id']	= addslashes($this->params['pid']);
			$this->DB->insert('wbs_portlet_controllers', $data);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('o') ));
			$this->_redirect('/portlet/management/frmlist' );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('p') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmcontroller'.$this->newUriParams);
		}
    }
    public function editcontrollerAction()
    {
		$this->params	= $this->_getAllParams();
		$this->setUriParams();

		$data	= $this->prepareControllerRegistration();
		if(!is_numeric($this->params['id']) or $this->params['id']!=$this->params['cid'] or !is_numeric($this->params['pid']) )
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmcontroller'.$this->newUriParams);
		}
		try
		{
			$where	= '`wbs_id` = '.WBSiD.' AND `cr_id` ='.addslashes($this->params['cid']).' AND `cr_pr_id`='.addslashes($this->params['pid']);
			$this->DB->update('wbs_portlet_controllers', $data , $where);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('q') ));
			$this->_redirect('/portlet/management/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('r') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmcontroller'.$this->newUriParams);
		}
    }	
	public function delcontrollerAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');

		$params	= $this->_getAllParams();
		
		if(!is_numeric($params['id']) or !is_numeric($params['prid']) )
		{
			$this->_helper->FlashMessenger(array($translate->_('h')));
			$this->_redirect('/portlet/management/frmlist#fragment-3');
		}
		if(!$this->hasChildren($params['id'], 'cr'))
		{
			$result	= $this->DB->delete('wbs_portlet_controllers','wbs_id='.WBSiD.' and `cr_id`='.addslashes($params['id']).' AND `cr_pr_id`='.addslashes($params['prid']));	
			if ($result)	$msg[] = $translate->_('s');
			else			$msg[] = $translate->_('t');
		}
		else
			$msg[] = $translate->_('u');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/portlet/management/frmlist#fragment-3');
    }
	
	
	
	public function getactiondataAction()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
		$acid	= $this->_getParam('acid');
		$crid	= $this->_getParam('crid');
    	$this->DB	= Zend_registry::get('front_db');
		if( $data = $this->getactionForEdit($acid, $crid) )
			$this->_helper->json->sendJson(array('state'=>'true', 'data'=>$data ) );
		else
		{
			$translate	= Zend_registry::get('translate');
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$translate->_('h') ) );
		}
	}
	public function crtactionAction()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
		$this->params	= $this->_getAllParams();
		$data			= $this->prepareactionRegistration();
		if( !$this->isExistController($this->params['cid'], $this->params['pid']) )
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$this->translate->_('h') ) );

		try
		{
			$data['wbs_id']		= WBSiD;
			$data['ac_cr_id']	= addslashes($this->params['cid']);
			$this->DB->insert('wbs_portlet_actions', $data);
			if( !$acid = $this->DB->lastInsertId() )	$acid = 0;
			$this->_helper->json->sendJson(array('state'=>'true', 'msg'=>$this->translate->_('f'), 'aid'=>$acid ) );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->json->sendJson(array('state'=>'true', 'msg'=>$this->translate->_('e') ) );
		}
    }
    public function editactionAction()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
		$this->params	= $this->_getAllParams();
		$data			= $this->prepareactionRegistration();
		if( !is_numeric($this->params['aid']) or !$this->isExistController($this->params['cid'], $this->params['pid']) )
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$this->translate->_('h') ) );

		try
		{
			$where	= '`wbs_id` = '.WBSiD.' AND `ac_id` ='.addslashes($this->params['aid']).' AND `ac_cr_id`='.addslashes($this->params['cid']);
			$this->DB->update('wbs_portlet_actions', $data , $where);
			$this->_helper->json->sendJson(array('state'=>'true', 'msg'=>$this->translate->_('d'), 'aid'=>$this->params['aid'] ) );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->json->sendJson(array('state'=>'true', 'msg'=>$this->translate->_('c') ) );
		}
    }	
	public function delactionAction()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
    	$this->DB	= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');

		$params	= $this->_getAllParams();
		
		if(!is_numeric($params['acid']) or !is_numeric($params['crid']) )
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$translate->_('h') ) );
		$result	= $this->DB->delete('wbs_portlet_actions','wbs_id='.WBSiD.' and `ac_id`='.addslashes($params['acid']).' AND `ac_cr_id`='.addslashes($params['crid']));	
		if ($result)
			$this->_helper->json->sendJson(array('state'=>'true', 'msg'=>$translate->_('al'), 'aid'=>$params['acid'] ) );
		else
			$this->_helper->json->sendJson(array('state'=>'false', 'msg'=>$translate->_('am') ) );

    }		
	public function runactionAction()
    {
    	$this->DB	= Zend_Registry::get('front_db');
		$params	= $this->_getAllParams();
		if(! $path = $this->getactionPath($params['id']) )
		{
			$translate	= Zend_Registry::get('translate');
			$this->_helper->FlashMessenger(array($translate->_('h')));
			$this->_redirect('/portlet/management/frmlist#fragment-3');
		}
		$this->_redirect('/portlet/'.$path);
		
	}


/// Helper Method for Actions -------------------------------------------------------------------*********
	protected function	_fetchWbsPortletss($start=0, $limit=20)
	{
		$sql	= 'SELECT pr.pr_id, pr.wbs_id, pr.pr_name, pr.pr_comment, cr.cr_name, cr.cr_id, cr.wbs_id AS cr_wbs FROM `wbs_portlets` AS `pr` '
				. ' LEFT JOIN `wbs_portlet_controllers` AS `cr` ON `cr`.`cr_pr_id` = `pr`.`pr_id` '
				. ' WHERE `pr`.`wbs_id` IN (0, '.WBSiD.') AND (`pr`.`wbs_group` RLIKE "\/'.str_replace(',','\/|\/',WBSgR).'\/") '
				. ' ORDER BY `pr`.`pr_id` DESC LIMIT '.$start.','.$limit ;
				
		if( !$result = $this->DB->fetchAll($sql) )	return false;
		foreach($result as $pr_cr)
		{
			$pr_set[ $pr_cr['pr_id'] ]['pr_id']	= $pr_cr['pr_id'];
			$pr_set[ $pr_cr['pr_id'] ]['wbs_id']= $pr_cr['wbs_id'];
			$pr_set[ $pr_cr['pr_id'] ]['pr_name']	= $pr_cr['pr_name'];
			$pr_set[ $pr_cr['pr_id'] ]['pr_comment']= $pr_cr['pr_comment'];
			if(!empty($pr_cr['cr_name']))
			$pr_set[ $pr_cr['pr_id'] ]['pr_cr'][]	= array('cr_name'=>$pr_cr['cr_name'], 'cr_id'=>$pr_cr['cr_id'], 'cr_wbs'=>$pr_cr['cr_wbs']);
		}
		$sql	= 'SELECT COUNT(pr_id) AS cnt FROM `wbs_portlets` '
				. ' WHERE `wbs_id` IN (0, '.WBSiD.') AND (`wbs_group` RLIKE "\/'.str_replace(',','\/|\/',WBSgR).'\/") ';
		$count	= $this->DB->fetchOne($sql);
		return array($pr_set, $count);
	}
    protected function getWbsLayouts()
    {
		$sql	= 'SELECT ly_id, ly_title, wbs_id  FROM wbs_portlet_layout WHERE wbs_id IN (0, '. WBSiD .') AND (wbs_group RLIKE "\/'.str_replace(',','\/|\/',WBSgR)
				. '\/") ORDER BY `ly_id` DESC';
		if( $result	= $this->DB->fetchAll($sql) )	return $result;
		return false;
	}
    protected function getControllerActions($id)
    {	
		$sql	= 'SELECT * FROM `wbs_portlet_actions` WHERE `wbs_id` IN (0,'.WBSiD.') AND ac_cr_id='.addslashes($id);
		if( $result	= $this->DB->fetchAll($sql) )	return $result;
		return false;
    }
    protected function getactionPath($id)
    {	
		if(!is_numeric($id))	return false;
		$sql	= 'SELECT pr.pr_name, cr.cr_name, ac.ac_name FROM wbs_portlets AS pr Inner Join wbs_portlet_controllers AS cr ON pr.pr_id = cr.cr_pr_id '
				. ' Inner Join wbs_portlet_actions AS ac ON cr.cr_id = ac.ac_cr_id WHERE ac.wbs_id IN (0,'.WBSiD.') AND ac.ac_id='.addslashes($id);
		if( $result	= $this->DB->fetchAll($sql) )	return $result[0]['pr_name'].':'.$result[0]['cr_name'].':'.$result[0]['ac_name'];
		return false;
    }
		
    protected function hasChildren($id, $type)
    {
		if($type=='cr')
			$sql	= 'SELECT `ac_id` FROM `wbs_portlet_actions` WHERE `wbs_id` IN (0,'.WBSiD.') AND ac_cr_id='.addslashes($id);
		if($type=='pr')
			$sql	= 'SELECT `cr_id` FROM `wbs_portlet_controllers` WHERE `wbs_id` IN (0,'.WBSiD.') AND cr_pr_id='.addslashes($id);
		if( $result	= $this->DB->fetchAll($sql) )	return true;
		return false;
	}	
    protected function isExistPortlet($id)
    {	
		if(!is_numeric($id)) return false;
		$sql	= 'SELECT `pr_id` FROM `wbs_portlets` WHERE `wbs_id` IN (0,'.WBSiD.') AND pr_id='.addslashes($id);
		if( $result	= $this->DB->fetchAll($sql) )	return true;
		return false;
    }
    protected function isExistController($id, $pid)
    {	
		if(!is_numeric($id) or !is_numeric($pid)) return false;
		$sql	= 'SELECT `cr_id` FROM `wbs_portlet_controllers` WHERE `wbs_id` IN (0,'.WBSiD.') AND cr_id='.addslashes($id).' AND cr_pr_id='.addslashes($pid);
		if( $result	= $this->DB->fetchAll($sql) )	return true;
		return false;
    }
	
    protected function getPortletForEdit($id)
    {	
		$this->view->assign('form_action', '/portlet/management/editportlet/id/'.$id);
		$sql	= 'SELECT * FROM `wbs_portlets` WHERE `wbs_id`='.WBSiD.' AND pr_id='.addslashes($id);
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			$data['pid']		= $result[0]['pr_id'];
			$data['layout']		= $result[0]['pr_layout'];
			$data['name']		= $result[0]['pr_name'];
			$data['comment']	= $result[0]['pr_comment'];
			$data['config']		= $result[0]['pr_config'];
			$data['bootstrap']	= $result[0]['pr_bootstrap'];
			
			$this->view->assign('data', $data );
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/portlet/management/frmlist#fragment-3');
		}
    }
    protected function getControllerForEdit($id, $pid)
    {	
		if(!is_numeric($id) or !is_numeric($pid)) return false;
		$this->view->assign('form_action', '/portlet/management/editcontroller/id/'.$id);
		$sql	= 'SELECT * FROM `wbs_portlet_controllers` WHERE `wbs_id`='.WBSiD.' AND `cr_id`='.addslashes($id).' AND `cr_pr_id`='.addslashes($pid);
		$result	= $this->DB->fetchAll($sql);
		if (is_array($result) and count($result)==1)
		{
			$data['pid']		= $pid;
			$data['cid']		= $result[0]['cr_id'];
			$data['layout']		= $result[0]['cr_layout'];
			$data['name']		= $result[0]['cr_name'];
			$data['comment']	= $result[0]['cr_comment'];
			$data['init']		= $result[0]['cr_init'];
			//$data['bootstrap']	= $result[0]['pr_bootstrap'];

			$this->view->assign('data', $data );
			$this->view->assign('cr_ac', $this->getControllerActions($id) );
		}
		else
		{
			$msg[]= $this->translate->_('m');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/portlet/management/frmlist#fragment-3');
		}
    }
    protected function getactionForEdit($id, $cid)
    {	
		if(!is_numeric($id) or !is_numeric($cid)) return false;

		$sql	= 'SELECT * FROM `wbs_portlet_actions` WHERE `wbs_id`='.WBSiD.' AND `ac_id`='.addslashes($id).' AND `ac_cr_id`='.addslashes($cid);
		if(! $result = $this->DB->fetchAll($sql)) return false;

		$data['aid']		= $result[0]['ac_id'];
		$data['acname']		= $result[0]['ac_name'];
		$data['aclayout']	= $result[0]['ac_layout'];
		$data['accomment']	= $result[0]['ac_comment'];
		$data['accode']		= $result[0]['ac_code'];
		
		return $data;
    }
	protected function preparePortletRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		
		//$data['wbs_id']			= WBSiD;
		//$data['pr_id']			= $this->params['pid'];
		$data['pr_name']		= $this->params['name'];
		$data['pr_layout']		= $this->params['layout'];
		$data['pr_comment']		= $this->params['comment'];
		$data['pr_config']		= $this->params['config'];
		$data['pr_bootstrap']	= $this->params['bootstrap'];
		$data	= array_map(trim, $data);
		if(empty($this->params['name']) or preg_match('/[\s\:\?]+/', $this->params['name']) )
		{
			$error[]	= $this->translate->_('k');
			$this->_helper->flashMessenger->addMessage( $error );
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmportlet'.$this->newUriParams);
		}

//		if(is_array($this->params['blocks']['nq']) and count($this->params['blocks']['nq'])>0)
//			foreach($this->params['blocks']['nq'] as $key=>$value)
//			{
//				$value['block']	= explode('__', $value['block']);
//				$this->params['blocks']['nq'][$key]['block']	= $value['block'][0];
//				$this->params['blocks']['nq'][$key]['type']		= $value['block'][1];
//			}

		//$this->validate();
		
		//$data				= $this->arrayDbData();
		return $data;
	}
	protected function prepareControllerRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		
		//$data['wbs_id']			= WBSiD;
		//$data['cr_id']			= $this->params['cid'];
		$data['cr_name']		= $this->params['name'];
		$data['cr_layout']		= $this->params['layout'];
		$data['cr_comment']		= $this->params['comment'];
		$data['cr_init']		= $this->params['init'];
		//$data['pr_bootstrap']	= $this->params['bootstrap'];
		$data	= array_map(trim, $data);
		if(empty($data['cr_name']) or preg_match('/[\s\:\?]+/', $data['cr_name']) )
		{
			$error[]	= $this->translate->_('n');
			$this->_helper->flashMessenger->addMessage( $error );
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/portlet/management/frmcontroller/prid/'.$this->params['pid']. $this->newUriParams);
		}
		return $data;
	}
	protected function prepareactionRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		
		//$data['wbs_id']			= WBSiD;
		//$data['ac_cr_id']		= $this->params['cid'];
		$data['ac_name']		= $this->params['acname'];
		$data['ac_layout']		= $this->params['aclayout'];
		$data['ac_comment']		= $this->params['accomment'];
		$data['ac_code']		= $this->params['accode'];
		//$data['pr_bootstrap']	= $this->params['bootstrap'];
		$data	= array_map(trim, $data);
		if(empty($data['ac_name']) or preg_match('/[\s\:\?]+/', $data['ac_name']) )
		{
			$error['state']	= 'false';
			$error['msg']	= $this->translate->_('v');
			$this->_helper->json->sendJson($error);
			//$this->_helper->flashMessenger->addMessage( $error );
			//$this->_helper->flashMessenger->addMessage($this->params);
			//$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			//$this->_redirect('/portlet/management/frmcontroller/prid/'.$this->params['pid']. $this->newUriParams);
		}
		return $data;
	}
	protected function setUriParams()
	{
		$this->newUriParams =	'';
		if ( is_numeric($this->params['prid']) )	$this->newUriParams .=	'/prid/'.$this->params['prid'];
		if ( is_numeric($this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-3';
	}
	
	
	
	
}
