<?php
/*
	*	
*/
require_once 'Page.php';

class Application_Model_Page_Content extends Application_Model_Page_Page
{

	var	$ContentTypes	= array('t', 'q', 'g');
	public function	__construct($data)
	{
		if($this->renderer=='interface') return;
		parent::__construct($data);
		//$this->u_condition	= $this->setUserCondition();
	}
	protected function	_pageConstruct($data)
	{
		parent::__construct($data);
	}


	public function translate($l) 
	{
		$tl	= array();
		$tl['fa']['continue']	= 'ادامه';
		if(empty($tl[LANG][$l]))	return $l;
		return $tl[LANG][$l];
	}
//	public function setUserCondition() 
//	{
//		$ses = new Zend_Session_Namespace('Zend_Auth');
//		$u_condition	= '';
//		$this->user['group']	= $ses->storage->user_group;
//		$this->user['is_admin']	= $ses->storage->is_admin;
//		
//		if($this->user['is_admin']!=1)
//		{
//			//if(!empty($ses->storage->user_group) )
//				$u_condition	= ' AND (user_group RLIKE "(^0$)'
//				. ( (empty($this->user['group']))?'") ':'|(\/'.str_replace('/','\/)|(\/',$this->user['group']).'\/)") ');
//				//" OR `user_group` LIKE '%/".preg_replace('/\//', "/%' OR `user_group` LIKE '%/", $ses->storage->user_group)."/%'";
//			//$u_condition	= " AND (`user_group`='0'".$u_condition.")";
//			
//		}
//		return $u_condition;
//	}
	public function getContentIds() 
	{
		if(! $this->page) return false;
		$pagexml	=	$this->page['wb_xml'];
		$xml 		= 	new SimpleXMLElement($pagexml); 
		$ContentIds	= array();
		foreach( $this->ContentTypes as $tkey=>$tvalue)
		{
			$result	= $xml->xpath('//'.$tvalue);
			if(is_array($result))
			{
				foreach($result as $id)				$ContentIds[$tvalue][]	= (integer) $id ;
				if (!empty($ContentIds[$tvalue]) && is_array($ContentIds[$tvalue]))	$ContentIds[$tvalue] 	= array_unique($ContentIds[$tvalue]);
			}
		}
		
		return $ContentIds;
	}
	public function	getPageRTC()
	{
		if(!is_array($this->ContentIds['t']) || count($this->ContentIds['t'])==0) return false;
		$sql	= 'SELECT co.id AS unic, co.title AS title1, co.ltn_name AS title2, co.description AS abstract, co.publish_up, co.content AS text, co.setting, co.type_id,'
				. ' me.author, me.extra_data '
				. ' FROM wbs_rtcs AS co LEFT JOIN wbs_rtc_metadata AS me ON co.id = me.txt_id '
				. ' WHERE '.Application_Model_Pubcon::get(1111, 'co')
//				. ' WHERE co.wbs_id IN (0, '.WBSiD.') AND (co.wbs_group RLIKE "\/'.str_replace(',','\/|\/',WBSgR).'\/") '
				. ' AND co.id IN ('.implode(',' , $this->ContentIds['t']).')';
//		if($this->user['is_admin']!=1)
//		$sql	.= ' AND (co.user_group RLIKE "(^0$)'
//				. ( (empty($this->user['group']))?'") ':'|(\/'.str_replace('/','\/)|(\/',$this->user['group']).'\/)") ');
				//die($sql);
		if(! $result	= $this->DB->fetchAll($sql) ) return false;
		
		foreach($result as $value)
		{
			$value['text']		= stripslashes($value['text']);
			$value['abstract']	= stripslashes($value['abstract']);
			$value['extra_data']= stripslashes($value['extra_data']);
			
			switch($value['type_id'])
			{
				//case 0:
				default:	$value = $this->_rtcTypeFn1($value); break;
				case 2:	$value = $this->_rtcTypeFn2($value); break;
				case 3:	$value = $this->_rtcTypeFn3($value); break;
			}
			$PageRTC[$value['unic']]			= $value;
			$PageRTC[$value['unic']]['title']	= ($this->page['page_dir']==2)?$value['title2']:$value['title1'];
		}
		return	$PageRTC;	
	}
	protected function _rtcTypeFn1($value)	// Normal Rtc
	{
		$pberakreg	= '\<div\s+style\=[\'\"]\s*page\-break\-after\s*\:\s*always\s*\;\s*[\'\"]\s*\>\s*[^\/]+\/span\>\<\/div\>'; 
		$noBreakCases	= array('SinglePost');
		if(empty($this->renderer) or !in_array($this->renderer, $noBreakCases))
		{
			$rep_str	= '<span class="rasta-rtc-continue"><a href="/rtc/#rasta-unic#">'.$this->translate('continue').' ..</a></span>';
			$value['text']		= preg_replace('/'.$pberakreg.'[\W\w]*$/', $rep_str, $value['text'],1);
			$value['abstract']	= preg_replace('/'.$pberakreg.'[\W\w]*$/', $rep_str, $value['abstract'],1);
		}
		else
		{
			$value['text']		= preg_replace('/'.$pberakreg.'/', '', $value['text']);
			$value['abstract']	= preg_replace('/'.$pberakreg.'/', '', $value['abstract']);
		}
		if( strlen( trim($value['extra_data']) )>4 )
		{
			if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
			$this->_XAL->disableAll();
			$this->_XAL->enable(array('execution'));
			$value['extra_data']	= '<execution>'.$value['extra_data'].'</execution>';
			$result	= $this->_XAL->run($value['extra_data']);
			$this->_XAL->enableAll();
			$user_params	= array();
			if(is_array($result) and count($result)>0)
				foreach($result as $key=>$val) 
					if( preg_match('/^var\:/', $key) )
					{
						$key	= str_replace('var:', '#rasta-', $key).'#';
						$user_params[ $key ]	= $val;
						
					}
			if(count($user_params)>0)	$value['user_params']	=	$user_params;
		}
		return $value;
	}
	protected function _rtcTypeFn2($value)	// Db Interface
	{
		$res	= preg_match('/\#rasta\-db\-interface\-\([^\)]+\)\#/', $value['text'], $mtch);
		if($res)
		{
			$mtch	= preg_replace('/(\#rasta\-db\-interface\-\()|(\)\#)/', '' , $mtch[0]);
			$dbint	= new Db_Model_Interface($mtch);
			$value['text']	= str_replace('#rasta-db-interface-('.$mtch.')#', $dbint->output, $value['text']);
		}
		$res2	= preg_match('/\#rasta\-db\-interface\-\([^\)]+\)\#/', $value['abstract'], $mtch2);
		if($res2)
		{
			$mtch2	= preg_replace('/(\#rasta\-db\-interface\-\()|(\)\#)/', '' , $mtch2[0]);
			if($mtch2==$mtch) $value['abstract']	= str_replace('#rasta-db-interface-('.$mtch.')#', $dbint->output, $value['abstract']);
			else
			{
				$dbint	= new Db_Model_Interface($mtch2);
				$value['abstract']	= str_replace('#rasta-db-interface-('.$mtch2.')#', $dbint->output, $value['abstract']);
			}
		}
		return $value;
	}
	protected function _rtcTypeFn3($value)	// Last Post Block
	{
		$LPB	= new Application_Model_LastPostsBlock;
		$value['text']		= $LPB->getScenarioBolock($value['text'], $value['extra_data']);
		
		return $value;
	}
	public function helper_ignite_XAL($handler='')	
	{
		if( is_object($handler) )	$this->_XAL	= $handler;
		else	$this->_XAL	= new Xal_Servlet('SAFE_MODE');
	}
	protected function _rtcTypeFn5($value)	// XAL
	{
		$value['text']		= trim($value['text']);
		$value['abstract']	= trim($value['abstract']);
		if( empty($value['text']) and empty($value['abstract']) ) return $value;
		if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
		if( !empty($value['text']) )
		{
			$value['text']	= '<execution>'.$value['text'].'</execution>';
			$result	= $this->_XAL->run($value['text']);
			$value['text']	= ( is_string($result) )?$result:'';
		}
		if( !empty($value['abstract']) )
		{
			$value['abstract']	= '<execution>'.$value['abstract'].'</execution>';
			$result	= $this->_XAL->run($value['abstract']);
			$value['abstract']	= ( is_string($result) )?$result:'';
		}
		return $value;
	}
	public function getPageGallery()
	{
		if(empty($this->ContentIds['g']) || !is_array($this->ContentIds['g']) || count($this->ContentIds['g'])==0) return false;
		$sql 	= 'SELECT * FROM `wbs_gallery` WHERE '.Application_Model_Pubcon::get().' AND `gallery_id` IN ('
				. implode(',' , $this->ContentIds['g']).")"; //.$this->u_condition;
		if(! $result	= $this->DB->fetchAll($sql) ) return false;	
		//if(! is_array($result) || count($result)==0) return false;
		$fida	= array();
		foreach($result as $value)
		{
			if( $gada	= $this->_formateGallery($value['gallery_html'], $value['tem_id'], $value['options']) )
			{
				$fida[$value['tem_id']]	= $gada['files'];
				$gallery[ $value['gallery_id'] ]['text']	= $gada['text'];
				$gallery[ $value['gallery_id'] ]['title']	= $value['gallery_title'];	
				$gallery[ $value['gallery_id'] ]['unic']	= $value['gallery_id'];
			}
		}
		$this->headGalleryFiles	= implode("\n", $fida);
		return $gallery;
	}
	
