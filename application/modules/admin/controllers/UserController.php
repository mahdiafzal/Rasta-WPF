<?php

class Admin_UserController extends Zend_Controller_Action
{
	protected 	$acc_level;
	var 		$DB;
	var 		$ses;
	var			$returnuri = '/admin';
	public function init()
	{
		$response 	= $this->getResponse();
		$this->ses	= new Zend_Session_Namespace('MyApp');
		$this->gethelper('viewRenderer')->view->assign('user_id',$this->ses->id); 
		
		$this->registry 	= Zend_Registry::getInstance();  
		$this->DB 			= $this->registry['front_db'];
		$this->acc_level	= $this->registry->config->admin->registeraccesslevel;
		$this->gethelper	('viewRenderer')->view->assign('acc_level', $this->acc_level);
	}
	
	public function indexAction()
	{ 
		$this->_helper->viewRenderer->setNoRender();
		$this->_redirect('/login');
	}
	public function userinfoAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$isXmlHttpRequest = $this->_request->isXmlHttpRequest();
		if(!$isXmlHttpRequest) return;
		$UserSess = new Zend_Session_Namespace('Zend_Auth');
		$usID = $usRg	= $isAdmin	= 0;
		if( is_object($UserSess->storage) )
		{
			$usID	= $UserSess->storage->id;
			//$usRg	= $UserSess->storage->user_group;
			$usRg	= $UserSess->storage->all_groups;
			$isAdmin= $UserSess->storage->is_admin;
		}
		$this->_helper->json->sendJson(array('id'=>$usID, 'roles'=>$usRg, 'isSuperAdmin'=>$isAdmin ));
	}
	public function frmloginAction()
	{
		$translate = $this->registry['translate'];
		$this->_helper->layout()->disableLayout();
		$auth		= Zend_Auth::getInstance(); 
		if	($auth->hasIdentity())
		{
			$this->_redirect($this->returnuri);
		}
		$request	= $this->getRequest();  
		$er			= $request->getparam('er');
		switch ($er)
		{
			case	 '1': $this->view->assign('description', $translate->_("b"));break;
			case	 '2': $this->view->assign('description', $translate->_("c"));break;
			case	 '3': $this->view->assign('description', $translate->_("d"));break;
			case	 '4': $this->view->assign('description', $translate->_("e"));break;
			case	 '5': $this->view->assign('description', $translate->_("f"));break;
			case	 '6': $this->view->assign('description', $translate->_("g"));break;
			case	 '7': $this->view->assign('description', $translate->_("h"));break;
			case	 '8': $this->view->assign('description', $translate->_("i"));break;
			case	 '9': $this->view->assign('description', $translate->_("j"));break;
			case	 '10': $this->view->assign('description', $translate->_("k"));break;
			case	 '11': $this->view->assign('description', $translate->_("l"));break;
			case	 '12': $this->view->assign('description', $translate->_("m"));break;
			case	 '13': $this->view->assign('description', $translate->_("n"));break;
			case	 '14': $this->view->assign('description', $translate->_("o"));break;
			default		: $this->view->assign('description',  $translate->_("a"));break;
		}
		$this->view->assign('action', "/admin/user/login");  
		$this->view->assign('translate', $translate);
	}
	public function loginAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$isXmlHttpRequest = $this->_request->isXmlHttpRequest();

		//$request		= $this->getRequest();
		$this->params	= $this->_getAllParams();
		$frmValidator	= new Application_Model_Validator();
		$userManager	= new Usermanager_Model_Usermanager;
		$captchaCode	= strtolower($this->params['captcha']);
		if ($captchaCode!=$this->ses->captchaCode)
		{
			if($isXmlHttpRequest) $this->_helper->json->sendJson(array('result'=>false, 'message'=>$this->registry['translate']->_("i") ));
			$this->_redirect('/admin/user/frmlogin/er/8');
		}


		$username		= $this->params['login_username'];
		$password		= $this->params['login_password'];
		
		// must be validate
		$dd		=array('username'=> $this->params['login_username']);
		$rule	=array('username'=> 'isEmail');
		$frmValidator->validate($dd, $rule);
		if ($frmValidator->getResult('username')==false)
		{
			if($isXmlHttpRequest)
				$this->_helper->json->sendJson(array('result'=>false, 'message'=>$this->registry['translate']->_("d") ));
			$this->_redirect( '/admin/user/frmlogin/er/3');
		}


		
		if ($password != '')
		{
			$result	= $userManager->authenticate($username, md5($password));
			if($isXmlHttpRequest)
			{
				switch ($result)
				{
					case  1 :	$response = array('result'=>true, 'message'=>'' )	;	break;
					case  0 :	$response = array('result'=>false, 'message'=>$this->registry['translate']->_("c") )	;	break;
					case -1 :	$response = array('result'=>false, 'message'=>$this->registry['translate']->_("e") )	;	break;
					case -2 :	$response = array('result'=>false, 'message'=>$this->registry['translate']->_("b") )	;	break;
					case -3 :	$response = array('result'=>false, 'message'=>$this->registry['translate']->_("j") )	;	break;
				}
				if(is_array($response))	$this->_helper->json->sendJson($response);
			}
			else
			{
				switch ($result)
				{
					case  1 :	$this->_redirect( $this->getRedirectionUrl())	;	break;
					case  0 :	$this->_redirect( '/admin/user/frmlogin/er/2')	;	break;
					case -1 :	$this->_redirect( '/admin/user/frmlogin/er/4')	;	break;
					case -2 :	$this->_redirect( '/admin/user/frmlogin/er/1')	;	break;
					case -3 :	$this->_redirect( '/admin/user/frmlogin/er/9')	;	break;
				}				
			}
		}
		else
		{
			$result		= $userManager->chkUsername($username);
			if($isXmlHttpRequest)
			{
				switch ($result)
				{
					case  1 :	
					case  0 :	
						if ($userManager->sendEmail($username)==true)
							$this->_helper->json->sendJson(array('result'=>false, 'message'=>$this->registry['translate']->_("k") )); 
						else
							$this->_helper->json->sendJson(array('result'=>false, 'message'=>$this->registry['translate']->_("h") ));
						break;
					case -1 :	$this->_helper->json->sendJson(array('result'=>false, 'message'=>$this->registry['translate']->_("e") ));	break;
					case -2 :	$this->_helper->json->sendJson(array('result'=>false, 'message'=>$this->registry['translate']->_("f") ));	break;
				}
			}
			else
			{
				switch ($result)
				{
					case  1 :	
					case  0 :	
						if ($userManager->sendEmail($username)==true)
						{
							$this->_redirect( '/admin/user/frmlogin/er/10'); 
						}
						else
						{
							$this->_redirect( '/admin/user/frmlogin/er/7'); 
						}
						break;
					case -1 :	$this->_redirect( '/admin/user/frmlogin/er/4');	break;
					case -2 :	$this->_redirect( '/admin/user/frmlogin/er/5');	break;
				}
			}
		}
	}	
	public function logoutAction()
	{
		//$this->_helper->viewRenderer->setNoRender();
		//$auth = Zend_Auth::getInstance();
		//$auth->clearIdentity();
		//Zend_Session::destroy(true);
		//$request		= $this->getRequest();
		Zend_Auth::getInstance()->clearIdentity();
		Application_Model_Session::clearStorage('all');
		$this->params	= $this->_getAllParams();
		$this->_redirect($this->getRedirectionUrl());
		die();
	}
	public function activationAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$userManager= new Usermanager_Model_Usermanager;		
		$data		= $_SERVER['REQUEST_URI'];
		$res		= $userManager->activation($data);
		switch ($res)
		{
			case  '1' :	$this->_redirect( '/admin/user/frmlogin/er/12');	break;
			case  '2' :	$this->_redirect( '/admin/user/frmlogin/er/13');	break;
			case  '3' :	$this->_redirect( '/admin/user/frmlogin/er/4');		break;
			case  '4' :	$this->_redirect( '/admin/user/frmlogin/er/1');		break;
			case  '5' :	$this->_redirect( '/admin/user/frmlogin/er/14');	break;
			case  '6' :	$this->_redirect( $this->return)				;	break;
		}

	}
	protected function getRedirectionUrl()
	{
		
		$redirecPath	= '/admin';
		if(!empty($this->ses->redirecPath) ){	$redirecPath	= $this->ses->redirecPath; unset($this->ses->redirecPath); }
		if(!empty($this->params['redir']))		$redirecPath	= $this->params['redir'];
		return $redirecPath;
	}
}	
?>
