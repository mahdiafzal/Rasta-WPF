<?php

class Godpanel_PanelController extends Zend_Controller_Action
{

    public function init()
    {
		Godpanel_Model_User_User::initUser();
		if(!defined('USRiD') or USRiD!=='1')	die(Application_Model_Messages::message(404));

		$this->ses 	= new Zend_Session_Namespace('MyApp');
		$this->registry	= Zend_registry::getInstance();
    	$this->DB		= $this->registry['front_db'];
    	
		if (!defined('USRiD'))
		{
			$this->_redirect('/godpanel/user/frmlogin');
		}
		else
		{
			$this->gethelper('viewRenderer')->view->assign('user_id',USRiD); 
			$response = $this->getResponse();
			$response->insert('menu',$this->view->render('menu.phtml'));		
		}
    }

	public function indexAction()
    {
		//$this->_redirect('/godpanel/panel/frmlistsite');
		
		$this->view->assign('title_site'		, 'صفحه اصلی');	
		$sql	='select * from `wbs_profile` where `host_id`='.USRiD;
		$result	= $this->DB->fetchAll($sql);
		if (count($result)==1)
		{
			$sql	= 'select * from `wbs_domain` where `wb_id`='.$result[0]['wb_id'].' ORDER BY `dom_id` ASC';
			$res	= $this->DB->fetchAll($sql);
			if ($res)
			{
				$this->view->assign('domainData'		, $res[0]);	
			}

			$sql	= 'select `body_id`,`theme_id` from `wbs_skin` where `skin_id`='.$result[0]['skin_id'];
			$res	= $this->DB->fetchAll($sql);
			if ($res)
			{
				$this->view->assign('skin'	, $res[0]['body_id'].'-'.$res[0]['theme_id']);	
			}
		$this->view->assign('siteData'		, $result[0]);
		}
		$auth		= Zend_Auth::getInstance(); 
		$user		= $auth->getIdentity();
		$this->view->assign('first_name', $user->first_name );		
		$this->view->assign('last_name'	, $user->last_name );			
		$this->view->assign('title'		, 'صفحه اصلی کنترل پنل' );		
		$this->view->assign('msg'		, $this->_helper->flashMessenger->getMessages());		
	}

	public function frmcrtsiteAction()
    {
		$this->view->assign('title_site', 'ایجاد پورتال');	
		$this->view->assign('title'		,'فرم ایجاد پورتال');
		$this->view->assign('msg'		, $this->_helper->flashMessenger->getMessages());		
		
//		if ($this->ses->isAdmin)
//		{
			$sql	='select `id`,`first_name`,`last_name`,`username` from `host_users` where `is_active` = 1 and `id` not in (select `host_id` from `wbs_profile`)';
			$result	= $this->DB->fetchAll($sql);
			//print_r($result);
			if (count($result)==0)
				{
					$this->_helper->flashMessenger->addMessage('<p>جهت ثبت پورتال جدید حتما باید کاربری که فعال است و پورتال ثبت نکرده است وجود داشته باشد.<br/>شما باید یک کاربر(هاست) جدید ثبت کنید و سپس اقدام به فعال سازی آن نمایید');
					$this->_redirect('/godpanel/User/frmreguser');
				}
			$this->view->assign('hostlist'	,$result);	
//		}
//		else
//		{
//			$sql	='select * from `wbs_profile` where `host_id`='.USRiD;
//			if (count($this->DB->fetchAll($sql))!=0)
//			{
//				$this->_helper->flashMessenger->addMessage('شما نمی توانید پورتال جدید ایجاد کنید');
//				$this->_redirect('/godpanel/panel/index');
//			}
//		}
	}

