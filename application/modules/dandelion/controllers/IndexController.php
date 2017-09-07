<?php
 
class Dandelion_IndexController extends Zend_Controller_Action 
{

    public function indexAction()
    {
		$md_id		= $this->_getParam('form_id');

		if(strlen($md_id)>5)	
			if( $dandelion = $this->_fetchDandelion($md_id) )
				if( $acts = $this->_fetchDanActs($dandelion['dn_id']) )
					$acts = $this->_arrangeActs($acts);
		if(!is_array($acts) or count($acts)==0 or empty($dandelion['dn_default']) or !isset($acts[$dandelion['dn_default']]) )
		{
			header('HTTP', true, 500);
			die(Application_Model_Messages::message(404));
		}

		$this->acts	= $acts;
		$this->_callAct($dandelion['dn_default']);
		die();
	}


 	protected function	_callAct($name)
	{
		if( !isset($this->acts[$name]) )	return false;
		$this->acts[$name]	= array_map(trim, $this->acts[$name]);
		$this->_xal_setup($this->acts[$name]['da_type']);
		$return	= $this->_runAct($this->acts[$name]['da_xal']);
		if( (is_array($return) or $return=='true' or $return=='1') and !empty($this->acts[$name]['da_success']) )
			return $this->_callAct($this->acts[$name]['da_success']);
		elseif(!empty($this->acts[$name]['da_failure']))
			return $this->_callAct($this->acts[$name]['da_failure']);
	}
	protected function	_runAct($code)
	{
		$code	= trim($code);
		if( empty($code) ) return false;
		$code	= '<execution>'.stripslashes($code).'</execution>';
		return $this->_XAL->run($code);
	}
	protected function _fetchDandelion($md_id)
    {
		if(!is_object($this->_DB))	$this->_DB	= Zend_Registry::get('front_db');
		$sql		= "SELECT * FROM wbs_dandelions WHERE ".Application_Model_Pubcon::get()." AND `dn_md_id` = '".addslashes($md_id)."' AND `dn_status`=1";
		if(!$result = $this->_DB->fetchAll($sql)) return false;
		return $result[0];
	}
    protected function _fetchDanActs($dn_id)
    {
		$sql	= "SELECT * FROM wbs_dandelion_actions WHERE ".Application_Model_Pubcon::get(1100)." AND `da_dn_id` = ".addslashes($dn_id)
				. ' ORDER BY `da_id` ASC LIMIT 0, 20';
		if(!$result = $this->_DB->fetchAll($sql)) return false;
		return $result;
	}
    protected function _arrangeActs($acts)
    {
		$nacts	= array();
		foreach($acts as $act)
			$nacts[$act['da_name']]	= $act;
		return	$nacts;
	}
	protected function	_xal_setup($type=0)
	{
		if( !is_object($this->_XAL) )
		{
			$this->_XAL	= new Xal_Servlet();
			$this->_XAL->set_sqlite_root( realpath(APPLICATION_PATH .'/../data/db').'/'.WBSiD.'/' );
			$env['ENV_HOST_ID']=  WBSiD;
			$env['ENV_USER_ID']=  Application_Model_User::ID();
			
			$this->_XAL->set_env($env);
		}
		
		switch($type)
		{
			//case 0: break;
			case 1: 
		$this->_XAL->disable(array('rtc','workflow', 'soap'));
		$this->_XAL->enable(array('email'));
		$em	= 'if( !is_object($this->registry["email"]) ) $this->registry["email"]	= new Dandelion_Model_Email; return $this->registry["email"]->run($fn_argus);';
		$this->_XAL->set_xal_tag('email', $em);
			break;
			case 2:
		$this->_XAL->disable(array('email','workflow', 'soap'));
		$this->_XAL->enable(array('rtc'));
		$rtc	= 'if( !is_object($this->registry["rtc"]) ) $this->registry["rtc"]	= new Rtcmanager_Model_Xaloperator; return $this->registry["rtc"]->run($fn_argus);';
		$this->_XAL->set_xal_tag('rtc', $rtc);
			break;
			case 3:
		$this->_XAL->disable(array('rtc','email', 'soap'));
		$this->_XAL->enable(array('workflow'));
		$wf	= 'if( !is_object($this->registry["workflow"]) ) $this->registry["workflow"]	= new Workflow_Model_Workflow; return $this->registry["workflow"]->run($fn_argus);';
		$this->_XAL->set_xal_tag('workflow', $wf);
			break;
			case 4:
		$this->_XAL->disable(array('rtc','email','workflow'));
		$this->_XAL->enable(array('soap'));
		$soap	= 'if( !is_object($this->registry["soap"]) ) $this->registry["soap"]	= new Dandelion_Model_Soap; return $this->registry["soap"]->run($fn_argus);';
		$this->_XAL->set_xal_tag('soap', $soap);
			break;
		}
		
	}

}
