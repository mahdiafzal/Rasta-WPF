<?php

class Application_Model_Helper_UrlAliases extends Zend_Controller_Action_Helper_Abstract
{

	public function preDispatch()
    {
							
		$this->request	= $this->getRequest();
		$action			= $this->request->getActionName();
		if($action!='error') return false;
		
		//$module			= $this->request->getModuleName();
		//$controller		= $this->request->getControllerName();
		$params			= $this->request->getParams();
			
		$DB			= Zend_Registry::get('front_db');
		$uri		= preg_replace('/\?[\w\d]+.*$/', '', $_SERVER['REQUEST_URI']);
		$uri		= preg_replace('/\![\w\d\,]*$/', '', $uri);
		$sql		= "SELECT * FROM `wbs_url_alias` WHERE ".Application_Model_Pubcon::get(1110)."  AND status=1 AND `a_uri`='".$uri."'";
		$result		= $DB->fetchAll($sql);

		if(count($result)==0) return; // die(Application_Model_Messages::message(404));
		// redirect to target
		if( $result[0]['a_type']==1 )
		{
			Zend_OpenId::redirect($result[0]['a_target']);
			return;
		}
		// forward to target
		if( $result[0]['a_type']==2 )	$this->actionForwarding(array(explode('.', $result[0]['a_target']), $this->request->getParams()));

		
		
		
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