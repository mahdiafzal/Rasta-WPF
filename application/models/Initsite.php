<?php
class Application_Model_Initsite
{
	public function	__construct($data,$domain)
	{
		$registry 		= Zend_Registry::getInstance();  
		$this->DB 		= $registry['front_db'];
		if ( $this->checkSite($data['host_id']) )
		{
			$this->siteID		= $this->CreateSite($data);
			$this->userID		= $this->CreateUser($data['host_id']);
			$this->AddDomain($domain);
			$this->CreatePage();
			$this->state		= true;
		}
		else
		{
			$this->state		= false;
		}
	}
	public function	checkSite($host_id)
	{
		$sql	= "select `wb_id` from `wbs_profile` WHERE `host_id`=".$host_id.";";
		$result	= $this->DB->fetchAll($sql);
		if(is_array($result) && count($result)>0) return false;
		return 	true;
	}
	public function	CreateSite($data)
	{
		$data['skin_id']	= '1';
		$result				= $this->DB->insert('wbs_profile',$data);
		if(!$result) die();
		return 	$this->DB->lastInsertId();
	}
	public function	CreateUser($host_id)
	{
		$result		= $this->DB->fetchRow('select * from `host_users` where `id`='.$host_id);
		if(!$result) die();
		unset($result['id']);
		$result['is_active'	]	= '1';
		$result['wb_user_id']	= $this->siteID;
		$result['is_admin'	]	= '1';
		$this->DB->insert('users',$result);		

		return 	$this->DB->lastInsertId();
	}
	public function	AddDomain($domain)
	{
		$data['domain']	= $domain;		
		$data['wb_id']	= $this->siteID;
		$result			= $this->DB->insert('wbs_domain',$data);
		if(!$result) 	die();
	}
	public function	CreatePage()
	{
		$data1['wbs_id']		= $this->siteID;
		$data1['local_id']		= '11';
		$data1['wb_page_title']	= 'صفحه اصلی';
		$data1['wb_page_slogan']= '';
		$data1['skin_id']		= '0';//$res[0];
		$data1['header_menu_path']= '4.1';

		$data2['wbs_id']		= $this->siteID;
		$data2['local_id']		= '12';	
		$data2['wb_page_title']	= 'تک نما';
		$data2['wb_page_slogan']= '';
		$data2['skin_id']		= '0';//$res[0];
		$data2['header_menu_path']= '4.1';
			
		$result		= $this->DB->insert('wbs_pages',$data1);
		$result		= $this->DB->insert('wbs_pages',$data2);
	}


}
?>