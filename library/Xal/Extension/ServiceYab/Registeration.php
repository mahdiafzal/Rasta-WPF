<?php

class Xal_Extension_ServiceYab_Registeration

{

	public function	run($argus)

	{

		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';

		foreach($argus as $ark=>$argu)

		{

			switch($ark)

			{

				case 'upload'	: return $this->_upload($argu); break;

				case 'reg.order'	: return $this->_regOrder($argu); break;

				case 'get.order'	: return $this->_getOrder($argu); break;
				
				case 'reg.bill'	: return $this->_regBill($argu); break;
				
				case 'get.bill'	: return $this->_getBill($argu); break;
				
				case 'reg.form_submit'	: return $this->_form_submit($argu); break;
				
				case 'get.form_data'	: return $this->_form_data($argu); break;

			}

			

		}

	}


	protected function	_upload($argus)

	{

		if(isset($_FILES["companyServicesImage"]) && $_FILES["companyServicesImage"]["error"]== UPLOAD_ERR_OK)

		{

			$UploadDirectory	= '../web/flsimgs/serviceyaab/2/images/Services/'; //specify upload directory ends with / (slash) ///clients/client2/web3

			//check if this is an ajax request

			if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){

				die("AJAX");

			}


			//Is file size is less than allowed size.

			if ($_FILES["companyServicesImage"]["size"] > 5242880) {

				die("حجم فایل زیاد است!");

			}

			

			//allowed file type Server side check

			switch(strtolower($_FILES['companyServicesImage']['type']))

				{

					//allowed file types

		            case 'image/png': 

					case 'image/gif': 

					case 'image/jpeg': 

					case 'image/pjpeg':

					//case 'text/plain':

					//case 'text/html': //html file

					//case 'application/x-zip-compressed':

					//case 'application/pdf':

					//case 'application/msword':

					//case 'application/vnd.ms-excel':

					//case 'video/mp4':

						break;

					default:

						die('فرمت فایل غیرمجاز است!'); //output error

			}

		
			$File_Name_service          = strtolower($_FILES['companyServicesImage']['name']);

			$File_Ext_service           = substr($File_Name_service, strrpos($File_Name_service, '.')); //get file extention

			$Random_Number_service      = rand(0, 9999999999); //Random number to be added to name.

			$NewFileName_service 		= $Random_Number_service.$File_Ext_service; //new file name

			if(move_uploaded_file($_FILES['companyServicesImage']['tmp_name'], $UploadDirectory.$NewFileName_service ))

			   {   

				die($NewFileName_service);

			}else{

				die('اشکالی در ارسال فایل به وجود آمده است.');

			}

		}

		else

