<?php
 
class Gadget_RtcController extends Zend_Controller_Action 
{
	var $DB;
	var $ses;
    public function init()
    {
		if($this->_getParam('action')=='register')
			if(isset($_GET['rtc']) and is_numeric($_GET['rtc']))
				$this->_forward('edit', null, null, $this->_getAllParams());
			else
				$this->_forward('crt', null, null, $this->_getAllParams());
		
		$this->ses 	= new Zend_Session_Namespace('MyApp');
    	$this->DB	= Zend_registry::get('front_db');
		
	}
    public function registerAction()
    {
		 die();
	}
    public function crtAction()
    {
		$data	= $this->prepareRegistration();
		$this->insertNewRTC($data[0], $data[1]);
	}
    public function editAction()
    {
		$data	= $this->prepareRegistration();
		if(isset($_GET['rtc']) and is_numeric($_GET['rtc'])) $this->updateRTC($data[0], $data[1], $_GET['rtc']);
		else
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_redirect($this->refer[0]);
		}
	}
	
/// Helper Method for Actions -------------------------------------------------------------------*********
	public function prepareRegistration()
	{
		$this->_helper->viewRenderer->setNoRender();
    	$this->translate	= Zend_registry::get('translate');
		$this->params	= $this->_getAllParams();
		
		$this->refer[0]	= $_SERVER['HTTP_REFERER'];
		$this->refer[1]	= '/gad/'.$this->params['gadget_id'];
		

		if(preg_match('/^\d\d\d\d/', $this->params['publishup'], $year))
			if($year[0]<2000)	$this->params['publishup']	= $this->fa_to_ger($this->params['publishup']);

		if(preg_match('/^\d\d\d\d/', $this->params['publishdown'], $year))
			if($year[0]<2000)	$this->params['publishdown']= $this->fa_to_ger($this->params['publishdown']);
		
		$this->systemicValidation();

		$data1['wbs_id']		= WBSiD;
		$data1['user_id']		= $this->ses->id;
		$data1['gad_id']		= $this->params['gadget_id'];
		$data1['title']			= $this->params['titlei'];
		$data1['ltn_name']		= $this->params['titleii'];
		$data1['description']	= $this->params['abstract'];
		
		$data1['is_published']	= ($this->params['status']=='1')?'1':'0';
		$data1['publish_up']	= $this->params['publishup'];
		$data1['publish_down']	= $this->params['publishdown'];
		
		$data1['content']		= $this->params['text'];
		$data1['scenarios']		= $this->params['scenario'];
		$data1['setting']		= (empty($this->params['setting']))?'00000':$this->params['setting'];
		$data1['user_group']	= (empty($this->params['usergroups']))?'0':$this->params['usergroups'];

		$data2['wbs_id']		= WBSiD;
		$data2['description']	= $this->params['description'];
		$data2['author']		= $this->params['author'];
		$data2['keywords']		= $this->params['keywords'];

		if(empty($data1 ['publish_down']))	$data1 ['publish_down']	= '9999-12-30 12:00:00';
		
		foreach($data1 as $key=>$value) $data1[$key]	= trim($value);
		foreach($data2 as $key=>$value) $data2[$key]	= trim($value);
		
		return array($data1, $data2);
	}
	public function insertNewRTC($data1, $data2)
	{
		$data1['crt_date']		= new Zend_DB_expr('now()');
		if(empty($data1['publish_up']))		$data1['publish_up']	= $data1['crt_date'];
		try
		{
			$this->DB->beginTransaction();
			$this->DB->insert('wbs_rtcs',$data1);
			$recordID	= $this->DB->lastInsertId();
			//add metadata
			if (!empty($data2['description'])or !empty($data2['author']) or !empty($data2['keywords']))
			{
				$data2['txt_id']	= $recordID;
				$this->DB->insert('wbs_rtc_metadata',$data2);
			}
			//end of add metadata
			$this->DB->commit();

			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('a') ));
			$this->_redirect($this->refer[1]);
		}
		catch(Zend_exception $e)
		{
			$this->DB->rollBack();
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('b') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect($this->refer[0]);
		}
	}
	public function updateRTC($data1, $data2, $rtcID)
	{
		if(empty($data1['publish_up']))		$data1['publish_up']	= new Zend_DB_expr('`wbs_rtcs`.`crt_date`');
		try
		{
			$this->DB->beginTransaction();
			$this->DB->update('wbs_rtcs',$data1 ,'`wbs_id` = '.WBSiD.' and id ='.$rtcID);
			//add metadata
			$rr=$this->DB->fetchAll('select * from wbs_rtc_metadata where `wbs_id` = '.WBSiD.' and `txt_id`='.$rtcID);
			if (count($rr)==1)
			{
				$this->DB->update('wbs_rtc_metadata',$data2 ,'`wbs_id` = '.WBSiD.' and `txt_id` ='.$rtcID);
			}
			else if (!empty($data2['description'])or !empty($data2['author']) or !empty($data2['keywords']))
			{
				$data3	= array_merge(array('txt_id'=> $rtcID), $data2 );
				$this->DB->insert('wbs_rtc_metadata', $data3 );
			}
			//end of add metadata
			$this->DB->commit();
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('c') ));
			$this->_redirect($this->refer[1]);
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect($this->refer[0]);
		}
	}
	public function systemicValidation()
	{
		$error	= array();
		if(!is_numeric($this->params['gadget_id']))	$error[]	= '';
		if(strlen($this->params['titlei'])<2)		$error[]	= '';
		if(strlen($this->params['text'])<2)			$error[]	= '';
		
		$dapa	= '/^\d\d\d\d\-\d\d\-\d\d\s\d\d\:\d\d\:\d\d$/';
		if(! preg_match($dapa, $this->params['publishup']))		$this->params['publishup']	= '';
		if(! preg_match($dapa, $this->params['publishdown']))	$this->params['publishdown']= '';
		
		$scpa	= '/^(\/\d+)+\/$/';
		if(!empty($this->params['scenario']))
			if(! preg_match($scpa, $this->params['scenario']))	$error[]	= '';
		if(!empty($this->params['usergroups']))
			if(! preg_match($scpa, $this->params['usergroups']))$error[]	= '';

		$stpa	= '/^[0-2]{5}$/';
		if(!empty($this->params['setting']))
			if(! preg_match($stpa, $this->params['setting']))	$error[]	= '';

		if(count($error)==0)	return;
		$this->_helper->flashMessenger->addMessage(array( count($error).$this->translate->_('g') ));
		$this->_helper->flashMessenger->addMessage($this->params);
		$this->_redirect($this->refer[0]);
	}
	public function fa_to_ger($date)
	{
		if ($date=='')	return	 NULL;
		$arr	= explode(' ',$date)	;
		$d		= explode('-',$arr[0])	;
		$pdate	= new Rasta_Pdate;
		$arr[0] = implode('-',$pdate->persian_to_gregorian($d[0],$d[1],$d[2]));
		return    implode(' ',$arr);
	}
}
