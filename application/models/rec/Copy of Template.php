<?php
/*
	*	
*/
require_once 'Page/Html.php';
class Application_Model_Template extends Application_Model_Page_Html
{

	public function	__construct($data)
	{
		parent::__construct($data);
		
		$this->ContentIds				= $this->getContentIds();
		$this->ContentIds				= array_merge($this->ContentIds['t'], $this->ContentIds['q']);
		$this->segments					= $this->getTemplateSegments();
	}
	public function preSkin($data)
	{
		$this->page['wb_xml']			= $data[3];
		$this->page['page_dir']			= 1;
		$this->page['wb_page_slogan']	= $data[2];
		$this->site['wb_title']			= 'انتخاب قالب';
		$this->page['wb_page_title']	= $data[2];
		$this->page['skin_id']			= $this->site['skin_id']	= $data[1];
	}
	public function getTemplateSegments()
	{
		if($this->page)
		{
			$this->TemplateContents		= $this->getTemplateContents();
			
			$pagexml	=	$this->page['wb_xml'];
			$xml 		= 	new SimpleXMLElement($pagexml); 			
			$segment	= 	array();
			
			foreach($xml->s as $section)
			{	 
				$section_id	= (string) $section->attributes()->id;
				$i=0;
				if (count($section->children())>0)
				{
					foreach ($section->children() as $type=>$content_id)
					{
						$type		= (string) $type;
						$content_id	= (string) $content_id;
						if($type=='t' && !empty($this->TemplateContents[$content_id]))
						{	
							$segment[$section_id][++$i]	=	$this->getBlockedContent($section_id, 'rtc', $this->TemplateContents[$content_id]);
						}
						elseif($type=='q' && !empty($this->TemplateContents[$content_id]))
						{
							$segment[$section_id][++$i]	=	$this->getBlockedContent($section_id, 'menu', $this->TemplateContents[$content_id]);
						}
						elseif($type=='g' && !empty($this->TemplateContents[$content_id]))
						{
							$segment[$section_id][++$i]	=	$this->getBlockedContent($section_id, 'gallery', $this->TemplateContents[$content_id]);
						}
					}
				}
				else
				{
					$segment[$section_id][1]	='';
				}
				ksort($segment[$section_id]);
			}
			return	$segment;
		}
		return false;			
	}
	public function getTemplateContents()
	{
		$sql	= "SELECT * FROM `helper_template_contents` WHERE `id` IN (".implode(',' , $this->ContentIds).")";
		$result	= $this->DB->fetchAll($sql);
		if(! is_array($result) || count($result)==0) return false;
		foreach($result as $value)
		{
			$Contents[$value['id']]['text']	= stripslashes($value['content']);
			$Contents[$value['id']]['title']	= $value['title'];
			$Contents[$value['id']]['unic']		= $value['id'];
		}
		return	$Contents;	
	}
}
?>