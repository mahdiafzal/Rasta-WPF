<?php


class Xal_Extension_RayaDars_Book_Getdata
{

	public function	run($argus)
	{
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'get.dataset'	: return $this->get($argu); break;
				//case 'force.download'	: return $this->_forceDownload($argu); break;
			}
		}
	}
    protected function get($argu)
    {
        $result = array();
        if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_book');
        $sql  = "SELECT * FROM `users`";
        $sql2 = "SELECT * FROM `book1` ";
        $sql3 = "SELECT * FROM `groupingList`";
        
        
        
        if($temp_result = $this->DB->fetchAll($sql3))
            $result['part1'] = $temp_result; 
//        return array('Result'=>$result);
        
        if($temp_result = $this->DB->fetchAll($sql2))
            $result['part2'] = $temp_result; 
        
        if($temp_result = $this->DB->fetchAll($sql))
            $result['part3'] = $temp_result; 
        
        return array('Result'=>$result);
       
    }
    
    
    
	
}

?>
