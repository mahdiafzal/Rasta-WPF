<?php
/*
	*	
*/
require_once 'Page/Free.php';

class Application_Model_Search extends Application_Model_Page_Html
{

	public function	__construct()
	{
		$this->scenario		= Zend_Registry::get('scenario');
		$this->properties	= $this->getScenrioProperties();

		$this->num			= (!empty($this->properties['count']) && is_numeric($this->properties['count']))?$this->properties['count']:10;
		$this->start		= 0;
		if(! empty($_GET['n']) )	$this->num		= $_GET['n'];
		if(! empty($_GET['s']) )	$this->start	= $_GET['s'];
		$this->sParams['q']							= $_GET['q'];
		$data[0]			= $this->scenario['page_id'];

		parent::__construct($data);

		$this->getSearchResults();
		$this->ContentIds	= $this->getContentIds();
		$this->segments		= $this->getPageSegments();

		//$this->segments[2][]	= $this->getBlockedContent(2, 'rtc', $this->generateSearchSegment() );
		array_unshift($this->segments[2], $this->getBlockedContent(2, 'rtc', $this->generateSearchSegment() ) );

		$this->HeaderMenu	= $this->getHeaderMenu(/*array('href')*/);
		$this->setPageTitle();
	}
	public function	setPageTitle()
	{
		$this->page['wb_page_title']	= $this->scenario['title'];
		if($this->page['page_dir']==2) $this->page['wb_page_title'] = $this->scenario['latin_title'];
	}
	public function	getSearchResults()
	{
		if(  empty($_GET['q']) )	return false;
		$this->count	= $this->search_keyword_relevancy_count($this->sParams);
		if($this->count == 0)		return false;
		$result		= $this->search_keyword_relevancy($this->sParams, $this->start, $this->num);
		$highKeys	= $this->keywords;
		$result		= $this->results_keyword_highlighting($highKeys, $result);

		$this->paging		= $this->getListPaging($this->count, $this->start, $this->num);
		
		return $result;
	}
	public function generateSearchSegment()
	{
		$segment['unic']	= 0;
		$segment['title']	= ($this->page['page_dir']==1)?$this->scenario['title']:$this->scenario['latin_title'];
		$segment['text']	= '<div dir="rtl">هیچ نتیجه ای یافت نشد.</div>';
		if(! $result = $this->getSearchResults() ) return $segment;
		$segmentHtml	= '<div>';
		$segmentHtml	.= '<div dir="rtl">'.$this->count.' مورد یافت شد</div>';
		$segmentHtml	.= '<br />'.$this->paging;
		$segmentHtml	.= '<ul>';
		foreach($result as $value)
		{
			$segmentHtml	.= '<li><ul>';
			$segmentHtml	.= '<li>&para;&nbsp;<a href="/rtc/'.$value['id'].'">'.$value['title'].'</a></li>';
			if(!empty($value['description']))
			$segmentHtml	.= '<li>'.$value['description'].'</li>';
			$segmentHtml	.= '</ul></li>';
		}
		$segmentHtml	.= '</ul>';
		$segmentHtml	.= $this->paging;
		$segmentHtml	.= '</div>';
		$segment['text'] = $segmentHtml;
		return $segment;
	}
	public function	getScenrioProperties()
	{
		$properxml	=	'<root>'.$this->scenario['properties'].'</root>';
		$xml 		= 	new SimpleXMLElement($properxml); 			
		$properties['count']	= (int) $xml->c;
		$properties['filter']	= (string) $xml->f;
		return $properties;
	}	
	
