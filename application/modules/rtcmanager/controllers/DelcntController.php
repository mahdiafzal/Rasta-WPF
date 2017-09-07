<?php

class Rtcmanager_DelcntController extends Zend_Controller_Action
{
//--------------
	var $DB;
	var $ses;
    public function init()
    {
        /* Initialize action controller here*/
		$this->ses 	= new Zend_Session_Namespace('MyApp');
		
		//$registry	= Zend_registry::getInstance();
    	$this->DB	= Zend_registry::get('front_db');
    	
//		if (!isset($this->ses->id))
//		{
//			$this->_redirect('/login');
//		}
//		else
//		{
////			$this->gethelper('viewRenderer')->view->assign('user_id',$this->ses->id); 
////			$response = $this->getResponse();
////			$response->insert('menu',$this->view->render('menu.phtml'));		
//		}
    }

    public function indexAction()
    {
    	$this->translate	= Zend_registry::get('translate');
		//$this->view->assign('translate'		, $this->translate ); 	

		$this->_helper->viewRenderer->setNoRender();
		
		$request		= $this->getRequest();
		$this->params	= $request->getParams();
		$this->setUriParams();

//		$env_param	= $request->getParam('env');
//		if ($env_param=='dsh')
//		{
//			$this->_helper->_layout->setLayout('dashboard');
//			$env =	'/env/dsh';
//		}
//		else
//		{
//			$env =	'';
//		}		


		if ($request->isPost())
		{
			$IDs	= implode(',',$_POST['ids']);
			if (!isset($_POST['ids'])or(!preg_match('/^[0-9]+(\,[0-9]+)*$/',$IDs)))
			{
				$this->_helper->flashMessenger->addMessage( $this->translate->_('a') );
				$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams);
			}
			
			try
			{
				$this->DB->beginTransaction();
				$this->DB->delete('wbs_rtcs'	, Application_Model_Pubcon::get(1001).' AND `id` 	IN ('.addslashes($IDs).')');
				$this->DB->delete('wbs_rtc_metadata', Application_Model_Pubcon::get(1000).' AND `txt_id` IN ('.addslashes($IDs).')');
				//$this->DB->delete('tbl_###','cnt_id in ('.addslashes($id).')');
				$this->DB->commit();
				$this->_helper->flashMessenger->addMessage( $this->translate->_('b') );
				$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams);
			}catch(Zen_exception $e)
			{
				$this->DB->rollBack();
				$this->_helper->flashMessenger->addMessage( $this->translate->_('c') );
				$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams);
			}
		}
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';

		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-2';
		}
	}

}

?>