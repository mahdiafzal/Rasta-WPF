<?php 
 
class Comment_IndexController extends Zend_Controller_Action
{

    public function indexAction() 
    {
    	$translate		= Zend_registry::get('translate');
		$this->params	= $this->getRequest()->getParams();
		$flashMsg		= $this->_helper->flashMessenger->getMessages();
		
		$this->setUriParams();
		$this->view->assign('translate'		, $translate ); 	
		$this->view->assign('title_site'	, $translate->_('a'));	
		$this->view->assign('newUriParams'	, $this->newUriParams ); 	


		if(!empty($this->params['pa'])) $this->params['pa'] = explode(':', $this->params['pa']);
		
		$this->view->assign('formId'	, $this->params['pa'][0]);
		if($this->params['pa'][1]==1)		$formType = 'rtc';
		elseif($this->params['pa'][1]==2)	$formType = 'gal';
		else								$formType = '';
		$this->view->assign('formType'	, $formType);
		
		$Comment	= new Comment_Model_Comment;
		$Comment->contentId	= $this->params['pa'][0];
		$Comment->contentType	= $this->params['pa'][1];
		if(! $ContentInfos	= $Comment->getContentInfos()) die('ERROR');
		$this->view->assign('contentTitle'	, $ContentInfos['title']);
		if($ContentInfos['setting'][3]==0) die('ERROR');
		elseif($ContentInfos['setting'][3]==2)
		{
		
			if ((isset($this->params['st'])) and (preg_match('/^[0-9]+$/',$this->params['st'])))
			{
				$Comment->setListInterval($this->params['st']);
			}
			
			if($List = $Comment->getList())
			{
				$this->view->assign('list'	, $List);
				$this->view->assign('count'	, $Comment->getListCount());
				$this->view->assign('start'	, $Comment->listStart);
				$this->view->assign('limit'	, $Comment->listLimit);
			}
			$this->view->assign('nolistmsg'	, $translate->_('e'));
		}
		elseif($ContentInfos['setting'][3]==1)
		{
			$this->view->assign('nolistmsg'	, $translate->_('f'));
		}
		$this->view->assign('env'	, $env);

		if( !empty( $flashMsg[0]) ) $this->view->assign('errormsg'	, $flashMsg[0]);
		if( !empty( $flashMsg[1]) ) 
		{
			$this->view->assign('params'	, $flashMsg[1]);
			return true;
		}

	}
	public function setUriParams()
	{
		$this->newUriParams =	'';

		if (!empty( $this->params['pa']) )
			$this->newUriParams .=	'/pa/'.$this->params['pa'];
		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-3';
		}
	}

}