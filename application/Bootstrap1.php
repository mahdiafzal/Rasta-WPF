<?php 
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initConfig()
    {
		$config	= new Zend_Config($this->_options['app']);
		Zend_Registry::set('config', $config);
		define('LANG', $config->language);
    }
	protected function _initDatabases()
    {
		$this->bootstrap('multidb');
		$resource	= $this->getResource('multidb');
    	$databases	= $this->_options['resources']['multidb'];
	    foreach ($databases as $name => $adapter)
	    {
	    	$db_adapter = $resource->getDb($name);
			$db_adapter->query('SET NAMES UTF8');
	    	Zend_Registry::set($name, $db_adapter);
	    }
		$this->fDB = $this->getResource('multidb')->getDb('front_db');
    }
	protected function _initHost()
    {
		$this->ses = new Zend_Session_Namespace('MyApp');
		if(empty($this->ses->WBSiD))
		{
			$wb_domain		= preg_replace("/^www\./", "", strtolower($_SERVER['HTTP_HOST']));
			//$DB = $this->getResource('multidb')->getDb('front_db'); //$this->DB['front_db'];
			$sql = "SELECT wb_id FROM wbs_domain WHERE domain='".$wb_domain."' ORDER BY `wb_id` ASC";
			$this->ses->WBSiD	= $this->fDB->fetchOne($sql);
			$this->ses->domain	= $wb_domain;
		}
		//Zend_Registry::set('WBSiD',  $this->ses->WBSiD);
		defined('WBSiD')
						|| define('WBSiD', $this->ses->WBSiD);
    }
	protected function _initSite()
    {
		$sql = "SELECT * FROM wbs_profile WHERE wb_id='".WBSiD."'";
		//$result	= $this->getResource('multidb')->getDb('front_db')->fetchAll($sql); //$this->DB['front_db']->fetchAll($sql);
		$result	= $this->fDB->fetchAll($sql);
		if(count($result)!=1) die(Application_Model_Messages::message(404));
		$result[0]['docroot'] = '/flsimgs';
		//try{
			if( isset($this->_options['app']['site']['docroot']) )
				$result[0]['docroot'] = $this->_options['app']['site']['docroot'];
		//}catch(Zend_exception $e){
			
		//}
		Zend_Registry::set('site', $result[0]);
		$this->ses->hostSize	= $result[0]['host_size'];
		$this->ses->docroot		= $result[0]['docroot'];
		defined('WBSgR')
						|| define('WBSgR', str_replace('/',',', preg_replace('/(^\/+)|(\/+$)/', '', $result[0]['wbs_group']) ) );
						
	}

	protected function _initRoute()
    {
 		$URI = $_SERVER['REQUEST_URI'];
		$uri_parts = explode('/', $URI);
		if(count($uri_parts)<2) return;
		
		switch($uri_parts[1])
		{
			case 'login':
				$ctrl	= Zend_Controller_Front::getInstance();
				$router = $ctrl->getRouter();
				$route = new Zend_Controller_Router_Route_Static('login', array('controller' => 'user', 'action' => 'frmlogin', 'module' 	=> 'admin') );
				$router->addRoute('login', $route);
				break;
			case 'page':
				$ctrl	= Zend_Controller_Front::getInstance();
				$router = $ctrl->getRouter();
				$router->addRoute(
					'webpage',
					new Zend_Controller_Router_Route('page/:webpage/*',
													 array( 'controller' 	=> 'index',
														    'action' 		=> 'index'))
				);	
				break;						
			case 'admin':
				if(isset($uri_parts[2]) and $uri_parts[2]=='page')
				{					
					$ctrl	= Zend_Controller_Front::getInstance();
					$router = $ctrl->getRouter();
					$router->addRoute('admin',
						new Zend_Controller_Router_Route('admin/page/:pageid/*',
														 array( 'module' 		=> 'admin',
														 		'controller' 	=> 'index',
															    'action' 		=> 'index'))
					);			
				}
				break;			
			case 'gallery':
				$ctrl	= Zend_Controller_Front::getInstance();
				$router = $ctrl->getRouter();
				$router->addRoute(
					'gallery',
					new Zend_Controller_Router_Route('gallery/:gallery_id/*',
													 array( 'module' 		=> 'default',
													 		'controller' 	=> 'index',
														    'action' 		=> 'gallery'))
				);
				break;			
			case 'rtc':
				$ctrl	= Zend_Controller_Front::getInstance();
				$router = $ctrl->getRouter();
				$router->addRoute(
					'rtc',
					new Zend_Controller_Router_Route('rtc/:rtc_id/*',
													 array( 'module' 		=> 'default',
													 		'controller' 	=> 'index',
														    'action' 		=> 'rtc'))
				);			
				break;	
			case 'feed':
				$ctrl	= Zend_Controller_Front::getInstance();
				$router = $ctrl->getRouter();
				$router->addRoute(
					'feed',
					new Zend_Controller_Router_Route('feed/:id/*',
													 array( 'module' 		=> 'scenario',
													 		'controller' 	=> 'index',
														    'action' 		=> 'feed'))
				);			
				break;		
			case 'comment':		case 'dandelion':	case 'dashboard':	case 'db':			case 'godpanel':	case 'help':
			case 'portlet':		case 'rcpanel':		case 'scenario':	case 'rtcmanager':	case 'skiner':		case 'stat':
			case 'usermanager':	case 'workflow':
			case 'public':
				break;	
			 default:
			 	if(!WBSiD)	return;
			 	$sql = 'SELECT * FROM wbs_custom_route WHERE '.Application_Model_Pubcon::get(1110).' AND status=1 AND (uri_route LIKE "'.addslashes($uri_parts[1]).'%")';
				if($result = $this->fDB->fetchAll($sql))
				{
					$ctrl	= Zend_Controller_Front::getInstance();
					$router = $ctrl->getRouter();
					foreach($result as $k=>$value)
						if($route = $this->helper_genCustomRouter($value))
							$router->addRoute('custom'.$k, $route);
				}
				
				break;
		}
	}
	protected function helper_genCustomRouter($data)
	{
		$target = explode('.', $data['target']);
		if(count($target)!=3) return false;
		$route = false;
		switch($data['route_type'])
		{
			case 1:
				$route = new Zend_Controller_Router_Route_Static($data['uri_route'], array('module'=> $target[0], 'controller'=> $target[1], 'action'=> $target[2]) );
				break;	
			case 2:
				$route = new Zend_Controller_Router_Route($data['uri_route'], array('module'=> $target[0], 'controller'=> $target[1], 'action'=> $target[2]) );
				break;	
			case 3:
				$route = new Zend_Controller_Router_Route_Regex($data['uri_route'], array('module'=> $target[0], 'controller'=> $target[1], 'action'=> $target[2]) );
				break;	
		}
		
		return $route;
	}
	protected function _initRemember()
    {
		$resources	= array(
					'\/admin\/page',
					'\/admin\/user\/login'
					);
		foreach($resources as $value)
			if(preg_match('/^'.$value.'/', $_SERVER['REQUEST_URI'])) 
			{
				Zend_Session::rememberMe(360000);
				return true;
			}
	}
	protected function _initActionHelper()
    {
    	$this->bootstrap('frontController');

    	//$UrlAliases			= Zend_Controller_Action_HelperBroker::addHelper(new Application_Model_Helper_UrlAliases());

    	$ViewProtocol		= Zend_Controller_Action_HelperBroker::addHelper(new Application_Model_Helper_ViewProtocol());
    	$AdminProtocol		= Zend_Controller_Action_HelperBroker::addHelper(new Application_Model_Helper_AdminProtocol());

		//if($_SESSION['MyApp']['domain'] == 'demo.'.$this->_options['app']['base']['domain'])
    	//$DemoProtocol	= Zend_Controller_Action_HelperBroker::addHelper(new Application_Model_Helper_DemoProtocol());
    }

}