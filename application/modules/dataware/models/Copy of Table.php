<?php
/*
	*	
*/
class Db_Model_Table
{
	protected	$_prename;
	public		$_name;
	public		$_dbh;

	public function	__construct($tb=NULL)
	{
		$this->_name	= $tb;
		//$wbsmd	= md5(WBSiD);
		//$this->_prename	= realpath(APPLICATION_PATH .'/../public/flsimgs/'.WBSiD.'/db/_'.$wbsmd[5].$wbsmd[3].$wbsmd[8].$wbsmd[2]);
		$this->_prename	= realpath(APPLICATION_PATH .'/../data/db/'.WBSiD);
		
		if(!empty($tb))	$this->_dbh($tb);
		//$sql='CREATE TABLE '.$tb.' (id INTEGER PRIMARY KEY, col1 TEXT, col2 TEXT)';
	}
	public function _dbh($tb, $new=false)
	{
		if(is_object($this->_dbh)) return $this->_dbh;
		
		$params['dbname']	= $this->_prename. "/".$tb.".db";
		if($new==false and !is_file($params['dbname']) ) return false;
		$this->_dbh = Zend_Db::factory('PDO_SQLITE', $params);
		return $this->_dbh;
	}
	public function insert($data)
	{
		return $this->_dbh->insert($this->_name, $data);
	}
	public function update($data, $where)
	{
		return $this->_dbh->update($this->_name, $data, $where);
	}
	public function delete($where)
	{
		return $this->_dbh->delete($this->_name, $where);
	}
	public function select($cols='*', $state=NULL)
	{
		$sql	= 'SELECT '.$cols.' FROM '.$this->_name.' '.$state;
		return $this->_dbh->fetchAll($sql);
	}
	public function count($state=NULL)
	{
		if(!empty($state))	$state	= 'WHERE '.$state;
		$sql	= 'SELECT COUNT(*) FROM '.$this->_name.' '.$state;
		return $this->_dbh->fetchOne($sql);
	}
	public function fullDrop()
	{
		if(empty($this->_prename) or empty($this->_name))	return false;
		if(is_object($this->_dbh))	$this->_dbh->closeConnection();
		return unlink($this->_prename. "/".$this->_name.".db");
	}
	public function semiDrop()
	{
		if(empty($this->_prename) or empty($this->_name))	return false;
		if(is_object($this->_dbh))	$this->_dbh->closeConnection();
		$dfp	= $this->_prename. "/".$this->_name.".db";
		return rename($dfp, $this->_prename. "/bc/".$this->_name.".db".'.bc');
	}
	public function semiEmpty()
	{
		if(empty($this->_prename) or empty($this->_name))	return false;
		$dfp	= $this->_prename. "/".$this->_name.".db";
		$ret	= copy($dfp, $this->_prename. "/bc/".$this->_name.".db".'.bc');
		if(!ret) return false;
		$this->_dbh($this->_name);
		$this->delete('');
		return true;
	}
	

}

?>