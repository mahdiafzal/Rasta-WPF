<?php
/*
	*	
*/
class Db_Model_Api
{

	public function	__construct($xml=NULL)
	{
		$this->process	= false;
		if(!empty($xml)) $this->process	= $this->run($xml);
	}
	public function	run($xml)
	{
		$this->process	= false;
		$_actions		= $this->getActions($xml);
		
		if($_actions)
			foreach($_actions['action'] as $action) $output[]	= $this->doDbJob($action);
		foreach($output as $ot) if(!$ot) return false;
		return true;
	}
	public function	getActions($xml)
	{
		$xmlstr	= '<?xml version="1.0" encoding="UTF-8"?><root>'.$xml.'</root>';
		$data	= $this->xmlstr_to_array($xmlstr);
		if(!is_array($data['action']))	return false;
		if(!isset($data['action'][1]))	$data['action']	= array($data['action']);
		return $data;
	}
	public function	doDbJob($acs)
	{
		if(empty($acs['table'])) return false;
		$db		= new Db_Model_Table($acs['table']);
		
		$mode	= $acs['mode']['insert'];
		if(!empty($mode) and $mode=='true')			$ret[]	= $db->insert($acs['vals']);

		$mode	= $acs['mode']['update'];
		if(is_array($mode) and $mode[0]=='true')	$ret[]	= $db->update($acs['vals'], $mode['@attributes']['where']);
		
		$mode	= $acs['mode']['delete'];
		if(is_array($mode) and $mode[0]=='true')	$db->delete($mode['@attributes']['where']);
		
		if(!is_array($ret))	return true;
		foreach($ret as $ot) if(!$ot) return false;
		return true;
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
<action>
	<mode>
		<insert>true</insert>
		<update where="id=4">false</update>
		<delete where="id=3">true</delete>
	</mode>
	<table>test</table>
	<vals>
		<col1>abc</col1>
		<col2>dfg</col2>
	</vals>
</action>
<action>
	......
</action>
*/

?>