<?php 
 
class Comment_RegisterController extends Zend_Controller_Action
{

    public function crtAction()
    {
		$data	= $this->prepareRegistration();
		$this->insertNew($data);
	}
	public function prepareRegistration()
	{
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');
		$request			= $this->getRequest();
		$this->params		= $request->getParams();
		$this->setUriParams();

		if(!empty($this->params['pa'])) $this->params['pa'] = explode(':', $this->params['pa']);
		if($this->params['c_type']=='rtc')		$this->params['c_type'] = 1;
		elseif($this->params['c_type']=='gal')	$this->params['c_type'] = 2;
		else									$this->params['c_type'] = 'error';

		$this->validate();
		$this->is_allowed($this->params['c_id']);
 	 	 	 	 	 	 	
		$data['wbs_id']		= WBSiD;
		$data['type_id']	= $this->params['c_type'];
		$data['content_id']	= $this->params['c_id'];
		$data['name']		= $this->params['c_name'];
		$data['email']		= $this->params['c_email'];
		$data['site']		= $this->params['c_site'];
		$data['text']		= $this->params['c_text'];

		foreach($data as $key=>$value) $data[$key]	= trim($value);
		
		return $data;
	}
	public function insertNew($data)
	{

		try
		{
			$this->DB->insert('wbs_content_comment',$data);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('a') ));
			$this->_redirect('/comment/index/index'.$this->newUriParams);
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('b') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/comment/index/index'.$this->newUriParams);
		}
	}
	public function is_allowed($id)
	{
		$sql	= 'select `setting` from `wbs_rtcs` where `wbs_id` = '. WBSiD .' AND `id`='. addslashes($id);
		$result	= $this->DB->fetchAll($sql);
		if(is_array($result) and count($result)==1)
		{
			if($result[0]['setting'][3]=='1' or $result[0]['setting'][3]=='2') return true;
		}

		$this->_helper->flashMessenger->addMessage( array($this->translate->_('e')) );
		$this->_helper->flashMessenger->addMessage($this->params);
		$this->_redirect('/comment/index/index'.$this->newUriParams);
		return false;
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'c_id'		=> $this->params['c_id'],
						'c_name'	=> $this->params['c_name'],
						'c_email'	=> $this->params['c_email'],
						'c_text'	=> $this->params['c_text']
			 		 );
		
		$rule=array	(
						'c_id'		=>'isNumber',
						'c_name'	=>'notNull',
						'c_email'	=>'isEmail',
						'c_text'	=>'notNull'
					);
		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('c_name')	== false) $error[]	= $this->translate->_('f'); 
		if($frmValidator->getResult('c_email')	== false) $error[]	= $this->translate->_('g'); 
		if($frmValidator->getResult('c_text')	== false) $error[]	= $this->translate->_('h'); 
		
		if($frmValidator->hasValidLength($this->params['c_text'], 5)	== false) $error[]	= $this->translate->_('i'); 

		if($frmValidator->getResult('c_id')		== false) $error	= array($this->translate->_('e')); 
//		$c_type	= array('rtc','gal');
//		if( !in_array($this->params['c_type'], $c_type) ) $error	= array($this->translate->_('e')); 
		if( $this->params['c_id']!= $this->params['pa'][0]) $error	= array($this->translate->_('e')); 
		if( $this->params['c_type']!= $this->params['pa'][1]) $error	= array($this->translate->_('e')); 

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/comment/index/index'.$this->newUriParams);
			return false;
		}
		return true;
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