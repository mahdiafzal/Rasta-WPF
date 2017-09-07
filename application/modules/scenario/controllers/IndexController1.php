<?php 
 
class Scenario_IndexController extends Zend_Controller_Action
{



    public function indexAction()
	{
		if($this->_request->isXmlHttpRequest())
		{
			$scen_id = $this->_getParam('id');
			if(!$scenario = $this->helper_getScenario($scen_id)) die(Application_Model_Messages::message(404));
			
			$data['scenario']			= $scenario;
			$data['content.properties']['data.fields']	= $this->_getParam('fields');
			$data['renderer'] 			= 'AJAX';
			$obj	= new Scenario_Model_Fetch($data);
			$this->_helper->json->sendJson( $obj->fetchAll('json') );
		}
		die(Application_Model_Messages::message(404));

	}
    public function feedAction()
	{
		$scen_id = $this->_getParam('id');
		if(!$scenario = $this->helper_getScenario($scen_id)) die(Application_Model_Messages::message(404));

/*		if( isset($_GET['output']) and $_GET['output']=='feed')
		{*/
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			$mode =( isset($_GET['mode']) and strtolower($_GET['mode'])=='atom')? 'atom':'rss';
			$feed	= new Scenario_Model_FetchFeed($scenario);
			echo $feed->generateFeed($mode);
			return true;
		/*}*/
		
		//$ads	= new Application_Model_Ads();		
		
/*		$content	= new Scenario_Model_Fetch($scenario);
		$this->view->assign('pageHead'		, $content->getHtmlHead());
		$HtmlBody	= $content->getHtmlBody();
		echo $HtmlBody;*/
		
		

	}
	
	
	public function helper_getScenario($id)
	{
		//echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		//die( $id );
		$id = addslashes($id);
		if(is_numeric($id))
			$stat = 'id='.$id;
		else //if(preg_match('/^[\w\d]+$/', $id))
			$stat = 'name="'.$id.'"';
		
		$this->DB	= Zend_Registry::get('front_db');
		$sql		= "SELECT *, ".Application_Model_Pubcon::get(2001)." AS is_allowed FROM `wbs_scenario` WHERE ".Application_Model_Pubcon::get(1110)." AND ".$stat;
		$result		= $this->DB->fetchAll($sql);
		
		if(count($result)==0) 				die(Application_Model_Messages::message(404));
		if( $result[0]['is_allowed']!=1 )	die(Application_Model_Messages::message(103));
		return $result[0];
	}


/*	public function lastpostsAction() 
    {

		die('scenario');
		if( isset($_GET['output']) and $_GET['output']=='feed')
		{
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			$mode =( isset($_GET['mode']) and strtolower($_GET['mode'])=='atom')? 'atom':'rss';
			$feed	= new Application_Model_LastPostsFeed;
			echo $feed->generateFeed($mode);
			return true;
		}
		
		//$ads	= new Application_Model_Ads();		
		
		$content	= new Application_Model_LastPosts;
		$this->view->assign('pageHead'		, $content->getHtmlHead());
		$HtmlBody	= $content->getHtmlBody();
		echo $HtmlBody;

    }
    public function searchAction() 
    {
		//$ads	= new Application_Model_Ads();		
		
		$this->_helper->viewRenderer->setNoRender();
		$content	= new Application_Model_Search;
		$this->view->assign('pageHead'		, $content->getHtmlHead());
		$HtmlBody	= $content->getHtmlBody();
		echo $HtmlBody;
    }
*/
//    public function commanderAction() 
//    {
//		$ads	= new Application_Model_Ads();	
//		$this->_helper->viewRenderer->setNoRender();
//		$content	= new Application_Model_Scenario_Commander;
//		die('dasdd');
//
//		$this->view->assign('pageHead'		, $content->getHtmlHead());
//		$HtmlBody	= $content->getHtmlBody();
//		echo $HtmlBody;
//    }

}