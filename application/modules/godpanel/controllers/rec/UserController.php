<?php

class Godpanel_UserController extends Zend_Controller_Action
{
	protected 	$acc_level;
	var 		$DB;
	var 		$ses;
	public function init()
	{
		Godpanel_Model_User_User::initUser();
		if(!defined('USRiD') or USRiD!=='1')	die(Application_Model_Messages::message(404));
		// Render  for every action
		$response 	= $this->getResponse();
		$this->ses	= new Zend_Session_Namespace('MyApp');
		
		$this->gethelper('viewRenderer')->view->assign('user_id',USRiD); 
		
		$auth	= Zend_Auth::getInstance(); 
		if	($auth->hasIdentity())
		{
			$response->insert('menu',$this->view->render('menu.phtml'));
		}
		
		$this->registry		= Zend_registry::getInstance();
		$this->DB 			= $this->registry['front_db'];
		$this->acc_level	= 'just_admin' ;

		$this->gethelper	('viewRenderer')->view->assign('acc_level',$this->acc_level);
		$this->gethelper	('viewRenderer')->view->assign('title_site','کنترل پنل');
	}
	//-------------------  
	public function baseUrl()
		{
			return "";
		}
	//-------------------  
	public function  chkLogin()
	{
		$auth		= Zend_Auth::getInstance(); 
		if	(!$auth->hasIdentity())
		{
			$this->_redirect('/Rcpanel');
		}
		else 
		{
			return $auth;
		}
	}
	//--------------------------
	public function indexAction()
		{ 
			$this->_redirect('/Rcpanel');
		}
	//-------------------  
	public function frmloginAction()
	{
		$this->_helper->layout()->disableLayout();
		$auth		= Zend_Auth::getInstance(); 
		if	($auth->hasIdentity())
			{
				$this->_redirect('/godpanel/panel/index');
			}
		$request	= $this->getRequest();  
		$er			= $request->getparam('er');
		switch ($er)
		{
			case	 '1': $this->view->assign('description', 'کلمه کاربري و رمز عبور نا معتبر است!'); break;
			case	 '2': $this->view->assign('description',  "حساب کاربری شما هنوز فعال نشده است.<br/> باید از طریق ایمیل خود آنرا فعال کنید");break;
			case	 '3': $this->view->assign('description', 'پست الکترونیکی نا معتبر است!'); break;
			case	 '4': $this->view->assign('description', 'شما باید از سوی مدیر پورتال فعال شوید. لطفا بعدا دوباره تلاش کنید'); break;
			case	 '5': $this->view->assign('description', 'شما اول باید ثبت نام کنید'); break;
			case	 '6': $this->view->assign('description', 'ایمیلی مبنی بر فعال سازی شما ارسال شده است . لطفا تا پایان روز نسبت به فعال سازی حساب خود اقدام کنید'); break;
			case	 '7': $this->view->assign('description', "<p>خطا در ارسال ایمیل!</p><br/> <a href='".$this->BaseUrl()."/Rcpanel'>لطفا دوباره تلاش کنید </a>"); break;
			case	 '8': $this->view->assign('description', 'کد امنیتی اشتباه است'); break;
			case	 '9': $this->view->assign('description', 'لطفا از پورتال خود اقدام به ورود به صفحه ی مدیریت کنید.'); break;
			case	 '10': $this->view->assign('description', 'لینک ورود به ایمیل شما ارسال شد.'); break;
			case	 '11': $this->view->assign('description', 'شما مجاز به مشاهده این بخش نیستید. لطفا وارد حساب کاربری خود شوید.'); break;
			case	 '12': $this->view->assign('description', 'کد فعال سازی نا معتبر می باشد.'); break;
			case	 '13': $this->view->assign('description', 'شتاریخ انقضای کد فعال سازی شما به پایان رسیده است.<br/>لطفا لینک فعال سازی جدیدی دریافت کنید.'); break;
			case	 '14': $this->view->assign('description', 'خطا در فرایند اعتبار سنجی.'); break;
			default		: $this->view->assign('description',  '<div style="color:#000">لطفا کلمه کاربري و رمز عبور را وارد کنيد</div>');
		}
		$this->view->assign('action', "/godpanel/user/login");  
		$this->view->assign('title', 'ورود به کنترل پنل پورتال');
		$this->view->assign('username', 'نام کاربری');	
		$this->view->assign('password', 'کلمه عبور');	
	}
	//-------------------  
	public function loginAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$request		= $this->getRequest();
		$frmValidator	= new Application_Model_Validator();
		$userManager	= new Usermanager_Model_Usermanagercp;
		$captchaCode	= strtolower( $request->getParam('captcha'));
		if ($captchaCode!=$this->ses->captchaCode)
		{
			$this->_redirect('/godpanel/user/frmlogin/er/8');
		}

