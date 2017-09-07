<?php

class Rtcmanager_RegisterController extends Zend_Controller_Action
{
//--------------
	var $DB;
	var $ses;
    public function init()
    {
		$this->ses 	= new Zend_Session_Namespace('MyApp');
    	$this->DB	= Zend_registry::get('front_db');
	}

	public function fa_to_ger($date)
	{
		if ($date=='')
		{
			return	 NULL;
		}
		else 
		{
			$arr	= explode(' ',$date)	;
			$d		= explode('-',$arr[0])	;
			$pdate	= new Rasta_Pdate;
			$arr[0] = implode('-',$pdate->persian_to_gregorian($d[0],$d[1],$d[2]));
			return    implode(' ',$arr);
		}
	}
//    public function indexAction()
//    {
//		$data	= $this->prepareRegistration();
//		if( isset($this->params['id']) ) $this->updateRTC($data[0], $data[1], $this->params['id']);
//		else $this->insertNewRTC($data[0], $data[1]);
//	}
    public function crtAction()
    {
		$data	= $this->prepareRegistration();
		$this->insertNewRTC($data[0], $data[1]);
	}
    public function editAction()
    {
		$data	= $this->prepareRegistration();
		if( isset($this->params['id']) ) $this->updateRTC($data[0], $data[1], $this->params['id']);
		else
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams);
		}
	}
	
	public function prepareRegistration()
	{
		$this->_helper->viewRenderer->setNoRender();

    	$this->translate	= Zend_registry::get('translate');

		//$request		= $this->getRequest();
		$this->params	= $this->_getAllParams();
		if( !is_numeric($this->params['ctype']) or $this->params['ctype']<1 )	$this->params['ctype'] = 1;
		$this->setUriParams();
		$this->getRtcScUg('taxoterm');
		$this->getRtcScUg('usergroups');
		
		//$this->getUserGroups();
		$this->getRTCSettings();
		$this->getRTCExtras();	
		$this->validate();
		$data1['wbs_id']		= WBSiD;
		$data1['user_id']		= $this->ses->id;
		$data1['type_id']		= $this->params['ctype'];
		$data1['ltn_name']		= $this->params['titleii'];
		$data1['title']			= $this->params['titlei'];
		$data1['is_published']	= ($this->params['status']=='true')?'1':'0';
		$data1['description']	= $this->params['abstract'];
		$data1['publish_up']	= $this->params['publishup'];
		$data1['publish_down']	= $this->params['publishdown'];
		$data1['content']		= $this->params['text'];
		$data1['taxoterms']		= $this->params['taxoterm'];
		$data1['setting']		= $this->params['setting'];
		$data1['user_group']	= (empty($this->params['usergroups']))?0:$this->params['usergroups'];

		$data2['wbs_id']		= WBSiD;
		$data2['description']	= $this->params['description'];
		$data2['author']		= $this->params['author'];
		$data2['keywords']		= $this->params['keywords'];
		$data2['extra_data']	= $this->params['extra_data'];
		

		if(empty($data1 ['publish_down']))	$data1 ['publish_down']	= '9999-12-30 12:00:00';
		
		$data1	= array_map(trim, $data1);
		$data2	= array_map(trim, $data2);
//		print_r($data1);
//		print_r($data2);
//		die();
		
//		foreach($data1 as $key=>$value) $data1[$key]	= trim($value);
//		foreach($data2 as $key=>$value) $data2[$key]	= trim($value);
		
		return array($data1, $data2);
	}
	public function getRtcScUg($name)
	{
		$sels	= array_filter( explode('/', $this->params[$name]) );
		if(!is_array($sels) || count($sels)==0)
		{
			$this->params[$name]	= '';
			return false;
		}
		sort($sels);
		$this->params[$name]	= '/'. implode('/', $sels).'/';;
		return true;
	}
	public function getRTCSettings()
	{
		$setting[0]	= (empty($this->params['showauthorname']) or $this->params['showauthorname']=='false')?'0':'1';
		$setting[1]	= (empty($this->params['showregisterdate']) or $this->params['showregisterdate']=='false')?'0':'1';
		$setting[2]	= (empty($this->params['showregistertime']) or $this->params['showregistertime']=='false')?'0':'1';
		$comment_modes	= array('disable'=>0, 'private'=>1, 'public'=>2);
		$setting[3]	= (!isset($comment_modes[ $this->params['commentsetting'] ]))?'0':$comment_modes[ $this->params['commentsetting'] ];
		$setting[4]	= (empty($this->params['showsinglepostlink']) or $this->params['showsinglepostlink']=='false')?'0':'1';
		$this->params['setting']	= ( is_array($setting) )?implode('', $setting):'00000';
	}
	public function getRTCExtras()
	{
		$this->params['extra_data']	= '';
		if(is_array($this->params['extra']))
			foreach($this->params['extra'] as $name=>$value)
			{
				$name	= trim($name);
				if (!is_array($value) and is_string($name) and strlen($name)>0 )
					$this->params['extra_data']	.= '<var:'.$name.'><![CDATA[ '.trim($value).' ]]></var:'.$name.'>';
			}
		
	}
	
	
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	'title'		 	=> $this->params['titlei'],
						//'ltn_name'	 	=> $this->params['ltn_name'] ,
						'content'	 	=> $this->params['text']
			 		 );
		
		$rule=array	(	'title'			=> 'notNull' ,
						//'ltn_name'		=> 'isLatin' ,
						'content'		=> 'notNull'
			 		 );
		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('title')	== false) $error[]	= $this->translate->_('e'); //'عنوان متن نباید خالی باشد';
		//if($frmValidator->getResult('ltn_name')	== false) $error[]	= 'عنوان لاتین نباید خالی باشد و باید از حروف لاتین استفاده شود';
		if($frmValidator->getResult('content')	== false) $error[]	= $this->translate->_('f'); //'متن نباید خالی باشد.';		 
		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/rtcmanager/frmregister/index'.$this->newUriParams);
			return false;
		}
		return true;
	}
	public function insertNewRTC($data1, $data2)
	{
		$data1['crt_date']		= new Zend_DB_expr('now()'); //date('Y-m-d  H:i:s');
		if(empty($data1['publish_up']))		$data1['publish_up']	= $data1['crt_date'];
		try
		{
			$this->DB->beginTransaction();
			$this->DB->insert('wbs_rtcs',$data1);
			$recordID	= $this->DB->lastInsertId();
			//add metadata
			if (!empty($data2['description'])or !empty($data2['author']) or !empty($data2['keywords']) or !empty($data2['extra_data']))
			{
				$data2['txt_id']	= $recordID;
				$this->DB->insert('wbs_rtc_metadata',$data2);
			}
			//end of add metadata
			$this->DB->commit();
			
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('a') ));
			if($this->params['actiontype']=='save') $this->_redirect('/rtcmanager/frmregister/index/ctype/'.$this->params['ctype'].'/id/'.$recordID.$this->env );
			else									$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->DB->rollBack();
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('b') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/rtcmanager/frmregister/index'.$this->newUriParams);
			//echo $e->getMessage();
		}
	}
	public function updateRTC($data1, $data2, $rtcID)
	{
		if(empty($data1['publish_up']))		$data1['publish_up']	= new Zend_DB_expr('`wbs_rtcs`.`crt_date`');
		try
		{
			$this->DB->beginTransaction();
			//print_r($data1); die();
			$this->DB->update('wbs_rtcs',$data1 , Application_Model_Pubcon::get(1001).' AND id ='.$rtcID);
			//add metadata
			$rr=$this->DB->fetchAll('SELECT * FROM wbs_rtc_metadata WHERE '.Application_Model_Pubcon::get(1000).' AND `txt_id`='.$rtcID);
			if (count($rr)==1)
			{
				$this->DB->update('wbs_rtc_metadata',$data2 , Application_Model_Pubcon::get(1000).' AND `txt_id` ='.$rtcID);
			}
			elseif (!empty($data2['description'])or !empty($data2['author']) or !empty($data2['keywords']) or !empty($data2['extra_data']))
			{
				$data3	= array_merge(array('txt_id'=> $rtcID), $data2 );
				$this->DB->insert('wbs_rtc_metadata', $data3 );
			}
			//end of add metadata
			$this->DB->commit();
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('c') ));
			if($this->params['actiontype']=='save') $this->_redirect('/rtcmanager/frmregister/index'.$this->newUriParams );
			else									$this->_redirect('/rtcmanager/frmlistcnt/index'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('d') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_redirect('/rtcmanager/frmregister/index'.$this->newUriParams);
		}
	}
	
	public function setUriParams()
	{
		$this->newUriParams =	'';
		if ( !is_numeric($this->params['ctype']) or  $this->params['ctype']<1 )
			$this->newUriParams .=	'/ctype/1';
		else
			$this->newUriParams .=	'/ctype/'.$this->params['ctype'];
		if ( is_numeric($this->params['id']) )
			$this->newUriParams .=	'/id/'.$this->params['id'];
		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-2';
		}
	}
	
//	public function getUserGroups()
//	{
//		$this->params['user_group']	= '0';
//		if(!is_array($this->params['ugroup']) || count($this->params['ugroup'])==0) return false;
//		sort($this->params['ugroup']);
//		$ugroup		= '/'. implode('/', $this->params['ugroup']).'/';
//		$this->params['user_group']	= $ugroup;
//		return true;
//	}

}

?>