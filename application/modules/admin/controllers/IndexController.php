<?php 
 
class Admin_IndexController extends Zend_Controller_Action
{

    public function indexAction() 
    {
//		$modelData['request']	= $request 		= $this->getRequest(); 91.05.17
//		$modelData[0]	= $request->getParam('pageid');
//		if(!isset($modelData[0]))	$this->_redirect('/admin/page/11');

		$modelData[0]	= $this->_getParam('pageid');

		// Commented On 2014-06-21	
		//if(empty($modelData[0]))	$this->_redirect('/admin/page/11');
		
		//2014-06-21
		if(empty($modelData[0]))
		{
			$site	= Zend_Registry::get('site');
			$matched = array();
			if(preg_match('/^\/page\/(\d+)/',$site['wb_homepage'], $matched) and is_numeric($matched[1]))
				$this->_redirect('/admin/page/'.$matched[1]);
			else
				$this->_redirect('/admin/page/11');
		}
		// END 2014-06-21
		

		$this->_helper->viewRenderer->setNoRender();
		$rastaJSfiles	= array(
					'verticalpanel', 'toolbar', 'breedable', 'menu', 'rtc', 'gallery', 'page', 'scenario', 'contextmenu', 'extlink', 'master',
					'paging', 'searchable', 'others'
								);
		foreach($rastaJSfiles as $filename) $this->view->headScript()->appendFile('/js/RSD_UIEjs/'.$filename.'.js');
		
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		
		$site		= new Application_Model_Site_Components_Component;
		$page		= new Application_Model_Page_Admin($modelData);

		$this->view->assign('PageID'		, $modelData[0]);
		$this->view->assign('website_id'	, WBSiD);	
		$this->view->assign('bodyId'		, $page->skin['skin_id']);
		
		$this->view->assign('pageslist'		, $site->getSitePages());
		$this->view->assign('sitescenarios'	, $site->getSiteScenarios());
		$this->view->assign('sitertcs'		, $site->getSiteRTCs());				
		$this->view->assign('sitesvms'		, $site->getSiteSmMenus());
		$this->view->assign('sitegallerys'	, $site->getSiteGalleries());						
		$this->view->assign('extLinks'		, $site->getSiteExtLinks());
		
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$this->view->assign('SkinVersin'	, $page->SkinVersin);
		echo $page->getHtmlBody();

//		$HtmlBody	= $page->getHtmlBody();
//		echo $HtmlBody;
    }
	
    public function sitesettingAction()
    {
		$env	= $this->_getParam('env');
		$this->_helper->_layout->setLayout('dashboard');
		
		$registry	= Zend_Registry::getInstance();  
		$siteinfos	= $registry['site'];
		$DB			= $registry['front_db'];
		$translate	= $registry['translate'];
		
		$flashmsg	= $this->_helper->flashMessenger->getMessages();
		if(count($flashmsg)>0) $siteinfos = $flashmsg[0];
		//$pages		= $DB->fetchAll("select CONCAT('/page/',`local_id`) as `local_id` , `wb_page_title` from `wbs_pages` where `wbs_id`=".WBSiD);
		//$senarios	= $DB->fetchAll("select `uri` , `title` from `wbs_scenario` where `wbs_id`=".WBSiD);
		//$this->view->assign('pages', $pages);
		//$this->view->assign('senarios', $senarios);
		$this->view->assign('site', $siteinfos);
		if (!empty($flashmsg[1])){$this->view->assign('mesg', $flashmsg[1]);}
		$this->view->assign('env'		,$env);
		$this->view->assign('title_site', $translate->_('a') );
		$this->view->assign('translate', $translate);
    }

    public function setsiteAction()
    {
		$this->_helper->layout()->disableLayout();
		$this->DB				= Zend_Registry::get('front_db');
		$translate				= Zend_Registry::get('translate');
		$request 				= $this->getRequest();
		$params					= $request->getParams();
		$data['wb_title']		= $params['s_title'];
		$data['latin_title']	= $params['s_latintitle'];
		$data['wb_status']		= $params['s_vstate'];		
		$data['wb_homepage']	= $params['s_page_senario'];
		$data['skin_id']		= $params['s_skin'];
		$data['wb_slogan']		= $params['s_slogan'];
		$data['wb_authors']		= $params['s_authors'];
		$data['wb_description']	= $params['s_description'];
		$data['wb_keywords']	= $params['s_keywords'];
		$env					= $params['env'];
		if(empty($data['skin_id']) || $data['skin_id']<=0)
		{
			$error[] 		= $translate->_('a'); //'پوسته انتخابی نا معتبر است';
			$siteinfos		= Zend_Registry::get('site');
			$data['skin_id']= $siteinfos['skin_id'];
		}
		else
		{
			$sql		= 'select count(`skin_id`) as `cnt` from `wbs_skin` where (`wbs_id` = '. WBSiD.' or `wbs_id` = 0) and `skin_id`=' .$data['skin_id'];
			$result		= $this->DB->fetchAll($sql);
			if($result[0]['cnt']!=1)
			{
				$error[]		= $translate->_('b'); //'پوسته انتخابی نا معتبر است'; 
				$siteinfos		= Zend_Registry::get('site');
				$data['skin_id']= $siteinfos['skin_id'];
			}
		}
		if(strlen($data['wb_title'])<4) $error[] = $translate->_('c'); //'عنوان سایت نباید کمتر از دو حرف باشد';
		if($data['wb_homepage']=="") 	$error[] = $translate->_('d'); //'صفحه ورودی نا معتبر می باشد';
		if(count($error)>0)
		{
			$this->_helper->FlashMessenger($data);
			$this->_helper->FlashMessenger($error);
			if($env!='dsh'){$this->_redirect('/admin/index/sitesetting');}else{$this->_redirect('/admin/index/sitesetting/env/dsh#fragment-1');}
		}
		else
		{
			$this->DB->update('wbs_profile',$data ,"`wb_id`='".WBSiD ."'");
		}

//		if($env!='dsh')
//		{
	   		/*die('<script>window.close();</script>');*/
//		}
//		else
//		{
			$this->_redirect('/dashboard#fragment-1');
//		}
//		$this->view->assign('resp', "");
    }

//    public function templateAction()
//    {
//		$this->_redirect('/skiner/skin/frmlist#fragment-4');	
//    }
//    public function authenticate() 
//    {
//		$auth		= Zend_Auth::getInstance(); 
//		$user		= $auth->getIdentity();
//		if	(!$auth->hasIdentity())			$this->_redirect('/login');
//		elseif($user->wb_user_id!=WBSiD)	$this->_redirect('/admin/user/logout');
//		return true;
//	}
//	public function redirectToLoginForm($logout = false)
//	{
//		$referer	= $_SERVER['REQUEST_URI'];
//		$this->ses	= new Zend_Session_Namespace('MyApp');
//		$this->ses->redirecPath	= $referer;
//		if($logout)	$this->_redirect('/admin/user/logout');
//		$this->_redirect('/login');
//	}


}