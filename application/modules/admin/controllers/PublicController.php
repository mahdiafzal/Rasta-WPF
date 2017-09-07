<?php 

class Admin_PublicController extends Zend_Controller_Action
{

	public function ckconfigAction()
	{ 
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		header('content-type: application/javascript; charset=UTF-8');
		echo "CKEDITOR.editorConfig = function( config ){";
		
		echo "config.language 		= '".LANG."';";
		echo "config.toolbar 		= 'RastakCMSToolbar';";
		echo "config.enterMode 		= CKEDITOR.ENTER_P;";
		echo "config.shiftEnterMode = CKEDITOR.ENTER_BR;";
		echo "config.tabIndex		= 1;";
		echo "config.height 		= '500px';";
		echo "config.removePlugins	= 'resize';	";
		echo "config.extraPlugins 	= 'save';";
		if(!empty($_SESSION['MyApp']['hostSize']) and $_SESSION['MyApp']['hostSize']>0)
		{
			echo "config.filebrowserBrowseUrl = '/finder/browse.php?type=files';";
			echo "config.filebrowserImageBrowseUrl = '/finder/browse.php?type=images';";
			echo "config.filebrowserFlashBrowseUrl = '/finder/browse.php?type=flash';";
			echo "config.filebrowserUploadUrl = '/finder/upload.php?type=files';";
			echo "config.filebrowserImageUploadUrl = '/finder/upload.php?type=images';";
			echo "config.filebrowserFlashUploadUrl = '/finder/upload.php?type=flash';";
		}
		 
		echo "config.toolbar_RastakCMSToolbar =";
		echo "[";
		echo 	"['Source','-', 'Save','NewPage','Preview','-','Templates'],";
		echo 	"['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],";
		echo 	"['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],";
		echo 	"['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],";
		echo 	"'/',";
		echo 	"['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],";
		echo 	"['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],";
		echo 	"['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],";
		echo 	"['BidiLtr', 'BidiRtl'],";
		echo 	"['Link','Unlink','Anchor'],";
		echo 	"['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'],";
		echo 	"'/',";
		echo 	"['Styles','Format','Font','FontSize'],";
		echo 	"['TextColor','BGColor'],";
		echo 	"['ShowBlocks']";
		echo "];";
			
			
		echo "CKEDITOR.config.keystrokes = ";
		echo "[";
		echo 	"[ CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/, 'redo' ],";
		echo 	"[ CKEDITOR.CTRL + 90 /*Z*/, 'undo' ],";
		echo 	"[ CKEDITOR.CTRL + 83 /*S*/, 'save' ]";
		echo "];";
		
		echo "};";

	}

    public function jslangAction()
    {
		$translate	= Zend_Registry::get('translate');
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	    header('content-type: application/javascript; charset=UTF-8');
		$jslangKeys	= array(
					'a', 'b', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
					'aa', 'ab', 'ac', 'ad', 'ae', 'af', 'ag', 'ah', 'ai', 'aj', 'ak', 'al', 'am', 'an', 'ao', 'ap', 'aq', 'ar', 'as', 'at', 'au', 'av', 'aw', 'ay', 'az', 'ba'
					);
		foreach($jslangKeys as $langkey) $jslangProperty[]	= $langkey.":'".$translate->_($langkey)."'";
		echo 'function Clang(){return Clang.fn.init();}Clang.fn=Clang.prototype={init:function(){return this;},'
			.implode(',',$jslangProperty)
			.'};lang=new Clang();';
	}

