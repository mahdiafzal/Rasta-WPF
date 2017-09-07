<?php

class Stat_Model_Helper_Register extends Zend_Controller_Action_Helper_Abstract
{
	
	public function preDispatch()
    {
		$this->DB	= Zend_Registry::get('front_db');
		//print_r($_SESSION); die();
		$resources	= array(
					'^\/admin\/ajaxget',
					'^\/admin$',
					'^\/admin\/ajaxset',
					'^\/admin\/public',
					'^\/admin\/user\/login',
					'^\/controlpanel\/public',
					'\.(css)|(js)|(ico)|(jpg)|(png)|(gif)$'
					);
		foreach($resources as $value)
			if(preg_match('/^'.$value.'/i', $_SERVER['REQUEST_URI'])) return false;

		//unset
		if(empty($_SESSION['MyApp']['visitor']['id']))
			$this->NewVisitor();
		elseif($_SESSION['MyApp']['visitor']['uri'] != @$_SERVER['REQUEST_URI'])
			$this->NewVisitor();
		else
			$this->NewVisit();
		return true;
		
	} 
	public function NewVisit()
    {
		try
		{
			$sql 	=	"UPDATE `log_visitor` SET `count`= (`count`+1) WHERE `id` = '".$_SESSION['MyApp']['visitor']['id']."'";
			$this->DB->query($sql);
		}
		catch(zend_exception $e)
		{
			$this->NewVisitor();
		}
		return true;
    }
	public function NewVisitor()
    {
		
		$data['wbs_id']		= $_SESSION['MyApp']['WBSiD'];
		$data['ip']			= $_SERVER['REMOTE_ADDR'];
		$Browser	= $this->getBrowser();
		$data['browser_id']	= $Browser['name'];
		$data['browser_ver']= $Browser['version'];
		$data['os_id']		= $Browser['platform'];
		$data['lang']		= preg_replace('/\,.+$/', '', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$data['referer']	= @$_SERVER['HTTP_REFERER'];
		$data['uri']		= $_SERVER['REQUEST_URI'];
		
		$sql	= 'SELECT @cou:=`country` FROM `ip2nation` WHERE ip < INET_ATON("'.$data['ip'].'")  ORDER BY `ip2nation`.`ip`  DESC LIMIT 0,1;';
		$sql	.= 'INSERT INTO `log_visitor` (`wbs_id`, `ip`, `browser_id`, `browser_ver`, `os_id`, `lang`, `referer`, `uri`, `country`) VALUES ';
		$sql	.= '("'.$data['wbs_id'].'", "'.$data['ip'].'", "'.$data['browser_id'].'", "'.$data['browser_ver'].'", "'.$data['os_id'].'", "'.$data['lang'].'", "'
				.	$data['referer'].'", "'.$data['uri'].'", @cou);';
		try
		{
			$result	= $this->DB->fetchOne($sql);
		}
		catch(Zend_exception $e)
		{
			return true;
		}
		$_SESSION['MyApp']['visitor']['id']	= $this->DB->lastInsertId();
		$_SESSION['MyApp']['visitor']['uri']= $data['uri'];
		return true;
    }
	public function getBrowser() 
	{ 
		$u_agent = $_SERVER['HTTP_USER_AGENT']; 
		$bname = 0; //'Unknown';
		$platform = 0; //'Unknown';
		$version= "";
	
		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 1;//'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 2; //'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 3; //'windows';
		}
		
		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
		{ 
			$bname = 1;//'Internet Explorer'; 
			$ub = "MSIE"; 
		} 
		elseif(preg_match('/Firefox/i',$u_agent)) 
		{ 
			$bname = 2;//'Mozilla Firefox'; 
			$ub = "Firefox"; 
		} 
		elseif(preg_match('/Chrome/i',$u_agent)) 
		{ 
			$bname = 3;//'Google Chrome'; 
			$ub = "Chrome"; 
		} 
		elseif(preg_match('/Safari/i',$u_agent)) 
		{ 
			$bname = 4; //'Apple Safari'; 
			$ub = "Safari"; 
		} 
		elseif(preg_match('/Opera/i',$u_agent)) 
		{ 
			$bname = 5; //'Opera'; 
			$ub = "Opera"; 
		} 
		elseif(preg_match('/Netscape/i',$u_agent)) 
		{ 
			$bname = 6; //'Netscape'; 
			$ub = "Netscape"; 
		} 
		
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
		
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
		
		// check if we have a number
		if ($version==null || $version=="") $version=NULL; //"?";
		
		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
	}
}