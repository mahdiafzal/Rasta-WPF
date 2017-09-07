<?php
 
class Dashboard_ScenarioController extends Zend_Controller_Action 
{

	public function init() 
	{ 
		$this->registry	= Zend_registry::getInstance();
	}
    public function frmlistAction() 
    {
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$this->view->assign('title_site', $translate->_('a'));
		$this->view->assign('translate', $translate);
		$st	= $this->_getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st))){$start	= $st;}else{$start	= 0;}
		$limit	= 25;
		$sql	= 'select * from `wbs_scenario` where '.Application_Model_Pubcon::get().' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from `wbs_scenario` where '.Application_Model_Pubcon::get());
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
    public function frmcrtAction() 
    {	
		$translate 		= Zend_registry::get('translate');
    	$this->DB		= Zend_registry::get('front_db');
		
		$this->view->assign('title_site', $translate->_('a') ); 
		$this->view->assign('translate', $translate);
		
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		
		$message = $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg',$message[0]);}			
		if(!empty($message[1])){$this->view->assign('data',$message[1]);}
		if(!empty($message[1]))
		{
			$xml 		= new SimpleXMLElement('<root>'.$message[1]['properties'].'</root>'); 
			$this->view->assign('count',$xml->c);
			$this->view->assign('paging',$xml->p);	
		}

		$sql	= 'select * from `wbs_pages` where `wbs_id` = '. WBSiD .' ORDER BY `local_id` DESC';
		$this->view->assign('pages',$this->DB->fetchAll($sql));
		$this->view->assign('whs_scens',$this->getSiteScenarios());
    }	
    public function crtAction() 
    {
		$this->_helper->viewRenderer->setNoRender();

		$translate 		= Zend_registry::get('translate');
    	$this->DB		= Zend_registry::get('front_db');
		$this->params			= $this->_getAllParams();
		
		if(!empty($this->params['subscens']))
		{
			foreach($this->params['subscens'] as $k=>$v) if('on'==strtolower($v))$subs[]	= $k;
			$subs = array_unique($subs);
			sort($subs);
			$data['first_subs']	= '/'.implode('/', $subs).'/';
		}
		$data['title']		= $this->params['s_title'];
		$data['latin_title']= $this->params['s_latin_title'];
		$data['uri']		= $this->params['s_uri'];
		$data['page_id']	= $this->params['s_page_id'];

		$data['wbs_id']		= WBSiD;
		
		$this->getUserGroups();
		$data['user_group']		= $this->params['user_group'];
		
		$data['action_id']	= $this->params['ddown_action'];		
		$count				= $this->params['count'];
		$paging				= ($this->params['paging'])? '1' : '0' ;
		if($data['action_id']==1) $data['properties']	= '<c>'.$count.'</c><p>'.$paging.'</p>';
		if($data['action_id']==2) $data['properties']	= '<c>'.$count.'</c><f>'.$paging.'</f>';

		if (strlen(trim($data['title']))==0								)	$msg[]= $translate->_('a');
		if (!preg_match('/^[a-zA-Z\d\:\_\-\s]+$/',$data['latin_title'])	)	$msg[]= $translate->_('b');
		if (!preg_match('/^[0-9]+$/',$count)							)	$msg[]= $translate->_('c');
		if (!preg_match('/^[0-9]+$/',$data['page_id'])					)	$msg[]= $translate->_('d');
		if (!preg_match('/^[\w\d]+(\/[\w\d]+)*$/',$data['uri'])	)	$msg[]= $translate->_('e');

		$data['uri']='/'.$data['uri'];
		$sql	= 'select * from `wbs_scenario` where `wbs_id` = '. WBSiD .' and `uri`="'.$data['uri'].'"';
		if (count($this->DB->fetchAll($sql))>0)									$msg[]= $translate->_('f');

		if(count($msg)>0)
		{
			$this->_helper->FlashMessenger($msg);
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/dashboard/scenario/frmcrt#fragment-2');
		}

		$this->DB->insert('wbs_scenario',$data);
		$id		= $this->DB->lastInsertId();
		$ret	= $this->updateScenAllSubs($id);
		
		$msg[] = $translate->_('g');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/scenario/frmlist#fragment-2');
    }	
    public function frmeditAction() 
    {	
		$translate 	= Zend_registry::get('translate');
    	$this->DB	= Zend_registry::get('front_db');
		$id			= $this->getRequest()->getParam	('id');

		$this->view->assign('title_site', $translate->_('a'));
		$this->view->assign('translate', $translate);
		$this->view->assign('whs_scens',$this->getSiteScenarios($id));
		
		$this->view->assign('wbsUserGroups', $this->getWbsUserGroups());
		
		$message = $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	, $message[0]);}			
		if(!empty($message[1])){$this->view->assign('data'	, $message[1]);}	
		if(!empty($message[1]))
		{
			$sql	= 'select * from `wbs_pages` where `wbs_id` = '. WBSiD .' ORDER BY `local_id` DESC';
			$this->view->assign('pages',$this->DB->fetchAll($sql));
			$xml 		= new SimpleXMLElement('<root>'.$message[1]['properties'].'</root>'); 
			$this->view->assign('count',$xml->c);
			if($message[1]['action_id']==1) $this->view->assign('paging',$xml->p);
			if($message[1]['action_id']==2) $this->view->assign('paging',$xml->f);
		}
		else
		{
			
			if (empty($id))
			{
				$msg[] = $translate->_('q');
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/dashboard/scenario/frmlist#fragment-2');
			}
			$sql	= "select * from `wbs_scenario` where `wbs_id` ='".WBSiD."' and `id`='".$id."'";
			$result	= $this->DB->fetchAll($sql);
			if (count($result)==1)
			{
				$this->view->assign('data', $result[0]);
				$sql	= 'select * from `wbs_pages` where `wbs_id` = '. WBSiD .' ORDER BY `local_id` DESC';
				$this->view->assign('pages',$this->DB->fetchAll($sql));
				$xml 		= new SimpleXMLElement('<root>'.$result[0]['properties'].'</root>'); 
				$this->view->assign('count',$xml->c);
				
				if($result[0]['action_id']==1) $this->view->assign('paging',$xml->p);
				if($result[0]['action_id']==2) $this->view->assign('paging',$xml->f);
			}
			else
			{
				$msg[] = $translate->_('r'); 
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/dashboard/scenario/frmlist#fragment-2');
			}
		}
    }	
    public function editAction() 
    {
		$this->_helper->viewRenderer->setNoRender();

		$translate 		= Zend_registry::get('translate');
    	$this->DB		= Zend_registry::get('front_db');
		$this->params	= $this->_getAllParams();
		
		if(!empty($this->params['subscens']))
		{
			foreach($this->params['subscens'] as $k=>$v) if('on'==strtolower($v))$subs[]	= $k;
			$subs = array_unique($subs);
			sort($subs);
			$data['first_subs']	= '/'.implode('/', $subs).'/';
		}
		else	$data['first_subs']	= '';

		$id					= $this->params['id'];
		$data['title']		= $this->params['s_title'];
		$data['latin_title']= $this->params['s_latin_title'];
		$data['uri']		= $this->params['s_uri'];
		$data['page_id']	= $this->params['s_page_id'];
		
		$this->getUserGroups();
		$data['user_group']		= $this->params['user_group'];
		
		$count				= $this->params['count'];
		$paging				= ($this->params['paging'])? '1' : '0' ;
		$data['action_id']	= $this->params['ddown_action'];
		if($data['action_id']==1) $data['properties']	= '<c>'.$count.'</c><p>'.$paging.'</p>';
		if($data['action_id']==2) $data['properties']	= '<c>'.$count.'</c><f>'.$paging.'</f>';

		if (strlen(trim($data['title']))==0)							$msg[]= $translate->_('a');
		if (!preg_match('/^[a-zA-Z\d\:\_\-]+$/',$data['latin_title']))	$msg[]= $translate->_('b');
		if (!preg_match('/^[0-9]+$/',$count))							$msg[]= $translate->_('c');
		if (!preg_match('/^[0-9]+$/',$data['page_id']))					$msg[]= $translate->_('d');
		if (!preg_match('/^[a-zA-Z\d]+(\/[a-zA-Z\d]+)*$/',$data['uri']))$msg[]= $translate->_('e');
		
		$data['uri']='/'.$data['uri'];
		$sql		= 'select * from `wbs_scenario` where `wbs_id` = '. WBSiD .' and `uri`="'.$data['uri'].'" and `id`!='.$id;
		if (count($this->DB->fetchAll($sql))>0)							$msg[]= $translate->_('f'); //'آدرس وارد شده تکراری میباشد' ;
		if(count($msg)>0)
		{
			$this->_helper->FlashMessenger($msg);
			$data['id']= $id;
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/dashboard/scenario/frmedit/id/'.$id.'#fragment-2');
		}
		$this->DB->update('wbs_scenario',$data,'`wbs_id`='.WBSiD.' and `id`='.$id);	
		
		$ret	= $this->updateScenAllSubs($id);

		$msg[] = $translate->_('g'); //'سناریو مورد نظر با موفقیت اصلاح شد';
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/scenario/frmlist#fragment-2');
    }		
    public function delAction() 
    {
		$this->_helper->viewRenderer->setNoRender();
    	$DB				= $this->registry['front_db'];
		$translate 		= $this->registry['translate'];
		$id	= $this->getRequest()->getParam	('id');
		$result	= $DB->delete('wbs_scenario','wbs_id="'.WBSiD.'" and id="'. addslashes($id) .'"');	
		if ($result)	$msg[] = $translate->_('a'); 
		else			$msg[] = $translate->_('b'); 
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/scenario/frmlist#fragment-2');
    }		
    public function updateallsubsAction() 
    {
		$translate 		= Zend_registry::get('translate');
		$id	= $this->_getParam('id');
		if (!is_numeric($id))	
		{
			$msg[]= $translate->_('b');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/dashboard/scenario/frmlist#fragment-2');
		}
		if($this->updateScenAllSubs($id))	$msg[]= $translate->_('a');
		else	$msg[]= $translate->_('b');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/dashboard/scenario/frmlist#fragment-2');
	}

