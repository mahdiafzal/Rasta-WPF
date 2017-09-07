<?php 
 
class Comment_ManagerController extends Zend_Controller_Action
{

    public function indexAction() 
    {
		$this->setLayout();
    	$translate		= Zend_registry::get('translate');
		$this->params	= $this->getRequest()->getParams();
		$flashMsg		= $this->_helper->flashMessenger->getMessages();
		if( !empty( $flashMsg[0]) ) $this->view->assign('errormsg'	, $flashMsg[0]);
		
		$this->setUriParams();
		$this->view->assign('translate'		, $translate ); 	
		$this->view->assign('title_site'	, $translate->_('a'));	
		$this->view->assign('newUriParams'	, $this->newUriParams ); 	


		if(!empty($this->params['pa'])) $this->params['pa'] = explode(':', $this->params['pa']);
		
		
		$Comment	= new Comment_Model_Comment;
		$Comment->contentId	= $this->params['pa'][0];
		$Comment->contentType	= $this->params['pa'][1];
		
		if( $ContentInfos	= $Comment->getContentInfos()) $this->view->assign('ContentInfos'	, $ContentInfos);
		

		if ((isset($this->params['st'])) and (preg_match('/^[0-9]+$/',$this->params['st'])))
		{
			$Comment->setListInterval($this->params['st']);
		}
		
		if($List = $Comment->getList('all')) 
		{
			$this->view->assign('list'	, $List);
			$this->view->assign('count'	, $Comment->getListCount('all'));
			$this->view->assign('start'	, $Comment->listStart);
			$this->view->assign('limit'	, $Comment->listLimit);
		}
		else
		{
			if($Comment->listStart>0)
			{
				$this->newUriParams	= preg_replace('/\/st\/\d+/', '', $this->newUriParams);
				$this->_redirect('/comment/manager/index' .$this->newUriParams);
			}
		}


	}
    public function statusAction()
    {
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		$this->_helper->viewRenderer->setNoRender();

		$request		= $this->getRequest();
		$this->params	= $request->getParams();
		$this->setUriParams();


		$id	= $this->params['id']; 
		if (preg_match("/^[0-9]+\:[0-9]+$/",$id))
		{
			$par	= explode(':',$id);
			$id		= $par[0];
			$act	= $par[1];
			$data	= array('status'	=> $act);
			try 
			{
				$this->DB->update('wbs_content_comment',$data,'`wbs_id` = '.WBSiD.' and id='.$id);
				$this->_redirect('/comment/manager/index' .$this->newUriParams);
			}
			catch (Zend_exception $e)
			{
				$this->_helper->flashMessenger->addMessage( $this->translate->_('y') );
				$this->_redirect('/comment/manager/index'.$this->newUriParams);
			}
		}
		else 
		{
			$this->_helper->flashMessenger->addMessage( $this->translate->_('x') );
			$this->_redirect('/comment/manager/index'.$this->newUriParams);
		}
    }
    public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= Zend_registry::get('front_db');
		$translate 		= Zend_registry::get('translate');
		$this->params	= $this->getRequest()->getParams();
		$this->setUriParams();
		$id	= $this->params['id'];
		if(!is_numeric($id)) return false;
		$result	= $DB->delete('wbs_content_comment','wbs_id="'.WBSiD.'" and id="'.addslashes($id).'"');	
		if ($result)	$msg[] = $translate->_('v'); 
		else			$msg[] = $translate->_('w'); 
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/comment/manager/index'.$this->newUriParams);
    }		
	public function setUriParams()
	{
		$this->newUriParams =	'';

		if (!empty( $this->params['pa']) )
			$this->newUriParams .=	'/pa/'.$this->params['pa'];
		if (!empty( $this->params['st']) )
			$this->newUriParams .=	'/st/'.$this->params['st'];
		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-2';
		}
	}
	public function setLayout()
	{
		$this->_helper->_layout->setLayout('dashboard');
	}
	

}