<?php

class Rasta_Xml_Parser
{

	static function getArr($xmlstr)
	{
		return self::xmlstr_to_array($xmlstr);
	}
	public function xmlstr_to_array($xmlstr)
	{
		$doc = new DOMDocument();
		libxml_use_internal_errors(TRUE);
		$result	= $doc->loadXML($xmlstr);
		if(!$result)
		{
			libxml_clear_errors();
			return false;
		}
		//$doc = new DOMDocument();
		//$doc->loadXML($xmlstr);
		return ( (is_object($this))?$this->domnode_to_array($doc->documentElement):self::domnode_to_array($doc->documentElement) );
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
				$v = ( (is_object($this))?$this->domnode_to_array($child):self::domnode_to_array($child) );
				
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

