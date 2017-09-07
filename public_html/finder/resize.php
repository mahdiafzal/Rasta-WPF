<?php
if(!isset($_SESSION)) session_start();
if($_SESSION['MyApp'] && isset($_SESSION['KCFINDER']))
{

	require 'Image.php';

	$uploaddir			= $_SESSION['KCFINDER']['uploadDir'];
	$selfdir			= $_SESSION['KCFINDER']['self']['dir'];
	$newdimension		= $_POST['imaged'];
	$newsize			= $_POST['imagen'];
	$data				= explode('*,*', $_POST['name']);
	
	foreach ($data as$key=>$imgname)
	{
		$image		= ($uploaddir."/".$selfdir."/".$imgname);
		$imageinfo	= getimagesize($image);
		
		switch ($imageinfo['mime'])
		{
			case "image/png"	:
			case "image/jpeg"	:
			case "image/gif"	:
			case "image/bmp"	:	
									$I = new Image($image);
									if ($newdimension=="width")
									{
										$I->width( $newsize );
									}
									elseif ($newdimension=="both")
									{
										$I->width($newsize ,$square=true );
									}
									else
									{
										$I->height( $newsize );
									}
									$I->save();
									$I->destroy();
									echo "فایل زیر با موفقیت تغییر سایز یافت \n";
									echo $imgname."\n";				
													
									break;
			default				: echo " ! لطفا فقط فایل های عکس انتخاب کنید"	; break;
		}

	}	  

}
else
{
	echo '<h1>ACCESS DENIED</h1>';
}
?>
