<?php

class Usermanager_RegisterController extends Zend_Controller_Action
{

//    public function indexAction()
//    {
//		$data	= $this->prepareRegistration();
//		if( isset($this->params['id']) ) $this->updateUser($data, $this->params['id']);
//		else $this->insertNewUser($data);
//	}
    public function crtAction()
    {
		$data	= $this->prepareRegistration();
		$this->checkUserUnique();
		$this->insertNewUser($data);
	}
    public function editAction()
    {
		$data	= $this->prepareRegistration();
		if( isset($this->params['id']) )
		{
			$this->checkUserUnique($this->params['id']);
			$this->updateUser($data, $this->params['id']);
		}
		else
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_redirect('/usermanager/frmlist/index'.$this->newUriParams);
		}
	}
	public function prepareRegistration()
	{
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		$request			= $this->getRequest();
		$this->params		= $request->getParams();
		if( isset($this->params['id']) ) $this->params['id']	= addslashes($this->params['id']);
		
		$this->view->assign('translate'		, $this->translate ); 	

		$this->getUserGroups();
		$this->setUriParams();
		$this->validate();
 	 	 	 	 	 	 	
		$data['first_name']		= $this->params['f_name'];
		$data['last_name']		= $this->params['l_name'];
		$data['username']		= $this->params['u_name'];
		if(!empty($this->params['p_word'])) 
			$data['password']	= md5($this->params['p_word']);
		$data['is_active']		= $this->params['u_status'];
		$data['wb_user_id']		= WBSiD;
		$data['user_group']		= $this->params['u_group'];

		//foreach($data as $key=>$value) $data[$key]	= trim($value);
		$data	= array_map(trim, $data);
		
		return $data;
	}
	public function insertNewUser($data)
	{
		$data['crt_date']		= new Zend_DB_expr('now()');
		$data['is_admin']		= 0;

		try
		{
			$this->DB->insert('users',$data);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('a') ));
			$this->_redirect('/usermanager/frmlist/index'.$this->env );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('b') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/usermanager/frmregister/index'.$this->newUriParams);
		}
	}
	public function updateUser($data, $userID)
	{
		try
		{
			$this->DB->update('users',$data ,'`wb_user_id` = '.WBSiD.' AND id ='.$userID.' AND `is_admin`=0');
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('c') ));
			$this->_redirect('/usermanager/frmlist/index'.$this->env );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/usermanager/frmregister/index'.$this->newUriParams);
		}
	}

	public function getUserGroups()
	{
		foreach($this->params as $key=>$value)
			if(preg_match('/^g\_\d+$/', $key)) 
				if($value == 'on') 
					$selectedGroups[] = preg_replace('/^g\_/', '', $key);
		
		$this->params['u_group']	= '0';
		if(!is_array($selectedGroups) || count($selectedGroups)==0) return false;

		sort($selectedGroups);
		//$groups		= implode('/', $selectedGroups);
		$this->params['u_group']	= implode('/', $selectedGroups);
		//return $groups;
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';

		if ( preg_match('/^\d+$/', $this->params['id']) )
			$this->newUriParams .=	'/id/'.$this->params['id'];
		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-1';
		}
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'first_name'	=> $this->params['f_name'],
						'last_name'		=> $this->params['l_name'],
						'username'		=> $this->params['u_name']
			 		 );
		
		$rule=array	(
						'first_name'	=>'isFarsiLatin',
						'last_name'		=>'isFarsiLatin',
						'username'		=>'isEmail'
					);
		if( ! isset($this->params['id']) )
		{
			$data['password']	= $this->params['p_word'];
			$rule['password']	= 'notNull';
		}

		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('first_name')	== false) $error[]	= $this->translate->_('e'); 
		if($frmValidator->getResult('last_name')	== false) $error[]	= $this->translate->_('f'); 
		if($frmValidator->getResult('username')		== false) $error[]	= $this->translate->_('g'); 
		
		if(!empty($data['password']))
		{
			if($frmValidator->getResult('password')		== false) $error[]	= $this->translate->_('i'); 
			if($frmValidator->hasValidLength($this->params['p_word'], 6)	== false) $error[]	= $this->translate->_('h'); 
			if($this->params['p_word'] != $this->params['p_word_r']) $error[]	= $this->translate->_('j'); 
		}
		elseif(!empty($this->params['p_word']))
		{
			if($frmValidator->hasValidLength($this->params['p_word'], 6)	== false) $error[]	= $this->translate->_('h'); 
			if($this->params['p_word'] != $this->params['p_word_r']) $error[]	= $this->translate->_('j'); 
		}


		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/usermanager/frmregister/index'.$this->newUriParams);
			return false;
		}
		return true;
	}
	public function checkUserUnique($id=NULL)
	{
		$sqlEnd	= '';
		if(!empty($id)) $sqlEnd	= ' AND `id`!='.addslashes($id);
		$sql1		= 'select * from `users` where `wb_user_id` = '.WBSiD.'  AND `username` ="'.addslashes($this->params['u_name']).'"'.$sqlEnd;
		$result1	= $this->DB->fetchAll($sql1);
		if(count($result1)!=0) $error[]	= $this->translate->_('k');
		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/usermanager/frmregister/index'.$this->newUriParams);
			return false;
		}
	}

}

?>