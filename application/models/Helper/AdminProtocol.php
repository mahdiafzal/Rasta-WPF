<?php
 
class Application_Model_Helper_AdminProtocol extends Zend_Controller_Action_Helper_Abstract
{
	public function preDispatch()
    {
		$this->site			= Zend_Registry::get('site');
		$this->request		= $this->getRequest();
		$this->response 	= $this->getResponse();
		$this->module		= strtolower($this->request->getModuleName());
		$this->controller	= strtolower($this->request->getControllerName());
		$this->action		= strtolower($this->request->getActionName());
		$this->params		= $this->request->getParams();
		//$this->checkExpiration();
		$this->authenticate();
		$this->localization();
		
	}
	public function localization()
	{
		$filepath	= realpath(APPLICATION_PATH .'/lang/'.LANG.'/'.$this->module.'/'.$this->controller.'.mo');
		if(!file_exists($filepath))
		{
			$filepath	= realpath(APPLICATION_PATH .'/lang/'.LANG.'/'.$this->module.'/'.$this->controller.'/'.$this->action.'.mo');
			if(!file_exists($filepath)) return false;
		}
		
		$translate = new Zend_Translate(
											array(
												'adapter' => 'gettext',
												'content' => $filepath,
												'locale'  => LANG
											)
										);
		Zend_Registry::set('translate', $translate);
	}
	public function authenticate()
	{
		$wbs_resourcesModules	= array('admin', 'rtcmanager', 'dashboard', 'usermanager', 'comment',
										 'skiner', 'stat', 'scenario', 'taxonomy', 'help', 'db', 'gadget');
		//$utopia_resourcesModules= array('utopia');
		if(in_array($this->module, $wbs_resourcesModules)) $this->wbsAuthentication();
		//elseif(in_array($this->module, $utopia_resourcesModules)) $this->utopiaAuthentication();
		else return true;
	}
	public function wbsAuthentication()
	{
		if($this->authExceptions()) return true;
		$auth		= Zend_Auth::getInstance(); 
		$user		= $auth->getIdentity();
		if	(!$auth->hasIdentity())
		{
			$this->ses	= new Zend_Session_Namespace('MyApp');
			$this->ses->redirecPath	= $_SERVER['REQUEST_URI'];
			$this->actionForwarding(array('admin','user','frmlogin',array() )); //response->setRedirect('/login');
		}
		elseif($user->wb_user_id!=WBSiD)
		{
			$this->response->setRedirect('/admin/user/logout');
		}
		else
		{
			$this->checkUserPermission($user);
		}
		return true;
	}
//	public function utopiaAuthentication()
//	{
//		$auth		= Zend_Auth::getInstance(); 
//		$user		= $auth->getIdentity();
//		if	(!$auth->hasIdentity())
//		{
//			$this->ses	= new Zend_Session_Namespace('MyApp');
//			$this->ses->redirecPath	= $_SERVER['REQUEST_URI'];
//			$this->actionForwarding(array('controlpanel','user','frmlogin',array() )); //response->setRedirect('/login');
//		}
//		elseif(isset($user->wb_user_id))
//		{
//			$this->response->setRedirect('/controlpanel/user/logout');
//		}
//		return true;
//	}
	public function checkUserPermission($user)
	{
		if( $user->is_admin == 1) return true;
		if(!$user->user_group >0) die( Application_Model_Messages::message(103) );
		$ses = new Zend_Session_Namespace('MyApp');
		if(empty($ses->userPermissions))
		{
			$this->DB	= Zend_Registry::get('front_db');
			$u_groups	= preg_replace('/\//', ', ', $user->user_group);
			$sql		= "SELECT * FROM `user_groups` WHERE ".Application_Model_Pubcon::get(1110)." AND `id` IN (".$u_groups.")";
			$result		= $this->DB->fetchAll($sql);
			if(count($result) < 1) die( Application_Model_Messages::message(103) );
			$p_array	= array_fill(0,120, 0);
			foreach($result as $value)
			{
				$gp_array	= str_split($value['permissions']);
				foreach($gp_array as $gp_key=>$gp_value) $p_array[$gp_key]	= ($gp_value==1)?$gp_value:$p_array[$gp_key];
			}
			$ses->userPermissions	= implode('', $p_array);
		}
		$p_array	= $ses->userPermissions;
		$a_perpos	= $this->getActionPerPos();
		
		if(is_array($a_perpos[0]))
		{
			foreach($a_perpos[0] as $k) if($p_array[ $k ] == 1)	return;
			if( $a_perpos[1] == 0)	echo json_encode(array(false, Application_Model_Messages::message(104)));
			else					echo Application_Model_Messages::message(103);
			die();
		}

		if($p_array[ $a_perpos[0] ] == 0)
		{
			if( $a_perpos[1] == 0)	echo json_encode(array(false, Application_Model_Messages::message(104)));
			else					echo Application_Model_Messages::message(103);
			die();
		}
	}
	public function getActionPerPos()
	{
		$resources	= array();
		$resources['admin_index_index']				= array(1, 1);
		$resources['admin_index_sitesetting']		= array(2, 1);
		$resources['admin_index_setsite']			= array(3, 1);
		
//		$resources['admin_ajaxget_getscenariodata']	= array(1, 0);
//		$resources['admin_ajaxget_scenariolist']	= array(1, 0);
//		$resources['admin_ajaxget_rtclist']			= array(1, 0);
//		$resources['admin_ajaxget_gallerylist']		= array(1, 0);
//		$resources['admin_ajaxget_menulist']		= array(1, 0);
//		$resources['admin_ajaxget_pagelist']		= array(1, 0);
//		$resources['admin_ajaxget_extlinklist']		= array(1, 0);
//		$resources['admin_ajaxget_autocomplete']	= array(1, 0);
//		$resources['admin_ajaxget_gettingdata']		= array(1, 0);
//		$resources['admin_ajaxget_getgallerypic']	= array(1, 0);
//		$resources['admin_ajaxget_geteditmenu']		= array(1, 0);
//		$resources['admin_ajaxget_getdataofpage']	= array(1, 0);
		
		$resources['admin_ajaxset_savescenario']	= array(4, 0);
		$resources['admin_ajaxset_editscenario']	= array(5, 0);
		$resources['admin_ajaxset_savegallery']		= array(6, 0);
		$resources['admin_ajaxset_editgallery']		= array(7, 0);
		$resources['admin_ajaxset_savemenu']		= array(8, 0);
		$resources['admin_ajaxset_replacemenu']		= array(9, 0);
		$resources['admin_ajaxset_savepage']		= array(10, 0);
		$resources['admin_ajaxset_replacepage']		= array(11, 0);
		$resources['admin_ajaxset_savelink']		= array(12, 0);
		$resources['admin_ajaxset_replacelink']		= array(13, 0);
		$resources['admin_ajaxset_savepagecontent']	= array(14, 0);
		//$resources['admin_ajaxset_savepageskin']	= array(1, 0);
		
		
		$resources['admin_template_select']			= array(15, 1);
		$resources['skiner_template_frmlist']		= array(16, 1);
		//$resources['admin_template_preview']		= array(16, 1);

		$resources['rtcmanager_frmregister_index']	= array(17, 1);
		$resources['rtcmanager_register_crt']		= array(18, 1);
		$resources['rtcmanager_register_edit']		= array(19, 1);
		$resources['rtcmanager_frmlistcnt_index']	= array(20, 1);
		$resources['rtcmanager_doact_index']		= array(21, 1);
		$resources['rtcmanager_delcnt_index']		= array(22, 1);
		$resources['rtcmanager_frmdelcnt_index']	= array(22, 1);

		$resources['dashboard_index_index']			= array(23, 1);

		$resources['dashboard_link_frmlist']		= array(24, 1);
		$resources['dashboard_link_del']			= array(25, 1);
		$resources['dashboard_link_frmedit']		= array(26, 1);
		$resources['dashboard_link_edit']			= array(26, 1);
		$resources['dashboard_link_frmcrt']			= array(27, 1);
		$resources['dashboard_link_crt']			= array(27, 1);

		/*$resources['dashboard_scenario_frmlist']		= array(28, 1);
		$resources['dashboard_scenario_del']			= array(29, 1);
		$resources['dashboard_scenario_frmedit']		= array(30, 1);
		$resources['dashboard_scenario_edit']			= array(30, 1);
		$resources['dashboard_scenario_updateallsubs']	= array(30, 1);
		$resources['dashboard_scenario_frmcrt']			= array(31, 1);
		$resources['dashboard_scenario_crt']			= array(31, 1);*/

		$resources['scenario_admin_frmlist']		= array(28, 1);
		$resources['scenario_admin_del']			= array(29, 1);
		$resources['scenario_admin_frmedit']		= array(30, 1);
		$resources['scenario_admin_edit']			= array(30, 1);
		$resources['scenario_admin_updateallsubs']	= array(30, 1);
		$resources['scenario_admin_frmcrt']			= array(31, 1);
		$resources['scenario_admin_crt']			= array(31, 1);

		$resources['dashboard_page_frmlist']		= array(32, 1);
		$resources['dashboard_page_del']			= array(33, 1);
		$resources['dashboard_page_frmedit']		= array(34, 1);
		$resources['dashboard_page_edit']			= array(34, 1);
		$resources['dashboard_page_frmcrt']			= array(35, 1);
		$resources['dashboard_page_crt']			= array(35, 1);

		$resources['usermanager_frmlist_index']				= array(36, 1);
		$resources['usermanager_frmregister_index']			= array(array(37,38), 1);
		$resources['usermanager_register_crt']				= array(37, 1);
		$resources['usermanager_register_edit']				= array(38, 1);
		$resources['usermanager_doact_delone']				= array(39, 1);
		$resources['usermanager_doact_delsome']				= array(40, 1);
		$resources['usermanager_doact_delconfirm']			= array(array(39,40), 1);
		$resources['usermanager_doact_activate']			= array(41, 1);
		$resources['usermanager_doact_activateconfirm']		= array(41, 1);
		$resources['usermanager_doact_deactivate']			= array(42, 1);
		$resources['usermanager_doact_deactivateconfirm']	= array(42, 1);
		
		$resources['usermanager_frmgrouplist_index']	= array(43, 1);
		$resources['usermanager_frmgroupregister_index']= array(array(44,45), 1);
		$resources['usermanager_groupregister_crt']		= array(44, 1);
		$resources['usermanager_groupregister_edit']	= array(45, 1);
		$resources['usermanager_doactgroup_delone']		= array(46, 1);
		$resources['usermanager_doactgroup_delsome']	= array(47, 1);
		$resources['usermanager_doactgroup_delconfirm']	= array(array(46,47), 1);

		$resources['dashboard_dandelion_frmlist']		= array(48, 1);
		$resources['dashboard_dandelion_del']			= array(49, 1);
		$resources['dashboard_dandelion_frmregister']	= array(array(50,51), 1);
		$resources['dashboard_dandelion_edit']			= array(50, 1);
		$resources['dashboard_dandelion_crt']			= array(51, 1);

		$resources['skiner_skin_frmlist']		= array(52, 1);
		$resources['skiner_skin_del']			= array(53, 1);
		$resources['skiner_skin_frmregister']	= array(array(54,55), 1);
		$resources['skiner_skin_edit']			= array(54, 1);
		$resources['skiner_skin_crt']			= array(55, 1);

		$resources['comment_manager_index']		= array(56, 1);
		$resources['comment_manager_del']		= array(57, 1);
		$resources['comment_manager_status']	= array(58, 1);

		$resources['skiner_body_frmlist']		= array(59, 1);
		$resources['skiner_body_del']			= array(60, 1);
		$resources['skiner_body_frmregister']	= array(array(61,62), 1);
		$resources['skiner_body_edit']			= array(61, 1);
		$resources['skiner_body_crt']			= array(62, 1);

		$resources['skiner_block_frmlist']		= array(63, 1);
		$resources['skiner_block_del']			= array(64, 1);
		$resources['skiner_block_frmregister']	= array(array(65,66), 1);
		$resources['skiner_block_edit']			= array(65, 1);
		$resources['skiner_block_crt']			= array(66, 1);

		$resources['dashboard_manual_frmlist']		= array(67, 1);
		$resources['dashboard_manual_del']			= array(68, 1);
		$resources['dashboard_manual_frmregister']	= array(array(69,70), 1);
		$resources['dashboard_manual_edit']			= array(69, 1);
		$resources['dashboard_manual_crt']			= array(70, 1);

		$resources['rtcmanager_rtctype_frmlist']	= array(71, 1);
		$resources['rtcmanager_rtctype_del']		= array(72, 1);
		$resources['rtcmanager_rtctype_frmregister']= array(73, 1);
		$resources['rtcmanager_rtctype_edit']		= array(74, 1);
		$resources['rtcmanager_rtctype_crt']		= array(75, 1);
		

		$resources['gadget_index_interface']		= array(76, 1);
		$resources['gadget_data_ajaxget']			= array(76, 1);
		$resources['gadget_data_ajaxset']			= array(77, 1);
		$resources['gadget_rtc_edit']				= array(78, 1);
		$resources['gadget_rtc_crt']				= array(79, 1);
		
		$resources['gadget_admin_publicgadlist']	= array(80, 1);
		
		$resources['gadget_admin_frmlist']			= array(81, 1);
		$resources['gadget_admin_frmregister']		= array(array(82,83), 1);
		$resources['gadget_admin_crt']				= array(82, 1);
		$resources['gadget_admin_edit']				= array(83, 1);
		$resources['gadget_admin_del']				= array(84, 1);
		$resources['gadget_admin_frmconfig']		= array(85, 1);
		$resources['gadget_admin_configure']		= array(85, 1);

		$resources['dashboard_gallery_frmlist']		= array(86, 1);
		$resources['dashboard_gallery_del']			= array(87, 1);
		$resources['dashboard_gallery_frmregister']	= array(array(6,7), 1);
		$resources['dashboard_gallery_edit']		= array(7, 1);
		$resources['dashboard_gallery_crt']			= array(6, 1);

		$resources['dashboard_menu_frmlist']		= array(88, 1);
		$resources['dashboard_menu_del']			= array(89, 1);
		$resources['dashboard_menu_frmregister']	= array(array(8,9), 1);
		$resources['dashboard_menu_edit']			= array(9, 1);
		$resources['dashboard_menu_crt']			= array(8, 1);

		$resources['skiner_gallery_frmlist']		= array(90, 1);
		$resources['skiner_gallery_del']			= array(91, 1);
		$resources['skiner_gallery_frmregister']	= array(array(92,93), 1);
		$resources['skiner_gallery_edit']			= array(92, 1);
		$resources['skiner_gallery_crt']			= array(93, 1);

		$r_key	= $this->module.'_'.$this->controller.'_'.$this->action;
		if( !empty($resources[$r_key]) ) return $resources[$r_key];
		return array(0, 0);
	}
	public function authExceptions()
	{
		$excepResources = array(
							'admin.user.*',
							'admin.public.*',
							'scenario.index.*',
							'comment.index.*',
							'comment.register.*'
							);
		foreach($excepResources as $value)
		{
			$exceptions	= explode('.', $value);
			if($exceptions[0]=='*') return true;
			if($exceptions[0]==$this->module)
				if($exceptions[1]=='*') return true;
				elseif($exceptions[1]==$this->controller)
					if($exceptions[2]=='*') return true;
					elseif($exceptions[2]==$this->action) return true;
		}
		return false;
	}
	public function checkExpiration()
	{
		$resourcesModules	= array('admin', 'rtcmanager');
		$today 				= strtotime(date("Y-m-d H:i:s"));
		$expiration_date 	= strtotime($this->site['wb_expirdate']);
		if (in_array($this->module, $resourcesModules) && $expiration_date < $today) 	die( Application_Model_Messages::message(102) );	
	}
	public function actionForwarding($data)
	{
		//$this->_forward('index', 'index', 'default', array('webpage' => '11'));
		
		$this->getRequest()->setParams($data[3]) 
							->setModuleName($data[0])
							->setControllerName($data[1])
							->setActionName($data[2])
							->setDispatched(false);
		
	}
}