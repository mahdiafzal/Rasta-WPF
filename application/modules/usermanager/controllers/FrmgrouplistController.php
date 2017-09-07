<?php

class Usermanager_FrmgrouplistController extends Zend_Controller_Action
{

    public function indexAction()
    {
    	$this->DB	= Zend_registry::get('front_db');
    	$translate	= Zend_registry::get('translate');
		$this->view->assign('translate'		, $translate ); 	
		$this->view->assign('title_site'	, $translate->_('a'));	
		
		$params		= $this->getRequest()->getParams();
		$env_param	= $this->getRequest()->getParam('env');
//		if ($env_param=='dsh')
//		{
			$this->_helper->_layout->setLayout('dashboard');
			$env =	'/env/dsh#fragment-1';
//		}
//		else
//		{
//			$env =	'';
//		}




		$data	= '';

		if ((isset($params['st'])) and (preg_match('/^[0-9]+$/',$params['st'])))
		{
			$start	= $params['st'];
		}
		else
		{
			$start	= 0;
		}

		$limit	= 25;
		$sql	= 'select * from `user_groups` where '. Application_Model_Pubcon::get(1110) .' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		$count	= $this->DB->fetchAll('select count(id) as `cnt` from `user_groups` where '. Application_Model_Pubcon::get(1110));
		

		$this->view->assign('title'	, $translate->_('b'));
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		$this->view->assign('env'	, $env);
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());	
	}

//-----------
}

?>