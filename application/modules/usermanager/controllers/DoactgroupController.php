<?php

class Usermanager_DoactgroupController extends Zend_Controller_Action
{

	public function deloneAction()
    {
		$this->prepareAct();
		if(preg_match('/^\d+$/', $this->params['id'])) $this->delSomeRows();
		else
		{
			$this->_helper->flashMessenger->addMessage( $this->translate->_('b') );
			$this->_redirect('/usermanager/frmlist/index'.$this->newUriParams);
		}
	}
	public function delsomeAction()
    {
		$this->prepareAct();
		$this->delSomeRows();
	}
	public function delconfirmAction()
    {
		$this->prepareConfirm( array('delone', 'delsome') );
		$this->view->assign('title_site'	, $this->translate->_('c') ); 	
	}
	public function delSomeRows()
	{
		try 
		{
			$this->DB->delete('user_groups','`wbs_id` = '.WBSiD.' AND `id` IN('.$this->params['id'].')');
			$this->_helper->flashMessenger->addMessage( $this->translate->_('a') );
			$this->_redirect('/usermanager/frmgrouplist/index'.$this->newUriParams);
		}
		catch (Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage( $this->translate->_('b') );
			$this->_redirect('/usermanager/frmgrouplist/index'.$this->newUriParams);
		}
	}
	public function prepareConfirm($act)
	{
    	$this->translate	= Zend_registry::get('translate');

		$request		= $this->getRequest();
		$this->params	= $request->getParams();
		if(!empty($this->params['chk']) && is_array($this->params['chk']) ) $this->params['id']	= implode(',', $this->params['chk']); 

		$this->setUriParams();
		if( !empty($this->params['id']) )
		{
			$this->params['id']	= addslashes($this->params['id']);
			$this->newUriParams = '/id/'.$this->params['id'].$this->newUriParams;
		}
		if(preg_match('/^\d+$/', $this->params['id']))					$confirmAct	= '/usermanager/doactgroup/'.$act[0];
		elseif(preg_match('/^(\d+\,)*\d+\,\d+$/', $this->params['id']))	$confirmAct	= '/usermanager/doactgroup/'.$act[1];
		else															$confirmAct	= '#';
		
		$this->view->assign('translate'		, $this->translate ); 	
		$this->view->assign('env'			, $this->env ); 	
		$this->view->assign('confirmAct'	, $confirmAct . $this->newUriParams ); 	
	}
	public function prepareAct()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

    	$this->DB	= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$request		= $this->getRequest();
		$this->params	= $request->getParams();
		$this->setUriParams();
		if( isset($this->params['id']) ) $this->params['id']	= addslashes($this->params['id']);
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';

		if (!empty( $this->params['st']) ) $this->newUriParams .= '/st/'.$this->params['st'];

		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-1';
		}
	}
}

?>