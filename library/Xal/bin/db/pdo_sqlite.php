<?php
/*
	*	
*/
class Db_pdo_sqlite
{
	protected	$_db_root	= '';
	protected	$_db		= false;

	protected	$_prename;
	public		$_name;
	public		$_dbh;

	public function	__construct($db=NULL)
	{
		if(!empty($db))	$this->set_db_handle($db);
	}

	public function set_db_handle($handle)
	{
		$this->_db = $handle;
	}

//	public function set_db_root($path)
//	{
//		$this->_db_root	= $path;
//	}
//	public function get_db_root()
//	{
//		return $this->_db_root;
//	}
//	
//	public function connect($db)
//	{
//		$db	= $this->_db_root . $db;
//		try
//		{
//			if( is_file($db) )
//				$this->_db = Zend_Db::factory('PDO_SQLITE', array('dbname'=>$db) );
//		}
//		catch(Zend_exception $e)
//		{
//			return false;
//		}
	}
	public function insert($table, $data)
	{
		if(!$this->_db) return false;
		try
		{
			return $this->_db->insert($table, $data);
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	public function update($table, $data, $where)
	{
		if(!$this->_db) return false;
		try
		{
			return $this->_db->update($table, $data, $where);
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	public function delete($table, $where)
	{
		if(!$this->_db) return false;
		try
		{
			return $this->_db->delete($table, $where);
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	public function select($fields, $table, $state=NULL)
	{
		if(!$this->_db) return false;
		$exp	= '';
		if(!empty($state['where']))	$exp	.= ' WHERE '.	$state['where'];
		if(!empty($state['group']))	$exp	.= ' GROUP BY '.$state['group'];
		if(!empty($state['order']))	$exp	.= ' ORDER BY '.$state['order'];
		if(!empty($state['limit']))	$exp	.= ' LIMIT '.	$state['limit'];
		$sql	= 'SELECT '. addslashes($fields) .' FROM '. addslashes($table) .' '. addslashes($exp);
		try
		{
			return $this->_db->fetchAll($sql);
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	public function count($table, $state=NULL, $field='*')
	{
		if(!$this->_db) return false;
		if(!empty($state))	$state	= 'WHERE '.$state;
		$sql	= 'SELECT COUNT('. addslashes($field) .') FROM '. addslashes($table) .' '. addslashes($state);
		try
		{
			return $this->_db->fetchOne($sql);
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	public function getServerVersion()
	{
		if(!$this->_db) return false;
		return $this->_db->getServerVersion();
	}
	
	public function fullDrop($table)
	{
//		if(empty($this->_prename) or empty($table))	return false;
//		if(is_object($this->_db))	$this->_db->closeConnection();
//		return unlink($this->_prename. "/".$this->_name.".db");
	}
	public function semiDrop($table)
	{
//		if(empty($this->_prename) or empty($table))	return false;
//		if(is_object($this->_db))	$this->_db->closeConnection();
//		$dfp	= $this->_prename. "/".$this->_name.".db";
//		return rename($dfp, $this->_prename. "/bc/".$this->_name.".db".'.bc');
	}
	public function semiEmpty()
	{
//		if(empty($this->_prename) or empty($this->_name))	return false;
//		$dfp	= $this->_prename. "/".$this->_name.".db";
//		$ret	= copy($dfp, $this->_prename. "/bc/".$this->_name.".db".'.bc');
//		if(!ret) return false;
//		$this->_db($this->_name);
//		$this->delete('');
//		return true;
	}
	

}

?>