	public function crtsiteAction()
    {
		$this->_helper->viewRenderer->setNoRender();
		$request	= $this->getRequest();
		$validator	= new Application_Model_Validator;
		$domain		=trim($request->getParam('s_subdomain'));	
		if(trim($request->getParam('s_title'))=='') 		 
		{
			$this->_helper->flashMessenger->addMessage('لطفا عنوان پورتال را وارد کنید');
			$this->_redirect('/godpanel/panel/frmcrtsite');
		}	
		else if ($validator->checksubdomain($domain)!=1)
		{
			$this->_helper->flashMessenger->addMessage('دامنه نا معتبر است! قبل از ذخیره دامنه را بررسی کنید');
			$this->_redirect('/godpanel/panel/frmcrtsite');
		}else
		{
			try
			{
				//add other values to $data
//				if ($this->ses->isAdmin)
//				{
					if (preg_match('/^\d+$/',$request->getPost('ddown_user')))
					{
						$data ['host_id']	= $request->getPost('ddown_user');
					}
					else
					{
						$this->_helper->flashMessenger->addMessage('خطا در آرگومانهای ورودی');
						$this->_redirect('/godpanel/panel/frmcrtsite');
					}
//				}
//				else
//				{
//					$data ['host_id']	= USRiD;
//				}
				$data ['wb_title'	]	= $request->getParam('s_title');
				$data ['latin_title']	= $request->getParam('s_latintitle');
				$data ['wb_description']= $request->getParam('s_description');
				$data ['wb_slogan'	]	= $request->getParam('s_slogan');
				$data ['wb_authors'	]	= $request->getParam('s_authors');
				$data ['wb_keywords']	= $request->getParam('s_keywords');
				$data ['wb_expirdate']	= new Zend_Db_Expr('DATE_ADD(NOW(),INTERVAL 1 MONTH)');
				$data ['wb_status'	]	= '1';
				
				$domainsuffix = '.'.$this->registry->config->base->domain; 
				$site	= new Application_Model_Initsite($data, $domain.$domainsuffix);
				if(! $site->state)
					$message	= 'پورتال شما پیش از این ساخته شده است.';
				else
					$message	= 'ایجاد پورتال جدید با موفقیت انجام گرفت و داده های پیش فرض با موفقیت نصب شدند.'
								. '<br/>با استفاده از نام کاربری و رمز عبور کنترل پنل خود می توانید وارد محیط مدیریت پورتال خود شوید.'
								. '<p><a style="color:blue;" href="http://'.$domain.$domainsuffix.'" >لینک ورود به مدیریت پورتال</a></p>';
				$this->_helper->flashMessenger->addMessage($message);
				$this->_redirect('/godpanel/panel/');

			}
			catch(Zend_exception $e)
			{
				//$this->DB->rollBack();
				$this->_helper->flashMessenger->addMessage('خطا در فرایند ایجاد پورتال! لطفا با مدیر تماس بگیرید!');
				$this->_redirect('/godpanel/panel/');
				//echo $e->getMessage();
			}
		}
    }

	public function frmlistsiteAction()
    {
		$this->view->assign('title_site', 'لیست پورتال ها');	

		if ($this->getRequest()->isPost())
		{
			$sel_crt_opration	= $this->getRequest()->getparam('sel_crt_opration');
			$txt_crt			= $this->fa_to_ger($this->getRequest()->getparam('txt_crt'));
			$sel_crt_Confine	= $this->getRequest()->getparam('sel_crt_Confine');
			$sel_exp_opration	= $this->getRequest()->getparam('sel_exp_opration');
			$txt_exp			= $this->fa_to_ger($this->getRequest()->getparam('txt_exp'));
			$sel_exp_Confine	= $this->getRequest()->getparam('sel_exp_Confine');
			$sel_state_user		= $this->getRequest()->getparam('sel_state_user');
			$sel_state_site		= $this->getRequest()->getparam('sel_state_site');
			$sel_count			= $this->getRequest()->getparam('sel_count');
			$start				= $this->getRequest()->getparam('start');
			$order_by			= $this->getRequest()->getparam('order_by');
			$asc_desc			= $this->getRequest()->getparam('asc_desc');
		}
		else
		{
			$sel_crt_opration	= 'all';
			$txt_crt			= '';
			$sel_crt_Confine	= '';
			$sel_exp_opration	= 'all';
			$txt_exp			= '';
			$sel_exp_Confine	= '';
			$sel_state_user		= 'all';
			$sel_state_site		= 'all';			
			$sel_count			= 20;
			$start				= 0;
			$order_by			= 'id';
			$asc_desc			= 'DESC';
		}
		//=============
		//print_r($_POST);
		$sql_base	= "select 
							`host_users`.`id`,CONCAT(`host_users`.`first_name`,' ',`host_users`.`last_name`) as name ,`host_users`.`username`
							,`wbs_profile`.`wb_id`,`wbs_profile`.`wb_creation`,`wbs_profile`.`wb_expirdate`,`wbs_profile`.`wb_status`
							,`wbs_profile`.`wb_title`,`wbs_profile`.`wb_description`
					from `host_users`,`wbs_profile`
					where 
							(`wbs_profile`.`host_id`=`host_users`.`id`)
					";
		$sql="";
		//----creation date					
		if ($sel_crt_Confine=='')
		{
			if($sel_crt_opration!='all'){$sql .= " and (SUBSTRING(`wbs_profile`.`wb_creation` ,1 ,10)   ".$sel_crt_opration."'" .$txt_crt. "')";}
		}
		else
		{
			if($sel_crt_Confine!='')	{$sql .= " and (`wbs_profile`.`wb_creation` >= DATE_SUB(CURDATE(), INTERVAL ".$sel_crt_Confine."))";}
		}
		//----expire date					
		if ($sel_exp_Confine=='')
		{
			if($sel_exp_opration!='all'){$sql .= " and (SUBSTRING(`wbs_profile`.`wb_expirdate` ,1 ,10)  ".$sel_exp_opration."'" .$txt_exp. "')";}			
		}
		else
		{
			if($sel_exp_Confine!=''){$sql .= " and (`wbs_profile`.`wb_expirdate` <= DATE_ADD(CURDATE(), INTERVAL ".$sel_exp_Confine."))";}
		}
		//----user state
		if ($sel_state_user!='all')	{$sql .= " and (`wbs_profile`.`host_id` in (select `id` from `host_users` where `host_users`.`is_active`=".$sel_state_user."))";}
		//----sitr state
		if ($sel_state_site!='all')	{$sql .= " and (`wbs_profile`.`wb_status`=".$sel_state_site.")";}
		//----count of show
		$sql_where	= $sql;
		if ($order_by=='title' or $order_by=='creation' or $order_by=='expirdate' or $order_by=='status' or $order_by=='id'){$order_by='`wbs_profile`.`wb_'.$order_by.'`';}
		if ($order_by=='username'){$order_by= '`host_users`.`'.$order_by.'`';}
		if ($order_by=='name'){$order_by= '`'.$order_by.'`';}
		
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
			$wbID	.= ','.$data['wb_id'] ;
		}
		$sql2	= "select * from `wbs_domain` where `wb_id` in (".$wbID.") and `wbs_domain`.`domain` like '%edus.ir' ;";
		$res2	= $this->DB->fetchAll($sql2);
		foreach($res2 as $d)
		{
			$domain[$d['wb_id']]= $d['domain'];
		}
				
