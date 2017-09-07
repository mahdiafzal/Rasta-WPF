<?php
/*
	*	
*/
require_once 'Html.php';
class Application_Model_Page_Common extends Application_Model_Page_Html
{

	public	$renderer	= 'common';
	public function	__construct($data)
	{
		parent::__construct($data);
		
		$this->analyzePageContentDeclaration();
		//$this->ContentIds	= $this->getContentIds();
		$this->segments		= $this->getPageSegments();
		$this->HeaderMenu	= $this->getHeaderMenu();
		//$this->headMetaData	= $this->getPageMetadata();
	}
/*	public function	getPageMetadata()
	{
		if( !empty($this->site['wb_description']) )	$description[]	= $this->site['wb_description'];	
		if( !empty($this->page['description']) )	$description[]	= $this->page['description'];
			
		if( !empty($this->site['wb_keywords']) )	$keywords[]		= $this->site['wb_keywords'];	
		if( !empty($this->page['keywords']) )		$keywords[]		= $this->page['keywords'];	

		if( !empty($this->site['wb_authors']) )		$authors[]		= $this->site['wb_authors'];	
		if( !empty($this->page['authors']) )		$authors[]		= $this->page['authors'];	

		$metaData	= '';
		if(!empty($description))	$metaData	.= '<meta name="description" content="'	. implode(',', $description)	.'" />'."\n\t";
		if(!empty($keywords))		$metaData	.= '<meta name="keywords" content="'	. implode(',', $keywords)		.'" />'."\n\t";
		if(!empty($authors))		$metaData	.= '<meta name="author" content="'		. implode(',', $authors)		.'" />'."\n\t";
		return $metaData;
	}*/
//	public function	parseSmMenu($data)
//	{
//		return Application_Model_Helper_Page::parseSmMenu($data);
//	}
	public function	_parseMlMenu($data)
	{
		return Application_Model_Helper_Page::parseMlMenu($data);
	}
}
?>