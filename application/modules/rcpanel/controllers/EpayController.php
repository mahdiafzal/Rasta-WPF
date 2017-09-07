<?php

class Rcpanel_EpayController extends Zend_Controller_Action
{
	var $DB;
	var $ses;

    public function init()
    {
		Rcpanel_Model_User_User::initUser();

		$this->ses 	= new Zend_Session_Namespace('MyApp');
		
		$this->registry	= Zend_registry::getInstance();
    	$this->DB	= $this->registry['front_db'];
    	
		if (!defined('USRiD'))	$this->_redirect('/rcpanel/user/frmlogin');

		$this->gethelper('viewRenderer')->view->assign('user_id',USRiD); 
    }
    public function indexAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	
		$this->view->assign('title_site'		, 'شارژ سایت');	
		$this->view->assign('action', '/rcpanel/epay/prepair');
		$this->view->assign('title'	, 'طرح نوروزی رستاک : لطفا شارژ مورد نیاز خود را انتخاب کنید');
    }
    public function prepairAction()
    {
		$this->translate= $this->registry['translate'];
		$this->view->assign('translate'		, $this->translate);	

		$this->view->assign('title_site'		, 'شارژ سایت::تائید پرداخت');	
		$WBSiD		= $this->DB->fetchRow("select `wb_id` from `wbs_profile` where `host_id`=".USRiD);
		if (!$WBSiD['wb_id'])
		{
			$this->_helper->flashMessenger->addMessage(array('ابتدا سایت ایجاد کنید. سپس نسبت به شارژ سایت اقدام کنید'));
			$this->_redirect('/rcpanel/panel/frmcrtsite');
		}
		$this->ses->hostWBSiD=$WBSiD['wb_id'];
		$type	= $this->getRequest()->getparam('rdo_typ_pay');
		switch ($type)
		{
//			case '3_month'	: 	$Amount	='98000';// '98000';
//								$txt	='دو ماهه';
//								break;
//			case '6_month'	: $Amount='235000';
//								$txt	='شش ماهه';
//								break;
			case '1_year'	: $Amount='1000000';
								$txt	='یک ساله';
								break;
			case '2_year'	: $Amount='1800000';
								$txt	='دو ساله';
								break;
			default 		: $this->_redirect('/rcpanel/epay/');																
		}

		$data	= array('amount'=> $Amount,'host_id'=> USRiD);
		$this->DB->insert('reservation_log',$data);
		
		$ResNum		= $this->DB->lastInsertId();
		$MID		= '02155166-151022';
		//$RedirectURL= 'http://rastakcms.com/rcpanel/epay/getresponse';
		//$RedirectURL= 'http://iranscholar.ir/rcpanel/epay/getresponse';
		$portal_url		= $this->registry->config->base->portal;
		$RedirectURL= 'http://'.$portal_url.'/rcpanel/epay/getresponse';

		$this->ses->ResNum	= $ResNum;	
		$data		=array('Amount'=>$Amount ,'MID'=>$MID , 'ResNum'=>$ResNum ,'RedirectURL'=>$RedirectURL , 'txt'=>$txt);
		//$this->view->assign('action', 'https://acquirer.sb24.com/CardServices/controller');
		$this->view->assign('action', 'http://acquirer.sb24.com/CardServices/controller');
		$this->view->assign('title'	, 'گزارش موارد انتخاب شده');
		$this->view->assign('data'	, $data);
    }
 	//-----------------------------------------------------------------------------   
    public function getresponseAction()
    {
		$this->translate= $this->registry['translate'];

		$this->_helper->viewRenderer->setNoRender();
		$state	= $this->getRequest()->getparam('State');
		$RefNum	= $this->getRequest()->getparam('RefNum');
		$ResNum	= $this->getRequest()->getparam('ResNum');
		$MID		= '02155166-151022';
		$this->_helper->flashMessenger->addMessage(array('state= '.$state.'  ---------  RefNum= '.$RefNum.'  --------  ResNum= '.$ResNum));
		//cheching
		if ($state=='OK')
		{
			// check referenceNumber in DB for double spending
			$resRefNum		= $this->DB->fetchAll("select * from `transaction_log` where `reference_number`='".$RefNum."'");
			if (count($resRefNum)==0)
			{
				require_once('nusoap.php');
				$client = new soapclient("https://acquirer.sb24.com/ref-payment/ws/ReferencePayment?WSDL");
				$result=  $client->VerifyTransaction($RefNum, $MID);

				//echo $result .'result<br/>';
				if ( $result <= 0 )
				{
					$data_log	= array('reference_number'	=> $RefNum,
										'amount'			=> '0',
										'state'				=> $state,
										'reservation_id'	=> $ResNum
										);
					$this->DB->insert('transaction_log',$data_log);
					$this->_helper->flashMessenger->addMessage(array('خطا در عملیات (تائید تراکنش دارای خطا می باشد): گزارش شماره خطا. ----->  ' .$result));
					$this->_redirect('/rcpanel/panel/index');
				}
				
				if ( $result > 0 )
				{
					$res			= $this->DB->fetchRow('select `amount` from `reservation_log` where `id`='.$ResNum .' and `host_id`='.USRiD);
					$amountofResNum	= $res['amount'];
					if 	($result==$amountofResNum)
					{
						$expDate=$this->DB->fetchRow('select `wb_expirdate` from `wbs_profile` where `wb_id`='.$this->ses->hostWBSiD.' and `host_id`='.USRiD);
						switch ($result)
						{
//							case '98000'	: $adddate	='DATE_ADD("'.$expDate['wb_expirdate'].'",INTERVAL 2 MONTH)';// '98000';
//												break;
//							case '235000'	: $adddate	='DATE_ADD("'.$expDate['wb_expirdate'].'",INTERVAL 6 MONTH)';
//												break;
							case '1000000'	: $adddate	='DATE_ADD("'.$expDate['wb_expirdate'].'",INTERVAL 12 MONTH)';
												break;
							case '1800000'	: $adddate	='DATE_ADD("'.$expDate['wb_expirdate'].'",INTERVAL 24 MONTH)';
												break;
						}
					
						$sql='UPDATE `wbs_profile` SET `wb_expirdate` = '.$adddate.' where `wb_id`='.$this->ses->hostWBSiD.' and `host_id`='.USRiD;		
						$this->DB->query($sql);
						$this->_helper->flashMessenger->addMessage(array(' عملیات شارژ سایت با موفقیت انجام گرفت. مبلغ شارژ شده  ---->  '.$result));
						//$this->_redirect('/rcpanel/panel/index');
					}
					else
					{
						$client->ReverseTransaction($RefNum,"02155166-151022","977873",$ResNum);
						$this->_helper->flashMessenger->addMessage(array(' خطا : مبلغ پرداخت شده تا 48 ساعت برگشت داده خواهد شد ---->  '.$result));
						//$this->_redirect('/rcpanel/panel/index');
					}
					$data		= array('reference_number'	=> $RefNum,
										'host_id'			=> USRiD,
										'reservation_id'	=> $ResNum,
										'amount'			=> $result,
										'type'				=>'1'
										);
					$this->DB->insert('pay_document',$data);
					$data_log	= array('reference_number'	=> $RefNum,
										'amount'			=> $result,
										'state'				=> $state, 
										'reservation_id'	=> $ResNum
										);
					$this->DB->insert('transaction_log',$data_log);				
					$this->_redirect('/rcpanel/panel/index');
				}
			}
			else 
			{
				$this->_helper->flashMessenger->addMessage(array(' خطا در عملیات(شماره تراکنش تکراری): در صورت پرداخت وجه ، مبلغ پرداخت شده تا 48 ساعت به حسابتان باز خواهد گشت.----->   '. $RefNum));
				$this->_redirect('/rcpanel/panel/index');
			}
		}
		else
		{
			//echo ' تراکنش با شکست روبرو شد ----->  ' .$state;
			$this->_helper->flashMessenger->addMessage(array(' تراکنش با شکست روبرو شد ----->  ' .$state));
			$this->_redirect('/rcpanel/panel/index');
		}		
    }
}