	public function generatecaptchaAction()
	{ 
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			$ses = new Zend_Session_Namespace('MyApp');
			 //Let's generate a totally random string using md5
			$md5_hash = md5(rand(0,999));
			//We don't need a 32 character long string so we trim it down to 5
			$security_code = substr($md5_hash, 15, 5);
			$ses->captchaCode = $security_code;
			//Set the image width and height
			$width = 120;
			$height = 30;
			//Create the image resource
			$image = ImageCreate($width, $height);
			//We are making three colors, white, black and gray
			$white = ImageColorAllocate($image, 255, 255, 255);
			$black = ImageColorAllocate($image, 0, 0, 0);
			$grey = ImageColorAllocate($image, 204, 204, 204);
			//Make the background black
			ImageFill($image, 0, 0, $black);
			//Add randomly generated string in white to the image
			ImageString($image, 3, 30, 3, $security_code, $white);
			//Throw in some lines to make it a little bit harder for any bots to break
			ImageRectangle($image,0,0,$width-1,$height-1,$grey);
			imageline($image, 0, $height/2, $width, $height/2, $grey);
			imageline($image, $width/2, 0, $width/2, $height, $grey);
			//Tell the browser what kind of file is come in
			header("Content-Type: image/jpeg");
			//Output the newly created image in jpeg format
			ImageJpeg($image);
		}
	public function watercapAction() 
	{
		$ses = new Zend_Session_Namespace('myApp');
		//Let's generate a totally random string using md5
		$md5_hash = md5(rand(0,999));
		//We don't need a 32 character long string so we trim it down to 5
		$code = strtolower(substr($md5_hash, 15, 5));
		$ses->captchaCode = $code;
		//Set the image width and height
		$width = 120;
		$height = 30;
		//Create the image resource
		$image = ImageCreate($width, $height)or die('Cannot initialize new GD image stream');
		/* seed random number gen to produce the same noise pattern time after time */
		/* init image */
		$font_size = $height * 0.85;
		/* set the colours */
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$text_color = imagecolorallocate($image, 20, 40, 100);
		$noise_color = imagecolorallocate($image, 100, 120, 180);
		/* create textbox and add text */
		$textbox = imagettfbbox($font_size, 0, 'impact', $code) or die('Error in imagettfbbox function');
		$x = ($width - $textbox[4])/2;
		$y = ($height - $textbox[5])/2;
		$d = -1;
		imagettftext($image, $font_size, 0, $x, $y, $text_color, 'impact' , $code) or die('Error in imagettftext function');
		imagettftext($image, $font_size, 0, $x + $d, $y + $d, $noise_color, 'impact' , $code) or die('Error in imagettftext function');
		imagettftext($image, $font_size, 0, $x + 2 * $d + 1, $y + 2 * $d + 1, $noise_color, 'impact' , $code) or die('Error in imagettftext function');
		imagettftext($image, $font_size, 0, $x + 2 * $d, $y + 2 * $d, $background_color, 'impact' , $code) or die('Error in imagettftext function');
		/* mix in background dots */
		for( $i=0; $i<($width*$height)/10; $i++ ) 
		{ 
			imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $background_color);		 
		}
		/* mix in text and noise dots */
		for( $i=0; $i<($width*$height)/25; $i++ ) 
		{ 
			imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);		 
			imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $text_color);		 
		}
		/* rotate a bit to add fuzziness */
		$image = imagerotate($image, 1, $background_color);
		//Tell the browser what kind of file is come in
		header("Content-Type: image/jpeg");
	//		header('Content-Type: text/plain; charset="UTF-8"');
		/* output */
		imagejpeg($image);
		imagedestroy($image);
	}

	public function helpAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$par	=$this->getRequest()->getParam('state');
		switch($par)
		{
			case 'menulist':
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">با ایجاد منو ها، میتوانید دسترسی آسانی به مطالب و صفحات سایت ایجاد کرده و در اختیار مخاطبان خود قرار دهید، این منو ها میتوانند شامل پیوند به سایت های دیگر، مطالب سایت، آلبوم ها و سایر صفحات سایت شما باشند.</div>';
					break;		
			case 'rtclist':		
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">لیست متن ها شامل تمام متن هایی است که شما در سایت خود ایجاد کردید، میتوانید این متن ها را در صفحات دلخواه خود اضافه کنید. میتوانید آنها را در نوار اصلی، در بخش زیرین و یا در نوار های کناری صفحات سایت استفاده کنید.</div>';
					break;		
			case 'gallerylist':			
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">آلبوم هایی را که تا کنون ایجاد کردید، در این بخش در دسترس هستند و شما میتوانید با اضافه کردن آنها به صفحات سایت خود، تصاویر مورد نظرتان را با دیگران به اشتراک بگذارید، این آلبوم ها قابل نمایش در نوارهای اصلی و کناری هستند.</div>';
					break;		
			case 'pagelist':			
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">در این قسمت لیستی از تمام صفحاتی که برای سایت خود ساخته اید تهیه شده و نیز امکان ایجاد و ویرایش صفحات داده شده تا بتوانید مدیریت بهتری بر صفحات سایت خود داشته باشید.</div>';
					break;		
			case 'scenariolist':			
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">سناریو به شما این امکان را می دهد که متن های خود را دسته بندی و با یک الگوی مشخص نمایش دهید. <br /> به عنوان مثال شما با این امکان می توانید برای سایت خود وبلاگ ایجاد نمایید. <br />یا اینکه صفحه اخبار درست کرده و آخرین اخبار را به این صفحه پست کنید.</div>';
					break;		
			case 'extlinklist':			
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">قابلیت ایجاد و مدیریت پیوندهای سایت در اینجا فراهم شده.شما در اینجا میتوانید پیوندهایی به سایتهای مورد علاقه و یا سایت های دوستان خود ایجاد کرده و از طریق اضافه کردن آنها به منوهای سایت، دیگران را با علایق و سلیقه ی خود و دوستانتان آشنا کنید.</div>';
					break;		
			case 'pagethemelist':			
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">شما برای سایت و صفحات سایت پوسته ی مشخصی انتخاب کردید، در این بخش میتوانید رنگ مایه ی پوسته ی سایت یا صفحه خود را تغییر دهید.</div>';
					break;		
			case 'mlmthemelist':		
					$rst	= 	'<div dir="rtl" style="text-align:justify; padding:7px;">در این بخش نیز میتوانید پوسته و رنگ مایه ی منوی افقی سایتتان را، مطابق سلیقه ی خود و مخاطبانتان انتخاب کنید.</div>';
					break;	
		}

		echo $rst;

	}
	
	
	
	
	
}