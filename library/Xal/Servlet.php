<?php
/*
	*	
*/

require_once 'Xal/bin/intfuncs.php';


class Xal_Servlet extends InternalFunctions
{
	protected	$_constants		= array();
	protected	$_status		= 'live';
	protected	$_lockedfns		= array();
	protected	$_xal_ENV		= array(
								'ENV_HOST'=> '',
								'ENV_HOST_ID'=> '',
								'ENV_USER_ID'=> '',
								'ENV_REQUEST_URI'=>''
									);
	public	$_xal_configs	= array(
								'_lockedfns'	=> array(),
								'_db'			=> array('dbms'=>'sqlite', 'namespace'=>'default' ),
								'_sqlite_root'	=> '',
								'_se_space'		=> 'xal_apps',	// set session namespace for xal applications
									);
	protected	$_assigned_fns		= array();
	public		$registry		= array();
	
	public function	__construct($mode='NORMAL_MODE')
	{
		$this->setTheRunningMode($mode);
		$this->_xal_ENV['ENV_HOST']	= $_SERVER['HTTP_HOST'];
		$this->_xal_ENV['ENV_REQUEST_URI']	= $_SERVER['REQUEST_URI'];
		$this->_xal_ENV['ENV_HTTP_USER_AGENT']	= $_SERVER['HTTP_USER_AGENT'];

	}
	public function	setTheRunningMode($mode)
	{
		switch($mode)
		{
			case 'SAFE_MODE':
			$this->_lockedfns	= array('print', 'die', 'redirect', 'db.connect', 'db.update', 'db.insert', 'db.delete', 'db.last_record_id', 'db.query');
			break;
			case 'NORMAL_MODE':
			$this->_lockedfns	= array('db.query');
			break;
			case 'FULL_MODE':
			$this->_lockedfns	= array();
			break;
		}
	}
	public function	enable($arr)
	{
		foreach($arr as $fn)
		{
			$key	= array_search($fn, $this->_lockedfns);
			if( $key!== FALSE ) unset($this->_lockedfns[$key]);
		}
	}
	public function	enableAll()
	{
		$this->_lockedfns	= array();
	}
	public function	disable($arr)
	{
		$this->_lockedfns	= array_merge($this->_lockedfns, $arr);
	}
	public function	disableAll()
	{
		$this->_lockedfns	= array(
		'var', 'constant', /*'property',*/ 'value', 'call', 'join', 'replace', 'add', 'subtract', 'multiply', 'divide', 'ceil', 'floor', 'switch', 'for',
		'foreach', 'return', 'unset', 'if',
		'is.numeric',
		'is_equal', 'response', 'execution', /*'construct',*/ 'print', 'die', 'param', 'param.post', 'param.get', 'param.env', 'session.get', 'session.set', 'eval',
		'tree', 'tree.push', 'tree.print', 'item',
		/*'db',*/ 'db.connect', 'db.fetch', 'db.update', 'db.insert', 'db.last_record_id', 'db.query'
		);
		
	}
	public function	set_sqlite_root($path)
	{
		$this->_xal_configs['_sqlite_root']	= $path;
	}
	public function	set_db_handle($ns, $handle, $user)
	{
		require_once 'bin/db/pdo.php';
		$this->_db[$ns]['h']	= new Db_pdo($handle);
		$this->_db[$ns]['u']	= $user;
	}
	public function	set_env($env)
	{
		foreach($env as $name=>$value)
			if(isset($this->_xal_ENV[$name]) and !is_array($value) )
				$this->_xal_ENV[$name]	= $value;
	}
	public function	set_xal_tag($name, $function)
	{
		$this->_assigned_fns[$name]	= $function;
	}
	public function	set_session($ns, $data)
	{
		if( !isset($_SESSION[ $this->_xal_configs['_se_space'] ][$ns]) ) $_SESSION[ $this->_xal_configs['_se_space'] ][$ns]	= array();
		$_SESSION[ $this->_xal_configs['_se_space'] ][$ns]	= array_merge($_SESSION[ $this->_xal_configs['_se_space'] ][$ns], $data);
	}
	public function	get_session($ns)
	{
		if( isset($_SESSION[ $this->_xal_configs['_se_space'] ][$ns]) ) return $_SESSION[ $this->_xal_configs['_se_space'] ][$ns];
	}
	public function	clear_session($ns)
	{
		if( isset($_SESSION[ $this->_xal_configs['_se_space'] ][$ns]) ) unset($_SESSION[ $this->_xal_configs['_se_space'] ][$ns]);
	}
	public function	clearCustomTag($name)
	{
		if( isset($this->_assigned_fns[$name]) )	unset($this->_assigned_fns[$fn_name]);
		if( isset($this->registry[$name]) )			unset($this->registry[$name]);
	}

	
	public function	run($code, $pre_vars = array(), $namespace = 'this')
	{
		$xmlns	= 'xmlns:var="#variable" xmlns:item="#item" xmlns:function="#function" ';
		$xmlns	.= 'xmlns:property="#property" xmlns:class="#class" xmlns:constant="#constant"';
		$xmlns	.= 'xmlns:tag="#tag" xmlns:extend="#extend" ';
		$code	= '<xal '.$xmlns.'>'.$code.'</xal>';
			if(!$execution	= $this->gener_code_object($code, $namespace)) return false;
			//print_r($execution[0]); die();
		foreach($execution as $exe_code)
			if($this->_status == 'live')
				$pre_vars	= $this->sys_execution($exe_code, $pre_vars);
		return $pre_vars;
	}
	
	
	public function	add_library($path, $namespace)
	{

	}
	public function	add_class($code, $namespace)
	{

	}
	public function	add_function($code, $namespace)
	{

	}


