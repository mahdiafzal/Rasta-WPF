<?php 

class Help_Model_Article
{

	protected $_DB	;
	public function	__construct()
	{
		$this->_DB 		= Zend_Registry::get('front_db');

	}
	public function get($param)
	{
		if( is_numeric($param) )		return $this->_fetchById($param);
		elseif( is_string($param) )		return $this->_fetchByPath($param);
		elseif( is_array($param) )		return $this->_search($param);
	}
	protected function _search($param)
	{
		if( isset($param['id']) )			$result	= $this->_fetchById($param['id']);
		elseif( isset($param['path']) )		$result	= $this->_fetchByPath($param['path']);
		elseif( isset($param['stags']) )	$result	= $this->_fetchByStags($param['stags']);
		elseif( isset($param['atags']) )	$result	= $this->_fetchByAtags($param['atags']);
		elseif( isset($param['q']) )		$result	= $this->_fetchByKeywords($param['q']);
		
		if( !$result )						return false;
		if( !isset($param['q']) )			return $result;
		
		return $this->_filterResults($result, $param['q']);
	}
	protected function _filterResults($result, $q)
	{
//		if( isset($result['ha_art']) )	$result['ha_art']	= $this->_highlightKeywords($result['ha_art'], $q);
//		else
		if( is_array($result) )
			foreach($result as $key=>$case)
				$result[$key]['ha_art']	= $this->_highlightKeywords($case['ha_art'], $q);
		return $result;
	}
	protected function _highlightKeywords($text, $q)
	{
		if( ! $qs	= $this->helper_filter_user_query($q) ) return $text;
		foreach ($qs as $q) $text = preg_replace('/([^\s\,\.\-]*'.$q.'[^\s\,\.\-]*)/', "<b>$1</b>", $text);
		return $text;
	}
	protected function _fetchByStags($tags)
	{
		if( ! $tags	= $this->helper_filter_user_query($tags) ) return false;
		$sql	=	 'SELECT ha_id, ha_title, ha_path, ha_art_tags  FROM `sys_help_articles` WHERE `ha_sys_tags` RLIKE "(\/'.implode('\/)|(\/', $tags).'\/)";';
		if(!$result	= $this->_DB->fetchAll($sql)) return false;
		return $result;
	}
	protected function _fetchByAtags($tags)
	{
		if( ! $tags	= $this->helper_filter_user_query($tags) ) return false;
		$sql	=	 'SELECT ha_id, ha_title, ha_path, ha_art_tags  FROM `sys_help_articles` WHERE `ha_art_tags` RLIKE "(\/'.implode('\/)|(\/', $tags).'\/)";';
		if(!$result	= $this->_DB->fetchAll($sql)) return false;
		return $result;
	}
	protected function _fetchByKeywords($q)
	{
		if( ! $q	= $this->helper_filter_user_query($q) ) return false;
		$sql	=	 'SELECT ha_id, ha_title, ha_path, ha_art_tags  FROM `sys_help_articles` WHERE `ha_art` RLIKE "('.implode(')|(', $q).')";';
		if(!$result	= $this->_DB->fetchAll($sql)) return false;
		return $result;
	}
	protected function helper_filter_user_query($q)
	{
		$q	= trim($q);
		if( empty($q) ) return false;
		$q	= explode(' ', $q);
		$q	= array_map(trim, $q);
		if( empty($q[0]) ) return false;
		$q	= array_map(addslashes, $q);
		return $q;
	}
	protected function _fetchById($id)
	{
		if( !is_numeric($id) ) return false;
		$sql	=	 'SELECT * FROM `sys_help_articles` WHERE `ha_id`='.addslashes($id).';';
		if(!$result	= $this->_DB->fetchAll($sql)) return false;
		return $result;
	}
	protected function _fetchByPath($path)
	{
		$sql	=	 'SELECT * FROM `sys_help_articles` AS ha INNER JOIN `sys_help_article_path` AS ap ON ha.ha_id=ap.ap_ha_id WHERE ap.`ap_path`="'.addslashes($path).'";';
		if(!$result	= $this->_DB->fetchAll($sql)) return false;
		return $result;
	}


}