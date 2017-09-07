<?php

class Portlet_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initRoute()
    {
 		$ctrl	= Zend_Controller_Front::getInstance();
		$router = $ctrl->getRouter();
		$router->addRoute(
			'portlet',
			new Zend_Controller_Router_Route('portlet/:pr_path',
											 array( 'module' 		=> 'portlet',
											 		'controller' 	=> 'index',
												    'action' 		=> 'router'))
		);
	}
}

