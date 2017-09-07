<?php
/*
	*
*/
require_once 'Html.php';

class Application_Model_Page_Admin extends Application_Model_Page_Html
{
	public	$renderer	= 'admin';
	public function	__construct($data)
	{
		$this->session_ns	= 'PageAdmin';
		parent::__construct($data);
		$this->analyzePageContentDeclaration();
		//$this->ContentIds	= $this->getContentIds();
		$this->segments		= $this->getPageSegments();
		$this->HeaderMenu	= $this->getHeaderMenu();
	}
//	public function	parseSmMenu($data)
//	{
//		return Application_Model_Helper_Page::parseAdminSmMenu($data);
//	}
	public function	_parseMlMenu($data)
	{
		return Application_Model_Helper_Page::parseMlMenu($data, 'admin');
	}
}
?>