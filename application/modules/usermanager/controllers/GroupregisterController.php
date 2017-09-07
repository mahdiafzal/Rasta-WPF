<?php

class Usermanager_GroupregisterController extends Zend_Controller_Action
{

    public function crtAction()
    {
		$data	= $this->prepareRegistration();
		$this->insertNewGroup($data);
	}
    public function editAction()
    {

		$data	= $this->prepareRegistration();
		if( isset($this->params['id']) ) $this->updateGroup($data, $this->params['id']);
		else
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_redirect('/usermanager/frmgrouplist/index'.$this->env );
		}
	}
	
    public function updateallsubsAction() 
    {
		$translate 	= Zend_registry::get('translate');
		$id	= $this->_getParam('id');
		if (!is_numeric($id))	
		{
			$msg[]= $translate->_('g');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/usermanager/frmgrouplist/index'.$this->env );
		}
		if($this->updateGroupAllSubs($id))	$msg[]= $translate->_('f');
		else	$msg[]= $translate->_('g');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/usermanager/frmgrouplist/index'.$this->env );
	}

//// Helper Methods
	protected function prepareRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB	= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		//$request		= $this->getRequest();
		$this->params	= $this->_getAllParams();
		$this->setUriParams();
		$permissions	= '1';
		for($i=1; $i<120; $i++) $permissions .= (!empty($this->params['p_'.$i]) && $this->params['p_'.$i]=='on')?'1':'0';
		$this->params['g_permission']	= $permissions;
		$this->getUserGroupFirstSubs();
		$this->validate();

		$data['wbs_id']			= WBSiD;
		$data['title']			= $this->params['g_title'];
		$data['permissions']	= $this->params['g_permission'];
		$data['first_subs']		= $this->params['first_subs'];
		$data	= array_map(trim, $data);
		//foreach($data as $key=>$value) $data[$key]	= trim($value);
		return $data;
	}
	protected function insertNewGroup($data)
	{
		try
		{
			$this->DB->insert('user_groups',$data);
			$id		= $this->DB->lastInsertId();
			$this->updateGroupAllSubs($id);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('a') ));
			$this->_redirect('/usermanager/frmgrouplist/index'.$this->env );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('b') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/usermanager/frmgroupregister/index'.$this->newUriParams);
		}
	}
	protected function updateGroup($data, $groupID)
	{
		try
		{
			$this->DB->update('user_groups',$data ,'`wbs_id` = '.WBSiD.' and `id` ='.$groupID);
			$this->updateGroupAllSubs($groupID);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('c') ));
			$this->_redirect('/usermanager/frmgrouplist/index'.$this->env );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/usermanager/frmgroupregister/index'.$this->newUriParams);
		}
	}
	protected function setUriParams()
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
	protected function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'g_title'	=> $this->params['g_title']
			 		 );
		
		$rule=array	(
						'g_title'	=>'notNull'
					);
		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('g_title')	== false) $error[]	= $this->translate->_('e'); 

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/usermanager/frmgroupregister/index'.$this->newUriParams);
			return false;
		}
		return true;
	}
	protected function getUserGroupFirstSubs()
	{
		$this->params['first_subs']	= '';
		if(!is_array($this->params['urgsubs']) || count($this->params['urgsubs'])==0) return false;
		sort($this->params['urgsubs']);
		$ugroup		= '/'. implode('/', $this->params['urgsubs']).'/';
		$this->params['first_subs']	= $ugroup;
		return true;
	}
	protected function	updateGroupAllSubs($data)
	{
		$allSubs	= $this->getGroupAllSubs($data);
		if($allSubs=='false')	return false;
		$allSubs	= trim($allSubs);
		
		if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
		$d['ug_id']	= (is_array($data))?$data['id']:$data;
		$d['wbs_id']= WBSiD;
		$d['subs']	= '/'.str_replace(',', '/', $allSubs).'/';
		
		$sql		= "SELECT COUNT(*)  FROM `user_group_allsubs` WHERE `wbs_id`='".WBSiD."' AND `ug_id`=".$d['ug_id'];
		try
		{
			if(0==$this->DB->fetchOne($sql))
				if(empty($allSubs)) return true;
				else	$this->DB->insert('user_group_allsubs',$d);
			else
				if(empty($allSubs))	$this->DB->delete('user_group_allsubs','`wbs_id`="'.WBSiD.'" and `ug_id`="'. addslashes($d['ug_id']) .'"');
				else	$this->DB->update('user_group_allsubs', $d,'`wbs_id`='.WBSiD.' and `ug_id`='.$d['ug_id']);	
			return true;
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	protected function	getGroupAllSubs($data, $i=0)
	{
		if($i>50) return '';
		$i++;
		if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
    	if(!is_array($data))
		{
			if(!is_numeric($data))	return false;
			$sql	= "SELECT `first_subs`  FROM `user_groups` WHERE `wbs_id`=".WBSiD." AND `id`=".$data;
			$result	= $this->DB->fetchAll($sql);
			if(empty($result[0]['first_subs']))	return '';
			$d['id']			= $data;
			//$d['first_subs']	= preg_replace('/\/?(\d+)\//', '$1,', $result[0]['first_subs']);
			$d['first_subs']	= str_replace('/', ',', $result[0]['first_subs']);
			$d['first_subs']	= preg_replace('/(^\s*\,)|(\,\s*$)/', '', $d['first_subs']);
		}
		else	$d	= $data;
		
		$exceptsubs	= array();
		if(!empty($this->first_subs))	$exceptsubs	= array_unique( array_filter( explode(',', $this->first_subs) ) );
		$exceptsubs[]	= $d['id'];
		$d['first_subs']	= implode(',', array_diff( array_unique( array_filter( explode(',', $d['first_subs']) ) ), $exceptsubs ) );
		if(empty($d['first_subs'])) return '';
		$this->first_subs	.= ','.$d['first_subs'];

		$sql	= "SELECT `first_subs`  FROM `user_groups` WHERE `wbs_id`=".WBSiD." AND `id` IN (".$d['first_subs'].")";
		$result	= $this->DB->fetchAll($sql);
		if(!$result) return $d['first_subs'];
		foreach($result as $fsubs)	
			if(!empty($fsubs['first_subs']))
			{
				$fsubs	= array( 'id'=>$d['id'], 'first_subs'=>preg_replace('/(^\s*\,)|(\,\s*$)/', '', str_replace('/', ',', $fsubs['first_subs'])) );
				$ret	= $this->getGroupAllSubs($fsubs, $i);
				if(!empty($ret))	$d['first_subs']	.= ','.$ret;
			}
		return $d['first_subs'];
	}
}

?>