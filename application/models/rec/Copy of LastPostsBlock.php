<?php
/*
	*	
*/

class Application_Model_LastPostsBlock 
{

	static function	getScenarioBolock($sc_id)
	{
		return self::_getScenarioBolock($sc_id);
	}
	public function	_getScenarioBolock($sc_id)
	{
		if(! $sc_sc = $this->_getScenrioFamily($sc_id) ) return false;
		$sc_pr	= $this->_getProperties($sc_sc['properties'],$sc_sc['sb_data']);
		
//		$block	= array('text'=>'', 'abstract'=>'', 'title'=>'', 'title2'=>'', 'unic'=>'', 'author'=>'', 'date'=>'', 'time'=>'',
//		 'comment']['count'=>'', 'comment']['link'=>'', 'author'=>'', 'date'=>'');

		
		
		$return	= array('title'=>$sc_sc['title'], 'uri'=>$sc_sc['uri'], 'content'=>array(), 'count'=>0, 'pagination'=>$sc_pr['paging'] );
		
		if(! $sc_co	= $this->_getScenrioRtcs($sc_id, $sc_pr, $sc_sc['subs']) ) return $return;
		$return['content']	= $sc_co[0];
		$return['count']	= $sc_co[1];
		return $return;
	}
	protected function	_getScenrioFamily($sc_id)
	{
		$sql		= 'SELECT sc.id, sc.uri, sc.title, sc.properties, sb.sb_xal, sb.sb_data, sa.subs '
					. ' FROM wbs_scenario AS sc Inner Join wbs_scenario_block AS sb ON sb.sb_sc_id = sc.id  Left Join wbs_scenario_allsubs AS sa ON sa.sc_id = sc.id '
					. ' WHERE sc.wbs_id IN (0, '.WBSiD.') AND (sc.wbs_group RLIKE "\/'.str_replace(',','\/|\/',WBSgR).'\/") AND sc.id='.addslashes($sc_id);
		if(! $result	= $this->DB->fetchAll($sql) ) return false;
		if( empty($result[0]['sb_xal']) )	return false;
		if( strlen($result[0]['subs'])>2 )
			$result[0]['subs']	= '\/'.str_replace('/','\/|\/', preg_replace('/(^\/+)|(\/+$)/', '', $result[0]['subs']) ).'\/';
		else	$result[0]['subs']	= '';
		return $result[0];
	}
	protected function	_getProperties($sc_pr, $sb_data)
	{
		$properxml	=	'<root>'.$sc_pr.'</root>';
		$xml 		= 	new SimpleXMLElement($properxml); 			
		$properties['count']	= (int) $xml->c;
		$properties['paging']	= (string) $xml->p;
		//$fl_ad	= array('id'=>'id ', 't1'=>'title AS title1 ', 't2'=>'ltn_name AS title2 ', 'ab'=>'description AS abstract ', 'tx'=>'content AS text ' );
		$fl_ad	= array('id ', 'title AS title1 ', 'ltn_name AS title2 ', 'description AS abstract ', 'content AS text ');
		$properties['fields']	= str_replace( array_keys($fl_ad), array_values($fl_ad), $sb_data);
		return $properties;
	} 	 	 	 	 	 	 	 	
	protected function	_getScenrioRtcs($sc_id, $sc_pr, $sc_su)
	{
		$sql_0	= 'SELECT '.$sc_pr['fields'];
		$sql_1	= 'SELECT COUNT(`id`) ';
		$sql_2	= ' FROM `wbs_rtcs`';
		$sql_3	= 'WHERE wbs_id IN (0, '.WBSiD.') AND (wbs_group RLIKE "\/'.str_replace(',','\/|\/',WBSgR).'\/") '
				. ' AND `is_published` != 0 AND `publish_up`<=NOW()  AND `publish_down`>=NOW()';
		$sql_4	= ( !empty($sc_su) )?' AND (`scenarios` RLIKE "'.$sc_su.'") ':'';
		
		if(! $count	= $this->DB->fetchOne( $sql_1.$sql_2.$sql_3.$sql_4 ) ) return false;
		if($count==0)	return false;
		
		$sql_5	= 'ORDER BY `publish_up` DESC';
		
		$start	= 0;
		if($sc_pr['paging']!=0)
		{
			if( is_numeric($_GET['st_'.$sc_id]) and $_GET['st_'.$sc_id] < $count )		$start	= $_GET['st_'.$sc_id];
			if( is_numeric($_POST['st_'.$sc_id]) and $_POST['st_'.$sc_id] < $count )	$start	= $_POST['st_'.$sc_id];
		}
		$limit	= ( is_numeric($sc_pr['count']) )?$sc_pr['count']:10;
		$sql_6	= 'LIMIT '.$start.' , '.$limit;
		
		if(! $content= $this->DB->fetchAll( $sql_0.$sql_2.$sql_3.$sql_4.$sql_5.$sql_6 ) ) return array($content, $count);
		return false;
	}
	
	
}
?>