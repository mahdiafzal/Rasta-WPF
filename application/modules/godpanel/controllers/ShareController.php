<?php

class Godpanel_ShareController extends Zend_Controller_Action
{
    public function init()
    {
		Godpanel_Model_User_User::initUser();
		if(!defined('USRiD') or USRiD!=='1')	die(Application_Model_Messages::message(404));
    }
    public function indexAction()
    {
		$this->params	= $this->_getAllParams();
		$this->co_id	= $this->params['id'];
		if(!is_numeric($this->co_id))	die('<h1>ERROR</h1>');
		$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		
		switch($this->params['path'])
		{
			//case 'usermanager.frmregister.index': $this->_shareUser(); break;
			case 'usermanager.frmgroupregister.index':	$data	= array('id AS unic, wbs_id, wbs_group, title','user_groups','id','user_groups'); break;
			case 'rtcmanager.frmregister.index': 		$data	= array('id AS unic, wbs_id, wbs_group, title','wbs_rtcs','id','rtc'); break;
			
			case 'dashboard.gallery.frmregister': 		$data	= array('gallery_id AS unic, wbs_id, wbs_group, gallery_title AS title','wbs_gallery','gallery_id','gallery'); break;
			case 'dashboard.menu.frmregister': 			$data	= array('id AS unic, wbs_id, wbs_group, menu_title AS title','wbs_menu','id','menu'); break;
			//case 'dashboard.page.frmedit': 				$data	= array('id AS unic, wbs_id, wbs_group, wb_page_title AS title','wbs_pages','local_id','page'); break;
			case 'dashboard.scenario.frmedit': 			$data	= array('id AS unic, wbs_id, wbs_group, title','wbs_scenario','id','scenario'); break;
			case 'dashboard.link.frmedit':				$data	= array('id AS unic, wbs_id, wbs_group, title','wbs_links','id','link'); break;
			case 'dandelion.management.frmregister':	$data	= array('dn_id AS unic, wbs_id, wbs_group, dn_title AS title','wbs_dandelions','dn_id','dandelion'); break;
			case 'dashboard.manual.frmregister':		$data	= array('id AS unic, wbs_id, wbs_group, title','wbs_manual_dashboard','id','dashboard'); break;
			case 'portlet.management.frmportlet':		$data	= array('pr_id AS unic, wbs_id, wbs_group, pr_name AS title','wbs_portlets','pr_id','portlet'); break;
			case 'portlet.management.frmcontroller':	$data	= array('cr_id AS unic, wbs_id, wbs_group, cr_name AS title','wbs_portlet_controllers','cr_id','portlet_controller'); break;
			case 'skiner.skin.frmregister':				$data	= array('skin_id AS unic, wbs_id, wbs_group, title','wbs_skin','skin_id','page_skin'); break;
			case 'skiner.gallery.frmregister':			$data	= array('id AS unic, wbs_id, wbs_group, title','wbs_gallery_template','id','gallery_skin'); break;



//			case 'usermanager.frmgroupregister.index':	$result	= $this->_shareUserGroup(); break;
//			case 'rtcmanager.frmregister.index': 		$result	= $this->_shareRtc(); break;
//			case 'dashboard.gallery.frmregister': 		$result	= $this->_shareGallery(); break;
//			case 'dashboard.menu.frmregister': 			$result	= $this->_shareMenu(); break;
//			case 'dashboard.page.frmedit': 				$result	= $this->_sharePage(); break;
//			case 'dashboard.scenario.frmedit': 			$result	= $this->_shareScenario(); break;
//			case 'dashboard.link.frmedit':				$result	= $this->_shareLink(); break;
//			case 'dandelion.management.frmregister':	$result	= $this->_shareDandelion(); break;
//			case 'dashboard.manual.frmregister':		$result	= $this->_shareMDashboard(); break;
//			case 'portlet.management.frmportlet':		$result	= $this->_sharePortlet(); break;
//			case 'portlet.management.frmcontroller':	$result	= $this->_sharePortletController(); break;
//			case 'skiner.skin.frmregister':				$result	= $this->_shareSkin(); break;
//			case 'skiner.gallery.frmregister':			$result	= $this->_shareGallerySkin(); break;
			default: $data = false; break;
		}
		if($data)		$result	= $this->_fetchContent($data);
		if(!$result)	die('<h1>ERROR</h1>'); 
		$response 	= $this->getResponse();
		$response->insert('menu',$this->view->render('menu.phtml'));

		$this->view->assign('translate', $this->translate); 
		$this->view->assign('title', $this->translate->_('a')); 
		$this->view->assign('content', $result); 
		$this->view->assign('wbsgrs', $this->_fetchAppWbsGroups()); 
    }
    public function registerAction()
    {
		$this->params	= $this->_getAllParams();
		$this->co_id	= $this->params['unic'];
		if(!is_numeric($this->co_id))	die('<h1>ERROR</h1>');
		$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		$next	= false;
		switch($this->params['ctype'])
		{
			case 'user_groups':		$table = 'user_groups'; $id_ns='id'; $next = '_shareNextOne'; break;
			case 'rtc':				$table = 'wbs_rtcs'; $id_ns='id'; $next = '_shareNextOne'; break;
			case 'gallery':			$table = 'wbs_gallery'; $id_ns='gallery_id'; break;
			case 'menu':			$table = 'wbs_menu'; $id_ns='id'; break;
			//case 'page':			$table = 'wbs_pages'; $id_ns='id'; break;
			case 'scenario':		$table = 'wbs_scenario'; $id_ns='id'; $next = '_shareNextOne'; break;
			case 'link':			$table = 'wbs_links'; $id_ns='id'; break;
			case 'dandelion':		$table = 'wbs_dandelions'; $id_ns='dn_id'; $next = '_shareNextOne'; break;
			case 'dashboard':		$table = 'wbs_manual_dashboard'; $id_ns='id'; break;
			case 'portlet':			$table = 'wbs_portlets'; $id_ns='pr_id'; break;
			case 'portlet_controller':		$table = 'wbs_portlet_controllers'; $id_ns='cr_id'; break;
			case 'page_skin':		$table = 'wbs_skin'; $id_ns='skin_id'; $next = '_shareSkinNext'; break;
			case 'gallery_skin':	$table = 'wbs_gallery_template'; $id_ns='id'; break;
			default: $table = false; break;
		}
		if($table)		$result	= $this->_updateContentStatus($table, $id_ns, $next);
		//$response 	= $this->getResponse();
		//$response->insert('menu',$this->view->render('menu.phtml'));

		//$this->view->assign('translate', $this->translate); 
		//$this->view->assign('title', $this->translate->_('a')); 
		//$this->view->assign('wbsgrs', $this->_fetchAppWbsGroups()); 
    }
	
