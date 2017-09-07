<?php
/*
	*	
*/
class Rtcmanager_Model_Api
{
	public function	__construct()
	{
		$this->DB 		= Zend_Registry::get('front_db');
	}
	public function	run($data, $params)
	{
		$this->process	= false;

		$xmlstr	= '<?xml version="1.0" encoding="UTF-8"?><root>'.$data['skin'].'</root>';
		$Data	= $this->xmlstr_to_array($xmlstr);
		if(!is_array($Data['action']))	return false;
		
		if(!isset($Data['action'][1]))	$this->doRtcJob($Data['action']);
		else
			foreach($Data['action'] as $action) $this->doRtcJob($action);
	}
	public function	doRtcJob($action)
	{
		$this->process	= false;
		$this->action	= $action;
		$regmode	= $action['regmode'];
		if(!empty($regmode) and $regmode=='append')			$this->appendToRTC();
		elseif(!empty($regmode) and $regmode=='prepend')	$this->prependToRTC();
		else
		{
			$data	= $this->prepareRegistration();
			if(!empty($params['edit']) and $params['edit']=='yes' and is_numeric($params['rtcid']) )
				$this->updateRTC($data[0], $data[1], $params['rtcid']);
			else
				$this->insertNewRTC($data[0], $data[1]);
		}

	}
	public function	filterData()
	{
		$data	= $this->action;
		
		$setting	= '';
		$st	= $data['setting']['author'];
		$setting	.= (empty($st) or ($st!='1' and $st!='on') )?'0':'1';
		$st	= $data['setting']['date'];
		$setting	.= (empty($st) or ($st!='1' and $st!='on') )?'0':'1';
		$st	= $data['setting']['time'];
		$setting	.= (empty($st) or ($st!='1' and $st!='on') )?'0':'1';
		$st	= $data['setting']['comment'];
		$setting	.= (empty($st) or ($st!='1' and $st!='2') )?'0':$st;
		$st	= $data['setting']['singlepostlink'];
		$setting	.= (empty($st) or ($st!='1' and $st!='on') )?'0':'1';
		$data['setting']['str']	= $setting;

		$st	= $data['fields']['status'];
		$data['fields']['status']	= (empty($st) or ($st!='1' and $st!='on') )?'0':'1';

		$pup	= $data['fields']['publishup'];
		$publish_up	= '';
		if($pup=='now') $publish_up	= new Zend_DB_expr('now()');
		elseif(preg_match('/^\d\d\d\d\-\d\d\-\d\d\s\d\d\:\d\d\:\d\d$/', $pup))
		{
			preg_match('/^\d\d\d\d/', $pup, $year);
			if($year[0]>2000)	$publish_up	= $pup;
			else				$publish_up	= $this->fa_to_ger($pup);
		}
		elseif(preg_match('/^now\+\d+$/', $pup))
		{
			preg_match('/\d+$/', $pup, $days);
			$publish_up	= new Zend_DB_expr('DATE_ADD(NOW(),INTERVAL '.$days[0].' DAY)');
		}
		$data['fields']['publishup']	= $publish_up;

		$pup	= $data['fields']['publishdown'];
		$publish_down	= '';
		if($pup=='never') $publish_down	= '9999-12-30 12:00:00';
		elseif(preg_match('/^\d\d\d\d\-\d\d\-\d\d\s\d\d\:\d\d\:\d\d$/', $pup))
		{
			preg_match('/^\d\d\d\d/', $pup, $year);
			if($year[0]>2000)	$publish_down	= $pup;
			else				$publish_down	= $this->fa_to_ger($pup);
		}
		elseif(preg_match('/^now\+\d+$/', $pup))
		{
			preg_match('/\d+$/', $pup, $days);
			$publish_down	= new Zend_DB_expr('DATE_ADD(NOW(),INTERVAL '.$days[0].' DAY)');
		}
		$data['fields']['publishdown']	= $publish_down;
		
		return 	$data;
	}
	public function appendToRTC()
	{
		$htmlstr	= $this->getRtcAsXml();
		if(empty($htmlstr))	return false;

		$doc = new DOMDocument();
		$doc->loadHTML($htmlstr);

		$xpath = new DOMXPath($doc);
		$query = '//*[@parentnode="true"]';
		$entries = $xpath->query($query)->item(0);
		if(empty($entries)) return false;

		$f = $doc->createTextNode('#rasta-temporary-injectionkey-i#');
		$entries->appendChild($f);
		
		$fhtml	= $doc->saveHTML();

		$fhtml	= preg_replace('/(\s*\<\/?)(meta|\!DOCTYPE|html|body|head)([^\>]*\/?\>\s*)/', '', $fhtml);
		
		$content	= $this->action['fields']['text'];
		$fhtml	= str_replace('#rasta-temporary-injectionkey-i#', "\n".$content."\n", $fhtml);

		$unic	= $this->action['unic'];
		$this->updateRTC(array('content'=>$fhtml), array(), addslashes($unic));
	}
	public function prependToRTC()
	{
		$htmlstr	= $this->getRtcAsXml();
		if(empty($htmlstr))	return false;

		$doc = new DOMDocument();
		$doc->loadHTML($htmlstr);

		$xpath = new DOMXPath($doc);
		$query = '//*[@parentnode="true"]';
		$entries = $xpath->query($query)->item(0);
		if(empty($entries)) return false;

		$f = $doc->createTextNode('#rasta-temporary-injectionkey-i#');
		$entries->insertBefore($f, $entries->firstChild);
		
		$fhtml	= $doc->saveHTML();

		$fhtml	= preg_replace('/(\s*\<\/?)(meta|\!DOCTYPE|html|body|head)([^\>]*\/?\>\s*)/', '', $fhtml);
		
		$content= $this->action['fields']['text'];
		$fhtml	= str_replace('#rasta-temporary-injectionkey-i#', "\n".$content."\n", $fhtml);

		$unic	= $this->action['unic'];
		$this->updateRTC(array('content'=>$fhtml), array(), addslashes($unic));
	}
	public function getRtcAsXml()
	{
		$unic	= $this->action['unic'];
		$sql		= "SELECT `content` FROM `wbs_rtcs` WHERE `wbs_id`='".WBSiD."' AND `id`=".addslashes($unic);
		$result		= $this->DB->fetchOne($sql);
		if(empty($result))	return false;
		return '<meta http-equiv="Content-Type" content="text/html; charset=utf8" />'.stripslashes($result);
	}
	public function updateRTC($data1, $data2, $rtcID)
	{
		try
		{
			$this->DB->beginTransaction();
			$this->DB->update('wbs_rtcs',$data1 ,'`wbs_id` = '.WBSiD.' and id ='.$rtcID);
			//add metadata
			if(!empty($data2['description'])or !empty($data2['author']) or !empty($data2['keywords']))
			{
				$rr=$this->DB->fetchAll('select `txt_id` from wbs_rtc_metadata where `wbs_id` = '.WBSiD.' and `txt_id`='.$rtcID);
				if (count($rr)==1)
				{
					$this->DB->update('wbs_rtc_metadata',$data2 ,'`wbs_id` = '.WBSiD.' and `txt_id` ='.$rtcID);
				}
				else
				{
					$data3	= array_merge(array('txt_id'=> $rtcID), $data2 );
					$this->DB->insert('wbs_rtc_metadata', $data3 );
				}
			}
			//end of add metadata
			$this->DB->commit();
			$this->process	= true;
		}
		catch(Zend_exception $e)
		{
			$this->DB->rollBack();
			$this->process	= false;
		}
	}
	public function insertNewRTC($data1, $data2)
	{
		$data1['crt_date']		= new Zend_DB_expr('now()');
		if(empty($data1['publish_up']))		$data1['publish_up']	= $data1['crt_date'];
		if(empty($data1['publish_down']))	$data1['publish_down']	= '9999-12-30 12:00:00';
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
			$this->process	= true;
		}
		catch(Zend_exception $e)
		{
			$this->DB->rollBack();
			$this->process	= false;
			//echo $e->getMessage();
		}
	}

	public function prepareRegistration()
	{
		$rowData= $this->filterData();
		$scens	= $this->getSenarios();	
		$ugroup	= $this->getUserGroups();

//		$this->validate();

		$data1['wbs_id']		= WBSiD;
		
		$this->ses 	= new Zend_Session_Namespace('MyApp');
		if(!empty($this->ses->id))	$data1['user_id']	= $this->ses->id;
		
		$data1['ltn_name']		= $rowData['fields']['title']['ii'];
		$data1['title']			= $rowData['fields']['title']['i'];
		$data1['is_published']	= $rowData['fields']['status'];
		$data1['publish_up']	= $rowData['fields']['publishup'];
		$data1['publish_down']	= $rowData['fields']['publishdown'];
		$data1['description']	= $rowData['fields']['abstract'];
		$data1['content']		= $rowData['fields']['text'];
		$data1['taxoterms']		= $scens;
		$data1['setting']		= $rowData['setting']['str'];
		$data1['user_group']	= $ugroup;

		$data2['wbs_id']		= WBSiD;
		$data2['description']	= $rowData['fields']['description'];
		$data2['author']		= $rowData['fields']['author'];
		$data2['keywords']		= $rowData['fields']['keywords'];

		$data1	= array_filter($data1);
		$data2	= array_filter($data2);

		return array($data1, $data2);
	}
	public function getSenarios()
	{
		$scentitles	= $this->action['scenario']['i'];
		if(empty($scentitles))	return NULL;
		if(count($scentitles)==0) return NULL;
		
		foreach($scentitles as $stitle) $wst[]	= '`title` LIKE "'.$stitle.'"';
		$wst	= ' AND ('.implode(' OR ',$wst).')';
		$sql		= "SELECT `id` FROM `wbs_scenario` WHERE `wbs_id`='".WBSiD."'".$wst;
		$result		= $this->DB->fetchAll($sql);
		if(empty($result))	return NULL;
		foreach($result as $res) $scenars[]	= $res['id'];
		sort($scenars);
		$scenars		= '/'. implode('/', $scenars).'/';
		return $scenars;
	}
	public function getUserGroups()
	{
		$usergroups	= $this->action['usergroups']['i'];
		if(empty($usergroups))	return '0';
		if(!is_array($usergroups))	$usergroups	= array($usergroups);
		if(count($usergroups)==0) return '0';
		
		foreach($usergroups as $utitle) $wst[]	= '`title` LIKE "'.$utitle.'"';
		$wst	= ' AND ('.implode(' OR ',$wst).')';
		$sql		= "SELECT `id` FROM `user_groups` WHERE `wbs_id`='".WBSiD."'".$wst;
		$result		= $this->DB->fetchAll($sql);
		if(empty($result))	return '0';
		foreach($result as $res) $ugroup[]	= $res['id'];
		sort($ugroup);
		$ugroup		= '/'. implode('/', $ugroup).'/';
		return $ugroup;
	}

	public function fa_to_ger($date)
	{
		if ($date=='')return NULL;
		$arr	= explode(' ',$date)	;
		$d		= explode('-',$arr[0])	;
		$pdate	= new Rasta_Pdate;
		$arr[0] = implode('-',$pdate->persian_to_gregorian($d[0],$d[1],$d[2]));
		return    implode(' ',$arr);
	}
		
	protected function xmlstr_to_array($xmlstr) {
	  $doc = new DOMDocument();
	  $doc->loadXML($xmlstr);
	  return $this->domnode_to_array($doc->documentElement);
	}
	protected function domnode_to_array($node)
	{
		$output = '';//array();
		switch ($node->nodeType)
		{
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:	$output = trim($node->textContent); break;
			case XML_ELEMENT_NODE:
			for ($i=0, $m=$node->childNodes->length; $i<$m; $i++)
			{
				$child = $node->childNodes->item($i);
				$v = $this->domnode_to_array($child);
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					if(!isset($output[$t]))		$output[$t] = array();
					$output[$t][] = $v;
				}
				else
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
			}
			
			if(is_array($output))
			{
				 if($node->attributes->length)
				 {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode)	$a[$attrName] = (string) $attrNode->value;
					$output['@attributes'] = $a;
				 }
				 foreach ($output as $t => $v)
					if(is_array($v) && count($v)==1 && $t!='@attributes')	$output[$t] = $v[0];
			}
			break;
		}
	  
	  return $output;
	}

}


