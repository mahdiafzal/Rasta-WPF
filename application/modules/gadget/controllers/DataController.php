<?php
 
class Gadget_DataController extends Zend_Controller_Action 
{

    public function ajaxgetAction()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
		if(!isset($_POST['gid']) or !is_numeric($_POST['gid']))	$this->_helper->json->sendJson(array('status'=>'error'));
		$DB		= (is_object($this->DB))?$this->DB:Zend_Registry::get('front_db');
		$sql	= 'SELECT * FROM `wbs_gadget_data` WHERE `wbs_id`='.WBSiD.' AND `gad_id`='.addslashes($_POST['gid']);
		$result	= $DB->fetchAll($sql);
		if(!is_array($result) or count($result)!=1)	$this->_helper->json->sendJson(array('status'=>'null'));;
		$this->_helper->json->sendJson(array('status'=>'success', 'data'=>$result[0]['data']));
    }
    public function ajaxsetAction()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
		if(!isset($_POST['gid']) or !is_numeric($_POST['gid']))
			$this->_helper->json->sendJson(array('status'=>'error'));
		if(!isset($_POST['data']))
			$this->_helper->json->sendJson(array('status'=>'error'));
		$DB		= (is_object($this->DB))?$this->DB:Zend_Registry::get('front_db');
		$sql	= 'SELECT COUNT(`gd_id`) FROM `wbs_gadget_data` WHERE `wbs_id`='.WBSiD.' AND `gad_id`='.addslashes($_POST['gid']);
		$result	= $DB->fetchOne($sql);
		$data['data']		= trim($_POST['data']);
		if($result==0)
		{
			$data['wbs_id']		= WBSiD;
			$data['gad_id']		= $_POST['gid'];
			$result	= $this->DB->insert('wbs_gadget_data', $data);
			if(!$result)	$this->_helper->json->sendJson(array('status'=>'error'));
		}
		elseif($result==1)
		{
			$result	= $this->DB->update('wbs_gadget_data', $data ,'`wbs_id` = '.WBSiD.' and `gad_id` ='.addslashes($_POST['gid']) );
			if(!$result)	$this->_helper->json->sendJson(array('status'=>'error'));
		}
		else
			$this->_helper->json->sendJson(array('status'=>'error'));
		$this->_helper->json->sendJson(array('status'=>'success'));
    }

}
