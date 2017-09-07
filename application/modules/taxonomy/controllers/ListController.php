<?php
 
class Taxonomy_ListController extends Zend_Controller_Action 
{



	public function init() 
	{ 
		$this->registry	= Zend_registry::getInstance();
		$this->_helper->_layout->setLayout('dashboard');
	}


    public function indexAction()
    {

    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$this->view->assign('title_site', $translate->_('a'));
		$this->view->assign('translate', $translate);
		$st	= $this->_getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st))){$start	= $st;}else{$start	= 0;}
		$limit	= 25;
		$sql	= 'select * from `wbs_taxonomy_terms` where '.Application_Model_Pubcon::get().' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from `wbs_taxonomy_terms` where '.Application_Model_Pubcon::get());
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }	
	
}
