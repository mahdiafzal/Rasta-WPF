<?php
 
class Help_MeController extends Zend_Controller_Action 
{
	public function init() 
    {
			$this->_helper->_layout->setLayout('dashboard');
    }

    public function indexAction()
    {
		//$this->setENV();
		//$this->view->assign('title_site', $this->translate->_('b'));
		echo '<h1>Help Me</h1>';
    }
    public function aboutAction()
    {
		$params	= $this->_getAllParams();
		if( isset($params['id']) )	$data['id']		= $params['id'];
		if( isset($params['p']) )	$data['path']	= $params['p'];
		if( isset($params['st']) )	$data['stags']	= $params['st'];
		if( isset($params['at']) )	$data['atags']	= $params['at'];
		if( isset($params['q']) )	$data['q']		= $params['q'];
		$art	= new Help_Model_Article();
		$this->view->assign('result', $art->get($data));
    }
    public function artAction()
    {
		$params	= $this->_getAllParams();
		if( is_string($params['p']) ) 	$data	= $params['p'];
		if( is_numeric($params['id']) ) $data	= $params['id'];
		if( !isset($data) )	return;
		$art	= new Help_Model_Article();
		$this->view->assign('result', $art->get($data));
    }


    public function setENV()
    {
		//$this->params	= $this->getRequest()->getParams();
		//$this->translate	= Zend_Registry::get('translate');
		//$this->view->assign('translate', $this->translate);
		$this->_helper->_layout->setLayout('dashboard');
    }

}
