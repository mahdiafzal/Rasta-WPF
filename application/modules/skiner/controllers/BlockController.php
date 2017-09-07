<?php
 
class Skiner_BlockController extends Zend_Controller_Action 
{
   public function init() 
    {
		$this->_helper->_layout->setLayout('dashboard');
    }
    public function indexAction()
    {
		$this->_redirect('/skiner/block/frmlist#fragment-4');
    }
    public function frmlistAction()
    {
    	$DB			= Zend_Registry::get('front_db');
		$translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $translate);
		$this->view->assign('title_site', $translate->_('a')); 

		$st	= $this->getRequest()->getParam('st');
		if ((isset($st)) and (preg_match('/^[0-9]+$/',$st)))	$start	= $st;	else	$start	= 0;
		
		$limit	= 25;
		$pubcon	= Application_Model_Pubcon::get(1000);
		$sql	= 'select * from `wbs_skin_block` where '. $pubcon .' ORDER BY `id` DESC limit '.$start.','.$limit;
		$result	= $DB->fetchAll($sql);
		$count	= $DB->fetchAll('select count(*) as `cnt` from `wbs_skin_block` where '. $pubcon);
		
		$this->view->assign('data'	, $result);
		$this->view->assign('count'	, $count[0]['cnt']);
		$this->view->assign('start'	, $start);
		$this->view->assign('limit'	, $limit);
		
		$message	= $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0]))	$this->view->assign('msg'	, $message[0]);	
    }
	public function frmregisterAction()
	{
		$this->translate	= Zend_Registry::get('translate');
		$this->view->assign('translate', $this->translate);
		$this->view->assign('title_site', $this->translate->_('g')); 

		$message =  $this->_helper->flashMessenger->getMessages();
		if(!empty($message[0])){$this->view->assign('msg'	,$message[0]);}
		if(!empty($message[1])){$this->view->assign('data'	,$message[1]);}
		if(!empty($message[2])){$this->view->assign('form_action'	,$message[2]);}
		if (!empty($message[1])) return true;

		$this->view->assign('form_action', '/skiner/block/crt');
		
		$id	=$this->getRequest()->getParam('id');
		if (!empty($id) and preg_match('/^\d+$/', $id)) $this->getBlockForEdit($id);
	
	}
    public function editAction()
    {
		$this->params		= $this->getRequest()->getParams();

		if(!is_numeric($this->params['id']))
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('h') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/block/frmregister'.$this->newUriParams);
		}
		
		$data	= $this->prepareRegistration();
		$this->updateBlock($data);
    }		
    public function crtAction()
    {
		$this->params		= $this->_getAllParams();
		$data	= $this->prepareRegistration();
		$this->insertNewBlock($data);
    }		
	public function delAction()
    {
		$this->_helper->viewRenderer->setNoRender();
    	$this->DB			= Zend_Registry::get('front_db');
		$this->translate	= Zend_Registry::get('translate');

		$id	= $this->getRequest()->getParam('id');
		if(!is_numeric($id)) return false;
		$result	= $this->DB->delete('wbs_skin_block', Application_Model_Pubcon::get(1000).' and `id`="'.$id.'"');	
		if ($result)	$msg[] = $this->translate->_('ac');
		else			$msg[] = $this->translate->_('ad');
		$this->_helper->FlashMessenger($msg);
		$this->_redirect('/skiner/block/frmlist#fragment-4');
    }		


