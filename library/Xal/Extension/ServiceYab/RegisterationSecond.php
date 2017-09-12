<?php


class Xal_Extension_ServiceYab_RegisterationSecond
{
	
	public function	run($argus)
	{
		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'upload'	: return $this->_upload($argu); break;

			}
			
		}
	}
	

	
	protected function	_upload($argus)
	{
		if(isset($_FILES["companyServicesImage"]) && $_FILES["companyServicesImage"]["error"]== UPLOAD_ERR_OK)
		{
			
			$UploadDirectory	= '../web/flsimgs/serviceyaab/2/images/Tops/'; //specify upload directory ends with / (slash) ///clients/client2/web3
			
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
				//echo($UploadDirectory.$NewFileName_service);
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
	
}

?>
