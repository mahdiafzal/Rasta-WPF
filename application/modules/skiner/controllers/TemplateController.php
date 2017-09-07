<?php
 
class Skiner_TemplateController extends Zend_Controller_Action 
{
   public function init() 
    {
		$this->_helper->_layout->setLayout('dashboard');
    }
    public function frmlistAction()
    {
    	$DB			= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('f')); 

		$st	= $this->getRequest()->getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 25;
		$pubcon	= '`wbs_id` =0 AND (wbs_group RLIKE "\/'.str_replace(',','\/|\/',WBSgR).'\/")';

		$sql	= 'select * from `wbs_skin` where '.$pubcon.' ORDER BY `rank` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from `wbs_skin` where '.$pubcon);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }

}
