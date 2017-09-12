<?php
	class Xal_Extension_Erp_Crm
	{
		public function	run($argus)
		{
			foreach($argus as $ark=>$argu)
			{
				switch($ark)
				{
					case 'get.dataset'	: return $this->_getDataset($argu); break;
				}
			}
		}
		
		
		protected function _getDataset($argus)
		{
			//if(!$user = $this->IsUserLogin()) return -1;
			if(!isset($_REQUEST['schema']))return -2;
			$schemas = $_REQUEST['schema'];
			if(!is_array($schemas)) $schemas = array($schemas);
			
			if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_erp');

			foreach($schemas as $schema)
			{
				$pattern = "/^(\w+)(\[config\:([\w\.]+)\])?\.([\w\.]+)?\[([\w\.\,\s]+)\]/";
				if( preg_match($pattern, $schema, $matches))
				//return $matches;
				if(isset($matches[3]))
				{
					if($matches[3]=='SearchQuery')
					{
						$searchworld = $_REQUEST['refrence'];
						$sql = "SELECT *  FROM `crm_view_persons` WHERE `LastName` LIKE '%"
								.$searchworld."%' OR `CellPhone` LIKE '%"
								.$searchworld."%' OR `Phone` LIKE '%"
								.$searchworld."%' OR `Fax` LIKE '%"
								.$searchworld."%' OR `TeleFax` LIKE '%"
								.$searchworld."%' OR `emails` LIKE '%"
								.$searchworld."%' LIMIT 0,30";
						//return $sql;
						if(!$result = $this->DB->fetchAll($sql)) return array("schema"=>$schema , "data"=> array());
						return array("schema"=>$schema , "data"=> $result);
							

					}
					if($matches[1]=='person')
					{
						$refrence = $_REQUEST['refrence'];
						$sql = "SELECT *  FROM `crm_view_persons` WHERE `id`=".$refrence;
						//return $sql;
						if($result = $this->DB->fetchAll($sql))
							return array("schema"=>$schema , "data"=>$result);
						return $sql;
					}			
					
					//if(!is_object($this->config)) $this->config = $this->InitConfigs();
					//if(!$query = Rasta_Util_DotNotation::GetValue($this->config['query'][ $matches[2] ], $matches[3])) return -7;
					//if(!$this->IsPermitted($user,$query['acl'])) return -5;
				}
				
				/*$pattern = "/^(\w+)(\[config\:([\w\.]+)\])\.([\w\.]+)?\[([\w\.\,\s]+)\]/";
				if( preg_match($pattern, $schema, $matches))
				if(isset($matches[3]))
				{
					$refrence = $_REQUEST['refrence'];
					$sql = "SELECT *  FROM `crm_view_persons` WHERE `id`=".$refrence;
					//return $sql;
					if($result = $this->DB->fetchAll($sql))
						return array("schema"=>$schema , "data"=> array(array("person"=>$result[0])));
				}*/		
			}
			
			return -100;

			
		}
		
	}
?>
