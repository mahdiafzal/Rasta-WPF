<?php
class Admin_AjaxgetController extends Zend_Controller_Action
{
    public function init()
    {
		if(! $this->_request->isXmlHttpRequest()) die(); 
    }
	public function getscenariodataAction()
	{
		$registry	= Zend_Registry::getInstance();  
		$DB			= $registry['front_db'];
		$params		= $this->getRequest()->getParams();
		$sql 	= "SELECT * FROM `wbs_scenario` WHERE (`id` = '".$params['state']."' AND `wbs_id` ='".WBSiD."')";
		$result	= $DB->fetchRow	($sql);	
		if($result)
		{
			$properxml	=	'<root>'.$result['properties'].'</root>';
			$xml 		= 	new SimpleXMLElement($properxml); 			
			$count	= (int) $xml->c;
			if($result['action_id']==1) $paging	= (string) $xml->p;
			if($result['action_id']==2) $paging	= (string) $xml->f;
			$result['uri']	= preg_replace('/^\//', '', $result['uri']);
			$response		= array($result['title'], $result['latin_title'], $result['uri'], $result['page_id'], $count, $paging, $result['action_id']);
			$this->_helper->json->sendJson(array(true, $response));
		}
		else
		{
			$this->_helper->json->sendJson(array(false, 'اشکال در فرایند ذخیره: دوباره تلاش کنید'));
		}
	}
	public function scenariolistAction()
	{
		$this->_helper->layout()->disableLayout();
		$content= new Application_Model_Content();
		$str	= strtolower($_SERVER['REQUEST_URI']);
		$uri	= explode('/ajaxget/scenariolist/',$str);

		if (preg_match('/^[0-9]+$/',$uri[1]))
		{
			$start		= $uri[1];			
		}
		else
		{
			$start		=0;			
		}
		$this->view->assign('result',$content->getSiteScenarios($start));
		$this->view->assign('count',$content->getCount('wbs_scenario'));

	}
	//-----------------------------------------------------------------
	public function rtclistAction()
	{
		$this->_helper->layout()->disableLayout();
		$content= new Application_Model_Content();
		$str	= strtolower($_SERVER['REQUEST_URI']);
		$uri	= explode('/ajaxget/rtclist/',$str);

		if (preg_match('/^[0-9]+$/',$uri[1]))
		{
			$start		= $uri[1];			
		}
		else
		{
			$start		=0;			
		}
		$this->view->assign('result',$content->getSiteRTCs($start));
		$this->view->assign('count',$content->getCount('wbs_rtcs'));

	}
	//-----------------------------------------------------------------
	public function gallerylistAction()
	{
		$this->_helper->layout()->disableLayout();
		$content= new Application_Model_Content();
		$str	= strtolower($_SERVER['REQUEST_URI']);
		$uri	= explode('/ajaxget/gallerylist/',$str);

		if (preg_match('/^[0-9]+$/',$uri[1]))
		{
			$start		= $uri[1];			
		}
		else
		{
			$start		=0;			
		}
		$this->view->assign('result',$content->getGalleryList($start));
		$this->view->assign('count',$content->getCount('wbs_gallery'));
	}
	//-----------------------------------------------------------------
	public function menulistAction()
	{
		$this->_helper->layout()->disableLayout();
		$content= new Application_Model_Content();
		$str	= strtolower($_SERVER['REQUEST_URI']);
		$uri	= explode('/ajaxget/menulist/',$str);

		if (preg_match('/^[0-9]+$/',$uri[1]))
		{
			$start		= $uri[1];			
		}
		else
		{
			$start		=0;			
		}
		$this->view->assign('result',$content->getMenuList($start));
		$this->view->assign('count',$content->getCount('wbs_menu'));
	}
	//-----------------------------------------------------------------
	public function pagelistAction()
	{
		$this->_helper->layout()->disableLayout();
		$content= new Application_Model_Content();
		$str	= strtolower($_SERVER['REQUEST_URI']);
		$uri	= explode('/ajaxget/pagelist/',$str);

		if (preg_match('/^[0-9]+$/',$uri[1]))
		{
			$start		= $uri[1];			
		}
		else
		{
			$start		= 0;			
		}
		$this->view->assign('result',$content->getPagelist($start));
		$this->view->assign('count',$content->getCount('wbs_pages'));
	}
	//-----------------------------------------------------------------
	public function extlinklistAction()
	{
		$this->_helper->layout()->disableLayout();
		$content= new Application_Model_Content();
		$str	= strtolower($_SERVER['REQUEST_URI']);
		$uri	= explode('/ajaxget/extlinklist/',$str);

		if (preg_match('/^[0-9]+$/',$uri[1]))
		{
			$start		= $uri[1];			
		}
		else
		{
			$start		=0;			
		}
		$this->view->assign('result',$content->getExtlinklist($start));
		$this->view->assign('count',$content->getCount('wbs_links'));
	}
	//-----------------------------------------------------------------
	public function autocompleteAction()
	{
		$param	= $this->getRequest()->getParam('t');
		$content= new Application_Model_Content();
		$data	= $content->getSerchResult($param , $_REQUEST['term']);
		//echo $data;
		$this->_helper->json->sendJson($data);
	}
	//-----------------------------------------------------------------
	public function gettingdataAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		$type	= $this->getRequest()->getParam('type');
		$section= $this->getRequest()->getParam('section');
		$id		= $this->getRequest()->getParam('id');
		$skin_id= $this->getRequest()->getParam('body_id');
		$page_id= $this->getRequest()->getParam('page_id');

