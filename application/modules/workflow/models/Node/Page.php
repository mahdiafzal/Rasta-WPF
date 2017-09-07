<?php
/*
	*	
*/

class Workflow_Model_Node_Page extends Application_Model_Page_Interface
{
	public function	__construct()
	{
		parent::__construct();
		$this->node_contents	= Zend_Registry::get('node_contents');
	}
	public function	renderThePage($data)
	{
		$this->_htmlConstruct($data);
		$this->_contentConstruct($data);
		$this->_pageConstruct($data);

		$this->htmlBlocks	= $this->getHtmlBlocks();
		$this->parseHeaderMenuBlock();
		$this->_replace_content();
		$this->ContentIds	= $this->getContentIds();
		$this->segments		= $this->getPageSegments();
		$this->HeaderMenu	= $this->getHeaderMenu();
		$this->_setPageTitle();
		$this->_add_node_contents();
		//print_r($this);
		//die('LLLLLLLLL');
	}
	protected function	_add_node_contents()
	{
		if( !is_array($this->node_contents) or count($this->node_contents)==0 )	return;
		if( is_array($this->node_contents['replace']) )
			foreach($this->node_contents['replace'] as $sc_id=>$sc_val)
			{
				if($sc_val['type']=='block')	$this->segments[$sc_id]	= array( $this->getBlockedContent($sc_id, 'rtc', $sc_val['content']) );
				else	$this->segments[$sc_id]	= array( $sc_val['content'] );
			}
		if( is_array($this->node_contents['prepend']) )
			foreach($this->node_contents['prepend'] as $sc_id=>$conts)
				if(is_array($conts))
				{
					krsort($conts);
					if( !is_array($this->segments[$sc_id]) )	$this->segments[$sc_id] = array();
					foreach($conts as $cn_val)
					{
						if($cn_val['type']=='block')	array_unshift( $this->segments[$sc_id], $this->getBlockedContent($sc_id, 'rtc', $cn_val['content']) );
						else	array_unshift( $this->segments[$sc_id], $cn_val['content'] );
					}
				}
		if( is_array($this->node_contents['append']) )
			foreach($this->node_contents['append'] as $sc_id=>$conts)
				if(is_array($conts))
				{
					ksort($conts);
					if( !is_array($this->segments[$sc_id]) )	$this->segments[$sc_id] = array();
					foreach($conts as $cn_val)
					{
						if($cn_val['type']=='block')	array_push( $this->segments[$sc_id], $this->getBlockedContent($sc_id, 'rtc', $cn_val['content']) );
						else	array_push( $this->segments[$sc_id], $cn_val['content'] );
					}
				}
	}
	protected function	_replace_content()
	{
//		$this->node_contents['replace']['3']	= array('type'=>'block', 'content'=>array('title'=>'AAAAAAAAAAAAAAAAAA', 'text'=>'CCCCCCCCCCCCCCCCCCCCCCC') );
//		$this->node_contents['prepend']['4'][1]	= array('type'=>'block', 'content'=>array('title'=>'AAAAAAAAAAAAAAAAAA', 'text'=>'CCCCCCCCCCCCCCCCCCCCCCC') );
//		$this->node_contents['prepend']['3'][2]	= array('type'=>'block', 'content'=>array('title'=>'AAAAAAAAAAAAAAAAAA', 'text'=>'CCCCCCCCCCCCCCCCCCCCCCC') );
		if( is_array($this->node_contents) and isset($this->node_contents['replace']) )
		{
			$sections	= array_keys($this->node_contents['replace']);
			$rdata		= array();
			foreach($sections as $s)	if(is_numeric($s))	$rdata[$s]	= '';
			$this->_replacePageXml($rdata);
		}
		if( $this->Post_id and !isset($this->node_contents['replace']['2']) )
		{
			$this->renderer	= 'SinglePost';
			$rdata['2'][]	= array('t', $this->Post_id);
			$this->_replacePageXml($rdata);
		}
	}
	protected function	_replacePageXml($data)
	{
		$sct		= array_keys($data);
		$pagexml	= $this->page['wb_xml'];
		$xml 		= new SimpleXMLElement($pagexml); 
		if(count($xml->s)>0)	
		{		
			foreach($xml->s as $section)
			{	
				$section_id	= (string) $section->attributes()->id;
				if ( in_array($section_id, $sct) ) //	=='2')
				{
					unset($section->t);
					unset($section->q);
					unset($section->g);
					if(is_array($data[$section_id]))
						foreach($data[$section_id] as $value)
								$section->addChild($value[0], $value[1]);
				}
			}
			$this->page['wb_xml']	= $xml->asXML();
		}
		else
		{
			$contents	= '';
			if(is_array($data))
				foreach($data as $key=>$value)
					if(is_array($value) and is_numeric($key))
					{
						$secContent	= '';
						foreach($value as $v)	$secContent	.= '<'.$v[0].'>'.$v[1].'</'.$v[0].'>';
						$contents	.= '<s id="'.$key.'">'.$secContent.'</s>';
					}
			$this->page['wb_xml']	= '<root>'.$contents.'</root>';
		}
		return true;
	}
	protected function	_setPageTitle()
	{
		if(! $this->Post_id ) return false;
		if(is_array($this->pageRTC[$this->Post_id]))
		{
			$this->page['wb_page_title']	= $this->pageRTC[$this->Post_id]['title1'];
			if($this->page['page_dir']==2 ) $this->page['wb_page_title'] = $this->pageRTC[$this->Post_id]['title2'];
			return true;
		}
	}

//	public function	getNodeContents()
//	{
//		$nc	= Zend_Registry::get('node_contents');
//		if( !is_array($nc) or count($nc)==0 )	return false;
//		$fnc	= $nc;
//		foreach($nc as $key=>$value)
//			if($value['mode']=='replace')	$fnc = array_slice($nc, $key);
//		return $fnc;
//	}
}
?>