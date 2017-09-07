<?php


class Rtcmanager_Model_Xaloperator 
{

	protected $_DB;

	
	public function	run($argus)
	{
		if( !isset($argus['method']) )	return '';
		$method	= $argus['method'];
		unset($argus['method']);
		switch($method)
		{
			case 'fetch'				: return $this->_fetch($argus); break;
			case 'set'					: return $this->_set($argus); break;
			case 'get'					: return $this->_get($argus); break;
			case 'save'					: return $this->_save($argus); break;
			
			case 'get.unic'				: return $this->__[ $this->helper_ns($argus) ]['unic']; break;
			case 'get.show.author'		: return $this->__[ $this->helper_ns($argus) ]['show']['author']; break;
			case 'get.show.date'		: return $this->__[ $this->helper_ns($argus) ]['show']['date']; break;
			case 'get.show.time'		: return $this->__[ $this->helper_ns($argus) ]['show']['time']; break;
			case 'get.show.comment'		: return $this->__[ $this->helper_ns($argus) ]['show']['comment']; break;
			case 'get.show.singlelink'	: return $this->__[ $this->helper_ns($argus) ]['show']['singlelink']; break;
			case 'get.status'			: return $this->__[ $this->helper_ns($argus) ]['is_published']; break;
			case 'get.publish.up'		: return $this->__[ $this->helper_ns($argus) ]['publish_up']; break;
			case 'get.publish.down'		: return $this->__[ $this->helper_ns($argus) ]['publish_down']; break;
			case 'get.title1'			: return $this->__[ $this->helper_ns($argus) ]['title1']; break;
			case 'get.title2'			: return $this->__[ $this->helper_ns($argus) ]['title2']; break;
			case 'get.author'			: return $this->__[ $this->helper_ns($argus) ]['author']; break;
			case 'get.description'		: return $this->__[ $this->helper_ns($argus) ]['description']; break;
			case 'get.keywords'			: return $this->__[ $this->helper_ns($argus) ]['keywords']; break;
			case 'get.abstract'			: return $this->__[ $this->helper_ns($argus) ]['abstract']; break;
			case 'get.text'				: return $this->__[ $this->helper_ns($argus) ]['text']; break;
			case 'get.type'				: return $this->__[ $this->helper_ns($argus) ]['type_id']; break;
			case 'get.taxoterms'		: return $this->__[ $this->helper_ns($argus) ]['taxoterms']; break;
			case 'get.usergroups'		: return $this->__[ $this->helper_ns($argus) ]['usergroups']; break;


			case 'set.show.author'		: return $this->_set_show_author($argus); break;
			case 'set.show.date'		: return $this->_set_show_date($argus); break;
			case 'set.show.time'		: return $this->_set_show_time($argus); break;
			case 'set.show.comment'		: return $this->_set_show_comment($argus); break;
			case 'set.show.singlelink'	: return $this->_set_show_singlelink($argus); break;
			case 'set.status'			: return $this->_set_status($argus); break;
			case 'set.publish.up'		: return $this->helper_set_value_i($argus, 'publish_up', 'now'); break;
			case 'set.publish.down'		: return $this->helper_set_value_i($argus, 'publish_down', 'never'); break;
			case 'set.title1'			: return $this->helper_set_value_i($argus, 'title1'); break;
			case 'set.title2'			: return $this->helper_set_value_i($argus, 'title2'); break;
			case 'set.author'			: return $this->helper_set_value_i($argus, 'author'); break;
			case 'set.description'		: return $this->helper_set_value_i($argus, 'description'); break;
			case 'set.keywords'			: return $this->helper_set_value_i($argus, 'keywords'); break;
			case 'set.abstract'			: return $this->helper_set_value_i($argus, 'abstract'); break;
			case 'set.text'				: return $this->helper_set_value_i($argus, 'text'); break;
			case 'set.type'				: return $this->helper_set_value_i($argus, 'type', '1'); break;
			case 'set.taxoterms'		: return $this->helper_set_value_i($argus, 'taxoterms', array()); break;
			case 'set.usergroups'		: return $this->helper_set_value_i($argus, 'usergroups', array()); break;

//			case 'set.show.author'		: return $this->_set_show_author($argus); break;
//			case 'set.show.date'		: return $this->_set_show_date($argus); break;
//			case 'set.show.time'		: return $this->_set_show_time($argus); break;
//			case 'set.show.comment'		: return $this->_set_show_comment($argus); break;
//			case 'set.show.singlelink'	: return $this->_set_show_singlelink($argus); break;
//			case 'set.status'			: return $this->_set_status($argus); break;
//			case 'set.publish.up'		: return $this->_set_publish_up($argus); break;
//			case 'set.publish.down'		: return $this->_set_publish_down($argus); break;
//			case 'set.title1'			: return $this->_set_title1($argus); break;
//			case 'set.title2'			: return $this->_set_title2($argus); break;
//			case 'set.author'			: return $this->_set_author($argus); break;
//			case 'set.description'		: return $this->_set_description($argus); break;
//			case 'set.keywords'			: return $this->_set_keywords($argus); break;
//			case 'set.abstract'			: return $this->_set_abstract($argus); break;
//			case 'set.text'				: return $this->_set_text($argus); break;
//			case 'set.type'				: return $this->_set_type($argus); break;
//			case 'set.taxoterms'		: return $this->_set_taxoterms($argus); break;
//			case 'set.usergroups'		: return $this->_set_usergroups($argus); break;

//			case 'get.unic'				: return $this->_get_unic($argus); break;
//			case 'get.show.author'		: return $this->_get_show_author($argus); break;
//			case 'get.show.date'		: return $this->_get_show_date($argus); break;
//			case 'get.show.time'		: return $this->_get_show_time($argus); break;
//			case 'get.show.comment'		: return $this->_get_show_comment($argus); break;
//			case 'get.show.singlelink'	: return $this->_get_show_singlelink($argus); break;
//			case 'get.status'			: return $this->_get_status($argus); break;
//			case 'get.publish.up'		: return $this->_get_publish_up($argus); break;
//			case 'get.publish.down'		: return $this->_get_publish_down($argus); break;
//			case 'get.title1'			: return $this->_get_title1($argus); break;
//			case 'get.title2'			: return $this->_get_title2($argus); break;
//			case 'get.author'			: return $this->_get_author($argus); break;
//			case 'get.description'		: return $this->_get_description($argus); break;
//			case 'get.keywords'			: return $this->_get_keywords($argus); break;
//			case 'get.abstract'			: return $this->_get_abstract($argus); break;
//			case 'get.text'				: return $this->_get_text($argus); break;
//			case 'get.type'				: return $this->_get_type($argus); break;
//			case 'get.taxoterms'		: return $this->_get_taxoterms($argus); break;
//			case 'get.usergroups'		: return $this->_get_usergroups($argus); break;
		}
	}
	protected function	_fetch($argus)
	{
		if(!isset($argus['value']))	$argus['value']	= 'new';
		$ns	= $this->helper_ns($argus);
		$result	= false;
		if(is_numeric($argus['value']) )	$result = $this->helper_fetch_rtc($argus['value']);
		elseif($argus['value']!='new')		return;
		$this->__[ $ns ]	= $this->helper_arrange_fetch_data($result);
	}	
	protected function	_get($argus)
	{
		$ns	= $this->helper_ns($argus);
		if(isset($this->__[ $ns ])) return $this->__[ $ns ];
	}	
	protected function	_set($argus)
	{
		$ns	= $this->helper_ns($argus);
		unset($argus['ns']);
		if(!is_array($argus) or count($argus)==0) return;
		$i=0;
		foreach($argus as $ark=>$argu)
		{
			if($i>20) break;
			$nar	= array('method'=>'set.'.$ark, 'ns'=>$ns, 'value'=>$argu);
			$this->run($nar);
			$i++;
		}
	}	
	protected function	_save($argus)
	{
		$ns	= $this->helper_ns($argus);
		if(!is_array($this->__[ $ns ]))	return;
		$data	= $this->helper_arrange_todb_data($this->__[ $ns ]);
		if( is_numeric($this->__[ $ns ]['unic']) )	$result	= $this->helper_update_rtc($this->__[ $ns ]['unic'], $data);
		elseif($this->__[ $ns ]['unic']=='new')		$result	= $this->helper_insert_rtc($data);
		if($result)	unset($this->__[ $ns ]);
	}	
	
