<?php

class Xal_Extension_ServiceYab_ContactUsForm
{
	
	public function	run($argus)
	{
		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				
				case 'form.form_wrapper'			: return $this->_ContactUsForm($argu); break;
								

			}
			
		}
	}
	
	protected function	_form_wrapper($argus)
	{
		
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');

		$data["rf_name"]			= (isset($argus["name"]))?$argus["name"]:'';
		$data["rf_email"]			= (isset($argus["email"]))?$argus["email"]:'';
		$data["rf_subject"]			= (isset($argus["subject"]))?$argus["subject"]:'';
		$data["rf_message"]			= (isset($argus["message"]))?$argus["message"]:'';
		
		date_default_timezone_set('Asia/Tehran');
		$submit_date = date('Y/m/d h:i:s a', time());
		
		$data["submit_date"] = $submit_date ;

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_sy_data');

		if($this->DB->insert('ContactUsForm', $data))

		{

			$data['co_id'] = $this->DB->lastInsertId();

		}

	}
}

?>

