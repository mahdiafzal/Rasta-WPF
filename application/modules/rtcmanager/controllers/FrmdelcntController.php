<?php

class Rtcmanager_FrmdelcntController extends Zend_Controller_Action
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
//			$response = $this->getResponse();
//			$response->insert('menu',$this->view->render('menu.phtml'));		
//		}
    }

//	public function baseUrl()
//		{
//			return "";
//		}

    public function indexAction()
    {
    	$this->translate	= Zend_registry::get('translate');
		$this->view->assign('translate'		, $this->translate ); 	

		//$ses	= new Zend_Session_Namespace('MyApp');
//		if (!$this->ses->isAdmin)
//		{
//			$this->_redirect('admin/user/userpage');
//		}
		
		//$this->view->headLink()->appendStylesheet('/css/gradient.css');
		$this->view->assign('title_site'		, 'حذف متن');	
		$request= $this->getRequest();
		$env_param	= $request->getParam('env');
		if ($env_param=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$env =	'/env/dsh#fragment-2';
		}
		else
		{
			$env =	'';
			//$this->_helper->_layout->setLayout('simple');
			//$this->_helper->layout()->disableLayout();
		}		

		if (($request->isPost()) and (count($request->getPost('chk'))>0))
		{
			$IDs= implode(',',$request->getPost('chk'));
			if (!preg_match('/^[0-9]+(\,[0-9]+)*$/',$IDs))
			{
				$this->_helper->flashMessenger->addMessage('خطا در آرگومان های ورودی');
				$this->_redirect('/rtcmanager/frmlistcnt/index'.$env);
			}
			$sql	= 'SELECT `id`,`title`,`ltn_name` FROM `wbs_rtcs` WHERE '.Application_Model_Pubcon::get(1001).' AND `id` IN ('.$IDs.')';
			$result	= $this->DB->fetchAll($sql);
			if ($result)
			{
				$this->view->assign('data',$result);
			}
		}
		else if ($request->isGet())
		{
			$id		= $request->getParam('id');
			if (!isset($id) or (!preg_match('/^[0-9]+$/',$id)))
			{
				$this->_redirect('/rtcmanager/frmlistcnt/index'.$env);
			}
			$sql	= 'SELECT `id`,`title`,`ltn_name` FROM `wbs_rtcs` WHERE '.Application_Model_Pubcon::get(1001).' AND `id`='.addslashes($id);
			$result	= $this->DB->fetchAll($sql);
			if (count($result)==1)
			{
				$this->view->assign('data',$result);
			}
		}
		$this->view->assign('title'	, 'حذف مطالب');	
		$this->view->assign('env'		, $env);
	}
//-----------
}

?>