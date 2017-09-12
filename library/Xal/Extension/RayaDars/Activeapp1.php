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
				case 'product'	: return $this->_getProductList(); break;
				case 'test'		: return $this->_webServiceTest($argu); break;

			}
			
		}
	}
	
	
	 protected function _getProductList()
    {
       				
		
	
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sqli = "SELECT sp_product_id FROM sold_products WHERE sp_customer_id='FDYS2' AND sp_status=1";
        $sql = "SELECT p_id, p_position FROM products WHERE p_id IN (".$sqli.") ORDER BY p_position";
        if( !$products = $this->DB->fetchAll($sql) ) return false;
       return $products;
	/*	$list = array();
		foreach($products as $order)
		{
			$data = array();	 	
			$data["id"]			= $order["co_id"];
			$data["datetime"]	= $order["co_datetime"];
			$data["customer"]	= $order["co_customer"];
			$data["discode"]	= $order["co_discount_code"];
			$data["discount"]	= $order["co_discount"];
			$data["paymode"]	= $order["co_paymode"];
			$data["costsum"]	= $order["co_price"]+$order["co_tax"]+$order["co_costs"];
			$data["ispayed"]	= $order["co_ispayed"];

			$list[] = $data;	
		}
		return $list;*/
	
		
		}
    
	
	
	
	

	
	
	
	
	


}

?>