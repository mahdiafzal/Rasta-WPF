<?php
 
class Gadget_IndexController extends Zend_Controller_Action 
{

    public function indexAction()
    {
		$this->_redirect('/gadget/admin/frmlist/env/dsh#fragment-3');
	}
    public function interfaceAction()
    {
		$modelData['gad_id']	= $this->_getParam('gad_id');
		
		$flashMsg	= $this->_helper->flashMessenger->getMessages();
		if( !empty( $flashMsg[0]) ) $modelData['alertmsg']	= $flashMsg[0];
		if( !empty( $flashMsg[1]) ) $modelData['rtcParams']	= $flashMsg[1];
		
		$page		= new Gadget_Model_GadgetInterface($modelData);
		
		$this->view->assign('pageHead'		, $page->getHtmlHead());
		$HtmlBody	=	$page->getHtmlBody();
		echo $HtmlBody;
    }
}
