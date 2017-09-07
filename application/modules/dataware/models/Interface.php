<?php
/*
	*	
*/
class Db_Model_Interface
{

	public $output	= '';
	public function	__construct($enid)
	{
		$this->_order	= $this->getOrder($enid);
		$_actions		= $this->getActions();
		if($_actions)	$this->output	= $this->getOutput($_actions);
	}
	public function	getOutput($_actions)
	{
		foreach($_actions['action'] as $action) $output[]	= $this->doDbJob($action);
		if(count($output)>1)
		{
			for($i=1; $i<=count($output); $i++)
			{
				$injkeys[]	= '#rasta-db-interface-output-'.$i.'#';
				$injvals[]	= $output[($i-1)];
			}
			$output	= str_replace($injkeys, $injvals, $_actions['multiact']['block']);
		}
		else	$output	= $output[0];
		return $output;
	}
	public function	doDbJob($action)
	{
		$output	= '';
		$action = $this->replaceParams($action);
		$data	= $this->getDbData($action);
		if($data)	$output	= $this->replaceDbData($action, $data);
		return $output;
	}
	public function	getPagingBlock($acpg)
	{
		$paging_html	= ''; 
		$con		= $acpg[1]['count'];
		if($this->dcount <= $con) return $paging_html;
		$pn		= $acpg[0]['@attributes']['param'];
		$paging_count	= ceil($this->dcount/$con);
		$paging_num		= floor($this->params[$pn]/$con)+1;

		$uri	= preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']).'?';
		$_getv	= $_GET;
		if(isset($_getv[$pn])) unset($_getv[$pn]);
		if(!empty($_getv) and count($_getv)>0)	foreach($_getv as $k=>$v) $tps[]	= $k.'='.$v;
		$tps[]	= $pn.'=';
		$uri	.= implode('&', $tps);
		
		if($paging_num!=1) $paging_html[]	= '&nbsp;<a href="'.$uri.($paging_num-2)*$con.'">'.$acpg[0]['prevlable'].'</a>&nbsp;';
		for($i=1; $i<=$paging_count; $i++)
		{
			$paging_href	= $uri.($i-1)*$con;
			$paging_one		= ($i == $paging_num )?'&nbsp;<b>'.$i.'</b>&nbsp;':'&nbsp;<a href="'.$paging_href.'">'.$i.'</a>&nbsp;';
			$paging_html[]	= $paging_one;
		}
		if($paging_num!=$paging_count) $paging_html[]	= '&nbsp;<a href="'.$uri.($paging_num)*$con.'">'.$acpg[0]['nextlable'].'</a>&nbsp;';
		$paging_html	= @implode('', $paging_html);
		$paging_html	= str_replace('#rasta-db-interface-paging-contenc#', $paging_html, $acpg[0]['block']);
		return $paging_html;
	}
	public function	replaceDbData($acs, $data)
	{
		$output	= '';
		foreach($data as $case)
		{
			$injkeys	= $injvals	= array();
			foreach($case as $n=>$v)
			{
				$injkeys[]	= '#rasta-db-interface-cols-'.$n.'#';
				$injvals[]	= $v;
			}
			$output[]	= str_replace($injkeys, $injvals, $acs['skin']['repeatedpart']);
		}
		if(!is_array($output))	return '';

		$pg	= $acs['mode']['paging']['@attributes']['param'];
		if(!empty($pg))	$output[]	= $this->getPagingBlock(array($acs['mode']['paging'], $acs['limit']));
		$output	= implode('', $output);

		$output	= str_replace('#rasta-db-interface-contenc#', $output, $acs['skin']['fixedpart']);

		return $output;
	}
	public function	getDbData($acs)
	{
		if(empty($acs['db'])) return false;
		$db		= new Db_Model_Table($acs['db']);
		
		$state	= '';
		if(!empty($acs['where']))
			$state[]	= 'WHERE '.$acs['where'];
		if(!empty($acs['order']['field']))	
			$state[]	= 'ORDER BY `'.$acs['order']['field'].'` '.(!empty($acs['order']['type']))?$acs['order']['type']:'ASC';
		if(!empty($acs['limit']['count']))	
			$state[]	= 'LIMIT '.$acs['limit']['start'].','.$acs['limit']['count'];
		if(is_array($state))	$state	= implode(' ', $state);
		
		if(!empty($acs['mode']['paging']['@attributes']['param'])) $this->dcount	= $db->count($acs['table'], $acs['where']);
		$res	= $db->select($acs['cols'], $acs['table'], $state);
		//print_r($res); die();
		return $res;
	}
	public function	replaceParams($acs)
	{
		if(!is_array($acs['params']['i']))	return $acs;
		if(!isset($acs['params']['i'][1]))	$acs['params']['i']	= array($acs['params']['i']);
		
		foreach($acs['params']['i'] as $param)
		{
			$ns	= $param[0];
			if(empty($_GET[$ns]))	$params[$ns]	= $param['@attributes']['default'];
			else					$params[$ns]	= $_GET[$ns];
			$injkeys[]	= '#rasta-db-interface-params-'.$ns.'#';
			$injvals[]	= $params[$ns];
		}
		
		$this->params	= $params;
		$acs['where']	= str_replace($injkeys, $injvals, $acs['where']);
		$acs['order']	= str_replace($injkeys, $injvals, $acs['order']);
		$acs['limit']	= str_replace($injkeys, $injvals, $acs['limit']);
		$acs['skin']	= str_replace($injkeys, $injvals, $acs['skin']);

		return $acs;
	}
	public function	getActions()
	{
		$xmlstr	= '<?xml version="1.0" encoding="UTF-8"?><root>'.$this->_order['xml'].'</root>';
		$data	= $this->xmlstr_to_array($xmlstr);
		if(!is_array($data['action']))	return false;
		if(!isset($data['action'][1]))	$data['action']	= array($data['action']);
		return $data;
	}
	public function	getOrder($enid)
	{
		$DB 	= Zend_Registry::get('front_db');
		$sql	= 'SELECT * FROM `wbs_db_interface` WHERE `wbs_id`='.WBSiD.' AND `id_en`="'.$enid.'";';
		$res	= $DB->fetchAll($sql);
		if($res) return $res[0];
		return false;
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
			
			if($node->attributes->length)
			{
				$a = array();
				foreach($node->attributes as $attrName => $attrNode)	$a[$attrName] = (string) $attrNode->value;
				if(!is_array($output))	$output	= array($output);
				$output['@attributes'] = $a;
			}
			if(is_array($output))
				 foreach ($output as $t => $v)
					if(is_array($v) && count($v)==1 && $t!='@attributes')	$output[$t] = $v[0];
			break;
		}
	  return $output;
	}

}


