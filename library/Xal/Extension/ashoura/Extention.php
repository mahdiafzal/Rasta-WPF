<?php
	class Xal_Extension_ashoura_Extention
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
			if(isset($_POST) == true){
				$errors= array();
				$file_name = $_FILES['audio']['name'];
				$file_size =$_FILES['audio']['size'];
				$file_tmp =$_FILES['audio']['tmp_name'];
				$file_type=$_FILES['audio']['type'];   
				$file_ext=strtolower(end(explode('.',$_FILES['audio']['name'])));
				$extensions = array("mp3"); 		
				if(in_array($file_ext,$extensions )=== false){
					$errors[]="extension not allowed, please choose a mp3 file.";
				}
				if($file_size > 5048576){
					$errors[]='File size grater than 5 MB';
				}				
				if(empty($errors)==true){
					move_uploaded_file($file_tmp,"/public_html/flsimgs/ashora/2/files/Main/assets/Audio/speech/".$file_name);
				}else{
					$myfile = fopen("log.txt", "w") or die("Unable to open file!");
					$txt = implode("\n", $errors);
					fwrite($myfile, $txt);
					fclose($myfile);
				}
			}
		}
	}
?>