	protected function	helper_fetch_rtc($id)
	{
		$sql	= 'SELECT co.id AS unic, co.type_id, co.ltn_name AS title2, co.title AS title1, co.description AS abstract, co.is_published , co.publish_up,'
				. ' co.publish_down, co.content AS text, co.taxoterms, co.setting, co.user_group as usergroups, me.*'
				. ' FROM wbs_rtcs AS co LEFT JOIN wbs_rtc_metadata AS me ON co.id = me.txt_id '
				. ' WHERE '.Application_Model_Pubcon::get(1110, 'co')
				. ' AND co.id='.addslashes($id);
		if(! $result	= $this->DB->fetchAll($sql) ) return false;
		return $result[0];
	}	
	protected function	helper_arrange_fetch_data($in)
	{
		$out	= array(
			'unic'			=> 'new',
			//'setting'		=> array(0,0,0,0,0);
			'show'			=> array('author'=>'false', 'date'=>'false', 'time'=>'false', 'comment'=>'disable', 'singlelink'=>'false'),
			'is_published'	=> 'false',
			'publish_up'	=> 'now',
			'publish_down'	=> 'never',
			'title1'		=> '',
			'title2'		=> '',
			'author'		=> '',
			'description'	=> '',
			'keywords'		=> '',
			'abstract'		=> '',
			'text'			=> '',
			'type_id'		=> '1',
			'taxoterms'		=> array(),
			'usergroups'	=> array()
		);
		if(!is_array($in)) return $out;
		
		$out['unic']		= $in['id'];
		$out['show']['author']		= ($in['setting'][0]==1)?'true':'false';
		$out['show']['date']		= ($in['setting'][1]==1)?'true':'false';
		$out['show']['time']		= ($in['setting'][2]==1)?'true':'false';
		$valids	= array('disable', 'private', 'public');
		$out['show']['comment']		= $valids[ $in['setting'][3] ];
		$out['show']['singlelink']	= ($in['setting'][4]==1)?'true':'false';
		$out['is_published']= ($in['is_published']==1)?'true':'false';
		$out['taxoterms']	= array_filter( explode('/', $in['taxoterms']) );
		$out['usergroups']	= array_filter( explode('/', $in['usergroups']) );
		$out['publish_up']	= $in['publish_up'];
		$out['publish_down']= $in['publish_down'];
		$out['title1']		= $in['title1'];
		$out['title2']		= $in['title2'];
		$out['author']		= $in['author'];
		$out['description']	= $in['description'];
		$out['keywords']	= $in['keywords'];
		$out['abstract']	= $in['abstract'];
		$out['text']		= $in['text'];
		$out['type_id']		= $in['type_id'];
		return $out;
	}	
	protected function	helper_arrange_todb_data($in)
	{
		$out['type_id']		= $in['type_id'];
		$out['ltn_name']	= $in['title2'];
		$out['title']		= $in['title1'];
		$out['description']	= $in['abstract'];
		$out['is_published']= ($in['is_published']=='true')?'1':'0';
		
		$out['publish_up']	= $this->helper_filter_ii($in['publish_up']);
		$out['publish_down']= $this->helper_filter_ii($in['publish_down']);
		
		$out['content']		= $in['text'];
		
		$in['taxoterms']	= $this->helper_filter_i($in['taxoterms']);
		$out['taxoterms']	= (count($in['taxoterms'])==0)?'0':'/'.implode('/', $in['taxoterms']).'/';
		
		$out['setting'][0]	= ($in['show']['author']=='true')?'1':'0';
		$out['setting'][1]	= ($in['show']['date']=='true')?'1':'0';
		$out['setting'][2]	= ($in['show']['time']=='true')?'1':'0';
		$valids	= array('disable'=>'0', 'private'=>'1', 'public'=>'2');
		$out['setting'][3]	= $valids[ $in['show']['comment'] ];
		$out['setting'][4]	= ($in['show']['singlelink']=='true')?'1':'0';
		$out['setting']		= implode('', $out['setting']);

		$in['usergroups']	= $this->helper_filter_i($in['usergroups']);
		$out['usergroups']	= (count($in['usergroups'])==0)?'0':'/'.implode('/', $in['usergroups']).'/';

		$mout['author']		= $in['author'];
		$mout['description']= $in['description'];
		$mout['keywords']	= $in['keywords'];
		
		return array($out, $mout);
	}
	protected function	helper_filter_i($array)
	{
		if(!is_array($array)) return array();
		foreach($array as $k=>$v)
			if(!is_numeric($v))	unset($array[$k]);
		return $array;
	}
	protected function	helper_filter_ii($date)
	{
		$date	= trim($date);
		$out	= '';
		if($date=='now') $out	= new Zend_DB_expr('now()');
		elseif(preg_match('/^\d\d\d\d\-\d\d\-\d\d\s\d\d\:\d\d\:\d\d$/', $date))	$out	= $date;
		elseif(preg_match('/^now\+\d+$/', $date))
		{
			preg_match('/\d+$/', $date, $days);
			$out	= new Zend_DB_expr('DATE_ADD(NOW(),INTERVAL '.$days[0].' DAY)');
		}
		return $out;
	}
	protected function	helper_update_rtc($id, $data)
	{
		$rtcID	= addslashes($id);
		if(empty($data[0]['publish_up']))	$data[0]['publish_up']	= new Zend_DB_expr('`wbs_rtcs`.`crt_date`');
		try
		{
			$this->DB->beginTransaction();
			$this->DB->update('wbs_rtcs',$data[0] , Application_Model_Pubcon::get(1000).' AND id ='.$rtcID);
			//add metadata
			$rr=$this->DB->fetchAll('SELECT `txt_id` FROM wbs_rtc_metadata WHERE '.Application_Model_Pubcon::get(1000).' AND `txt_id`='.$rtcID);
			if (count($rr)==1)	$this->DB->update('wbs_rtc_metadata',$data[1] , Application_Model_Pubcon::get(1000).' AND `txt_id` ='.$rtcID);
			else if (!empty($data[1]['description']) or !empty($data[1]['author']) or !empty($data[1]['keywords']))
			{
				$data[1]['txt_id']	= $rtcID;
				$this->DB->insert('wbs_rtc_metadata', $data[1] );
			}
			//end of add metadata
			$this->DB->commit();
			return true;
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	protected function	helper_insert_rtc($data)
	{
		$data[0]['crt_date']	= new Zend_DB_expr('now()');
		if(empty($data[0]['publish_up']))	$data[0]['publish_up']	= $data[0]['crt_date'];
		try
		{
			$this->DB->beginTransaction();
			$this->DB->insert('wbs_rtcs',$data[0]);
			$recordID	= $this->DB->lastInsertId();
			//add metadata
			if (!empty($data[1]['description'])or !empty($data[1]['author']) or !empty($data[1]['keywords']))
			{
				$data[1]['txt_id']	= $recordID;
				$this->DB->insert('wbs_rtc_metadata',$data[1]);
			}
			//end of add metadata
			$this->DB->commit();
			return true;
		}
		catch(Zend_exception $e)
		{
			return false;
		}
	}
	protected function	helper_ns($argus)
	{
		$argus['ns']	= trim($argus['ns']);
		if( empty($argus['ns']) )	return 'default';
		return $argus['ns'];
	}
	protected function	helper_set_value_i($argus, $key, $default='')
	{
		if(!isset($argus['value']))	$argus['value']	= $default;
		$this->__[ $this->helper_ns($argus) ][$key]	= $argus['value'];
	}


	protected function	_set_show_author($argus)
	{
		if(!isset($argus['value']))	$argus['value']	= '';
		$this->__[ $this->helper_ns($argus) ]['show']['author']	= ($argus['value']=='true' or $argus['value']=='1')?'true':'false';
	}	
	protected function	_set_show_date($argus)
	{
		if(!isset($argus['value']))	$argus['value']	= '';
		$this->__[ $this->helper_ns($argus) ]['show']['date']	= ($argus['value']=='true' or $argus['value']=='1')?'true':'false';
	}
	protected function	_set_show_time($argus)
	{
		if(!isset($argus['value']))	$argus['value']	= '';
		$this->__[ $this->helper_ns($argus) ]['show']['time']	= ($argus['value']=='true' or $argus['value']=='1')?'true':'false';
	}
	protected function	_set_show_comment($argus)
	{
		if(!isset($argus['value']))	$argus['value']	= '';
		$valids	= array('disable', 'private', 'public');
		$this->__[ $this->helper_ns($argus) ]['show']['comment']	= (in_array($argus['value'], $valids))?$argus['value']:'disable';
	}
	protected function	_set_show_singlelink($argus)
	{
		if(!isset($argus['value']))	$argus['value']	= '';
		$this->__[ $this->helper_ns($argus) ]['show']['singlelink']	= ($argus['value']=='true' or $argus['value']=='1')?'true':'false';
	}

	protected function	_set_status($argus)
	{
		if(!isset($argus['value']))	$argus['value']	= '';
		$this->__[ $this->helper_ns($argus) ]['is_published']	= ($argus['value']=='active' or $argus['value']=='true' or $argus['value']=='1')?'active':'inactive';
	}




//	protected function	_set_publish_up($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= 'now';
//		$this->__[ $this->helper_ns($argus) ]['publish_up']	= $argus['value'];
//	}
//	protected function	_set_publish_down($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= 'never';
//		$this->__[ $this->helper_ns($argus) ]['publish_down']	= $argus['value'];
//	}
//	protected function	_set_title1($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= '';
//		$this->__[ $this->helper_ns($argus) ]['title1']	= $argus['value'];
//	}
//	protected function	_set_title2($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= '';
//		$this->__[ $this->helper_ns($argus) ]['title2']	= $argus['value'];
//	}
//	protected function	_set_author($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= '';
//		$this->__[ $this->helper_ns($argus) ]['author']	= $argus['value'];
//	}
//	protected function	_set_description($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= '';
//		$this->__[ $this->helper_ns($argus) ]['description']	= $argus['value'];
//	}
//	protected function	_set_keywords($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= '';
//		$this->__[ $this->helper_ns($argus) ]['keywords']	= $argus['value'];
//	}
//	protected function	_set_abstract($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= '';
//		$this->__[ $this->helper_ns($argus) ]['abstract']	= $argus['value'];
//	}
//	protected function	_set_text($argus)
//	{
//		if(!isset($argus['value']))	$argus['value']	= '';
//		$this->__[ $this->helper_ns($argus) ]['text']	= $argus['value'];
//	}
//	protected function	_set_type($argus)
//	{
//		if(!is_numeric($argus['value']))	$argus['value']	= '1';
//		$this->__[ $this->helper_ns($argus) ]['type_id']	= $argus['value'];
//	}
//	protected function	_set_taxoterms($argus)
//	{
//		if(!is_array($argus['value']))	$argus['value']	= array();
//		$this->__[ $this->helper_ns($argus) ]['taxoterms']	= $argus['value'];
//	}
//	protected function	_set_usergroups($argus)
//	{
//		if(!is_array($argus['value']))	$argus['value']	= array();
//		$this->__[ $this->helper_ns($argus) ]['usergroups']	= $argus['value'];
//	}

//	protected function	_get_unic($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['unic'];
//	}	
//	protected function	_get_show_author($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['setting'][0];
//	}	
//	protected function	_get_show_date($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['setting'][1];
//	}
//	protected function	_get_show_time($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['setting'][2];
//	}
//	protected function	_get_show_comment($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['setting'][3];
//	}
//	protected function	_get_show_singlelink($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['setting'][4];
//	}
//	protected function	_get_status($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['is_published'];
//	}
//	protected function	_get_publish_up($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['publish_up'];
//	}
//	protected function	_get_publish_down($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['publish_down'];
//	}
//	protected function	_get_title1($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['title1'];
//	}
//	protected function	_get_title2($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['title2'];
//	}
//	protected function	_get_author($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['author'];
//	}
//	protected function	_get_description($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['description'];
//	}
//	protected function	_get_keywords($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['keywords'];
//	}
//	protected function	_get_abstract($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['abstract'];
//	}
//	protected function	_get_text($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['text'];
//	}
//	protected function	_get_type($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['type_id'];
//	}
//	protected function	_get_taxoterms($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['taxoterms'];
//	}
//	protected function	_get_usergroups($argus)
//	{
//		return $this->__[ $this->helper_ns($argus) ]['usergroups'];
//	}

}
?>