<?php
/*
	*	
*/
class Application_Model_SinglePureRTC 
{

	public function	__construct($data)
	{
		$this->DB		= Zend_Registry::get('front_db');
		$this->PureRTC	= $this->getPureRTC($data['Post_id']);
	}
	public function	getPureRTC($data)
	{
		if(empty($data)) return false;
		$sql	= 'SELECT id AS unic, title AS title1, ltn_name AS title2, description AS abstract, publish_up, content AS text'
				. ' FROM wbs_rtcs '
				. ' WHERE '.Application_Model_Pubcon::get()
				. ' AND `is_published` != 0 AND `publish_up`<=NOW() AND `publish_down`>=NOW() AND id='.addslashes($data);

		//$sql	= "SELECT * FROM `wbs_rtcs` WHERE wbs_id='".WBSiD."' AND `is_published` != '0' AND `publish_up`<=NOW() AND `publish_down`>=NOW() AND id=".$data;
		if( !$result = $this->DB->fetchAll($sql) )	return false;
		//if(! is_array($result) || count($result)==0) return false;
		$result[0]['text']		= stripslashes($result[0]['text']);
		$result[0]['abstract']	= stripslashes($result[0]['abstract']);
		
//		$PageRTC['text']	= stripslashes($result1[0]['content']);
//		$PageRTC['abstract']= stripslashes($result1[0]['description']);
//		$PageRTC['title']	= $result1[0]['title'];
//		$PageRTC['unic']	= $result1[0]['id'];
		return	$result[0];
		//return	$PageRTC;	
	}
}
?>