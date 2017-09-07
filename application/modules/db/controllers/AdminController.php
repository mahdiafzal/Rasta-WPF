<?php

class Db_AdminController extends Zend_Controller_Action 
{

	public function init() 
    {
		$this->_helper->_layout->setLayout('dashboard');
    }
    public function indexAction()
    {
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('title_site', $translate->_('a')); 

		include('db_manager_all.php');
		$auth = new Db_Model_Authorization(); //create authorization object
		if(!$auth->isAuthorized()) //user is not authorized - display the login screen
			$this->_redirect('/db/admin/login');

		echo '<link href="/modules/db/skin1/style.css" rel="stylesheet" type="text/css" />';
		echo '<script type="text/javascript" src="/modules/db/adminjs.js"></script>';
		
		include('db_manager_admin.php');

	}

    public function loginAction()
    {

		include('db_manager_all.php');
		$auth = new Db_Model_Authorization(); //create authorization object

		if($auth->isAuthorized()) //user is authorized - display the main screen
			$this->_redirect('/db/admin');

		if(isset($_POST['logout'])) //user has attempted to log out
			$auth->revoke();
		else if(isset($_POST['login']) || isset($_POST['proc_login'])) //user has attempted to log in
		{
			$_POST['login'] = true;
		
			if($_POST['password']==SYSTEMPASSWORD) //make sure passwords match before granting authorization
			{
				if(isset($_POST['remember']))
					$auth->grant(true);
				else
					$auth->grant(false);
				$this->_redirect('/db/admin');
			}
		}


	}

    public function helpAction()
    {
	}

    public function tbemptyAction()
    {
		$params	= $this->getRequest()->getParams();
		if(!$params['tb'] ) $this->_redirect($_SERVER['HTTP_REFERER']);
		$name	= $params['tb'];

		try
		{
			$dbtb	= new Db_Model_Table($name);
			$ret	= $dbtb->count();
			if($ret>0)
			{
				$ret	= $dbtb->semiEmpty();
				if(!ret)	die('Error');
			}
		}
		catch(Zend_exception $e)
		{
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
		$this->_redirect('/db/admin/frmlist#fragment-1');
	}
    public function tbdropAction()
    {
		$params	= $this->getRequest()->getParams();
		if(!$params['tb'] ) $this->_redirect($_SERVER['HTTP_REFERER']);
		$name	= $params['tb'];

		try
		{
			$dbtb	= new Db_Model_Table($name);
			$ret	= $dbtb->count();
			if($ret>0)
			{
				$ret	= $dbtb->semiDrop();
				if(!ret)	die('Error');
			}
			else
			{
				$ret	= $dbtb->fullDrop();
				if(!ret)	die('Error');
			}
		}
		catch(Zend_exception $e)
		{
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
		$this->_redirect('/db/admin/frmlist#fragment-1');
	}
    public function tbregisterAction()
    {
		$params	= $this->getRequest()->getParams();
		if(!$params['tb'] ) $this->_redirect($_SERVER['HTTP_REFERER']);
		$name	= $params['tb'];


		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('a')); 
		
		$dtypes	= array(1=>'INTEGER', 2=>'TEXT', 3=>'REAL');
		$pu		= array('non'=>'', 'pk'=>'PRIMARY KEY', 'un'=>'UNIQUE');
		foreach($params['fls'] as $fl)	if(!empty($fl['title']))	$cols[]	= $fl['title'].' '.$dtypes[$fl['type']].' '.$pu[$fl['pu']];
		if(!is_array($cols) ) $this->_redirect($_SERVER['HTTP_REFERER']);
		$cols	= implode(', ', $cols);
		$sql='CREATE TABLE '.$name.' ('.$cols.')';
		//die($sql);
		try
		{
			$dbtb	= new Db_Model_Table();
			$dbtb->_dbh($name, true);
			$ret	= $dbtb->_dbh->query($sql);
			if($ret)	$this->_redirect('/db/admin');
		//	else		$this->_redirect($_SERVER['HTTP_REFERER']);
		}
		catch(Zend_exception $e)
		{
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
//		print_r($cols); 
		die();
	}
    public function frmtbregisterAction()
    {
		$params	= $this->getRequest()->getParams();
		if(!$params['tb'] ) $this->_redirect($_SERVER['HTTP_REFERER']);
		$fc	= (!$params['fc'] )?3:$params['fc'];
		$this->view->assign('tbname', $params['tb']); 
		$this->view->assign('fcount', $fc); 

		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('a')); 
		
		//print_r($info); die();
	}
    public function tbbrowseAction()
    {
		$params	= $this->getRequest()->getParams();
		if(!$params['tb'] ) $this->_redirect($_SERVER['HTTP_REFERER']);
		$start	= (!$params['st'] )?0:$params['st'];
		$limit	= (!$params['lim'] )?30:$params['lim'];
		$name	= $params['tb'];
		try
		{
			$dbtb	= new Db_Model_Table($name);
			$info	= $dbtb->select('*', 'LIMIT '.$start.','.$limit);
		}
		catch(Zend_exception $e)
		{
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
		
		print_r($info); die();
	}
    public function tbstrucAction()
    {
		$params	= $this->getRequest()->getParams();
		if(!$params['tb'] ) $this->_redirect($_SERVER['HTTP_REFERER']);
		$name	= $params['tb'];
		try
		{
			$dbtb	= new Db_Model_Table($name);
			$info	= $dbtb->_dbh->describeTable($name);
		}
		catch(Zend_exception $e)
		{
			$this->_redirect($_SERVER['HTTP_REFERER']);
		}
		
		
		print_r($info); die();
	}
    public function frmlistAction()
    {
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('a')); 

		$st	= $this->getRequest()->getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		$limit	= 40;
		
		$result	= $this->getTablesList($start, $limit);
		$data	= $this->getTablesRecords($result[0]);

		$this->view->assign('data'	, $data);
		$this->view->assign('count'	, $result[1]);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }


/// Helper Method for Actions -------------------------------------------------------------------*********
	public function getTablesList($st, $limit)
	{
//		$wbsmd	= md5(WBSiD);
//		$_path	= realpath(APPLICATION_PATH .'/../public/flsimgs/'.WBSiD.'/db/_'.$wbsmd[5].$wbsmd[3].$wbsmd[8].$wbsmd[2]);
		$_path	= realpath(APPLICATION_PATH .'/../data/db/'.WBSiD);
		$i	= 0;
		foreach(array_diff(scandir($_path),array('.','..')) as $f)
			if(is_file($_path.'/'.$f) && ereg('.db$',$f) )
			{
				$list[$i]['name']	= preg_replace('/\.db$/', '', $f); 
				$list[$i]['size']	= filesize($_path.'/'.$f); 
				if($list[$i]['size'])	$list[$i]['size']	= round($list[$i]['size']/1000);
				$i++;
			}
		return array(array_slice($list, $st, $limit), count($list));
	}
	public function getTablesRecords($data)
	{
		foreach($data as $k=>$c)
			if($dbtb = new Db_Model_Table($c['name']))
			{
				$data[$k]['records']	= $dbtb->count(); 
			}
		return $data;
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';
		if ( preg_match('/^\d+$/', $this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-4';
	}

}