/// Helper Method for Actions -------------------------------------------------------------------*********
	protected function	getSiteScenarios($except=false)
	{
    	if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
		$wherest	= '';
		if($except)	$wherest	= ' AND `id`!='.$except;
		$sql			= "SELECT id, title  FROM `wbs_scenario` WHERE `wbs_id`='".WBSiD."'".$wherest." ORDER BY `id` DESC;";
		$result			= $this->DB->fetchAll($sql);
		return 	$result;
	}
	protected function	updateScenAllSubs($data)
	{
		$allSubs	= $this->getScenAllSubs($data);
		if($allSubs=='false')	return false;
		$allSubs	= trim($allSubs);
		
		if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
		$d['sc_id']	= (is_array($data))?$data['id']:$data;
		$d['wbs_id']= WBSiD;
		$d['subs']	= '/'.str_replace(',', '/', $allSubs).'/';
		
		$sql		= "SELECT COUNT(*)  FROM `wbs_scenario_allsubs` WHERE `wbs_id`='".WBSiD."' AND `sc_id`=".$d['sc_id'];
		try
		{
			if(0==$this->DB->fetchOne($sql))
				if(empty($allSubs)) return true;
				else	$this->DB->insert('wbs_scenario_allsubs',$d);
			else
				if(empty($allSubs))	$this->DB->delete('wbs_scenario_allsubs','`wbs_id`="'.WBSiD.'" and `sc_id`="'. addslashes($d['sc_id']) .'"');
				else	$this->DB->update('wbs_scenario_allsubs', $d,'`wbs_id`='.WBSiD.' and `sc_id`='.$d['sc_id']);	
			return true;
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	protected function	getScenAllSubs($data, $i=0)
	{
		if($i>50) return '';
		$i++;
		if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
    	if(!is_array($data))
		{
			if(!is_numeric($data))	return false;
			$sql	= "SELECT `first_subs`  FROM `wbs_scenario` WHERE `wbs_id`=".WBSiD." AND `id`=".$data;
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

		$sql	= "SELECT `first_subs`  FROM `wbs_scenario` WHERE `wbs_id`=".WBSiD." AND `id` IN (".$d['first_subs'].")";
		$result	= $this->DB->fetchAll($sql);
		if(!$result) return $d['first_subs'];
		foreach($result as $fsubs)	
			if(!empty($fsubs['first_subs']))
			{
				$fsubs	= array( 'id'=>$d['id'], 'first_subs'=>preg_replace('/(^\s*\,)|(\,\s*$)/', '', str_replace('/', ',', $fsubs['first_subs'])) );
				$ret	= $this->getScenAllSubs($fsubs, $i);
				if(!empty($ret))	$d['first_subs']	.= ','.$ret;
			}
		return $d['first_subs'];
	}
	protected function getWbsUserGroups()
	{
		$sql		= "SELECT * FROM `user_groups` WHERE ".Application_Model_Pubcon::get(1110);
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	protected function getUserGroups()
	{
		$this->params['user_group']	= '0';
		if(!is_array($this->params['ugroup']) || count($this->params['ugroup'])==0) return false;
		sort($this->params['ugroup']);
		$ugroup		= '/'. implode('/', $this->params['ugroup']).'/';
		$this->params['user_group']	= $ugroup;
		return true;
	}

}
