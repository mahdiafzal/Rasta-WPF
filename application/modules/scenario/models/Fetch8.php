<?php 

class Scenario_Model_Fetch //extends Application_Model_Page_Free
{


	/*public function	__construct($data)
	{
		$this->isValid = false;
		if(!is_array($data['html.block']) or strlen( trim($data['html.block']['block']) )<4 ) return;
		
		//print_r($data); die();
		$this->userID	= Application_Model_User::ID();
		$this->DB 		= Zend_Registry::get('front_db');
		
		$this->scenario		= $data['scenario'];
		$this->contentPros	= $data['content.properties'];
		$this->htmlBlock	= $data['html.block'];
		$this->renderer		= $data['renderer'];

		$this->doScenarioXAL();
		$this->configs	= $this->getScenrioConfiguration();
		
		$this->feedlink_rss	= '/feed/'.$this->scenario['id'].'?mode=rss';
		$this->feedlink_atom= '/feed/'.$this->scenario['id'].'?mode=atom';

		//$this->sc_family	= $this->getScenrioFamily();
		$this->params	= $this->getParams();
		$this->isValid	= true;

		$this->setPageTitle();		
	}*/
	public function	__construct($data)
	{
		$this->isValid = false;
		
		
		//print_r($data); die();
		$this->userID	= Application_Model_User::ID();
		$this->DB 		= Zend_Registry::get('front_db');
		
		$this->scenario		= $data['scenario'];
		$this->contentPros	= $data['content.properties'];
		$this->renderer		= $data['renderer'];
		$this->htmlBlock	= array();
		if($this->renderer!='AJAX')
		{
			if(!is_array($data['html.block']) or strlen( trim($data['html.block']['block']) )<4 ) return;
			$this->htmlBlock	= $data['html.block'];
		}
		$this->doScenarioXAL();
		$this->configs	= $this->getScenrioConfiguration();
		
		$this->feedlink_rss	= '/feed/'.$this->scenario['id'].'?mode=rss';
		$this->feedlink_atom= '/feed/'.$this->scenario['id'].'?mode=atom';

		//$this->sc_family	= $this->getScenrioFamily();
		$this->params	= $this->getParams();
		$this->isValid	= true;

		//$this->setPageTitle();		
	}
	public function doScenarioXAL()
	{
		if( !is_object($this->_XAL) ) $this->helper_ignite_XAL();
		$this->_XAL->setTheRunningMode('SAFE_MODE');
		if( !empty($this->scenario['xal']) )
		{
			$this->scenario['xal']	= $this->_XAL->run('<execution>'.$this->scenario['xal'].'</execution>');
		}		
	}
	public function	getScenrioConfiguration()
	{
		$config['count']	= (is_numeric($this->scenario['xal']['var:config']['count']))?$this->scenario['xal']['var:config']['count']:30;
		$config['count']	= (is_numeric($this->contentPros['data.count']))?$this->contentPros['data.count']:$config['count'];
		$config['start']	= (is_numeric($this->scenario['xal']['var:config']['start']))?$this->scenario['xal']['var:config']['start']:0;
		$config['paging']	= (isset($this->scenario['xal']['var:config']['paging']))?$this->scenario['xal']['var:config']['paging']:'false';
		//$config['taxoterm']	= (isset($this->scenario['xal']['var:taxoterm']))?$this->scenario['xal']['var:taxoterm']:'false';
		$config['taxoterm']	= (isset($this->scenario['xal']['var:taxoterm']))?$this->scenario['xal']['var:taxoterm']:false;
		$config['order']	= (isset($this->scenario['xal']['var:order']))?$this->scenario['xal']['var:order']:false;
		$config['find']	= (isset($this->scenario['xal']['var:find']))?$this->scenario['xal']['var:find']:false;
		$config['user_params']	= (isset($this->scenario['xal']['var:myparams']))?$this->scenario['xal']['var:myparams']:'false';
		
		/// Fields
		$config['fields']	= 'co.id , co.title AS `title1`, co.ltn_name AS `title2`, co.publish_up AS `datetime`, co.description AS `abstract` ';
		if(!empty($this->contentPros['data.fields']))
		{
			$ex_fls	= implode(',', array_intersect( array_map( trim, explode(',', $this->contentPros['data.fields']) ) , array('id','title1','title2','abstract','text', 'datetime') ) );
			$fl_ad	= array('id'=>'co.id ', 'title1'=>'co.title AS `title1` ', 'title2'=>'co.ltn_name AS `title2` ', 'abstract'=>'co.description AS `abstract` ', 'text'=>'co.content AS `text` ', 
				'datetime'=>'co.publish_up AS `datetime` ' );
			$config['fields']	= str_replace( array_keys($fl_ad), array_values($fl_ad), $ex_fls);
		}
		
		/// Order
		if($config['order'])
		{
			if(preg_match("/^(\s*(id|title1|title2|datetime)\s+(DESC|ASC|desc|asc)\s*)$/", $config['order']))
			{
				$fl_or	= array('id'=>'co.id ', 'title1'=>'co.title', 'title2'=>'co.ltn_name', 'datetime'=>'co.publish_up');
				$config['order']	= str_replace( array_keys($fl_or), array_values($fl_or), $config['order']);
			}
			else
				$config['order'] = ' co.publish_up DESC ';
		}
		else
			$config['order'] = ' co.publish_up DESC ';
			

		
		
		/// HTML Blocks
		$config['block']	= '';
		if($this->renderer!='AJAX')
			$config['block']	= (isset($this->contentPros['skin.block']) and $this->contentPros['skin.block']=='metablock')?'metablock':'normal';
		
		return $config;
	}
	public function	getParams()
	{
		$params['start'] = 0;
		if($this->configs['paging']=='true')
			if(is_numeric($_POST['start'][$this->scenario['id']]))		$params['start'] = $_POST['start'][$this->scenario['id']];
			elseif(is_numeric($_GET['start'][$this->scenario['id']]))	$params['start'] = $_GET['start'][$this->scenario['id']];
		return $params;
	}
	public function	getScenrioRtcs()
	{
		
		if($this->configs['taxoterm']=='false') return '';
		$sql_0	= 'SELECT '.$this->configs['fields'].', co.type_id, me.author, me.extra_data ';
		$sql_1	= 'SELECT COUNT(co.`id`) ';
		$sql_2_1= ' FROM `wbs_rtcs` AS co ';
		$sql_2_2= ' FROM `wbs_rtcs` AS co LEFT JOIN `wbs_rtc_metadata` AS me ON co.id = me.txt_id ';
		$sql_3	= ' WHERE '.Application_Model_Pubcon::get(1111, 'co')
				. ' AND co.type_id NOT IN (SELECT ts_ct_id FROM `wbs_content_type_setting` WHERE '.Application_Model_Pubcon::get(1100).' AND (ts_status=0 OR ts_data_sc="/0/") )'
				. ' AND co.`is_published` != 0 AND co.`publish_up`<=NOW()  AND co.`publish_down`>=NOW()';
				
		//$sql_4	= ' AND (co.`taxoterms` RLIKE "'.$this->scenario['taxoquery'].'") '; //????????
		//$sql_4	= ' AND (co.`taxoterms` RLIKE "'.$this->configs['taxoterm'].'") '; //????????
		$sql_4	= '';
		if(is_array($this->configs['taxoterm']))
		{
			$this->configs['taxoterm'] = array_filter($this->configs['taxoterm']);
			if(count($this->configs['taxoterm'])>0)
			{
				$this->configs['taxoterm'] = implode('") AND (co.`taxoterms` RLIKE "', $this->configs['taxoterm']);
				$sql_4	= ' AND (co.`taxoterms` RLIKE "'.$this->configs['taxoterm'].'") ';
			}
		}
		elseif(is_string($this->configs['taxoterm']))
			$sql_4	= ' AND (co.`taxoterms` RLIKE "'.$this->configs['taxoterm'].'") ';
		
		//else
		//	$sql_4	= ' AND (co.`taxoterms` RLIKE "'.$this->configs['taxoterm'].'") ';
		
		/// Find
		if(is_array($this->configs['find']))
		{
			if(isset($this->configs['find']['title1']))
			{
				$sql_4	.= ' AND (co.`title` LIKE "'.$this->configs['find']['title1'].'") ';
			}
		}
		
		//$sql_5	= ' ORDER BY `publish_up` DESC ';
		$sql_5	= ' ORDER BY '. $this->configs['order'];
		//$sql_6	= ' LIMIT '.$this->params['start'].' , '.$this->configs['count'];
		$sql_6	= ' LIMIT '.$this->configs['start'].' , '.$this->configs['count'];
		
		//$count = $sql_0.$sql_2_2.$sql_3.$sql_4.$sql_5.$sql_6;
		//return array('count'=>$count);
		
		//die($sql_0.$sql_2_2.$sql_3.$sql_4.$sql_5.$sql_6);
		if(! $count		= $this->DB->fetchOne( $sql_1.$sql_2_1.$sql_3.$sql_4 ) ) return false;
		if(! $content	= $this->DB->fetchAll( $sql_0.$sql_2_2.$sql_3.$sql_4.$sql_5.$sql_6 ) )	return false;
		
		$count = $sql_0.$sql_2_2.$sql_3.$sql_4.$sql_5.$sql_6;
		//print_r(array('content'=>$content, 'count'=>$count));
		//die();
		return array('content'=>$content, 'count'=>$count);
	}
	public function fetchAll($mode)
	{
		if(!$this->isValid) return '';
		
		if( !$rtcData = $this->getScenrioRtcs() )
		{
			if($this->configs['block']=='metablock')	return $this->_scenMetaBlocker($rtcData);
			else										return '';
		}
		
		switch($mode)
		{
			case 'html':
				if($this->configs['block']=='normal')			return $this->_scenBlocker($rtcData);
				elseif($this->configs['block']=='metablock')	return $this->_scenMetaBlocker($rtcData);
				break;
			case 'json':
				return $this->_scenJSON($rtcData);
				break;
		}
	}
	
