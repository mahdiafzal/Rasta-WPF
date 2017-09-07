<?php
/*
	*	
*/
class Dataware_Model_Axml
{
	protected	$_prename;
	public		$_name;
	public		$_dbh;

	public function	__construct($axml)
	{
		$this->_atree	= $this->axml_to_atree($axml);
		$this->_getAllParams();
		$this->_aproperties	= $this->_atree['properties'];
		$this->_amethods	= $this->_atree['methods'];
		
		$this->sys_construct();
		
		//print_r($_REQUEST); die();
		//print_r($this->_aproperties); die();
		//$this->sys_call(array('method'=>'salam'), array());
		

	}
	protected function	_getAllParams()
	{
		if(! isset($this->_atree['properties']) ) return false;
		foreach($this->_atree['properties'] as $pname=>$pvalue)
		{
			if( is_array($pvalue) ) continue;
			if( preg_match('/\#rasta\.params\.[\w\d\.\-\_]+\#/', $pvalue, $prequest) )
			{
				$param_mode	= '';
				$param_value= '';
				$_modes	= array('post', 'get');
				$param_properties	= explode('.', $prequest[0], 4);
				if( count($param_properties) >= 4 and in_array($param_properties[2], $_modes) ) $param_mode	= $param_properties[2];
				
				switch($param_mode)
				{
					case 'post':
						$param_name	= str_replace( array('#', 'rasta.params.post.'), '', $prequest[0]);
						if( isset($_POST[$param_name]) ) $param_value= $_POST[$param_name];
						break;
					case 'get':
						$param_name	= str_replace( array('#', 'rasta.params.get.'), '', $prequest[0]);
						if( isset($_GET[$param_name]) ) $param_value= $_GET[$param_name];
						break;
					default:
						$param_name	= str_replace( array('#', 'rasta.params.'), '', $prequest[0]);
						if( isset($_POST[$param_name]) ) $param_value= $_POST[$param_name];
						elseif( isset($_GET[$param_name]) ) $param_value= $_GET[$param_name];
				}
				$this->_atree['properties'][ $pname ]	= $param_value;
			}
		}
	}
	







	protected function _function($fn_name, $fn_tree, $pre_vars)
	{

	}
	protected function _variable($var_name, $var_tree, $pre_vars)
	{
		if( is_array($var_tree) )	$var_tree	= $this->_axmlExecution($var_tree, $pre_vars);
		if(! is_array($var_tree)  )
			$pre_vars[ $var_name ]	= $var_tree;
		return $pre_vars;
	}
	protected function _property($pro_name, $pro_tree, $pre_vars)
	{
		if( is_array($pro_tree) )	$pro_tree	= $this->_axmlExecution($pro_tree, $pre_vars);
		if(! is_array($pro_tree) )
		{
			$this->_aproperties[ $pro_name ]	= $pro_tree;
			return true;
		}
		return false;
	}




	protected function _call($call_tree, $pre_vars)
	{

	}

	
	protected function _axmlExecution($com_tree, $pre_vars)
	{
	
			$com_name	= array_pop( array_keys($com_tree) );
			if( preg_match('/^property\:/', $com_name) )		return $this->_property($com_name, $com_tree[ $com_name ], $pre_vars);
			elseif( preg_match('/^variable\:/', $com_name) )	return $this->_variable($com_name, $com_tree[ $com_name ], $pre_vars);
			elseif( preg_match('/^function\:/', $com_name) )	return $this->_function($com_name, $com_tree[ $com_name ], $pre_vars);
			else
				if( $this->_isIdentifiedSystemFunction($com_name) ) return $this->__runSysFunction($com_name, $com_tree[ $com_name ], $pre_vars);
	}
	