/// Helper Method for Actions -------------------------------------------------------------------*********
    public function getBlockForEdit($id)
    {	
    	$DB			= Zend_Registry::get('front_db');
		$this->view->assign('form_action', '/skiner/block/edit/id/'.$id);
		//$sql	= "select * from `wbs_skin_block` where ".Application_Model_Pubcon::get(1000). " and `id`='".$id."'";
		$sql	= "SELECT * FROM `wbs_skin_block` AS bc LEFT JOIN `wbs_skin_block_meta` AS bm ON `bm`.`bm_bc_id`=`bc`.`id` WHERE "
				. Application_Model_Pubcon::get(1000). " AND `bc`.`id`=".addslashes($id);
		$result	= $DB->fetchAll($sql);
		if (count($result)==1)
		{
			$this->view->assign('data', $this->arrayParams($result[0]) );
		}
		else
		{
			$msg[]= $this->translate->_('j');
			$this->_helper->FlashMessenger($msg);
			$this->_redirect('/skiner/block/frmlist#fragment-4');
		}
			
    }	
	public function insertNewBlock($data)
	{
		//print_r($data); die();
		try
		{
			$this->DB->beginTransaction();
			
			$data[0]['wbs_id']	= WBSiD;
			$this->DB->insert('wbs_skin_block', $data[0]);
			$recordID	= $this->DB->lastInsertId();
			//add metadata
			if ( is_array($data[1]) and strlen( trim($data[1]['bm_code']) )>5 )
			{
				$data[1]['bm_bc_id']	= $recordID;
				$this->DB->insert('wbs_skin_block_meta',$data[1]);
			}
			//end of add metadata
			$this->DB->commit();

			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('z') ));
			$this->_redirect('/skiner/block/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('aa') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/block/frmregister'.$this->newUriParams);
		}
	}
	public function updateBlock($data)
	{
		try
		{
			$this->DB->beginTransaction();
			$this->params['bid']	= addslashes($this->params['bid']);
			$this->DB->update('wbs_skin_block'	, $data[0] , Application_Model_Pubcon::get(1000).' and `id` ='.$this->params['bid'] );
			
			$rr=$this->DB->fetchOne('SELECT COUNT(*) FROM wbs_skin_block_meta WHERE `bm_bc_id`='.$this->params['bid'] );
			if ($rr!=0)
			{
				if(!is_array($data[1]))	$data[1] = array('bm_type'=>'', 'bm_code'=>'');
				$this->DB->update('wbs_skin_block_meta',$data[1] , '`bm_bc_id` ='.$this->params['bid'] );
			}
			elseif ( is_array($data[1]) and strlen( trim($data[1]['bm_code']) )>5 )
			{
				$data[1]['bm_bc_id']	= $this->params['bid'];
				$this->DB->insert('wbs_skin_block_meta', $data[1] );
			}
			//end of add metadata
			$this->DB->commit();
			
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('y') ));
			$this->_redirect('/skiner/block/frmlist'.$this->newUriParams );
		}
		catch(Zend_exception $e)
		{
			$this->_helper->flashMessenger->addMessage(array( $this->translate->_('x') ));
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/block/frmregister'.$this->newUriParams);
		}
	}
	public function arrayParams($result)
	{
		$data	= array();
		$data['bid']		= $result['id'];
		$data['title']		= $result['block_title'];
		$data['type']		= $result['type'];
		$data['block']		= $result['block'];
		$data['meta_type']	= $result['bm_type'];
		$data['meta_block']	= $result['bm_code'];
		return $data;
	}
	public function prepareRegistration()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
    	$this->DB			= Zend_registry::get('front_db');
    	$this->translate	= Zend_registry::get('translate');

		$this->setUriParams();
		$this->validate();
		
		$data				= $this->arrayDbData();
		return $data;
	}
	public function arrayDbData()
	{
		//$data['id']				= $this->params['bid'];
		$data['block_title']	= $this->params['title'];
		$data['type']			= $this->params['type'];
		$data['block']			= $this->params['block'];
		if($data['type']!='h')
		{
			$data2['bm_type']		= ($this->params['meta_type']<4)?$this->params['meta_type']:1;
			$data2['bm_code']		= $this->params['meta_block'];
			return array($data, $data2);
		}
		return array($data, false);
	}
	public function validate()
	{
		$frmValidator	= new Application_Model_Validator;
		$data = array(	
						'title'		=> $this->params['title'],
						'block'		=> $this->params['block']
			 		 );
		$rule=array	(
						'title'		=>'notNull',
						'block'		=>'notNull'
					);

		$frmValidator->validate($data,$rule);
		$error	= array();
		if($frmValidator->getResult('title')	== false) $error[]	= $this->translate->_('u'); 
		if($frmValidator->getResult('block')	== false) $error[]	= $this->translate->_('w'); 
		
		$sysError	= ''; 
		if($this->params['bid']!=$this->params['id'])							$sysError	= $this->translate->_('h');
		if(!empty($this->params['bid']) and !is_numeric($this->params['bid']) ) $sysError	= $this->translate->_('h');
		$validBTypes	= array('h', 'q', 'n', 'm', 'i');
		if(!in_array($this->params['type'], $validBTypes))						$sysError	= $this->translate->_('h');
		if(!empty($sysError)) $error[]	= $sysError;

		if(count($error)>0)
		{
			$this->_helper->flashMessenger->addMessage($error);
			$this->_helper->flashMessenger->addMessage($this->params);
			$this->_helper->flashMessenger->addMessage($_SERVER['REQUEST_URI']);
			$this->_redirect('/skiner/block/frmregister'.$this->newUriParams);
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
