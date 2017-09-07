<?php
/*
	*	
*/
//require_once 'Html.php';

class Workflow_Model_Init
{

	protected $_DB;
	protected $_XAL;
	
	public function	__construct($ID)
	{
		$this->_DB 		= Zend_Registry::get('front_db');
		$this->wf_id	= $ID;
		if( !$WfENV = $this->_fetchWorkflow() )	die(Application_Model_Messages::message(404));
		$this->_setWflowEnv($WfENV);
		$this->_xal_setup();
		if( !$this->node = $this->_routWflow($WfENV['wf_router']) )	$this->node = $WfENV['wf_def_node'];
	}
	protected function	_fetchWorkflow()
	{
		$sql	= 'SELECT *, '.Application_Model_Pubcon::get(2001).' AS is_allowed FROM `wbs_workflows` '
				. ' WHERE '.Application_Model_Pubcon::get(1110)
				. ' AND wf_id= '.addslashes($this->wf_id);
		if( !$result = $this->_DB->fetchAll($sql) ) return false;
		return array_map(stripslashes, $result[0]);
	}
	protected function	_setWflowEnv($WfENV)
	{
		unset($WfENV['wf_router']);
		$_SESSION['WfENV']['wf']	= $WfENV;
//		unset($WnENV['wbs_id']);
//		if( !is_object($this->_XAL) ) $this->_XAL	= new Xal_Servlet();
//		$this->_XAL->set_session('WfENV', array('wf'=>$WfENV) ); 
	}
//	protected function	_fetchNodeId($title)
//	{
//		$sql	= 'SELECT `wn_id` FROM `wbs_workflow_nodes` '
//				. ' WHERE '.Application_Model_Pubcon::get(1110)
//				. ' AND wn_wf_id= '.addslashes($this->wf_id)
//				. ' AND wn_title= "'.addslashes($title).'"';
//		if( !$result = $this->_DB->fetchAll($sql) ) return false;
//		return $result[0]['wn_id'];
//	}
	protected function	_routWflow($wf_router)
	{
		$wf_router	= trim($wf_router);
		if( empty($wf_router) ) return false;
		$wf_router	= '<execution>'.$wf_router.'</execution>';
		$result	= $this->_XAL->run($wf_router);
		if( is_numeric($result) )	return $result;
		return true;
		
		//if( !is_array($result) )	return false;
		//if( is_numeric($result['var:node']) ) return $result['var:node'];
//		if( is_string($result['var:url']) ) return $result['var:url'];
//		if( is_numeric($result['var:node']) ) return '/workflow/node:'.$result['var:node'];
//		if( is_string($result['var:node']) and $wn_id = $this->_fetchNodeId($result['var:node']) ) return '/workflow/node:'.$wn_id;
		//return false;
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
}
?>