	public function	getPageSmMenus()
	{		
		if(! is_array($this->ContentIds['q']) || count($this->ContentIds['q'])==0) return false;
		$sql	=  'SELECT id, menu_title, content FROM wbs_menu WHERE '.Application_Model_Pubcon::get().' AND id IN ('.implode(',' , $this->ContentIds['q']).')';
		if(!$result	= $this->DB->fetchAll($sql))	return false;
		foreach ($result as $value)
		{
			$PageSmMenus[ $value['id'] ]['text']	= $value['content'];
			$PageSmMenus[ $value['id'] ]['title']	= $value['menu_title'];
			$PageSmMenus[ $value['id'] ]['unic']	= $value['id'];
		}
		return 	$PageSmMenus;

//		$result	= $this->DB->fetchAll($sql);
//		if(! is_array($result) || count($result)==0) return false;
//		foreach ($result as $value)
//		{
//			$value['content']	= '<root>'.trim($value['content']).'</root>';
//			if(strlen($value['content'])>1)
//			{
//				if(!is_array($this->skin['simple_menu']))
//				{
//					$menuli	= explode('))', $this->skin['simple_menu'] );
//					$this->skin['simple_menu']			='';
//					$this->skin['simple_menu']['li']	= preg_replace('/^\(\(/', '', $menuli[0] );
//					$this->skin['simple_menu']['block']	= $menuli[1] ;
//				}
//				$data	= array(
//							'xml'	=> $value['content'],
//							'db'	=> $this->DB,
//							'temp'	=> $this->skin['simple_menu']['patterns'],
//							'page'	=> $this->PageID
//							);
//							
//				$menuContent	= $this->_parseMlMenu($data);
//				$PageSmMenus[ $value['id'] ]['text']	= ( is_array($menuContent) )?implode('', $menuContent):'';
//				$PageSmMenus[ $value['id'] ]['title']	= $value['menu_title'];
//				$PageSmMenus[ $value['id'] ]['unic']	= $value['id'];
//			}
//		}
//		return 	$PageSmMenus;
	}
	public function getHeaderMenu()
	{
		if(!is_array($this->htmlBlocks['htmlHmenu'])) return;
		
		$pagexml	=	$this->page['wb_xml'];
		$xml 		=	new SimpleXMLElement($pagexml); 
		((string) $xml->m != '')? $MenuId[]=(string) $xml->m : $MenuId=NULL;
		
		$HeaderMenu['html']	= '<div class="headerMenu" unic="" style="width:100%; height:50px; position:relative;z-index:999;display:none;"></div>';	
		$HeaderMenu['files']='';

		
		if(!empty($this->htmlBlocks['htmlHmenu']))
		{
			if($HMenu['html']	= $this->getPageMlMenus($MenuId, $this->htmlBlocks['htmlHmenu']['patterns']) )
			{
				$HeaderMenu['html']	= str_replace("#rasta-blockcontent#", $HMenu['html'][$MenuId[0]]['content'], $this->htmlBlocks['htmlHmenu']['block']);
				$HeaderMenu['html']	= '<div class="headerMenu" unic="'.$MenuId[0].'">'.$HeaderMenu['html'].'</div>';	
			}
		}
		else
		{
			$MenuFigure[0]	= '<li><a href="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
			$MenuFigure[1]	= '<div><ul>#rasta-submenuContent#</ul></div>';
			$MenuFigure[2]	= '<li><a href="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
			$MenuFigure[3]	= '<div><ul>#rasta-submenuContent#</ul></div>';
			$MenuFigure[4]	= '<li><a href="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
			
			if($HMenu['html']	= $this->getPageMlMenus($MenuId, $MenuFigure) )
			{
				$DIR	= 'RTL';
				$path	= preg_replace('/\./', '/', $this->page['header_menu_path']);
				// Page Language Direction Addon
				if($this->page['page_dir']==2) $DIR = 'LTR';
				// End
				$themePath	= explode("." , $this->page['header_menu_path']);
				$HeaderMenu['html']	= '<div class="headerMenu" unic="'.$MenuId[0].'" style="width:100%; height:50px; position:relative;z-index:999;">'
									. 	'<div id="menu"><ul class="menu">'
									. 		$HMenu['html']["$MenuId[0]"]['content']
									. 	'</ul></div>'
									. 	'<link rel="stylesheet" type="text/css" href="/templates/mlmenu/apycom/'.$path.'/menu.css" />'
									. 	'<link rel="stylesheet" type="text/css" href="/templates/mlmenu/apycom/'.$themePath[0].'/'.$DIR.'.css" />'
									. '</div>';	
			}
		}
		return	$HeaderMenu;
	}
	public function	getPageMlMenus($data, $MenuFigure)
	{
		if((! is_array($data)) || count($data)==0) return false;
		$sql	=  'SELECT id, menu_title, content FROM wbs_menu WHERE '.Application_Model_Pubcon::get().' AND id IN ('.implode(',' , $data).')';
		$result	= $this->DB->fetchAll($sql);
		if(! is_array($result) || count($result)==0) return false;
		foreach ($result as $value)
		{
			$value['content']	= trim($value['content']);
			if(strlen($value['content'])>1)
			{
				$value['content']	= '<root>'.$value['content'].'</root>';
				$data	= array(
							'xml'		=> $value['content'],
							'db'		=> $this->DB,
							'temp'		=> $MenuFigure,
							'page'		=> $this->PageID
							);
							
				$menuContent	= $this->_parseMlMenu($data);
				$PageMlMenus[ $value['id'] ]['content']	= implode('', $menuContent);
				$PageMlMenus[ $value['id'] ]['title']	= $value['menu_title'];
				$PageMlMenus[ $value['id'] ]['unic']	= $value['id'];
			}
		}
		return 	$PageMlMenus;
	}
	public function getBlockedContent($section, $type, $content)
	{
		$stype	= array('menu'=>'q', 'gallery'=>'n', 'rtc'=>'n');
		if(!is_array($this->htmlBlocks[$section][ $stype[$type] ])) return false;
		switch($type)
		{
			case 'menu':
				$this->_prepareMenuBlock($this->htmlBlocks[$section][ $stype[$type] ], $content);
			break;
			case 'gallery':
			break;
			case 'rtc':
			break;
		}
		
		$stype	= ($type == 'menu')? 'q' : 'n';

		if(empty($this->htmlBlocks[$section][$stype])) return false;
		$value	= $this->htmlBlocks[$section][$stype];
		if($value['type'] == 'q')	$value['block'] = preg_replace('/^[\s\(]+[^\)]*\)\)\s*/', '', $value['block']);
		
		
		$sysParams['#rasta-blockcontent#']	= $content['text'];
		$sysParams['#rasta-blockheader#']	= $content['title'];
		$sysParams['#rasta-unic#']			= (!empty($content['unic']))?$content['unic']:'';
		$sysParams['#rasta-type#']			= $type;
		
		
		if($stype == 'n')
		{
			$sysParams['#rasta-blockcontent-abstract#']	= '';
			$sysParams['#rasta-blockheader2#']			= '';
			$sysParams['#rasta-content-author#']		= '';
			$sysParams['#rasta-content-date#']			= '';
			$sysParams['#rasta-content-time#']			= '';
			$sysParams['#rasta-content-comment-count#']	= '';
			$sysParams['#rasta-content-comment-link#']	= '';
			$sysParams['#rasta-content-author-display#']	= 'display:none;';
			$sysParams['#rasta-content-date-display#']		= 'display:none;';
			$sysParams['#rasta-content-time-display#']		= 'display:none;';
			$sysParams['#rasta-content-comment-display#']	= 'display:none;';
			
			if($type == 'rtc')
			{
				$sysParams['#rasta-blockcontent-abstract#']	= $content['abstract'];
				$sysParams['#rasta-blockheader2#']			= $content['title2'];
				$sysParams['#rasta-content-author#']		= $content['author'];
				$sysParams['#rasta-content-date#']			= $content['date'];
				$sysParams['#rasta-content-time#']			= $content['time'];
			}
			if( !empty($content['author']) )			$sysParams['#rasta-content-author-display#']= '';
			if( !empty($content['date']) )				$sysParams['#rasta-content-date-display#']	= '';
			if( !empty($content['time']) )				$sysParams['#rasta-content-time-display#']	= '';
			if( !empty($content['comment']['link']) )
			{
				$sysParams['#rasta-content-comment-display#']	= '';
				$sysParams['#rasta-content-comment-count#']	= $content['comment']['count'];
				$sysParams['#rasta-content-comment-link#']	= $content['comment']['link'];
			}
		}
		$blockWITHtext	= str_replace(array_keys($sysParams), array_values($sysParams), $value['block']);
		if(is_array($content['user_params']))
			$blockWITHtext	= str_replace(array_keys($content['user_params']), array_values($content['user_params']), $blockWITHtext);
		
		return 	$blockWITHtext;
	}
	public function getPageSegments()
	{
		if($this->page)
		{
			$this->pageRTC		= $this->getBlockFooter( $this->getPageRTC()		, 1);
			$this->SmMenus		= $this->getPageSmMenus();
			$this->gallery		= $this->getBlockFooter( $this->getPageGallery()	, 2);
			
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
						if($type=='t' && !empty($this->pageRTC[$content_id]))
						{	
							$segment[$section_id][++$i]	=	$this->getBlockedContent($section_id, 'rtc', $this->pageRTC[$content_id]);
						}
						elseif($type=='q' && !empty($this->SmMenus[$content_id]))
						{
							$segment[$section_id][++$i]	=	$this->getBlockedContent($section_id, 'menu', $this->SmMenus[$content_id]);
						}
						elseif($type=='g' && !empty($this->gallery[$content_id]))
						{
							$segment[$section_id][++$i]	=	$this->getBlockedContent($section_id, 'gallery', $this->gallery[$content_id]);
						}
					}
				}
				else
				{
					$segment[$section_id][1]	='';
				}
				if( is_array($segment[$section_id]) ) ksort($segment[$section_id]);
			}
			return	$segment;
		}
		return false;			
	}
