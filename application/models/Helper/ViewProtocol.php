<?php

class Application_Model_Helper_ViewProtocol extends Zend_Controller_Action_Helper_Abstract
{

	public function preDispatch()
    {
							
		$bootstrap	= $this->getActionController()->getInvokeArg('bootstrap');
		$config		= $bootstrap->getOptions();
		
		$site		= Zend_Registry::get('site');
		$request	= $this->getRequest();
		$module		= $request->getModuleName();
		
		$this->setLayOut($module, $config);
		$this->setPortalModules($module, $config['app']);
		$this->checkWbState($module, $site);
		
		
	} 
	public function setLayOut($module, $config)
	{
		if (!isset($config[$module]['resources']['layout']['layout'])) return;
		$layoutScript = $config[$module]['resources']['layout']['layout'];
		$this->getActionController()
			 ->getHelper('layout')
			 ->setLayout($layoutScript);
	}
	public function setPortalModules($module, $config)
	{
		$resources	= array('controlpanel', 'rcpanel', 'godpanel'); 
		if(in_array($module, $resources) && $_SESSION['MyApp']['domain']!= $config['base']['portal'])	die(Application_Model_Messages::message(404));
	}
	public function checkWbState($module, $site)
	{
		$resources	= array('default', 'scenario'); 
		if($site['wb_status']==0 && in_array($module, $resources))	die(Application_Model_Messages::message(101));
	}
	
}