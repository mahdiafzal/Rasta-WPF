<?php

class Comment_Model_Comment 
{
	var $listStart	= 0;
	var $listLimit	= 15;
	var $contentId;
	var $contentType;
	
	
	public function	__construct()
	{
    	$this->DB	= Zend_registry::get('front_db');
	
	}
	public function getList($stat=2)
	{
		$statCon	= '  AND `status`='.$stat;
		if($stat=='all') $statCon	= '';
		$sql	= 'SELECT `wbs_content_comment`.*, `wbs_content_response`.`res_text` FROM `wbs_content_comment` LEFT JOIN `wbs_content_response`'
				. ' ON `wbs_content_comment`.`id`=`wbs_content_response`.`com_id`'
				. ' WHERE `wbs_content_comment`.`wbs_id` = '. WBSiD . $statCon .' AND `wbs_content_comment`.`content_id`='. $this->contentId
				. ' AND `wbs_content_comment`.`type_id`='. $this->contentType
				. ' ORDER BY `wbs_content_comment`.`id` DESC limit '. $this->listStart .','. $this->listLimit;
//		$sql	= 'select * from `wbs_content_comment` where `wbs_id` = '. WBSiD .' AND `content_id`='. $this->contentId 
//				. ' ORDER BY `id` DESC limit '. $this->listStart .','. $this->listLimit;
		$result	= $this->DB->fetchAll($sql);
		if(is_array($result) and count($result)>0) return $result;
		return false;
	}
	public function getListCount($stat=2)
	{
		$statCon	= '  AND `status`='.$stat;
		if($stat=='all') $statCon	= '';
		return $this->DB->fetchOne('SELECT COUNT(id) AS `cnt` FROM `wbs_content_comment` WHERE `wbs_id` = '. WBSiD . $statCon 
		.' AND `type_id`='. $this->contentType.' AND `content_id`='. $this->contentId);
	}
	public function getContentInfos()
	{
		if($this->contentType!='1') return false;
		$sql	= "SELECT `title`, `setting` FROM `wbs_rtcs` WHERE wbs_id='".WBSiD."' AND id =".$this->contentId;
		$result	= $this->DB->fetchAll($sql);
		if(is_array($result) and count($result)==1) return $result[0];
		return false;
	}
	public function setListInterval($start, $limit=NULL)
	{
		$this->listStart	= $start;
		if(!empty($limit))	$this->listLimit	= $limit;
	}
}