<?php
/*
	*	
*/
class Scenario_Model_FetchFeed 
{

	public function	__construct($scenario)
	{
		$this->DB			= Zend_Registry::get('front_db');
		$this->scenario		= $scenario;
		$this->site			= Zend_Registry::get('site');
		$this->properties	= $this->getScenrioProperties();
		$this->items		= $this->getScenrioRtcs();
	}
	public function	getScenrioFamily()
	{
		$sql		= "SELECT *  FROM `wbs_scenario_allsubs` WHERE ".Application_Model_Pubcon::get(1100)." AND `sc_id`=".$this->scenario['id'];
		$result	= $this->DB->fetchAll($sql);
		if($result)
			if(count($result)!=0)
				$sc_ids		= array_unique( array_filter( explode('/', $result[0]['subs']) ) );
		$sc_ids[]	= $this->scenario['id'];
		$sql	= implode("/%' OR `co`.`scenarios` LIKE '%/", $sc_ids);
		return $sql;
	}
	public function	getScenrioRtcs()
	{
		$sql_start	= 0;
		$sql	= "SELECT * FROM `wbs_rtcs` AS co LEFT JOIN `wbs_rtc_metadata` AS me ON `co`.`id`=`me`.`txt_id`"
				. " WHERE ".Application_Model_Pubcon::get(1111, 'co')." AND `co`.`is_published` != '0' AND `co`.`publish_up`<NOW() AND `co`.`publish_down`>NOW() "
				. " AND (`co`.`scenarios` LIKE '%/".$this->getScenrioFamily()."/%') "
				. "ORDER BY `co`.`publish_up` DESC LIMIT ".$sql_start." , ".$this->properties['count'];
		$result	= $this->DB->fetchAll($sql);
		if(@count($result)>0) return $result;
		return false;
	}
	public function	getScenrioProperties()
	{
		$properxml	=	'<root>'.$this->scenario['properties'].'</root>';
		$xml 		= 	new SimpleXMLElement($properxml); 			
		$properties['count']	= (int) $xml->c;
		return $properties;
	}
	public function generateFeed($mode='rss')
	{
		$config	= Zend_Registry::get('config');
		$feed = new Zend_Feed_Writer_Feed;
		
		$feed->setTitle($this->site['wb_title'].'::'.$this->scenario['title']);
		$feed->setGenerator(array(
			'name'		=> 'rastakcms',
			'version'	=> $config->version,
			'uri'		=> 'http://'.$config->base->portal,
		));

		$feed->setLink('http://'.$_SERVER['HTTP_HOST'].$this->scenario['uri']);
		$feed->setDescription("RSS Feed");
		$feed->setFeedLink('http://'.$_SERVER['HTTP_HOST'].$this->scenario['uri'].'?output=feed&mode='.$mode, $mode);
//		$feed->addAuthor(array(
//			'name'  => 'Paddy',
//			'email' => 'paddy@example.com',
//			'uri'   => 'http://www.example.com',
//		));
		$feed->setDateModified(time());
	//	$feed->addHub('http://pubsubhubbub.appspot.com/');
		 
		/**
		* Add one or more entries. Note that entries must
		* be manually added once created.
		*/
		if($this->items)
			foreach($this->items as $item)
			{
				$entry = $feed->createEntry();
				$entry->setTitle($item['title']);
				$entry->setLink('http://'.$_SERVER['HTTP_HOST'].'/rtc/'.$item['id']);
				$entry->addAuthor(array(
					'name'  => ( empty($item['author']) )?'NA':$item['author']
//					'email' => 'paddy@example.com',
//					'uri'   => 'http://www.example.com',
				));
				$entry->setDateCreated(strtotime($item['publish_up']));
				if($item['upt_date']=='0000-00-00 00:00:00')	$item['upt_date']	= $item['publish_up'];
				$entry->setDateModified(strtotime($item['upt_date']));
				
				$entry->setDescription( ( empty($item['description']) )?'NA':$item['description'] );
				if($mode=='rss') $entry->setContent( $item['content'] );
				$feed->addEntry($entry);
			}
		 
		/**
		* Render the resulting feed to Atom 1.0 and assign to $out.
		* You can substitute "atom" with "rss" to generate an RSS 2.0 feed.
		*/
		$out = $feed->export($mode);
		return $out;

	}

}
?>