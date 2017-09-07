<?php

class Rtcmanager_IndexController extends Zend_Controller_Action
{

	public function indexAction()
    {
		$this->_redirect('/rtcmanager/frmlistcnt/index/env/dsh#fragment-2');
//		$this->_helper->viewRenderer->setNoRender();
//		$this->getResponse()->setHeader('Refresh', '3; URL=/rtcmanager/frmlistcnt');

    }
}

