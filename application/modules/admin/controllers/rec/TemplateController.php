<?php 
 
class Admin_TemplateController extends Zend_Controller_Action
{

    var $templateSet	= array(
						array(
								array(10,11,12,13,14),
								array(15,16,17,18,19),
								array(20,21,22,23,24),
								array(25,26,27,28,29),
								array(31,32,33,34,35),
								array(37,38,39,40,41),
								array(42,43,44,45,46),
								array(47,48,49,50,51),
								array(52,53,54,55),
								array(57,58,59,60,61),
								array(56)
								)
							);
	var $groups		= array('general','tehran','sharif','allameh tabatabai','mofid university','shahid beheshti','tarbiat modares');
	var $titles		= array('عمومی','دانشگاه تهران','دانشگاه شریف','دانشگاه علامه طباطبایی','دانشگاه مفید','دانشگاه شهید بهشتی','دانشگاه تربیت مدرس');
	public function init()
    {
        /* Initialize action controller here */
    }
	//---------------------------------------------------------------------------------------
    public function indexAction()
    {
		$this->_helper->layout()->disableLayout();
		$request 	= $this->getRequest();
		$params		= $request->getParams();
		if(empty($params['group']))    $this->_redirect('/admin/template/preview/group/1/layout/1/theme/1');
		$url	= '/admin/template/preview/group/'.$params['group'].'/layout/'.$params['layout'].'/theme/'.$params['theme'];
		$this->_redirect($url);
    }
    public function selectAction()
    {
		$request 	= $this->getRequest();
		$params		= $request->getParams();
		if( isset($params['sid']) )
		{
			$DB			= Zend_Registry::get('front_db');
			$sql		= 'select count(`skin_id`) as `cnt` from `wbs_skin` where (`wbs_id` = '. WBSiD.' or `wbs_id` = 0) and `skin_id`=' .addslashes($params['sid']);
			$result		= $DB->fetchAll($sql);
			if($result[0]['cnt']!=1)	$this->_redirect($_SERVER['HTTP_REFERER']);
			$sSkin		= $params['sid'];
		}
		else
		{
			$groupID	= $params['group']-1;
			$layoutID	= $params['layout']-1;
			$themeID	= $params['theme']-1;
	//		$env		= $params['env'];
			if(! isset($this->templateSet[$groupID][$layoutID][$themeID]) ) $this->_redirect($_SERVER['HTTP_REFERER']);
			$sSkin		= $this->templateSet[$groupID][$layoutID][$themeID];
		}
		//print_r($sSkin); die();
//		if($env!='dsh')
//		{
	   		$response	= '<script>if(window.opener!=null){window.opener.$var.tempSelect('.$sSkin.'); window.close();}else{window.location="'.$_SERVER['HTTP_REFERER'].'";}</script>';
	   		die($response);
//		}
//		else
//		{
//
//			die('sssssssssssss');	
//		}
    }
    public function previewAction()
    {
		$this->_helper->layout()->disableLayout();
		$this->_redirect('/skiner/template/frmlist');
		return true;
		$request 	= $this->getRequest();
		$params		= $request->getParams();
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		//print_r($params); die();
		//$this->authenticate();
		
		$modelData[0]	= 11;
		$groupID	= $params['group']-1;
		$layoutID	= $params['layout']-1;
		$themeID	= $params['theme']-1;
		$env		= (!empty($params['env']))?$params['env']:'';
		if(! isset($this->templateSet[$groupID][$layoutID][$themeID]) ) return false;

		$modelData[1]	= $this->templateSet[$groupID][$layoutID][$themeID];
		$modelData[2]	= $this->titles[$groupID];
		$modelData[3]	=	'<root>';
		$modelData[3]	.=	'<s id="1"><q>4</q><t>3</t></s>';
		if($groupID <= 3) $modelData[3]	.=	'<s id="2"><t>6</t></s>';
		if($groupID >  3) $modelData[3]	.=	'<s id="2"><t>7</t></s>';
		
		$modelData[3]	.=	'<s id="3"><q>4</q></s>';
		$modelData[3]	.=	'<s id="4"><t>5</t></s>';
		$modelData[3]	.=	'</root>';

		$page		= new Application_Model_Template($modelData);
		
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$HtmlBody	= $page->getHtmlBody();
		echo $HtmlBody;
    }

//    public function authenticate() 
//    {
//		$auth		= Zend_Auth::getInstance(); 
//		$user		= $auth->getIdentity();
//		if	(!$auth->hasIdentity())			$this->_redirect('/login');
//		elseif($user->wb_user_id!=WBSiD)	$this->_redirect('/admin/user/logout');
//		return true;
//	}
/*
    public function generalAction() 
    {
		$this->_helper->layout()->disableLayout();
		
		//$this->authenticate();
		
		$request 		= $this->getRequest();
		$modelData[0]	= 11;
		$layoutID	= $request->getParam('layout')-1;
		$themeID	= $request->getParam('theme')-1;
		$templateSet	= array(
						array(102,103,104,105),
						array(106,107,108,109),
						array(110,111,112,113),
						array(114,115,116,117)
							);
		if(! isset($templateSet[$layoutID][$themeID]) ) return false;
		$modelData[1]	= $templateSet[$layoutID][$themeID];
		$modelData[2]	= 'عمومی';
		$modelData[3]	=	'<root>';
		$modelData[3]	.=	'<s id="1"><q>1</q><q>4</q></s>';
		$modelData[3]	.=	'<s id="2"><t>6</t></s>';
		$modelData[3]	.=	'<s id="3"><q>4</q><t>3</t></s>';
		$modelData[3]	.=	'<s id="4"><t>5</t></s>';
		$modelData[3]	.=	'</root>';

		$page		= new Application_Model_Template($modelData);
		
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$HtmlBody	= $page->getHtmlBody();
		echo $HtmlBody;
   }
    public function tehranAction() 
    {
		$this->_helper->layout()->disableLayout();
		$this->authenticate();
		$request 		= $this->getRequest();
		$modelData[0]	= 11;
		$layoutID	= $request->getParam('layout')-1;
		$themeID	= $request->getParam('theme')-1;
		$templateSet	= array(
						array(62,63,64,65,66),
						array(67,68,69,70,71),
						array(72,73,74,75,76),
						array(77,78,79,80,81)
							);
		if(! isset($templateSet[$layoutID][$themeID]) ) return false;
		$modelData[1]	= $templateSet[$layoutID][$themeID];
		$modelData[2]	= 'دانشگاه تهران';
		$modelData[3]	=	'<root>';
		$modelData[3]	.=	'<s id="1"><q>1</q><q>4</q></s>';
		$modelData[3]	.=	'<s id="2"><t>2</t></s>';
		$modelData[3]	.=	'<s id="3"><q>4</q><t>3</t></s>';
		$modelData[3]	.=	'<s id="4"><t>5</t></s>';
		$modelData[3]	.=	'</root>';

		$page		= new Application_Model_Template($modelData);
		
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$HtmlBody	= $page->getHtmlBody();
		echo $HtmlBody;
   }
    public function sharifAction() 
    {
		$this->_helper->layout()->disableLayout();
		$auth		= Zend_Auth::getInstance(); 
		$user		= $auth->getIdentity();
		$this->authenticate();
		$request 		= $this->getRequest();
		$modelData[0]	= 11;
		$layoutID	= $request->getParam('layout')-1;
		$themeID	= $request->getParam('theme')-1;
		$templateSet	= array(
						array(82,83,84,85,86),
						array(87,88,89,90,91),
						array(92,93,94,95,96),
						array(97,98,99,100,101)
							);
		if(! isset($templateSet[$layoutID][$themeID]) ) return false;
		$modelData[1]	= $templateSet[$layoutID][$themeID];
		$modelData[2]	= 'دانشگاه شریف';
		$modelData[3]	=	'<root>';
		$modelData[3]	.=	'<s id="1"><q>1</q><q>4</q></s>';
		$modelData[3]	.=	'<s id="2"><t>2</t></s>';
		$modelData[3]	.=	'<s id="3"><q>4</q><t>3</t></s>';
		$modelData[3]	.=	'<s id="4"><t>5</t></s>';
		$modelData[3]	.=	'</root>';
	
		$page		= new Application_Model_Template($modelData);
		
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$HtmlBody	= $page->getHtmlBody();
		echo $HtmlBody;
   }
*/

}