	protected function	_scenBlocker($rtcs)
	{

		//$value	= $this->htmlBlock;
		//print_r($this->htmlBlock); die();		
		$out	= '';
		foreach($rtcs['content'] as $content)
		{
			//if(is_numeric($content['type_id']) and $content['type_id']==3)
			//	if(strlen( trim($this->htmlBlocks[$section][$stype]['bm_code']) )>4)	
			//		$content = $this->_rtcTypeFn3($content, array('type'=>$this->htmlBlocks[$section][$stype]['bm_type'], 'code'=>$this->htmlBlocks[$section][$stype]['bm_code']));
			
			if( strlen( trim($content['extra_data']) )>4 )	$content['user_params']	= $this->_parseUserParams($content['extra_data']);
				
			
			$content['title1']	= (isset($content['title1']))?$content['title1']:'';
			$content['title2']	= (isset($content['title2']))?$content['title2']:'';
			
			$sysParams['#rasta-unic#']					= (!empty($content['id']))?$content['id']:'';
			$sysParams['#rasta-type#']					= 'scenario';
			$sysParams['#rasta-blockcontent#']			= $content['text'];
			$sysParams['#rasta-blockcontent-abstract#']	= $content['abstract'];
			$sysParams['#rasta-blockheader#']			= $content['title1'];
			$sysParams['#rasta-blockheader2#']			= $content['title2'];
			$sysParams['#rasta-content-datetime#']		= $content['datetime'];
			
			
			
			/*$sysParams['#rasta-content-author#']		= '';
			$sysParams['#rasta-content-date#']			= '';
			$sysParams['#rasta-content-time#']			= '';


			$sysParams['#rasta-blockcontent-abstract#']	= $content['abstract'];
			$sysParams['#rasta-blockheader2#']			= $content['title2'];
			$sysParams['#rasta-content-author#']		= $content['author'];
			$sysParams['#rasta-content-date#']			= $content['date'];
			$sysParams['#rasta-content-time#']			= $content['time'];			

			
			$sysParams['#rasta-content-author-display#']	= 'display:none;';
			$sysParams['#rasta-content-date-display#']		= 'display:none;';
			$sysParams['#rasta-content-time-display#']		= 'display:none;';
			$sysParams['#rasta-content-comment-display#']	= 'display:none;';
			if( !empty($content['author']) )			$sysParams['#rasta-content-author-display#']= '';
			if( !empty($content['date']) )				$sysParams['#rasta-content-date-display#']	= '';
			if( !empty($content['time']) )				$sysParams['#rasta-content-time-display#']	= '';
			if( !empty($content['comment']['link']) )
			{
				$sysParams['#rasta-content-comment-display#']	= '';
				$sysParams['#rasta-content-comment-count#']	= $content['comment']['count'];
				$sysParams['#rasta-content-comment-link#']	= $content['comment']['link'];
			}*/

			$blockWITHtext	= str_replace(array_keys($sysParams), array_values($sysParams), $this->htmlBlock['block']);
			
			if(is_array($content['user_params']))
				$blockWITHtext	= str_replace(array_keys($content['user_params']), array_values($content['user_params']), $blockWITHtext);
			
			if(is_array($content['custom_var']))
				$blockWITHtext	= str_replace(array_keys($content['custom_var']), array_values($content['custom_var']), $blockWITHtext);
			$out	= $out . $blockWITHtext;		
		}
		return $out;
	}
	