//////////// Block Footer Methods
	public function getBlockFooter($data, $type)
	{
		if(!is_array($data)) return false;
		foreach($data as $key=>$value)
		{
			$data[ $key ]	= array_merge($data[ $key ], array( 'date'=>'', 'time'=>'', 'comment'=>'' ));
			if( !isset($value['setting']) ) continue;
			if($value['setting'] != '0')
			{
//				print_r($value['setting'].'<br />'.$value['setting'][0].'<br />');
				if($value['setting'][0] != '1') $data[ $key ]['author']	= '';
				if($value['setting'][1] == '1') $data[ $key ]['date']	= $this->getPersianTimestamp($data[ $key ]['publish_up'], 'date');
				if($value['setting'][2] == '1') $data[ $key ]['time']	= $this->getPersianTimestamp($data[ $key ]['publish_up'], 'time');
				if($value['setting'][3] == '1'
				or $value['setting'][3] == '2') $data[ $key ]['comment']= $this->getCommentsLink($value['unic'], $type);
				if($value['setting'][4] == '1') $data[ $key ]['title']	= $this->getSinglePostLink($value['unic'], $value['title'], $type);
				if($value['setting'][4] == '1') 
					if(!empty($data[ $key ]['title2']))
						$data[ $key ]['title2']= $this->getSinglePostLink($value['unic'], $value['title2'], $type);
			}
		}
		return $data;
	}
	
	public function getPersianTimestamp($timestamp, $section=NULL)
	{
		$date	= new Rasta_Pdate;
		$pdate	= $date->gregorian_to_persian(substr($timestamp, 0,4), substr($timestamp, 5,2), substr($timestamp, 8,2));
		$ptimestamp[]	= $pdate[0].'/'.$pdate[1].'/'.$pdate[2];
		$ptimestamp[]	= substr($timestamp, 11,8);
		if(empty($section))return '';
		if($section=='date')return $ptimestamp[0];
		if($section=='time')return $ptimestamp[1];
	}
	public function getSinglePostLink($unic, $title, $type)
	{
		if($type==1)	$typetxt	= 'rtc';
		if($type==2)	$typetxt	= 'gallery';
		$href	= '/'.$typetxt.'/'.$unic;
		$html = $title;
		if($_SERVER['REQUEST_URI'] != $href) $html = '<a href="'.$href.'">'.$title.'</a>';
		return $html;
	}
	public function getCommentsLink($unic, $type)
	{
		$sql	= 'SELECT COUNT(`id`) FROM `wbs_content_comment` WHERE '.Application_Model_Pubcon::get(1000).' AND `type_id`='.$type
				. ' AND `content_id`='.$unic.' AND `status`=2;';
		$result	= $this->DB->fetchOne($sql);
		$data['count']	= $result;
		$data['link']	= '/comment/index/index/pa/'.$unic.':'.$type;
		return $data;
	}
}
?>