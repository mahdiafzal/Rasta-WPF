<?php

class Skiner_SkinController extends Zend_Controller_Action 
{
   public function init() 
    {
		$this->_helper->_layout->setLayout('dashboard');
    }
    public function indexAction()
    {
		$this->_redirect('/skiner/skin/frmlist#fragment-4');
    }
    public function frmlistAction()
    {
    	$DB			= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('a')); 

		$st	= $this->getRequest()->getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 15;
		$pubcon	= Application_Model_Pubcon::get(1000);
		$sql	= 'select * from `wbs_skin` where '. $pubcon .' ORDER BY `skin_id` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from `wbs_skin` where '. $pubcon);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
	public function frmregisterAction()
	{
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('g')); 
		$this->view->assign('wbs_bodies', $this->getWbsBodies()); 
		$this->view->assign('wbs_blocks', $this->getWbsBlocks()); 
		

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/skiner/skin/crt');
		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getSkinForEdit($id);
	
	}
    public function editAction()
    {
		$this->params		= $this->getRequest()->getParams();
		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/skin/frmregister'.$this->newUriParams);
		}
		$data	= $this->prepareRegistration();
		$this->updateSkin($data);
    }		
    public function crtAction()
    {
		$this->params		= $this->getRequest()->getParams();
		$data	= $this->prepareRegistration();
		$this->insertNewSkin($data);
    }		
	public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');

		$id	= $this->getRequest()->getParam('id');
		if(!is_numeric($id)) return false;
		$result	= $this->DB->delete('wbs_skin'	, Application_Model_Pubcon::get(1000).' and `skin_id`="'.addslashes($id).'"');	
		if ($result)	$msg[] = $this->translate->_('ac');
		else			$msg[] = $this->translate->_('ad');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/skiner/skin/frmlist#fragment-4');
    }		


