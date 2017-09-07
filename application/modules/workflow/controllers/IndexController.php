<?php
 
class Workflow_IndexController extends Zend_Controller_Action 
{

	public function init() 
	{
	
	}

    public function indexAction()
    {
//		$action	= new  Portlet_Model_Container_Action();
//		$params	= array('module'=>'index', 'controller'=>'index', 'action'=>'index');
//		$action->setParams($params);
//		$action->renderAction();
	}


    public function routerAction()
    {
		
		//$path	= $this->_getParam('wf_path');
		if( !$path = $this->_getParam('wf_path') )
			if( $scenario = Zend_Registry::get('scenario') )
			{
				$path	= $scenario['page_id'];
				//print_r($scenario); die();
			}
		if(is_numeric($path))	$this->_renderWorkflow($path);
		else
		{
			$path	= explode(':', $path);
			if( !is_numeric($path[1]) )			die(Application_Model_Messages::message(404));
			$vpages	= array('node', 'progbar');
			if( !in_array($path[0], $vpages) )	die(Application_Model_Messages::message(404));
			if($path[0]=='node')				$this->_renderNodePage($path[1]);
			elseif($path[0]=='progbar')			$this->_renderProgbarPage();
		}
	}
    protected function _renderWorkflow($ID)
    {
		$wf	= new  Workflow_Model_Init($ID);
		if( is_numeric($wf->node) )	$this->_renderNodePage($wf->node);
		//if($page->url)	$this->_redirect($page->url);
		//die(Application_Model_Messages::message(404));
    }
    protected function _renderNodePage($ID)
    {
	
		//$data	= array('ref_id'=>$ID, 'refrence'=>'wf');
		$page	= new  Workflow_Model_Node($ID);
		$this->view->assign('pageHead', $page->pageHead);
	//	$HtmlBody	= $page->getHtmlBody();
	//	echo $HtmlBody;
		
		
		//$action->rout($path);
		//$action->renderAction();
		//die('ssssssssss');

    }


}
