<?php
/*
	*	
*/

class InternalFunctions
{
	protected	$_db 			= array();
	protected	$__is			= NULL;
	protected	$__db			= NULL;


	public function	__construct()
	{
		
	}	
	private function _cons_is_object()
	{
		require_once 'is.php';
		$this->__is = new is($this);
	}
	private function _cons_db_object()
	{
		require_once 'db.php';
		$this->__db = new db($this);
	}	

/// xml namespaces
	protected function _function($fn_name, $fn_tree)
	{
		$this->_apptree['this'][$fn_name]	=	$fn_tree;
	}
	protected function _tag($fn_name, $fn_tree, $pre_vars)
	{
		$fn_name	= str_replace('tag:', '', $fn_name);
		$attrs	= '';
		if(!is_array($fn_tree)) return $this->helper_html_tag($fn_name, $attrs, $fn_tree);
		
		if(isset($fn_tree['@attributes']))
		{
			foreach($fn_tree['@attributes'] as $a_name=>$a_value)
				$attrs	.= ' '.$a_name.'="'.$a_value.'"';
			unset($fn_tree['@attributes']);
		}
		$output	= array();
		foreach($fn_tree as $com_tree)
		{
			if(!is_array($com_tree))	return $this->helper_html_tag($fn_name, $attrs, $com_tree);
			$com_name	= array_pop( array_keys($com_tree) );
			$result	= $this->_axmlExecution($com_tree, $pre_vars);
			if( preg_match('/^var\:/', $com_name) ){		$pre_vars[$com_name]	= $result;  /*print_r($pre_vars);*/ }
			elseif( 'foreach'==$com_name or  'for'==$com_name or 'unset'==$com_name ){		$pre_vars	= $result;  /*print_r($pre_vars); */ }
			$output[]	= $result;
		}
		return $this->helper_html_tag($fn_name, $attrs, implode('', $output) );
	}
	protected function _var($var_tree, $pre_vars)
	{
		/*if( is_array($var_tree) )	*/
		$var_tree	= $this->_axmlExecution($var_tree, $pre_vars);
		return $var_tree;
		
	}
	
