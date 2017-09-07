<?php 
 
class Stat_DataController extends Zend_Controller_Action
{

    public function visitsAction() 
	{
		$this->_helper->layout()->disableLayout();
    	$this->DB			= Zend_Registry::get('front_db');
//		$this->translate	= Zend_Registry::get('translate');
//		$this->view->assign('translate', $translate);
//		$this->view->assign('title_site', $translate->_('a')); 

		$this->params	= $this->getRequest()->getParams();
		
		$sql	= 'SELECT DATE(`time`) as `date`, count(`id`) as `cnt` FROM `log_visitor` WHERE `wbs_id`='.WBSiD.' GROUP BY (`date`)';
		$result	= $this->DB->fetchAll($sql);
		$pdate	= new Rasta_Pdate;
		$pMonth_name	= array('', 'Farvadin', 'Ordibehesht', 'Khordad', 'Tir', 'Mordad', 'Shahrivar', 'Mehr', 'Aban', 'Azar', 'Dey', 'Bahman', 'Esfand' );
		foreach($result as $case)
		{
			$tmpdate		= explode('-', $case['date']);
			$tmpdate		= $pdate->gregorian_to_persian($tmpdate[0], $tmpdate[1], $tmpdate[2]);
			$tmpdate[0]		= $pMonth_name[(integer)$tmpdate[1]].'-'.$tmpdate[0];
			$result2[$tmpdate[0]][0]			= (integer)$tmpdate[1];
			$result2[$tmpdate[0]][(integer)$tmpdate[2]]	= $case['cnt'];
		}
//			$result2['Mordad-1391'][0]	= 5;
//			$result2['Mordad-1392'][0]	= 5;
//			$result2['Mordad-1393'][0]	= 5;
//			$result2['Mordad-1394'][0]	= 5;
//			$result2['Mordad-1395'][0]	= 5;
//			$result2['Mordad-1396'][0]	= 5;
//			$result2['Mordad-1397'][0]	= 5;
//			$result2['Mordad-1398'][0]	= 5;
//			$result2['Mordad-1399'][0]	= 5;
//			$result2['Mordad-1400'][0]	= 5;
//			$result2['Mordad-1401'][0]	= 5;
//			$result2['Mordad-1402'][0]	= 5;
		
		$this->view->assign('data', $result2);
//		print_r($result2);
//		die();
		header('content-type: application/xml; charset=UTF-8');
	}
    public function visitorsAction() 
	{
		$this->_helper->layout()->disableLayout();
    	$this->DB			= Zend_Registry::get('front_db');
//		$this->translate	= Zend_Registry::get('translate');
//		$this->view->assign('translate', $translate);
//		$this->view->assign('title_site', $translate->_('a')); 

		$this->params	= $this->getRequest()->getParams();
		
		$sql	= 'SELECT DATE(`time`) as `date`, count(DISTINCT `ip`) as `cnt` FROM `log_visitor` WHERE `wbs_id`='.WBSiD.' GROUP BY (`date`)';
		$result	= $this->DB->fetchAll($sql);
		$pdate	= new Rasta_Pdate;
		$pMonth_name	= array('', 'Farvadin', 'Ordibehesht', 'Khordad', 'Tir', 'Mordad', 'Shahrivar', 'Mehr', 'Aban', 'Azar', 'Dey', 'Bahman', 'Esfand' );
		foreach($result as $case)
		{
			$tmpdate		= explode('-', $case['date']);
			$tmpdate		= $pdate->gregorian_to_persian($tmpdate[0], $tmpdate[1], $tmpdate[2]);
			$tmpdate[0]		= $pMonth_name[(integer)$tmpdate[1]].'-'.$tmpdate[0];
			$result2[$tmpdate[0]][0]			= (integer)$tmpdate[1];
			$result2[$tmpdate[0]][(integer)$tmpdate[2]]	= $case['cnt'];
		}
		
		$this->view->assign('data', $result2);
		header('content-type: application/xml; charset=UTF-8');
	}
    public function activeusersAction() 
	{
		$this->_helper->layout()->disableLayout();
    	$this->DB			= Zend_Registry::get('front_db');
//		$this->translate	= Zend_Registry::get('translate');
//		$this->view->assign('translate', $translate);
//		$this->view->assign('title_site', $translate->_('a')); 

		$this->params	= $this->getRequest()->getParams();
		
		$sql	= 'SELECT count(`id`) as `cnt`, `is_active` FROM `users` WHERE `wb_user_id`='.WBSiD.' AND `is_admin`=0 GROUP BY (`is_active`)';
		$result	= $this->DB->fetchAll($sql);
//		print_r($result);
//		die();
		foreach($result as $case)
		{
			$result2[$case['is_active']]	= $case['cnt'];
//			$tmpdate		= explode('-', $case['date']);
//			$tmpdate		= $pdate->gregorian_to_persian($tmpdate[0], $tmpdate[1], $tmpdate[2]);
//			$tmpdate[0]		= $pMonth_name[(integer)$tmpdate[1]].'-'.$tmpdate[0];
//			$result2[$tmpdate[0]][0]			= (integer)$tmpdate[1];
//			$result2[$tmpdate[0]][(integer)$tmpdate[2]]	= $case['cnt'];
		}
		
		$this->view->assign('data', $result2);
		header('content-type: application/xml; charset=UTF-8');
	}
    public function usersgroupsAction() 
	{
		$this->_helper->layout()->disableLayout();
    	$this->DB			= Zend_Registry::get('front_db');
//		$this->translate	= Zend_Registry::get('translate');
//		$this->view->assign('translate', $translate);
//		$this->view->assign('title_site', $translate->_('a')); 

		$this->params	= $this->getRequest()->getParams();
		
		$sql	= 'SELECT count(`id`) as `cnt`, `user_group` FROM `users` WHERE `wb_user_id`='.WBSiD.' AND `is_admin`=0 GROUP BY (`user_group`)';
		$result	= $this->DB->fetchAll($sql);
		foreach($result as $case)
		{
			$ug	= $case['user_group'];
			$cnt= (integer)$case['cnt'];
			if(preg_match('/^\d+$/',$ug)) $result2[$ug]	= (empty($result2[$ug]))?$cnt:($cnt+$result2[$ug]);
			else
			{
				$ug	= explode('/',$case['user_group']);
				foreach($ug as $g) $result2[$g]	= (empty($result2[$g]))?$cnt:($cnt+$result2[$g]);
			}
		}
		print_r($result2);
		die();
		
		$this->view->assign('data', $result2);
		header('content-type: application/xml; charset=UTF-8');
	}
    public function sitecontentsAction() 
	{
		$this->_helper->layout()->disableLayout();
    	$this->DB			= Zend_Registry::get('front_db');
//		$this->translate	= Zend_Registry::get('translate');
//		$this->view->assign('translate', $translate);
//		$this->view->assign('title_site', $translate->_('a')); 

		$this->params	= $this->getRequest()->getParams();
		
		$sql1	= 'SELECT count(*) as `cnt` FROM `wbs_rtcs` WHERE `wbs_id`='.WBSiD;
		$sql2	= 'SELECT count(*) as `cnt` FROM `wbs_gallery` WHERE `wbs_id`='.WBSiD;
		$sql3	= 'SELECT count(*) as `cnt` FROM `wbs_menu` WHERE `wbs_id`='.WBSiD;
		$sql4	= 'SELECT count(*) as `cnt` FROM `wbs_pages` WHERE `wbs_id`='.WBSiD;
		$sql5	= 'SELECT count(*) as `cnt` FROM `wbs_scenario` WHERE `wbs_id`='.WBSiD;
		$sql6	= 'SELECT count(*) as `cnt` FROM `wbs_links` WHERE `wbs_id`='.WBSiD;
		
		$result['rtc']		= $this->DB->fetchOne($sql1);
		$result['gallery']	= $this->DB->fetchOne($sql2);
		$result['menu']		= $this->DB->fetchOne($sql3);
		$result['page']		= $this->DB->fetchOne($sql4);
		$result['scenario']	= $this->DB->fetchOne($sql5);
		$result['extlink']	= $this->DB->fetchOne($sql6);

//		print_r($result2);
//		die();
		
		$this->view->assign('data', $result);
		header('content-type: application/xml; charset=UTF-8');
	}

}