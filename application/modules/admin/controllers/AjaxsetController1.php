<?php

class Admin_AjaxsetController extends Zend_Controller_Action
{

	var 	$DB;

    public function init() 
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
		$registry	= Zend_Registry::getInstance();  
		$this->DB	= $registry['front_db'];
    }
	public function indexAction()
	{

	}
	public function savescenarioAction()
	{
		try
		{
			$params		= $this->getRequest()->getParams();
			if($params['sceaction']==1) $properties	= '<c>'.$params['count'].'</c><p>'.$params['paging'].'</p>';
			if($params['sceaction']==2) $properties	= '<c>'.$params['count'].'</c><f>'.$params['paging'].'</f>';
//			if($params['state'] == 'new')
//			{
				$sql 	= "INSERT INTO `wbs_scenario` (`wbs_id` ,`action_id` ,`page_id` ,`uri` ,`title` ,`latin_title` ,`properties`) "
						. "VALUES ( '".WBSiD."', '".$params['sceaction']."', '".$params['page']."', '/".$params['url']."', '"
						. $params['title']."', '".$params['latin']."', '".$properties."');";
				$result	= $this->DB->query($sql);
				$message= 'سناریو جدید ذخیره شد';
//			}
//			elseif( preg_match('/^\d+$/', $params['state']) )
//			{
//				
//				$data				= array();
//				$data['page_id']	= $params['page'];
//				$data['uri']		= '/'.$params['url'];
//				$data['title']		= $params['title'];
//				$data['latin_title']= $params['latin'];
//				$data['properties']	= $properties;
//				$this->DB->update('wbs_scenario', $data, "`wbs_id`='".WBSiD ."' and `id`='".$params['state']."'" );
//				$message= 'اطلاعات سناریو با موفقیت بروزرسانی شد';
//			}
			$this->_helper->json->sendJson(array(true, $message));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
	public function editscenarioAction()
	{
		try
		{
			$params		= $this->getRequest()->getParams();
			if($params['sceaction']==1) $properties	= '<c>'.$params['count'].'</c><p>'.$params['paging'].'</p>';
			if($params['sceaction']==2) $properties	= '<c>'.$params['count'].'</c><f>'.$params['paging'].'</f>';
//			if($params['state'] == 'new')
//			{
//				$sql 	= "INSERT INTO `wbs_scenario` (`wbs_id` ,`action_id` ,`page_id` ,`uri` ,`title` ,`latin_title` ,`properties`) "
//						. "VALUES ( '".WBSiD."', '".$params['sceaction']."', '".$params['page']."', '/".$params['url']."', '"
//						. $params['title']."', '".$params['latin']."', '".$properties."');";
//				$result	= $this->DB->query($sql);
//				$message= 'سناریو جدید ذخیره شد';
//			}
//			elseif( preg_match('/^\d+$/', $params['state']) )
//			{
				
				$data				= array();
				$data['action_id']	= $params['sceaction'];
				$data['page_id']	= $params['page'];
				$data['uri']		= '/'.$params['url'];
				$data['title']		= $params['title'];
				$data['latin_title']= $params['latin'];
				$data['properties']	= $properties;
				$this->DB->update('wbs_scenario', $data, "`wbs_id`='".WBSiD ."' and `id`=".$params['state']."" );
				$message= 'اطلاعات سناریو با موفقیت بروزرسانی شد';
//			}
			$this->_helper->json->sendJson(array(true, $message));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------		
	public function savegalleryAction()
	{
		try
		{
			$images = $this->_getParam('images');
			$title	= $this->_getParam('title');
			if(!is_array($images)) $this->_helper->json->sendJson(array(false, 'خطا!!'));
			$galXAL = '<var:gallery><tree>';
			foreach($images as $image)
			{
				if(!is_array($image)) continue;
				$galXAL .= '<item><tree>';
				foreach($image as $ikey=>$idata)
					$galXAL .= '<item:'.$ikey.'> <![CDATA['.$idata.']]> </item:'.$ikey.'>';
				$galXAL .= '</tree></item>';
			}
			$galXAL .= '</tree></var:gallery>';
			//die($galXAL);
			//foreach($images)
			
			
			//$images	= $this->getRequest()->getParam('images');
			//$title	= $this->getRequest()->getParam('title');
			//$images	= explode(',',$images);
			//array_pop($images);
			//
			//foreach($images as $key=>$value)
			//{
			//	$urlpieces		=  preg_split('/flsimgs\//',$value,2);
			//	$images[$key]	=  '/flsimgs/'.$urlpieces[1];
			//}
			//foreach($images as $value) if(file_exists($_SERVER['DOCUMENT_ROOT'].$value)) $trueImages[] = $value;
			//$urlPath		= '';
			//if(is_array($trueImages))
			//	foreach($trueImages as $value)
			//	{
			//		$urlpieces	=  preg_split('/images\//',$value,2);
			//		$urlPath	.= $urlpieces[1].',';
			//	}
			
			$sql 	=	"insert into `wbs_gallery` (`wbs_id`,`gallery_html`, `gallery_title`) VALUES ('".WBSiD."','".$galXAL."', '".$title."')";
			$this->DB->query($sql);
			$this->_helper->json->sendJson(array(true, 'آلبوم جدید ذخیره شد'));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------	
	public function editgalleryAction()
	{
		try
		{
			$images	= $this->_getParam('images');
			$title	= $this->_getParam('title');
			$id		= $this->_getParam('id');
			if(!is_array($images)) $this->_helper->json->sendJson(array(false, 'خطا!!'));
			$galXAL = '<var:gallery><tree>';
			foreach($images as $image)
			{
				if(!is_array($image)) continue;
				$galXAL .= '<item><tree>';
				foreach($image as $ikey=>$idata)
					$galXAL .= '<item:'.$ikey.'> <![CDATA['.$idata.']]> </item:'.$ikey.'>';
				$galXAL .= '</tree></item>';
			}
			$galXAL .= '</tree></var:gallery>';
			
			
			//$images	= explode(',',$images);
			//array_pop($images);
			//foreach($images as $key=>$value)
			//{
			//	$urlpieces		=  preg_split('/flsimgs\//',$value,2);
			//	$images[$key]	=  '/flsimgs/'.$urlpieces[1];
			//}
			//foreach($images as $value) if(file_exists($_SERVER['DOCUMENT_ROOT'].$value)) $trueImages[] = $value;
			//$urlPath		= '';
			//if(is_array($trueImages))
			//	foreach($trueImages as $value)
			//	{
			//		$urlpieces	=  preg_split('/images\//',$value,2);
			//		$urlPath	.= $urlpieces[1].',';
			//	}
			
			$sql 	=	"UPDATE `wbs_gallery`
						SET `gallery_html`='".$galXAL."', `gallery_title`='".$title."'
						WHERE (`gallery_id` = '".$id."' AND `wbs_id` ='".WBSiD."')";
			$this->DB->query($sql);
			$this->_helper->json->sendJson(array(true, 'اطلاعات آلبوم تصاویر با موفقیت بروزرسانی شد'));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در بروزرسانی: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------	
	public function savemenuAction()
	{
		try
		{
			$menucontent	= $this->getRequest()->getParam('menucontent');
			$title			= $this->getRequest()->getParam('title');
			$XML 			= new SimpleXMLElement("<root>". stripslashes($menucontent) ."</root>");
			$types = array(
							'page'=>1,
							'rtc'=>2,
							'extlink'=>3,
							'gallery'=>4,
							'scenario'=>5
							);
			$finalXml	='';
			foreach($XML->li as $li)
			{
				$urlAttr = (string) $li->a->attributes()->url;
				$data		= explode('/',$urlAttr);
				if(is_string($data[1]) && strlen($data[1])>1) $type= $types[ $data[1] ];
				$finalXml	.= '<l t="'.$type.'" i="'.$data[2].'">';
				if( count($li->div->ul->li)>0)
				{
					foreach($li->div->ul->li as $li2)
					{
						$urlAttr = (string) $li2->a->attributes()->url;
						$data		= explode('/',$urlAttr);
						if(is_string($data[1]) && strlen($data[1])>1) $type= $types[ $data[1] ];
						$finalXml	.= '<l t="'.$type.'" i="'.$data[2].'">';
						if( count($li2->div->ul->li)>0)
						{
							foreach($li2->div->ul->li as $li3)
							{
								$urlAttr = (string) $li3->a->attributes()->url;
								$data		= explode('/',$urlAttr);
								if(is_string($data[1]) && strlen($data[1])>1) $type= $types[ $data[1] ];
								$finalXml	.= '<l t="'.$type.'" i="'.$data[2].'">';
								$finalXml	.= '</l>';
							}
						}
						$finalXml	.= '</l>';
					}
				}
				$finalXml	.= '</l>';
			}
		
			$sql 	=	"insert into `wbs_menu`
						(`wbs_id`,`content`, `menu_title`)
						VALUES ('".WBSiD."','".$finalXml."', '".$title."')";
			$this->DB->query($sql);
			$this->_helper->json->sendJson(array(true, 'منوی جدید ذخیره شد'));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------
	public function replacemenuAction()
	{
		try
		{
			$menucontent	= $this->getRequest()->getParam('menucontent');
			$title			= $this->getRequest()->getParam('title');
			$menuid			= $this->getRequest()->getParam('menuid');
			
			$XML 			= new SimpleXMLElement("<root>". stripslashes($menucontent)."</root>");
			$types = array(
							'page'=>1,
							'rtc'=>2,
							'extlink'=>3,
							'gallery'=>4,
							'scenario'=>5
							);
			$finalXml	='';
			
			foreach($XML->li as $li)
			{
				$urlAttr = (string) $li->a->attributes()->url;
				$data		= explode('/',$urlAttr);
				if(is_string($data[1]) and strlen($data[1])>1) $type= $types[ $data[1] ];
				$finalXml	.= '<l t="'.$type.'" i="'.$data[2].'">';
				if( count($li->div->ul->li)>0)
				{
					foreach($li->div->ul->li as $li2)
					{
						$urlAttr = (string) $li2->a->attributes()->url;
						$data		= explode('/',$urlAttr);
						if(is_string($data[1]) and strlen($data[1])>1) $type= $types[ $data[1] ];
						$finalXml	.= '<l t="'.$type.'" i="'.$data[2].'">';
						if( count($li2->div->ul->li)>0)
						{
							foreach($li2->div->ul->li as $li3)
							{
								$urlAttr = (string) $li3->a->attributes()->url;
								$data		= explode('/',$urlAttr);
								if(is_string($data[1]) and strlen($data[1])>1) $type= $types[ $data[1] ];
								$finalXml	.= '<l t="'.$type.'" i="'.$data[2].'">';
								$finalXml	.= '</l>';
							}
						}
						$finalXml	.= '</l>';
					}
				}
				$finalXml	.= '</l>';
			}
			$sql 	=	"UPDATE `wbs_menu`
						SET `content`='".$finalXml."', `menu_title`='".$title."'
						WHERE (`id` = '".$menuid."' AND `wbs_id` ='".WBSiD."')";
			$this->DB->query($sql);
			$this->_helper->json->sendJson(array(true, 'اطلاعات منو با موفقیت بروزرسانی شد'));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در بروزرسانی: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------
	public function savepageAction()
	{
		$title		= $this->getRequest()->getParam('title');
		
		$sql1 		= "SELECT MAX(local_id)+1 as pagenum FROM `wbs_pages` WHERE `wbs_id` ='".WBSiD."'";
		$pagenum	= $this->DB->fetchone($sql1);

		//$sql 		= "insert into `wbs_pages` (`wbs_id`, `local_id`, `wb_page_title`, `header_menu_path`) VALUES ('".WBSiD."', ".$pagenum .", '".$title."', '4.1')";
		$sql 		= "insert into `wbs_pages` (`wbs_id`, `local_id`, `wb_page_title`) VALUES ('".WBSiD."', ".$pagenum .", '".$title."')";
		$result		= $this->DB->query($sql);
		
		if($result)
		{
			$this->_helper->json->sendJson(array(true, 'صفحه جدید ذخیره شد'));
		}
		else
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------
	public function replacepageAction()
	{
		$title		= $this->getRequest()->getParam('title');
		$pageid		= $this->getRequest()->getParam('pageid');

		$sql 		= "UPDATE `wbs_pages` SET `wb_page_title`='".$title."' WHERE (`local_id` = '".$pageid."' AND `wbs_id` ='".WBSiD."')";
		$result		= $this->DB->query($sql);

		
		if($result)
		{
			$this->_helper->json->sendJson(array(true, 'اطلاعات صفحه با موفقیت بروزرسانی شد'));
		}
		else
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در بروزرسانی: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------
	public function savelinkAction()
	{
		$extlinktitle		= $this->getRequest()->getParam('extlinktitle');
		$extlinkurl			= $this->getRequest()->getParam('extlinkurl');

		$sql 		= "insert into `wbs_links` (`wbs_id`, `url`, `title`) VALUES ('".WBSiD."', '".$extlinkurl."', '".$extlinktitle."')";
		$result		= $this->DB->query($sql);

		if($result)
		{
			$this->_helper->json->sendJson(array(true, 'پیوند جدید ذخیره شد'));
		}
		else
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------
	public function replacelinkAction()
	{
		$extlinktitle	= $this->getRequest()->getParam('extlinktitle');
		$extlinkurl		= $this->getRequest()->getParam('extlinkurl');
		$extlinkid		= $this->getRequest()->getParam('extlinkid');

		$sql 		= "UPDATE `wbs_links` SET `title`='".$extlinktitle."', `url`='".$extlinkurl."' WHERE (`id` = '".$extlinkid."' AND `wbs_id` ='".WBSiD."')";
		$result		= $this->DB->query($sql);

		if($result)
		{
			$this->_helper->json->sendJson(array(true, 'اطلاعات پیوند با موفقیت بروزرسانی شد'));
		}
		else
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
//---------------------------------------------------------------------------
	public function savepagecontentAction()
	{			
		try
		{

		$content		=$this->_getParam('content');
		$pageID			=$this->_getParam('pageID');
		$slogan			=$this->_getParam('slogan');
		$authors		=$this->_getParam('authors');
		$description	=$this->_getParam('description');
		$keywords		=$this->_getParam('keywords');
//		$skin			=explode('.',$this->getRequest()->getParam('skin'));
		$skin			=$this->_getParam('skin');
		$menuskin		=$this->_getParam('menuskin');
		$pagedir		=$this->_getParam('pagedir');
		
		if(empty($menuskin)) $menuskin= '4.1';
//		$this->_helper->json->sendJson( array(false, stripslashes($content) ) );

		$XML	= new SimpleXMLElement('<root>'.stripslashes($content).'</root>');
		
		$pageXAL_m = $pageXAL_c = '';

		//$xmlResult='';
		foreach($XML->children() as $sec)
		{
			if ($sec->attributes()->class=='headermenu')
			{
				//$xmlResult	.= '<m>'.$sec->attributes()->unic.'</m>';
				$pageXAL_m = "\n<var:headermenu><tree><item:default><tree><item:id>".$sec->attributes()->unic."</item:id></tree></item:default></tree></var:headermenu>";
			}
			else
			{
				//$xmlResult	.= '<s id="'.$sec->attributes()->section.'">';
				$section_id = $sec->attributes()->section;
				$rank = 1;
				foreach($sec->children() as $itm)
				{
					$type= $itm->attributes()->type;
					
					$type = ($type=='rtc')?'t':( ($type=='gallery')?'g':( ($type=='menu')?'q':( ($type=='scenario')?'s':NULL ) ) );
					$unic = $itm->attributes()->unic;
					
					//if($type=='rtc')
					//{
					//	$xmlResult	.= '<t>'.$itm->attributes()->unic.'</t>';
					//}
					//else if($type=='gallery')
					//{
					//	$xmlResult	.= '<g>'.$itm->attributes()->unic.'</g>';
					//}
					//else if($type=='menu')
					//{
					//	$xmlResult	.= '<q>'.$itm->attributes()->unic.'</q>';
					//}
					$senarioPart = ($type=='s')?"<item:data.fields>id, title1</item:data.fields><item:skin.block>metablock</item:skin.block>":"";
					$pageXAL_c .= "\n<item><tree><item:ns></item:ns><item:type>".$type."</item:type><item:id>".$unic."</item:id><item:container>".$section_id."</item:container><item:rank>".$rank."</item:rank>".$senarioPart."</tree></item>";
					$rank ++;
					
				}
				//$xmlResult	.= '</s>';
			}
		}
		//$pageXAL_p = "<var:page><tree><item:dir>".$pagedir."</item:dir></tree></var:page>";
		$pageXAL	= "<var:page><tree><item:dir>".$pagedir."</item:dir></tree></var:page>" 
					. $pageXAL_m 
					. "\n<var:contents><tree>".$pageXAL_c."\n</tree></var:contents>";

		//$content		= new Application_Model_Content();
		//$finalresult	= $content->replacePageXML($pageID,$xmlResult);
		
			//$result = $this->DB->fetchRow('select `skin_id` from `wbs_skin` where `body_id`="'.$skin[0].'" and `theme_id`="'.$skin[1].'"');
			$data=array();
			//$data['wb_xml']				= $finalresult;
			$data['wb_xml']				= $pageXAL;
			$data['skin_id']			= $skin;
			$data['wb_page_slogan']		= $slogan;
			//$data['header_menu_path']	= $menuskin;
			$data['authors']			= $authors;
			$data['description']		= $description;
			$data['keywords']			= $keywords;
			$data['page_dir']			= $pagedir;

			$this->DB->update('wbs_pages',$data ,"`wbs_id`='".WBSiD ."' and `local_id`='".$pageID."'");
			$this->_helper->json->sendJson(array(true,'محتوای صفحه با موفقیت ذخیره شد'));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
	public function savepageskinAction()
	{			
		try
		{
			$pageID			= $this->getRequest()->getParam('pageID');
//			$skin			=explode('.',$this->getRequest()->getParam('skin'));
			$data['skin_id']= $this->getRequest()->getParam('skin');
			//$result = $this->DB->fetchRow('select `skin_id` from `wbs_skin` where `body_id`="'.$skin[0].'" and `theme_id`="'.$skin[1].'"');
			//$data=array();
			//$data['skin_id']	= $result['skin_id'];
			$this->DB->update('wbs_pages',$data ,"`wbs_id`='".WBSiD ."' and `local_id`='".$pageID."'");
			$this->_helper->json->sendJson(array(true,'پوسته صفحه با موفقیت ذخیره شد'));
		}
		catch(zend_exception $e)
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}




}

