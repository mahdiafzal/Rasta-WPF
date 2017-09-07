<?php
/*
	*	
*/
class Db_Model_Table
{
	protected	$_prename;
	public		$_name;
	public		$_dbh;

	public function	__construct($db=NULL)
	{
		$this->_name	= $db;
		//$wbsmd	= md5(WBSiD);
		//$this->_prename	= realpath(APPLICATION_PATH .'/../public/flsimgs/'.WBSiD.'/db/_'.$wbsmd[5].$wbsmd[3].$wbsmd[8].$wbsmd[2]);
		$this->_prename	= realpath(APPLICATION_PATH .'/../data/db/'.WBSiD);
		
		if(!empty($db))	$this->_dbh($db);
		//$sql='CREATE TABLE '.$tb.' (id INTEGER PRIMARY KEY, col1 TEXT, col2 TEXT)';
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