		$username		= $request->getParam('login_username');
		$password		= $request->getParam('login_password');
		
		// must be validate
		$dd		=array('username'=> $request->getParam('login_username'));
		$rule	=array('username'=> 'isEmail');
		$frmValidator->validate($dd, $rule);
		if ($frmValidator->getResult('username')==false)
		{
			$this->_redirect( '/godpanel/user/frmlogin/er/3');
		}
		
		if ($password != '')
		{
			$result	= $userManager->authenticate($username, md5($password));
			switch ($result)
			{
				case  1 :	$this->_redirect( '/godpanel/panel/index')			;	break;
				case  0 :	$this->_redirect( '/godpanel/user/frmlogin/er/2')	;	break;
				case -1 :	$this->_redirect( '/godpanel/user/frmlogin/er/4')	;	break;
				case -2 :	$this->_redirect( '/godpanel/user/frmlogin/er/1')	;	break;
				case -3 :	$this->_redirect( '/godpanel/user/frmlogin/er/9')	;	break;
			}
		}
		else
		{
			$result		= $userManager->chkUsername($username);
			switch ($result)
			{
				case  1 :	
				case  0 :	
					if ($userManager->sendEmail($username)==true)
					{
						$this->_redirect( '/godpanel/user/frmlogin/er/10'); 
					}
					else
					{
						$this->_redirect( '/godpanel/user/frmlogin/er/7'); 
					}
					break;
				case -1 :	$this->_redirect( '/godpanel/user/frmlogin/er/4');	break;
				case -2 :	$this->_redirect( '/godpanel/user/frmlogin/er/5');	break;
			}
		}
	}
	//-------------------  
	//	public function errpageAction()
	//	{ 
	//		$this->_helper->layout()->disableLayout();
	//		$request	= $this->getRequest();  		
	//		$this->view->assign('title_site'		, 'خطای دسترسی');	
	//		$this->view->assign('title', 'خطای مجوز دسترسی');
	//		switch ($request->getParam('er'))
	//		{
	//			case	 '1': $this->view->assign('description', 'شما مجاز به مشاهده این بخش نیستید.'); break;
	//		}
	//	}
		//-------------------  
	//	public function userpageAction()
	//		{
	//			$auth		= $this->chkLogin();
	//			$this->_redirect( '/godpanel/panel/');
	//			$user		= $auth->getIdentity();
	//			$id			= $user->id;
	//			$first_name	= $user->first_name;
	//			$last_name	= $user->last_name;
	//			$username	= $user->username;
	//			
	//			$this->view->assign('first_name', $first_name);
	//			$this->view->assign('last_name',$last_name);
	//			$this->view->assign('user_id',  $id);