	protected function gener_code_object($code, $namespace='this')
	{
		$doc = new DOMDocument();
		libxml_use_internal_errors(TRUE);
		$result	= $doc->loadXML($code);
		if(!$result)
		{
			libxml_clear_errors();
			return false;
		}

		$root	= $doc->documentElement;
		if($root->nodeType == XML_ELEMENT_NODE)
		{
			for ($i=0, $m=$root->childNodes->length; $i<$m; $i++)
			{
				$child = $root->childNodes->item($i);
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					if( $t=="execution" )
						$execution[]	= $this->execution_to_array($child);
						//$this->_apptree[$namespace]['execution'][]	= $this->execution_to_array($child);
					elseif( preg_match('/^function\:/', $t) )
						$this->_apptree[$namespace]/*['function']*/[$t]	= $this->regtags_to_array($child);
					elseif( preg_match('/^class\:/', $t) )
						$this->_apptree[$namespace]['class'][$t] 		= $this->regtags_to_array($child);
				}
			}
			if(is_array($execution)) return $execution;
		}
		return false;
	}
	protected function regtags_to_array($node)
	{
		$output = '';
		switch ($node->nodeType)
		{
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:	$output = trim($node->textContent); break;
			case XML_ELEMENT_NODE:
			for ($i=0, $m=$node->childNodes->length; $i<$m; $i++)
			{
				$child = $node->childNodes->item($i);
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					switch ($t)
					{
						case (preg_match('/^tag\:/', $t)?$t:''):
						case "value":
						case "tree":
						case "execution": $v = $this->execution_to_array($child); break;
						default			: $v = $this->regtags_to_array($child);
					}
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();

					$output[$t][] = $v;
				}
				else
				{
				
					$v = $this->regtags_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
			}
			
//			if( preg_match('/^tag\:/', $node->tagName) and $node->attributes->length )
//			{
//				$a = array();
//				foreach($node->attributes as $attrName => $attrNode)	$a[$attrName] = (string) $attrNode->value;
//				if(!is_array($output))	$output	= array($output);
//				$output['@attributes'] = $a;
//			}
			if(is_array($output))
				 foreach ($output as $t => $v)
					if(is_array($v) && count($v)==1 && $t!='@attributes')	$output[$t] = $v[0];
			break;
		}
	  return $output;
	}
	protected function execution_to_array($node)
	{
		$output = '';
		switch ($node->nodeType)
		{
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:	$output = trim($node->textContent); break;
			case XML_ELEMENT_NODE:
			for ($i=0, $m=$node->childNodes->length; $i<$m; $i++)
			{
				$child = $node->childNodes->item($i);
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					switch ($t)
					{
						case (preg_match('/^tag\:/', $t)?$t:''):
						case "value":
						case "tree":
						case "execution": $v = $this->execution_to_array($child); break;
						default			: $v = $this->regtags_to_array($child);
					
					}
					$output[$i][$t] = $v;
				}
				else
				{
				
					$v = $this->regtags_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
			}
			
			if( preg_match('/^tag\:/', $node->tagName) and $node->attributes->length )
			{
				$a = array();
				foreach($node->attributes as $attrName => $attrNode)	$a[$attrName] = (string) $attrNode->value;
				if(!is_array($output))	$output	= array($output);
				$output['@attributes'] = $a;
			}
//			if(is_array($output))
//				 foreach ($output as $t => $v)
//					if(is_array($v) && count($v)==1 && $t!='@attributes')	$output[$t] = $v[0];
			break;
		}
	  return $output;
	}
	protected function gener_library_object($path, $namespace='library')
	{
		try
		{
			$doc = new DOMDocument();
			$doc->load($path);
		}
		catch(Zend_exception $e)
		{
			return false;
		}
		$root	= $doc->documentElement;
		if($root->nodeType == XML_ELEMENT_NODE)
			for ($i=0, $m=$root->childNodes->length; $i<$m; $i++)
			{
				$child = $node->childNodes->item($i);
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					if( $t=="execution" )
						$this->_apptree[$namespace]['execution'][]	= $this->execution_to_array($child);
					elseif( preg_match('/^function\:/', $t) )
						$this->_apptree[$namespace]['function'][$t]	= $this->regtags_to_array($child);
					elseif( preg_match('/^class\:/', $t) )
						$this->_apptree[$namespace]['class'][$t] 		= $this->regtags_to_array($child);
				}
			}
	}
	
	
	
}

?>