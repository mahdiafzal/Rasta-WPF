<?php
 
class Dashboard_IndexController extends Zend_Controller_Action 
{

 	var $ses;
	//-----------------------------------------------------------------------------
   public function init() 
    {
		$this->registry	= Zend_registry::getInstance();
    }

    public function indexAction()
    {
		$translate 		= $this->registry['translate'];
		$this->view->assign('title_site', $translate->_('a')); //"صفحه اصلی");
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());			
    }
}
