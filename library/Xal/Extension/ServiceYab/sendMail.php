<?php

//Coded by Mohammadreza Kadivar

class Xal_Extension_serviceyaab_sendMail
{
	
	public function	run($argus)
	{
		if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				//case 'sendMailToCustomer'	: return $this->_sendMailToCustomer($argu); break;
				case 'sendMailToAdmin'	: return $this->_sendMailToAdmin($argu); break;
			}
			
		}
	}

	
	protected function _sendMailToAdmin($argus)
	{
		
		use Zend\Mail;
		$mail = new Mail\Message('UTF-8');
		
		$email->setBodyHtml('سلام. این یک ایمیل آزمایشی است.');
		$email->setFrom('sail@serviceyaab.ir', 'serviceyaab');
		$email->setSubject("ثبت سفارش جدید");
		$email->addTo( 'kadivar@outlook.com' , 'rayaweb');
		
		$transport = new Mail\Transport\Sendmail();
		$transport->send($email);

	}
    
}

?>
