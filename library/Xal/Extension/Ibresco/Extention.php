<?php
	class Xal_Extension_Ibresco_Extention
	{
		public function	run($argus)
		{
			if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
			foreach($argus as $ark=>$argu)
			{
				switch($ark)
				{
					case 'upload'	: return $this->_upload($argu); break;
					case 'upload.msds'	: return $this->_uploadMsds($argu); break;
				}
			}
		}
		protected function	_upload($argus)
		{
			if(isset($_FILES["managerPersonalImageUpload"]) && $_FILES["managerPersonalImageUpload"]["error"]== UPLOAD_ERR_OK){
				$managerPersonalImageUploadDirectory	= '../public_html/flsimgs/ibresco/2/files/catalogues/';
				if ($_FILES["managerPersonalImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['managerPersonalImageUpload']['type']))
					{
			            case 'application/pdf':
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						//case 'text/plain':
						//case 'text/html': //html file
						//case 'application/x-zip-compressed':
						//case 'application/msword':
						//case 'application/vnd.ms-excel':
						//case 'video/mp4':
						break;
						default:
				}
				$File_Name_managerPersonal          = strtolower($_FILES['managerPersonalImageUpload']['name']);
				$File_Ext_managerPersonal           = substr($File_Name_managerPersonal, strrpos($File_Name_managerPersonal, '.'));
				$Random_Number_managerPersonal      = rand(0, 9999999999);
				$NewFileName_managerPersonal 		= $Random_Number_managerPersonal.$File_Ext_managerPersonal;
				if(move_uploaded_file($_FILES['managerPersonalImageUpload']['tmp_name'], $managerPersonalImageUploadDirectory.$NewFileName_managerPersonal ))
				   {
					echo $NewFileName_managerPersonal ;
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadMsds($argus)
		{
			if(isset($_FILES["managerPersonalImageUpload"]) && $_FILES["managerPersonalImageUpload"]["error"]== UPLOAD_ERR_OK){
				$managerPersonalImageUploadDirectory	= '../public_html/flsimgs/ibresco/2/files/msds/';
				if ($_FILES["managerPersonalImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['managerPersonalImageUpload']['type']))
					{
			            case 'application/pdf':
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						//case 'text/plain':
						//case 'text/html': //html file
						//case 'application/x-zip-compressed':
						//case 'application/msword':
						//case 'application/vnd.ms-excel':
						//case 'video/mp4':
						break;
						default:
				}
				$File_Name_managerPersonal          = strtolower($_FILES['managerPersonalImageUpload']['name']);
				$File_Ext_managerPersonal           = substr($File_Name_managerPersonal, strrpos($File_Name_managerPersonal, '.'));
				$Random_Number_managerPersonal      = rand(0, 9999999999);
				$NewFileName_managerPersonal 		= $Random_Number_managerPersonal.$File_Ext_managerPersonal;
				if(move_uploaded_file($_FILES['managerPersonalImageUpload']['tmp_name'], $managerPersonalImageUploadDirectory.$NewFileName_managerPersonal ))
				   {
					echo $NewFileName_managerPersonal ;
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
	}
?>
