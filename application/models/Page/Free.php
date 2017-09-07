<?php
/*
	*	
*/
require_once 'Html.php';

class Application_Model_Page_Free extends Application_Model_Page_Html
{

	public	$renderer	= 'free';
	public function	__construct($data)
	{
		parent::__construct($data);
	}
	public function	replacePageXml($data)
	{
		if(is_array($this->page['wb_xml']['var:contents']))
			foreach($this->page['wb_xml']['var:contents'] as $k=>$v)
				if($v['container']==2) unset($this->page['wb_xml']['var:contents'][$k]);

		if(!is_array($this->page['wb_xml']['var:contents'])) $this->page['wb_xml']['var:contents']=array();
		foreach($data as $value)
			$this->page['wb_xml']['var:contents'][] = array('type'=>$value[0], 'id'=>$value[1], 'container'=>2);
		
		return true;
		
		print_r($this->page); die();
			//$newContent['var:contents'][] = array('type'=>$value[0], 'id'=>$value[1], 'container'=>2);
		if(!is_array($this->page['wb_xml'])) $this->page['wb_xml']=array();
		$this->page['wb_xml'] = array_merge($this->page['wb_xml'], $newContent);
		
		
		
		print_r($this->page['wb_xml']);

		
				
		$pagexml	=	$this->page['wb_xml'];
		$xml 		= 	new SimpleXMLElement($pagexml); 
		if(count($xml->s)>0)	
		{		
			foreach($xml->s as $section)
			{	
				$section_id	= (string) $section->attributes()->id;
				if ($section_id	=='2')
				{
					unset($section->t);
					unset($section->q);
					unset($section->g);
					if(is_array($data))
						foreach($data as $value)
								$section->addChild($value[0], $value[1]);
				}
			}
			$this->page['wb_xml']	= $xml->asXML();
		}
		else
		{
			$secContent	= '';
			if(is_array($data))
				foreach($data as $value)
						$secContent	.= '<'.$value[0].'>'.$value[1].'</'.$value[0].'>';
			$this->page['wb_xml']	= '<root><s id="2">'.$secContent.'</s></root>';
		}
		return true;
	}
	public function	_parseMlMenu($data)
	{
		return Application_Model_Helper_Page::parseMlMenu($data);
	}
}
?>