		$content= new Application_Model_Content($page_id, $skin_id);
		$type	= strtolower($type);
		if($type=='rtc')
		{
			$result	= '';
			$block	= $content->getBlock('n', $section, $skin_id) ; 
			if($rtc	= $content->getRtc($id))		
				$result	= $this->merg($block,$rtc,'rtc');
		}
		elseif ($type=='menu')
		{
			if ($section=='headermenu')
			{
				//$content= new Application_Model_Content();
				$block	= $content->getBlock	('m', NULL, $skin_id) ; 
				$headerMenu	= $content->getEditHeaderMenu($id	,$page_id, $block);
				$result 	= $headerMenu['content'];
			}
			else
			{
				$block	= $content->getBlock('q', $section, $skin_id) ; 
//				$menu	= $content->getMenu 	($id, $block['block'], $page_id);	
				$menu	= $content->getMenu($id, $block, $page_id);	
				$skin	= array('block'=> trim($menu['block']), 'skin_path'=> '');
				$result	= $this->merg($skin, $menu, 'menu');
			}
		}
		elseif ($type=='gallery')
		{
			$block	= $content->getBlock	('n',$section,$skin_id) ; 
			$gallery= $content->getGallery	($id);	
			$result	= $this->merg	($block, $gallery, 'gallery');
		}
		echo $content->injectSysParamsValue($result);
	}
	//-----------------------------------------------------------------
	public function merg($skin,$content,$type)
	{
		$block	= $skin['block'];
		
		$sysParams[]	= '#rasta-blockcontent#';
		$paramsValue[]	= $content['content'];
		$sysParams[]	= '#rasta-blockcontent-abstract#';
		$paramsValue[]	= $content['abstract'];
		$sysParams[]	= '#rasta-blockheader#';
		$paramsValue[]	= $content['title'];
		$sysParams[]	= '#rasta-blockheader2#';
		$paramsValue[]	= @$content['title2'];

		$sysParams[]	= '#rasta-type#';
		$paramsValue[]	= $type;
		$sysParams[]	= '#rasta-unic#';
		$paramsValue[]	= $content['id'];

		$sysParams[]	= '#rasta-content-author#';
		$paramsValue[]	= @$content['author'];
		$sysParams[]	= '#rasta-content-date#';
		$paramsValue[]	= @$content['date'];
		$sysParams[]	= '#rasta-content-time#';
		$paramsValue[]	= @$content['time'];
		$sysParams[]	= '#rasta-content-comment-count#';
		$paramsValue[]	= @$content['comment']['count'];
		$sysParams[]	= '#rasta-content-comment-link#';
		$paramsValue[]	= @$content['comment']['link'];


		$sysParams[]	= '#rasta-content-author-display#';
		$paramsValue[]	= (empty($content['author']))?'display:none;':'';
		$sysParams[]	= '#rasta-content-date-display#';
		$paramsValue[]	= (empty($content['date']))?'display:none;':'';
		$sysParams[]	= '#rasta-content-time-display#';
		$paramsValue[]	= (empty($content['time']))?'display:none;':'';
		$sysParams[]	= '#rasta-content-comment-display#';
		$paramsValue[]	= (empty($content['comment']['link']))?'display:none;':'';

		$result	= str_replace($sysParams, $paramsValue, $block);

		return $result;

		
//		$xml 		= new SimpleXMLElement('<root>'.$skin['skin_path'].'</root>'); 
//		$skin['base']	= (string) $xml->b;
//		$skin['root']	= (string) $xml->r;

//		if($type=='rtc')			$result	= preg_replace('/#rasta-type#/'	,'rtc'		,$block);
//		elseif ($type=='menu')		$result	= preg_replace('/#rasta-type#/'	,'menu'		,$block);
//		elseif ($type=='gallery')	$result	= preg_replace('/#rasta-type#/'	,'gallery'	,$block);
//		
//		$result	= preg_replace('/\#rasta\-unic\#/'			,$content['id']		,$result);
//		$result	= preg_replace('/\#rasta\-blockheader\#/'	,$content['title']	,$result);
//		$result	= preg_replace('/\#rasta\-blockcontent\#/'	,$content['content'],$result);
//		$result	= preg_replace('/\#rasta\-blockcontent\#/'	,$content['content'],$result);
//		$result	= preg_replace('/\#rasta\-skinbase\#/'		,$skin['base']		,$result);
//		$result	= preg_replace('/\#rasta\-skinroot\#/'		,$skin['root']		,$result);
	}
	//-----------------------------------------------------------------
	public function getgallerypicAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$id		= $this->_getParam('id');
		$content= new Application_Model_Content();

		//echo $content->getImageOfGallery($id);
		$result = $content->getImageOfGallery($id);
		if( is_array($result) )
			$this->_helper->json->sendJson( $result );
		else
			$this->_helper->json->sendJson( array() );
	}
	//-----------------------------------------------------------------
	public function geteditmenuAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();

		$page_id= $this->getRequest()->getParam('page_id');
		$id		= $this->getRequest()->getParam('id');

		$content= new Application_Model_Content();
		$a		= $content->getEditMenu($id	,$page_id);
		echo $a['content'];
		//$this->_helper->json->sendJson($a);
	}
	//-----------------------------------------------------------------
	public function	getdataofpageAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();		

		$registry	= Zend_Registry::getInstance();  
		$DB			= $registry['front_db'];

		$pageID			=$this->getRequest()->getParam('pageID');

		$result				= $DB->fetchRow('select * from `wbs_pages` where `wbs_id`='. WBSiD .' and `local_id`=' . $pageID);
		$data['slogan']		= $result['wb_page_slogan'];
		$data['title']		= $result['wb_page_title'];
		$data['mlmskin']	= $result['header_menu_path'];
		$data['authors']	= $result['authors'];
		$data['description']= $result['description'];
		$data['keywords']	= $result['keywords'];
		$data['page_dir']	= $result['page_dir'];
		$data['skin']		= $result['skin_id'];
//		if ($result['skin_id']==0)
//		{
//			$data['skin']	= 0;
//		}
//		else
//		{
//			$skin_id		= $result['skin_id'];
//			$result			= $DB->fetchRow('select `body_id`,`theme_id` from `wbs_skin` where `skin_id`='. $skin_id);
//			$data['skin']	= $result['body_id'].'.'.$result['theme_id'];
//		}
//		$result				= $DB->fetchRow('select `wb_authors`,`wb_description`,`wb_keywords` from `wbs_profile` where `wb_id`='. WBSiD);
//		$data['authors']	= $result['wb_authors'];
//		$data['description']= $result['wb_description'];
//		$data['keywords']	= $result['wb_keywords'];

		$this->_helper->json->sendJson($data);
	}
	//--------------------------------------------------
}

?>
