<?php

class Rtcmanager_FrmlistcntController extends Zend_Controller_Action
{
	var $DB;
	var $ses;
    public function init()
    {
		//$this->ses 	= new Zend_Session_Namespace('MyApp');
    	$this->DB	= Zend_registry::get('front_db');
    }
    public function indexAction()
    {
    	$this->translate	= Zend_registry::get('translate');
		$this->view->assign('translate'		, $this->translate ); 	
		$this->view->assign('title_site'		, $this->translate->_('lc'));	
		$this->params	= $this->_getAllParams();
		$this->setUriParams();
		

		$this->start	= ( is_numeric($this->params['st']) )?$this->params['st']:0;
		$this->limit	= 25;
		
		$data	= $this->_fetchRtcList();


		

		//$this->view->assign('title'	, 'لیست مطالب');
		
		$this->view->assign('data'	, $data['list']);
		$this->view->assign('count'	, $data['count']);
		$this->view->assign('start'	, $this->start);
		$this->view->assign('limit'	, $this->limit);
		$this->view->assign('ctype_id'	, $this->params['ctype']);
		$this->view->assign('env'	, $this->env);
		$this->view->assign('ctypes', $this->_fetchWbsRtcTypes());
		$this->view->assign('msg'	, $this->_helper->flashMessenger->getMessages());	
	}
	protected function _fetchWbsRtcTypes()
	{
		$sql		= 'SELECT ct.ct_title, ts.* FROM wbs_content_type AS ct INNER JOIN wbs_content_type_setting AS ts ON ts.ts_ct_id = ct.ct_id '
					. ' WHERE '.Application_Model_Pubcon::get(1110, 'ct')
					. ' AND '. Application_Model_Pubcon::get(1001, 'ts')
					. ' AND ct.`lang`="'.LANG.'" AND ts.ts_status=1 ;'; //die($sql);
		//$sql	= 'SELECT * FROM `wbs_content_type_setting` WHERE '.Application_Model_Pubcon::get(1001).' ORDER BY `ts_id` ASC';
		if( $result	= $this->DB->fetchAll($sql) ) return $result;
		return false;
	}
	protected function _fetchRtcList()
	{
		$sql_1	= 'SELECT `id`, `wbs_id`, `ltn_name`,`title`,`description`,`crt_date`,`upt_date`,`is_published`,`publish_up`,`publish_down`,`setting`';
		$sql_2	= ' FROM `wbs_rtcs`'
				. ' WHERE '.Application_Model_Pubcon::get()
				. ' AND type_id='.addslashes($this->params['ctype']);
				//. ' AND `gad_id` NOT IN (SELECT `gad_id` FROM `wbs_gadget_options` WHERE `wbs_id`='.WBSiD.' AND `rtc_lock`=1)'
		$sql_3	= ' ORDER BY `id` DESC LIMIT '.$this->start.','.$this->limit;
		if( !$result	= $this->DB->fetchAll($sql_1.$sql_2.$sql_3) ) return false;
		
		$sql_1	= 'SELECT COUNT(id) ';
		$count	= $this->DB->fetchOne($sql_1.$sql_2);
		
		//$count	= $this->DB->fetchAll('SELECT COUNT(id) AS `cnt` FROM wbs_rtcs WHERE '.Application_Model_Pubcon::get() );
		$illegalDates	= array('9999-12-30 12:00:00', '0000-00-00 00:00:00');
		for($i=0; $i<count($result); $i++)	if( in_array($result[$i]['publish_down'], $illegalDates) ) $result[$i]['publish_down'] = ''; 
	
		return array( 'list'=>$result, 'count'=>$count);
	}
	protected function setUriParams()
	{
		if ( !is_numeric($this->params['ctype']) or  $this->params['ctype']<0 )	$this->params['ctype'] = 1;
		
		$this->newUriParams =	'';
		if ( !is_numeric($this->params['ctype']) or  $this->params['ctype']<1 )
			$this->newUriParams .=	'/ctype/1';
		else
			$this->newUriParams .=	'/ctype/'.$this->params['ctype'];
		//$this->newUriParams .=	'/ctype/'.$this->params['ctype'];
		if (!empty( $this->params['env']) && $this->params['env']=='dsh')
		{
			$this->_helper->_layout->setLayout('dashboard');
			$this->newUriParams .= $this->env	= '/env/dsh#fragment-2';
		}
	}
}

?>