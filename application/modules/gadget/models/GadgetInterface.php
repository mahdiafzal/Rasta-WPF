<?php
/*
	*	
*/
class Gadget_Model_GadgetInterface extends Application_Model_Page_Free
{

	public function	__construct($data)
	{
		if(!is_numeric($data['gad_id']))		die(Application_Model_Messages::message(404));
		$this->gad_id		= $data['gad_id'];
		$this->rtc_id		= (is_numeric($_GET['rtc']))?$_GET['rtc']:false;
		
		if(!$this->gadget	= $this->getGadget())	die(Application_Model_Messages::message(404));
		
		$data[0]	= (!empty($this->gadget['page_id']))?$this->gadget['page_id']:'12';
		
		$alertmsg	= '';
		if(isset($data['alertmsg']))
		{
			$alertmsg	= $data['alertmsg'];
			unset($data['alertmsg']);
		}
		$this->addAlertMsgToGadText($alertmsg);
		
		if(isset($data['rtcParams']))
		{
			$data['rtcParams']['rtc_id']	= ($this->rtc_id)?$this->rtc_id:'';
			$this->rtcdata	=	$data['rtcParams'];
			unset($data['rtcParams']);
		}
		
		parent::__construct($data);
		$this->replacePageXml('');
		
		$this->ContentIds	= $this->getContentIds();
		$this->segments		= $this->getPageSegments();
		$this->HeaderMenu	= $this->getHeaderMenu(array('href'));
		$this->page['wb_page_title']	= $this->gadget['title'];
		
		if(isset($_GET['rtc']) and $_GET['rtc']=='list')
			$this->segments[2][1]	= $this->setGadgetAsList();
		else
			$this->segments[2][1]	= $this->setGadgetAsForm();

	}
/// Main Methods	
	public function	setGadgetAsList()
	{
		if(empty($this->gadget['list_skin'])) return 'SKIN ERROR';
		$skin	= Rasta_Xml_Parser::getArr($this->gadget['list_skin']);
		if(empty($skin['nocontentmsg']) or empty($skin['repeatedpart']) or empty($skin['fixedpart']) )	return 'SKIN ERROR';
		
		if(! $result	= $this->getSiteRtcList() )
			return $this->getBlockedContent('2', 'rtc', array('text'=>@$skin['nocontentmsg'], 'title'=>$this->gadget['title'], 'unic'=>$this->gadget['id']));
		
		$list	= '';
		$j		= (isset($_GET['st']) and is_numeric($_GET['st']))?($_GET['st']+1):1;
		foreach($result as $rtc)
		{
			$repeatedpart	= str_replace('#rasta-list-id#', $j, $skin['repeatedpart']);
			$list	.= $this->addRtcDataToList($repeatedpart, $rtc);
			$j++;
		}
		$list	= str_replace('#rasta-gadgetlist-contenc#', $list, $skin['fixedpart']);
		return $this->getBlockedContent('2', 'rtc', array('text'=>$list, 'title'=>$this->gadget['title'], 'unic'=>$this->gadget['id']));
	}
	public function	setGadgetAsForm()
	{
		if(is_array($this->rtcdata))
		{
			$this->gadget['text']	= $this->addRtcToText($this->gadget['text'], $this->rtcdata);
		}
		else
		{	
			if($this->rtc_id)
				if($this->rtcdata	= $this->getRtc())
				{
					$this->setPostMetadata();
					$this->gadget['text']	= $this->addRtcToText($this->gadget['text'], $this->rtcdata);
				}
				else
					die(Application_Model_Messages::message(404));
			else
				$this->gadget['text']	= $this->getTextFreeOfRtcKeys($this->gadget['text']);
		}
		
		$gdata	= array();
		if($this->gadget['scen_data']=='1')		$gdata['sc']	= $this->getSiteScenarios();
		if($this->gadget['ugroup_data']=='1')	$gdata['ug']	= $this->getWbsUserGroups();
		if(count($gdata)>0)	$this->gadget['text']	.= $this->arrangeGadData($gdata);
		return $this->getBlockedContent('2', 'rtc', array('text'=>$this->gadget['text'], 'title'=>$this->gadget['title'], 'unic'=>$this->gadget['id']));
	}
/// List Methods	
	public function	getSiteRtcList()
	{
		$start	= (isset($_GET['st']) and is_numeric($_GET['st']))?$_GET['st']:0;
		$limit	= 25;
		
		$sql	= 'SELECT `id`,`ltn_name`,`title`,`description`,`crt_date`,`upt_date`,`is_published`,`publish_up`,`publish_down` FROM `wbs_rtcs`'
				. ' WHERE `wbs_id` = '. WBSiD .' AND `gad_id`='.$this->gad_id.'  ORDER BY `id` DESC LIMIT '.$start.','.$limit;
		if(! $result	= $this->DB->fetchAll($sql) ) return false;
		
		$count	= $this->DB->fetchAll('SELECT COUNT(id) AS `cnt` FROM `wbs_rtcs` WHERE `wbs_id` = '.WBSiD.' AND `gad_id`='.$this->gad_id);
		
		$illegalDates	= array('9999-12-30 12:00:00', '0000-00-00 00:00:00');
		for($i=0; $i<count($result); $i++)	if( in_array($result[$i]['publish_down'], $illegalDates) ) $result[$i]['publish_down'] = ''; 
		return $result;
	}
	public function	addRtcDataToList($input, $rtc)
	{
		$sysParams	= array();
		$paramsValue= array();
		
		$illegalDates	= array('9999-12-30 12:00:00', '0000-00-00 00:00:00');
		if( in_array($rtc['publish_down'], $illegalDates) ) $rtc['publish_down'] = '';

		$sysParams[]	= '#rasta-rtc-unic#';
		$paramsValue[]	= $rtc['id'];
		$sysParams[]	= '#rasta-rtc-titlei#';
		$paramsValue[]	= $rtc['title'];
		$sysParams[]	= '#rasta-rtc-titleii#';
		$paramsValue[]	= $rtc['ltn_name'];
		$sysParams[]	= '#rasta-rtc-abstract#';
		$paramsValue[]	= $rtc['description'];

		$sysParams[]	= '#rasta-rtc-creationdate#';
		$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['crt_date']):$rtc['crt_date'];
		$sysParams[]	= '#rasta-rtc-lastupdate#';
		$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['upt_date']):$rtc['upt_date'];

		$sysParams[]	= '#rasta-rtc-status#';
		$paramsValue[]	= $rtc['is_published'];
		$sysParams[]	= '#rasta-rtc-publishup#';
		$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['publish_up']):$rtc['publish_up'];
		$sysParams[]	= '#rasta-rtc-publishdown#';
		$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['publish_down']):$rtc['publish_down'];

		$output	= str_replace($sysParams, $paramsValue, $input);
		return $output;
	}