//		}
	//--------------------------
	public function regsuccessAction()
	{
		$this->_helper->layout()->disableLayout();
	}
	//------------------- 				
	public function frmreguserAction()
	{ 
		$this->view->assign('title_site'		, 'ثبت کاربر جدید');	
		$auth		= Zend_Auth::getInstance(); 
		if ($this->acc_level=='just_admin')
		{
			if ($auth->hasIdentity())
			{
//				if (!$this->ses->isAdmin)
//				{
//					$this->_redirect('/godpanel/user/frmlogin/er/11');
//				}
			}
			else
			{
				$this->_redirect('/godpanel/user/frmlogin/er/11');
			}
		}

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
				case	 '1': $this->gethelper('viewRenderer')->view->assign('description', 'کلمه کاربري و رمز عبور نا معتبر است!'); break;
				case	 '2': $this->gethelper('viewRenderer')->view->assign('description',  "این کاربر قبلا ثبت نام شده است.<br/>ایمیل دیگری وارد کنید"); break;
				case	 '3': $this->gethelper('viewRenderer')->view->assign('description', 'کد امنیتی اشتباه است'); break;
				case	 '4': $this->gethelper('viewRenderer')->view->assign('description', "<p>خطا در ارسال ایمیل!</p><br/> <a href='".$this->BaseUrl()."/godpanel/user/frmreguser'>لطفا دوباره تلاش کنید </a>");break;
				case	 '5': $this->gethelper('viewRenderer')->view->assign('description', 'هر دو رمز باید یکسان و بیشتر از پنج کاراکتر باشند!'); break;
				//default		: $this->gethelper('viewRenderer')->view->assign('description',  'لطفا مشخصات کاربر جدید را وارد کنيد');
			}
			//$this->view->assign	('action', "/godpanel/user/reguser");  
			$this->gethelper('viewRenderer')->view->assign	('title', 'فرم ثبت کاربر جدید');
			$this->gethelper('viewRenderer')->view->assign	('data',$userData);
			$this->gethelper('viewRenderer')->view->assign	('msg'	, $this->_helper->flashMessenger->getMessages());		

		$response 	= $this->getResponse();
		if ($auth->hasIdentity())
		{
			$response->insert('registeruser',$this->view->render('frmregnewuser2.phtml'));
		}
		else
		{
			$this->_helper->layout()->disableLayout();
			$response->insert('registeruser',$this->view->render('frmregnewuser1.phtml'));
		}
	}
	//--------------------------
	public function reguserAction()
	{ 
		$this->_helper->viewRenderer->setNoRender();
		$request		= $this->getRequest();
		$frmValidator	= new Application_Model_Validator;
		$userManager	= new Usermanager_Model_Usermanagercp;		
		$data = array('first_name'	=> $request->getParam('first_name'),
					  'last_name'	=> $request->getParam('last_name'),
					  'username'	=> $request->getParam('username')
					  );
		$this->ses->userRegData= $data;

		$captchaCode	= strtolower($request->getParam('captcha'));
		if ($captchaCode!=$this->ses->captchaCode)
		{
			$this->_redirect('/godpanel/user/frmreguser/er/3');
		}

		$pass1			= $request->getParam('password_1');
		$pass2			= $request->getParam('password_2'); 
		$res			= $frmValidator	->chkPass($pass1,$pass2);
		switch ($res)
		{
			case 'inCorrect' : 
			case 'less'		 :	$this->_redirect('/godpanel/user/frmreguser/er/5')		; break;
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
		if ($frmValidator->getResult('username')==false or $frmValidator->getResult('password_1')==false)
			{
				$this->_redirect('/godpanel/user/frmreguser/er/1');
			}
		//end validate 
		try 
		{
			if ($this->acc_level=='eventual_users')
			{
				//register user
				$data['is_active']='-1';
				$this->DB->insert('host_users', $data);
				if (defined('USRiD'))
				{
					$this->_helper->flashMessenger->addMessage('ثبت موقت شما با موفقیت انجام شد');
					$this->_helper->flashMessenger->addMessage('ایمیلی مبنی بر فعال سازی شما ارسال شده است . لطفا تا پایان روز نسبت به فعال سازی حساب خود اقدام کنید');
					$this->_redirect('/godpanel/panel/index');
				}
				else
				{
					$this->_redirect( '/godpanel/user/regsuccess'); 
				}
			}
			else 
			{
				$data['is_active']='0';
				if ($userManager->sendEmail($data['username'])== true)
				{
					//register user
					$this->DB->insert('host_users', $data);
					if (defined('USRiD'))
					{
						$this->_helper->flashMessenger->addMessage('ثبت موقت شما با موفقیت انجام شد');
						$this->_helper->flashMessenger->addMessage('ایمیلی مبنی بر فعال سازی شما ارسال شده است . لطفا تا پایان روز نسبت به فعال سازی حساب خود اقدام کنید');
						$this->_redirect('/godpanel/panel/index');
					}
					else
					{
						$this->_redirect( '/godpanel/user/regsuccess'); 
					}
				}
				else
				{ 
					$this->_redirect( '/godpanel/user/frmreguser/er/4'); 
				}
			}
		}
		catch (Zend_Exception $e)
		{
			//echo "Db error : " . $e->getMessage() . "\n";  
			$this->_redirect('/godpanel/user/frmreguser/er/2');
		}
	}
	//--------------------------
	public function activationAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$userManager= new Usermanager_Model_Usermanagercp;		
		$data		= $_SERVER['REQUEST_URI'];
		$res		= $userManager->activation($data);
		switch ($res)
		{
			case  '1' :	$this->_redirect( '/godpanel/user/frmlogin/er/12');	break;
			case  '2' :	$this->_redirect( '/godpanel/user/frmlogin/er/13');	break;
			case  '3' :	$this->_redirect( '/godpanel/user/frmlogin/er/4');	break;
			case  '4' :	$this->_redirect( '/godpanel/user/frmlogin/er/1');	break;
			case  '5' :	$this->_redirect( '/godpanel/user/frmlogin/er/14');	break;
			case  '6' :	$this->_redirect( '/godpanel/panel/index')		;	break;
		}
//		$res		= $userManager->activation($data);
//		if ($res)
//		{
//			$this->_redirect( '/godpanel/panel/index');
//		}
//		else 
//		{
//			$this->_redirect( '/godpanel/user/frmlogin/er/1');
//		}
	}
	//-------------------  
	public function logoutAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();
		Zend_Session::destroy(true);
		$this->_redirect('/Rcpanel');
	}
	//-------------------  
	public function frmeditAction()
	{	
		$this->view->assign('title_site'		, 'ویرایش مشخصات شخصی');	
		$auth		= $this->chkLogin();
		//$DB 		= $this->DB;
		$request 	= $this->getRequest();		
		
		$userinfo			= $auth->getIdentity();
		$data['first_name']	= $userinfo->first_name;
		$data['last_name']	= $userinfo->last_name;
		$data['username']	= $userinfo->username;		

		switch ($request->getParam('er'))
		{
			case	 '1': $this->view->assign('description', 'هر دو رمز باید یکسان و بیشتر از پنج کاراکتر باشند!'); break;
			case	 '2': $this->view->assign('description',  "ایمیل وارد شده نا معتبر است.<br/>ایمیل دیگری وارد کنید"); break;
			case	 '3': $this->view->assign('description', "<p>خطا در ارسال ایمیل!</p>لطفا دوباره تلاش کنید "); break;
			case	 '4': $this->view->assign('description',  "این کاربر قبلا ثبت نام شده است.<br/>ایمیل دیگری وارد کنید"); break;
			default		: $this->view->assign('description','لطفا تغییرات را در فرم زیر اعمال کنید');
		}
		$this->view->assign('data',$data);
		$this->view->assign('action',"/godpanel/user/edit");
		$this->view->assign('title','ویرایش');
		$this->view->assign('label_fname','نام:');
		$this->view->assign('label_lname','نام خانوادگی:');	
		$this->view->assign('label_uname','نام کاربری:');	
		$this->view->assign('label_pass_1','کلمه عبور:');	
		$this->view->assign('label_pass_2','تکرار کلمه عبور:');	
		$this->view->assign('label_submit','  ویرایش  ');
	}  
	//-------------------  
	public function editAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$auth		= $this->chkLogin();
		$DB 		= $this->DB;
		$userManager= new Usermanager_Model_Usermanagercp;
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
			case 'less'		 : $this->_redirect('/godpanel/user/frmedit/er/1'); break;
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
					$res= $this->DB->fetchRow('select `wb_id` from `wbs_profile` where `host_id`='.$id);
					if($res){$this->DB->update('users', $data,"wb_user_id=".$res['wb_id']." AND is_admin=1");}
				$this->_helper->flashMessenger->addMessage('اصلاح مشخصات با موفقیت انجام شد');
				$auth->clearIdentity();
				$userManager->authenticate2($username,$pass);
				$this->_redirect('/godpanel/panel/');
				//echo $res;
			}
		else if ($frmValidator->getResult('username')==true)//for new email
			{	
				try 
				{
					$data['username']	= $request->getParam('username');
					if ($userManager->sendEmail($data['username'])==true)
					{ 
						if ($id	!= '1'){$data['is_active']	= '0';}
						$this->DB	->update('host_users', $data,"id=".$id);
							$res= $this->DB->fetchRow('select `wb_id` from `wbs_profile` where `host_id`='.$id);
							if($res){$this->DB->update('users', $data,"wb_user_id=".$res['wb_id']." AND is_admin=1");}
						$auth		->clearIdentity();
						$userManager->authenticate2($data['username'],$pass);
						//print_r($res);
						$this->_helper->flashMessenger->addMessage('اصلاح مشخصات با موفقیت انجام شد');
						$this->_helper->flashMessenger->addMessage('پس از خروج باید باستفاده از لینک فعال سازی که به ایمیل شما ارسال شده است اقدام به فعال سازی خود نمایید');
						$this->_redirect( '/godpanel/panel/'); 
					}
					else
					{ 
						$this->_redirect( '/godpanel/user/frmedit/er/3'); 
					}
				}
				catch(Zend_exception $e)
				{
					$this->_redirect( '/godpanel/user/frmedit/er/4');
				}
			}
		else
			{
				$this->_redirect('/godpanel/user/frmedit/er/2');
			}
	}
	//--------------------------
	public function frmeditusersAction()
	{
		$this->view->assign('title_site'		, 'ویرایش مشخصات کاربران');	
//		if	((!$this->ses->isAdmin) and (!isset($id)))
//			{
//				$this->_redirect('/Rcpanel');
//			}
		$request 	= $this->getRequest();
		$id			= $request->getParam('id');
		
		switch ($request->getParam('er'))
		{
			case	 '1': $this->view->assign('description', 'هر دو رمز باید یکسان و بیشتر از پنج کاراکتر باشند!'); break;
			case	 '2': $this->view->assign('description',  "ایمیل وارد شده نا معتبر است.<br/>ایمیل دیگری وارد کنید"); break;
			case	 '3': $this->view->assign('description', "<p>خطا در ارسال ایمیل!</p>لطفا دوباره تلاش کنید "); break;
			case	 '4': $this->view->assign('description',  "این کاربر قبلا ثبت نام شده است.<br/>ایمیل دیگری وارد کنید"); break;
			default		: $this->view->assign('description','لطفا تغییرات را در فرم زیر اعمال کنید');
		}

		$DB			= $this->DB;	
		$sql 		= "SELECT * FROM `host_users` WHERE id='".addslashes($id)."'";
		$result 	= $DB->fetchRow($sql);
		if ($result)
		{
			$this->view->assign('data',$result);
			$this->view->assign('action',"/godpanel/user/editusers");
			$this->view->assign('title','ویرایش');
			$this->view->assign('label_fname','نام:');
			$this->view->assign('label_lname','نام خانوادگی:');	
			$this->view->assign('label_uname','نام کاربری:');	
			$this->view->assign('label_pass_1','کلمه عبور:');	
			$this->view->assign('label_pass_2','تکرار کلمه عبور:');	
			$this->view->assign('label_submit','  ویرایش  ');
		}
		else
		{
			$this->_redirect('/godpanel/panel/index');
		}
	}
	//-------------------  
	public function editusersAction()
	{
		$this->_helper->viewRenderer->setNoRender();
//		if	((!$this->ses->isAdmin) and (!isset($id)))
//		{
//			$this->_redirect('/Rcpanel');
//		}
		$request 	= $this->getRequest();
		$id			= $request->getParam('id');
		$sql 		= "SELECT * FROM `host_users` WHERE id='".addslashes($id)."'";
		$result 	= $this->DB->fetchRow($sql);
		if ($result)
		{
			$username	= $result['username'];
			$pass		= $result['password'];
			$is_active	= $result['is_active'];
		}
		else
		{
			$this->_redirect('/godpanel/user/frmlistuser');
		}

		$userManager	= new Usermanager_Model_Usermanagercp;
		//validator
		$frmValidator	= new Application_Model_Validator;
		$pass1			= $request->getParam('password_1');
		$pass2			= $request->getParam('password_2'); 
		$res			= $frmValidator	->chkPass($pass1,$pass2);
		switch ($res)
		{
			case 'inCorrect' : 
			case 'less'		 : $this->_redirect('/godpanel/user/frmeditusers/id/'.$id.'/er/1'); break;
			case 'correct'	 : 
				$data['password']	= md5($request->getParam('password_1'));
				$pass				= $data['password'];
				break;
			case 'empty'	 : 
		}
		$dd		=array('username'=> $request->getParam('username'));
		$rule	=array('username'=> 'isEmail');
		$frmValidator->validate($dd, $rule);
		//end validator
		
		$data ['first_name']= $request->getParam('first_name');
		$data ['last_name']	= $request->getParam('last_name');
		
		if ($username == $request->getParam('username'))
		{
			$this->DB->update('host_users', $data,"id ='".addslashes($id)."'");
				$res= $this->DB->fetchRow('select `wb_id` from `wbs_profile` where `host_id`='.$id);
				if($res){$this->DB->update('users', $data,"wb_user_id=".$res['wb_id']." AND is_admin=1");}
			$this->_helper->flashMessenger->addMessage('اصلاح مشخصات با موفقیت انجام شد');
			$this->_redirect('/godpanel/panel/index');
		}
		else if ($frmValidator->getResult('username')==true)  //for new email
		{	
			try
			{
				$data['username']	= $request->getParam('username');
				if($is_active != '-1')
				{
					if ($userManager->sendEmail($data['username'])==true)
					{ 
						$data ['is_active']	= '0';	
						$this->DB->update('host_users', $data,"id = '".addslashes($id)."'");
							$res= $this->DB->fetchRow('select `wb_id` from `wbs_profile` where `host_id`='.$id);
							if($res){$this->DB->update('users', $data,"wb_user_id=".$res['wb_id']." AND is_admin=1");}
						$this->_helper->flashMessenger->addMessage('اصلاح مشخصات با موفقیت انجام شد');
						$this->_helper->flashMessenger->addMessage('پس از خروج باید باستفاده از لینک فعال سازی که به ایمیل شما ارسال شده است اقدام به فعال سازی خود نمایید');
						$this->_redirect('/godpanel/panel/index');
					}
					else
					{ 
						$this->_redirect( '/godpanel/user/frmeditusers/id/'.$id.'/er/3'); 
					}
				}
				else
				{
					$this->DB	->update('host_users', $data,"id = '".addslashes($id)."'");
						$res= $this->DB->fetchRow('select `wb_id` from `wbs_profile` where `host_id`='.$id);
						if($res){$this->DB->update('users', $data,"wb_user_id=".$res['wb_id']." AND is_admin=1");}
					$this->_helper->flashMessenger->addMessage('اصلاح مشخصات با موفقیت انجام شد');
					$this->_redirect('/godpanel/panel/index');
				}
			}
			catch (Zend_Exception $e)
			{
				$this->_redirect( '/godpanel/user/frmeditusers/id/'.$id.'/er/4');
 			}
		}
		else
		{
			$this->_redirect('/godpanel/user/frmeditusers/id/'.$id.'/er/2');
		}
	}
	//--------------------------