	public function	getKeywordsFromSearchQuery($keyquery)
	{
		$exact		= preg_match_all('/\"+[^\"]+\"+/', $keyquery, $exactkeywords);
		$keywords = preg_replace('/\s*\"+$/', '', preg_replace('/^\"+\s*/', '', $exactkeywords[0]));
		if($exact>0) $keyquery	= preg_replace('/\"[^\"]+\"/', '', $keyquery);
		$keywords	= array_merge( $keywords, preg_split('/\s+/', trim($keyquery) ));
		$this->keywords	= array_filter($keywords);
		return $this->keywords;
	}
	public function	search_keyword_relevancy_orderquery($keywords, $sParams)
	{
		if( is_array($keywords) && count($keywords)>0 )
		{
			$titleQuery	= '( ';
			$tagsQuery	= '( ';
			$textQuery	= '( ';
			foreach($keywords as $keyword)
			{
				$titleQuery .= '(`wbs_rtcs`.`title` LIKE "%'.$keyword.'%") + ';
				$tagsQuery 	.= '(`wbs_rtcs`.`description` LIKE "%'.$keyword.'%") + ';
				$textQuery 	.= '(`wbs_rtcs`.`content` LIKE "%'.$keyword.'%") + ';
			}
			$titleQuery	= preg_replace('/\+\s*$/', ' )', $titleQuery);
			$tagsQuery	= preg_replace('/\+\s*$/', ' )', $tagsQuery);
			$textQuery	= preg_replace('/\+\s*$/', ' )', $textQuery);
		}
		else $titleQuery	= $tagsQuery = $textQuery = '1';
		$query		= 'ORDER BY ('.$titleQuery.' + '.$tagsQuery.' + '.$textQuery.')  DESC ';
		return $query;
	}
	public function	search_keyword_relevancy_wherequery($keywords, $sParams)
	{
		$titleQueryi= '';
		$tagsQueryi	= '';
		$textQueryi	= '';
		foreach($keywords as $keyword)
		{
			$titleQueryi .= '`wbs_rtcs`.`title` LIKE "%'.$keyword.'%" OR ';
			$tagsQueryi .= '`wbs_rtcs`.`description` LIKE "%'.$keyword.'%" OR ';
			$textQueryi .= '`wbs_rtcs`.`content` LIKE "%'.$keyword.'%" OR ';
		}
		$titleQueryi= preg_replace('/OR\s*$/', '', $titleQueryi);
		$tagsQueryi	= preg_replace('/OR\s*$/', '', $tagsQueryi);
		$textQueryi	= preg_replace('/OR\s*$/', '', $textQueryi);
		
		$query[]	= Application_Model_Pubcon::get().' ';
		$query[]	= '`is_published`!=0 ';
		if($this->properties['filter'] == 1)
			$query[]	= '(`scenarios` LIKE "%/'.$this->getScenrioFamily().'/%") ';
			//$query[]	= '`scenarios` LIKE "%/'.$this->scenario['id'].'/%" ';
		if(! empty($sParams['q']) )
			$query[]	= '('.$titleQueryi.' OR '.$tagsQueryi.' OR '.$textQueryi.') ';

		return 'WHERE '.implode('AND ', $query);
	}
	public function	search_keyword_relevancy($sParams, $start, $num)
	{
		$keyquery	= $sParams['q'];
		$keywords	= $this->getKeywordsFromSearchQuery($keyquery);
		$sql		= 'SELECT `id`, `title`, `description` ';
		$sql		.= 'FROM `wbs_rtcs` ';
		$sql		.= $this->search_keyword_relevancy_wherequery($keywords, $sParams);
		$sql		.= $this->search_keyword_relevancy_orderquery($keywords, $sParams);
		$sql		.= 'LIMIT '.($start*$num).' , '.$num.'';
		return $this->DB->fetchAll($sql);
	}
	public function	search_keyword_relevancy_count($sParams)
	{
		$keyquery	= $sParams['q'];
		$keywords	= $this->getKeywordsFromSearchQuery($keyquery);
		$sqli		= 'SELECT COUNT(`id`) as cnt FROM `wbs_rtcs` ';
		$sqli		.= $this->search_keyword_relevancy_wherequery($keywords, $sParams);
		return $this->DB->fetchOne($sqli);
	}
	public function	text_keyword_highlighting($keywords, $text)
	{
		$highlighttext	= $text;
		//$keywords		= preg_replace('/[\(\)\[\]]/', "&",)
		foreach ($keywords as $value) $highlighttext = preg_replace('/([^\s\,\.\-]*'.$value.'[^\s\,\.\-]*)/', "<b>$1</b>", $highlighttext);
		return $highlighttext;
	}
	public function	results_keyword_highlighting($keywords, $results)
	{
		foreach ($results as $key=>$value) 
		{
			$results[$key]['title'] = $this->text_keyword_highlighting($keywords, $value['title']);
			$results[$key]['description'] = $this->text_keyword_highlighting($keywords, $value['description']);
		}
		return $results;
	}
	public function getListPaging($count, $start, $num)
	{
		if($count <= $num) return NULL;
		
		$paging_count	= $paging_total = ceil($count/$num);
		$paging_current	= ($start != 0) ? ($start/$num)+1 : 1;
		
		if($paging_current	<= 10 && $paging_count >= ($paging_current+9)){	$paging_count = $paging_current+9; $first = 1; }
		if($paging_current	<= 10 && $paging_count <  ($paging_current+9)){	$first = 1; }
		if($paging_current	>  10 && $paging_count >= ($paging_current+9)){	$paging_count = $paging_current+9; $first = $paging_current-9; }
		if($paging_current	>  10 && $paging_count <  ($paging_current+9)){	$first = $paging_count-19; }
		if($first<=0) $first = 1;
		
		$uri_free		= preg_replace('/\&s\=[^&]*/', '', $_SERVER['REQUEST_URI'] );
		$uri_new		= preg_replace('/^([^\?]+)\??(.*)/', '${1}?${2}&s=', $uri_free);
		
		$paging_html	= '<div id="contPaging" style="width:100%;height:35px;text-align:center;">';
		if($first>1) $paging_html .= '&nbsp;<a href="'.$uri_new.'0">&lt;&lt;</a>&nbsp;';
		for($i=$first; $i<=$paging_count; $i++)
		{
			$newstart		= ($i-1)*$num;
			$paging_href	= $uri_new.$newstart;
			$paging_one		= '&nbsp;<a href="'.$paging_href.'">'.$i.'</a>&nbsp;';
			if($i == $paging_current ) $paging_one	='&nbsp;<b>'.$i.'</b>&nbsp;';
			$paging_html	.= $paging_one;
		}
		if($paging_count < $paging_total) $paging_html .= '&nbsp;<a href="'.$uri_new.(($paging_total-1)*$num).'">&gt;&gt;</a>&nbsp;';
		$paging_html	.= '</div>';
		
		return $paging_html;
	}
	public function	_parseMlMenu($data)
	{
		return Application_Model_Helper_Page::parseMlMenu($data);
	}
	public function	getScenrioFamily()
	{
		$sql		= "SELECT *  FROM `wbs_scenario_allsubs` WHERE ".Application_Model_Pubcon::get(1100)." AND `sc_id`=".$this->scenario['id'];
		$result	= $this->DB->fetchAll($sql);
		if($result)
			if(count($result)!=0)
				$sc_ids		= array_unique( array_filter( explode('/', $result[0]['subs']) ) );
		$sc_ids[]	= $this->scenario['id'];
		$sql	= implode('/%" OR `scenarios` LIKE "%/', $sc_ids);
		return $sql;
	}
}
?>