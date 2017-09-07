<?php

class Rtcmanager_FrmregisterController extends Zend_Controller_Action
{
//--------------
	var $DB;
	var $ses;
    public function init()
    {
    	$this->DB	= Zend_registry::get('front_db');
		//$this->pubcon	= Application_Model_Pubcon::get();
    }

    public function indexAction()
    {

    	$this->translate	= Zend_registry::get('translate');
		$this->view->assign('translate'		, $this->translate ); 	
		$this->view->assign('title_site'	, $this->translate->_('a') ); 	
		$this->view->assign('title'	, $this->translate->_('b') ); 
		$this->params	= $this->_getAllParams();
		$this->inj_data['#rasta-rtc-registration-action-url#']	 = '/rtcmanager/register/crt/actiontype/save'.$this->setUriParams();
		
		$flashMsg	= $this->_helper->flashMessenger->getMessages();
		
		if( empty( $flashMsg[1]) )
		{
			if ( is_numeric($this->params['id']) )
			{
				if( $rtc = $this->getRtc($this->params['id']) )
				{
					$this->params['ctype']	= $rtc['ctype'];
					$this->ctype	= $this->_getContentTypeData();
					$this->addRtcToEditor($rtc);
				}
	//			else
	//				$this->ctype	= $this->_getContentTypeData();
	
			}
			else
				$this->ctype	= $this->_getContentTypeData();
		}
		else
		{
			$flashMsg[1]['abstract']	= htmlspecialchars( stripslashes($flashMsg[1]['abstract']) );
			$flashMsg[1]['text']		= htmlspecialchars( stripslashes($flashMsg[1]['text']) );
			
			if ( is_numeric($this->params['id']) )
			{
				$this->view->assign('title'	, $this->translate->_('c') ); 
				$this->inj_data['#rasta-rtc-registration-action-url#']	 = '/rtcmanager/register/edit/actiontype/save'.$this->setUriParams();
			}
			//print_r($flashMsg[1]); die();
			$this->ctype	= $this->_getContentTypeData();
			$this->addRtcToEditor($flashMsg[1]);
			//if( !empty( $flashMsg[1]) ) $this->view->assign('rtcParams'	, $flashMsg[1]);
		}

		if( !empty( $flashMsg[0]) ) $this->view->assign('errormsg'	, $flashMsg[0]);
		$this->arrangeSiteData();
		$this->addDataToEditor();
		$this->getEditorFreeOfKeys();
		$this->view->assign('editor'	, $this->editor);
		//print_r( $this->editor );

		$this->view->assign('ctypes', $this->_fetchWbsRtcTypes());
		
		
    }

	protected function _fetchWbsRtcTypes()
	{
		$sql		= 'SELECT ct.ct_title, ts.* FROM wbs_content_type AS ct INNER JOIN wbs_content_type_setting AS ts ON ts.ts_ct_id = ct.ct_id '
					. ' WHERE '.Application_Model_Pubcon::get(1110, 'ct')
					. ' AND '. Application_Model_Pubcon::get(1001, 'ts')
					. ' AND ct.`lang`="'.LANG.'" AND ts.ts_status=1 ;'; //die($sql);
		//$sql	= 'SELECT * FROM `wbs_content_type_setting` WHERE '.Application_Model_Pubcon::get(1001).' ORDER BY `ts_id` ASC';
		if( $result	= $this->DB->fetchAll($sql) ) return $result;
		return false;
	}