	protected function sys_construct()
	{
		if(! isset($this->_atree['construct']) or !is_array($this->_atree['construct']) ) return false;
		$this->sys_execution($this->_atree['construct'], array());

	}
	protected function sys_execution($ex_tree, $pre_vars)
	{
		foreach($ex_tree as $com_tree)
		{
			$com_name	= array_pop( array_keys($com_tree) );
			$result	= $this->_axmlExecution($com_tree, $pre_vars);
			if( preg_match('/^variable\:/', $com_name) ){		$pre_vars	= $result; }
		}
	}
	protected function sys_variable($fn_tree, $pre_vars)
	{
		if( !is_array($fn_tree) and isset($pre_vars[ 'variable:'.$fn_tree ]) ) return $pre_vars[ 'variable:'.$fn_tree ];
		return $this->__errorHandler('vaiable is not defined');
	}
	protected function sys_property($fn_tree, $pre_vars)
	{
		if( !is_array($fn_tree) and isset($this->_aproperties[ 'property:'.$fn_tree ]) ) return $this->_aproperties[ 'property:'.$fn_tree ];
		return $this->__errorHandler('property is not defined');
	}
	protected function sys_value($fn_tree, $pre_vars)
	{
		if( !is_array($fn_tree) ) return $fn_tree;
		return $this->__errorHandler('value tag has not correct pattern');
	}
	protected function sys_call($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['method']) or is_array($fn_tree['method']) ) return $this->__errorHandler('call tag has not correct pattern');

		$fn_argus	= array();
		if( isset($fn_tree['arguments']) and is_array($fn_tree['arguments']) )
		{
			foreach($fn_tree['arguments'] as $arg_name=>$arg_value)
				if( !is_array($arg_value) )	$fn_argus[$arg_name]	= $arg_value;
				else	continue;
			unset($fn_tree['arguments']);	
		}
		$method_path	= explode('.', $fn_tree['method']);
		if( count($method_path)==1 )
			if( isset($this->_afunctions[ 'function:'.$method_path[0] ]) )
				$result	= $this->__runAxmlFunction($this->_afunctions[ 'function:'.$method_path[0] ], $fn_argus);
			
		elseif( count($method_path)==3 and  $method_path[0]=='custom' and  $method_path[1]=='method')
			if( isset($this->_amethods[ 'function:'.$method_path[2] ]) )
				$result	= $this->__runAxmlFunction($this->_amethods[ 'function:'.$method_path[2] ], $fn_argus);
			
		elseif( count($method_path)==3 and  $method_path[0]=='system' and  $method_path[1]=='method')
			if( $this->_isIdentifiedSystemMethod($method_path[2]) )
				$result	= $this->__runSysMethod($this->_amethods[ $method_path[2] ], $fn_argus);
		else
			return $this->__errorHandler("called method is not defined");
		$pre_vars[ 'variable:'.$fn_tree['method'].'.result' ]	= $result;
		if($result)
		{
			if( isset($fn_tree['success']) and is_array($fn_tree['success']) ) return $this->_axmlExecution($fn_tree['success'], $pre_vars);
			return true;
		}
		else
		{
			if( isset($fn_tree['failure']) and is_array($fn_tree['failure']) ) return $this->_axmlExecution($fn_tree['failure'], $pre_vars);
			return false;
		}
	}
	
	
	protected function __runAxmlFunction($fn_tree, $fn_argus)
	{
	
		if(! isset($fn_tree['execution']) or ! is_array($fn_tree['execution']) ) return $this->__errorHandler('function has not correct pattern');

		$fn_argus	= array();
		if( isset($fn_tree['arguments']) and is_array($fn_tree['arguments']) )
		{
			foreach($fn_tree['arguments'] as $arg_name=>$arg_value)
				if( !is_array($arg_value) )	$fn_argus[$arg_name]	= $arg_value;
				else	continue;
			unset($fn_tree['arguments']);	
		}
		return $this->_axmlExecution($fn_tree, $fn_argus);
	}
	
	
	
	protected function sys_join($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['item']) or ! is_array($fn_tree['item']) )	return $this->__errorHandler('join tag has not correct pattern');
		foreach($fn_tree['item'] as $item_key=>$item_value)
			if( is_array($item_value) )
				$fn_tree['item'][$item_key]	= $this->_axmlExecution($item_value, $pre_vars);
		try
		{
			return implode('', $fn_tree['item']);
		}
		catch(Zend_exception $e)
		{
			return $this->__errorHandler('unknown error acquired');
		}
	}
	protected function sys_inject($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['subject']) or ! isset($fn_tree['input']) or ! isset($fn_tree['key']) )
			return $this->__errorHandler('inject tag has not correct pattern');
		
		if( gettype($fn_tree['input']) != gettype($fn_tree['key']) )
			return $this->__errorHandler('inject tag has not correct pattern');
		if( is_array($fn_tree['input']) and  ! isset($fn_tree['input']['item']))
			return $this->__errorHandler('inject tag has not correct pattern');
		if( is_array($fn_tree['key']) and  ! isset($fn_tree['key']['item']))
			return $this->__errorHandler('inject tag has not correct pattern');
		if( is_array($fn_tree['input']) and  count($fn_tree['input']['item'])!=count($fn_tree['key']['item']))
			return $this->__errorHandler('inject tag has not correct pattern');
		
		if( is_array($fn_tree['subject']) )	$fn_tree['subject']	= $this->_axmlExecution($fn_tree['subject'], $pre_vars);

		
		if( is_array($fn_tree['input']) )
		{
			foreach($fn_tree['input']['item'] as $item_key=>$item_value)
				if( is_array($item_value) )	$fn_tree['input']['item'][$item_key]	= $this->_axmlExecution($item_value, $pre_vars);
			foreach($fn_tree['key']['item'] as $item_key=>$item_value)
				if( is_array($item_value) )	$fn_tree['key']['item'][$item_key]	= $this->_axmlExecution($item_value, $pre_vars);
		}
		else
		{
			$fn_tree['input']	= array('item'=> array($fn_tree['input']) );
			$fn_tree['key']		= array('item'=> array($fn_tree['key']) );
		}
		
		try
		{
			return str_replace($fn_tree['key']['item'], $fn_tree['input']['item'], $fn_tree['subject']);
		}
		catch(Zend_exception $e)
		{
			return $this->__errorHandler('unknown error acquired');
		}
	
	}
	protected function sys_add($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['left']) or ! isset($fn_tree['right']) )
			return $this->__errorHandler('add tag has not correct pattern');
		
		if( is_array($fn_tree['left']) )	$fn_tree['left']	= $this->_axmlExecution($fn_tree['left'], $pre_vars);
		if( is_array($fn_tree['right']) )	$fn_tree['left']	= $this->_axmlExecution($fn_tree['right'], $pre_vars);
		
		if(! is_numeric($fn_tree['left']) or ! is_numeric($fn_tree['right']) )
			return $this->__errorHandler('add tag has invalid inputs');
		return ($fn_tree['left'] + $fn_tree['right']);
	}
	protected function sys_subtract($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_multiply($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_divide($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_ceil($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_floor($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_switch($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_for($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_foreach($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_is_equal($fn_tree, $pre_vars)
	{
	
	}
	protected function sys_response($fn_tree, $pre_vars)
	{
	
	}








	
	protected function _isIdentifiedSystemMethod($method)
	{
		$identifiedMethods	= array(
		'variable', 'property', 'value', 'call', 'join', 'inject', 'add', 'subtract', 'multiply', 'divide', 'ceil', 'floor', 'switch', 'for', 'foreach', 
		'is_equal', 'response', 'execution'
												
		);
		if( in_array($method, $identifiedMethods) ) return true;
		return false;

	}
	protected function __runSysMethod($met_name, $met_argus)
	{
		switch($fn_name)
		{
			case 'variable': $this->sys_variable($fn_tree, $pre_vars); break;

		}
	}
	protected function _isIdentifiedSystemFunction($function)
	{
		$identifiedFunctions	= array(
		'variable', 'property', 'value', 'call', 'join', 'inject', 'add', 'subtract', 'multiply', 'divide', 'ceil', 'floor', 'switch', 'for', 'foreach', 
		'is_equal', 'response', 'execution'
												
		);
		if( in_array($function, $identifiedFunctions) ) return true;
		return false;

	}
	protected function __runSysFunction($fn_name, $fn_tree, $pre_vars)
	{
		switch($fn_name)
		{
			case 'variable': return $this->sys_variable($fn_tree, $pre_vars); break;
			case 'property': return $this->sys_property($fn_tree, $pre_vars); break;
			case 'value': return $this->sys_value($fn_tree, $pre_vars); break;
			case 'call': return $this->sys_call($fn_tree, $pre_vars); break;
			case 'join': return $this->sys_join($fn_tree, $pre_vars); break;
			case 'inject': return $this->sys_inject($fn_tree, $pre_vars); break;
			case 'add': return $this->sys_add($fn_tree, $pre_vars); break;
			case 'subtract': return $this->sys_subtract($fn_tree, $pre_vars); break;
			case 'multiply': return $this->sys_multiply($fn_tree, $pre_vars); break;
			case 'divide': return $this->sys_divide($fn_tree, $pre_vars); break;
			case 'ceil': return $this->sys_ceil($fn_tree, $pre_vars); break;
			case 'floor': return $this->sys_floor($fn_tree, $pre_vars); break;
			case 'switch': return $this->sys_switch($fn_tree, $pre_vars); break;
			case 'for': return $this->sys_for($fn_tree, $pre_vars); break;
			case 'foreach': return $this->sys_foreach($fn_tree, $pre_vars); break;
			case 'is_equal': return $this->sys_is_equal($fn_tree, $pre_vars); break;
			case 'response': return $this->sys_response($fn_tree, $pre_vars); break;
			case 'execution': return $this->sys_execution($fn_tree, $pre_vars); break;

		}
	}
	protected function __errorHandler($error)
	{
		return false;
	}

	protected function axml_to_atree($axmlstr)
	{
	  $doc = new DOMDocument();
	  $doc->load( realpath(APPLICATION_PATH . '/../library/Xal/XAL2.xml') );
	  //$doc->loadXML($axmlstr);
	  return $this->domnode_to_array($doc->documentElement);
	}

	protected function domnode_to_array($node)
	{
		$output = '';//array();
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
						case "construct":
						case "execution": $v = $this->execution_to_array($child); break;
						default			: $v = $this->domnode_to_array($child);
					}
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();
					$output[$t][] = $v;
				}
				else
				{
				
					$v = $this->domnode_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
			}
			
			if($node->attributes->length)
			{
				$a = array();
				foreach($node->attributes as $attrName => $attrNode)	$a[$attrName] = (string) $attrNode->value;
				if(!is_array($output))	$output	= array($output);
				$output['@attributes'] = $a;
			}
			if(is_array($output))
				 foreach ($output as $t => $v)
					if(is_array($v) && count($v)==1 && $t!='@attributes')	$output[$t] = $v[0];
			break;
		}
	  return $output;
	}

	protected function execution_to_array($node)
	{
		$output = '';//array();
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
						case "execution": $v = $this->execution_to_array($child); break;
						default			: $v = $this->domnode_to_array($child);
					
					}
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();
					$output[$i][$t] = $v;
				}
				else
				{
				
					$v = $this->domnode_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
			}
			
			if($node->attributes->length)
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










	public function _dbh($db, $new=false)
	{
		if(is_object($this->_dbh)) return $this->_dbh;
		
		$params['dbname']	= $this->_prename. "/".$db.".db";
		if($new==false and !is_file($params['dbname']) ) return false;
		$this->_dbh = Zend_Db::factory('PDO_SQLITE', $params);
		return $this->_dbh;
	}
	public function insert($table, $data)
	{
		return $this->_dbh->insert($table, $data);
	}
	public function update($table, $data, $where)
	{
		return $this->_dbh->update($table, $data, $where);
	}
	public function delete($table, $where)
	{
		return $this->_dbh->delete($table, $where);
	}
	public function select($cols='*', $table, $state=NULL)
	{
		$sql	= 'SELECT '.$cols.' FROM '.$table.' '.$state;
		return $this->_dbh->fetchAll($sql);
	}
	public function count($table, $state=NULL)
	{
		if(!empty($state))	$state	= 'WHERE '.$state;
		$sql	= 'SELECT COUNT(*) FROM '.$table.' '.$state;
		return $this->_dbh->fetchOne($sql);
	}
	public function fullDrop($table)
	{
//		if(empty($this->_prename) or empty($table))	return false;
//		if(is_object($this->_dbh))	$this->_dbh->closeConnection();
//		return unlink($this->_prename. "/".$this->_name.".db");
	}
	public function semiDrop($table)
	{
//		if(empty($this->_prename) or empty($table))	return false;
//		if(is_object($this->_dbh))	$this->_dbh->closeConnection();
//		$dfp	= $this->_prename. "/".$this->_name.".db";
//		return rename($dfp, $this->_prename. "/bc/".$this->_name.".db".'.bc');
	}
	public function semiEmpty()
	{
//		if(empty($this->_prename) or empty($this->_name))	return false;
//		$dfp	= $this->_prename. "/".$this->_name.".db";
//		$ret	= copy($dfp, $this->_prename. "/bc/".$this->_name.".db".'.bc');
//		if(!ret) return false;
//		$this->_dbh($this->_name);
//		$this->delete('');
//		return true;
	}
	

}

?>