/*
<multiact>
	<block><![CDATA[ <div>#rasta-db-interface-output-1#</div><div>#rasta-db-interface-output-2#</div> ]]></block>
</multiact>
<action>
	<mode>
		<paging param="st">
			<block><![CDATA[ <div>#rasta-db-interface-paging-contenc#</div> ]]></block>
			<nextlable>next</nextlable>
			<prevlable>prev</prevlable>
		</paging>
	</mode>
	<db>test</db>
	<table>table1</table>
	<cols>*</cols>
	<where></where>
	<order>
		<field></field>
		<type></type>
	</order>
	<limit>
		<start>#rasta-db-interface-params-st#</start>
		<count>2</count>
	</limit>
	<skin>
		<fixedpart><![CDATA[ #rasta-db-interface-contenc# ]]></fixedpart>
		<repeatedpart><![CDATA[ <div>#rasta-db-interface-cols-col1# :::::::::::::: #rasta-db-interface-cols-col2#</div> ]]></repeatedpart>
	</skin>
	<params>
		<i default="0">st</i>
	</params>
</action>
<action>
	......
</action>
#rasta-db-interface-params-..[param_name]..#
#rasta-db-interface-cols-..[col_name]..#
#rasta-db-interface-output-..[index]..#
#rasta-db-interface-contenc#
#rasta-db-interface-paging-contenc#
*/
?>