<?php
/*
	*	
*/

class is
{
	protected $context;
	
	public function	__construct($context)
	{
		$this->context = $context;
	}
	
	public function numeric($fn_tree, $pre_vars)
	{
		if( !is_array($fn_tree) ) return is_numeric($fn_tree);
		$fn_tree	= $this->context->_axmlExecution($fn_tree, $pre_vars);
		return is_numeric($fn_tree);		
	}
	public function equal($fn_tree, $pre_vars)
	{
		//if(! isset($fn_tree['left']) or ! isset($fn_tree['right']) ) return 'ERROR';		
		//if( is_array($fn_tree['left']) ) $fn_tree['left']	= $this->context->_axmlExecution($fn_tree['left'], $pre_vars);
		//if( is_array($fn_tree['right']) ) $fn_tree['right']	= $this->context->_axmlExecution($fn_tree['right'], $pre_vars);
		//return ( ($fn_tree['left']==$fn_tree['right'])?true:false );
		return $this->compare('equal', $fn_tree, $pre_vars);
	}
	public function greater($fn_tree, $pre_vars)
	{
		return $this->compare('greater', $fn_tree, $pre_vars);
	}
	public function less($fn_tree, $pre_vars)
	{
		return $this->compare('less', $fn_tree, $pre_vars);
	}
	public function semiGreater($fn_tree, $pre_vars)
	{
		return $this->compare('semiGreater', $fn_tree, $pre_vars);
	}
	public function semiLess($fn_tree, $pre_vars)
	{
		return $this->compare('semiLess', $fn_tree, $pre_vars);
	}
	private function compare($mode, $fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['left']) or ! isset($fn_tree['right']) ) return 'ERROR';		
		if( is_array($fn_tree['left']) ) $fn_tree['left']	= $this->context->_axmlExecution($fn_tree['left'], $pre_vars);
		if( is_array($fn_tree['right']) ) $fn_tree['right']	= $this->context->_axmlExecution($fn_tree['right'], $pre_vars);
		
		switch($mode)
		{
			case 'equal':		return ($fn_tree['left']==$fn_tree['right']);	break;
			case 'greater':		return ($fn_tree['left']>$fn_tree['right']);	break;
			case 'less':		return ($fn_tree['left']<$fn_tree['right']);	break;
			case 'semiGreater':	return ($fn_tree['left']>=$fn_tree['right']);	break;
			case 'semiLess':	return ($fn_tree['left']<=$fn_tree['right']);	break;
		}
	}
	
	
	
	
}

?>