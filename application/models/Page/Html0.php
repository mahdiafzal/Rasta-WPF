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
		
		
		if( !is_object($this->_XAL) )
		{
			$this->helper_ignite_XAL();
		}
		$imgmap	= '<execution>'.$imgmap.'</execution>';
		$xal_result	= $this->_XAL->run($imgmap);
		if(!is_array($xal_result['var:gallery'])) return false;
		$_return	= '';
		$i = 1;
		foreach($xal_result['var:gallery'] as $item)
		{
			if(!is_array($item)) continue;
			$temp_var = array();
			foreach($item as $key=>$value)
				$temp_var['#rasta-'.$key.'#'] = $value;
			if(count($temp_var)>0)
				$_return .= str_replace( array_keys($temp_var), array_values($temp_var), $result[0]['block_rep']);
			if($result[0]['rep_max']!=0)
			{
				if($i>=$result[0]['rep_max']) break;
				$i++;
			}
		}
		$_return	= str_replace('#rasta-gallery-content#', $_return, $result[0]['block_fix']);
		
		$sParams[]	= '#rasta-gallery-jsoptions#';
		$pValues[]	= $options;
		$sParams[]	= '#rasta-host-root#';
		$pValues[]	= '/flsimgs/'.WBSiD;
		$_return	= str_replace($sParams, $pValues, $_return);
		
		return array('files'=>stripslashes($result[0]['files']), 'text'=>stripslashes($_return) );		
		

		/*$imgmap		= array_filter( explode(",",$imgmap) );
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
		
		return array('files'=>stripslashes($result[0]['files']), 'text'=>stripslashes($rt) );*/
	}

	public function	getHtmlBlocks()
	{
		$xml 		= new SimpleXMLElement('<root>'.$this->skin['skin_blocks'].'</root>'); 
		$qblocks	= $xml->xpath('//q');
		$blocks		= array_merge($xml->xpath('//h'),$qblocks,$xml->xpath('//n'),$xml->xpath('//m'),$xml->xpath('//c'));
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
				if(strlen($value['bm_code'])>4 and $value['bm_type']==1)
					$this->skin['simple_menu']	= array('block'=>$value['block'] , 'patterns'=>$this->parseMlMenuMetaPattern($value['bm_code']) );
				else	$this->skin['simple_menu']	= $this->parseMlMenuBlock($value['block']);
				break;
			}
		return $fresult;
	}
	public function parseHeaderMenuBlock()
	{
		if(empty($this->htmlBlocks['htmlHmenu'])) return false;
		if(strlen($this->htmlBlocks['htmlHmenu']['bm_code'])>4)
		{
			if($this->htmlBlocks['htmlHmenu']['bm_type']==1)
				$this->htmlBlocks['htmlHmenu']['patterns']	= $this->parseMlMenuMetaPattern($this->htmlBlocks['htmlHmenu']['bm_code']);
			unset($this->htmlBlocks['htmlHmenu']['bm_code']);
		}
		else
			$this->htmlBlocks['htmlHmenu']	= array_merge($this->htmlBlocks['htmlHmenu'], $this->parseMlMenuBlock($this->htmlBlocks['htmlHmenu']['block']) );
		return true;
	}
	public function parseMlMenuMetaPattern($metablock)
	{
		$patterns	= array_map( trim, explode('#rasta-separator#', $metablock) );

		return	$patterns;
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
/*	protected function	_scenMetaBlocker($scen, $meta_block)
	{
		if( strlen( trim($meta_block['code']) )<4 )	return;
		if($meta_block['type']==1)
		{
			$patterns	= explode('#rasta-separator#',  $meta_block['code']);
			if(count($patterns)<3)	return;
			foreach($scen['content'] as $con)
			{
				$con['t1']	= (isset($con['t1']))?$con['t1']:'';
				$con['t2']	= (isset($con['t2']))?$con['t2']:'';
				
				if( strlen( trim($con['extra_data']) )>4 )	$con['user_params']	= $this->_parseUserParams($con['extra_data']);
				
				$sysParams['#rasta-content-datetime#']	= (isset($con['dt']))?Application_Model_Localize::datetime($con['dt']):'';
				$sysParams['#rasta-blockcontent#']	= (isset($con['tx']))?$con['tx']:'';
				$sysParams['#rasta-blockheader#']	= ($this->page['page_dir']==2 )?$con['t2']:$con['t1'];
				$sysParams['#rasta-blockheader2#']	= $con['t2'];
				$sysParams['#rasta-unic#']			= (isset($con['id']))?$con['id']:'';
				$sysParams['#rasta-blockcontent-abstract#']	= (isset($con['ab']))?$con['ab']:'';
				$tmpout	= str_replace(array_keys($sysParams), array_values($sysParams), $patterns[1]);
				if(is_array($con['user_params']))
					$tmpout	= str_replace(array_keys($con['user_params']), array_values($con['user_params']), $tmpout);
				$out[]	= $tmpout;
			}
			$sysParams	= array();
			//$sysParams['#rasta-scenario-unic#']		=
			$sysParams['#rasta-scenario-title#']	= $scen['data']['title'];
			$sysParams['#rasta-scenario-uri#']		= $scen['data']['uri'];
			$sysParams['#rasta-scenario-count#']	= $scen['data']['count'];
			$fixparts	= str_replace(array_keys($sysParams), array_values($sysParams), array('pre'=>$patterns[0], 'post'=>$patterns[2]) );
			$out	= $fixparts['pre'].' '.implode('', $out).' '.$fixparts['post'];
			if(isset($scen['paging_block']))	$out	= $scen['paging_block'].$out.$scen['paging_block'];
			return $out;
		}
		elseif($meta_block['type']==3)
		{
			foreach($scen['content'] as $key=>$con)
			{
				if( strlen( trim($con['extra_data']) )>4 )	$scen['content'][$key]['extra']	= $this->_parseUserParams($con['extra_data'], 'normal');
				if( isset($con['dt']) )	$scen['content'][$key]['dt']	= Application_Model_Localize::datetime($con['dt']);
			}
			if( !is_object($this->_XAL) )	$this->helper_ignite_XAL();
			$this->_XAL->setTheRunningMode('SAFE_MODE');
			$ext_data	= '<execution>'.stripslashes($meta_block['code']).'</execution>';
			$result	= $this->_XAL->run($ext_data, array('var:scenario'=>$scen) );
			if(is_string($result))	return $result;
			return '';
		}
	}
*/
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
		if(is_array($this->segments))
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
		$HtmlBody	= $this->injectSysParamsValue($HtmlBody);
		return	$this->injectCustomVariables($HtmlBody);
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
	public function injectCustomVariables($input)
	{
		$variables	= $this->getHtmlCustomVariables();
		if(!is_array($variables)) return $input;
		// Page Scope Variables
		if(!empty($variables[2]))
		{
			if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
			$this->_XAL->setTheRunningMode('NORMAL_MODE');				
			$result			= $this->_XAL->run('<execution>'.$variables[2].'</execution>');
			$custom_vars	= $this->helper_xalresult2sitevars($result);
			if(count($custom_vars)>0)
				$input = str_replace(array_keys($custom_vars), array_values($custom_vars), $input);
		}
		// Skin Scope Variables
		if(!empty($variables[5]))
		{
			if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
			$this->_XAL->setTheRunningMode('NORMAL_MODE');
			$result	= $this->_XAL->run('<execution>'.$variables[5].'</execution>');
			$custom_vars	= $this->helper_xalresult2sitevars($result);
			if(count($custom_vars)>0)
				$input = str_replace(array_keys($custom_vars), array_values($custom_vars), $input);
		}
		// Site Scope Variables
		if(!empty($variables[1]))
		{
			if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
			$this->_XAL->setTheRunningMode('NORMAL_MODE');
			$result	= $this->_XAL->run('<execution>'.$variables[1].'</execution>');
			$custom_vars	= $this->helper_xalresult2sitevars($result);
			if(count($custom_vars)>0)
				$input = str_replace(array_keys($custom_vars), array_values($custom_vars), $input);
		}
		return $input;	
	}
	public function getHtmlCustomVariables()
	{
		/// Get Site, Page, Skin Custom Varibles
		$refrences = '0,'.$this->page['local_id'].','.$this->skin['skin_id'];
		$sql2	= 'SELECT * '
				. ' FROM wbs_custom_variables '
				. ' WHERE '.Application_Model_Pubcon::get(1100)
				. ' AND scope_id IN (1,2,5) AND refrence_id IN ('.$refrences.')';
		$custom_varibles = $this->DB->fetchAll($sql2);
		if(is_array($custom_varibles))
		{
			$structured_custom_varibles = array();
			foreach($custom_varibles as $varibles)
			{
				$structured_custom_varibles[$varibles['scope_id']] = $varibles['variables'];
			}
			return $structured_custom_varibles;
		}
		return false;
		
		
	}

}
?>