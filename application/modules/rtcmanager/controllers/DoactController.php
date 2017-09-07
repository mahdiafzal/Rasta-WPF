<?php

class Rtcmanager_DoactController extends Zend_Controller_Action
{
//--------------
	var $DB;
	var $ses;
    public function init()
    {
        /* Initialize action controller here*/
		$this->ses 	= new Zend_Session_Namespace('MyApp');
		
		$registry	= Zend_registry::getInstance();
    	$this->DB	= $registry['front_db'];
    	
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
	//-------------------  
    public function indexAction()
    {
    	$this->translate	= Zend_registry::get('translate');
		//$this->view->assign('translate'		, $this->translate ); 	

		$this->_helper->viewRenderer->setNoRender();
//		if (!$this->ses->isAdmin)
//		{
//			$this->_redirect('admin/user/userpage');
//		}
		$request		= $this->getRequest();
		$this->params	= $request->getParams();
		$this->setUriParams();

//		$env_param	= $this->getRequest()->getParam('env');
//		if ($env_param=='dsh')
//		{
//			$this->_helper->_layout->setLayout('dashboard');
//			$env =	'/env/dsh';
//		}
//		else
//		{
//			$env =	'';
//			//$this->_helper->_layout->setLayout('simple');
//			//$this->_helper->layout()->disableLayout();
//		}	


		$id	= $this->params['id']; //$this->getRequest()->getParam('id');
		if (preg_match("/^[0-9]+\.[0-9]+\.[0-9]+$/",$id))
		{
			$par	= explode('.',$id);
			$id		= $par[0];
			$act	= $par[1];
			$data	= array('is_published'	=> $act);
			try 
			{
				$this->DB->update('wbs_rtcs',$data, Application_Model_Pubcon::get(1001).' AND id='.$id);
				$this->_redirect('/rtcmanager/frmlistcnt/index/st/'.$par[2] .$this->newUriParams);
			}
			catch (Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage( $this->translate->_('a') );
				$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams);
				//echo $e->getMessage();
			}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage( $this->translate->_('b') );
			$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams);
		}
    }
	public function setUriParams()
	{
		if ( !is_numeric($this->params['ctype']) or  $this->params['ctype']<0 )	$this->params['ctype'] = 1;
		$this->newUriParams =	'';
		
		if ( !is_numeric($this->params['ctype']) or  $this->params['ctype']<1 )
			$this->newUriParams .=	'/ctype/1';
		else
			$this->newUriParams .=	'/ctype/'.$this->params['ctype'];

		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-2';
		}
	}
}

?>