	protected function _extend($fn_name, $fn_tree, $pre_vars)
	{
		if(is_array($fn_tree)) $fn_tree = $this->_axmlExecution($fn_tree, $pre_vars);
		
		if(is_string($fn_tree) and strlen($fn_tree)>1) 
		{
			$fn_name = str_replace('extend:', '', $fn_name);
			$fn_tree = 'Xal_Extension_'. trim($fn_tree);
			try
			{
				$this->set_xal_tag( $fn_name, new $fn_tree() ) ;
				return true;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		return false;
	}
	
	
	
	protected function _constant($cons_name, $cons_tree, $pre_vars)
	{
		/*if( is_array($var_tree) )	*/
		$cons_tree	= $this->_axmlExecution($cons_tree, $pre_vars);
		if( is_string($cons_tree) )
			$this->_constants[ $cons_name ]	= $cons_tree;
		//return true;
	}
	protected function _item($item_tree, $pre_vars)
	{
		if( is_array($item_tree) )	$item_tree	= $this->_axmlExecution($item_tree, $pre_vars);
		return $item_tree;
	}
	public function _axmlExecution($com_tree, $pre_vars)
	{
		if($this->_status != 'live') return;
		if(!is_array($com_tree)) return $com_tree;
		$com_name	= array_pop( array_keys($com_tree) );
		if( preg_match('/^var\:[a-zA-Z_][a-zA-Z0-9_]*$/', $com_name) )	return $this->_var($com_tree[ $com_name ], $pre_vars);
		elseif( preg_match('/^constant\:[a-zA-Z_][a-zA-Z0-9_]*$/', $com_name) )		return $this->_constant($com_name, $com_tree[ $com_name ], $pre_vars);
		elseif( preg_match('/^function\:/', $com_name) )	return $this->_function($com_name, $com_tree[ $com_name ]);
		elseif( preg_match('/^tag\:/', $com_name) )	return $this->_tag($com_name, $com_tree[ $com_name ], $pre_vars);
		elseif( preg_match('/^extend\:[a-zA-Z][a-zA-Z0-9_\.]*$/', $com_name) )		return $this->_extend($com_name, $com_tree[ $com_name ], $pre_vars);
		else
			if( $this->_isIdentifiedSystemFunction($com_name) ) return $this->__runSysFunction($com_name, $com_tree[ $com_name ], $pre_vars);
	}

	protected function helper_html_tag($name, $attrs, $content)
	{
		if($name=='input')	return '<'.$name.$attrs.((!empty($content))?' value="'.htmlspecialchars($content).'"':'').' />';
		elseif($name=='img')return '<'.$name.$attrs.((!empty($content))?' src="'.htmlspecialchars($content).'"':'').' />';
		elseif($name=='link')return '<'.$name.$attrs.((!empty($content))?' href="'.htmlspecialchars($content).'"':'').' />';
		elseif($name=='br' or $name=='meta' or  $name=='hr')	return '<'.$name.$attrs.' />';
		elseif($name=='textarea')return '<'.$name.$attrs.'>'.htmlspecialchars($content).'</'.$name.'>';
		return '<'.$name.$attrs.'>'.$content.'</'.$name.'>';
	}

/// Internal Functions	
	
	protected function sys_execution($ex_tree, $pre_vars)
	{
		if(!is_array($ex_tree)) return false;
		foreach($ex_tree as $com_tree)
		{
			if($this->_status != 'live') return; // for procedure endding
			$com_name	= array_pop( array_keys($com_tree) );
			$result	= $this->_axmlExecution($com_tree, $pre_vars);
			if( preg_match('/^var\:[a-zA-Z_][a-zA-Z0-9_]*$/', $com_name) ){		$pre_vars[$com_name]	= $result;  /*print_r($pre_vars);*/ }
			elseif( 'foreach'==$com_name or  'for'==$com_name or  'if'==$com_name or 'switch'==$com_name or 'unset'==$com_name ){		$pre_vars	= $result;  /*print_r($pre_vars); */ }
			elseif( 'return'==$com_name and !in_array($com_name, $this->_lockedfns) ){ /*die($result);*/	return $result;}
		}
		return $pre_vars;
	}
	protected function sys_var($fn_tree, $pre_vars)
	{

		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( !is_string($fn_tree) or empty($fn_tree) ) return false;
		$varpath	= explode('.', $fn_tree);
		if(count($varpath)==1)
		{
			if(isset($pre_vars[ 'var:'.$fn_tree ]) ) return $pre_vars[ 'var:'.$fn_tree ];
		}
		else
		{
			$varvalue	= $pre_vars[ 'var:'.$varpath[0] ];
			unset($varpath[0]);
			foreach($varpath as $key)
			{
				if(is_numeric($key)) return false;
				if(preg_match("/^eq\:\d+$/", $key)) $key = str_replace('eq:','', $key);
				if( !isset($varvalue[$key]) ) return false;
				$varvalue	= $varvalue[$key];
			}
			return $varvalue;
		}
		return $this->__errorHandler('vaiable is not defined');
	}
	protected function sys_constant($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if(is_string($fn_tree))
			if(isset($this->_constants['constant:'.$fn_tree])) return $this->_constants['constant:'.$fn_tree];
		return '';
	}
	protected function sys_value($fn_tree, $pre_vars)
	{
		//print_r($fn_tree); //die();
		if( !is_array($fn_tree) ) return $fn_tree;
		$_string = '';
		foreach($fn_tree as $fn_val)
		{
			$fn_val = $this->_axmlExecution($fn_val, $pre_vars);
			if( is_string($fn_val)) $_string = $_string.$fn_val;
		}
		return $_string;
	}
	protected function sys_unset($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( !is_string($fn_tree) or empty($fn_tree) ) return $pre_vars;
		$varpath	= explode('.', $fn_tree);
		$var_name	= array_shift($varpath);
		if(empty($var_name))		return $pre_vars;
		if(count($varpath)==0)		unset($pre_vars[ 'var:'.$fn_tree ]);
		elseif(count($varpath)==1)	unset($pre_vars[ 'var:'.$var_name ][ $varpath[0] ]);
		else						$pre_vars['var:'.$var_name]	= $this->helper_unsetTreeItem($varpath, $pre_vars['var:'.$var_name]) ;
		return $pre_vars;
	}
	protected function sys_call($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['name']) ) return $this->__errorHandler('call tag has not correct pattern');
		$fn_tree['name']	= $this->_axmlExecution($fn_tree['name'], $pre_vars);
		if( !is_string($fn_tree['name']) or empty($fn_tree['name']) ) return;
		$namepath	= explode('.', $fn_tree['name']);
		$fnname		= array_pop($namepath);
		$fn_ns		= (count($namepath)>0)?implode('.', $namepath):'this';
		if(! $cfn_tree = $this->_apptree[$fn_ns]['function:'.$fnname])	return;
		
		if(!is_array($cfn_tree['arguments']))	return $this->sys_execution($cfn_tree['execution'], array());
		
		$fn_argus	= array();
		if( is_array($fn_tree['arguments']) )	$cfn_tree['arguments']	= array_merge($cfn_tree['arguments'], $fn_tree['arguments']);
		foreach($cfn_tree['arguments'] as $arg_name=>$arg_value)
			$fn_argus['var:'.$arg_name]	= $this->_axmlExecution($arg_value, $pre_vars);
		return $this->sys_execution($cfn_tree['execution'], $fn_argus);
	}
	protected function sys_return($fn_tree, $pre_vars)
	{
		return $this->_axmlExecution($fn_tree, $pre_vars);
	}
	
	
	
/// string functions
	protected function sys_print($fn_tree, $pre_vars)
	{
	/*	$_string = "";
		if(is_array($fn_tree))
			foreach($fn_tree as $fn_val)
			{
				$fn_val = $this->_axmlExecution($fn_val, $pre_vars);
				if( is_string($fn_val)) $_string = $_string.$fn_val;
			}
		elseif(is_string($fn_tree))
			$_string = $fn_tree;
		echo $_string;*/
		if(is_string($fn_tree))
		{
			echo $fn_tree;
			return;
		}
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( is_string($fn_tree) or is_numeric($fn_tree) )	echo $fn_tree;
	}
	protected function sys_tree_print($fn_tree, $pre_vars)
	{
		if(!is_array($fn_tree)) return;
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		//if(!is_array($fn_tree)) return;
		print_r($fn_tree);
	}
	protected function sys_die($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( is_string($fn_tree) or is_numeric($fn_tree) )	print $fn_tree;
		$this->_status = 'die';
	}
	protected function sys_join($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( is_array($fn_tree) )
			return implode('', $fn_tree);
		return '';
	}
	protected function sys_replace($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['subject']) or ! isset($fn_tree['input']) or ! isset($fn_tree['key']) )
			return $this->__errorHandler('replace tag has not correct pattern');
		
		$fn_tree['input']	= $this->_axmlExecution($fn_tree['input'], $pre_vars);
		$fn_tree['key']		= $this->_axmlExecution($fn_tree['key'], $pre_vars);
		
		if( !is_array($fn_tree['key']) and  is_array($fn_tree['input']) )
			return $this->__errorHandler('replace tag has not correct pattern');

		$fn_tree['subject']	= $this->_axmlExecution($fn_tree['subject'], $pre_vars);
		
		try
		{
			return str_replace($fn_tree['key'], $fn_tree['input'], $fn_tree['subject']);
		}
		catch(Zend_exception $e)
		{
			return $this->__errorHandler('unknown error acquired');
		}	
	}

	///Math Functions
	protected function sys_operator($operator, $fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['left']) or ! isset($fn_tree['right']) )
			return $this->__errorHandler($operator.' tag has not correct pattern');
		/*if( is_array($fn_tree['left']) )	*/$fn_tree['left']	= $this->_axmlExecution($fn_tree['left'], $pre_vars);
		/*if( is_array($fn_tree['right']) )	*/$fn_tree['right']	= $this->_axmlExecution($fn_tree['right'], $pre_vars);
		//print_r($fn_tree); die();
		
		/*if(! is_numeric($fn_tree['left']) or ! is_numeric($fn_tree['right']) )
			return $this->__errorHandler($operator.' tag has invalid inputs');*/
		if(! is_numeric($fn_tree['left']) ) $fn_tree['left'] = 0;
		if(! is_numeric($fn_tree['right'])) $fn_tree['right'] = 0;
		
		switch($operator)
		{
			case 'add':			return ($fn_tree['left'] + $fn_tree['right']); break;
			case 'subtract':	return ($fn_tree['left'] - $fn_tree['right']); break;
			case 'multiply':	return ($fn_tree['left'] * $fn_tree['right']); break;
			case 'divide':		return ($fn_tree['left'] / $fn_tree['right']); break;
			case 'modulo':		return ($fn_tree['left'] % $fn_tree['right']); break;
			//case 'is.equal':	return ( ($fn_tree['left']==$fn_tree['right'])?true:false ); break;
		}
		
	}
	protected function sys_ceil($fn_tree, $pre_vars)
	{
		if( is_array($fn_tree) )	$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( is_numeric($fn_tree) )	return ceil($fn_tree);
		return false;
	}
	protected function sys_floor($fn_tree, $pre_vars)
	{
		if( is_array($fn_tree) )	$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( is_numeric($fn_tree) )	return floor($fn_tree);
		return false;
	}





	/// Envaromental Variables
	protected function sys_param($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if(!is_string($fn_tree))	return false;
		if(isset($_POST[$fn_tree])) return $_POST[$fn_tree];
		if(isset($_GET[$fn_tree])) return $_GET[$fn_tree];
		return false;
	}
	protected function sys_param_post($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if(!is_string($fn_tree))	return false;
		if(isset($_POST[$fn_tree])) return $_POST[$fn_tree];
		return false;
	}
	protected function sys_param_get($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if(!is_string($fn_tree))	return false;
		if(isset($_GET[$fn_tree])) return $_GET[$fn_tree];
		return false;
	}
	protected function sys_param_uri($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		
		if(!is_string($fn_tree))	return false;
		$uri_parts = explode('/', $_SERVER['REQUEST_URI']);
		$fn_tree = urlencode($fn_tree);
		foreach($uri_parts as $key=>$value)
		{			
			if($fn_tree == $value)
				if(isset($uri_parts[$key+1]))
					return urldecode($uri_parts[$key+1]);
		}
		///if(isset($_GET[$fn_tree])) return $_GET[$fn_tree];
		return false;
	}
	protected function sys_param_env($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if(!is_string($fn_tree)) return false;
		
		if(preg_match("/^(http)?s?(\:\/\/)?".str_replace('.', '\.', $_SERVER['HTTP_HOST'])."/", $_SERVER['HTTP_REFERER']))
			$this->_xal_ENV['ENV_INT_REFERER']	= $_SERVER['HTTP_REFERER'];
		else
			$this->_xal_ENV['ENV_EXT_REFERER']	= $_SERVER['HTTP_REFERER'];
		
		if(isset($this->_xal_ENV[$fn_tree])) return $this->_xal_ENV[$fn_tree];
		return false;
	}
	protected function sys_session_get($fn_tree, $pre_vars)
	{
		if( !isset($_SESSION[ $this->_xal_configs['_se_space'] ]) ) return false;
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if(!is_string($fn_tree))	return false;
		$session= $_SESSION[ $this->_xal_configs['_se_space'] ];
		$sepath	= explode('.', $fn_tree);
		foreach($sepath as $sekey)
		{
			if( !isset($session[$sekey]) ) return false;
			$session	= $session[$sekey];
		}
		return $session;
	}
	
	protected function sys_session_clear()
	{	
		$_SESSION[ $this->_xal_configs['_se_space'] ]	= array();
	}
	
	protected function sys_session_set($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['fn.name']) or ! isset($fn_tree['fn.value']) )
			return $this->__errorHandler('session.set tag has not correct pattern');
		
		if( is_array($fn_tree['fn.name']) )	$fn_tree['fn.name']	= $this->_axmlExecution($fn_tree['fn.name'], $pre_vars);
		if( is_array($fn_tree['fn.value']) )$fn_tree['fn.value']= $this->_axmlExecution($fn_tree['fn.value'], $pre_vars);
		
		$sepath	= explode('.', $fn_tree['fn.name']);
		if(count($sepath)==0) return false;
		if( !isset($_SESSION[ $this->_xal_configs['_se_space'] ]) ) $_SESSION[ $this->_xal_configs['_se_space'] ]	= array();

		//$seitem = $this->helper_createTreeArray($sepath, array(), $fn_tree['fn.value']);
		//$_SESSION[ $this->_xal_configs['_se_space'] ]	= array_merge($_SESSION[ $this->_xal_configs['_se_space'] ], $seitem);
		
		$_SESSION[ $this->_xal_configs['_se_space'] ] = $this->helper_createTreeArray2($sepath, $_SESSION[ $this->_xal_configs['_se_space'] ] , $fn_tree['fn.value']);
		
		return $session;
	}





	protected function sys_eval($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		if( is_string($fn_tree))	$this->run($fn_tree, $pre_vars);
	}
	protected function sys_tree($fn_tree, $pre_vars)
	{
		if(!is_array($fn_tree)) return array();
		foreach($fn_tree as $item_tree)
		{
			$item_name	= array_pop( array_keys($item_tree) );
			if( preg_match('/^item\:/', $item_name) )	$result[ str_replace('item:','', $item_name) ]	= $this->_item($item_tree[$item_name], $pre_vars);
			elseif($item_name=='item')					$result[]	= $this->_axmlExecution($item_tree, $pre_vars);
		}
		return $result;
	}
	protected function sys_tree_push($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['fn.name']) or ! isset($fn_tree['fn.value']) )
			return $this->__errorHandler('session.set tag has not correct pattern');	
		
		$fn_tree['fn.name']	= $this->_axmlExecution($fn_tree['fn.name'], $pre_vars);
		$fn_tree['fn.value']	= $this->_axmlExecution($fn_tree['fn.value'], $pre_vars);
		
		$path	= explode('.', $fn_tree['fn.name']);
		$var_name	= array_shift($path);
		
		if(!is_array($pre_vars['var:'.$var_name])) return false;
		//$pre_vars['var:'.$var_name]	= array_merge( $pre_vars['var:'.$var_name], $this->helper_createTreeArray($path, array(), $fn_tree['fn.value']) );		
		$pre_vars['var:'.$var_name]	= $this->helper_createTreeArray2($path, $pre_vars['var:'.$var_name], $fn_tree['fn.value']);		
		return $pre_vars['var:'.$var_name];
	}
	protected function sys_item($fn_tree, $pre_vars)
	{
		$fn_tree	= $this->_axmlExecution($fn_tree, $pre_vars);
		return $fn_tree;
	}


	protected function sys_foreach($fn_tree, $pre_vars)
	{
		if(!is_array($fn_tree['execution']))return;
		if(!is_array($fn_tree['input']))	return $this->__errorHandler('foreach tag has not correct pattern');
		if(!is_string($fn_tree['foreach.value']) or empty($fn_tree['foreach.value']) )return $this->__errorHandler('foreach tag has not correct pattern');
		$input	= $this->_axmlExecution($fn_tree['input'], $pre_vars);
		if( !is_array($input) or count($input)==0 ) return;
		if( is_string($fn_tree['foreach.key']) and !empty($fn_tree['foreach.key']) )
			foreach($input as $key=>$value)
			{
				$pre_vars['var:'.$fn_tree['foreach.key'] ]	= $key;
				$pre_vars['var:'.$fn_tree['foreach.value']]= $value;
				$pre_vars	= $this->sys_execution($fn_tree['execution'], $pre_vars);
			}
		else
			foreach($input as $value)
			{
				$pre_vars['var:'.$fn_tree['foreach.value']]= $value;
				$pre_vars	= $this->sys_execution($fn_tree['execution'], $pre_vars);
			}
		return $pre_vars;
	}
	protected function sys_for($fn_tree, $pre_vars)
	{
		if( !isset($fn_tree['start']) or !isset($fn_tree['end']) or !isset($fn_tree['step']) or !isset($fn_tree['execution']) ) return '';
		$fn_tree['start']	= $this->_axmlExecution($fn_tree['start'], $pre_vars);
		$fn_tree['end']		= $this->_axmlExecution($fn_tree['end'], $pre_vars);
		$fn_tree['step']	= $this->_axmlExecution($fn_tree['step'], $pre_vars);
		if( !is_numeric($fn_tree['start']) or !is_numeric($fn_tree['end']) or !is_numeric($fn_tree['step']) ) return '';
		$cu_name	= ( is_string($fn_tree['counter']) and !empty($fn_tree['counter']) )?'var:'.$fn_tree['counter']:'var:counter';
		for($pre_vars[$cu_name]=$fn_tree['start']; $pre_vars[$cu_name]<= $fn_tree['end']; $pre_vars[$cu_name]+= $fn_tree['step'])
			$pre_vars	= $this->sys_execution($fn_tree['execution'], $pre_vars);
		return $pre_vars;
	}
	protected function sys_if($fn_tree, $pre_vars)
	{
		if( (!isset($fn_tree['is']) and !isset($fn_tree['isnot']) ) or !isset($fn_tree['execution']) ) return '';
		if(isset($fn_tree['is']))
		{
			$condition	= false;
			if($fn_tree['is'] = $this->_axmlExecution($fn_tree['is'], $pre_vars))	$condition	= true;
		}
		else
		{
			$condition	= true;
			if($fn_tree['isnot'] = $this->_axmlExecution($fn_tree['isnot'], $pre_vars))	$condition	= false;
		}
		if($condition)	$pre_vars	= $this->sys_execution($fn_tree['execution'], $pre_vars);
		elseif(!$condition and isset($fn_tree['else']['execution']) )	$pre_vars	= $this->sys_execution($fn_tree['else']['execution'], $pre_vars);
		return $pre_vars;
	}
	protected function sys_switch($fn_tree, $pre_vars)
	{
		if( !isset($fn_tree['test']) or !is_array($fn_tree['execution']) ) return '';
		$fn_tree['test'] = $this->_axmlExecution($fn_tree['test'], $pre_vars);
		$default = false;
		foreach($fn_tree['execution'] as $case)
		{
			if(!is_array($case)) continue;
			if(is_array($case['case']))
			{
				if( !isset($case['case']['match']) ) continue;
				$case['case']['match'] = $this->_axmlExecution($case['case']['match'], $pre_vars);
				if($case['case']['match']==$fn_tree['test'])
				{
					if(is_array($case['case']['execution'])) $pre_vars	= $this->sys_execution($case['case']['execution'], $pre_vars);
					return $pre_vars;
				}
			}
			elseif( is_array($case['default']) )
			{
				$default = $case['default'];
			}
		}
		if(is_array($default) and is_array($default['execution']))  $pre_vars	= $this->sys_execution($default['execution'], $pre_vars);
		return $pre_vars;
	}


