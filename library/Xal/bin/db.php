<?php
/*
	*	
*/

class db
{
	protected $context;
	protected $handles	= array();
	
	public function	__construct($context)
	{
		$this->context = $context;
		
	}
	
	public function connect($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['db.type']) or ! isset($fn_tree['db.name']) ) return false;
		
		if( is_array($fn_tree['db.type']) ) $fn_tree['db.type']	= $this->context->_axmlExecution($fn_tree['db.type'], $pre_vars);
		if( is_array($fn_tree['db.name']) ) $fn_tree['db.name']	= $this->context->_axmlExecution($fn_tree['db.name'], $pre_vars);

		$db_handle_id = md5( count($this->handles)+1 );
		if($fn_tree['db.type']=='mysql')
		{
			$_handle	= Zend_Registry::get('extra_db_'.$fn_tree['db.name']);
			if(!is_object($_handle))	return false;
			$this->handles[$db_handle_id]=$_handle;
			return $db_handle_id;
		}
		elseif($fn_tree['db.type']=='sqlite')
		{
			$params['dbname']	= $this->context->_xal_configs['_sqlite_root']. "/".$fn_tree['db.name'].".db";
			if(!is_file($params['dbname'])) return false;
			$_handle	= Zend_Db::factory('PDO_SQLITE', $params);
			$this->handles[$db_handle_id]=$_handle;
			return $db_handle_id;
		}
		return false;		
	}
	public function fetch($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['table']) or  ! isset($fn_tree['db.handle']) )	return $this->__errorHandler('db.fetch tag has not correct pattern');
		
		$argus		= array('db.handle', 'table', 'fields', 'where', 'group', 'order', 'limit');
		$fn_tree	= $this->context->helper_get_fn_argus_value($fn_tree, $pre_vars, $argus);
		
		if(!isset($this->handles[$fn_tree['db.handle']])) return false;
		
		if( empty($fn_tree['fields']) )	$fn_tree['fields']	= '*';
		
		$sql = 'SELECT '.$fn_tree['fields'].' FROM '.$fn_tree['table']
			.  ( (empty($fn_tree['where']))?'':' WHERE '.$fn_tree['where'] )
			.  ( (empty($fn_tree['group']))?'':' GROUP BY '.$fn_tree['group'] )
			.  ( (empty($fn_tree['order']))?'':' ORDER BY '.$fn_tree['order'] )
			.  ( (empty($fn_tree['limit']))?'':' LIMIT '.$fn_tree['limit'] );
		
		$result	= $this->handles[$fn_tree['db.handle']]->fetchAll($sql);
		if($result) return $result;
		return '';
	}
	public function insert($fn_tree, $pre_vars)
	{
		
		if(! isset($fn_tree['table']) or  ! isset($fn_tree['db.handle']) or  ! isset($fn_tree['data']) )	return $this->__errorHandler('db.fetch tag has not correct pattern');	
		$argus		= array('db.handle', 'table', 'data');
		$fn_tree	= $this->context->helper_get_fn_argus_value($fn_tree, $pre_vars, $argus);

		if(!isset($this->handles[$fn_tree['db.handle']])) return false;
		
		return $this->handles[$fn_tree['db.handle']]->insert($fn_tree['table'], $fn_tree['data']);
	}
	public function update($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['table']) or  ! isset($fn_tree['db.handle']) or  ! isset($fn_tree['data']) )	return $this->__errorHandler('db.fetch tag has not correct pattern');
		$argus		= array('db.handle', 'table', 'data', 'where');
		$fn_tree	= $this->context->helper_get_fn_argus_value($fn_tree, $pre_vars, $argus);
		
		if(!isset($this->handles[$fn_tree['db.handle']])) return false;
		if(empty($fn_tree['where']))	$fn_tree['where'] = '';
		
		return $this->handles[$fn_tree['db.handle']]->update($fn_tree['table'], $fn_tree['data'], $fn_tree['where']);
	}

	public function last_record_id($fn_tree, $pre_vars)
	{
		if( ! isset($fn_tree['db.handle']) )	return $this->__errorHandler('db.fetch tag has not correct pattern');
		$argus		= array('db.handle');
		$fn_tree	= $this->context->helper_get_fn_argus_value($fn_tree, $pre_vars, $argus);
		
		if(!isset($this->handles[$fn_tree['db.handle']])) return false;
		return $this->handles[$fn_tree['db.handle']]->lastInsertId();
	}
	public function query($fn_tree, $pre_vars)
	{
		if(! isset($fn_tree['sql']) or  ! isset($fn_tree['db.handle']) )	return $this->__errorHandler('db.fetch tag has not correct pattern');

		$argus		= array('db.handle', 'sql');
		$fn_tree	= $this->context->helper_get_fn_argus_value($fn_tree, $pre_vars, $argus);
		
		if(!isset($this->handles[$fn_tree['db.handle']])) return false;
		
		return $this->handles[$fn_tree['db.handle']]->query($fn_tree['sql']);
	}

}

?>