<?php
/*
	*	
*/



class Xal_Extension_RayaDars_ActivationAPP

{
	public function	run($argus)
	{
	die("Unable to connect to $site");
		if(!is_string($argus['sms.ns']))	$argus['sms.ns'] = 'default';
		
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'send'		: return $this->_send($argu); break;

			}
			
		}
	}
	
	

	protected $customer;
/* protected function _getProductList()
    {
        if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sqli = "SELECT sp_product_id FROM sold_products WHERE sp_customer_id='".$this->customer."' AND sp_status=1";
        $sql = "SELECT p_id, p_position FROM products WHERE p_id IN (".$sqli.") ORDER BY p_position";
        if( !$products = $this->DB->fetchAll($sql) ) return false;
        return $products;
    }*/
    protected function _getProductList()
    {
		die("jjgjg");
        if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_rd_data');
		$sqli = "SELECT cu_fname FROM customer_info WHERE cu_code=FDYS2";
      //  $sql = "SELECT p_id, p_position FROM products WHERE p_id IN (".$sqli.") ORDER BY p_position";
        if( !$products = $this->DB->fetchAll($sqli) ) return $products;
        return print "hello";
    }
	
}

?>