<?php
class IndexController extends Zend_Controller_Action
{	
    public function indexAction() 
    {
		$modelData[0]	= $this->_getParam('webpage');
		if(empty($modelData[0]))
		{
			$site	= Zend_Registry::get('site');
			//2014-04-08//if(!empty($site['wb_homepage']))$this->_redirect($site['wb_homepage']);
			//2014-06-21//if(!empty($site['wb_homepage']) and $site['wb_homepage']!='/')$this->_redirect($site['wb_homepage']);
			//2014-06-21//else //2014-04-08//$this->_redirect('/page/11');
			//2014-06-21//	$modelData[0] = 11;
			
			//2014-06-21
			$matched = array();
			if(preg_match('/^\/page\/(\d+)/',$site['wb_homepage'], $matched) and is_numeric($matched[1]))
				$modelData[0] = $matched[1];
			elseif(empty($site['wb_homepage']) or $site['wb_homepage']=='/')
				$modelData[0] = 11;
			else 
				$this->_redirect($site['wb_homepage']);
			// END 2014-06-21
		}
		//if(!empty($_GET['sid']) and is_numeric($_GET['sid'])) $modelData['sid'] = addslashes($_GET['sid']);
		$page		= new  Application_Model_Page_Common($modelData);
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$this->view->assign('SkinVersin'	, $page->SkinVersin);
		$HtmlBody	= $page->getHtmlBody();
		echo $HtmlBody;
    }
    public function galleryAction()
    {
		//$ads	= new Application_Model_Ads();	
			
		//$request 		= $this->getRequest();
		$modelData[0]	= '12';
		$modelData['type']		= 'g';
		$modelData['Post_id']	= $this->_getParam('gallery_id');
		$page		= new Application_Model_SinglePost($modelData);
		$HtmlHead	=	$page->getHtmlHead();
		$HtmlBody	=	$page->getHtmlBody();
		$this->view->assign('SkinVersin'	, $page->SkinVersin);
		echo $HtmlHead.$HtmlBody;
    }
    public function rtcAction()
    {
		//$request 		= $this->getRequest();
		
		if($this->_request->isXmlHttpRequest())
		{
			$modelData['Post_id']	= $this->_getParam('rtc_id');
			$singleRTC	= new Application_Model_SinglePureRTC($modelData);
			$this->_helper->json->sendJson($singleRTC->PureRTC);
			return true;
		}
		
		//$ads	= new Application_Model_Ads();	
			
		$webPageID		= '12';
		$modelData[0]	= '12';
		$modelData['type']		= 't';
		$modelData['Post_id']	= $this->_getParam('rtc_id');
		$page		= new Application_Model_SinglePost($modelData);
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$HtmlBody	=	$page->getHtmlBody();
		$this->view->assign('SkinVersin'	, $page->SkinVersin);
		echo $HtmlBody;
    }

}