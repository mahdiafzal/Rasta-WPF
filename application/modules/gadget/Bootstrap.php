<?php

class Gadget_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initRoute()
    {
 		$ctrl	= Zend_Controller_Front::getInstance();
		$router = $ctrl->getRouter();
		$router->addRoute(
			'gadget',
			new Zend_Controller_Router_Route('gad/:gad_id',
											 array( 'module' 		=> 'gadget',
											 		'controller' 	=> 'index',
												    'action' 		=> 'interface'))
		);
	}
}