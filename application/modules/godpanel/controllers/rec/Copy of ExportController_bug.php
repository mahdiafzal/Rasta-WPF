<?php

class Godpanel_ExportController extends Zend_Controller_Action
{
	var $DB;
	var $ses;
	var $prifix;
	//-----------	
	public function init()
    {
		Godpanel_Model_User_User::initUser();
		if(!defined('USRiD') or USRiD!=='1')	die(Application_Model_Messages::message(404));

		$this->ses 	= new Zend_Session_Namespace('MyApp');

		$registry	= Zend_registry::getInstance();
    	$this->DB	= $registry['front_db'];

    	if (!defined('USRiD'))
		{
			$this->_redirect('/godpanel/user/frmlogin');
		}
    }
	//-----------	
    public function indexAction()
    {
     	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
		$this->_redirect('/godpanel/panel/');
   	}
	//-----------	
    public function frmwordpressAction()
    {
		$this->gethelper('viewRenderer')->view->assign('user_id',USRiD); 
		$response = $this->getResponse();
		$response->insert('menu',$this->view->render('menu.phtml'));		
 		$this->view->assign('title_site','ایجاد خروجی وردپرس');
		$this->view->assign('title','ایجاد خروجی وردپرس');
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());		
       	}
   	//-----------	
   	public function wordpressAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	$this->_helper->layout()->disableLayout();
    	if($this->getRequest()->getParam('db_prefix')!='')
		{
			$this->prifix	= $this->getRequest()->getParam('db_prefix');
		}
		else
		{
			$this->prifix	= 'wp_';
		}

		$exp_wp	 = $this->expscenario();	
		$exp_wp	.= $this->exptext();		
		$exp_wp	.= $this->explinks();		
		$exp_wp	.= $this->expsitedata();
		$fname			= "wb_".WBSiD."_".time().".sql";
		$ourFileName 	= "../temporary/";
		$help	= "فایل اس کیو ال را در پی اچ پی مای ادمین اینپورت کنید.\n\r www.iranscholar.ir ";
		//require '/library/rasta/zipclass.php';		
		$ziper 	= new Rasta_Zip();
		$ziper->addFile($exp_wp,$fname); //array of files
		$ziper->addFile($help  ,"help.txt");
		$zipFname	= "wb_export_".WBSiD."_".time().".zip";
		$ziper->output($ourFileName .$zipFname);		
		$this->output_file($ourFileName .$zipFname, $zipFname , 'application/zip');		
		//$this->_redirect('/godpanel/panel/');	
    }
   	//-----------	
    public function expscenario()
    {
  		//fetch data from rastak
    	$sql	= 'select `id`,`title`,`latin_title` from `wbs_scenario` where wbs_id=' . WBSiD ;	
    	$result	= $this->DB->fetchAll($sql);
		//----------
    	$prifix	= $this->prifix;
    	$sql	='';
    	foreach ($result as $item)
    	{
    		$tbl_name= 'terms';
    		$sql	.= sprintf("INSERT INTO `%s%s` (`term_id` ,`name` ,`slug` ,`term_group` )VALUES (%s,%s,%s,%s);",$prifix,$tbl_name,'NULL' ,"'".addslashes($item['title'])."'", "'".addslashes($item['latin_title']).MD5(rand())."'", "'0'");   	
		    $sql	.= 'SET @lastid = LAST_INSERT_ID();';
			$tbl_name= 'term_taxonomy';
		    $sql	.= sprintf("INSERT INTO `%s%s` (`term_taxonomy_id` ,`term_id` ,`taxonomy` ,`description` ,`parent` ,`count`  )VALUES (%s,%s,%s,%s,%s,%s);",$prifix,$tbl_name,'NULL' , '@lastid', "'category'","'".$item['id']."'", "'0'", "'0'");
    	}
   		return $sql; 
   	}
	//-----------    
	public function exptext()
    {
		//fetch data from rastak
    	$sql	= 'select `ltn_name`,`title`,`description`,`crt_date`,`is_published`,`content`,`scenarios` from `wbs_rtcs` where `wbs_id`=' . WBSiD ;	
    	$result	= $this->DB->fetchAll($sql);
    	//---
    	$prifix	= $this->prifix;
    	$sql='';
    	foreach ($result as $item)
    	{
    		$tbl_name= 'posts';
    		if ($item['is_published']=='1'){$post_status="'publish'";}else{$post_status="'pending'";} ;
    		$sql	.= sprintf("INSERT INTO `%s%s` (`ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`	, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`)VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s);",$prifix,$tbl_name,'NULL',"'0'","'".addslashes($item['crt_date'])."'","'".addslashes($item['crt_date'])."'","'".addslashes($item['content'])."'","'".addslashes($item['title'])."'","''",$post_status, "'open'", "'open'", "''", "''", "''", "''", "'0000-00-00 00:00:00'", "'0000-00-00 00:00:00'", "''", "'0'","''", "'0'", "'post'", "''", "'0'"); 		
	    	$sql	.= 'SET @lastid = LAST_INSERT_ID();';
	    	$scenarioid= explode('/',$item['scenarios']);
	    	foreach ($scenarioid as $scen)
	    	{
		    	if ($scen!='')
		    	{
		    		$sql	.= "select @taxonomyid := `term_taxonomy_id` from ".$prifix."term_taxonomy where description='".$scen."';";
	    			$tbl_name= 'term_relationships';
			    	$sql	.= sprintf("INSERT INTO `%s%s` (`object_id` ,`term_taxonomy_id` ,`term_order` )VALUES (%s,%s,%s);",$prifix,$tbl_name,'@lastid','@taxonomyid',"'0'"); 		
		    	}
	    	}
    		//echo $sql;
    	}
    	return $sql;   	
   	}
	//-----------	
    public function explinks()
    {
    	$sql	= 'select `title`,`url` from `wbs_links` where wbs_id=' . WBSiD ;	
    	$result	= $this->DB->fetchAll($sql);
    	//---
    	$prifix	= $this->prifix;
    	$tbl_name= 'links';
    	$sql	= '';
    	foreach ($result as $item)
    	{
			$sql	.= sprintf("INSERT INTO `%s%s` (`link_id` ,`link_url` ,`link_name` ,`link_image` ,`link_target` ,`link_description` ,`link_visible` ,`link_owner` ,`link_rating` ,`link_updated` ,`link_rel` ,`link_notes` ,`link_rss`)VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s);",$prifix,$tbl_name,'NULL' ,"'http://www.".addslashes($item['url'])."'","'".addslashes($item['title'])."'", "''","''","''","'Y'","'1'","'0'","'0000-00-00 00:00:00'","''","''","''");
    	}
    	return $sql;   	
   	}
	//-----------	
    public function expsitedata()
    {
  		//fetch data from rastak
		$registry	= Zend_registry::getInstance();
    	$site	= $registry['site'];
		//----------
    	$prifix	= $this->prifix;
    	$tbl_name= 'options';		
    	$sql	 = sprintf("UPDATE `%s%s` SET `option_value` = '%s' WHERE `wp_options`.`option_name` ='blogname' ;",$prifix,$tbl_name,addslashes($site['wb_title']));    	
    	$sql	 .= sprintf("UPDATE `%s%s` SET `option_value` = '%s' WHERE `wp_options`.`option_name` ='blogdescription' ;",$prifix,$tbl_name,addslashes($site['wb_description']));    	
    	return $sql;
   	}
	//-----------  	
	public function output_file($file, $name, $mime_type='')
	{
	 /*
	 This function takes a path to a file to output ($file), 
	 the filename that the browser will see ($name) and 
	 the MIME type of the file ($mime_type, optional).
	 
	 If you want to do something on download abort/finish,
	 register_shutdown_function('function_name');
	 */
	 if(!is_readable($file)) die('File not found or inaccessible!');
	 
	 $size = filesize($file);
	 $name = rawurldecode($name);
	 
	 /* Figure out the MIME type (if not specified) */
	 $known_mime_types=array(
	 	"pdf" => "application/pdf",
	 	"txt" => "text/plain",
	 	"html" => "text/html",
	 	"htm" => "text/html",
		"exe" => "application/octet-stream",
		"zip" => "application/zip",
		"doc" => "application/msword",
		"xls" => "application/vnd.ms-excel",
		"ppt" => "application/vnd.ms-powerpoint",
		"gif" => "image/gif",
		"png" => "image/png",
		"jpeg"=> "image/jpg",
		"jpg" =>  "image/jpg",
		"php" => "text/plain"
	 );
	 
	 if($mime_type==''){
		 $file_extension = strtolower(substr(strrchr($file,"."),1));
		 if(array_key_exists($file_extension, $known_mime_types)){
			$mime_type=$known_mime_types[$file_extension];
		 } else {
			$mime_type="application/force-download";
		 };
	 };
	 
	 @ob_end_clean(); //turn off output buffering to decrease cpu usage
	 
	 // required for IE, otherwise Content-Disposition may be ignored
	 if(ini_get('zlib.output_compression'))
	  ini_set('zlib.output_compression', 'Off');
	 
	 header('Content-Type: ' . $mime_type);
	 header('Content-Disposition: attachment; filename="'.$name.'"');
	 header("Content-Transfer-Encoding: binary");
	 header('Accept-Ranges: bytes');
	 
	 /* The three lines below basically make the 
	    download non-cacheable */
	 header("Cache-control: private");
	 header('Pragma: private');
	 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	 
	 // multipart-download and download resuming support
	 if(isset($_SERVER['HTTP_RANGE']))
	 {
		list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
		list($range) = explode(",",$range,2);
		list($range, $range_end) = explode("-", $range);
		$range=intval($range);
		if(!$range_end) {
			$range_end=$size-1;
		} else {
			$range_end=intval($range_end);
		}
	 
		$new_length = $range_end-$range+1;
		header("HTTP/1.1 206 Partial Content");
		header("Content-Length: $new_length");
		header("Content-Range: bytes $range-$range_end/$size");
	 } else {
		$new_length=$size;
		header("Content-Length: ".$size);
	 }
	 
	 /* output the file itself */
	 $chunksize = 1*(1024*1024); //you may want to change this
	 $bytes_send = 0;
	 if ($file = fopen($file, 'r'))
	 {
		if(isset($_SERVER['HTTP_RANGE']))
		fseek($file, $range);
	 
		while(!feof($file) && 
			(!connection_aborted()) && 
			($bytes_send<$new_length)
		      )
		{
			$buffer = fread($file, $chunksize);
			print($buffer); //echo($buffer); // is also possible
			flush();
			$bytes_send += strlen($buffer);
		}
	 fclose($file);
	 } else die('Error - can not open file.');
	 
	die();
	}	
   	

}