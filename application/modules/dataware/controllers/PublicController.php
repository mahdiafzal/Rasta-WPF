<?php
class Dataware_PublicController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	}
	public function indexAction()
	{ 
		
$input['var:scenario']['content'][]	= array('id'=>1, 'title'=>'عنوان1', 'abstract'=>'خلاصه اخبار 1', 'txet'=>'خیلی خبر' );
$input['var:scenario']['content'][]	= array('id'=>2, 'title'=>'عنوان2', 'abstract'=>'خلاصه اخبار 2', 'txet'=>'خیلی خبر' );
$input['var:scenario']['content'][]	= array('id'=>3, 'title'=>'عنوان3', 'abstract'=>'خلاصه اخبار 3', 'txet'=>'خیلی خبر' );
		
		$xml	= '
<execution>
<salam>hhhhhhhhhhhhhhhhhhhh</salam>
<var:a>
	<tree>
		<item:v>ggggggg</item:v>
		<item:t>
			<tree>
				<item:r>ggggggg</item:r>
				<item:j>
					<tree>
						<item:r>ggggggg</item:r>
						<item:j>ggggggg</item:j>
						<item:p>ggggggg</item:p>
					</tree>
				</item:j>
				<item:p>ggggggg</item:p>
			</tree>
		</item:t>
		<item:h>ggggggg</item:h>
	</tree>
</var:a>
<unset>a.t.j.</unset>


</execution>
		';
		$xml	= '
<execution><var:news>/files/logo1.png </var:news></execution>
		';
//		$doc = new DOMDocument();
//		$doc->loadXML($xml);
//		print_r( $doc->documentElement->childNodes->item(1)->tagName );
//		die();

		$axml	= new Xal_Servlet();
		$axml->set_sqlite_root( realpath(APPLICATION_PATH .'/../data/db').'/'.WBSiD.'/' );
		//$axml->disable(array('print'));
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$axml->set_env(array('ENV_HOST_ID'=> WBSiD));
		//$salam	= function($f, $p){ print_r($p); die($f); };
		//$axml->set_xal_tag('salam', $salam);
		
		
		$wf	= '$wf	= new Workflow_Model_Workflow; return $wf->run($fn_argus);';
		$axml->set_xal_tag('workflow', $wf);
		
		$result	= $axml->run($xml, $input);
		print_r($result);
		
		die();
		
	}
	
	
	
	
	

	protected function methodCalling($call, $prevars)
	{
		if( preg_match('^system\.fn\./', $call['method']) )
		{
			if( isset($this->systemObject['fn'][$call['method']]) )
			{
				$this->systemObject['fn'][$call['method']]($call, $prevars);
				return true;
			}
		
		}
		if( preg_match('^this\.fn\./', $call['method']) )
		{
			$method	= str_replace('this.fn.', '', $call['method']);
			if( isset($this->customObject['methods'][ 'fn:'.$method ]) )	$this->run($this->customObject['methods'][ 'fn:'.$method ], $call, $prevars);
			return true;
		}

	}


	protected function runa($method, $call, $prevars)
	{
		foreach($call['arguments'] as $name=>$value)
			if( is_array($value) )
			{
				$variables[$name]	= $prevars[ 'var:'.$value['variable'] ];
			}
			elseif( is_string($value) )
			{
				$variables[$name]	= $value;
			}
		


	}


	protected function xmlstr_to_array($xmlstr)
	{
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
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					switch ($t)
					{
						//case "arguments": $v = $this->arguments_to_array($child); break;
						case "execution": $v = $this->execution_to_array($child); break;
						//case "call": $v = $this->call_to_array($child); break;
						
						default: 
						/*if(preg_match('/^var\:/', $t) ) $v = $this->var_to_array($child);
						else*/							$v = $this->domnode_to_array($child);
					
					}
					//$v = $this->domnode_to_array($child);
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();
					//die($t.'sss');
					//$output[$i][$t] = $v;
					$output[$t][] = $v;
				}
				else
				{
				
					$v = $this->domnode_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
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
	protected function arguments_to_array($node)
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
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					switch ($t)
					{
						case "arguments": $v = $this->arguments_to_array($child); break;
						case "execution": $v = $this->execution_to_array($child); break;
						case "call": $v = $this->call_to_array($child); break;
						
						default: 
						if(preg_match('/^var\:/', $t) ) $v = $this->var_to_array($child);
						else							$v = $this->domnode_to_array($child);
					
					}
					//$v = $this->domnode_to_array($child);
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();
					//die($t.'sss');
					//$output[$i][$t] = $v;
					$output[$t][] = $v;
				}
				else
				{
				
					$v = $this->domnode_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
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
	protected function var_to_array($node)
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
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					switch ($t)
					{
						case "arguments": $v = $this->arguments_to_array($child); break;
						case "execution": $v = $this->execution_to_array($child); break;
						case "call": $v = $this->call_to_array($child); break;
						
						default: 
						if(preg_match('/^var\:/', $t) ) $v = $this->var_to_array($child);
						else							$v = $this->domnode_to_array($child);
					
					}
					//$v = $this->domnode_to_array($child);
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();
					//die($t.'sss');
					//$output[$i][$t] = $v;
					$output[$t][] = $v;
				}
				else
				{
				
					$v = $this->domnode_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
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
	protected function execution_to_array($node)
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
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					switch ($t)
					{
						//case "arguments": $v = $this->arguments_to_array($child); break;
						case "execution": $v = $this->execution_to_array($child); break;
						//case "call": $v = $this->call_to_array($child); break;
						
						default: 
						/*if(preg_match('/^var\:/', $t) ) $v = $this->var_to_array($child);
						else*/							$v = $this->domnode_to_array($child);
					
					}
					//$v = $this->domnode_to_array($child);
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();
					//die($t.'sss');
					$output[$i][$t] = $v;
					//$output[$t][] = $v;
				}
				else
				{
				
					$v = $this->domnode_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
			}
			
			if($node->attributes->length)
			{
				$a = array();
				foreach($node->attributes as $attrName => $attrNode)	$a[$attrName] = (string) $attrNode->value;
				if(!is_array($output))	$output	= array($output);
				$output['@attributes'] = $a;
			}
//			if(is_array($output))
//				 foreach ($output as $t => $v)
//					if(is_array($v) && count($v)==1 && $t!='@attributes')	$output[$t] = $v[0];
			break;
		}
	  return $output;
	}
	protected function call_to_array($node)
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
				
				if(isset($child->tagName))
				{
					$t = $child->tagName;
					switch ($t)
					{
						case "arguments": $v = $this->arguments_to_array($child); break;
						case "execution": $v = $this->execution_to_array($child); break;
						case "call": $v = $this->call_to_array($child); break;
						
						default: 
						if(preg_match('/^var\:/', $t) ) $v = $this->var_to_array($child);
						else							$v = $this->domnode_to_array($child);
					
					}
					//$v = $this->domnode_to_array($child);
					//$output[]	= array();
					//if(!isset($output[$t]))		$output[$t] = array();
					//die($t.'sss');
					//$output[$i][$t] = $v;
					$output[$t][] = $v;
				}
				else
				{
				
					$v = $this->domnode_to_array($child);
					if($v)				$output = (string) $v;
					elseif($v=='0')		$output = $v;
				}
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
?>
