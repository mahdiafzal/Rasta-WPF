<?php
/*
	*
*/
class Application_Model_Site_Components_Component 
{
	public function	__construct()
	{
		$registry 			= Zend_Registry::getInstance();  
		$this->DB 			= $registry['front_db'];
		$this->site			= $registry['site'];
		//$this->setUserData();
		$this->pubcon	= Application_Model_Pubcon::get();

	}
//	public function setUserData() 
//	{
//		$ses = new Zend_Session_Namespace('Zend_Auth');
//		$this->user['group']	= $ses->storage->user_group;
//		$this->user['is_admin']	= $ses->storage->is_admin;
//		$this->user['condition']	= '';
//		if($this->user['is_admin']!=1)
//		$this->user['condition']	= ' AND (user_group RLIKE "(^0$)'
//				. ( (empty($this->user['group']))?'") ':'|(\/'.str_replace('/','\/)|(\/',$this->user['group']).'\/)") ');
//	}
	public function	getComponentCount($table, $condition=NULL, $field='*')
	{
		//if(!empty($condition)) $condition = ' AND '. $condition;
		$sql		= 'SELECT COUNT('.$field.') AS `cnt` FROM `'.$table.'` WHERE '. $condition;
		$result		= $this->DB->fetchAll($sql);
		return	$result[0]['cnt'];	
	}
	public function	getSitePages($start=0, $limit=8)
	{
		//$pubcon	= Application_Model_Pubcon::get();
		$SitePages[0]	= $this->getComponentCount('wbs_pages', $this->pubcon, 'wb_page_id');
		$sql			= "SELECT local_id,wb_page_title FROM wbs_pages WHERE ".$this->pubcon." ORDER BY `local_id` DESC LIMIT ".$start.','.$limit;
		$result			= $this->DB->fetchAll($sql);
		$SitePages[1]	= $result;
		return 	$SitePages;
	}
	public function	getSiteScenarios($start=0, $limit=8)
	{
		//$pubcon	= Application_Model_Pubcon::get();
		$SiteScenarios[0]	= $this->getComponentCount('wbs_scenario', $this->pubcon, 'id');
		//$sql			= "SELECT id, title, uri  FROM `wbs_scenario` WHERE ".$this->pubcon." ORDER BY `id` DESC LIMIT ".$start.','.$limit;
		$sql			= "SELECT id, title  FROM `wbs_scenario` WHERE ".$this->pubcon." ORDER BY `id` DESC LIMIT ".$start.','.$limit;
		$result			= $this->DB->fetchAll($sql);
		$SiteScenarios[1]	= $result;
		return 	$SiteScenarios;
	}
	public function	getSiteRTCs($start=0, $limit=8)
	{
		//$pubcon	= Application_Model_Pubcon::get();
		$SiteRTC[0]	= $this->getComponentCount('wbs_rtcs', $this->pubcon, 'id');
		$sql		= 'SELECT `id`, `ltn_name`, `title` FROM `wbs_rtcs` WHERE '.$this->pubcon.' ORDER BY `wbs_rtcs`.`id` DESC LIMIT '.$start.','.$limit;
		$result		= $this->DB->fetchAll($sql);
		$SiteRTC[1]	= $result;
		return	$SiteRTC;	
	}
	public function	getSiteGalleries($start=0, $limit=8)
	{
		$SiteGalleries[0]	= $this->getComponentCount('wbs_gallery', $this->pubcon, 'gallery_id');
		$sql				= 'SELECT `gallery_id`, `gallery_title` FROM `wbs_gallery` WHERE '.$this->pubcon.' ORDER BY `gallery_id` DESC LIMIT '.$start.','.$limit;
		$result				= $this->DB->fetchAll($sql);
		$SiteGalleries[1]	= $result;
		return	$SiteGalleries;	
	}
	public function	getSiteExtLinks($start=0, $limit=8)
	{
		//$pubcon	= Application_Model_Pubcon::get(1110);
		$ExtLinks[0]	= $this->getComponentCount('wbs_links', $this->pubcon, 'id');
		$sql			= 'SELECT `title`, `url`, `id` FROM `wbs_links` WHERE '.$this->pubcon.' ORDER BY `id` DESC LIMIT '.$start.','.$limit;
		$result			= $this->DB->fetchAll($sql);
		$ExtLinks[1]		= $result;
		return	$ExtLinks;	
	}
	public function	getSiteSmMenus($start=0, $limit=8)
	{
		$SiteSmMenu[0]	= $this->getComponentCount('wbs_menu', $this->pubcon, 'id');
		$sql			= 'SELECT id, menu_title FROM wbs_menu WHERE '.$this->pubcon.' ORDER BY id DESC LIMIT '.$start.','.$limit;
		$result			= $this->DB->fetchAll($sql);
		$SiteSmMenu[1]	= $result;
		return	$SiteSmMenu;	
	}
}
?>