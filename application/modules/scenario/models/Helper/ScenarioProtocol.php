<?php 

class Scenario_Model_Helper_ScenarioProtocol extends Zend_Controller_Action_Helper_Abstract
{

	var $message		= array();
						
	public function preDispatch()
    {
		
		$this->request	= $this->getRequest();
		$module			= $this->request->getModuleName();
		$controller		= $this->request->getControllerName();
		$action			= $this->request->getActionName();
		$params			= $this->request->getParams();
			if($action!='error') return false;
		$DB			= Zend_Registry::get('front_db');
		$uri		= preg_replace('/\?[\w\d]+.*$/', '', $_SERVER['REQUEST_URI']);
		$uri		= preg_replace('/\![\w\d\,]*$/', '', $uri);
		$sql		= "SELECT *, ".Application_Model_Pubcon::get(2001)." AS is_allowed FROM `wbs_scenario` WHERE ".Application_Model_Pubcon::get(1110)." AND `uri`='".$uri."'";
		$result		= $DB->fetchAll($sql);

		if(count($result)==0) return true; //die(Application_Model_Messages::message(404));
		if( $result[0]['is_allowed']!=1 )	die(Application_Model_Messages::message(103));
		Zend_Registry::set('scenario',  $result[0]);
		
		$action		= $this->actionIdentify($result[0]['action_id']);
		$this->actionForwarding(array($action, $this->request->getParams()));
	} 
	public function actionIdentify($data)
	{
		$actions	= array(
				'1'	=> array('scenario','index','lastposts'),
				'2'	=> array('scenario','index','search'),
				'6'	=> array('workflow','index','router')
				);
		return 	$actions[$data];
	}
	public function actionForwarding($data)
	{
		$this->getRequest()->setParams($data[1]) 
							->setModuleName($data[0][0])
							->setControllerName($data[0][1])
							->setActionName($data[0][2])
							->setDispatched(false);
		
	}


}