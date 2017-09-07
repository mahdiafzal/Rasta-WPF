<?php
/*
	*	
*/
class Application_Model_Page_Page
{

	var $namespace='default';
	var $userID;
	
	public function	__construct($data)
	{
		$this->userID	= Application_Model_User::ID();
	
		$registry 		= Zend_Registry::getInstance();		
		$this->DB 		= $registry['front_db'];
		$this->site		= $registry['site'];
		
		//if(!is_numeric($this->PageID))	$this->PageID	= $data[0];
		if(empty($this->PageID))	$this->PageID	= $data[0];
		
		$this->page		= $this->getPage();
		//print_r($this->page); die();
		
		if( method_exists($this, 'preSkin') ) $this->preSkin($data);

		$this->skin		= $this->getPageSkin();
	
		$this->setPageDirection();
	}
	public function	getPage()
	{
		$stat = (is_numeric($this->PageID))?'`local_id`='.$this->PageID:'`name`="'.addslashes($this->PageID).'"';

		$sql	= "SELECT *, ".Application_Model_Pubcon::get(2001)." AS is_allowed FROM `wbs_pages` WHERE "
				. Application_Model_Pubcon::get(1110).' AND '.$stat;
				//" AND `local_id`='".addslashes($this->PageID)."'";
		$result	= $this->DB->fetchAll($sql);
		
		if(empty($result[0])) die( Application_Model_Messages::message(404) );
		$excep0	= array('interface', 'free', 'SinglePost');
		if( $result[0]['is_allowed']!=1 and !in_array($this->renderer, $excep0) )	die(Application_Model_Messages::message(103));
		$excep0	= array('admin');
		$excep2	= array('admin','interface', 'free', 'SinglePost');
		if( $result[0]['page_state']==0 and !in_array($this->renderer, $excep0) )	die(Application_Model_Messages::message(101));
		if( $result[0]['page_state']==2 and !in_array($this->renderer, $excep2) )	die(Application_Model_Messages::message(404));
			
		if(!isset($this->_XAL) or !is_object($this->_XAL) ) $this->helper_ignite_XAL();
		$this->_XAL->disableAll();
		$this->_XAL->enable(array('execution', 'tree', 'item', 'if', 'param', 'param.get', 'param.post', 'param.env'));
		$result[0]['wb_xml']	= $this->_XAL->run('<execution>'.$result[0]['wb_xml'].'</execution>');
		$this->_XAL->enableAll();
		
		//$result[0]['wb_xml']	= '<root>'.$result[0]['wb_xml'].'</root>';
		
		return 	$result[0];
	}
	public function	getPageSkin() 
	{
		$sns	= ( isset($this->session_ns) )?$this->session_ns:'MyApp';
		$ses	= new Zend_Session_Namespace($sns);
		
		if(!empty($_GET['setskin']) )
			if($_GET['setskin']=='site')		$ses->SiD = $this->site['skin_id'];
			elseif($_GET['setskin']=='unset')	if(isset($ses->SiD))	unset($ses->SiD);

		if(!empty($_GET['sid']) and is_numeric($_GET['sid']))
			if( $skin = $this->getSkin(addslashes($_GET['sid'])) )
			{
				if($_GET['setskin']=='this')	$ses->SiD = $_GET['sid'];
				return 	$skin;
			}
		if(!empty($ses->SiD) and is_numeric($ses->SiD))
			if( $skin = $this->getSkin($ses->SiD) )	return 	$skin;

		if( $skin = $this->getSkin($this->page['skin_id']) )	return 	$skin;

		if( $skin = $this->getSkin($this->site['skin_id']) )	return 	$skin;
		
		$config	= Zend_Registry::get('config');
		$def_skin	= (is_numeric($config->default->skin))?$config->default->skin:1;
		if( $skin = $this->getSkin($def_skin) )	return 	$skin;

		if( $skin = $this->getSkin(1) )	return 	$skin;
		
	}
	public function	setPageDirection()
	{
		if($this->page['page_dir']==1) return true;
		$this->headPageDir	= '<link rel="stylesheet" href="#rasta-skinroot#ltr.css" type="text/css" media="screen" />';
		$this->site['wb_title'] = $this->site['latin_title'];
	}
	public function	getSkin($sid)
	{
		if($sid	== 0)	return false;
		$sql	=	 "SELECT sk.`skin_id`, sk.`wbs_id`, `sk`.`skin_path`,`sk`.`skin_blocks`, `bd`.`body_id`, `bd`.`body`"
					." FROM `wbs_skin_body` AS bd, `wbs_skin` AS sk"
					." WHERE (`sk`.body_id = `bd`.body_id)"
					." AND (`sk`.skin_id =".$sid.")"
					." AND ".Application_Model_Pubcon::get(1110, 'sk');
		if(!$result	= $this->DB->fetchAll($sql)) return false;
		
		$result[0]['body']	= stripslashes($result[0]['body']);
		return $result[0];
	}
}
?>