/// Helper Method for Actions -------------------------------------------------------------------*********
    public function getWbsBodies()
    {
		$sql	= 'select `body_id`, `body_title` from `wbs_skin_body` where '. Application_Model_Pubcon::get(1000) .' ORDER BY `body_id` DESC';
		$result	= $this->DB->fetchAll($sql);
		return $result;
	}	
    public function getWbsBlocks()
    {
		$sql	= 'select `id`, `type`, `block_title` from `wbs_skin_block` where '. Application_Model_Pubcon::get(1000) .' ORDER BY `id` DESC';
		$result	= $this->DB->fetchAll($sql);
		return $result;
	}	
    public function getSkinForEdit($id)
    {	
		$this->view->assign('form_action', '/skiner/skin/edit/id/'.$id);
		$sql	= "select * from `wbs_skin` where ".Application_Model_Pubcon::get(1000)." and `skin_id`='".$id."'";
		$result	= $this->DB->fetchAll($sql);
		if (count($result)==1)
		{
			$this->view->assign('data', $this->arrayParams($result[0]) );
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/skiner/skin/frmlist#fragment-4');
		}
    }	
	public function insertNewSkin($data)
	{
		try
		{
			$data['wbs_id']	= WBSiD;
			$this->DB->insert('wbs_skin', $data);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/skiner/skin/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/skin/frmregister'.$this->newUriParams);
		}
	}
	public function updateSkin($data)
	{
		try
		{
			$this->DB->update('wbs_skin', $data , Application_Model_Pubcon::get(1000).' and `skin_id` ='.$this->params['sid']);
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/skiner/skin/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/skin/frmregister'.$this->newUriParams);
		}
	}
	public function arrayParams($result)
	{
		$data	= array();
		$data['sid']		= $result['skin_id'];
		$data['title']		= $result['title'];
		$data['dirfile']	= $result['skin_path'];
		$data['body']		= $result['body_id'];

		$xml 		= new SimpleXMLElement('<root>'.$result['skin_blocks'].'</root>'); 
		
		$data['blocks']['h']	= (string) $xml->h;
		$data['blocks']['m']	= (string) $xml->m;
		
		$nqblocks		= array_merge($xml->xpath('//q'),$xml->xpath('//n'));
		foreach($nqblocks as $value) $nqblockids[] = (string) $value;
		$nqblockids = array_unique($nqblockids);
		foreach($xml->s as $section) foreach($section->xpath('./*') as $block) $B_S[(string)$block][]	= (string)$section->attributes()->id;
		foreach($nqblockids as $nqid) $data['blocks']['nq'][]	= array(
															'block'	=> $nqid,
															'sect'	=> implode(',', $B_S[$nqid])
															);
		return $data;
	}
	public function prepareRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		if(is_array($this->params['blocks']['nq']) and count($this->params['blocks']['nq'])>0)
			foreach($this->params['blocks']['nq'] as $key=>$value)
			{
				$value['block']	= explode('__', $value['block']);
				$this->params['blocks']['nq'][$key]['block']	= $value['block'][0];
				$this->params['blocks']['nq'][$key]['type']		= $value['block'][1];
			}

		$this->setUriParams();
		$this->validate();
		
		$data				= $this->arrayDbData();
		return $data;
	}
	public function genSkinBlocksXml($regBlocks)
	{
		
		$XML	= '';
		if(!empty($regBlocks['h']))	$XML	= '<h>'.$regBlocks['h'].'</h>'.$XML;
		if(!empty($regBlocks['m']))	$XML	= '<m>'.$regBlocks['m'].'</m>'.$XML;
		if(is_array($regBlocks['nq']) and count($regBlocks['nq'])>0)
		{
			foreach($regBlocks['nq'] as $nqBlock)
			{
				if(!preg_match('/^\d+/', $nqBlock['sect'])) $error[]	= $this->translate->_('h');
				$B_sec	= (!empty($nqBlock['sect']))?explode(',', $nqBlock['sect']):array();
				foreach($B_sec as $seID) $S_blo[$seID][]	= array($nqBlock['type'], $nqBlock['block']);
			}
			ksort($S_blo);
			foreach($S_blo as $secid=>$items)
			{
				$XML .='<s id="'.$secid.'">';
				foreach($items as $item)	$XML .='<'.$item[0].'>'.$item[1].'</'.$item[0].'>';
				$XML .='</s>';
			}
		}
		return $XML;
	}
	public function arrayDbData()
	{
		//$data['skin_id']	= $this->params['sid'];
		$data['title']		= $this->params['title'];
		$data['body_id']	= $this->params['body'];
		$data['skin_path']	= $this->params['dirfile'];
		$data['skin_blocks']= $this->genSkinBlocksXml($this->params['blocks']);

		foreach($data as $key=>$value) $data[$key]	= trim($value);
		return $data;
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'title'		=> $this->params['title'],
						'dirfile'	=> $this->params['dirfile'],
						'body'		=> $this->params['body']
			 		 );
		
		$rule=array	(
						'title'		=>'notNull',
						'dirfile'	=>'notNull',
						'body'		=>'body'
					);

		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('title')	== false) $error[]	= $this->translate->_('u'); 
		if($frmValidator->getResult('dirfile')	== false) $error[]	= $this->translate->_('v'); 
		if($frmValidator->getResult('body')		== false) $error[]	= $this->translate->_('w');
		
		$sysError	= ''; 
		if($this->params['sid']!=$this->params['id'])														$sysError	= $this->translate->_('h');
		if(!empty($this->params['sid']) and !is_numeric($this->params['sid']) )  							$sysError	= $this->translate->_('h');
		if(!empty($this->params['blocks']['h']) and !is_numeric($this->params['blocks']['h']) )				$sysError	= $this->translate->_('h');
		if(!empty($this->params['blocks']['m']) and !is_numeric($this->params['blocks']['m']) )				$sysError	= $this->translate->_('h');
		foreach($this->params['blocks']['nq'] as $nq) if(!empty($nq['block']) and !is_numeric($nq['block']))$sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;

		foreach($this->params['blocks']['nq'] as $nq) if(!preg_match('/^\d+/', $nq['sect']))	$sectError	= $this->translate->_('t');
		if(!empty($sectError)) $error[]	= $sectError;

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/skin/frmregister'.$this->newUriParams);
			return false;
		}
		return true;
	}
	public function setUriParams()
	{
		$this->newUriParams =	'';
		if ( preg_match('/^\d+$/', $this->params['id']) )	$this->newUriParams .=	'/id/'.$this->params['id'];
		$this->newUriParams .= '#fragment-4';
	}

}
