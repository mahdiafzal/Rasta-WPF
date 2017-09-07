<?php
/*
	*	
*/
//require_once 'Html.php';

class Workflow_Model_Node 
{

	protected $_DB;
	protected $_XAL;
	
	public function	__construct($ID)
	{
		$this->_DB 		= Zend_Registry::get('front_db');
		$this->wf_id	= false;
		if( $this->WfENV = $this->_getWflowEnv() ) $this->wf_id	= $this->WfENV['wf_id'];
		if($this->wf_id)	$this->initialize($ID);
		else				$this->restart($ID);
		
	}
	public function	initialize($ID)
	{
		$this->wn_id	= $ID;
		if( !$NODE = $this->_fetchNode() )	$this->_error(404);
		$this->_setWnodeEnv($NODE);
		
		$this->_clear_registry();
		$this->_xal_setup();
		if( $this->_wf_bootstrap() )
			if( $this->_wn_init($NODE['wn_init']) )	$this->_wn_page_render($NODE);

		//print_r($_SESSION);
		//die();
		 
		//$this->Post_id	= $NODE['wn_rtc_id']; //($NODE['wn_rtc_id']>0)?$NODE['wn_rtc_id']:false;
		//$this->_renderThePage(array($NODE['wn_page_id']));
	}
	public function	restart($ID)
	{
		$this->wn_id	= $ID;
		if( !$NODE = $this->_fetchNode() )	$this->_error(404);
		$this->_setWnodeEnv($NODE);

		$this->wf_id	= $NODE['wn_wf_id'];
		if( !$this->WfENV = $this->_fetchWorkflow() )	$this->_error(404);
		$this->_setWflowEnv($this->WfENV);
		
		$this->_clear_registry();
		$this->_xal_setup();
		if( $this->_wf_bootstrap() )
			if( $this->_wn_init($NODE['wn_init']) )	$this->_wn_page_render($NODE);
	}
	protected function	_xal_setup()
	{
		if( !is_object($this->_XAL) ) $this->_XAL	= new Xal_Servlet();
		$this->_XAL->set_sqlite_root( realpath(APPLICATION_PATH .'/../data/db').'/'.WBSiD.'/' );
		$this->_XAL->set_env(array('ENV_HOST_ID'=> WBSiD));
		$wf	= 'if( !is_object($this->Workf_handler) ) $this->Workf_handler	= new Workflow_Model_Workflow; return $this->Workf_handler->run($fn_argus);';
		$this->_XAL->set_xal_tag('workflow', $wf);
		//$this->_XAL->disable(array('print', 'die'));
	}
	protected function	_wf_bootstrap()
	{
		$this->WfENV['wf_bootstrap']	= trim($this->WfENV['wf_bootstrap']);
		if( empty($this->WfENV['wf_bootstrap']) ) return false;
		$this->WfENV['wf_bootstrap']	= '<execution>'.$this->WfENV['wf_bootstrap'].'</execution>';
		$result	= $this->_XAL->run($this->WfENV['wf_bootstrap']);
		if( is_array($result) )	return false;
		if( $result == 'true' )	return true;
		if( is_numeric($result) )	$this->_error($result);
		return false;
	}
	protected function	_wn_init($wn_init)
	{
		$wn_init	= trim($wn_init);
		if( empty($wn_init) ) return false;
		$wn_init	= '<execution>'.$wn_init.'</execution>';
		$result	= $this->_XAL->run($wn_init);
		if( is_array($result) )	return false;
		if( $result == 'true' )	return true;
		if( is_numeric($result) )	$this->_error($result);
		return false;
	}
	protected function	_wn_page_render($NODE)
	{
		$page	= new Workflow_Model_Node_Page;
		$page->Post_id	= $NODE['wn_rtc_id'];
		$page->renderThePage(array($NODE['wn_page_id']));
		$this->pageHead	= $page->getHtmlHead();
		echo $page->getHtmlBody();
	}
	protected function	_error($code)
	{
		$code	= (int) $code;
		$valids	= array(404);
		if( in_array($code, $valids) )
			die(Application_Model_Messages::message($code));
	}
	protected function	_clear_registry()
	{
		Zend_Registry::set('node_contents','');
//		$reg_keys	= array('sa');
//		$registry 	= Zend_Registry::getInstance();
//		foreach($reg_keys as $key)	$registry->set($key, '');
	}
	protected function	_fetchNode()
	{
		$sql	= 'SELECT *, '.Application_Model_Pubcon::get(2001).' AS is_allowed FROM `wbs_workflow_nodes` '
				. ' WHERE '.Application_Model_Pubcon::get(1110)
				. ( ($this->wf_id)?' AND wn_wf_id= '.addslashes($this->wf_id):'' )
				. ' AND wn_id= '.addslashes($this->wn_id);
		if( !$result = $this->_DB->fetchAll($sql) ) return false;
		return array_map(stripslashes, $result[0]);
	}
	protected function	_fetchWorkflow()
	{
		$sql	= 'SELECT *, '.Application_Model_Pubcon::get(2001).' AS is_allowed FROM `wbs_workflows` '
				. ' WHERE '.Application_Model_Pubcon::get(1110)
				. ' AND wf_id= '.addslashes($this->wf_id);
		if( !$result = $this->_DB->fetchAll($sql) ) return false;
		return array_map(stripslashes, $result[0]);
	}
	protected function	_setWnodeEnv($WnENV)
	{
		$_SESSION['WfENV']['wn']	= $WnENV;
//		unset($WnENV['wbs_id']);
//		if( !is_object($this->_XAL) ) $this->_XAL	= new Xal_Servlet();
//		$this->_XAL->set_session('WfENV', array('wn'=>$WnENV) );
	}
	protected function	_setWflowEnv($WfENV)
	{
		unset($WfENV['wf_router']);
		$_SESSION['WfENV']['wf']	= $WfENV;
//		unset($WnENV['wbs_id']);
//		if( !is_object($this->_XAL) ) $this->_XAL	= new Xal_Servlet();
//		$this->_XAL->set_session('WfENV', array('wf'=>$WfENV) ); 
	}
	protected function	_getWflowEnv()
	{
		if( isset($_SESSION['WfENV']['wf']) )	return $_SESSION['WfENV']['wf'];
		return false;
//		if( !is_object($this->_XAL) ) $this->_XAL	= new Xal_Servlet();
//		$WfENV	= $this->_XAL->get_session('WfENV');
//		if( isset($WfENV['wf']) )	return $WfENV['wf'];
	}
	

}
?>