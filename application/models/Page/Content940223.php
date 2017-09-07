<?php
/*
	*	
*/
require_once 'Page.php';

class Application_Model_Page_Content extends Application_Model_Page_Page
{

	var	$ContentTypes	= array('t', 'q', 'g', 's');
	public function	__construct($data)
	{
		if($this->renderer=='interface') return;
		parent::__construct($data);
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
	public function analyzePageContentDeclaration()
	{
		if(!$this->page or !is_array($this->page['wb_xml']['var:contents'])) return false;
		//print_r($this->page['wb_xml']);
		foreach($this->page['wb_xml']['var:contents'] as $content)
		{
			if(!in_array($content['type'], $this->ContentTypes)) continue;

			if(!is_numeric($content['container'])) continue;
			
			$this->allPageContents[$content['type']]['data'][$content['id']]	= $content;
			$this->allPageContents[$content['type']]['allID'][]	= $content['id'];
			$this->allPageSegments[$content['container']][]		= $content;
		}
		foreach( $this->ContentTypes as $tvalue)
				if (is_array($this->allPageContents[$tvalue]['allID']))	
					$this->allPageContents[$tvalue]['allID']	= array_unique($this->allPageContents[$tvalue]['allID']);
		//print_r($this->allPageContents);
	}
/*	public function getContentIds() 
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
	}*/
	public function	getPageRTC()
	{
		if(!is_array($this->allPageContents['t']['allID']) || count($this->allPageContents['t']['allID'])==0) return false;
		$sql	= 'SELECT co.id AS unic, co.title AS title1, co.ltn_name AS title2, co.description AS abstract, co.publish_up, co.content AS text, co.setting, co.type_id,'
				. ' me.author, me.extra_data '
				. ' FROM wbs_rtcs AS co LEFT JOIN wbs_rtc_metadata AS me ON co.id = me.txt_id '
				. ' WHERE '.Application_Model_Pubcon::get(1111, 'co')
				. ' AND co.id IN ('.implode(',' , $this->allPageContents['t']['allID']).')';
		if(! $result	= $this->DB->fetchAll($sql) ) return false;
		
		/// Get Contents Custom Varibles
		$sql2	= 'SELECT * '
				. ' FROM wbs_custom_variables '
				. ' WHERE '.Application_Model_Pubcon::get(1100)
				. ' AND scope_id=4 AND refrence_id IN ('.implode(',' , $this->allPageContents['t']['allID']).')';
		$custom_varibles = $this->DB->fetchAll($sql2);
		
		if(is_array($custom_varibles))
		{
			$structured_custom_varibles = array();
			foreach($custom_varibles as $varibles)
			{
				$structured_custom_vars[$varibles['refrence_id']] = $varibles['variables'];
			}
		}
				
		foreach($result as $value)
		{
			$value['text']		= stripslashes($value['text']);
			$value['abstract']	= stripslashes($value['abstract']);
			$value['extra_data']= stripslashes($value['extra_data']);
			$value['custom_var']= (isset($structured_custom_vars[$value['unic']]))?stripslashes($structured_custom_vars[$value['unic']]):"";
			
			switch($value['type_id'])
			{
				//case 0:
				default:	$value = $this->_rtcTypeFn1($value); break;
				case 2:	$value = $this->_rtcTypeFn2($value, "Safe"); break;
				case 5:	$value = $this->_rtcTypeFn2($value, "Normal"); break;
				//case 3:	$value = $this->_rtcTypeFn3($value); break;
				case 3:	break;
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
			$user_params	= $this->helper_xalresult2sitevars($result);
			if(count($user_params)>0)	$value['user_params']	=	$user_params;
		}
		unset($value['extra_data']);
		
		if( strlen( trim($value['custom_var']) )>4 )
		{
			if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
			$this->_XAL->setTheRunningMode('NORMAL_MODE');
			$value['custom_var']	= '<execution>'.$value['custom_var'].'</execution>';
			$result	= $this->_XAL->run($value['custom_var']);
			$custom_vars	= $this->helper_xalresult2sitevars($result);
			$value['custom_var'] = "";
			if(count($custom_vars)>0)	$value['custom_var']	=	$custom_vars;
		}
		
		
		return $value;
	}
	public function helper_xalresult2sitevars($xal_result)
	{
		if(!is_array($xal_result) or count($xal_result)<=0) return array();
		$site_vars = array();
		foreach($xal_result as $key=>$val) 
			if( preg_match('/^var\:/', $key) )
			{
				$key	= str_replace('var:', '#rasta-', $key).'#';
				$site_vars[ $key ]	= $val;				
			}
		return $site_vars;
	}
/*	protected function _rtcTypeFn3($value, $meta_block)	// Last Post Block
	{
		if( strlen( trim($value['extra_data']) )<4 ) return $value;
		
		if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
		$this->_XAL->disableAll();
		$this->_XAL->enable(array('execution'));
		$value['extra_data']	= '<execution>'.$value['extra_data'].'</execution>';
		$result	= $this->_XAL->run($value['extra_data']);
		$this->_XAL->enableAll();
		if( !is_numeric($result['var:scen_id']) )	return $value;
		
		$LPB	= new Application_Model_LastPostsBlock;
		if( $scen = $LPB->getScenarioContent($result, $meta_block['type']) )	$value['text'] = $this->_scenMetaBlocker($scen, $meta_block);
		else	$value['text'] = '';
		$value['abstract']	= $value['text'];
		return $value;
	}
*/
	protected function _rtcTypeFn2($value, $mode)	// XAL
	{
		$value['text']		= trim($value['text']);
		$value['abstract']	= trim($value['abstract']);
		if( empty($value['text']) and empty($value['abstract']) ) return $value;
		if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
		
		if($mode=="Safe")		$this->_XAL->setTheRunningMode('SAFE_MODE');
		elseif($mode=="Normal")	$this->_XAL->setTheRunningMode('NORMAL_MODE');
		
		$this->_XAL->set_env(array('ENV_USER_ID'=> $this->userID));
		
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
	protected function _rtcTypeFn5($value)	// Db Interface
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
	protected function _parseUserParams($ext_data, $mode='systemparam')
	{
		if( !is_object($this->_XAL) )
		{
			$this->helper_ignite_XAL();
			$this->_XAL->disableAll();
			$this->_XAL->enable(array('execution'));
		}
		$ext_data	= '<execution>'.$ext_data.'</execution>';
		$result	= $this->_XAL->run($ext_data);
		$user_params	= array();
		if(is_array($result) and count($result)>0)
			foreach($result as $key=>$val) 
				if( preg_match('/^var\:/', $key) )
				{
					if($mode=='normal')				$key	= str_replace('var:', '', $key);
					elseif($mode=='systemparam')	$key	= str_replace('var:', '#rasta-', $key).'#';
					$user_params[ $key ]	= $val;
					
				}
		if(count($user_params)>0)	return $user_params;
		return false;
	}
	public function helper_ignite_XAL($handler='')	
	{
		if( is_object($handler) )	$this->_XAL	= $handler;
		else	$this->_XAL	= new Xal_Servlet('SAFE_MODE');
	}	

	public function getPageScenarios()
	{
		if(!is_array($this->allPageContents['s']['allID']) || count($this->allPageContents['s']['allID'])==0) return false;
		$sql		= "SELECT * FROM `wbs_scenario` WHERE ".Application_Model_Pubcon::get(1110)
					. " AND id IN (".implode(',' , $this->allPageContents['s']['allID']).")";
		if(! $result= $this->DB->fetchAll($sql) ) return false;
		//print_r($result);
		//print_r($this->htmlBlocks);
		//die('ps');

		$scenario = array();
		foreach($result as $raw_scenario)
		{
			$data['scenario']			= $raw_scenario;
			$data['content.properties']	= $this->allPageContents['s']['data'][$raw_scenario['id']];
			$data['html.block']	= $this->htmlBlocks[$this->allPageContents['s']['data'][$raw_scenario['id']]['container']]['c'];							
			$data['renderer'] = $this->renderer;
			$obj	= new Scenario_Model_Fetch($data);
			$scenario[$raw_scenario['id']] = $obj->fetchAll('html');
		}
		return $scenario;
	}

	public function getPageGallery()
	{
		if(!is_array($this->allPageContents['g']['allID']) || count($this->allPageContents['g']['allID'])==0) return false;
		$sql 	= 'SELECT * FROM `wbs_gallery` WHERE '.Application_Model_Pubcon::get().' AND `gallery_id` IN ('
				. implode(',' , $this->allPageContents['g']['allID']).")";
		if(! $result	= $this->DB->fetchAll($sql) ) return false;	

		/// Get Gallery Custom Varibles
		$sql2	= 'SELECT * '
				. ' FROM wbs_custom_variables '
				. ' WHERE '.Application_Model_Pubcon::get(1100)
				. ' AND scope_id=8 AND refrence_id IN ('.implode(',' , $this->allPageContents['g']['allID']).')';
		$custom_varibles = $this->DB->fetchAll($sql2);
		
		if(is_array($custom_varibles))
		{
			$structured_custom_varibles = array();
			foreach($custom_varibles as $varibles)
			{
				$structured_custom_vars[$varibles['refrence_id']] = $varibles['variables'];
			}
		}

		$fida	= array();
		foreach($result as $value)
		{
			if( $gada	= $this->_formateGallery($value['gallery_html'], $value['tem_id'], $value['options']) )
			{
				$fida[$value['tem_id']]	= $gada['files'];
				$gallery[ $value['gallery_id'] ]['text']	= $gada['text'];
				$gallery[ $value['gallery_id'] ]['title']	= $value['gallery_title'];	
				$gallery[ $value['gallery_id'] ]['unic']	= $value['gallery_id'];
				if(isset($structured_custom_vars[$value['gallery_id']]))
				{
					if( strlen( trim($structured_custom_vars[$value['gallery_id']]) )>4 )
					{
						if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
						$this->_XAL->setTheRunningMode('NORMAL_MODE');
						$custom_vars	= $this->_XAL->run('<execution>'.stripslashes($structured_custom_vars[$value['gallery_id']]).'</execution>');
						$custom_vars	= $this->helper_xalresult2sitevars($custom_vars);
						$gallery[ $value['gallery_id'] ]['custom_var'] = "";
						if(count($custom_vars)>0)	$gallery[ $value['gallery_id'] ]['custom_var']	=	$custom_vars;						
					}

				}
			}
		}
		$this->headGalleryFiles	= implode("\n", $fida);
		return $gallery;
	}	
	public function	getPageSmMenus()
	{		
		if(! is_array($this->allPageContents['q']['allID']) || count($this->allPageContents['q']['allID'])==0) return false;
		$sql	=  'SELECT id, menu_title, content FROM wbs_menu WHERE '.Application_Model_Pubcon::get().' AND id IN ('.implode(',' , $this->allPageContents['q']['allID']).')';
		$result	= $this->DB->fetchAll($sql);
		if(! is_array($result) || count($result)==0) return false;
		foreach ($result as $value)
		{
			$value['content']	= '<root>'.trim($value['content']).'</root>';
			if(strlen($value['content'])>1)
			{
				if(!is_array($this->skin['simple_menu']))
				{
					$menuli	= explode('))', $this->skin['simple_menu'] );
					$this->skin['simple_menu']			='';
					$this->skin['simple_menu']['li']	= preg_replace('/^\(\(/', '', $menuli[0] );
					$this->skin['simple_menu']['block']	= $menuli[1] ;
				}
				$data	= array(
							'xml'	=> $value['content'],
							'db'	=> $this->DB,
							'temp'	=> $this->skin['simple_menu']['patterns'],
							'page'	=> $this->PageID
							);
							
				$menuContent	= $this->_parseMlMenu($data);
				$PageSmMenus[ $value['id'] ]['text']	= ( is_array($menuContent) )?implode('', $menuContent):'';
				$PageSmMenus[ $value['id'] ]['title']	= $value['menu_title'];
				$PageSmMenus[ $value['id'] ]['unic']	= $value['id'];
			}
		}
		return 	$PageSmMenus;
	}
	public function getHeaderMenu()
	{
/*		$pagexml	=	$this->page['wb_xml'];
		$xml 		=	new SimpleXMLElement($pagexml); 
		((string) $xml->m != '')? $MenuId[]=(string) $xml->m : $MenuId=NULL;
*/		
		$HeaderMenu['html']	= '<div class="headerMenu" unic="" style="width:100%; height:50px; position:relative;z-index:999;display:none;"></div>';	
		$HeaderMenu['files']='';
		
		if( !isset($this->page['wb_xml']['var:headermenu'][$this->namespace]['id']) ) return $HeaderMenu;		
		$MenuId[0]			= $this->page['wb_xml']['var:headermenu'][$this->namespace]['id'];
		if(!is_numeric($MenuId[0])) return $HeaderMenu;
		
		//$HeaderMenu['html']	= '<div class="headerMenu" unic="" style="width:100%; height:50px; position:relative;z-index:999;display:none;"></div>';	
		//$HeaderMenu['files']='';

		
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
				$PageMlMenus[ $value['id'] ]['content']	= (is_array($menuContent))?implode('', $menuContent):'';
				$PageMlMenus[ $value['id'] ]['title']	= $value['menu_title'];
				$PageMlMenus[ $value['id'] ]['unic']	= $value['id'];
			}
		}
		return 	$PageMlMenus;
	}
	public function getBlockedContent($section, $type, $content)
	{
		$stype	= ($type == 'menu')? 'q' : 'n';

		if(empty($this->htmlBlocks[$section][$stype])) return false;
		$value	= $this->htmlBlocks[$section][$stype];
		if($value['type'] == 'q')	$value['block'] = preg_replace('/^[\s\(]+[^\)]*\)\)\s*/', '', $value['block']);
		
		if(is_numeric($content['type_id']) and $content['type_id']==3)
			if(strlen( trim($this->htmlBlocks[$section][$stype]['bm_code']) )>4)	
				$content = $this->_rtcTypeFn3($content, array('type'=>$this->htmlBlocks[$section][$stype]['bm_type'], 'code'=>$this->htmlBlocks[$section][$stype]['bm_code']));

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
		
		if(is_array($content['custom_var']))
			$blockWITHtext	= str_replace(array_keys($content['custom_var']), array_values($content['custom_var']), $blockWITHtext);
			
		
		return 	$blockWITHtext;
	}
	public function getPageSegments()
	{
		//print_r($this->allPageSegments);
		//die();
		if( !$this->page or !isset($this->allPageSegments) or !is_array($this->allPageSegments) or count($this->allPageSegments)==0 ) return false; 
		
		$this->pageRTC		= $this->getBlockFooter( $this->getPageRTC()		, 1);
		$this->SmMenus		= $this->getPageSmMenus();
		$this->Scenarios	= $this->getPageScenarios();
		$this->gallery		= $this->getBlockFooter( $this->getPageGallery()	, 2);
		
		foreach($this->allPageSegments as $section_id=>$s_contents)
		{
			if(!is_array($s_contents))
			{
				$segment[$section_id][1]	='';
				continue;
			}
			$i = count($s_contents);
			foreach($s_contents as $content)
			{
				$i = (is_numeric($content['rank']))?$content['rank']:($i+1);
				$type		= $content['type'];
				$content_id	= $content['id'];
				
				if($type=='t' && !empty($this->pageRTC[$content_id]))
					$segment[$section_id][$i]	=	$this->getBlockedContent($section_id, 'rtc', $this->pageRTC[$content_id]);
				elseif($type=='q' && !empty($this->SmMenus[$content_id]))
					$segment[$section_id][$i]	=	$this->getBlockedContent($section_id, 'menu', $this->SmMenus[$content_id]);
				elseif($type=='g' && !empty($this->gallery[$content_id]))
					$segment[$section_id][$i]	=	$this->getBlockedContent($section_id, 'gallery', $this->gallery[$content_id]);
				elseif($type=='s' && !empty($this->Scenarios[$content_id]))
					$segment[$section_id][$i]	=	$this->Scenarios[$content_id];					
			}
			if( is_array($segment[$section_id]) ) ksort($segment[$section_id]);
		}
		
		
		
/*		$pagexml	=	$this->page['wb_xml'];
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
*/		
		return	$segment;

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
