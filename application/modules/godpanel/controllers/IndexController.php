<?php

class Godpanel_IndexController extends Zend_Controller_Action
{
    public function init()
    {
		Godpanel_Model_User_User::initUser();
		if(!defined('USRiD') or USRiD!=='1')	die(Application_Model_Messages::message(404));
    }
    public function indexAction()
    {
		$this->_redirect('/godpanel/panel');
    }
}

