<?php
 
class Taxonomy_ActsController extends Zend_Controller_Action 
{



	public function init() 
	{ 

	}

    public function indexAction()
    {


    }
	
    public function delAction() 
    {
		$this->_helper->viewRenderer->setNoRender();
		if(empty($this->DB))	$this->DB	= Zend_registry::get('front_db');
		$translate 		= Zend_registry::get('translate');

		$id	= $this->_getParam('id');
		if(is_numeric($id))
			try
			{
				$this->DB->beginTransaction();
				$result1 = $result2 = false;
				$result1 = $this->DB->delete('wbs_taxonomy_terms_allsubs','wbs_id="'.WBSiD.'" and tt_id="'. addslashes($id) .'"');
				$result2 = $this->DB->delete('wbs_taxonomy_terms','wbs_id="'.WBSiD.'" and id="'. addslashes($id) .'"');
				if($result2)
				{
					$this->DB->commit();
					$msg[] = $translate->_('c'); 
				}
				else
				{
					$this->DB->rollBack();
					$msg[] = $translate->_('d'); 
				}
			}															
			catch(Zend_exception $e)
			{
				$this->DB->rollBack();
				$msg[] = $translate->_('d');
				//echo $e->getMessage();
			}				
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/taxonomy/list#fragment-2');
    }		
    public function updateallsubsAction() 
    {
		$translate 		= Zend_registry::get('translate');
		$id	= $this->_getParam('id');
		if (!is_numeric($id))	
		{
			$msg[]= $translate->_('b');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/taxonomy/list#fragment-2');
		}
		if($this->updateTermAllSubs($id))	$msg[]= $translate->_('a');
		else	$msg[]= $translate->_('b');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/taxonomy/list#fragment-2');
	}


/// Helper Method for Actions -------------------------------------------------------------------*********


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
