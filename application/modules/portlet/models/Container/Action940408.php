<?php
/*
	*	
*/

class Portlet_Model_Container_Action
{

	protected $_params	= array();
	protected $_module	= array();
	protected $_controller	= array();
	protected $_action	= array();
	protected $_layout	= array();
	protected $_sqlite_root		= '';
	protected $_invalid_dbs		= array('rastakinfo');
	protected $_DB	;
	protected $_XAL	;
	
	public function	__construct()
	{
		$this->_DB 		= Zend_Registry::get('front_db');
		$this->_sqlite_root	= realpath(APPLICATION_PATH .'/../data/db').'/'.WBSiD.'/';
	}
	public function	rout($path)
	{
		$parts	= explode(':', $path);
		if(count($parts)==1)		$params	= array('module'=>$parts[0], 'controller'=>'index', 'action'=>'index');
		elseif(count($parts)==2)	$params	= array('module'=>$parts[0], 'controller'=>$parts[1], 'action'=>'index');
		elseif(count($parts)>=3)	$params	= array('module'=>$parts[0], 'controller'=>$parts[1], 'action'=>$parts[2]);
		$this->setParams($params);
	}
	public function	renderAction()
	{
		if(! $this->_fetchScripts() ) die(Application_Model_Messages::message(404));
		$this->_layout	= $this->_getLayout();
		$this->_XAL	= new Xal_Servlet();
		//$this->_XAL->set_sqlite_root( $this->_sqlite_root );
		$this->_XAL->set_env(array('ENV_HOST_ID'=> WBSiD));
		

		$this->_doConfigs();
		$this->_bootstrapModule();
		$this->_initController();
		$this->_runAction();
		$this->_renderView();
		
		
		
	}
	public function	setParams($params)
	{
		$this->_params	= array_merge($this->_params, $params);
	}
	
	protected function	_renderView()
	{
		$this->helper_run_scripts($this->_layout['ly_code']);
//		$view	= trim($this->_layout['ly_code']);
//		if( empty($view) ) return;
//		$view	= '<execution>'.stripslashes($view).'</execution>';
//		$result	= $this->_XAL->run($view);
	}
	protected function	_runAction()
	{
		$this->helper_run_scripts($this->_action['ac_code']);
//		$action	= trim($this->_action['ac_code']);
//		if( empty($action) ) return;
//		$action	= '<execution>'.stripslashes($action).'</execution>';
//		$result	= $this->_XAL->run($action);
	}
	protected function	_initController()
	{
		$this->helper_run_scripts($this->_controller['cr_init']);
//		$init	= trim($this->_controller['cr_init']);
//		if( empty($init) ) return;
//		$init	= '<execution>'.stripslashes($init).'</execution>';
//		$result	= $this->_XAL->run($init);
	}
	protected function	_bootstrapModule()
	{
		$this->helper_run_scripts($this->_module['pr_bootstrap']);
//		$boot	= trim($this->_module['pr_bootstrap']);
//		if( empty($boot) ) return;
//		$boot	= '<execution>'.stripslashes($boot).'</execution>';
//		$result	= $this->_XAL->run($boot);
	}
	protected function	_doConfigs()
	{
		$configs	= trim($this->_module['pr_config']);
		if( empty($configs) ) return;
		$configs	= '<execution>'.stripslashes($configs).'</execution>';
		$this->_XAL->disableAll();
		$this->_XAL->enable(array('execution', 'tree'));
		$result	= $this->_XAL->run($configs);
		$this->enableRegularXalFns();
		if( !is_array($result) )	return;
		if( is_array($result['var:multidb']) ) $this->helper_multidb_connect($result['var:multidb']);
	}