	protected function _updateContentStatus($table, $id_ns='id', $next=false)
	{
		$data['wbs_id']	= 0;
		$data['wbs_group'] = $this->_genWbGroupString();
		try
		{
			$this->DB->update($table, $data ,'`'.$id_ns.'`='.addslashes($this->co_id) );
			if( $next )
			{
				if($this->$next($table, $id_ns))	die('<script>window.close();</script>');
				else die('<h1>ERROR</h1>');
			}
			else	die('<script>window.close();</script>');
				
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
	}
	protected function _genWbGroupString()
	{
		if(!is_array($this->params['wbgroup']) || count($this->params['wbgroup'])==0) return '0';
		if(in_array('1', $this->params['wbgroup']))	return '/1/';
		sort($this->params['wbgroup']);
		$ugroup		= '/'. implode('/', $this->params['wbgroup']).'/';
		return $ugroup;
	}
	
	
	
	protected function _fetchAppWbsGroups()
	{
		$sql		= "SELECT * FROM `app_wbs_groups` ";
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	protected function _fetchContent($data)
	{
		$sql		= 'SELECT '.$data[0].' FROM '.$data[1].' WHERE '.$data[2].'='.addslashes($this->co_id);
		//die($sql);
		if(! $result = $this->DB->fetchAll($sql))	return false;
		$result[0]['type']	= $data[3];
		return $result[0];
	}
	
	protected function _shareNextOne($table, $id_ns)
	{
		switch($table)
		{
			case 'wbs_rtcs':		$table='wbs_rtc_metadata'; $p_id_ns='txt_id'; break;
			case 'user_groups':		$table='user_group_allsubs'; $p_id_ns='ug_id'; break;
			case 'wbs_scenario':	$table='wbs_scenario_allsubs'; $p_id_ns='sc_id'; break;
			case 'wbs_dandelions':	$table='wbs_dandelion_actions'; $p_id_ns='da_dn_id'; break;
			default: return false; break;
		}
		try
		{
			$sql	= 'UPDATE '.$table.' SET `wbs_id`=0 WHERE '.$p_id_ns.' = '.addslashes($this->co_id);
			$this->DB->query($sql);
			return true;
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	protected function _shareSkinNext($table, $id_ns)
	{
		try
		{
			$sql	= 'SELECT @cou:=COUNT(`rank`)+1 FROM `wbs_skin`;'
					. 'UPDATE `wbs_skin` SET `rank` = @cou WHERE `skin_id` = '.addslashes($this->co_id).' LIMIT 1;';
			$this->DB->query($sql);
			return true;
				
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	
	
	
//	protected function _shareUser()
//	{
//		$sql		= "SELECT * FROM `users` WHERE `id`=".addslashes($this->co_id);
//		if(! $result = $this->DB->fetchAll($sql))	return false;
//		$this->view->assign('content', $result[0]); 
//		if($this->params['action']=='index')	return $result[0];
//	}

	protected function _shareUserGroup()
	{
	}
	protected function _shareRtc()
	{
	
	}
	protected function _shareGallery()
	{
	
	}
	protected function _shareMenu()
	{
	
	}
	protected function _sharePage()
	{
	
	}
	protected function _shareScenario()
	{
	
	}
	protected function _shareLink()
	{
	
	}
	protected function _shareDandelion()
	{
	
	}
	protected function _shareMDashboard()
	{
	
	}
	protected function _sharePortlet()
	{
	
	}
	protected function _sharePortletController()
	{
	
	}
	protected function _shareGallerySkin()
	{
	
	}

}

