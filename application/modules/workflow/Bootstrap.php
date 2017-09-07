<?php

class Workflow_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initRoute()
    {
 		$ctrl	= Zend_Controller_Front::getInstance();
		$router = $ctrl->getRouter();
		$router->addRoute(
			'workflow',
			new Zend_Controller_Router_Route('workflow/:wf_path',
											 array( 'module' 		=> 'workflow',
											 		'controller' 	=> 'index',
												    'action' 		=> 'router'))
		);
	}
}

