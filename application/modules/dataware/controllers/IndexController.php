<?php
 
class Dataware_IndexController extends Zend_Controller_Action 
{

   public function init() 
    {
    }

    public function indexAction()
    {
		
		$f	= explode('.', '');
		print_r($f);
		die();
		
		$this->_prename	= realpath(APPLICATION_PATH .'/../data/db/'.WBSiD);
		$params['dbname']	= $this->_prename. "/ddf.sqlite";
		$this->_dbh = Zend_Db::factory('PDO_SQLITE', $params);
		
		$sql	= 'SELECT * FROM ds';
		$result	= $this->_dbh->fetchAll($sql);
		print_r($result);
		
		
		die();
		//$int	= new Db_Model_Api($xml);
		//echo $int->output;
		
//		$db	= new Db_Model_Table('test');
//		$data['col1']	= '2سلام';
//		$data['col2']	= '2خوبی';
//		$db->insert($data);
//		//$db->update($data, 'id =1');
//		//$db->delete('id =2');
//		print_r($db->select('*'));
    }
    public function sysparamsAction()
    {
		die();
//		$path	= realpath(APPLICATION_PATH . "/../data/db/test.db");
//		$dbh = new PDO('sqlite:'.$path);
		//$dbh->exec("CREATE TABLE table1 (id INTEGER PRIMARY KEY, col1 TEXT UNIQUE, col2 TEXT)");
		
		/*$stmt = $dbh->prepare("INSERT INTO table1 (col1, col2) VALUES (:col1, :col2)");
		$stmt->bindParam(':col1', $col1_val);
		$stmt->bindParam(':col2', $col2_val); 
		// insert our data $col1_val = "sqlite"; $col2_val = "rocks"; $stmt->execute(); 
		$col1_val = "this needs to be unique";
		$col2_val = "this doesn't!";
		$stmt->execute();*/
		
		//foreach ($dbh->query('SELECT * FROM table1', PDO::FETCH_ASSOC) as $row) { print_r($row); }
		
		//$params['dbname']	= realpath(APPLICATION_PATH . "/../data/db/test.db");
	//	$db = Zend_Db::factory('PDO_SQLITE', $params);
		//$rs	= $db->fetchAll('SELECT * FROM table1');
	//	print_r($rs);
    }


}