/*

<action>
	*<unic>new/rtc_id</unic>
	*<regmode>append/prepend/null</regmode>
	<scenario>
		<i>scen1</i>
		<i>scen2</i>
	</scenario>
	<setting>
		<author>0/1</author>
		<date>0/1</date>
		<time>0/1</time>
		<comment>0/1/2</comment>
		<singlepostlink>0/1</singlepostlink>
	</setting>
	<usergroups>
		<i>ugrou1</i>
		<i>ugrou2</i>
	</usergroups>
	<fields>
		<title>
			<i>#rasta-dandelion-title#</i>
			<ii></ii>
		</title>
		<status>0/1</status>
		<publishup>2011-06-01 00:00:00/now/now+n</publishup>
		<publishdown>2011-06-01 00:00:00/never/now+n</publishdown>
	
		<keywords></keywords>
		<description></description>
		<author></author>
		
		<abstract></abstract>
		<text><![CDATA[
	<div>
	<strong><font item="title" sel="*">#rasta-dandelion-title#</font></strong><br />
	<span style="font-family: Tahoma;"><font item="f1" sel="*">#rasta-dandelion-f1#</font></span><br />
	<span style="font-family: Tahoma;"><font item="f2" sel="*">#rasta-dandelion-f2#</font>   <font item="f3" sel="a[@href]"><a href="#rasta-dandelion-f3#">..</a></font></span>
	</div>
		]]></text>
	</fields>
</action>
<action>
	......
</action>


*/
?>