//	public function listAction()
//	{
//		$this->view->assign('title_site'		, 'لیست کاربران');	
//		if (!$this->ses->isAdmin)
//		{
//			$this->_redirect('/Rcpanel');
//		}
//		$userManager= new Usermanager_Model_Usermanagercp;		
//		$result		=$userManager->listing();
//
//		$this->view->assign('title','لیست کاربران');
//		$this->view->assign('datas',$result);		
//	}



	//--------------------------
	public function frmlistuserAction()
	{
		$this->view->assign('title_site', 'لیست کاربران');	
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}

		if ($this->getRequest()->isPost())
		{
			$txt_family			= $this->getRequest()->getparam('txt_family');
			$txt_username		= $this->getRequest()->getparam('txt_username');
			$txt_sitename		= $this->getRequest()->getparam('txt_sitename');
			$txt_siteurl		= $this->getRequest()->getparam('txt_siteurl');
			$sel_state_user		= $this->getRequest()->getparam('sel_state_user');
			$sel_state_site		= $this->getRequest()->getparam('sel_state_site');
			$sel_count			= $this->getRequest()->getparam('sel_count');
			$start				= $this->getRequest()->getparam('start');
			$order_by			= $this->getRequest()->getparam('order_by');
			$asc_desc			= $this->getRequest()->getparam('asc_desc');
			$sel_state_site_of_users= $this->getRequest()->getparam('sel_state_site_of_users');	
		}
		else
		{
			$txt_family			= '';
			$txt_username		= '';
			$txt_sitename		= '';
			$txt_siteurl		= '';
			$sel_state_user		= 'all';
			$sel_state_site		= 'all';			
			$sel_count			= 20;
			$start				= 0;
			$order_by			= 'id';
			$asc_desc			= 'DESC';
			$sel_state_site_of_users= 'all';
			
		}
		//=============
		//print_r($_POST);
		$sql_base	= "select 
							`host_users`.`id`,CONCAT(`host_users`.`first_name`,' ',`host_users`.`last_name`) as name ,`host_users`.`username`
							,`host_users`.`is_active`,`wbs_profile`.`wb_id`,`wbs_profile`.`wb_status`,`wbs_profile`.`wb_title`,`wbs_profile`.`wb_description`
					from `host_users` left join `wbs_profile` on (`wbs_profile`.`host_id`=`host_users`.`id`) where (1=1) ";
		$sql="";
		//----name				
		if ($txt_family!='')
		{
			$sql	.= " and (CONCAT(`host_users`.`first_name`,' ',`host_users`.`last_name`) like '%".$txt_family."%') ";
		}
		//----user name				
		if ($txt_username!='')
		{
			$sql	.= " and (`host_users`.`username` like '%".$txt_username."%') ";
		}
		//---- site name				
		if ($txt_sitename!='')
		{
			$sql	.= " and (`wbs_profile`.`wb_title` like '%".$txt_sitename."%') ";
		}
		//---- site address				
		if ($txt_siteurl!='')
		{
			$sql	.= " and (`wbs_profile`.`wb_id` in (select `wb_id` from `wbs_domain` where `domain` like '%".$txt_siteurl."%')) ";
		}
		//----user state
		if ($sel_state_user!='all')	{$sql .= " and (`host_users`.`is_active`=".$sel_state_user.")";}
		//----site state
		if ($sel_state_site!='all')	{$sql .= " and (`wbs_profile`.`wb_status`=".$sel_state_site.")";}
		//----site_of_users
		if ($sel_state_site_of_users!='all'){$sql .= " and (`wbs_profile`.`wb_id` ".$sel_state_site_of_users.")";}

		//----count of show
	$sql_where	= $sql;
	if ($order_by=='title'){$order_by='`wbs_profile`.`wb_'.$order_by.'`';}
	if ($order_by=='username' or $order_by=='id' or $order_by=='status'){$order_by=	'`host_users`.`'.$order_by.'`';}
	if ($order_by=='name'){$order_by=	'`'.$order_by.'`';}
		
		if ($sel_count=='all')
		{
			$sql_where	.=' ORDER BY '.$order_by.' '.$asc_desc;
		}
		else
		{
			$sql_where	.=' ORDER BY '.$order_by.' '.$asc_desc.' limit '.$start.','.$sel_count;
		}
	
		$result	= $this->DB->fetchAll($sql_base.$sql_where);
		//echo $sql_base.$sql_where;
		//=============
		$wbID='-1';
		foreach ($result as $data)
		{
			if ($data['wb_id']!=NULL){$wbID	.= ','.$data['wb_id'];} 
		}
		$domainsuffix = '.'.$this->registry->config->base->domain;
		$sql2	= "select * from `wbs_domain` where `wb_id` in (".$wbID.") and (`wbs_domain`.`domain` like '%".$domainsuffix."') ;";
		$res2	= $this->DB->fetchAll($sql2);
		foreach($res2 as $d)
		{
			$domain[$d['wb_id']]= $d['domain'];
		}
		$sql_base_for_count	= "select 
							count(*) as cnt
					from `host_users` left join `wbs_profile` on (`wbs_profile`.`host_id`=`host_users`.`id`) where (1=1) 
								";
		//echo $sql_base_for_count.$sql;	
		//echo $sql_base.$sql;					
		$count	= $this->DB->fetchAll($sql_base_for_count.$sql);
		$this->view->assign('title'	, 'لیست کاربران');
		$this->view->assign('data'	, $result);
		$this->view->assign('domain', $domain);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $sel_count);
		$this->view->assign('post_data'	,$_POST);
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());
	}

	//--------------------------
	public function actAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$auth		= $this->chkLogin();
		$userinfo	= $auth->getIdentity();
		$request 	= $this->getRequest();
		$typ		= $request->getParam('typ');
		$id			= $request->getParam('id');
		
		if (!(($userinfo->id == '1') and ( isset($id)) and (($typ==1) or ($typ==0) or ($typ==-1))))
		{
			$this->_redirect('/Rcpanel');
		}
		else 
		{
			$userManager	= new Usermanager_Model_Usermanagercp;
			$userManager->doAct($id,$typ);
			$this->_redirect('/godpanel/user/frmlistuser');		
		}
	}
	//--------------------------
	public function frmdelAction()
	{
		$this->view->assign('title_site'		, 'حذف');	
//		if	(!$this->ses->isAdmin)
//			{
//				$this->_redirect('/Rcpanel');
//			}
		$request 	= $this->getRequest();
		$id			= $request->getParam('id');
		//$DB			= $this->DB;
		$sql = "SELECT * FROM `host_users` where `id`=".addslashes($id);
		$result = $this->DB->fetchRow($sql);
		if ($result)
		{
			$this->view->assign('first_name',$result['first_name']);
			$this->view->assign('last_name'	,$result['last_name']);	
			$this->view->assign('username'	,$result['username']);
			$this->view->assign('id'		,$result['id']);
		}
		else 
		{
			$this->_redirect('/godpanel/user/frmlistuser');
		}
	}
	//--------------------------
	public function delAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$request 	= $this->getRequest();
		$id			= $request->getParam('id');
//		if	((!$this->ses->isAdmin) and  (!isset($id)))
//			{
//				$this->_redirect('/Rcpanel');
//			}
	
		if ($id !=1)
		{
			//$DB		= $this->DB;
			$result = $this->DB->delete('host_users','id='.addslashes($id));
		}
		$this->_redirect('/godpanel/user/frmlistuser');
	}
}	
?>
