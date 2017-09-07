<?php
class Application_Model_Helper_Page
{
	public static function	parseMlMenu($data, $mode=NULL)
	{
		$menuXML	= $data['xml'];
		$DB			= $data['db'];
		$MenuTemp	= $data['temp'];
		$pageID		= $data['page'];

		$XML	= 	new SimpleXMLElement($menuXML); 
		$types = array(
						1=>'page',
						2=>'rtc',
						3=>'extlink',
						4=>'gallery',
						5=>'scenario'
						);
		foreach( $types as $tkey=>$tvalue)
		{
			$result	= $XML->xpath('//l[@t='.$tkey.']/@i');
			if(is_array($result))
				foreach($result as $id)
				{
					$AllId[$tvalue][]	= (integer) $id ;
				}
		}
		/* Page */
		if(!empty($AllId['page']))
		{
			$sql			= "SELECT local_id,wb_page_title,name FROM wbs_pages WHERE ".Application_Model_Pubcon::get(1110)." AND `local_id` IN(". implode(',', $AllId['page']) .")";
			$result	= $DB->fetchAll($sql);
			
			foreach($result as $value)
			{
				$AllTitle['page'][ $value['local_id'] ] = $value['wb_page_title'];
				$pageNames[$value['local_id']] = $value['name'];
			}
		}
		/* RTC */
		if(!empty($AllId['rtc']))
		{
			$sql		= 'SELECT `id`, `ltn_name`, `title` FROM `wbs_rtcs` WHERE '.Application_Model_Pubcon::get(1110).' AND `id` IN('. implode(',', $AllId['rtc']) .")";
			$result	= $DB->fetchAll($sql);
			foreach($result as $value)	$AllTitle['rtc'][ $value['id'] ] = $value['title'] ;
		}
		/* extlink */
		$linksURL	= array();
		if(!empty($AllId['extlink']))
		{
			$sql		= 'SELECT `id`, `title`, `url` FROM `wbs_links` WHERE '.Application_Model_Pubcon::get().' AND `id` IN('. implode(',', $AllId['extlink']) .")";
			$result	= $DB->fetchAll($sql);
			foreach($result as $value)
			{
				$AllTitle['extlink'][ $value['id'] ] = $value['title'] ;
				$linksURL [$value['id']] = $value['url'] ;
			}
		}
		/* gallery */
		if(!empty($AllId['gallery']))
		{
			$sql		= 'SELECT `gallery_id`, `gallery_title` FROM `wbs_gallery` WHERE '.Application_Model_Pubcon::get(1110)
						. ' AND `gallery_id` IN('. implode(',', $AllId['gallery']) .")";
			$result	= $DB->fetchAll($sql);
			foreach($result as $value)	$AllTitle['gallery'][ $value['gallery_id'] ] = $value['gallery_title'] ;
		}
		/* scenario */
		if(!empty($AllId['scenario']))
		{
			//$sql		= 'SELECT `id`, `title`, `uri` FROM `wbs_scenario` WHERE '.Application_Model_Pubcon::get(1110).' AND `id` IN('. implode(',', $AllId['scenario']) .")";
			//$result	= $DB->fetchAll($sql);
			//foreach($result as $value)
			//{
			//	$AllTitle['scenario'][ $value['id'] ]	= $value['title'];
			//	$ScenUri[$value['id']]					= $value['uri'];
			//}
		}

		$bData	= array('p'=>$types ,'l'=>$linksURL ,'t'=>$AllTitle );
		if(!empty($mode))		$bData['mo']	= $mode;
		if(!empty($ScenUri))	$bData['sc']	= $ScenUri;
		if(!empty($pageNames))	$bData['pn']	= $pageNames;

		foreach($XML->l as $li)
			if($menuLi1 = self::liOperation($li, $MenuTemp[0], $bData) )
			{
				$subMenu2='';
				if(count($li->l)>0 and !empty($MenuTemp[1]))
				{
					foreach($li->l as $li2)
						if($menuLi2 = self::liOperation($li2,  $MenuTemp[2], $bData) )
						{
							$subMenu3	='';
							if(count($li2->l)>0 and !empty($MenuTemp[3]))
							{
								foreach($li2->l as $li3)
									if($menuLi3 = self::liOperation($li3,  $MenuTemp[4], $bData) )
										$subMenu3	.= str_replace('#rasta-submenu#', '', $menuLi3);
								$subMenu3	= trim($subMenu3);
								if(! empty($subMenu3) ) $subMenu3	= preg_replace('/\#rasta-submenuContent\#/', $subMenu3, $MenuTemp[3]);
							}
							$subMenu2	.= preg_replace('/\#rasta-submenu\#/', $subMenu3, $menuLi2);
						}
					$subMenu2	= trim($subMenu2);
					if(! empty($subMenu2) ) $subMenu2 = preg_replace('/\#rasta-submenuContent\#/', $subMenu2, $MenuTemp[1]);
				}
				$menuLi1 = str_replace('#rasta-submenu#', $subMenu2, $menuLi1);
				$menuContent[]	=  $menuLi1;
			}

		return $menuContent;
	}

	/// helper methods
	protected function liOperation($li, $MenuTemp, $bData)
	{
			$bu	= '';
			if(!empty($bData['mo']) and $bData['mo']=='admin') $bu	= '#';
			
			$typeKey = (integer) $li->attributes()->t;
			if($typeKey==5) return;
			$titleId = (integer) $li->attributes()->i;

			$title	= $bData['t'][ $bData['p'][$typeKey] ][ $titleId ];
			if(empty($title)) return false;

			
			$href	= '/'.$bData['p'][$typeKey].'/'.$titleId;
			
			if(empty($bData['mo']) or $bData['mo']!='editmenu')
			{
				if($bData['p'][$typeKey]=='extlink')	$href	= $bData['l'][$titleId];
				if($bData['p'][$typeKey]=='scenario')	$href	= $bData['sc'][$titleId];
				if($bData['p'][$typeKey]=='page' and 
					strlen($bData['pn'][$titleId])>0)	$href	= '/page/'.$bData['pn'][$titleId];
				if($href==$_SERVER['REQUEST_URI'])		$href	= '#';
			}
			$href	= $bu.$href;

			$menuLi = str_replace(array('#rasta-linkhref#', '#rasta-linktitle#'), array($href, $title), $MenuTemp);
			
			return $menuLi;
	}

}
?>