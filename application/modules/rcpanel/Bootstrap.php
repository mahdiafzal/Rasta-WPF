<?php

class Rcpanel_Bootstrap extends Zend_Application_Module_Bootstrap
{
//	protected function _initConfig()
//    {
//		$_aconf = new Zend_Config_Ini(APPLICATION_PATH . "/modules/" . strtolower($this->getModuleName()) . "/configs/application.ini", APPLICATION_ENV);
//		$_mconf = '';
//		
//		if(preg_match('/^\/'.strtolower($this->getModuleName()).'/', strtolower($_SERVER['REQUEST_URI'])))
//			$_mconf = new Zend_Config_Ini(APPLICATION_PATH . "/modules/" . strtolower($this->getModuleName()) . "/configs/module.ini", APPLICATION_ENV);
//		
//		$_aconf	= (is_object($_aconf))?$_aconf->toArray():array();
//		$_mconf	= (is_object($_mconf))?$_mconf->toArray():array();
//		if(!function_exists('options_array_merge'))
//		{
//			function options_array_merge($_arr1, $_arr2)
//			{
//			  foreach($_arr2 as $key => $Value)
//				if(array_key_exists($key, $_arr1) && is_array($Value))	$_arr1[$key] = options_array_merge($_arr1[$key], $_arr2[$key]);
//				else	$_arr1[$key] = $Value;
//			  return $_arr1;
//			}
//		}
//		if(count($_aconf)>0)
//			$this->_application->_options	= options_array_merge($this->_application->_options, $_aconf);
//		if(count($_mconf)>0)
//			$this->_application->_options	= options_array_merge($this->_application->_options, $_mconf);
//		if(is_array($_aconf['rastak']) or is_array($_mconf['rastak']))
//			Zend_Registry::set('config', new Zend_Config($this->_application->_options['rastak']));
//	}
}