		{

			die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');

		}

	}

	protected function	_getOrder($argus)

	{

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');

		$sql = "SELECT * FROM `plans_proposals`  LIMIT 0, 30";  // ORDER BY `cu_code_id`".$sortType." LIMIT ".$start.",".$count;

		if(! $result = $this->DB->fetchAll($sql)) return false;

		return $result;

	

	}

	protected function	_regOrder($argus)

	{

		//print_r($_GET);

		//print_r($_POST);

		//die("POST");

		//----------------
		$data["plan_services_choosed"] = ($_POST["planServicesChoosed"])?$_POST["planServicesChoosed"]: "";
		$data["plan_tops_choosed"] = ($_POST["planTopsChoosed"])?$_POST["planTopsChoosed"]: "";
		$data["plan_text_choosed"] = ($_POST["planTextChoosed"])?$_POST["planTextChoosed"]: "";
		//----------------
		$data["paymenter_pp"] = ($_POST["Paymenter"])?$_POST["Paymenter"]: "";

		$data["email_pp"] = ($_POST["Email"])?$_POST["Email"]: "";

		$data["company_name"] = ($_POST["company_name"])?$_POST["company_name"]: "";

		$data["how_to_inform"] = ($_POST["how_to_inform"])?$_POST["how_to_inform"]: "";
		
		$data["company_tel"] = ($_POST["company_tel"])?$_POST["company_tel"]: "";

		$data["mobile_pp"] = ($_POST["Mobile"])?$_POST["Mobile"]: "";

		$data["postal_code"] = ($_POST["postal_code"])?$_POST["postal_code"]: "";

		$data["address"] = ($_POST["address"])?$_POST["address"]: "";

		$data["pipe_opening"] = ($_POST["pipe_opening"])?$_POST["pipe_opening"]: "";

		$data["heater_piping"] = ($_POST["heater_piping"])?$_POST["heater_piping"]: "";

		$data["broken_pipe"] = ($_POST["broken_pipe"])?$_POST["broken_pipe"]: "";

		$data["old_pits_improvement"] = ($_POST["old_pits_improvement"])?$_POST["old_pits_improvement"]: "";

		$data["pit_clearing"] = ($_POST["pit_clearing"])?$_POST["pit_clearing"]: "";

		$data["pit_opening"] = ($_POST["pit_opening"])?$_POST["pit_opening"]: "";

		$data["diging_new_pit"] = ($_POST["diging_new_pit"])?$_POST["diging_new_pit"]: "";

		$data["pit_vaccum"] = ($_POST["pit_vaccum"])?$_POST["pit_vaccum"]: "";

		$data["clearing_smell"] = ($_POST["clearing_smell"])?$_POST["clearing_smell"]: "";

		$data["clearing_wetness"] = ($_POST["clearing_wetness"])?$_POST["clearing_wetness"]: "";

		$data["consultatnt"] = ($_POST["consultatnt"])?$_POST["consultatnt"]: "";

		$data["advertisement_text"] = ($_POST["advertisement_text"])?$_POST["advertisement_text"]: "";

		$data["total_price_pp"] = ($_POST["Price"])?$_POST["Price"]: "";

		$data["profile_price_bill"] = ($_POST["profile_price_bill"])?$_POST["profile_price_bill"]: "";

		$data["service_price_bill"] = ($_POST["service_price_bill"])?$_POST["service_price_bill"]: "";

		$data["tops_price_bill"] = ($_POST["tops_price_bill"])?$_POST["tops_price_bill"]: "";

		$data["text_price_bill"] = ($_POST["text_price_bill"])?$_POST["text_price_bill"]: "";

		$data["region_price_bill"] = ($_POST["region_price_bill"])?$_POST["region_price_bill"]: "";

		$data["regions"] = ($_POST["regions"])?$_POST["regions"]: "";

		$data["services_page_banner_link"] = ($_POST["services_page_banner_link"])?$_POST["services_page_banner_link"]: "";

		$data["top_companies_banner_link"] = ($_POST["top_companies_banner_link"])?$_POST["top_companies_banner_link"]: "";
		
		$data["designPic"] = ($_POST["designPic"])?$_POST["designPic"]: "";
		
		$data["designBanner"] = ($_POST["designBanner"])?$_POST["designBanner"]: "";
		
		date_default_timezone_set('Asia/Tehran');
		$submit_date = date('Y/m/d H:i:s a', time());
		
		$data["submit_date"] = $submit_date ;
		



		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');

		if($this->DB->insert('plans_proposals', $data))

		{

			$data['co_id'] = $this->DB->lastInsertId();

			//die("OK");
		}

		//die("NOK");

	}


	//-----------------------------------Payment Bill-----------------------------------
	
	protected function	_regBill($argus)

	{
		$data["bil_resnumber"] = ($_POST["refnumber"])?$_POST["refnumber"]: "";
		$data["status"] = ($_POST["status"])?$_POST["status"]: "";
		
		date_default_timezone_set('Asia/Tehran');
		$submit_date_pay = date('Y/m/d H:i:s a', time());
		
		$data["submit_date"] = $submit_date_pay ;
		
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');
		if($this->DB->insert('payments', $data))
		{
			$data['co_id'] = $this->DB->lastInsertId();
		}
	}
	
	
	protected function	_getBill($argus)

	{

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');

		$sql = "SELECT * FROM `payments`  LIMIT 0, 30";  // ORDER BY `cu_code_id`".$sortType." LIMIT ".$start.",".$count;

		if(! $result = $this->DB->fetchAll($sql)) return false;

		return $result;

	

	}

	//-----------------------------------Contact Us form---------------------------------------

	protected function	_form_submit($argus)

	{
		$data["rf_name"] = ($_POST["name"])?$_POST["name"]: "";
		$data["rf_email"] = ($_POST["email"])?$_POST["email"]: "";
		$data["rf_subject"] = ($_POST["subject"])?$_POST["subject"]: "";
		$data["rf_message"] = ($_POST["message"])?$_POST["message"]: "";
		
		date_default_timezone_set('Asia/Tehran');
		$submit_date_pay = date('Y/m/d H:i:s', time());
		
		$data["submit_date"] = $submit_date_pay ;
		
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');
		if($this->DB->insert('ContactUsForm', $data))
		{
			$data['co_id'] = $this->DB->lastInsertId();
		}
	}


	protected function	_form_data($argus)

	{

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');

		$sql = "SELECT * FROM `ContactUsForm`  LIMIT 0, 30";  // ORDER BY `cu_code_id`".$sortType." LIMIT ".$start.",".$count;

		if(! $result = $this->DB->fetchAll($sql)) return false;

		return $result;

	

	}


	

}



?>