		$sql_base_for_count	= "select 
									count(*) as cnt
								from `host_users`,`wbs_profile`
								where 
										(`wbs_profile`.`host_id`=`host_users`.`id`)
								";			
		$count	= $this->DB->fetchAll($sql_base_for_count.$sql);
		$this->view->assign('title'	, 'لیست پورتال ها');
		$this->view->assign('data'	, $result);
		$this->view->assign('domain', $domain);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $sel_count);
		$this->view->assign('post_data'	,$_POST);
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());
		//jalali
		$this->view->headLink	()->appendStylesheet('/jalali/skins/calendar-blue2.css');
		$this->view->headScript	()->appendFile		('/jalali/calendar.js');	
		$this->view->headScript	()->appendFile		('/jalali/jalali.js');	
		$this->view->headScript	()->appendFile		('/jalali/lang/calendar-fa.js');		
		$this->view->headScript	()->appendFile		('/jalali/calendar-setup.js');		
		//------
	}

	public function frmeditsiteAction()
    {
		$this->view->assign('title_site'	,'فرم ویرایش پورتال');	
		$this->view->assign('title','فرم ویرایش پورتال');
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());		

//		if ($this->ses->isAdmin)
//		{
			$request= $this->getRequest();
			if (preg_match('/^[0-9]+$/',$request->getParam('id')))
			{
				$wb_id	= $request->getParam('id');
			}
			else
			{
				$this->_helper->flashMessenger->addMessage('خطا در آرگومانهای ورودی');
				$this->_redirect('/godpanel/panel/frmlistsite');
			}

			$sql	='select * from `wbs_profile` where `wb_id`='.$wb_id;
			$result	=$this->DB->fetchAll($sql);
			if (count($result)==0)
			{
				$this->_helper->flashMessenger->addMessage('پورتالی با این شناسه پیدا نشد!');
				$this->_redirect('/godpanel/panel/frmlistsite');
			}
			else if (count($result)==1)
			{
				$this->view->assign('data',$result[0]);
			}
			else
			{
				$this->_helper->flashMessenger->addMessage('شما بیش از یک پورتال ثبت کرده اید. لطفا با مدیریت تماس بگیرید');
				$this->_redirect('/godpanel/panel/');
			}
