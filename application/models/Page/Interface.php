<?php
/*
	*	
*/
require_once 'Html.php';

class Application_Model_Page_Interface extends Application_Model_Page_Html
{

	public	$renderer	= 'interface';
	public function	__construct()
	{
	}
	public function	_htmlConstruct($data)
	{
		parent::__construct($data);
	}
	
	public function	replacePageXml($data)
	{
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
						$secContent	= '<'.$value[0].'>'.$value[1].'</'.$value[0].'>';
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