/// Form Methods	
	public function	arrangeGadData($gdata)
	{
		$data	= '';
		if(!empty($gdata['sc']))
		{
			$data	.= '<ul id="rasta_scenarios">';
			foreach($gdata['sc'] as $sc)	$data	.= '<li unic="'.$sc['id'].'">'.$sc['title'].'</li>';
			$data	.= '</ul>';
		}
		if(!empty($gdata['ug']))
		{
			$data	.= '<ul id="rasta_usergroups">';
			foreach($gdata['ug'] as $ug)	$data	.= '<li unic="'.$ug['id'].'">'.$ug['title'].'</li>';
			$data	.= '</ul>';
		}
		if(empty($data))	return $data;
		$data	= '<div id="rasta_gadget_data" style="display:none">'.$data.'</div>';
		return $data;
	}
	public function	getSiteScenarios()
	{
		$sql	= "SELECT id, title  FROM `wbs_scenario` WHERE `wbs_id`='".WBSiD."' ORDER BY `id` DESC" ;
		$result	= $this->DB->fetchAll($sql);
		return 	$result;
	}
	public function getWbsUserGroups()
	{
		$sql		= "SELECT id, title FROM `user_groups` WHERE `wbs_id`='".WBSiD."' ORDER BY `id` DESC";
		$result		= $this->DB->fetchAll($sql);
		return $result;
	}
	public function	getTextFreeOfRtcKeys($input)
	{
		$sysParams	= array('#rasta-rtc-unic#','#rasta-rtc-titlei#','#rasta-rtc-titleii#','#rasta-rtc-abstract#',
							'#rasta-rtc-status#','#rasta-rtc-publishup#','#rasta-rtc-publishdown#','#rasta-rtc-text#','#rasta-rtc-scenario#',
							'#rasta-rtc-setting#','#rasta-rtc-usergroups#','#rasta-rtc-keywords#','#rasta-rtc-description#','#rasta-rtc-author#');
		$paramsValue= array('','','','','','','','','','','','','','','','');
		$output	= str_replace($sysParams, $paramsValue, $input);
		return $output;
	}
	public function	addRtcToText($input, $rtc)
	{
		$sysParams	= array();
		$paramsValue= array();

		$illegalDates	= array('9999-12-30 12:00:00', '0000-00-00 00:00:00');
		if( in_array($rtc['publishdown'], $illegalDates) ) $rtc['publishdown'] = '';

		$sysParams[]	= '#rasta-rtc-unic#';
		$paramsValue[]	= $rtc['rtc_id'];
		$sysParams[]	= '#rasta-rtc-titlei#';
		$paramsValue[]	= $rtc['titlei'];
		$sysParams[]	= '#rasta-rtc-titleii#';
		$paramsValue[]	= $rtc['titleii'];
		$sysParams[]	= '#rasta-rtc-abstract#';
		$paramsValue[]	= $rtc['abstract'];
		
		$sysParams[]	= '#rasta-rtc-status#';
		$paramsValue[]	= $rtc['status'];
		$sysParams[]	= '#rasta-rtc-publishup#';
		$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['publishup']):$rtc['publishup'];
		$sysParams[]	= '#rasta-rtc-publishdown#';
		$paramsValue[]	= ($this->gadget['lang']=='fa')?$this->ger_to_fa($rtc['publishdown']):$rtc['publishdown'];
		$sysParams[]	= '#rasta-rtc-text#';
		$paramsValue[]	= $rtc['text'];
		$sysParams[]	= '#rasta-rtc-scenario#';
		$paramsValue[]	= $rtc['scenario'];
		$sysParams[]	= '#rasta-rtc-setting#';
		$paramsValue[]	= $rtc['setting'];
		$sysParams[]	= '#rasta-rtc-usergroups#';
		$paramsValue[]	= $rtc['usergroups'];

		$sysParams[]	= '#rasta-rtc-keywords#';
		$paramsValue[]	= $rtc['keywords'];
		$sysParams[]	= '#rasta-rtc-description#';
		$paramsValue[]	= $rtc['description'];
		$sysParams[]	= '#rasta-rtc-author#';
		$paramsValue[]	= $rtc['author'];

		$output	= str_replace($sysParams, $paramsValue, $input);
		return $output;
	}
	public function	getRtc()
	{
		$sql	= "SELECT * FROM `wbs_rtcs` WHERE wbs_id='".WBSiD. "' AND `id`=". addslashes($this->rtc_id). " AND `gad_id`=". addslashes($this->gad_id);
		$result	= $this->DB->fetchAll($sql);
		if(is_array($result) && count($result)==1)	return $this->arrayRtcDbdataToParams($result[0]);
		return false;
	}
	public function	arrayRtcDbdataToParams($result)
	{
		$data['rtc_id']		= $result['id'];
		$data['titlei']		= $result['title'];
		$data['titleii']	= $result['ltn_name'];
		$data['abstract']	= $result['description'];
		$data['status']		= $result['is_published'];
		$data['publishup']	= $result['publish_up'];
		$data['publishdown']= $result['publish_down'];
		$data['text']		= $result['content'];
		$data['scenario']	= $result['scenarios'];
		$data['setting']	= $result['setting'];
		$data['usergroups']	= $result['user_group'];
		$data['description']= '';
		$data['keywords']	= '';
		$data['author']		= '';
		return $data;
	}
	public function	setPostMetadata()
	{
		$sql	= "SELECT * FROM `wbs_rtc_metadata` WHERE wbs_id='".WBSiD."' AND `txt_id` =". addslashes($this->rtc_id);
		$result	= $this->DB->fetchAll($sql);
		if(empty($result) or count($result)!=1) return false;
		$this->rtcdata['description']	= $result[0]['description'];
		$this->rtcdata['keywords']		= $result[0]['keywords'];
		$this->rtcdata['author']		= $result[0]['author'];
		return true;
	}
