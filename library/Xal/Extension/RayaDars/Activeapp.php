<?php
/*
	*	
*/



class Xal_Extension_RayaDars_Activeapp
{
	
	public function	run($argus)
	{
		
	
		if(!is_string($argus['ep.ns']))	$argus['ep.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'product'	: return $this->_getProductList($argus); break;
				case 'test'		: return $this->_getsysid(); break;

			}
			
		}
	}
	
	
	 protected function _getProductList($argus)
    {
       				
		$cuid = addslashes($_POST['cid']);
		$sysid = addslashes($_POST['sid']);
	
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		//$this->DB->insert('app_devices','hgsao');
		
		
	//	$sql = "SELECT * FROM `app_devices` WHERE `cu_id`='FDYS2';";
	//	$devices = $this->DB->fetchAll($sql);
		
	//	if(!is_array($devices))
	//	{
	//		if(!$this->DB->insert('app_devices','jesol')) return -3;
		//	$device = $data['sys_id'];
	//	}
		
		
		
		
		$sqli = "SELECT sp_product_id FROM sold_products WHERE sp_customer_id LIKE '".$cuid."' AND sp_status=1";
		
       if( !$products = $this->DB->fetchAll($sqli) ) return false;
    
		$list = array();
		foreach($products as $order)
		{
		  if($order["sp_product_id"]=="12314")	$order["sp_product_id"] = "12344";
		  $list[] = $order["sp_product_id"];;	
		}
		return $list;
	

		
		}
	
    
	
	
	
	

	
	
	
	
	


}

?>