//		}
//		else
//		{
//			$sql	='select * from `wbs_profile` where `host_id`='.USRiD;
//			$result	=$this->DB->fetchAll($sql);
//			if (count($result)==0)
//			{
//				$this->_helper->flashMessenger->addMessage('شما باید ابتدا پورتال جدید ایجاد کنید سپس اقدام به ویرایش آن نمایید');
//				$this->_redirect('/godpanel/panel/');
//			}
//			else if (count($result)==1)
//			{
//				$this->view->assign('data',$result[0]);
//				
//			}
//			else
//			{
//				$this->_helper->flashMessenger->addMessage('شما بیش از یک پورتال ثبت کرده اید. لطفا با مدیریت تماس بگیرید');
//				$this->_redirect('/godpanel/panel/index');
//			}
//		}	    
	}

	public function editsiteAction()
    {
		$this->_helper->viewRenderer->setNoRender();
		$request		= $this->getRequest();

		if(trim($request->getParam('s_title'))=='') 		 
		{
			$this->_helper->flashMessenger->addMessage('لطفا عنوان پورتال را وارد کنید');
//			if ($this->ses->isAdmin)
//			{
				if (preg_match('/^[0-9]+$/',$request->getParam('id')))
				{
					$this->_redirect('/godpanel/panel/frmeditsite/id/'.$request->getParam('id'));
				}
				else
				{
						$this->_helper->flashMessenger->addMessage('خطا در آرگومانهای ورودی');
						$this->_redirect('/godpanel/panel/frmlistsite/');			
				}
//			}
//			else
//			{
//				$this->_redirect('/godpanel/panel/frmeditsite');
//			}
		}
		else 
		{
			try
			{
				//add other values to $data
				$data ['wb_title'	]	= ($request->getParam('s_title'));
				$data ['latin_title']	= ($request->getParam('s_latintitle'));
				$data ['wb_description']= ($request->getParam('s_description'));
				$data ['wb_slogan'	]	= ($request->getParam('s_slogan'));
				$data ['wb_authors'	]	= ($request->getParam('s_authors'));
				$data ['wb_keywords']	= ($request->getParam('s_keywords'));
//				if ($this->ses->isAdmin)
//				{
					if (!preg_match('/^[0-9]+$/',$request->getParam('id')))
					{
						$this->_helper->flashMessenger->addMessage('خطا در آرگومانهای ورودی');
						$this->_redirect('/godpanel/panel/frmlistsite/');			
					}
					$this->DB->update('wbs_profile',$data ,'`wb_id`='.$request->getParam('id'));
					$this->_helper->flashMessenger->addMessage('ویرایش پورتال با موفقیت انجام گرفت');
					$this->_redirect('/godpanel/panel/');
//				}
//				else
//				{
//					//end adding to $data
//					$this->DB->update('wbs_profile',$data ,'`host_id`='.USRiD);
//					$this->_helper->flashMessenger->addMessage('ویرایش پورتال با موفقیت انجام گرفت');
//					$this->_redirect('/godpanel/panel/');
//				}
			}
			catch(Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage('خطا در فرایند ویرایش پورتال! لطفا با مدیر تماس بگیرید!');
				$this->_redirect('/godpanel/panel/');
				//echo $e->getMessage();
			}
		}
	}

    public function doactAction()
    {
		$this->_helper->viewRenderer->setNoRender();
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز!');
//			$this->_redirect('/godpanel/user/frmlogin');
//		}
		$id	= $this->getRequest()->getParam('id');
		if (preg_match("/^[0-9]+\.[0-9]+$/",$id))
		{
			$par	= explode('.',$id);
			$id		= $par[0];
			$act	= $par[1];
			$data	= array('wb_status'	=> $act);
			try 
			{
				$this->DB->update('wbs_profile',$data,'`wb_id` = '.$id);
				$this->_helper->flashMessenger->addMessage('انشار/عدم انتشار با موفقیت صورت گرفت');
				$this->_redirect('/godpanel/panel/frmlistsite/');
			}
			catch (Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage('خطا در بروز رسانی');
				$this->_redirect('/godpanel/panel/frmlistsite');
				//echo $e->getMessage();
			}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage('خطا در آرگومانهای ورودی');
			$this->_redirect('/godpanel/panel/');
		}
    }

    public function frmparkdomainAction()
    {
		$domainsuffix = '.'.$this->registry->config->base->domain;		
		$sql	= 'SELECT * FROM wbs_domain WHERE (wbs_domain.domain not like "%'.$domainsuffix.'") and (wb_id = ( SELECT wb_id FROM `wbs_profile` WHERE host_id ='.USRiD .'))';
		$result	= $this->DB->fetchAll($sql);
		//print_r($result);
		$this->view->assign('title_site','پارک دامنه');
		$this->view->assign('title','پارک دامنه');
		$this->view->assign('data',$result);
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());		
    }

    public function unparkdomainAction()
    {
		$this->_helper->viewRenderer->setNoRender();
		$domain	= $this->getRequest()->getParam('domain');
		$domainsuffix = '.'.$this->registry->config->base->domain;		
		$sql	= 'SELECT * FROM wbs_domain WHERE (wbs_domain.domain not like "%'.$domainsuffix.'") and '
				  .'(wbs_domain.domain="'.$domain.'") and (wb_id = ( SELECT wb_id FROM `wbs_profile` WHERE host_id ='.USRiD.'))';
		echo $sql;
		$result	= $this->DB->fetchAll($sql);
		if ($result)
		{
			try 
			{
				$host			= $this->registry->config->cpanel->host; 
				$username		= $this->registry->config->cpanel->username; 
				$password		= $this->registry->config->cpanel->password; 
				$domain_class	= new Rasta_Domain($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x3', $domain);
				if ($domain_class->unparkDomain())
				{
					$this->DB->delete('wbs_domain','wb_id='.$result[0]['wb_id'] .' and domain="'.$domain.'"');
				$this->_helper->flashMessenger->addMessage('دامنه '.$domain.' با موفقیت حذف شد.' );
					$this->_redirect('/godpanel/panel/');
				}
				else
				{
					$this->_helper->flashMessenger->addMessage('خطا در حذف دامنه، لطفا دوباره تلاش کنید' );
					$this->_redirect('/godpanel/panel/frmparkdomain');
				}
			}
			catch (Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage('خطا در حذف دامنه');
				$this->_redirect('/godpanel/panel/');
				//echo $e->getMessage();
			}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage('خطا: امکان حذف این دامنه برای شما وجود ندارد');
			$this->_redirect('/godpanel/panel/frmparkdomain');
		}
    }
    public function parkdomainAction()
    {
		$this->_helper->viewRenderer->setNoRender();
		$domain=trim($this->getRequest()->getParam('s_subdomain'));
		$validator	= new Application_Model_Validator;
		
		//echo $validator->checkdomain($domain);
		if ($validator->checkdomain($domain)==1)
		{
			try 
			{
				$sql	= 'select `wb_id` from `wbs_profile` where `host_id`='.USRiD;
				$result= $this->DB->fetchAll($sql);
				if ($result)
				{
					$data['wb_id']	= $result[0]['wb_id'];
					$data['domain']	= $domain ;

					$host			= $this->registry->config->cpanel->host; 
					$username		= $this->registry->config->cpanel->username; 
					$password		= $this->registry->config->cpanel->password; 
					$domain_class	= new Rasta_Domain($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x3', $domain);
					if ($domain_class->parkDomain())
					{
						$this->DB->insert('wbs_domain',$data);
				$this->_helper->flashMessenger->addMessage('دامنه '.$domain.' با موفقیت پارک شد.<br/> شما از هم اکنون با این دامنه می توانید از پورتال خود استفاده کنید' );
						$this->_redirect('/godpanel/panel/');
					}
					else
					{
						$this->_helper->flashMessenger->addMessage('خطا: قبل از ثبت دامنه ی جدید، شما باید از صحت موارد زیر مطمئن شوید:<br/>1- دی ان اس دامنه به <strong>ns1.dnameserver.info</strong>  و  <strong>ns2.dnameserver.info</strong> تغییر کرده باشد<br/>2- این دامنه به هیچ پورتالی اشاره نکرده باشد' );
						$this->_redirect('/godpanel/panel/frmparkdomain');
					}
				}
				else
				{
					$this->_helper->flashMessenger->addMessage('قبل از پارک دامنه باید پورتال ثبت کنید');
					$this->_redirect('/godpanel/panel/');
				}
			}
			catch (Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage('خطا در ثبت دامنه');
				$this->_redirect('/godpanel/panel/');
				//echo $e->getMessage();
			}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage('خطا در ثبت دامنه! قبل از ذخیره دامنه را بررسی کنید');
			$this->_redirect('/godpanel/panel/frmparkdomain');
		}
    }
    public function publishsiteAction()
    {
		$this->_helper->viewRenderer->setNoRender();
		$id	= $this->getRequest()->getParam('id');
		if (preg_match("/^[0-1]$/",$id))
		{
			if($id==0)
			{
				$st=' غیر فعال ';
				$data	= array('wb_status'	=> '0');
			}
			else if($id==1)
			{
				$st=' فعال ';
				$data	= array('wb_status'	=> '1');			
			}
				try 
				{
					$res =$this->DB->update('wbs_profile',$data,'`host_id` = '.USRiD);
					if ($res)
					{
						$this->_helper->flashMessenger->addMessage('هم اکنون قسمت نمایش پورتال شما '.$st.' شده است');
					}
					$this->_redirect('/godpanel/panel/');
				}
				catch (Zend_exception $e)
				{
					$this->_helper->flashMessenger->addMessage('خطا در بروز رسانی');
					$this->_redirect('/godpanel/panel/');
					//echo $e->getMessage();
				}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage('خطا در آرگومانهای ورودی');
			$this->_redirect('/godpanel/panel/');
		}
    }

	public function frmsendmailAction()
	{
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}

		$id	= $this->getRequest()->getParam('id');
		if (!preg_match("/^[0-9]+$/",$id))
		{
			$this->_helper->flashMessenger->addMessage('خطا در داده های ورودی');
			$this->_redirect('/godpanel/panel/frmlistsite');
		}
		$sql	= 'select concat(`first_name`," ",`last_name`) as name,`username` from `host_users` where `id`='. $id;
		$res	= $this->DB->fetchAll($sql);
		if (!$res)
		{
			$this->_helper->flashMessenger->addMessage('کاربر مورد نظر یافت نشد');
			$this->_redirect('/godpanel/panel/frmlistsite/');
		}
		$this->view->assign('data'	, $res[0]);
		$this->view->assign('title'	, 'ارسال پیام');
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());	
		$this->view->assign('title_site', 'ارسال پیام');	
		//ckeditor	
		$this->view->headScript	()->appendFile('/ckeditor/ckeditor.js');
		//-------
	}

    public function sendmailAction()
    {
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}

		$this->_helper->viewRenderer->setNoRender();
		$email 	= $this->getRequest()->getParam('email');
		$from 	= $this->getRequest()->getParam('from');
		$text 	= $this->getRequest()->getParam('editor1');
		$family = $this->getRequest()->getParam('family');
		$Subject= $this->getRequest()->getParam('subject');

		$body	=  '<body dir="rtl"><div style=" width: 600px; border: 1px solid black; margin: 0pt auto; text-align: right; -moz-border-radius:8px; padding: 10px;">';
		$body 	.= $text;
		$body	.=  '</div></body>';
		try 
		{
			$mail = new Zend_Mail('UTF-8');
			$mail	->setBodyHtml	(stripslashes($body))
					->setFrom		($from, 'Rastak CMS (http://iranscholar.ir/)')
					->addTo			($email,'Dear user')
					->setSubject	($Subject)
					->send()
					;	
			if ($mail)
			{	
				$this->_helper->flashMessenger->addMessage('پیام به '. $family .' ارسال شد');
				$this->_redirect('/godpanel/panel/frmlistsite/');
			}
			else
			{
				$this->_helper->flashMessenger->addMessage('خطا: پیام ارسال نشد لطفا مجددا تلاش کنید');
				$this->_redirect('/godpanel/panel/frmlistsite/');
			}
		}
		catch (Zend_Exception $e)
		{
				$this->_helper->flashMessenger->addMessage('خطا: پیام ارسال نشد لطفا مجددا تلاش کنید');
				$this->_redirect('/godpanel/panel/frmlistsite/');
		}
    }

    public function frmgroupmailAction()
    {
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}
		$this->view->headScript	()->appendFile('/ckeditor/ckeditor.js');
		$request= $this->getRequest();
		if (($request->isPost()) and (count($request->getPost('chk'))>0))
		{
			$IDs= implode(',',$request->getPost('chk'));
			if (!preg_match('/^[0-9]+(\,[0-9]+)*$/',$IDs))
			{
				$this->_helper->flashMessenger->addMessage('خطا در آرگومان های ورودی');
				$this->_redirect('/godpanel/panel/frmlistsite');
			}
			$sql	= 'select concat(`first_name`," ",`last_name`) as name,`username` from `host_users` where `id` in ('.$IDs.')';
			$result	= $this->DB->fetchAll($sql);
			if ($result)
			{
				$this->view->assign('data',$result);
				$this->view->assign('title'		, 'ارسال پیام گروهی');
				$this->view->assign('title_site', 'ارسال پیام گروهی');	
				$this->view->assign('callback', $this->getRequest()->getParam('callback'));	
			}
			else
			{
				$this->_helper->flashMessenger->addMessage('هیچ کاربری یافت نشد');
				$this->_redirect('/godpanel/panel/frmlistsite');
			}
			$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());	
		}
		else
		{
			$this->_helper->flashMessenger->addMessage('هیچ پورتالی انتخاب نشده است');
			$this->_redirect('/godpanel/panel/frmlistsite');
		}
    }

    public function frmgroupmailbyqueryAction()
    {
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}
		$sel_crt_opration	= $this->getRequest()->getparam('sel_crt_opration');
		$txt_crt			= $this->fa_to_ger($this->getRequest()->getparam('txt_crt'));
		$sel_crt_Confine	= $this->getRequest()->getparam('sel_crt_Confine');
		$sel_exp_opration	= $this->getRequest()->getparam('sel_exp_opration');
		$txt_exp			= $this->fa_to_ger($this->getRequest()->getparam('txt_exp'));
		$sel_exp_Confine	= $this->getRequest()->getparam('sel_exp_Confine');
		$sel_state_user		= $this->getRequest()->getparam('sel_state_user');
		$sel_state_site		= $this->getRequest()->getparam('sel_state_site');
		$sel_count			= $this->getRequest()->getparam('sel_count');
		$start				= $this->getRequest()->getparam('start');
		$sql_base	= "select 
							CONCAT(`first_name`,' ',`host_users`.`last_name`) as name, `username`
					from `host_users`,`wbs_profile`
					where 
							(`wbs_profile`.`host_id`=`host_users`.`id`)
					";
		$sql="";
		//----creation date					
		if ($sel_crt_Confine=='')
		{
			if($sel_crt_opration!='all'){$sql .= " and (SUBSTRING(`wbs_profile`.`wb_creation` ,1 ,10)   ".$sel_crt_opration."'" .$txt_crt. "')";}
		}
		else
		{
			if($sel_crt_Confine!='')	{$sql .= " and (`wbs_profile`.`wb_creation` >= DATE_SUB(CURDATE(), INTERVAL ".$sel_crt_Confine."))";}
		}
		//----expire date					
		if ($sel_exp_Confine=='')
		{
			if($sel_exp_opration!='all'){$sql .= " and (SUBSTRING(`wbs_profile`.`wb_expirdate` ,1 ,10)  ".$sel_exp_opration."'" .$txt_exp. "')";}			
		}
		else
		{
			if($sel_exp_Confine!=''){$sql .= " and (`wbs_profile`.`wb_expirdate` <= DATE_ADD(CURDATE(), INTERVAL ".$sel_exp_Confine."))";}
		}
		//----user state
		if ($sel_state_user!='all')	{$sql .= " and (`wbs_profile`.`host_id` in (select `id` from `host_users` where `host_users`.`is_active`=".$sel_state_user."))";}
		//----sitr state
		if ($sel_state_site!='all')	{$sql .= " and (`wbs_profile`.`wb_status`=".$sel_state_site.")";}
		//----count of show
		$sql_where	= $sql;
		if ($sel_count=='all')
		{
			$sql_where	.=' ORDER BY `wbs_profile`.`wb_id` DESC ';
		}
		else
		{
			$sql_where	.=' ORDER BY `wbs_profile`.`wb_id` DESC limit '.$start.','.$sel_count;
		}
	
		$result	= $this->DB->fetchAll($sql_base.$sql_where);
		$this->view->headScript	()->appendFile('/ckeditor/ckeditor.js');
		$this->view->assign('title'	, 'ارسال ایمیل گروهی');
		$this->view->assign('data'	, $result);
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());
    }

    public function groupmailAction()
    {
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}
		$this->_helper->viewRenderer->setNoRender();
		$email 	= $this->getRequest()->getParam('email');
		$from 	= $this->getRequest()->getParam('from');
		$text 	= $this->getRequest()->getParam('editor1');
		$Subject= $this->getRequest()->getParam('subject');
		
		$email	= explode(',',$email);	
		unset($email[0]);
		print_r($email);
		$body	=  '<body dir="rtl"><div style=" width: 600px; border: 1px solid black; margin: 0pt auto; text-align: right; -moz-border-radius:8px; padding: 10px;">';
		$body 	.= $text;
		$body	.=  '</div></body>';
		try 
		{
			$mail = new Zend_Mail('UTF-8');
			$mail	->setBodyHtml	(stripslashes($body))
					->setFrom		($from, 'Rastak CMS (http://iranscholar.ir/)')
					->addBcc		($email)					
					->addTo			('rastakGroup@iranscholar.ir','Dear users')
					->setSubject	($Subject)
					->send()
					;	
			if ($mail)
			{	
				$this->_helper->flashMessenger->addMessage('پیام با موفقیت ارسال شد');
				$this->_redirect('/godpanel/panel/frmlistsite/');
			}
			else
			{
				$this->_helper->flashMessenger->addMessage('خطا: پیام ارسال نشد لطفا مجددا تلاش کنید');
				$this->_redirect('/godpanel/panel/frmlistsite/');
			}
		}
		catch (Zend_Exception $e)
		{
			$this->_helper->flashMessenger->addMessage('خطا: پیام ارسال نشد لطفا مجددا تلاش کنید');
			$this->_redirect('/godpanel/panel/frmlistsite/');
	}}

	public function frmaddchargeAction()
	{
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}
		$request= $this->getRequest();
		if (($request->isPost()) and (count($request->getPost('chk'))>0))
		{
			$IDs= implode(',',$request->getPost('chk'));
			if (!preg_match('/^[0-9]+(\,[0-9]+)*$/',$IDs))
			{
				$this->_helper->flashMessenger->addMessage('خطا در آرگومان های ورودی');
				$this->_redirect('/godpanel/panel/frmlistsite');
			}
			
			$sql="select CONCAT(`first_name`,' ',`last_name`) as name,`wb_title`,`wb_expirdate`,`wb_creation` from `host_users`,`wbs_profile` where "
				." (`host_users`.`id`=`wbs_profile`.`host_id`) and (`host_users`.`id` in (".$IDs."))";			
			$result	= $this->DB->fetchAll($sql);
			if ($result)
			{
				$this->view->assign('data',$result);
				$this->view->assign('id',$IDs);
				$this->view->assign('title'		, 'افزودن شارژ گروهی');
				$this->view->assign('title_site', 'افزودن شارژ گروهی');	
			}
			else
			{
				$this->_helper->flashMessenger->addMessage('هیچ پورتالی یافت نشد');
				$this->_redirect('/godpanel/panel/frmlistsite');
			}
			$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());	
		}
		else
		{
			$this->_helper->flashMessenger->addMessage('هیچ پورتالی انتخاب نشده است');
			$this->_redirect('/godpanel/panel/frmlistsite');
		}
	}

    public function addchargeAction()
    {
		$this->_helper->viewRenderer->setNoRender();
//		if (!$this->ses->isAdmin)
//		{
//			$this->_helper->flashMessenger->addMessage('دسترسی غیر مجاز:شما مجاز به دیدن این قسمت نیستید');
//			$this->_redirect('/godpanel/panel/');
//		}
		$id= $this->getRequest()->getParam('id');
		if (!preg_match('/^[0-9]+(\,[0-9]+)*$/',$id))
		{
			$this->_helper->flashMessenger->addMessage('خطا در آرگومان های ورودی');
			$this->_redirect('/godpanel/panel/frmlistsite');
		}
		$type	= $this->getRequest()->getparam('rdo_typ_pay');
		switch ($type)
		{
			case '3_month'	: $adddate	='DATE_ADD(`wbs_profile`.`wb_expirdate`,INTERVAL 2 MONTH)';// '98000';
								break;
			case '6_month'	: $adddate	='DATE_ADD(`wbs_profile`.`wb_expirdate`,INTERVAL 6 MONTH)';
								break;
			case '1_year'	: $adddate	='DATE_ADD(`wbs_profile`.`wb_expirdate`,INTERVAL 12 MONTH)';
								break;
			case '2_year'	: $adddate	='DATE_ADD(`wbs_profile`.`wb_expirdate`,INTERVAL 24 MONTH)';
								break;
		}
		$sql='UPDATE `wbs_profile` SET `wb_expirdate` = '.$adddate.' where `host_id` in ('.$id.')';		
		$result	= $this->DB->query($sql);
		if($result)
		{
			$this->_helper->flashMessenger->addMessage('شارژ پورتال با موفقیت صورت گرفت');
		}
		else
		{
			$this->_helper->flashMessenger->addMessage('خطا در عملیات افزودن شارژ');
		}
		$this->_redirect('/godpanel/panel/frmlistsite');
	}







    public function fileAction() 
    {
		$this->_helper->viewRenderer->setNoRender();
		echo $this->foldersize('/flsimgs/41');
//		$SIZE_LIMIT = 5368709120; // 5 GB
//		$sql="select * from users order by id";
//		$result=mysql_query($sql);
//		$disk_used = foldersize("C:/xampp/htdocs/freehosting/".$row['name']);
//		$disk_remaining = $SIZE_LIMIT - $disk_used;
//		print 'Name: ' . $row['name'] . '<br>';
//		print 'diskspace used: ' . format_size($disk_used) . '<br>';
//		print 'diskspace left: ' . format_size($disk_remaining) . '<br><hr>';
    }
	



// Controller Helper Methods
	public function foldersize($path)
	{
		$total_size = 0;
		$files = scandir($path);
		foreach($files as $t) {
		if (is_dir(rtrim($path, '/') . '/' . $t)) {
		if ($t<>"." && $t<>"..") {
			$size = foldersize(rtrim($path, '/') . '/' . $t);
			$total_size += $size;
		}
		} else {
		$size = filesize(rtrim($path, '/') . '/' . $t);
		$total_size += $size;
		}   
		}
		return $total_size;
		}
	public function format_size($size) 
	{	
		$mod = 1024;
		$units = explode(' ','B KB MB GB TB PB');
		for ($i = 0; $size > $mod; $i++) {
		$size /= $mod;
		}
		return round($size, 2) . ' ' . $units[$i];
	}
	public function fa_to_ger($date)
	{
		if ($date=='')
		{
			return	 NULL;
		}
		else 
		{
			$arr	= explode(' ',$date)	;
			$d		= explode('-',$arr[0])	;
			$pdate	= new Rasta_Pdate;
			$arr[0] = implode('-',$pdate->persian_to_gregorian($d[0],$d[1],$d[2]));
			return    implode(' ',$arr);
		}
	}

}

