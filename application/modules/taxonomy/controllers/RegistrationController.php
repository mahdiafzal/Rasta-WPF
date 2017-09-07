<?php
 
class Taxonomy_RegistrationController extends Zend_Controller_Action 
{



	public function init() 
	{ 
		$this->_helper->_layout->setLayout('dashboard');
	}


    public function indexAction()
    {


    }


    public function frmcrtAction() 
    {	
		$translate 		= Zend_registry::get('translate');
    	$this->DB		= Zend_registry::get('front_db');
		
		$this->view->assign('title_site', $translate->_('a') ); 
		$this->view->assign('translate', $translate);

		$message = $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg',$message[0]);}			
		if(!empty($message[1])){$this->view->assign('data',$message[1]);}
		$this->view->assign('whs_tterms',$this->getSiteTaxonomyTerms());
    }	
    public function crtAction() 
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
		$data['title']	= $this->params['s_title'];
		$data['name']	= $this->params['s_name'];
		$data['wbs_id']	= WBSiD;
		
		if (strlen(trim($data['title']))==0				)	$msg[]= $translate->_('a');
		if (!preg_match('/^[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\w\d\_\-]+$/',$data['name']))	$msg[]= $translate->_('b');

		$sql	= 'select * from `wbs_taxonomy_terms` where `wbs_id` = '. WBSiD .' and `name`="'.$data['name'].'"';
		if (count($this->DB->fetchAll($sql))>0)	$msg[]= $translate->_('f');

		if(count($msg)>0)
		{
			$this->_helper->FlashMessenger($msg);
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/taxonomy/registration/frmcrt#fragment-2');
		}

		$this->DB->insert('wbs_taxonomy_terms',$data);
		$id		= $this->DB->lastInsertId();
		$ret	= $this->updateTermAllSubs($id);
		
		$msg[] = $translate->_('g');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/taxonomy/list#fragment-2');
    }	
    public function frmeditAction() 
    {	
		$translate 	= Zend_registry::get('translate');
    	$this->DB	= Zend_registry::get('front_db');
		$id			= $this->getRequest()->getParam	('id');

		$this->view->assign('title_site', $translate->_('a'));
		$this->view->assign('translate', $translate);
		$this->view->assign('whs_tterms',$this->getSiteTaxonomyTerms($id));
		
		$message = $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	, $message[0]);}			
		if(!empty($message[1])){$this->view->assign('data'	, $message[1]);}	
		if(empty($message[1]))
		{
			if (empty($id))
			{
				$msg[] = $translate->_('q');
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/taxonomy/list#fragment-2');
			}
			$sql	= "select * from `wbs_taxonomy_terms` where `wbs_id` ='".WBSiD."' and `id`='".$id."'";
			$result	= $this->DB->fetchAll($sql);
			if (count($result)==1)
			{
				$this->view->assign('data', $result[0]);
			}
			else
			{
				$msg[] = $translate->_('r'); 
				$this->_helper->FlashMessenger($msg);
				$this->_redirect('/taxonomy/list#fragment-2');
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
		$data['name']		= $this->params['s_name'];


		if (strlen(trim($data['title']))==0)					$msg[]= $translate->_('a');
		if (!preg_match('/^[ابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهیآءأإؤژيةۀ\w\d\_\-]+$/',$data['name']))	$msg[]= $translate->_('b');

		$sql		= 'select * from `wbs_taxonomy_terms` where `wbs_id` = '. WBSiD .' and `name`="'.$data['name'].'" and `id`!='.$id;
		if (count($this->DB->fetchAll($sql))>0)							$msg[]= $translate->_('f'); //'آدرس وارد شده تکراری میباشد' ;
		if(count($msg)>0)
		{
			$this->_helper->FlashMessenger($msg);
			$data['id']= $id;
			$this->_helper->FlashMessenger($data);
			$this->_redirect('/taxonomy/registration/frmedit/id/'.$id.'#fragment-2');
		}
		$this->DB->update('wbs_taxonomy_terms',$data,'`wbs_id`='.WBSiD.' and `id`='.$id);	
		
		$ret	= $this->updateTermAllSubs($id);

		$msg[] = $translate->_('g'); //'سناریو مورد نظر با موفقیت اصلاح شد';
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/taxonomy/list#fragment-2');
    }		

/// Helper Method for Actions -------------------------------------------------------------------*********
	protected function	getSiteTaxonomyTerms($except=false)
	{
    	if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
		$wherest	= '';
		if($except)	$wherest	= ' AND `id`!='.$except;
		$sql			= "SELECT id, title  FROM `wbs_taxonomy_terms` WHERE `wbs_id`='".WBSiD."'".$wherest." ORDER BY `id` DESC;";
		$result			= $this->DB->fetchAll($sql);
		return 	$result;
	}

	protected function	updateTermAllSubs($data)
	{
		$allSubs	= $this->getTermAllSubs($data);
		if($allSubs=='false')	return false;
		$allSubs	= trim($allSubs);
		
		if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
		$d['tt_id']	= (is_array($data))?$data['id']:$data;
		$d['wbs_id']= WBSiD;
		$d['subs']	= '/'.str_replace(',', '/', $allSubs).'/';
		
		$sql		= "SELECT COUNT(*)  FROM `wbs_taxonomy_terms_allsubs` WHERE `wbs_id`='".WBSiD."' AND `tt_id`=".$d['tt_id'];
		try
		{
			if(0==$this->DB->fetchOne($sql))
				if(empty($allSubs)) return true;
				else	$this->DB->insert('wbs_taxonomy_terms_allsubs',$d);
			else
				if(empty($allSubs))	$this->DB->delete('wbs_taxonomy_terms_allsubs','`wbs_id`="'.WBSiD.'" and `tt_id`="'. addslashes($d['tt_id']) .'"');
				else	$this->DB->update('wbs_taxonomy_terms_allsubs', $d,'`wbs_id`='.WBSiD.' and `tt_id`='.$d['tt_id']);	
			return true;
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	protected function	getTermAllSubs($data, $i=0)
	{
		if($i>50) return '';
		$i++;
		if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
    	if(!is_array($data))
		{
			if(!is_numeric($data))	return false;
			$sql	= "SELECT `first_subs`  FROM `wbs_taxonomy_terms` WHERE `wbs_id`=".WBSiD." AND `id`=".$data;
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

		$sql	= "SELECT `first_subs`  FROM `wbs_taxonomy_terms` WHERE `wbs_id`=".WBSiD." AND `id` IN (".$d['first_subs'].")";
		$result	= $this->DB->fetchAll($sql);
		if(!$result) return $d['first_subs'];
		foreach($result as $fsubs)	
			if(!empty($fsubs['first_subs']))
			{
				$fsubs	= array( 'id'=>$d['id'], 'first_subs'=>preg_replace('/(^\s*\,)|(\,\s*$)/', '', str_replace('/', ',', $fsubs['first_subs'])) );
				$ret	= $this->getTermAllSubs($fsubs, $i);
				if(!empty($ret))	$d['first_subs']	.= ','.$ret;
			}
		return $d['first_subs'];
	}


}
