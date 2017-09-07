<?php
 
class Skiner_IndexController extends Zend_Controller_Action 
{

   public function init() 
    {
		//$this->_helper->_layout->setLayout('dashboard');
    }
    public function indexAction()
    {
		$this->_redirect('/skiner/skin/frmlist#fragment-4');
    }
}