/// General Methods	
	public function	getGadget()
	{
		if(empty($this->gad_id)) return false;
		$DB		= (is_object($this->DB))?$this->DB:Zend_Registry::get('front_db');
		$sql	= 'SELECT * FROM `wbs_gadget` LEFT JOIN `wbs_gadget_options` ON `wbs_gadget`.`id`=`wbs_gadget_options`.`gad_id`'
				. ' WHERE (`wbs_gadget`.`wbs_id`=0 OR `wbs_gadget`.`wbs_id`='.WBSiD.') AND `wbs_gadget`.`id`='.addslashes($this->gad_id);
		$result	= $DB->fetchAll($sql);
		if(is_array($result) && count($result)==1)	return $result[0];
		return false;
	}
	public function	addAlertMsgToGadText($almsg)
	{
		$txt	= '';
		if(is_array($almsg))
		{
			$txt	.= '<div id="alert_msgs">';
			foreach($almsg as $msg)	$txt	.= '<div class="alert_msg">'.$msg.'</div>';
			$txt	.= '</div>';
		}
		$this->gadget['text']	= str_replace('#rasta-gadget-alertmsg#', $txt, $this->gadget['text']);
	}
	public function ger_to_fa($date)
	{
		if ($date=='')	return	 NULL;
		$arr	= explode(' ',$date)	;
		$d		= explode('-',$arr[0])	;
		$pdate	= new Rasta_Pdate;
		$arr[0] = implode('-',$pdate->gregorian_to_persian($d[0],$d[1],$d[2]));
		return  implode(' ',$arr);
	}
}

/*	
	<skin>
		<paging param="st">
				<block><![CDATA[ <div>#rasta-gadgetlist-paging-contenc#</div> ]]></block>
				<nextlable>next</nextlable>
				<prevlable>prev</prevlable>
		</paging>
		<fixedpart><![CDATA[ #rasta-gadgetlist-contenc# ]]></fixedpart>
		<repeatedpart><![CDATA[ <div>#rasta-rtc-unic# ...</div> ]]></repeatedpart>
		<nocontentmsg></nocontentmsg>
	</skin>
*/
?>