//	protected function sys_compare($comparison, $fn_tree, $pre_vars)
//	{
//		if(! isset($fn_tree['left']) or ! isset($fn_tree['right']) )
//			return $this->__errorHandler($comparison.' tag has not correct pattern');
//		
//		if( is_array($fn_tree['left']) )	$fn_tree['left']	= $this->_axmlExecution($fn_tree['left'], $pre_vars);
//		if( is_array($fn_tree['right']) )	$fn_tree['left']	= $this->_axmlExecution($fn_tree['right'], $pre_vars);
//		
////		if(! is_numeric($fn_tree['left']) or ! is_numeric($fn_tree['right']) )
////			return $this->__errorHandler($operator.' tag has invalid inputs');
//		
//		switch($comparison)
//		{
//			case 'add':			return ($fn_tree['left'] + $fn_tree['right']); break;
//			case 'subtract':	return ($fn_tree['left'] - $fn_tree['right']); break;
//			case 'multiply':	return ($fn_tree['left'] * $fn_tree['right']); break;
//			case 'divide':		return ($fn_tree['left'] / $fn_tree['right']); break;
//		}
//		
//	}


/// HTTP functions
	protected function sys_redirect($fn_tree, $pre_vars)
	{
		if( !is_array($fn_tree) )	Zend_OpenId::redirect($fn_tree);
		if( !isset($fn_tree['url']) )	return ;
		$url	= $this->_axmlExecution($fn_tree['url'], $pre_vars);
		$params	= ( isset($fn_tree['params']) )?$this->_axmlExecution($fn_tree['params'], $pre_vars):NULL;
		$method	= ( isset($fn_tree['method']) )?$this->_axmlExecution($fn_tree['method'], $pre_vars):'GET';
		$method = ( is_string($method) and $method=='post' )?'POST':'GET';
		if( is_array($params) )	foreach($params as $key=>$value) if(!is_string($key) or is_array($value)) unset($params[$key]);
		Zend_OpenId::redirect($url, $params, null, $method);
	}
	
	protected function sys_send_json($fn_tree, $pre_vars)
	{
		if(!is_array($fn_tree)) return;
		$fn_tree = $this->_axmlExecution($fn_tree, $pre_vars);
		//if(!is_array($fn_tree)) return;
		header('Content-type: application/json');
		echo json_encode($fn_tree);
		die();
	}







	public function helper_get_fn_argus_value($fn_tree, $pre_vars, $argus)
	{
		$new_fn_tree	= array();
		foreach($argus as $argu)
		{
			if( !isset($fn_tree[$argu]) ) continue;
			if( is_array($fn_tree[$argu]) )	$new_fn_tree[$argu]	= $this->_axmlExecution($fn_tree[$argu], $pre_vars);
			else	$new_fn_tree[$argu]	= $fn_tree[$argu];
		}
		return $new_fn_tree;
	}
	public function helper_get_fn_argus($fn_tree, $pre_vars)
	{
		if( !is_array($fn_tree) )	return $fn_tree;
		$new_fn_tree	= array();
		foreach($fn_tree as $ar_name=>$argu)
		{
			if( is_array($argu) )	$new_fn_tree[$ar_name]	= $this->_axmlExecution($argu, $pre_vars);
			else	$new_fn_tree[$ar_name]	= $argu;
		}
		return $new_fn_tree;
	}
	public function helper_createTreeArray($keys, $parent, $value)
	{
		$c	= count($keys);
		$k	= array_shift($keys);
		if( is_numeric($k) )	$k	= (integer) $k;
		if(!empty($k))
		{
			if($c>1)		$parent[$k]	= $this->helper_createTreeArray($keys, $parent, $value);
			elseif($c==1)	$parent[$k]	= $value;
		}
		else
			$parent[]	= $value;
		return $parent;
	}
	public function helper_createTreeArray2($keys, $parent, $value)
	{
		$c	= count($keys);
		$k	= array_shift($keys);
		if( is_numeric($k) )	$k	= (integer) $k;
		if(!empty($k))
		{
			if(!isset($parent[$k])) $parent[$k]=array();
			if($c>1)		$parent[$k]	= $this->helper_createTreeArray2($keys, $parent[$k], $value);
			elseif($c==1)	$parent[$k]	= $value;
		}
		else
			$parent[]	= $value;
		return $parent;
	}
	public function helper_unsetTreeItem($keys, $source)
	{
		$c	= count($keys);
		$k	= array_shift($keys);
		if( is_numeric($k) )	$k	= (integer) $k;
		if(!empty($k))
		{
			if(isset($source[$k]) and $c>2)			$source[$k]	= $this->helper_unsetTreeItem($keys, $source[$k]) ;
			elseif(is_array($source[$k]) and $c==2)	unset($source[$k][ $keys[0] ]); 
		}
		return $source;
	}
	public function __errorHandler($error)
	{
		return false;
	}



	

	protected function _isIdentifiedSystemFunction($function)
	{
		$identifiedFunctions	= array(
		'var', 'constant', /*'property',*/ 'value', 'call', 'join', 'replace', 'add', 'subtract', 'multiply', 'divide', 'ceil', 'modulo', 'floor', 'switch', 'for',
		'foreach', 'return', 'unset', 'redirect', 'send.json', 'if',
		'is.numeric', 'is.greater', 'is.less', 'is.semigreater', 'is.semiless',
		'is.equal', 'response', 'execution', /*'construct',*/ 'print', 'die', 'param', 'param.post', 'param.get', 'param.uri', 'param.env',
		'session.get', 'session.set', 'session.clear', 'eval',
		'tree', 'tree.push', 'tree.print', 'item',
		/*'db',*/ 'db.connect', 'db.fetch', 'db.update', 'db.insert', 'db.last_record_id', 'db.query'
												
		);
		
		if( in_array($function, $this->_lockedfns) ) return false;

		if( in_array($function, $identifiedFunctions) or array_key_exists($function, $this->_assigned_fns) ) return true;
		return false;

	}
	protected function __runSysFunction($fn_name, $fn_tree, $pre_vars)
	{
		switch($fn_name)
		{
			case 'var': return $this->sys_var($fn_tree, $pre_vars); break;
			case 'constant': return $this->sys_constant($fn_tree, $pre_vars); break;
			case 'value': return $this->sys_value($fn_tree, $pre_vars); break;
			case 'call': return $this->sys_call($fn_tree, $pre_vars); break;
			case 'join': return $this->sys_join($fn_tree, $pre_vars); break;
			case 'replace': return $this->sys_replace($fn_tree, $pre_vars); break;
			
			case 'modulo':
			case 'add': 
			case 'subtract': 
			case 'multiply': 
			case 'divide': return $this->sys_operator($fn_name, $fn_tree, $pre_vars); break;
			case 'ceil': return $this->sys_ceil($fn_tree, $pre_vars); break;
			case 'floor': return $this->sys_floor($fn_tree, $pre_vars); break;
			case 'switch': return $this->sys_switch($fn_tree, $pre_vars); break;
			case 'for': return $this->sys_for($fn_tree, $pre_vars); break;
			case 'foreach': return $this->sys_foreach($fn_tree, $pre_vars); break;
			case 'return': return $this->sys_return($fn_tree, $pre_vars); break;
			case 'unset': return $this->sys_unset($fn_tree, $pre_vars); break;
			case 'redirect': return $this->sys_redirect($fn_tree, $pre_vars); break;
			case 'send.json': return $this->sys_send_json($fn_tree, $pre_vars); break;
			
			case 'if': return $this->sys_if($fn_tree, $pre_vars); break;
			
			case 'is.equal': if(!is_object($this->__is)) $this->_cons_is_object(); return $this->__is->equal($fn_tree, $pre_vars); break;
			case 'is.numeric': if(!is_object($this->__is)) $this->_cons_is_object(); return $this->__is->numeric($fn_tree, $pre_vars); break;
			case 'is.greater': if(!is_object($this->__is)) $this->_cons_is_object(); return $this->__is->greater($fn_tree, $pre_vars); break;
			case 'is.less': if(!is_object($this->__is)) $this->_cons_is_object(); return $this->__is->less($fn_tree, $pre_vars); break;
			case 'is.semigreater': if(!is_object($this->__is)) $this->_cons_is_object(); return $this->__is->semiGreater($fn_tree, $pre_vars); break;
			case 'is.semiless': if(!is_object($this->__is)) $this->_cons_is_object(); return $this->__is->semiLess($fn_tree, $pre_vars); break;			

 
			
			
			case 'response': return $this->sys_response($fn_tree, $pre_vars); break;
			case 'execution': return $this->sys_execution($fn_tree, $pre_vars); break;
			case 'print': return $this->sys_print($fn_tree, $pre_vars); break;
			case 'die': return $this->sys_die($fn_tree, $pre_vars); break;
			case 'param': return $this->sys_param($fn_tree, $pre_vars); break;
			case 'param.post': return $this->sys_param_post($fn_tree, $pre_vars); break;
			case 'param.get': return $this->sys_param_get($fn_tree, $pre_vars); break;
			case 'param.uri': return $this->sys_param_uri($fn_tree, $pre_vars); break;
			case 'param.env': return $this->sys_param_env($fn_tree, $pre_vars); break;
			case 'session.get': return $this->sys_session_get($fn_tree, $pre_vars); break;
			case 'session.set': return $this->sys_session_set($fn_tree, $pre_vars); break;
			case 'session.clear': return $this->sys_session_clear(); break;
			case 'eval': return $this->sys_eval($fn_tree, $pre_vars); break;
			case 'tree': return $this->sys_tree($fn_tree, $pre_vars); break;
			case 'tree.push': return $this->sys_tree_push($fn_tree, $pre_vars); break;
			case 'tree.print': return $this->sys_tree_print($fn_tree, $pre_vars); break;
			case 'item': return $this->sys_item($fn_tree, $pre_vars); break;
			
			
			//case 'db': return $this->sys_db($fn_tree, $pre_vars); break;
			case 'db.connect': 			if(!is_object($this->__db)) $this->_cons_db_object(); 	return $this->__db->connect($fn_tree, $pre_vars); break;
			case 'db.fetch': 			if(!is_object($this->__db)) $this->_cons_db_object(); 	return $this->__db->fetch($fn_tree, $pre_vars); break;
			case 'db.update': 			if(!is_object($this->__db)) $this->_cons_db_object(); 	return $this->__db->update($fn_tree, $pre_vars); break;
			case 'db.insert': 			if(!is_object($this->__db)) $this->_cons_db_object(); 	return $this->__db->insert($fn_tree, $pre_vars); break;
			case 'db.last_record_id':	if(!is_object($this->__db)) $this->_cons_db_object();	return $this->__db->last_record_id($fn_tree, $pre_vars); break;
			case 'db.query': 			if(!is_object($this->__db)) $this->_cons_db_object(); 	return $this->__db->query($fn_tree, $pre_vars); break;

		}
		return $this->custom_function($fn_name, $fn_tree, $pre_vars);
		return '';
	}
	
	protected function custom_function($fn_name, $fn_tree, $pre_vars)
	{
		
		if(!$this->_assigned_fns[$fn_name])	return '';
		$fn_argus	= $this->helper_get_fn_argus($fn_tree, $pre_vars);
		if( is_object($this->_assigned_fns[$fn_name]))
		{
			if(method_exists($this->_assigned_fns[$fn_name],'run'))
				return $this->_assigned_fns[$fn_name]->run($fn_argus);
		}
		elseif( is_string($this->_assigned_fns[$fn_name]) )
		{
			return eval($this->_assigned_fns[$fn_name]);
		}
		else		return $this->_assigned_fns[$fn_name]($fn_argus);
		return '';
	}
	
	


	protected function __runAxmlFunction($fn_tree, $fn_argus)
	{
	
//		if(! isset($fn_tree['execution']) or ! is_array($fn_tree['execution']) ) return $this->__errorHandler('function has not correct pattern');
//
//		$fn_argus	= array();
//		if( isset($fn_tree['arguments']) and is_array($fn_tree['arguments']) )
//		{
//			foreach($fn_tree['arguments'] as $arg_name=>$arg_value)
//				if( !is_array($arg_value) )	$fn_argus[$arg_name]	= $arg_value;
//				else	continue;
//			unset($fn_tree['arguments']);	
//		}
//		return $this->_axmlExecution($fn_tree, $fn_argus);
	}
	protected function sys_construct($con_tree, $pre_vars)
	{
		if(is_string($con_tree) and preg_match('/^class\.[\w\d\_]+$/', $con_tree) )
		{
			 die($con_tree);
		}
		if(! isset($this->_atree['construct']) or !is_array($this->_atree['construct']) ) return false;
		$this->sys_execution($this->_atree['construct'], array());

	}
	protected function sys_property($fn_tree, $pre_vars)
	{
		if( !is_array($fn_tree) and isset($this->_aproperties[ 'property:'.$fn_tree ]) ) return $this->_aproperties[ 'property:'.$fn_tree ];
		return $this->__errorHandler('property is not defined');
	}
	protected function _property($pro_name, $pro_tree, $pre_vars)
	{
		if( is_array($pro_tree) and 'item'!=array_pop( array_keys($pro_tree) ) )	$pro_tree	= $this->_axmlExecution($pro_tree, $pre_vars);

		//if( is_array($pro_tree) and 'item'!=@array_pop( array_keys($com_tree) ) )	return false;
		
		$this->_aproperties[ $pro_name ]	= $pro_tree;
		return true;
	}

}

?>