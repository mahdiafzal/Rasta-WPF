<?php

class Usermanager_IndexController extends Zend_Controller_Action
{

	public function indexAction()
    {
		$this->_redirect('/usermanager/frmlist/index/env/dsh#fragment-1');
//		$this->_helper->viewRenderer->setNoRender();
//		$this->getResponse()->setHeader('Refresh', '3; URL=/rtcmanager/frmlistcnt');
    }
}

