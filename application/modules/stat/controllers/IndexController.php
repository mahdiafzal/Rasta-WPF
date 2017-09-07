<?php 
 
class Stat_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }
    public function indexAction() 
    {
		$this->params	= $this->getRequest()->getParams();
		$this->view->assign('rep', $this->params['rep']);
    }

}