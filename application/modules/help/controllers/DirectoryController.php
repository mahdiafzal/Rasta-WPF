<?php
 
class Help_DirectoryController extends Zend_Controller_Action 
{

   public function init() 
    {
		
    }

    public function partiAction()
    {
		$this->setENV();
		$this->view->assign('scontent', $this->translate->_($this->params['season']));
		$this->view->assign('stitle', $this->translate->_($this->params['season'].'-t'));
		$this->view->assign('nexttitle', $this->translate->_($this->params['season'].'-nt'));
		$this->view->assign('nextlink', $this->translate->_($this->params['season'].'-nl'));
		
    }
    public function listAction()
    {
		$this->setENV();
    }



    public function setENV()
    {
		$this->params	= $this->_getAllParams();
		$this->translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('a'));
		//if($this->params['env']=='dsh') 
		$this->_helper->_layout->setLayout('dashboard');
    }

}
