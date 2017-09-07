<?php
 
class Help_IndexController extends Zend_Controller_Action 
{

   public function init() 
    {
		
    }

    public function indexAction()
    {
		$this->setENV();
		$this->view->assign('title_site', $this->translate->_('b'));
    }
    public function sysparamsAction()
    {
		$this->setENV();
		$this->view->assign('title_site', $this->translate->_('a'));
    }


    public function setENV()
    {
		$this->params	= $this->getRequest()->getParams();
		$this->translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $this->translate);
		$this->_helper->_layout->setLayout('dashboard');
    }

}