	protected function	_scenMetaBlocker($rtcs)
	{
		if( strlen( trim($this->htmlBlock['bm_code']) )<4 )	return;
		
		if( !$rtcs )
		{
			if($this->renderer!='admin')	return '';
			$sysParams['#rasta-unic#']					= $this->scenario['id'];
			$sysParams['#rasta-type#']					= 'scenario';
			$sysParams['#rasta-blockcontent#']			= '';
			$sysParams['#rasta-blockcontent-abstract#']	= '';
			$sysParams['#rasta-blockheader#']			= $this->scenario['title'];
			$sysParams['#rasta-blockheader2#']			= $this->scenario['latin_title'];
			$sysParams['#rasta-content-datetime#']		= '';
			
			return str_replace(array_keys($sysParams), array_values($sysParams), $this->htmlBlock['block']);
		}
		//print_r($rtcs); die();
		if($this->htmlBlock['bm_type']==1)
		{
			$patterns	= explode('#rasta-separator#',  $this->htmlBlock['bm_code']);
			if(count($patterns)<3)	return;
			
			foreach($rtcs['content'] as $con)
			{
				$con['title1']	= (isset($con['title1']))?$con['title1']:'';
				$con['title2']	= (isset($con['title2']))?$con['title2']:'';
				
				if( strlen( trim($con['extra_data']) )>4 )	$con['user_params']	= $this->_parseUserParams($con['extra_data']);
				
				$sysParams['#rasta-content-datetime#']		= (isset($con['datetime']))?Application_Model_Localize::datetime($con['datetime']):'';
				$sysParams['#rasta-blockcontent#']			= (isset($con['text']))?$con['text']:'';
				$sysParams['#rasta-blockheader#']			= ($this->page['page_dir']==2 )?$con['title2']:$con['title1'];
				$sysParams['#rasta-blockheader2#']			= $con['title2'];
				$sysParams['#rasta-unic#']					= (isset($con['id']))?$con['id']:'';
				$sysParams['#rasta-blockcontent-abstract#']	= (isset($con['abstract']))?$con['abstract']:'';
				
				$tmpout	= str_replace(array_keys($sysParams), array_values($sysParams), $patterns[1]);
				if(is_array($con['user_params']))
					$tmpout	= str_replace(array_keys($con['user_params']), array_values($con['user_params']), $tmpout);
				$out[]	= $tmpout;
			}
			
			//$sysParams	= array();
			//$sysParams['#rasta-scenario-unic#']		=
			//$sysParams['#rasta-scenario-uri#']		= $scen['data']['uri'];
			//$sysParams['#rasta-scenario-title#']	= $this->scenario['title'];
			//$sysParams['#rasta-scenario-count#']	= $rtcs['count'];			
			//$fixparts	= str_replace(array_keys($sysParams), array_values($sysParams), array('pre'=>$patterns[0], 'post'=>$patterns[2]) );
			
			$js_out	= '<script> rasta_scenario={'; //['.$this->scenario['id'].']';
			if($this->configs['paging']=='true')	$js_out	= $js_out.'count:'.$rtcs['count'];
			$js_out	= $js_out.'}</script>';
			
			$out	= $patterns[0].' '.implode('', $out).' '.$patterns[2].$js_out;

			$sysParams['#rasta-unic#']					= $this->scenario['id'];
			$sysParams['#rasta-type#']					= 'scenario';
			$sysParams['#rasta-blockcontent#']			= $out;
			$sysParams['#rasta-blockcontent-abstract#']	= '';
			$sysParams['#rasta-blockheader#']			= $this->scenario['title'];
			$sysParams['#rasta-blockheader2#']			= $this->scenario['latin_title'];
			$sysParams['#rasta-content-datetime#']		= '';
			
			return str_replace(array_keys($sysParams), array_values($sysParams), $this->htmlBlock['block']);
			
			//print_r($out); die();
			//return $out;
		}
		elseif($this->htmlBlock['bm_type']==3)
		{
			foreach($rtcs['content'] as $key=>$con)
			{
				if( strlen( trim($con['extra_data']) )>4 )	$scen['content'][$key]['extra']	= $this->_parseUserParams($con['extra_data'], 'normal');
				if( isset($con['datetime']) )	$scen['content'][$key]['datetime']	= Application_Model_Localize::datetime($con['datetime']);
			}
			if( !is_object($this->_XAL) )	$this->helper_ignite_XAL();
			$this->_XAL->setTheRunningMode('SAFE_MODE');
			$ext_data	= '<execution>'.stripslashes($this->htmlBlock['code']).'</execution>';
			$result	= $this->_XAL->run($ext_data, array('var:scenario'=>$rtcs) );
			if(is_string($result))	return $result;
			return '';
		}
	}
	protected function	_scenJSON($rtcs)
	{
		$json = array();
		$json['scenario']	= array(
				"id"		=> $this->scenario['id'],
				"title1"	=> $this->scenario['title'],
				"title2"	=> $this->scenario['latin_title'],
				"count"		=> 0
			);
		if( !$rtcs )	return $json;

		foreach($rtcs['content'] as $key=>$con)
		{
			if( strlen( trim($con['extra_data']) )>4 )	$con['extra']	= $this->_parseUserParams($con['extra_data'], 'normal');
			unset($con['extra_data']);
			if(isset($con['datetime'])) $con['datetime'] = Application_Model_Localize::datetime($con['datetime']);
			$rtcs['content'][$key] = $con;
		}
		$json['scenario']['count'] = $rtcs['count'];
		$json['content'] = $rtcs['content'];
		return $json;
	}	
	protected function _parseUserParams($ext_data, $mode='systemparam')
	{
		if( !is_object($this->_XAL) )
		{
			$this->helper_ignite_XAL();
			$this->_XAL->disableAll();
			$this->_XAL->enable(array('execution'));
		}
		$ext_data	= '<execution>'.$ext_data.'</execution>';
		$result	= $this->_XAL->run($ext_data);
		$user_params	= array();
		if(is_array($result) and count($result)>0)
			foreach($result as $key=>$val) 
				if( preg_match('/^var\:/', $key) )
				{
					if($mode=='normal')				$key	= str_replace('var:', '', $key);
					elseif($mode=='systemparam')	$key	= str_replace('var:', '#rasta-', $key).'#';
					$user_params[ $key ]	= $val;
					
				}
		if(count($user_params)>0)	return $user_params;
		return false;
	}
	
	
	
