<?php
/*
	*	
*/
//require_once 'Html.php';

class Workflow_Model_Workflow 
{

	protected $_DB;
	protected $_XAL;
	
	public function	__construct()
	{
	}
	public function	__destruct()
	{
		//Zend_Registry::set('node_contents',$this->node_contents);
		//die('ddddddddddd');

	}
	public function	run($argus)
	{
		if( !isset($argus['method']) )	return '';
		$method	= $argus['method'];
		unset($argus['method']);
		switch($method)
		{
			case 'start.progress'	: return $this->_start_progress($argus); break;	// (wf_id)				+ run wf startup + insert new wp and return its id
			case 'start.stage'		: return $this->_start_stage($argus); break;	// get wp_id (and wn_id)+ run wn startup + insert new ws in running mode and return its id
			case 'pass.stage'		: return $this->_pass_stage($argus); break;	// get wp_id (and wn_id)+ run wn startup and shutdown + insert new ws in complete mode and return its id
			case 'end.progress'		: return $this->_end_progress($argus); break;	// get wp_id (and wf_id)+ run wf shutdown + update wp to complete mode 
			case 'end.stage'		: return $this->_end_stage($argus); break;	// get wp_id (and wn_id)+ update ws to complete mode and return its id
			case 'goto.node'		: return $this->_goto_node($argus); break;	// get wn_id			+ render the node page
			case 'next.stage'		: return $this->_next_stage($argus); break;	// get wp_id (and wn_id)+ return next nodes id and title for the progress 
			case 'is.valid.stage'	: return $this->_is_valid_stage($argus); break;	// get wp_id (and wn_id)+ return true if required stages passed before 
			
			case 'set.progresses'	: return $this->_set_progresses($argus); break;	// get wp_id (and wf_id)+ set wp in env
			case 'get.valid.progresses'	: return $this->_get_valid_progresses($argus); break;	// (wn_id)			+ returns wp's id that passed stages up to the node
			case 'get.valid.nodes'	: return $this->_get_valid_nodes($argus); break;	// (wf_id)				+ returns nodes id and title that user can access to them
			
			case 'append.block'		: return $this->_append_block($argus); break;	// 
			case 'prepend.block'	: return $this->_prepend_block($argus); break;	// 
			case 'append.row'		: return $this->_append_row($argus); break;	// 
			case 'prepend.row'		: return $this->_prepend_row($argus); break;	// 
			case 'replace.block'	: return $this->_replace_block($argus); break;	// 
			case 'replace.row'		: return $this->_replace_row($argus); break;	// 
			case 'get.env'			: return $this->_get_env($argus); break;	// 

		}
	}
	protected function	_start_progress($argus)
	{
		
	}	
	protected function	_start_stage($argus)
	{
		
	}	
	protected function	_pass_stage($argus)
	{
		
	}	
	protected function	_end_progress($argus)
	{
		
	}	
	protected function	_end_stage($argus)
	{
		
	}	
	protected function	_goto_node($argus)
	{
		
	}	
	protected function	_next_stage($argus)
	{
		
	}	
	protected function	_is_valid_stage($argus)
	{
		
	}
	protected function	_set_progresses($argus)
	{
		
	}
	protected function	_get_valid_progresses($argus)
	{
		
	}
	protected function	_get_valid_nodes($argus)
	{
		
	}
	protected function	_append_block($argus)
	{
		$this->helper_app_pre($argus, 'append', 'block');
	}
	protected function	_prepend_block($argus)
	{
		$this->helper_app_pre($argus, 'prepend', 'block');
	}
	protected function	_append_row($argus)
	{
		$this->helper_app_pre($argus, 'append', 'row');
	}
	protected function	_prepend_row($argus)
	{
		$this->helper_app_pre($argus, 'prepend', 'row');
	}
	protected function	_replace_block($argus)
	{
		$this->helper_replace_content($argus, 'block');
	}
	protected function	_replace_row($argus)
	{
		$this->helper_replace_content($argus, 'row');
	}
	protected function	_get_env($argus)
	{
		if(!is_string($argus['name']))	return '';
		$valids1	= array('workflow'=>'wf', 'node'=>'wn', 'progress'=>'wp');
		$name	= explode('.', $argus['name']);
		if( empty($name[1]) or empty($name[0]) or !isset($valids1[$name[0]]) )	return '';
		$ns	= $valids1[$name[0]];
		$valids2	= array('id'=>$ns.'_id', 'access'=>$ns.'_access', 'title'=>$ns.'_title', 'is_allowed'=>'is_allowed',
		 'bootstrap'=>$ns.'_bootstrap', 'startup'=>$ns.'_startup', 'shutdown'=>$ns.'_shutdown', 'init'=>$ns.'_init',
		  'default_node'=>'wf_def_node', 'type'=>$ns.'_type'/*, 'page'=>$ns.'_page_id', 'rtc'=>$ns.'_rtc_id',*/ );
		if( !isset($valids2[$name[1]]) )	return '';
		$key	= $valids2[$name[1]];
		if( isset($_SESSION['WfENV'][$ns][$key]) )	return $_SESSION['WfENV'][$ns][$key];
		return '';
	}


	protected function	helper_app_pre($argus, $pos, $type)
	{
		if(!is_numeric($argus['section'])) return;
		if( $type=='row' and !isset($argus['content']) )	return;
		$content	= ($type=='row')?$argus['content']:$argus;
		if(is_numeric($argus['index']))
		{
			$index	= (int)$argus['index'];
			$this->node_contents[$pos][$argus['section']][$index]	= array('type'=>$type, 'content'=>$content);
		}
		else
			$this->node_contents[$pos][$argus['section']][]	= array('type'=>$type, 'content'=>$content);
		Zend_Registry::set('node_contents',$this->node_contents);
	}
	protected function	helper_replace_content($argus, $type)
	{
		if(!is_numeric($argus['section'])) return;
		if( $type=='row' and !isset($argus['content']) )	return;
		$content	= ($type=='row')?$argus['content']:$argus;
		$this->node_contents['replace'][$argus['section']]	= array('type'=>$type, 'content'=>$content);
		Zend_Registry::set('node_contents',$this->node_contents);
	}


}
?>