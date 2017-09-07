<?php
/*
	*	
*/
require_once 'Page/Free.php';

class Application_Model_LastPosts extends Application_Model_Page_Free
{

	public function	__construct()
	{
		$this->scenario		= Zend_Registry::get('scenario');
		$this->properties	= $this->getScenrioProperties();
		$data[0]			= $this->scenario['page_id'];
		$this->feedlink_rss	= $this->scenario['uri'].'?output=feed&mode=rss';
		$this->feedlink_atom= $this->scenario['uri'].'?output=feed&mode=atom';
		
		parent::__construct($data);
		
		$this->sc_family	= $this->getScenrioFamily();
		$data	= $this->getScenrioRtcs();
		
		$this->replacePageXml($data);

		$this->ContentIds	= $this->getContentIds();
		$this->segments		= $this->getPageSegments();
		$this->HeaderMenu	= $this->getHeaderMenu(/*array('href')*/);
		if($this->properties['paging'] != 0)
				$this->prependToSection[2]	.= $this->appendToSection[2]	.= $this->getPagingHtml();
		$this->setPageTitle();
		
		
	}
	public function	setPageTitle()
	{
		$this->page['wb_page_title']	= $this->scenario['title'];
		if($this->page['page_dir']==2) $this->page['wb_page_title'] = $this->scenario['latin_title'];
	}
	public function	getScenrioFamily()
	{
		$sql		= "SELECT *  FROM `wbs_scenario_allsubs` WHERE ".Application_Model_Pubcon::get(1100)." AND `sc_id`=".$this->scenario['id'];
		$result	= $this->DB->fetchAll($sql);
		if($result) //$sc_ids[]	= $this->scenario['id'];
			if(count($result)!=0)	//$sc_ids[]	= $this->scenario['id'];
				$sc_ids		= array_unique( array_filter( explode('/', $result[0]['subs']) ) );
		$sc_ids[]	= $this->scenario['id'];
		$sql	= implode("/%' OR `scenarios` LIKE '%/", $sc_ids);
		//die($sql);
		return $sql;
	}
	public function	getScenrioRtcs()
	{
		$sql_start	= 0;
		if($paging_num = $this->getParam(0)) 
			if($this->properties['paging'] != 0 && is_numeric($paging_num)) $sql_start	= ($paging_num-1) * $this->properties['count'];
//		$sql	= "SELECT `id` FROM `wbs_rtcs` WHERE wbs_id='".WBSiD. "' AND `is_published` != '0' AND `publish_up`<=NOW()  AND `publish_down`>=NOW()"
//				. " AND `scenarios` LIKE '%/".$this->scenario['id']."/%' ORDER BY `wbs_rtcs`.`publish_up` DESC LIMIT ".$sql_start." , ".$this->properties['count'];
		$sql	= "SELECT `id` FROM `wbs_rtcs` WHERE ".Application_Model_Pubcon::get()." AND `is_published` != '0' AND `publish_up`<=NOW()  AND `publish_down`>=NOW()"
				. " AND (`scenarios` LIKE '%/".$this->sc_family."/%') ORDER BY `publish_up` DESC LIMIT ".$sql_start." , ".$this->properties['count'];
		$result	= $this->DB->fetchAll($sql);
		foreach($result as $value) $ScenRtcs[] = array('t', $value['id']); 
		if(!empty($ScenRtcs) && count($ScenRtcs)>0) return $ScenRtcs;
		//die(Message404);
	}
	public function	getScenrioProperties()
	{
		$properxml	=	'<root>'.$this->scenario['properties'].'</root>';
		$xml 		= 	new SimpleXMLElement($properxml); 			
		$properties['count']	= (int) $xml->c;
		$properties['paging']	= (string) $xml->p;
		return $properties;
	}
	public function	getPagingHtml()
	{
//		$sql			= "SELECT COUNT(`id`) FROM `wbs_rtcs` WHERE wbs_id='".WBSiD. "' AND `is_published` != '0' AND `scenarios` LIKE '%/".$this->scenario['id']."/%' ";
		$sql	= "SELECT COUNT(`id`) FROM `wbs_rtcs` WHERE ".Application_Model_Pubcon::get()." AND `is_published` != '0' AND `publish_up`<=NOW()  AND `publish_down`>=NOW()"
				. " AND (`scenarios` LIKE '%/".$this->sc_family."/%') ";
		$result			= $this->DB->fetchOne($sql);
		if($result <= $this->properties['count']) return NULL;
		$paging_count	= ceil($result/$this->properties['count']);
		
		if(! $paging_num = $this->getParam(0)) $paging_num	= 1;
		if(! is_numeric($paging_num)) $paging_num	= 1;
		
		$paging_html	= '<div id="contPaging" style="width:100%;height:35px;text-align:center;">';
		for($i=1; $i<=$paging_count; $i++)
		{
			$paging_href	= preg_replace('/^([^\!]+)\!?\d*\,?/', '${1}!'.$i.',', $_SERVER['REQUEST_URI']);
			$paging_one		= '&nbsp;<a href="'.$paging_href.'">'.$i.'</a>&nbsp;';
			if($i == $paging_num ) $paging_one	='&nbsp;<b>'.$i.'</b>&nbsp;';
			$paging_html	.= $paging_one;
		}
		$paging_html	.= '</div>';
		return $paging_html;
	}
	public function	getParam($i)
	{
		$uri_query		= preg_replace('/^[^\!]+\!/', '', $_SERVER['REQUEST_URI']);
		$uri_params		= explode(',', $uri_query);
		if(!empty($uri_params[$i]))return $uri_params[$i];
		return false;
	}
	
	
}
?>