	public function	setPageTitle()
	{
		$this->page['wb_page_title']	= $this->scenario['title'];
		if($this->page['page_dir']==2) $this->page['wb_page_title'] = $this->scenario['latin_title'];
	}
	public function	getScenrioFamily()
	{
		$sql		= "SELECT *  FROM `wbs_scenario_allsubs` WHERE ".Application_Model_Pubcon::get(1100)." AND `sc_id`=".$this->scenario['id'];
		$result	= $this->DB->fetchAll($sql);
		if($result) //$sc_ids[]	= $this->scenario['id'];
			if(count($result)!=0)	//$sc_ids[]	= $this->scenario['id'];
				$sc_ids		= array_unique( array_filter( explode('/', $result[0]['subs']) ) );
		$sc_ids[]	= $this->scenario['id'];
		$sql	= implode("/%' OR `scenarios` LIKE '%/", $sc_ids);
		//die($sql);
		return $sql;
	}

	public function	getPagingHtml()
	{
//		$sql			= "SELECT COUNT(`id`) FROM `wbs_rtcs` WHERE wbs_id='".WBSiD. "' AND `is_published` != '0' AND `scenarios` LIKE '%/".$this->scenario['id']."/%' ";
		$sql	= "SELECT COUNT(`id`) FROM `wbs_rtcs` WHERE ".Application_Model_Pubcon::get()." AND `is_published` != '0' AND `publish_up`<=NOW()  AND `publish_down`>=NOW()"
				. " AND (`scenarios` LIKE '%/".$this->sc_family."/%') ";
		$result			= $this->DB->fetchOne($sql);
		if($result <= $this->properties['count']) return NULL;
		$paging_count	= ceil($result/$this->properties['count']);
		
		if(! $paging_num = $this->getParam(0)) $paging_num	= 1;
		if(! is_numeric($paging_num)) $paging_num	= 1;
		
		$paging_html	= '<div id="contPaging" style="width:100%;height:35px;text-align:center;">';
		for($i=1; $i<=$paging_count; $i++)
		{
			$paging_href	= preg_replace('/^([^\!]+)\!?\d*\,?/', '${1}!'.$i.',', $_SERVER['REQUEST_URI']);
			$paging_one		= '&nbsp;<a href="'.$paging_href.'">'.$i.'</a>&nbsp;';
			if($i == $paging_num ) $paging_one	='&nbsp;<b>'.$i.'</b>&nbsp;';
			$paging_html	.= $paging_one;
		}
		$paging_html	.= '</div>';
		return $paging_html;
	}

	
	public function helper_ignite_XAL($handler='')	
	{
		if( is_object($handler) )	$this->_XAL	= $handler;
		else	$this->_XAL	= new Xal_Servlet('SAFE_MODE');
		$this->_XAL->set_env(array('ENV_USER_ID'=> $this->userID));
	}	

	


}