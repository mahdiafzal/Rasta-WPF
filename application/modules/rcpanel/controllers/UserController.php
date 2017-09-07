<?php

class Rcpanel_UserController extends Zend_Controller_Action
{
	protected 	$acc_level;
	var 		$DB;
	var 		$ses;
	public function init()
	{
		Rcpanel_Model_User_User::initUser();

		$response 	= $this->getResponse();
		$this->ses	= new Zend_Session_Namespace('MyApp');
		$this->gethelper('viewRenderer')->view->assign('user_id',USRiD); 
		
		$this->registry		= Zend_registry::getInstance();
		$this->DB 			= $this->registry['front_db'];
		
		$this->acc_level	= $this->registry->config->rcpanel->registeraccesslevel_cp ;
		$this->gethelper('viewRenderer')->view->assign('acc_level',$this->acc_level);
	}
	public function  chkLogin()
	{
		$auth		= Zend_Auth::getInstance(); 
		if	(!defined('USRiD'))
		{
			$this->_redirect('/rcpanel');
		}
		else 
		{
			return $auth;
		}
	}
	public function indexAction()
	{ 
		$this->_redirect('/rcpanel');
	}
	public function frmloginAction()
	{
		$this->translate	= $this->registry['translate'];
		$this->view->assign('translate',$this->translate);
		
		$this->view->assign('title_site',$this->translate->_('y'));
		$auth		= Zend_Auth::getInstance(); 
		if	(defined('USRiD'))	$this->_redirect('/rcpanel/panel/index');
		$request	= $this->getRequest();  
		$er			= $request->getparam('er');
		switch ($er)
		{
			case	 '1':	$msg[]	= 'کلمه کاربري و رمز عبور نا معتبر است!'; break;
			case	 '2':	$msg[]	= "حساب کاربری شما هنوز فعال نشده است.<br/> باید از طریق ایمیل خود آنرا فعال کنید.";break;
			case	 '3':	$msg[]	= 'پست الکترونیکی نا معتبر است!'; break;
			case	 '4':	$msg[]	= 'شما باید از سوی مدیر سایت فعال شوید. لطفا بعدا دوباره تلاش کنید.'; break;
			case	 '5':	$msg[]	= 'شما اول باید ثبت نام کنید.'; break;
			case	 '6':	$msg[]	= 'ایمیلی مبنی بر فعال سازی شما ارسال شده است . لطفا تا پایان روز نسبت به فعال سازی حساب خود اقدام کنید.'; break;
			case	 '7':	$msg[]	= "<p>خطا در ارسال ایمیل!</p><br/> <a href='/rcpanel'>لطفا دوباره تلاش کنید </a>"; break;
			case	 '8':	$msg[]	= 'کد امنیتی اشتباه است.'; break;
			case	 '9':	$msg[]	= 'لطفا از سایت خود اقدام به ورود به صفحه ی مدیریت کنید.'; break;
			case	 '10':	$msg[]	= 'لینک ورود به ایمیل شما ارسال شد.'; break;
			case	 '11':	$msg[]	= 'شما مجاز به مشاهده این بخش نیستید. لطفا وارد حساب کاربری خود شوید.'; break;
			case	 '12':	$msg[]	= 'کد فعال سازی نا معتبر می باشد.'; break;
			case	 '13':	$msg[]	= 'شتاریخ انقضای کد فعال سازی شما به پایان رسیده است.<br/>لطفا لینک فعال سازی جدیدی دریافت کنید.'; break;
			case	 '14':	$msg[]	= 'خطا در فرایند اعتبار سنجی.'; break;
			default		:	$msg[]	= 'نام کاربري و کلمه عبور را وارد کنيد.';
		}
		$fmsg	= $this->_helper->flashMessenger->getMessages();
		if(is_array($fmsg[0]))
			if(is_array($msg))	$msg	= array_merge($msg,  $fmsg[0]);
			else				$msg	= $fmsg[0];
		$this->view->assign('msg', $msg);
	}
	public function loginAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$this->translate= $this->registry['translate'];
		$request		= $this->getRequest();
		
		$frmValidator	= new Application_Model_Validator();
		$userManager	= new Rcpanel_Model_User_User;
		
		$captchaCode	= strtolower( $request->getParam('captcha'));
		if ($captchaCode!=$this->ses->captchaCode)	$this->_redirect('/rcpanel/user/frmlogin/er/8');

		$username		= $request->getParam('login_username');
		$password		= $request->getParam('login_password');
		
