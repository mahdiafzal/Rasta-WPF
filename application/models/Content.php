<?php
class Application_Model_Content
{
	var 	$DB;

	public function	__construct($page_id=NULL, $skin_id=NULL)
	{
		$registry 			= Zend_Registry::getInstance();  
		$this->DB 			= $registry['front_db'];
		$this->site			= $registry['site'];
		
		///Change in 20140809: Dedicate ini file
		$this->docroot	= $this->site['docroot'];
		
		//$this->setUserData();
		if(!empty($page_id)) $this->page	= $this->getPageInfos($page_id);
		if(!empty($skin_id)) $this->skin	= $this->getSkinInfos($skin_id);
		
	}
//	public function setUserData() 
//	{
//		$ses = new Zend_Session_Namespace('Zend_Auth');
//		$this->user['group']	= $ses->storage->user_group;
//		$this->user['is_admin']	= $ses->storage->is_admin;
//		$this->user['condition']	= '';
//		if($this->user['is_admin']!=1)
//		$this->user['condition']	= ' AND (user_group RLIKE "(^0$)'
//				. ( (empty($this->user['group']))?'") ':'|(\/'.str_replace('/','\/)|(\/',$this->user['group']).'\/)") ');
//	}
	public function getPageInfos($page_id)
	{
		$sql			= "SELECT * FROM `wbs_pages` WHERE ".Application_Model_Pubcon::get(1001)." AND `wbs_pages`.`local_id`=". $page_id ;
		$result			= $this->DB->fetchAll($sql);
//		$result[0]['wb_xml']	= '<root>'.$result[0]['wb_xml'].'</root>';
		return 	$result[0];
	}
	public function getSkinInfos($skin_id)
	{
		$sql	=	 "SELECT * FROM  `wbs_skin` WHERE ".Application_Model_Pubcon::get(1110)." AND (skin_id =".$skin_id.")";
		$result	= $this->DB->fetchAll($sql);
		return 	$result[0];
	}


	//--------------------------------------------------
	public function	getCount($table)
	{
		$sql		= 'select count(*) as `cnt` from `'.$table.'` where '.Application_Model_Pubcon::get();
		$result		= $this->DB->fetchAll($sql);
		//$SiteRTC[1]	= $result;
		return	$result[0]['cnt'];	
	}
	public function	getSiteScenarios($start=0, $limit=8)
	{
		$sql			= "SELECT id, title, uri, properties  FROM `wbs_scenario` WHERE ".Application_Model_Pubcon::get()." ORDER BY `id` DESC LIMIT ".$start.','.$limit;
		$result			= $this->DB->fetchAll($sql);
		//$SiteScenarios[1]	= $result;
		return 	$result;
	}

	//--------------------------------------------------
	public function	getSiteRTCs($start,$limit=8)
	{
		$sql		= 'select `id`, `ltn_name`, `title` from `wbs_rtcs` where '.Application_Model_Pubcon::get().' ORDER BY `wbs_rtcs`.`id` DESC limit '.$start.','.$limit;
		$result		= $this->DB->fetchAll($sql);
		//$SiteRTC[1]	= $result;
		return	$result;	
	}
	
