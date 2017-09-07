<?php
/*
	*	
*/

class Application_Model_LastPostsBlock 
{

	public function	getScenarioContent($sb_extra, $mb_type)
	{
		
		//$this->_XAL	= new Xal_Servlet();
		//if(! $sb_extra	= $this->_getExtraData($sb_extra) )		return false;
		$this->DB	= Zend_Registry::get('front_db');
		if(! $sc_sc = $this->_getScenrioFamily($sb_extra['var:scen_id']) ) return false;
		$sc_pr		= $this->_getProperties($sb_extra);
		if(! $sc_co	= $this->_getScenrioRtcs($sb_extra['var:scen_id'], $sc_pr, $sc_sc['subs']) ) return false;
		$return		= array(	'data'=> array('title'=>$sc_sc['title'], 'uri'=>$sc_sc['uri'], 'count'=>$sc_co[1], 'pagination'=>$sc_pr['paging'], 'limit'=>$sc_pr['count'] ),
								'content'=> $sc_co[0]
						);
		if($mb_type==1 and $sc_pr['paging']==1 and $sc_co[1]>$sc_pr['count'])
			$return['paging_block']	= $this->_genPagingHtml($sc_co[1], $sc_pr['count'], 'st_'.$sb_extra['var:scen_id'] );
		return  $return; 
		
		//$xal_input['var:scenario']	= array('title'=>$sc_sc['title'], 'uri'=>$sc_sc['uri'], 'content'=>$sc_co[0], 'count'=>$sc_co[1], 
		//			'pagination'=>$sc_pr['paging'] );
		//return $this->_generateBlock($sb_xal ,$xal_input);
	}
//	protected function	_generateBlock($sb_xal ,$xal_input)
//	{
//		$sb_xal	= trim($sb_xal);
//		if( empty($sb_xal) ) return;
//		$sb_xal	= '<execution>'.$sb_xal.'</execution>';
//		$this->_XAL->disable(array('print'));
//		$result	= $this->_XAL->run($sb_xal, $xal_input);
//		if( !is_array($result) or !isset($result['var:block']) )	return false;
//		return $result['var:block'];
//	} 	 	 	 	 	 	 	 	
//	protected function	_getExtraData($sb_extra)
//	{
//		$sb_extra	= trim($sb_extra);
//		if( empty($sb_extra) ) return;
//		$sb_extra	= '<execution>'.$sb_extra.'</execution>';
//		$this->_XAL->disableAll();
//		$this->_XAL->enable(array('execution', 'tree'));
//		$result	= $this->_XAL->run($sb_extra);
//		$this->enableRegularXalFns();
//		if( !is_array($result) )	return false;
//		return $result;
//	} 	 	 	 	 	 	 	 	
	protected function	_getScenrioFamily($sc_id)
	{
		if(!is_numeric($sc_id))	return false;
		$sql		= 'SELECT sc.id, sc.uri, sc.title, sc.properties, sa.subs '
					. ' FROM wbs_scenario AS sc Left Join wbs_scenario_allsubs AS sa ON sa.sc_id = sc.id '
					. ' WHERE '.Application_Model_Pubcon::get(1111, 'sc').' AND sc.id='.addslashes($sc_id);
		if(! $result	= $this->DB->fetchAll($sql) ) return false;
		if( strlen($result[0]['subs'])>2 )
			$result[0]['subs']	= '\/'.str_replace('/','\/|\/', preg_replace('/(^\/+)|(\/+$)/', '', '/'.$sc_id.$result[0]['subs']) ).'\/';
		else	$result[0]['subs']	= '\/'.$sc_id.'\/';
		return $result[0];
	}
	protected function	_getProperties($sb_extra)
	{
		$sc_pr['paging']	= (isset($sb_extra['var:scen_pagination']) and $sb_extra['var:scen_pagination']=='true')?1:0;
		$sc_pr['count']		= (is_numeric($sb_extra['var:scen_limit']) and $sb_extra['var:scen_limit']>0)?$sb_extra['var:scen_limit']:10;
		$sc_pr['fields']	= 'co.id , co.title AS t1, co.ltn_name AS t2, co.publish_up AS `dt`, co.description AS ab ';
		if(empty($sb_extra['var:scen_fields']))	return $sc_pr;
		
		$ex_fls	= implode(',', array_intersect( array_map(trim, explode(',', $sb_extra['var:scen_fields']) ) , array('id','t1','t2','ab','tx', 'dt') ) );
		$fl_ad	= array('id'=>'co.id ', 't1'=>'co.title AS t1 ', 't2'=>'co.ltn_name AS t2 ', 'ab'=>'co.description AS ab ', 'tx'=>'co.content AS tx ', 
			'dt'=>'co.publish_up AS `dt` ' );
		$sc_pr['fields']	= str_replace( array_keys($fl_ad), array_values($fl_ad), $ex_fls);
		return $sc_pr;
	} 	 	 	 	 	 	 	 	
	protected function	_getScenrioRtcs($sc_id, $sc_pr, $sc_su)
	{
		if(!is_numeric($sc_id))	return false;
		$sql_0	= 'SELECT '.$sc_pr['fields'].', co.type_id, me.author, me.extra_data ';
		$sql_1	= 'SELECT COUNT(co.`id`) ';
		$sql_2	= ' FROM `wbs_rtcs` AS co ';
		$sql_3	= 'WHERE '.Application_Model_Pubcon::get(1111, 'co').' AND co.type_id NOT IN (2, 3)'
				. ' AND co.`is_published` != 0 AND co.`publish_up`<=NOW()  AND co.`publish_down`>=NOW()';
		$sql_4	= ( !empty($sc_su) )?' AND (co.`scenarios` RLIKE "'.$sc_su.'") ':'';
		
		
		if(! $count	= $this->DB->fetchOne( $sql_1.$sql_2.$sql_3.$sql_4 ) ) return false;
		if($count==0)	return false;
		$sql_5	= 'ORDER BY `publish_up` DESC ';
		
		$start	= '0';
		if($sc_pr['paging']!=0 and $count > $sc_pr['count'])
		{
			if( is_numeric($_GET['st_'.$sc_id]) )		$start	= $_GET['st_'.$sc_id];
			if( is_numeric($_POST['st_'.$sc_id]))	$start	= $_POST['st_'.$sc_id];
		}
		if( $start>0 )	$start	= ($start-1) * $sc_pr['count'];
		if( $start >= $count)	$start	= '0';
		$sql_6	= ' LIMIT '.$start.' , '.$sc_pr['count'];

		$sql_2	= ' FROM wbs_rtcs AS co LEFT JOIN wbs_rtc_metadata AS me ON co.id = me.txt_id ';

		if(! $content = $this->DB->fetchAll( $sql_0.$sql_2.$sql_3.$sql_4.$sql_5.$sql_6 ) )	return false;
		return array($content, $count);
	}
	protected function	_genPagingHtml($count, $limit, $pkey)
	{
		$paging_count	= ceil($count/$limit);
		$start	= 1;
		if( is_numeric($_GET[$pkey]) )	$start	= $_GET[$pkey];
		if( is_numeric($_POST[$pkey]) )	$start	= $_POST[$pkey];
		$paging_html	= '<div id="contPaging" style="width:100%;height:35px;text-align:center;">';
		if(isset($_GET[$pkey])) unset($_GET[$pkey]);
		$qur	= implode('&', $_GET);
		$qur	= (empty($qur))?'?':'?'.$qur.'&';
		for($i=1; $i<=$paging_count; $i++)
		{
			$paging_href	= preg_replace('/\?.*$/', '' , $_SERVER['REQUEST_URI']).$qur.$pkey.'='.$i;
			$paging_one		= '&nbsp;<a href="'.$paging_href.'">'.$i.'</a>&nbsp;';
			if($i == $start ) $paging_one	='&nbsp;<b>'.$i.'</b>&nbsp;';
			$paging_html	.= $paging_one;
		}
		$paging_html	.= '</div>';
		return $paging_html;
	}
//	protected function	enableRegularXalFns()
//	{
//		$this->_XAL->enableAll();
//		// $this->_XAL->disable();
//	}
	
}
?>