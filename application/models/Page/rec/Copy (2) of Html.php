<?php
/*
	*	
*/
require_once 'Content.php';

class Application_Model_Page_Html extends Application_Model_Page_Content
{

	var $prependToSection	= array('','','','','');
	var $appendToSection	= array('','','','','');
	var $headPageDir;
	var $headGalleryFiles;
	var $headMetaData;
	var $HeaderMenu			=array('files'=>'','html'=>'');
	
	public function	__construct($data)
	{
		if($this->renderer=='interface') return;
		parent::__construct($data);
		$this->htmlBlocks	= $this->getHtmlBlocks();
		$this->parseHeaderMenuBlock();
	}
	protected function	_contentConstruct($data)
	{
		parent::__construct($data);
		//$this->htmlBlocks	= $this->getHtmlBlocks();
		//$this->parseHeaderMenuBlock();
	}
	
	public function _formateGallery($imgmap, $tem, $options='')
	{
		$sql	=	 "SELECT * FROM `wbs_gallery_template` WHERE ".Application_Model_Pubcon::get(1110)." AND `id`=".$tem;
		$result	= $this->DB->fetchAll($sql);
		if(!is_array($result) or count($result)!=1)	return false;

		$imgmap		= array_filter( explode(",",$imgmap) );
		$i	= 1;
		$rt	= '';
		foreach($imgmap as $img)
		{
			$rt	.= str_replace('#rasta-image-name#', $img, $result[0]['block_rep']);
			if($result[0]['rep_max']!=0)
			{
				if($i>=$result[0]['rep_max']) break;
				$i++;
			}
		}
		$rt	= str_replace('#rasta-gallery-content#', $rt, $result[0]['block_fix']);
		
		$sParams[]	= '#rasta-gallery-jsoptions#';
		$pValues[]	= $options;
		$sParams[]	= '#rasta-host-root#';
		$pValues[]	= '/flsimgs/'.WBSiD;
		$rt	= str_replace($sParams, $pValues, $rt);
		
		return array('files'=>stripslashes($result[0]['files']), 'text'=>stripslashes($rt) );
	}
	public function	getHtmlBlocks()
	{
		$xml 		= new SimpleXMLElement('<root>'.$this->skin['skin_blocks'].'</root>'); 
		$qblocks	= $xml->xpath('//q');
		$blocks		= array_merge($xml->xpath('//h'),$qblocks,$xml->xpath('//n'),$xml->xpath('//m'));
		foreach($blocks as $value) $blockids[] = (string) $value;
		$blockids = array_unique($blockids);
		//$sql	=	 "SELECT * FROM `wbs_skin_block` WHERE ".Application_Model_Pubcon::get(1100)." AND `id` IN (".implode(',', $blockids).")";
		$sql	=	 "SELECT * FROM `wbs_skin_block` AS bc LEFT JOIN `wbs_skin_block_meta` AS bm ON `bm`.`bm_bc_id`=`bc`.`id` WHERE `bc`.`id` IN (".implode(',', $blockids).")";
		$result	= $this->DB->fetchAll($sql);
		foreach($result as $key=>$val)
		{
			$result[$key]['block']	= stripslashes($val['block']);
			if(strlen($val['bm_code'])>3)	$result[$key]['bm_code']	= stripslashes($val['bm_code']);
		}
		foreach($result as $value) 
			if($value['id'] == (string)$xml->h) $fresult['htmlHeadB'] = $value;
			elseif($value['id'] == (string)$xml->m) $fresult['htmlHmenu'] = $value;

		foreach($xml->s as $section) 
			foreach($section->xpath('./*') as $sblocks) 
				foreach($result as $value) if($value['id'] == (string)$sblocks) 
					$fresult[(string)$section->attributes()->id][$sblocks->getName()] = $value;

		foreach($result as $value)
			if($value['id'] == (string)$qblocks[0] )
			{
				$this->skin['simple_menu']	= $this->parseMlMenuBlock($value['block']);
				break;
			}
		print_r($fresult); die();
		return $fresult;
	}
	public function parseHeaderMenuBlock()
	{
		if(empty($this->htmlBlocks['htmlHmenu'])) return false;
		$this->htmlBlocks['htmlHmenu']	= array_merge($this->htmlBlocks['htmlHmenu'], $this->parseMlMenuBlock($this->htmlBlocks['htmlHmenu']['block']) );
		return true;
	}
	public function parseMlMenuBlock($block)
	{
		if(empty($block)) return false;
		$matchs		= preg_match('/^[\s\(]+[^\)]*\)\)\s*/', $block, $patterns);
		$block		= preg_replace('/^[\s\(]+[^\)]*\)\)\s*/', '', $block);
		$patterns	= explode('#rasta-separator#', preg_replace('/(\(\()|(\)\))/', '', $patterns[0]) );
		foreach($patterns as $key=>$value) $patterns[$key]	= trim($value);
		
		if(empty($patterns[1])) return array('block'=>$block , 'patterns'=>$patterns );
		
		if(empty($patterns[2]))	$patterns[2]	= $patterns[0];
		if(empty($patterns[3]))	$patterns[3]	= $patterns[1];
		if(empty($patterns[4]))	$patterns[4]	= $patterns[0];
		
		return array('block'=>$block , 'patterns'=>$patterns );
	}
	public function	getHtmlHead()
	{
		$HtmlHead	= ''; 
		$HeadBolck	= $this->htmlBlocks['htmlHeadB']['block'];
		$HtmlHead	.= $HeadBolck;
		$HtmlHead	.= $this->headPageDir;
		$HtmlHead	.= $this->HeaderMenu['files'];
		$HtmlHead	.= $this->headGalleryFiles;
		return 	$this->injectSysParamsValue($HtmlHead);
	}
	public function getHtmlBody() 
	{
		if(! $this->page) return false;
		$HtmlBody	= $this->skin['body'];	
		$HtmlBody	= preg_replace('/\#rasta-headermenu\#/',	$this->HeaderMenu['html'],		$HtmlBody);
		foreach ($this->segments as $key=>$value)
		{
			if( is_array($value) )
			{ 
				ksort($value);
				$value 		= implode('',$value);
			}
			$value		= (!empty($this->prependToSection[$key]))?$this->prependToSection[$key].$value : $value;
			$value		.= (!empty($this->appendToSection[$key]))?$this->appendToSection[$key]:'';
			$HtmlBody	= preg_replace('/\#rasta\-section'.$key.'\#/',$value, $HtmlBody);
		}
		$HtmlBody	= preg_replace('/\#rasta\-section\d+\#/','', $HtmlBody);
		return	$this->injectSysParamsValue($HtmlBody);
	}
	public function injectSysParamsValue($input)
	{
		$output	= $input;
		$sysParams	= array();
		$paramsValue= array();

		$sysParams['#rasta-null#']				= '';
		$sysParams['#rasta-sitetitle#']			= $this->site['wb_title'];
		$sysParams['#rasta-site-description#']	= $this->site['wb_description'];
		$sysParams['#rasta-site-keywords#']		= $this->site['wb_keywords'];
		$sysParams['#rasta-site-authors#']		= $this->site['wb_authors'];
		$sysParams['#rasta-slogantext#']		= $this->page['wb_page_slogan'];
		$sysParams['#rasta-pagetitle#']			= $this->page['wb_page_title'];
		$sysParams['#rasta-page-description#']	= $this->page['description'];
		$sysParams['#rasta-page-keywords#']		= $this->page['keywords'];
		$sysParams['#rasta-page-authors#']		= $this->page['authors'];
		$sysParams['#rasta-host-root#']			= '/flsimgs/'.WBSiD;
		$sysParams['#rasta-thumbs-root#']		= '/flsimgs/'.WBSiD.'/.thumbs';
		$sysParams['#rasta-skinroot#']			= ($this->skin['wbs_id']>0)?'/flsimgs/'.WBSiD.'/files'.$this->skin['skin_path']:$this->skin['skin_path'];


		$sysParams['#rasta-feedlink-rss#']	= (isset($this->feedlink_rss))?$this->feedlink_rss:'';
		$sysParams['#rasta-feedlink-atom#']	= (isset($this->feedlink_atom))?$this->feedlink_atom:'';


		$output	= str_replace(array_keys($sysParams), array_values($sysParams), $input);
		return $output;
	}

}
?>