	//--------------------------------------------------
	public function	getGalleryList($start,$limit=8)
	{
		$sql	= 'SELECT * FROM `wbs_gallery` WHERE '.Application_Model_Pubcon::get().' ORDER BY `wbs_gallery`.`gallery_id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		return 	$result;
	}
	//--------------------------------------------------
	public function	getMenuList($start,$limit=8)
	{
		$sql	= 'SELECT * FROM `wbs_menu` WHERE '.Application_Model_Pubcon::get().' ORDER BY `wbs_menu`.`id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		return 	$result;
	}
	//--------------------------------------------------
	public function	getPagelist($start,$limit=8)
	{
		$sql	= 'SELECT * FROM `wbs_pages` WHERE '.Application_Model_Pubcon::get().' ORDER BY `wbs_pages`.`wb_page_id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		return 	$result;
	}
	//--------------------------------------------------
	public function	getExtlinklist($start,$limit=8)
	{
		$sql	= 'SELECT * FROM `wbs_links` WHERE '.Application_Model_Pubcon::get().' ORDER BY `wbs_links`.`id` DESC limit '.$start.','.$limit;
		$result	= $this->DB->fetchAll($sql);
		return 	$result;
	}
	//--------------------------------------------------
	public function	getSerchResult($param ,$key)
	{
		switch ($param)
		{
			case 'rtclist' : 	
							$sql	= 'SELECT `id`,`title`,`ltn_name`  FROM `wbs_rtcs` WHERE '.Application_Model_Pubcon::get()
									.	' and `wbs_rtcs`.`title` like "%' .$key. '%" ORDER BY `wbs_rtcs`.`id` DESC limit 0,20 ';
							$result	= $this->DB->fetchAll($sql);									
							foreach($result as $value)
							{
								$str=		'<a>ویرایش</a>'
										.	'<a>حذف</a>'
										.	'<a>مشاهده در پنجره جدید</a>'
										.	'<a class="RTCtitle" rtcid="'.$value['id'].'" rtcname="'.$value['ltn_name'].'" title="'.$value['title'].'">'
										.	substr($value['title'],0,60).'</a>';
								$data[] = array('data' => $str , 'value' => substr($value['title'],0,60));
							}
							break;
			case 'Gallerylist' : 	
							$sql	= 'SELECT `gallery_id`,`gallery_title` FROM `wbs_gallery` WHERE '.Application_Model_Pubcon::get()
									.	' and `wbs_gallery`.`gallery_title` like "%' .$key. '%" ORDER BY `wbs_gallery`.`gallery_id` DESC limit 0,20 ';
							$result	= $this->DB->fetchAll($sql);									
							foreach($result as $value)
							{
								$str=		'<a>ویرایش</a>'
										.	'<a>مشاهده در پنجره جدید</a>'
										.	'<a class="Gallerytitle" Galleryid="'.$value['gallery_id'].'" Galleryname="'.$value['gallery_title']
										.	'" title="'.$value['gallery_title'].'">'.substr($value['gallery_title'],0,60).'</a>';
								$data[] = array('data' => $str , 'value' => substr($value['gallery_title'],0,60));
							}
							break;
			case 'menulist' : 	
							$sql	= 'SELECT `id`,`menu_title` FROM `wbs_menu` WHERE '.Application_Model_Pubcon::get()
									.	' and `wbs_menu`.`menu_title` like "%' .$key. '%" ORDER BY `wbs_menu`.`id` DESC limit 0,20 ';
							$result	= $this->DB->fetchAll($sql);									
							foreach($result as $value)
							{
								$str=		'<a>ویرایش</a>'
										.	'<a>مشاهده در پنجره جدید</a>'
										.	'<a class="menutitle" type="vsm" mid="'.$value['id'].'" title="'.$value['menu_title'].'">'
										.	substr($value['menu_title'],0,60).'</a>';
								$data[] = array('data' => $str , 'value' => substr($value['menu_title'],0,60));
							}
							break;

			case 'pagelist' : 	
							$sql	= 'SELECT `local_id`,`wb_page_title` FROM `wbs_pages` WHERE '.Application_Model_Pubcon::get()
									.	' and `wbs_pages`.`wb_page_title` like "%' .$key. '%" ORDER BY `wbs_pages`.`local_id` DESC limit 0,20 ';
							$result	= $this->DB->fetchAll($sql);									
							foreach($result as $value)
							{
								$str	=	'<a>ویرایش عنوان صفحه</a>'
										.	'<a>مشاهده در همین پنجره</a>'
										.	'<a>مشاهده در پنجره جدید</a>'
										.	'<a class="pagetitle" pageid="'.$value['local_id'].'" title="'.$value['wb_page_title'].'">'
										.	substr($value['wb_page_title'],0,60).'</a>';
								$data[] = array('data' => $str , 'value' => substr($value['wb_page_title'],0,60));
							}
							break;
			case 'pageid' : 	
							$sql	= 'SELECT `local_id`,`wb_page_title` FROM `wbs_pages` WHERE '.Application_Model_Pubcon::get()
									.	' and `wbs_pages`.`wb_page_title` like "%' .$key. '%" ORDER BY `wbs_pages`.`local_id` DESC limit 0,20 ';
							$result	= $this->DB->fetchAll($sql);									
							foreach($result as $value)
							{
								$str	= $value['local_id'];
								$data[] = array('data' => $str , 'value' => substr($value['wb_page_title'],0,60));
							}
							break;
							
			case 'scenariolist' : 	
							$sql	= 'SELECT `id`,`title`, `uri` FROM `wbs_scenario` WHERE '.Application_Model_Pubcon::get()
									.	' and `title` like "%' .$key. '%" ORDER BY `id` DESC limit 0,20 ';
							$result	= $this->DB->fetchAll($sql);									
							foreach($result as $value)
							{
								$str	=	'<a>ویرایش</a>'
										.	'<a>مشاهده در پنجره جدید</a>'
										.	'<a class="scenariotitle" scenarioid="'.$value['id'].'" url="'.$value['uri'].'" title="'.$value['title'].'">'
										.	substr($value['title'],0,60).'</a>';
								$data[] = array('data' => $str , 'value' => substr($value['title'],0,60));
							}
							break;
			case 'extlinklist' : 	
							$sql	= 'SELECT `id`,`url`,`title` FROM `wbs_links` WHERE '.Application_Model_Pubcon::get()
									.	' and `wbs_links`.`title` like "%' .$key. '%" ORDER BY `wbs_links`.`id` DESC limit 0,20 ';
							$result	= $this->DB->fetchAll($sql);									
							foreach($result as $value)
							{
								$str	=	'<a>ویرایش عنوان پیوند</a>'
										.	'<a url="'.$value['url'].'">مشاهده در پنجره جدید</a>'
										.	'<a class="extlinktitle" title="'.$value['title'].'">'.substr($value['title'],0,60).'</a>';
								$data[] = array('data' => $str , 'value' => substr($value['title'],0,60));
							}
							break;
		}
		return  $data;
	}
	//--------------------------------------------------
	public function	getBlock($type, $section_id, $skin_id)
	{

		$xml 		= new SimpleXMLElement('<root>'.$this->skin['skin_blocks'].'</root>'); 

		if(!empty($section_id))
		{
			foreach($xml->s as $section) if($section_id == (string)$section->attributes()->id) break;
			foreach($section->xpath('./'.$type) as $sblocks) $block_id = (string)$sblocks;
		}
		else
		{
			$blocks		= $xml->xpath('//'.$type);
			foreach($blocks as $value) $blockids[] = (string) $value;
			if(is_array($blockids))
			{
				$block_id	= $blockids[0];
			}
		}
		if(empty($block_id)) return false;
		//$sql	= "SELECT `block` FROM `wbs_skin_block` WHERE `id` =". $block_id;
		$sql	= "SELECT * FROM `wbs_skin_block` AS bc LEFT JOIN `wbs_skin_block_meta` AS bm ON `bm`.`bm_bc_id`=`bc`.`id` WHERE `bc`.`id` =". $block_id;
		$result2	= $this->DB->fetchAll($sql);
		$result2[0]['block']	= stripslashes($result2[0]['block']);
		if(strlen($result2[0]['bm_code'])>3)	$result2[0]['bm_code']	= stripslashes($result2[0]['bm_code']);
		$result		= array_merge( $this->skin, $result2[0]);
		return 	$result;
	}
	//--------------------------------------------------
	public function	getRtc($id)
	{
		$sql		= 'SELECT co.*, me.`author` FROM `wbs_rtcs` AS co LEFT JOIN `wbs_rtc_metadata` AS me ON co.`id`= me.`txt_id`'
					. ' WHERE '.Application_Model_Pubcon::get(1111, 'co').' AND co.`id`='.$id;
		//if($this->user['is_admin']!=1)
		//$sql	.= ' AND (co.user_group RLIKE "(^0$)'
		//		. ( (empty($this->user['group']))?'") ':'|(\/'.str_replace('/','\/)|(\/',$this->user['group']).'\/)") ');

		if(!$result		= $this->DB->fetchAll($sql)) return false;
		$result[0]['content']	= stripslashes($result[0]['content']);
		$result[0]['abstract']	= stripslashes($result[0]['description']);
		$result[0]['title2']	= $result[0]['ltn_name'];
		$result	= $this->getBlockFooter($result, 1);
		return 	$result[0];
	}
	//--------------------------------------------------
	public function	getMenu($id,$block,$page_id)
	{
		$sql		= 'SELECT id, menu_title, content FROM `wbs_menu` WHERE '.Application_Model_Pubcon::get() .' AND `id`='.$id ;
		$result		= $this->DB->fetchAll($sql);
		if(count($result)==1)
		{
			$SmMenus = '';
				if(strlen(trim($result[0]['content']))>1)
				{				

					if(strlen($block['bm_code'])>4 and $block['bm_type']==1)
						$skin['simple_menu']	= array('block'=>$block['block'] , 'patterns'=>$this->parseMlMenuMetaPattern($block['bm_code']) );
					else	$skin['simple_menu']	= $this->parseMlMenuBlock($block['block']);


					//$skin['simple_menu']	= $this->parseMlMenuBlock($block);
					
					
					$result[0]['content']	= '<root>'.trim($result[0]['content']).'</root>';
					$data	= array(
								'xml'	=> $result[0]['content'],
								'db'	=> $this->DB,
								'temp'	=> $skin['simple_menu']['patterns'],
								'page'	=> $page_id
								);
								
					$menuContent	= Application_Model_Helper_Page::parseMlMenu($data, 'admin');
					
					$SmMenus['content']	= @implode('', $menuContent);
					$SmMenus['title']	= $result[0]['menu_title'];
					$SmMenus['id']		= $result[0]['id'];
					$SmMenus['block']	= $skin['simple_menu']['block'] ;
				}
			}
		return 	$SmMenus;
	}
	//--------------------------------------------------
	public function	getEditMenu($id,$page_id)
	{
		$sql		= 'SELECT id, menu_title, content FROM `wbs_menu` WHERE '.Application_Model_Pubcon::get().' AND `id`='.$id ;
		$result		= $this->DB->fetchAll($sql);
		if(count($result)==1)
		{
			$MLMenus = '';
				if(strlen(trim($result[0]['content']))>1)
				{				
					$result[0]['content']	= '<root>'.trim($result[0]['content']).'</root>';
					$MenuFigure[0]	= '<li><a url="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
					$MenuFigure[1]	= '<div><ul>#rasta-submenuContent#</ul></div>';
					$MenuFigure[2]	= '<li><a url="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
					$MenuFigure[3]	= '<div><ul>#rasta-submenuContent#</ul></div>';
					$MenuFigure[4]	= '<li><a url="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
					
					$data	= array(
								'xml'	=> $result[0]['content'],
								'db'	=> $this->DB,
								'temp'	=> $MenuFigure,
								'page'	=> $page_id
								);
								
					$menuContent	= Application_Model_Helper_Page::parseMlMenu($data, 'editmenu');
					$MLMenus['content']	= implode('', $menuContent);
					$MLMenus['title']	= $result[0]['menu_title'];
					$MLMenus['unic']	= $result[0]['id'];
				}
			}
		return 	$MLMenus;
	}
	//--------------------------------------------------
	public function parseHeaderMenuBlock()
	{
		if(empty($this->htmlBlocks['htmlHmenu'])) return false;
		if(strlen($this->htmlBlocks['htmlHmenu']['bm_code'])>4 )
		{
			if($this->htmlBlocks['htmlHmenu']['bm_type']==1)
				$this->htmlBlocks['htmlHmenu']['patterns']	= $this->parseMlMenuMetaPattern($this->htmlBlocks['htmlHmenu']['bm_code']);
			unset($this->htmlBlocks['htmlHmenu']['bm_code']);
		}
		else
			$this->htmlBlocks['htmlHmenu']	= array_merge($this->htmlBlocks['htmlHmenu'], $this->parseMlMenuBlock($this->htmlBlocks['htmlHmenu']['block']) );
		return true;
//
//		if(empty($this->htmlBlocks['htmlHmenu'])) return false;
//		$this->htmlBlocks['htmlHmenu']	= array_merge($this->htmlBlocks['htmlHmenu'], $this->parseMlMenuBlock($this->htmlBlocks['htmlHmenu']['block']) );
//		return true;
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

	public function	getEditHeaderMenu($id,$page_id, $block=NULL)
	{
		$sql		= 'SELECT id, menu_title, content FROM `wbs_menu` WHERE '.Application_Model_Pubcon::get().' AND `id`='.$id ;
		$result		= $this->DB->fetchAll($sql);
		if(count($result)==1)
		{

			$MLMenus['title']	= $result[0]['menu_title'];
			$MLMenus['unic']	= $result[0]['id'];
			if(!empty($block))
			{
				$this->htmlBlocks['htmlHmenu']	= $block;
				$this->parseHeaderMenuBlock();
				$result[0]['content']	= '<root>'.trim($result[0]['content']).'</root>';
				$data	= array(
							'xml'		=> $result[0]['content'],
							'db'		=> $this->DB,
							'temp'		=> $this->htmlBlocks['htmlHmenu']['patterns'],
							'page'		=> $page_id
							);

					$menuContent	= Application_Model_Helper_Page::parseMlMenu($data, 'admin');
					$MLMenus['content']	= implode('', $menuContent);
					$MLMenus['content']	= str_replace("#rasta-blockcontent#", $MLMenus['content'], $this->htmlBlocks['htmlHmenu']['block']);

			}
			else
			{
				$MenuFigure[0]	= '<li><a href="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
				$MenuFigure[1]	= '<div><ul>#rasta-submenuContent#</ul></div>';
				$MenuFigure[2]	= '<li><a href="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';
				$MenuFigure[3]	= '<div><ul>#rasta-submenuContent#</ul></div>';
				$MenuFigure[4]	= '<li><a href="#rasta-linkhref#"><span>#rasta-linktitle#</span></a>#rasta-submenu#</li>';

				$result[0]['content']	= '<root>'.trim($result[0]['content']).'</root>';
				$data	= array(
							'xml'		=> $result[0]['content'],
							'db'		=> $this->DB,
							'temp'		=> $MenuFigure,
							'page'		=> $page_id
							);

					$menuContent	= Application_Model_Helper_Page::parseMlMenu($data, 'admin');
					
					$MLMenus['content']	= implode('', $menuContent);
					
					$sql			= "SELECT `header_menu_path` FROM wbs_pages WHERE wbs_id='".WBSiD."' AND local_id='".$page_id."'";
					$result			= $this->DB->fetchAll($sql);
					if(strlen($result[0]['header_menu_path'])<1)$result[0]['header_menu_path']='4.1';
					$themePath = explode("." , $result[0]['header_menu_path']);
					$MLMenus['content']	.=	'<link rel="stylesheet" type="text/css" href="/templates/mlmenu/apycom/'
											.preg_replace('/\./', '/', $result[0]['header_menu_path'])
											.'/menu.css" />'
											.'<link rel="stylesheet" type="text/css" href="/templates/mlmenu/apycom/'
											.$themePath[0]
											.'/RTL.css" />';

			}

		}
		return 	$MLMenus;
	}
	//--------------------------------------------------
	public function	getGallery($id)
	{
		$sql		= "SELECT * FROM `wbs_gallery` WHERE ".Application_Model_Pubcon::get()." AND `gallery_id`= ".addslashes($id) ;
		$result		= $this->DB->fetchAll($sql);
		if(count($result)!=1)	return '';
		if(! $gada	= $this->_formateGallery($result[0]['gallery_html'], $result[0]['tem_id'], $result[0]['options'])  )	return '';
		
		$gallery['content']	= $gada['files']."\n".$gada['text'];
		$gallery['title']	= $result[0]['gallery_title'];	
		$gallery['id']		= $result[0]['gallery_id'];
		return $gallery;
	}
	public function helper_ignite_XAL($handler='')	
	{
		if( is_object($handler) )	$this->_XAL	= $handler;
		else	$this->_XAL	= new Xal_Servlet('SAFE_MODE');
	}
	public function _formateGallery($imgmap, $tem, $options='')
	{
		$sql	=	 "SELECT * FROM `wbs_gallery_template` WHERE ".Application_Model_Pubcon::get(1110)." AND `id`=".$tem;
		$result	= $this->DB->fetchAll($sql);
		if(!is_array($result) or count($result)!=1)	return false;
		
		if( !is_object($this->_XAL) )	$this->helper_ignite_XAL();
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
		$pValues[]	= $this->docroot.'/'.WBSiD;
		$_return	= str_replace($sParams, $pValues, $_return);
		
		return array('files'=>stripslashes($result[0]['files']), 'text'=>stripslashes($_return) );		
	}

//	public function _formateGallery($imgmap, $tem, $options='')
//	{
//		$sql	=	 "SELECT * FROM `wbs_gallery_template` WHERE ".Application_Model_Pubcon::get(1110)." AND `id`=".$tem;
//		$result	= $this->DB->fetchAll($sql);
//		if(!is_array($result) or count($result)!=1)	return false;
//
//		$imgmap		= array_filter( explode(",",$imgmap) );
//		$i	= 1;
//		$rt	= '';
//		foreach($imgmap as $img)
//		{
//			$rt	.= str_replace('#rasta-image-name#', $img, $result[0]['block_rep']);
//			if($result[0]['rep_max']!=0)
//			{
//				if($i>=$result[0]['rep_max']) break;
//				$i++;
//			}
//		}
//		$rt	= str_replace('#rasta-gallery-content#', $rt, $result[0]['block_fix']);
//		
//		$sParams[]	= '#rasta-gallery-jsoptions#';
//		$pValues[]	= $options;
//		$sParams[]	= '#rasta-host-root#';
//		$pValues[]	= '/flsimgs/'.WBSiD;
//		$rt	= str_replace($sParams, $pValues, $rt);
//		return array('files'=>stripslashes($result[0]['files']), 'text'=>stripslashes($rt) );
//		//return array('files'=>$result[0]['files'], 'text'=>$rt);
//	}
	//--------------------------------------------------
	public function	getImageOfGallery($id)
	{
		$sql		= "SELECT  gallery_html FROM wbs_gallery WHERE ".Application_Model_Pubcon::get()." AND gallery_id = ".$id ;
		$result		= $this->DB->fetchAll($sql);

		if(count($result)!=1)	return false;
		
		if(!is_object($this->_XAL))	$this->_XAL	= new Xal_Servlet('SAFE_MODE');
		$result	= $this->_XAL->run('<execution>'.$result[0]['gallery_html'].'</execution>');
		if(!is_array($result['var:gallery'])) return false;
		return $result['var:gallery'];
		
		//$imgmap	= array_filter( explode(",",$result[0]['gallery_html']) );
		//$images	= "";
		//foreach($imgmap as $img)	$images	.= '<img src="/flsimgs/'.WBSiD.'/.thumbs/images/'.$img.'" />';
		//return $images;
	}
	//--------------------------------------------------
	public function	replacePageXML($ID, $newXML)
	{
		$sql		= "SELECT `wb_xml` FROM `wbs_pages` WHERE ".Application_Model_Pubcon::get()." AND `wbs_pages`.`local_id`=". $ID  ;
		$result		= $this->DB->fetchAll($sql);
		if(empty($result[0])) die(Application_Model_Messages::message(404));
		if(empty($result[0]['wb_xml'])) return $newXML;

		$pagexml	=	$result[0]['wb_xml'];
		$xml_new 	= 	new SimpleXMLElement('<root>'.$newXML.'</root>'); 
		$xml_old 	= 	new SimpleXMLElement('<root>'.$pagexml.'</root>'); 			
		$result	= $newXML;
		
		foreach($xml_old->s as $section)
		{	
			$section_id	= (string) $section->attributes()->id;
			$rst	= array('','');
			if($section_id>0) $rst	= $xml_new->xpath('//s[@id='.$section_id.']');
			if (count($rst)==0)
			{
				$result	.=$section->asXML();
			}		
		}
		return 	$result;

	}
	//////////// Block Footer Methods
	public function getBlockFooter($data, $type)
	{
		if(!is_array($data)) return false;
		foreach($data as $key=>$value)
		{
			if($value['setting'] != '0')
			{
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
		$sql	= "SELECT COUNT(`id`) FROM `wbs_content_comment` WHERE `wbs_id`='".WBSiD."' AND `type_id`='".$type."' AND `content_id`='".$unic."' AND `status`='2';";
		$result	= $this->DB->fetchOne($sql);
		$data['count']	= $result;
		$data['link']	= '/comment/index/index/pa/'.$unic.':'.$type;
		return $data;
	}
	//////////// Block Footer Methods END
	//--------------------------------------------------
	public function injectSysParamsValue($input)
	{
		$output	= $input;
		$sysParams	= array();
		$paramsValue= array();

		$sysParams[]	= '#rasta-sitetitle#';
		$paramsValue[]	= $this->site['wb_title'];
		$sysParams[]	= '#rasta-site-description#';
		$paramsValue[]	= $this->site['wb_description'];
		$sysParams[]	= '#rasta-site-keywords#';
		$paramsValue[]	= $this->site['wb_keywords'];
		$sysParams[]	= '#rasta-site-authors#';
		$paramsValue[]	= $this->site['wb_authors'];

		$sysParams[]	= '#rasta-slogantext#';
		$paramsValue[]	= $this->page['wb_page_slogan'];
		$sysParams[]	= '#rasta-pagetitle#';
		$paramsValue[]	= $this->page['wb_page_title'];
		$sysParams[]	= '#rasta-page-description#';
		$paramsValue[]	= $this->page['description'];
		$sysParams[]	= '#rasta-page-keywords#';
		$paramsValue[]	= $this->page['keywords'];
		$sysParams[]	= '#rasta-page-authors#';
		$paramsValue[]	= $this->page['authors'];

		$sysParams[]	= '#rasta-host-root#';
		$paramsValue[]	= $this->docroot.'/'.WBSiD;
		$sysParams[]	= '#rasta-thumbs-root#';
		$paramsValue[]	= $this->docroot.'/'.WBSiD.'/.thumbs';
		

		$sysParams[]	= '#rasta-skinroot#';
		$paramsValue[]	= ($this->skin['wbs_id']>0)?$this->docroot.'/'.WBSiD.'/files'.$this->skin['skin_path']:$this->skin['skin_path'];

		$sysParams[]	= '#rasta-feedlink-rss#';
		$paramsValue[]	= $this->feedlink_rss;
		$sysParams[]	= '#rasta-feedlink-atom#';
		$paramsValue[]	= $this->feedlink_atom;


		$output	= str_replace($sysParams, $paramsValue, $input);
		return $output;
	}
	
}	

?>