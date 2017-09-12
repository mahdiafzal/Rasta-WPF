<?php
/*
	*
*/

class Xal_Extension_Email
{

	protected $_sent=0;
	protected $_max	=5;

	public function	run($argus)
	{
		if( !isset($argus['method']) )	return '';
		$method	= $argus['method'];
		unset($argus['method']);
		switch($method)
		{
			case 'new'			: return $this->_new($argus); break;
			case 'set.smtp'		: return $this->_set_smtp($argus); break;
			case 'set.body'		: return $this->_set_body($argus); break;
			case 'set.from'		: return $this->_set_from($argus); break;
			case 'set.subject'	: return $this->_set_subject($argus); break;
			case 'add.to'		: return $this->_add_to($argus); break;
			case 'add.cc'		: return $this->_add_cc($argus); break;
			case 'add.bcc'		: return $this->_add_bcc($argus); break;
			case 'send'			: return $this->_send($argus); break;
		}
	}


	protected function	_new($argus)
	{
		$ns	= $this->helper_ns($argus);
		$this->__[ $ns ] = new Zend_Mail('UTF-8');
		if( isset($argus['ns']) ) unset($argus['ns']);
		if(!is_array($argus) or count($argus)==0) return;
		$i=0;

		foreach($argus as $ark=>$argu)
		{
			if($i>20) break;
			if($ark=='new') continue;
			$nar	= array('method'=>$ark, 'ns'=>$ns, 'value'=>$argu);
			$result	= $this->run($nar);
			$i++;
		}

		return $result;
	}

	protected function	_set_smtp($argus)
	{
		$ns	= $this->helper_ns($argus);
		if(!isset($this->__[ $ns ]) or !is_array($argus['value']) )	return;
	}
	protected function	_set_body($argus)
	{
		$ns	= $this->helper_ns($argus);
		if(!isset($this->__[ $ns ]) or !is_string($argus['value']) )	return;
		$this->__[ $ns ]->setBodyHtml($argus['value']);
	}
	protected function	_set_from($argus)
	{
		$ns	= $this->helper_ns($argus);
		if(!isset($this->__[ $ns ]) or !is_array($argus['value']) )	return;
		$this->__[ $ns ]->setFrom($argus['value']['address'], $argus['value']['name']);
	}
	protected function	_set_subject($argus)
	{
		$ns	= $this->helper_ns($argus);
		if(!isset($this->__[ $ns ]) or !is_string($argus['value']) )	return;
		$this->__[ $ns ]->setSubject($argus['value']);
	}
	protected function	_add_to($argus)
	{
		$ns	= $this->helper_ns($argus);
		if(!isset($this->__[ $ns ]) or !isset($argus['value']) )	return;
		if(isset($argus['value']['address']) )
			$this->__[ $ns ]->addTo($argus['value']['address'], $argus['value']['name']);
		else
			foreach($argus['value'] as $case)
				if(isset($case['address']) )
					$this->__[ $ns ]->addTo($case['address'], $case['name']);
	}
	protected function	_add_cc($argus)
	{

	}
	protected function	_add_bcc($argus)
	{

	}
	protected function	_send($argus)
	{
		if($this->_max <= $this->_sent) return false;
		$ns	= $this->helper_ns($argus);
		if(!isset($this->__[ $ns ]))	return;
		try
		{
			$this->__[ $ns ]->send();
			$this->_sent++;
		}
		catch (Zend_Exception $e)
		{
			return false;
		}
		return true;
	}

	protected function	helper_smtp($argus)
	{
		$config = array('auth' => 'login',
						'username' => 'myusername',
						'password' => 'password');

		$transport = new Zend_Mail_Transport_Smtp('mail.server.com', $config);

	}
	protected function	helper_ns($argus)
	{
		$argus['ns']	= trim($argus['ns']);
		if( empty($argus['ns']) )	return 'default';
		return $argus['ns'];
	}

}
?>