		// validate username
		$dd		= array('username'=> $request->getParam('login_username'));
		$rule	= array('username'=> 'isEmail');
		$frmValidator->validate($dd, $rule);
		if ($frmValidator->getResult('username')==false)	$this->_redirect( '/rcpanel/user/frmlogin/er/3');
		
		if ($password != '')
		{
			$result	= $userManager->_authenticate($username, md5($password));
			switch ($result)
			{
				case  1 :	$this->_redirect( '/rcpanel/panel/index')			;	break;
				case  0 :	$this->_redirect( '/rcpanel/user/frmlogin/er/2')	;	break;
				case -1 :	$this->_redirect( '/rcpanel/user/frmlogin/er/4')	;	break;
				case -2 :	$this->_redirect( '/rcpanel/user/frmlogin/er/1')	;	break;
				case -3 :	$this->_redirect( '/rcpanel/user/frmlogin/er/9')	;	break;
			}
		}
		else
		{
			$result		= $userManager->_checkUsername($username);
			switch ($result)
			{
				case  1 :	
				case  0 :
					$newdata	= array('msg' => $this->translate->_('o'), 'subject' => $this->translate->_('p') ); 
					if ($userManager->_sendEmail($username, $newdata)==true)
					//if ($userManager->sendEmail($username)==true)
					{
						$this->_redirect( '/rcpanel/user/frmlogin/er/10'); 
					}
					else
					{
						$this->_redirect( '/rcpanel/user/frmlogin/er/7'); 
					}
					break;
				case -1 :	$this->_redirect( '/rcpanel/user/frmlogin/er/4');	break;
				case -2 :	$this->_redirect( '/rcpanel/user/frmlogin/er/5');	break;
			}
		}
	}
	public function regsuccessAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->translate	= $this->registry['translate'];
		$this->view->assign('translate',$this->translate);
	}
	public function frmreguserAction()
	{ 
		$this->translate	= $this->registry['translate'];
		$this->view->assign('translate',$this->translate);
		$this->view->assign('title_site',$this->translate->_('u'));

		if ($this->acc_level=='just_admin')
			$this->_redirect('/rcpanel/user/frmlogin/er/11');

		$request	= $this->getRequest();  		
		if ((isset($this->ses->userRegData)) and ($request->getParam('er')!=''))
		{
			$userData= $this->ses->userRegData;		
		}
		else 
		{
			$userData= array(	'first_name'=> "",
								'last_name'	=> "",
								'username'	=> ""
							);
		}
		switch ($request->getParam('er'))
		{
			case	 '1':	$msg[]	= 'کلمه کاربري و رمز عبور نا معتبر است!'; break;
			case	 '2':	$msg[]	= "این کاربر قبلا ثبت نام شده است.<br/>ایمیل دیگری وارد کنید"; break;
			case	 '3':	$msg[]	= 'کد امنیتی اشتباه است'; break;
			case	 '4':	$msg[]	= "<p>خطا در ارسال ایمیل!</p><br/> <a href='/rcpanel/user/frmreguser'>لطفا دوباره تلاش کنید </a>";break;
			case	 '5':	$msg[]	= 'هر دو رمز باید یکسان و بیشتر از پنج کاراکتر باشند!'; break;
			default		:	$msg[]	=  'مشخصات خود را وارد کنيد';
		}
		$this->view->assign	('data',$userData);
		
		$fmsg	= $this->_helper->flashMessenger->getMessages();
		if(is_array($fmsg[0]))
			if(is_array($msg))	$msg	= array_merge($msg,  $fmsg[0]);
			else				$msg	= $fmsg[0];
		
		$this->view->assign	('msg', $msg);		
	}
	public function reguserAction()
	{ 
		$this->_helper->viewRenderer->setNoRender();
		$this->translate	= $this->registry['translate'];
		$request		= $this->getRequest();
		$frmValidator	= new Application_Model_Validator;
		//$userManager	= new Usermanager_Model_Usermanagercp;		
		$data = array('first_name'	=> $request->getParam('first_name'),
					  'last_name'	=> $request->getParam('last_name'),
					  'username'	=> $request->getParam('username')
					  );
		$this->ses->userRegData= $data;

		$captchaCode	= strtolower($request->getParam('captcha'));
		if ($captchaCode!=$this->ses->captchaCode)	$this->_redirect('/rcpanel/user/frmreguser/er/3');

		$pass1			= $request->getParam('password_1');
		$pass2			= $request->getParam('password_2'); 
		$res			= $frmValidator	->chkPass($pass1,$pass2);
		switch ($res)
		{
			case 'inCorrect' : 
			case 'less'		 :	$this->_redirect('/rcpanel/user/frmreguser/er/5')		; break;
			case 'correct'	 : 	$data['password']	= md5($request->getParam('password_1'))	; break;
			case 'empty'	 : //echo 'empty' ; break;
		}
		//validate 
		$rule=array	(
						'first_name'	=>'isFarsiLatin',
						'last_name'		=>'isFarsiLatin',
						'username'		=>'isEmail',
						'password'		=>'tNull'
					);
		$frmValidator->validate($data, $rule);
		if ($frmValidator->getResult('username')==false or $frmValidator->getResult('password_1')==false)	$this->_redirect('/rcpanel/user/frmreguser/er/1');

		//end validate 
		try 
		{
			if ($this->acc_level=='eventual_users')
			{
				//register user
				$data['is_active']='-1';
				$this->DB->insert('host_users', $data);
				$msg[]	= 'ثبت موقت شما با موفقیت انجام شد';
				$msg[]	= 'پس از تأیید مدیر سایت ایمیلی مبنی بر فعال سازی حساب کاربری شما ارسال خواهد شد. مهلت استفاده از لینک فعال سازی یک روز می باشد.';
				$this->_helper->flashMessenger->addMessage($msg);
				$this->_redirect('/rcpanel/user/frmlogin');
			}
			else 
			{
				$data['is_active']='0';
				$newdata	= array('msg' => $this->translate->_('o'), 'subject' => $this->translate->_('p') ); 
				if (Rcpanel_Model_User_User::sendEmail($data['username'], $newdata)==true)
				{
					//register user
					$this->DB->insert('host_users', $data);
					$msg[]	= 'ثبت موقت شما با موفقیت انجام شد';
					$msg[]	= 'ایمیلی مبنی بر فعال سازی حساب کاربری شما ارسال شده است. لطفا تا پایان روز نسبت به فعال سازی حساب خود اقدام کنید';
					$this->_helper->flashMessenger->addMessage($msg);
					$this->_redirect('/rcpanel/user/frmlogin');
				}
				else
				{ 
					$this->_redirect( '/rcpanel/user/frmreguser/er/4'); 
				}
			}
		}
		catch (Zend_Exception $e)
		{
			//echo "Db error : " . $e->getMessage() . "\n";  
			$this->_redirect('/rcpanel/user/frmreguser/er/2');
		}
	}
	public function activationAction()
	{
		$this->_helper->viewRenderer->setNoRender();

		$data		= $_SERVER['REQUEST_URI'];
		$res		= Rcpanel_Model_User_User::activation($data);
		switch ($res)
		{
			case  '1' :	$this->_redirect( '/rcpanel/user/frmlogin/er/12');	break;
			case  '2' :	$this->_redirect( '/rcpanel/user/frmlogin/er/13');	break;
			case  '3' :	$this->_redirect( '/rcpanel/user/frmlogin/er/4');	break;
			case  '4' :	$this->_redirect( '/rcpanel/user/frmlogin/er/1');	break;
			case  '5' :	$this->_redirect( '/rcpanel/user/frmlogin/er/14');	break;
			case  '6' :	$this->_redirect( '/rcpanel/panel/index')		;	break;
		}
	}
	public function logoutAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		Zend_Session::destroy(true);
		$this->_redirect('/rcpanel');
	}
	public function frmeditAction()
	{	
		$this->translate	= $this->registry['translate'];
		$this->view->assign('translate',$this->translate);

		$this->view->assign('title_site'		, 'ویرایش مشخصات شخصی');	
		$auth		= $this->chkLogin();
		$request 	= $this->getRequest();		
		
		$userinfo			= $auth->getIdentity();
		$data['first_name']	= $userinfo->first_name;
		$data['last_name']	= $userinfo->last_name;
		$data['username']	= $userinfo->username;		

		switch ($request->getParam('er'))
		{
			case	 '1':	$msg[]	= 'هر دو رمز باید یکسان و بیشتر از پنج کاراکتر باشند!'; break;
			case	 '2':	$msg[]	= "ایمیل وارد شده نا معتبر است.<br/>ایمیل دیگری وارد کنید"; break;
			case	 '3':	$msg[]	= "<p>خطا در ارسال ایمیل!</p>لطفا دوباره تلاش کنید "; break;
			case	 '4':	$msg[]	= "این کاربر قبلا ثبت نام شده است.<br/>ایمیل دیگری وارد کنید"; break;
			default		:	$msg[]	= 'لطفا تغییرات را در فرم زیر اعمال کنید';
		}
		$this->view->assign('msg',$msg);
		$this->view->assign('data',$data);
		$this->view->assign('action',"/rcpanel/user/edit");
		$this->view->assign('title','ویرایش');
		$this->view->assign('label_fname','نام:');
		$this->view->assign('label_lname','نام خانوادگی:');	
		$this->view->assign('label_uname','نام کاربری:');	
		$this->view->assign('label_pass_1','کلمه عبور:');	
		$this->view->assign('label_pass_2','تکرار کلمه عبور:');	
		$this->view->assign('label_submit','  ویرایش  ');
	}  
	public function editAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->translate	= $this->registry['translate'];

		$auth		= $this->chkLogin();
		$DB 		= $this->DB;
		$userManager= new Rcpanel_Model_User_User;
		$userinfo	= $auth->getIdentity();
		$username	= $userinfo->username;
		$pass		= $userinfo->password;
		$id			= $userinfo->id;
		//validator
		$request 		= $this->getRequest();
		$frmValidator	= new Application_Model_Validator;
		
		$pass1			= $request->getParam('password_1');
		$pass2			= $request->getParam('password_2'); 
		$res			= $frmValidator	->chkPass($pass1,$pass2);
		switch ($res)
		{
			case 'inCorrect' : 
			case 'less'		 : $this->_redirect('/rcpanel/user/frmedit/er/1'); break;
			case 'correct'	 : 
				$data['password']	= md5($request->getParam('password_1'));
				$pass				= $data['password'];
				break;
			case 'empty'	 : //echo 'empty' ; break;
		}
		
		$dd		=array('username'=> $request->getParam('username'));
		$rule	=array('username'=> 'isEmail');
		$frmValidator->validate($dd, $rule);
		//end validator
		$data ['first_name']= $request->getParam('first_name');
		$data ['last_name']	= $request->getParam('last_name');
		if ($username == $request->getParam('username'))
		{
			$this->DB	->update('host_users', $data,"id=".$id);	
			$res	= $this->DB->fetchRow('select `wb_id` from `wbs_profile` where `host_id`='.$id);
			if($res)	$this->DB->update('users', $data,"wb_user_id=".$res['wb_id']." AND is_admin=1");
			
			$msg[]	= 'اصلاح مشخصات با موفقیت انجام شد';
			$this->_helper->flashMessenger->addMessage($msg);
			$auth->clearIdentity();
			$userManager->_authenticate($username,$pass, true);
			$this->_redirect('/rcpanel/panel/');
		}
		else 
			if ($frmValidator->getResult('username')==true)//for new email
			{	
				try 
				{
					$data['username']	= $request->getParam('username');
					$newdata	= array('msg' => $this->translate->_('o'), 'subject' => $this->translate->_('p') ); 
					if ($userManager->_sendEmail($data['username'], $newdata)==true)
					//if ($userManager->sendEmail($data['username'])==true)
					{ 
						if ($id	!= '1')	$data['is_active']	= '0';
						$this->DB	->update('host_users', $data,"id=".$id);
						$res	= $this->DB->fetchRow('select `wb_id` from `wbs_profile` where `host_id`='.$id);
						if($res)	$this->DB->update('users', $data,"wb_user_id=".$res['wb_id']." AND is_admin=1");
						$auth		->clearIdentity();
						$userManager->_authenticate($data['username'], $pass, true);
	
						$msg[]	= 'اصلاح مشخصات با موفقیت انجام شد';
						$msg[]	= 'پس از خروج باید باستفاده از لینک فعال سازی که به ایمیل شما ارسال شده است اقدام به فعال سازی حساب کاربری خود نمایید';
						$this->_helper->flashMessenger->addMessage($msg);
						$this->_redirect( '/rcpanel/panel/'); 
					}
					else
					{ 
						$this->_redirect( '/rcpanel/user/frmedit/er/3'); 
					}
				}
				catch(Zend_exception $e)
				{
					$this->_redirect( '/rcpanel/user/frmedit/er/4');
				}
			}
			else
			{
				$this->_redirect('/rcpanel/user/frmedit/er/2');
			}
	}
}	
?>
