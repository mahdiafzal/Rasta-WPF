<?php
class IndexController extends Zend_Controller_Action
{	
    public function indexAction() 
    {
				
		/*$post = new Xal_Extension_RayaDars_PostSoap();
		//for($i=26; $i<32; $i++)
		//	$post->_getCities($i);
		$argus['products'][] = array('id'=>30524, 'count'=>1, 'discount'=>1 );
		$argus['products'][] = array('id'=>30525, 'count'=>1, 'discount'=>1 );
		$argus['products'][] = array('id'=>30526, 'count'=>1, 'discount'=>10 );
		$argus['products'][] = array('id'=>30527, 'count'=>1, 'discount'=>10 );
		$argus['products'][] = array('id'=>30528, 'count'=>1, 'discount'=>10 );
		
		$argus['city']		= 451;
		$argus['service']	= 1;
		$argus['pay']		= 1;
		$argus['fname']		= 'لیلا';
		$argus['lname']		= 'قاسمی';
		$argus['address']	= 'کوی فرهنگ خیابان سی متری شقایق پلاک 379';
		$argus['phone']		= '02415244882';
		$argus['mobile']	= '09126423214';
		$argus['postalcode']= '4519954317';
		
		$post->_addParcel($argus);
		die();*/
		
		/*$acti = new Xal_Extension_RayaDars_Activation();
		$argus['customer']='P7L7L';
		$argus['system']= '2F4BEB19D6J7Y63FCSB9F1F5F';
		$code = $acti->ComputeActivationCode($argus);
		die($code);*/
		
		/*$this->DB = Zend_Registry::get('extra_db_rd_data');
		$result = $this->DB->fetchOne("SELECT NOW();");
		//die($result);
		date_default_timezone_set('GMT');
		$date = new Zend_Date();
		die($date->get(Zend_date::TIMES));*/
		
		//echo bindec('00010') ;
		
/*		die('<!-- Begin Susa Web Tools - Danestaniha -->
<script src="http://susawebtools.ir/services/txtservice/index.php?type=danestaniha&skin=10"></script>
<div style="margin-right:25%; margin-top:-3%"><a href="http://susawebtools.ir/?p=17">SusaWebTools</a></div>
<!-- End Susa Web Tools - Danestaniha -->');*/

		
		
		/*$date = new Zend_Date();
		$d = $date->get(Zend_Date::DAY_OF_YEAR)+1;
		//$d = $d+1;
		die($d.'l');*/
		
		//$cud = new Xal_Extension_RayaDars_Activation();
		//$cud->_newRequest(array('customer'=>'p7l7l', 'system'=>'aaaaasssssdddddfffffggggg') );
		//die($_SERVER['REQUEST_URI']);
		/*$cud = new Xal_Extension_RayaDars_Customer();
		$ret = $cud->_getDiscount('EIW');*/
		//print realpath(dirname($_SERVER['DOCUMENT_ROOT']));

		/*print realpath($_SERVER['DOCUMENT_ROOT']);
		die();*/
		
/*		$date = new Zend_Date("1392-10-19 12:00:00");
		$date->addMinute(210);
		
 
		// view our date object
		print $date->get(Zend_Date::TIMES);
		$pdate	= new Rasta_Pdate;
		echo('<br/>');
		//echo implode('-',$pdate->persian_to_gregorian($date->get(Zend_Date::YEAR),$date->get(Zend_Date::MONTH),$date->get(Zend_Date::DAY)));
		//echo(date("Y-m-d H:i:s"));
		echo implode('-', $pdate->gregorian_to_persian(date("Y"), date("m"), date("d")) )." ".date("H:i:s");
		
		die();*/


		//print_r($this); die();
		//$ads	= new Application_Model_Ads();	
		//$request 		= $this->getRequest();
		//$modelData[0]	= $request->getParam('webpage');
		
		
		
/*		$client = new Zend_Soap_Client("http://svc.ebazaar-post.ir/EshopService.svc?wsdl");
		$client->setSoapVersion(SOAP_1_1);
		//print($client->Add(2,1));
		
		// $result1 is a string
		//$result1 = $client->Add(array("n1"=>10,"n2"=> 2));
		//$result1 = $client->GetStates();
		$methodName = "GetCities";
		$result1 = $client->$methodName(array("stateId" => 3));
		
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$resultName = "GetCitiesResult";
		print_r($result1->$resultName);
		die();
		
		$GetDeliveryPriceParams = array(
			"username"		=> "",
			"password"		=> "",
			"cityCode"		=> 91,
			"Price"			=> 380000,
			"Weight"		=> 1665,
			"serviceType"	=> 1,
			"payType"		=> 1
		);
		//GetDeliveryPriceResult
		
		$tempProduct[]=array(
			"Id"=> 3632,
			"Count"=>1,
			"DisCount"=>15
		);
		$AddParcelParams = array(
			"username"		=> "",
			"password"		=> "",
			"productsId"	=> $tempProduct,
			"cityCode"		=> 91,
			"serviceType"	=> 1,
			"payType"		=> 1,
			"registerFirstName"		=> "محمد",
			"registerLastName"		=> "سلیمیان",
			"registerAddress"		=> "تهران",
			"registerPhoneNumber"	=> "02188888888",
			"registerMobile"		=> "09122222222",
			"registerPostalCode"	=> "1444444444"
		);
		//AddParcelResult		
		
		//GetStates
		
		$GetCitiesParams	= array(
			"stateId" => 3
		);
		
		$MakeParcelReadyParams = array(
			"username"			=> "",
			"password"			=> "",
			"parcelCodes" 		=> "",
			"datereadyforPost" 	=> ""
		);*/
		

		
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
		echo $HtmlBody;
    }

}