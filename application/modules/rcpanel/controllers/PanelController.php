<?php

class Rcpanel_PanelController extends Zend_Controller_Action
{
    public function init()
    {
		Rcpanel_Model_User_User::initUser();

		$this->ses 	= new Zend_Session_Namespace('MyApp');
		$this->registry	= Zend_registry::getInstance();
    	$this->DB		= $this->registry['front_db'];
    	
		if (!defined('USRiD'))	$this->_redirect('/rcpanel/user/frmlogin');
		
		$this->gethelper('viewRenderer')->view->assign('user_id',USRiD); 
		$response = $this->getResponse();
    }
	public function indexAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	
		$this->view->assign('title_site'	, $this->translate->_('a'));	
		$sql	='select * from `wbs_profile` where `host_id`='.USRiD;
		$result	= $this->DB->fetchAll($sql);
		if (count($result)==1)
		{
			$sql	= 'select * from `wbs_domain` where `wb_id`='.$result[0]['wb_id'].' ORDER BY `dom_id` DESC';
			$res	= $this->DB->fetchAll($sql);
			if ($res)	$this->view->assign('domainData'		, $res[0]);	
			$this->view->assign('siteData'		, $result[0]);
		}
		$auth		= Zend_Auth::getInstance(); 
		$user		= $auth->getIdentity();
		$this->view->assign('first_name', $user->first_name );		
		$this->view->assign('last_name'	, $user->last_name );			
		//$this->view->assign('title'		, 'صفحه اصلی کنترل پنل' );
		$fmsg	= $this->_helper->flashMessenger->getMessages();
		$this->view->assign('msg'		, $fmsg[0]);		
	}
	public function frmcrtsiteAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	
		
		$sql	='select * from `wbs_profile` where `host_id`='.USRiD;
		if (count($this->DB->fetchAll($sql))!=0)
		{
			$this->_helper->flashMessenger->addMessage(array('شما نمی توانید سایت جدید ایجاد کنید'));
			$this->_redirect('/rcpanel/panel/index');
		}
		
		$fmsg	= $this->_helper->flashMessenger->getMessages();
		
		$this->view->assign('title_site', 'ایجاد سایت');	
		$this->view->assign('title'		,'فرم ایجاد سایت');
		$this->view->assign('msg'		, $fmsg[0]);		
		$this->view->assign('data'		, $fmsg[1]);		
		
	}
	public function crtsiteAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	

		$this->_helper->viewRenderer->setNoRender();
		$request	= $this->getRequest();
		$params		= $request->getParams();
		$validator	= new Application_Model_Validator;
		$domain		= trim($params['s_subdomain']);	
		if(trim($params['s_title'])=='') 		 
		{
			$this->_helper->flashMessenger->addMessage(array('لطفا عنوان سایت را وارد کنید'));
			$this->_helper->flashMessenger->addMessage($params);
			$this->_redirect('/rcpanel/panel/frmcrtsite');
		}	
		else if ($validator->checksubdomain($domain)!=1)
		{
			$this->_helper->flashMessenger->addMessage(array('دامنه نا معتبر است! قبل از ذخیره دامنه را بررسی کنید'));
			$this->_helper->flashMessenger->addMessage($params);
			$this->_redirect('/rcpanel/panel/frmcrtsite');
		}else
		{
			try
			{
				$data ['host_id']		= USRiD;
				$data ['wb_title'	]	= $params['s_title'];
				$data ['latin_title']	= $params['s_latintitle'];
				$data ['wb_description']= $params['s_description'];
				$data ['wb_slogan'	]	= $params['s_slogan'];
				$data ['wb_authors'	]	= $params['s_authors'];
				$data ['wb_keywords']	= $params['s_keywords'];
				$data ['wb_expirdate']	= new Zend_Db_Expr('DATE_ADD(NOW(),INTERVAL '.$this->registry->config->base->init->expinterval.')');
				$data ['wb_status'	]	= $this->registry->config->base->init->wbsstatus;
				$data ['host_size'	]	= $this->registry->config->base->init->hostsize;
				
				$domainsuffix = '.'.$this->registry->config->base->domain; 
				$site	= new Rcpanel_Model_Site_Setup($data, $domain.$domainsuffix);
				if(! $site->state)
					$message[]	= 'سایت شما پیش از این ساخته شده است.';
				else
					$message[]	= 'ایجاد سایت جدید با موفقیت انجام گرفت و داده های پیش فرض با موفقیت نصب شدند.'
								. '<br/>با استفاده از نام کاربری و رمز عبور کنترل پنل خود می توانید وارد محیط مدیریت سایت خود شوید.'
								. '<p><a style="color:blue;" href="http://'.$domain.$domainsuffix.'" >لینک ورود به مدیریت سایت</a></p>';
				$this->_helper->flashMessenger->addMessage($message);
				$this->_redirect('/rcpanel/panel/');

			}
			catch(Zend_exception $e)
			{
				//$this->DB->rollBack();
				$this->_helper->flashMessenger->addMessage(array('خطا در فرایند ایجاد سایت! لطفا با مدیر تماس بگیرید!'));
				$this->_redirect('/rcpanel/panel/');
				//echo $e->getMessage();
			}
		}
    }
    public function frmparkdomainAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	

		$domainsuffix = '.'.$this->registry->config->base->domain;		
		$sql	= 'SELECT * FROM wbs_domain WHERE (wbs_domain.domain not like "%'.$domainsuffix.'") and (wb_id = ( SELECT wb_id FROM `wbs_profile` WHERE host_id ='.USRiD .'))';
		$result	= $this->DB->fetchAll($sql);
		$this->view->assign('title_site','پارک دامنه');
		$this->view->assign('title','پارک دامنه');
		$this->view->assign('data',$result);
		$fmsg	= $this->_helper->flashMessenger->getMessages();
		$this->view->assign('msg'		, $fmsg[0]);		
    }
    public function unparkdomainAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	

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
				$this->_helper->flashMessenger->addMessage(array('دامنه '.$domain.' با موفقیت حذف شد.'));
					$this->_redirect('/rcpanel/panel/');
				}
				else
				{
					$this->_helper->flashMessenger->addMessage(array('خطا در حذف دامنه، لطفا دوباره تلاش کنید'));
					$this->_redirect('/rcpanel/panel/frmparkdomain');
				}
			}
			catch (Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage(array('خطا در حذف دامنه'));
				$this->_redirect('/rcpanel/panel/');
				//echo $e->getMessage();
			}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage(array('خطا: امکان حذف این دامنه برای شما وجود ندارد'));
			$this->_redirect('/rcpanel/panel/frmparkdomain');
		}
    }
    public function parkdomainAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	

		$this->_helper->viewRenderer->setNoRender();
		$domain=trim($this->getRequest()->getParam('s_subdomain'));
		$validator	= new Application_Model_Validator;
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
				$this->_helper->flashMessenger->addMessage(array('دامنه '.$domain.' با موفقیت پارک شد.<br/> شما از هم اکنون با این دامنه می توانید از سایت خود استفاده کنید'));
						$this->_redirect('/rcpanel/panel/');
					}
					else
					{
						$this->_helper->flashMessenger->addMessage(array('خطا: قبل از ثبت دامنه ی جدید، شما باید از صحت موارد زیر مطمئن شوید:<br/>1- دی ان اس دامنه به <strong>ns1.dnameserver.info</strong>  و  <strong>ns2.dnameserver.info</strong> تغییر کرده باشد<br/>2- این دامنه به هیچ سایتی اشاره نکرده باشد'));
						$this->_redirect('/rcpanel/panel/frmparkdomain');
					}
				}
				else
				{
					$this->_helper->flashMessenger->addMessage(array('قبل از پارک دامنه باید سایت ثبت کنید'));
					$this->_redirect('/rcpanel/panel/');
				}
			}
			catch (Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage(array('خطا در ثبت دامنه'));
				$this->_redirect('/rcpanel/panel/');
				//echo $e->getMessage();
			}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage(array('خطا در ثبت دامنه! قبل از ذخیره دامنه را بررسی کنید'));
			$this->_redirect('/rcpanel/panel/frmparkdomain');
		}
    }
    public function publishsiteAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	

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
					if ($res)	$this->_helper->flashMessenger->addMessage(array('هم اکنون قسمت نمایش سایت شما '.$st.' شده است'));
					$this->_redirect('/rcpanel/panel/');
				}
				catch (Zend_exception $e)
				{
					$this->_helper->flashMessenger->addMessage(array('خطا در بروز رسانی'));
					$this->_redirect('/rcpanel/panel/');
					//echo $e->getMessage();
				}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage(array('خطا در آرگومانهای ورودی'));
			$this->_redirect('/rcpanel/panel/');
		}
    }

//// Action Helper Methods
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