	protected function	_getLayout()
	{
		$ses = new Zend_Session_Namespace('Portlets');
		
		if(!empty($_GET['setskin']) )
			if($_GET['setskin']=='portlet')		$ses->SiD = $this->_module['pr_layout'];
			elseif($_GET['setskin']=='section')	$ses->SiD = $this->_controller['cr_layout'];
			elseif($_GET['setskin']=='unset')	if(isset($ses->SiD))	unset($ses->SiD);

		if(!empty($_GET['sid']) and is_numeric($_GET['sid']))
			if( $skin = $this->_fetchLayout($_GET['sid']) )
			{
				if($_GET['setskin']=='this')	$ses->SiD = $_GET['sid'];
				return 	$skin;
			}
		if(!empty($ses->SiD) and is_numeric($ses->SiD))
			if( $skin = $this->_fetchLayout($ses->SiD) )	return 	$skin;

		if( $skin = $this->_fetchLayout($this->_action['cp_layout']) )	return 	$skin;
		if( $skin = $this->_fetchLayout($this->_controller['cr_layout']) )	return 	$skin;
		if( $skin = $this->_fetchLayout($this->_module['pr_layout']) )	return 	$skin;
		
	}
	protected function	_fetchLayout($lid)
	{
		if($lid	== 0)	return false;
		$sql	=	 'SELECT * FROM `wbs_portlet_layout` WHERE `wbs_id` IN (0,'.WBSiD.') AND `ly_id`='.addslashes($lid).';';
		if(!$result	= $this->_DB->fetchAll($sql)) return false;
		return $result[0];
	}
	protected function	_fetchScripts()
	{
		$sql	= 'SELECT * FROM `wbs_portlets` WHERE `wbs_id` IN (0,'.WBSiD.') AND pr_name= "'.addslashes($this->_params['module']).'" ORDER BY `pr_id` DESC LIMIT 0,1;';
		//$result	= $this->_DB->fetchAll($sql);
		if( !$result = $this->_DB->fetchAll($sql) ) return false;
		$this->_module	= $result[0];
		
		$sql	= 'SELECT * FROM `wbs_portlet_controllers` WHERE `wbs_id` IN (0,'.WBSiD.') AND `cr_name`= "'.addslashes($this->_params['controller'])
				. '" AND `cr_pr_id`='.$this->_module['pr_id'].'  ORDER BY `cr_id` DESC LIMIT 0,1;';
		if( !$result = $this->_DB->fetchAll($sql) ) return false;
		$this->_controller	= $result[0];
		
		$sql	= 'SELECT * FROM `wbs_portlet_actions` WHERE `wbs_id` IN (0,'.WBSiD.') AND `ac_name`= "'.addslashes($this->_params['action'])
				. '" AND `ac_cr_id`='.$this->_controller['cr_id'].'  ORDER BY `ac_id` DESC LIMIT 0,1;';
		if( !$result = $this->_DB->fetchAll($sql) ) return false;
		$this->_action	= $result[0];
		
		return true;
		print_r($this->_action);
		die('ddd');
		
	}
	
	protected function	enableRegularXalFns()
	{
		$this->_XAL->enableAll();
		// $this->_XAL->disable();
	}
	protected function	helper_multidb_connect($dbs)
	{
		foreach($dbs as $ns=>$data)
		{
			if( !$db = $this->helper_fetch_db($data) ) continue;
			if($db['dbms']==2)
			{
				$params	= array('host'=>'localhost', 'username'=>$db['db_un'], 'password'=>$db['db_pw'], 'dbname'=>$db['dbname']);
				if(! $dbh = Zend_Db::factory('PDO_MYSQL', $params) ) continue;
				try
				{
					$dbh->query('SET NAMES UTF8');
				}
				catch(Zend_exception $e)
				{
					continue;
				}
			}
			elseif($db['dbms']==1)
			{
				if(! $dbh = Zend_Db::factory('PDO_SQLITE', array('dbname'=> $this->_sqlite_root.$db['dbname']) ) ) continue;
				try
				{
					$dbh->getServerVersion();
				}
				catch(Zend_exception $e)
				{
					continue;
				}
			}
			$this->_XAL->set_db_handle($ns, $dbh, $db);
		}
	}
	protected function	helper_fetch_db($db)
	{
		if( empty($db['dbms']) or empty($db['dbname']) or empty($db['username']) or empty($db['password']) )	return false;
		if( !in_array($db['dbms'], array('sqlite','mysql')) or in_array($db['dbname'], $this->_invalid_dbs) )		return false;
		$db['dbms']	= ($db['dbms']=='mysql')?'2':'1';
		$db		= array_map(addslashes, $db );
		$sql	= 'SELECT * FROM `wbs_portlet_dbusers` WHERE `wbs_id` IN (0,'.WBSiD.') AND `dbms`='.$db['dbms'].' AND `dbname`="'.$db['dbname']
				. '" AND `du_username`="'.$db['username'].'" AND `du_password`="'.md5($db['password']).'" AND `wbs_group` IN ('.WBSgR.') LIMIT 0,1;';
		if(!$result	= $this->_DB->fetchAll($sql)) return false;
		return $result[0];
	}
	protected function	helper_run_scripts($code)
	{
		$code	= trim($code);
		if( empty($code) ) return false;
		$code	= '<execution>'.stripslashes($code).'</execution>';
		return $this->_XAL->run($code);
	}
	
}
?>