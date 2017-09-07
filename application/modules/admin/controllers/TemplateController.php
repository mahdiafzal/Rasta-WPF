<?php 
 
class Admin_TemplateController extends Zend_Controller_Action
{

	public function init()
    {
        /* Initialize action controller here */
    }
    public function indexAction()
    {
		$this->_redirect('/skiner/skin/frmlist#fragment-4');
    }
    public function selectAction()
    {
		$params 	= $this->_getAllParams();
		if( isset($params['sid']) )
		{
			$DB			= Zend_Registry::get('front_db');
			$sql		= 'select count(`skin_id`) as `cnt` from `wbs_skin` where (`wbs_id` = '. WBSiD.' or `wbs_id` = 0) and `skin_id`=' .addslashes($params['sid']);
			$result		= $DB->fetchAll($sql);
			if($result[0]['cnt']!=1)	$this->_redirect($_SERVER['HTTP_REFERER']);
			$sSkin		= $params['sid'];
	   		$response	= '<script>if(window.opener!=null){window.opener.$var.tempSelect('.$sSkin.'); window.close();}else{window.location="'
						. $_SERVER['HTTP_REFERER'].'";}</script>';
	   		die($response);
		}
		$this->_redirect($_SERVER['HTTP_REFERER']);
    }

}