	protected function _getContentTypeData()
	{
		if ( !is_numeric($this->params['ctype']) or  $this->params['ctype']<1 )	$this->params['ctype']	= 1;
		
		//$sql		= "SELECT * FROM `wbs_content_type` WHERE `wbs_id` IN (".WBSiD.", 0) AND `ct_id`=".$this->params['ctype'];
		
		$sql		= 'SELECT * FROM wbs_content_type AS ct INNER JOIN wbs_content_type_setting AS ts ON ts.ts_ct_id = ct.ct_id '
					. ' WHERE '.Application_Model_Pubcon::get(1110, 'ct')
					. ' AND '. Application_Model_Pubcon::get(1001, 'ts')
					. ' AND `lang`="'.LANG.'" AND `ct_id`='.$this->params['ctype']; //die($sql);
		if(! $result		= $this->DB->fetchAll($sql) ) die(Application_Model_Messages::message(404));
		$this->editor	= stripslashes($result[0]['ct_editor']);
		if(strlen($result[0]['ts_data_sc'])>1 and $result[0]['ts_data_sc']!='/0/')
			$result[0]['ts_data_sc']	= str_replace('/',',', preg_replace('/(^\/+)|(\/+$)/', '', $result[0]['ts_data_sc']) );
		if(strlen($result[0]['ts_data_ug'])>1 and $result[0]['ts_data_ug']!='/0/')
			$result[0]['ts_data_ug']	= str_replace('/',',', preg_replace('/(^\/+)|(\/+$)/', '', $result[0]['ts_data_ug']) );
		return $result[0];
	}
	protected function	arrangeSiteData()
	{
		$scdata	= $ugdata	= '';
		
		$taxoterms	= $this->getWbsTaxTerms();
		if( is_array($taxoterms) )
				foreach($taxoterms as $sc)	$scdata	.= '<li unic="'.$sc['id'].'">'.$sc['title'].'</li>';
		$this->inj_data['#rasta-rtc-data-taxoterm#'] = '<ul id="rasta_site_taxoterms">' . $scdata . '</ul>';

		$usergroups	= $this->getWbsUserGroups();
		if( is_array($usergroups) )
				foreach($usergroups as $ug)	$gudata	.= '<li unic="'.$ug['id'].'">'.$ug['title'].'</li>';
		$this->inj_data['#rasta-rtc-data-usergroup#'] = '<ul id="rasta_site_usergroups">' . $gudata . '</ul>';
	}
	protected function	getWbsTaxTerms()
	{
		if($this->ctype['ts_data_sc']=='/0/')	return false;
		$sql	= 'SELECT id, title  FROM `wbs_taxonomy_terms` WHERE '.Application_Model_Pubcon::get(1110)
				. ( (empty($this->ctype['ts_data_sc']))?'':' AND id IN ('.$this->ctype['ts_data_sc'].') ')
				. ' ORDER BY `id` DESC'; 
		$result	= $this->DB->fetchAll($sql);
		return 	$result;
	}
	protected function getWbsUserGroups()
	{
		if($this->ctype['ts_data_ug']=='/0/')	return false;
		$sql	= 'SELECT id, title  FROM `user_groups` WHERE '.Application_Model_Pubcon::get(1110)
				. ( (empty($this->ctype['ts_data_ug']))?'':' AND id IN ('.$this->ctype['ts_data_ug'].') ')
				. ' ORDER BY `id` DESC';
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	protected function	addDataToEditor()
	{
//		$sysParams	= array();
//		$paramsValue= array();
//
//		foreach($this->inj_data as $v)
//		{
//			$sysParams[]	= $v['key'];
//			$paramsValue[]	= $v['value'];
//		}
		//$this->editor	= str_replace($sysParams, $paramsValue, $this->editor);
		$this->editor	= str_replace(array_keys($this->inj_data), array_values($this->inj_data), $this->editor);
	}
	protected function	addRtcToEditor($rtc)
	{
		
		$sysParams	= array();
		$paramsValue= array();

		$illegalDates	= array('9999-12-30 12:00:00', '0000-00-00 00:00:00');
		if( in_array($rtc['publishdown'], $illegalDates) ) $rtc['publishdown'] = '';

		$sysParams[]	= '#rasta-rtc-param-unic#';
		$paramsValue[]	= $rtc['rtc_id'];
		$sysParams[]	= '#rasta-rtc-param-title1#';
		$paramsValue[]	= $rtc['titlei'];
		$sysParams[]	= '#rasta-rtc-param-title2#';
		$paramsValue[]	= $rtc['titleii'];
		$sysParams[]	= '#rasta-rtc-param-abstract#';
		$paramsValue[]	= $rtc['abstract'];
		
		$sysParams[]	= '#rasta-rtc-param-status#';
		$paramsValue[]	= $rtc['status'];
		
		$sysParams[]	= '#rasta-rtc-param-publishup#';
		$paramsValue[]	= $rtc['publishup'];
		//$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['publishup']):$rtc['publishup'];
		$sysParams[]	= '#rasta-rtc-param-publishdown#';
		$paramsValue[]	= $rtc['publishdown'];
		//$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['publishdown']):$rtc['publishdown'];
		$sysParams[]	= '#rasta-rtc-param-text#';
		$paramsValue[]	= $rtc['text'];
		
		$sysParams[]	= '#rasta-rtc-param-taxoterm#';
		$currentScens	= '';
//		if(!empty($rtc['taxoterm'])) $currentScens	= '['.implode( ',', array_filter( explode('/', $rtc['taxoterm']) ) ).']';
//		$paramsValue[]	= $currentScens;
		$paramsValue[]	= (!empty($rtc['taxoterm']))?'['.implode( ',', array_filter( explode('/', $rtc['taxoterm']) ) ).']':'[]';
		
		//$sysParams[]	= '#rasta-rtc-setting#';
		$sysParams[]	= '#rasta-rtc-param-showauthorname#';
		$paramsValue[]	= (empty($rtc['showauthorname']))?'false':'true';
//		$paramsValue[]	= ($rtc['setting'][0]=='1')?'true':'false';
		
		$sysParams[]	= '#rasta-rtc-param-showregisterdate#';
		$paramsValue[]	= (empty($rtc['showregisterdate']))?'false':'true';
//		$paramsValue[]	= ($rtc['setting'][1]=='1')?'true':'false';
		
		$sysParams[]	= '#rasta-rtc-param-showregistertime#';
		$paramsValue[]	= (empty($rtc['showregistertime']))?'false':'true';
//		$paramsValue[]	= ($rtc['setting'][2]=='1')?'true':'false';
		
		$sysParams[]	= '#rasta-rtc-param-commentsetting#';
		$paramsValue[]	= (empty($rtc['commentsetting']))?'disable':$rtc['commentsetting'];
//		$comment_modes	= array('disable', 'private', 'public');
//		$paramsValue[]	= $comment_mode[ $rtc['setting'][3] ];
		
		$sysParams[]	= '#rasta-rtc-param-showsinglepostlink#';
		$paramsValue[]	= (empty($rtc['showsinglepostlink']))?'false':'true';
//		$paramsValue[]	= ($rtc['setting'][4]=='1')?'true':'false';
		//$paramsValue[]	= $rtc['setting'];

		$sysParams[]	= '#rasta-rtc-param-usergroups#';
		$currentUsgs	= '';
		//if(!empty($rtc['usergroups'])) $currentUsgs	= '['.implode( ',', array_filter( explode('/', $rtc['usergroups']) ) ).']';
		//$paramsValue[]	= $currentUsgs;
		$paramsValue[]	= (!empty($rtc['usergroups']))?'['.implode( ',', array_filter( explode('/', $rtc['usergroups']) ) ).']':'[]';

		$sysParams[]	= '#rasta-rtc-param-keywords#';
		$paramsValue[]	= $rtc['keywords'];
		$sysParams[]	= '#rasta-rtc-param-description#';
		$paramsValue[]	= $rtc['description'];
		$sysParams[]	= '#rasta-rtc-param-author#';
		$paramsValue[]	= $rtc['author'];
		$sysParams[]	= '#rasta-rtc-param-extra-object#';
		$extra	= '{}';
		if(is_array($rtc['extra']))
		{
			$extra	= array();
			foreach($rtc['extra'] as $key=>$val)	$extra[]	= $key.': "'.$val.'"';
			$extra	= '{ '.implode(', ', $extra).' }';
		}
		$paramsValue[]	= $extra;

		$this->editor	= str_replace($sysParams, $paramsValue, $this->editor);
	}
	protected function	getEditorFreeOfKeys()
	{
		$sysParams	= array('#rasta-rtc-param-unic#'=>'','#rasta-rtc-param-title1#'=>'','#rasta-rtc-param-title2#'=>'','#rasta-rtc-param-abstract#'=>'',
							'#rasta-rtc-param-status#'=>'','#rasta-rtc-param-publishup#'=>'','#rasta-rtc-param-publishdown#'=>'','#rasta-rtc-param-text#'=>'',
							'#rasta-rtc-param-keywords#'=>'','#rasta-rtc-param-description#'=>'','#rasta-rtc-param-author#'=>'',
							'#rasta-rtc-param-showauthorname#'=>'', '#rasta-rtc-param-showregisterdate#'=>'', '#rasta-rtc-param-showregistertime#'=>'',
							'#rasta-rtc-param-commentsetting#'=>'', '#rasta-rtc-param-showsinglepostlink#'=>'', '#rasta-rtc-param-taxoterm#'=>'', 
							'#rasta-rtc-param-usergroups#'=>'', '#rasta-rtc-param-extra-object#'=>'{}'
							 );
		//$paramsValue= array('','','','','','','','','','','','','','','','', '', '', '', '', '{}');
		$this->editor	= str_replace( array_keys($sysParams), array_values($sysParams), $this->editor);
	}
	protected function	getRtc($id)
	{
		$this->view->assign('title'	, $this->translate->_('c') ); 
		$sql	= 'SELECT co.*, me.keywords, me.description AS metades, me.author, me.extra_data '
				. ' FROM wbs_rtcs AS co LEFT JOIN wbs_rtc_metadata AS me ON co.id = me.txt_id '
				. ' WHERE '.Application_Model_Pubcon::get(1001, 'co')
				. ' AND id='.addslashes($id);
		//die($sql);
		//$sql	= "SELECT * FROM `wbs_rtcs` WHERE wbs_id='".WBSiD. "' AND `id`=". addslashes($id); //. " AND `type_id`=". addslashes($this->ctype['ct_id']);
		if(!$result = $this->DB->fetchAll($sql))	return false;
		
		//$sql2	= "SELECT * FROM `wbs_rtc_metadata` WHERE wbs_id='".WBSiD."' AND `txt_id` =". addslashes($id);
		//$result2= $this->DB->fetchAll($sql);
		$this->params['ctype']	= $result[0]['type_id'];
		$this->inj_data['#rasta-rtc-registration-action-url#']	 = '/rtcmanager/register/edit/actiontype/save'.$this->setUriParams();
		if(is_array($result) && count($result)==1)	return $this->arrayRtcDbdataToParams($result[0]);
		return false;
	}
	protected function	arrayRtcDbdataToParams($result)
	{
		$data['rtc_id']		= $result['id'];
		$data['ctype']		= $result['type_id'];
		$data['titlei']		= $result['title'];
		$data['titleii']	= $result['ltn_name'];
		$data['abstract']	= htmlspecialchars( stripslashes($result['description']) );
		$data['status']		= (empty($result['is_published']))?'false':'true'; //$result['is_published'];
		$data['publishup']	= $result['publish_up'];
		$data['publishdown']= $result['publish_down'];
		$data['text']		= htmlspecialchars( stripslashes($result['content']) );
		$data['taxoterm']	= $result['taxoterms'];
		$data['setting']	= $result['setting'];

		$data['showauthorname']		= $result['setting'][0];//($result['setting'][0]=='1')?'true':'false';
		$data['showregisterdate']	= $result['setting'][1];//($result['setting'][1]=='1')?'true':'false';
		$data['showregistertime']	= $result['setting'][2];//($result['setting'][2]=='1')?'true':'false';
		$comment_modes	= array('disable', 'private', 'public');
		$data['commentsetting']		= $comment_modes[ (integer)$result['setting'][3] ];
		$data['showsinglepostlink']	= $result['setting'][4];//($result['setting'][4]=='1')?'true':'false';
		
		$data['usergroups']	= $result['user_group'];
		$data['description']= htmlspecialchars( stripslashes($result['metades']) );
		$data['keywords']	= $result['keywords'];
		$data['author']		= $result['author'];
		
		$data['extra']		= '';
		if(strlen( trim($result['extra_data']) )>4)
		{
			if( !is_object($this->_XAL) ) $this->_XAL	= new Xal_Servlet('SAFE_MODE');
			$this->_XAL->disableAll();
			$this->_XAL->enable(array('execution'));
			$result['extra_data']	= '<execution>'.$result['extra_data'].'</execution>';
			$xresult	= $this->_XAL->run($result['extra_data']);
			$this->_XAL->enableAll();
			$user_params	= array();
			if(is_array($xresult) and count($xresult)>0)
				foreach($xresult as $key=>$val) 
					if( preg_match('/^var\:/', $key) )
					{
						$key	= str_replace('var:', '', $key);
						$user_params[ $key ]	= $val;
					}
			if(count($user_params)>0)	$data['extra']	=	$user_params;
		}
		return $data;
	}
	protected function setUriParams()
	{
		$newUriParams =	'';
		if ( isset($this->params['env']) and $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$newUriParams .=	'/env/dsh';
		}
		if ( !is_numeric($this->params['ctype']) or  $this->params['ctype']<1 )
			$newUriParams .=	'/ctype/1';
		else
			$newUriParams .=	'/ctype/'.$this->params['ctype'];
		if ( is_numeric($this->params['id']) )
			$newUriParams .=	'/id/'.$this->params['id'];
		
		return $newUriParams.'#fragment-2';
	}

}

?>