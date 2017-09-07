<?php
/*
	*	SinglePost, Page  and Adminpage are 3 same moldels and Page model is base
*/
class Application_Model_SinglePost extends Application_Model_Page_Free
{

	public function	__construct($data)
	{
		$this->renderer	= 'SinglePost';
		$this->DB 		= Zend_Registry::get('front_db');
		$this->type		= $data['type'];
		$this->Post_id	= $data['Post_id'];
		if($data['type']=='t')		$this->Post_id	= $this->setPostData();
		elseif($data['type']=='g')	$this->Post_id	= $this->getGallery();
		
		parent::__construct($data);
		$this->setPostMetadata();
		if( $this->Post_id )
			$this->replacePageXml(array( array($data['type'], $this->Post_id) ));
			
		$this->analyzePageContentDeclaration();
		//$this->ContentIds	= $this->getContentIds();
		$this->segments		= $this->getPageSegments();
		$this->HeaderMenu	= $this->getHeaderMenu();
		$this->setPageTitle();
	}
	public function	setPageTitle()
	{
		if(! $this->Post_id ) return false;
		if($this->type == 'g')
		{
			$this->page['wb_page_title']	= $this->gallery[$this->Post_id]['title'];
			return true;
		}
		if($this->type == 't' and is_array($this->pageRTC[$this->Post_id]))
		{
			$this->page['wb_page_title']	= $this->pageRTC[$this->Post_id]['title1'];
			if($this->page['page_dir']==2 ) $this->page['wb_page_title'] = $this->pageRTC[$this->Post_id]['title2'];
			return true;
		}
	}
	public function	setPostData()
	{
		$sql	= 'SELECT co.id , co.type_id , me.* FROM wbs_rtcs AS co LEFT JOIN wbs_rtc_metadata AS me ON co.id = me.txt_id WHERE '.Application_Model_Pubcon::get(1111, 'co')
				. ' AND co.is_published=1 ';
		$sql2	= (is_numeric($this->Post_id))?' AND co.id ='.addslashes($this->Post_id):' AND co.title ="'.addslashes( str_replace("-", " ", $this->Post_id) ).'"';
		
		$result	= $this->DB->fetchAll($sql.$sql2);
		if(count($result)!=1) return false;

		$this->meta['description']	= $result[0]['description'];
		$this->meta['keywords']		= $result[0]['keywords'];
		$this->meta['authors']		= $result[0]['author'];
		$sql	= 'SELECT ts_single FROM wbs_content_type_setting WHERE '.Application_Model_Pubcon::get(1000).' AND `ts_ct_id`='.$result[0]['type_id'];
		$this->PageID	= $this->DB->fetchOne($sql);
		if($this->PageID==0)	die( Application_Model_Messages::message(404) );
		return $result[0]['id'];
	}
	public function	setPostMetadata()
	{
		$this->page['description']	= $this->meta['description'];
		$this->page['keywords']		= $this->meta['keywords'];
		$this->page['authors']		= $this->meta['authors'];
	}
	public function	getGallery()
	{
		$sql	= "SELECT `gallery_id` FROM `wbs_gallery` WHERE ".Application_Model_Pubcon::get()." AND `status`!=0 ";
		$sql2	= (is_numeric($this->Post_id))?' AND `gallery_id`='. addslashes($this->Post_id):' AND gallery_title ="'.addslashes( str_replace("_", " ", $this->Post_id) ).'"';

		if( $result	= $this->DB->fetchAll($sql.$sql2) )	return $result[0]['gallery_id'];
		return false;
	}
}
?>