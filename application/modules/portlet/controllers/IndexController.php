<?php
 
class Portlet_IndexController extends Zend_Controller_Action 
{

   public function init() 
    {
    }

    public function indexAction()
    {
		$action	= new  Portlet_Model_Container_Action();
		$params	= array('module'=>'index', 'controller'=>'index', 'action'=>'index');
		$action->setParams($params);
		$action->renderAction();
		$this->view->assign('SkinVersin', 2);
		die();
	}

    public function testAction()
    {
		
		echo str_replace('/',',', preg_replace('/(^\/+)|(\/+$)/', '', '/1/2/34/') );
		die();
		
		$xml	= '


<execution>
<tag:body><constant>htmlcontent</constant></tag:body>

</execution>



		';

		$axml	= new Xal_Servlet();
		$axml->set_sqlite_root( realpath(APPLICATION_PATH .'/../data/db').'/'.WBSiD.'/' );
		//$axml->disable(array('print'));
		$axml->set_env(array('ENV_HOST_ID'=> WBSiD));
		$axml->run($xml);
		//print_r($_SERVER);
		//$axml	= new Dataware_Model_Axml($xml);
		
		//$function	= $this->xmlstr_to_array($xml);
		//$function	= $axml->_atree; 
		
		


		

		print_r( 'ssssssssssssssssss' );
		die();
		//print_r( $call );
    }

    public function routerAction()
    {
		
		$path	= $this->_getParam('pr_path');
		$action	= new  Portlet_Model_Container_Action();
		$action->rout($path);
		$action->renderAction();
		$this->view->assign('SkinVersin', 2);
		die();
		
    }


}
