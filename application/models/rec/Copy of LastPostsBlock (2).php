<?php
/*
	*	
*/

class Application_Model_LastPostsBlock 
{

	public function	getScenarioBolock($sb_xal, $sb_extra)
	{
		
		$this->_XAL	= new Xal_Servlet();
		if(! $sc_ex	= $this->_getExtraData($sb_extra) )		return false;
		$this->DB	= Zend_Registry::get('front_db');
		if(! $sc_sc = $this->_getScenrioFamily($sc_ex['var:sc_id']) ) return false;
		//$this->setUserData();
		$sc_pr		= $this->_getProperties($sc_sc['properties'],$sc_ex['var:sc_fields']);
		if(! $sc_co	= $this->_getScenrioRtcs($sc_ex['var:sc_id'], $sc_pr, $sc_sc['subs']) ) return false;
		$xal_input['var:scenario']	= array('title'=>$sc_sc['title'], 'uri'=>$sc_sc['uri'], 'content'=>$sc_co[0], 'count'=>$sc_co[1], 
					'pagination'=>$sc_pr['paging'] );
		return $this->_generateBlock($sb_xal ,$xal_input);
	}
	protected function	_generateBlock($sb_xal ,$xal_input)
	{
		$sb_xal	= trim($sb_xal);
		if( empty($sb_xal) ) return;
		$sb_xal	= '<execution>'.$sb_xal.'</execution>';
		$this->_XAL->disable(array('print'));
		$result	= $this->_XAL->run($sb_xal, $xal_input);
		if( !is_array($result) or !isset($result['var:block']) )	return false;
		return $result['var:block'];
	} 	 	 	 	 	 	 	 	
	protected function	_getExtraData($sb_extra)
	{
		$sb_extra	= trim($sb_extra);
		if( empty($sb_extra) ) return;
		$sb_extra	= '<execution>'.$sb_extra.'</execution>';
		$this->_XAL->disableAll();
		$this->_XAL->enable(array('execution', 'tree'));
		$result	= $this->_XAL->run($sb_extra);
		$this->enableRegularXalFns();
		if( !is_array($result) )	return false;
		return $result;
	} 	 	 	 	 	 	 	 	
	protected function	_getScenrioFamily($sc_id)
	{
		if(!is_numeric($sc_id))	return false;
		$sql		= 'SELECT sc.id, sc.uri, sc.title, sc.properties, sa.subs '
					. ' FROM wbs_scenario AS sc Left Join wbs_scenario_allsubs AS sa ON sa.sc_id = sc.id '
					. ' WHERE '.Application_Model_Pubcon::get(1111, 'sc').' AND sc.id='.addslashes($sc_id);
		if(! $result	= $this->DB->fetchAll($sql) ) return false;
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
		$fl_ad	= array('id'=>'id ', 't1'=>'title AS title1 ', 't2'=>'ltn_name AS title2 ', 'ab'=>'description AS abstract ', 'tx'=>'content AS text ' );
		$properties['fields']	= str_replace( array_keys($fl_ad), array_values($fl_ad), $sb_data);
		return $properties;
	} 	 	 	 	 	 	 	 	
	protected function	_getScenrioRtcs($sc_id, $sc_pr, $sc_su)
	{
		if(!is_numeric($sc_id))	return false;
		$sql_0	= 'SELECT '.$sc_pr['fields'];
		$sql_1	= 'SELECT COUNT(`id`) ';
		$sql_2	= ' FROM `wbs_rtcs`';
		$sql_3	= 'WHERE '.Application_Model_Pubcon::get().' AND type_id NOT IN (2, 3)'
				. ' AND `is_published` != 0 AND `publish_up`<=NOW()  AND `publish_down`>=NOW()';//.$this->user['condition'];
		$sql_4	= ( !empty($sc_su) )?' AND (`scenarios` RLIKE "'.$sc_su.'") ':'';
		
		
		if(! $count	= $this->DB->fetchOne( $sql_1.$sql_2.$sql_3.$sql_4 ) ) return false;
		if($count==0)	return false;
		$sql_5	= 'ORDER BY `publish_up` DESC ';
		
		$start	= 0;
		if($sc_pr['paging']!=0)
		{
			if( is_numeric($_GET['st_'.$sc_id]) and $_GET['st_'.$sc_id] < $count )		$start	= $_GET['st_'.$sc_id];
			if( is_numeric($_POST['st_'.$sc_id]) and $_POST['st_'.$sc_id] < $count )	$start	= $_POST['st_'.$sc_id];
		}
		$limit	= ( is_numeric($sc_pr['count']) )?$sc_pr['count']:10;
		$sql_6	= ' LIMIT '.$start.' , '.$limit;

		if( $content= $this->DB->fetchAll( $sql_0.$sql_2.$sql_3.$sql_4.$sql_5.$sql_6 ) ) return array($content, $count);
		return false;
	}
	protected function	enableRegularXalFns()
	{
		$this->_XAL->enableAll();
		// $this->_XAL->disable();
	}
	
}
?>