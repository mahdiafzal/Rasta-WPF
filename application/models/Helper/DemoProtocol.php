<?php

class Application_Model_Helper_DemoProtocol extends Zend_Controller_Action_Helper_Abstract
{

	var $message		= array();
	
						
	public function preDispatch()
    {
							
		$request	= $this->getRequest();
		$module		= $request->getModuleName();
		$controller	= $request->getControllerName();
		$action		= $request->getActionName();
		$params		= $request->getParams();
		$restResources = array(
							array('admin.ajaxset.savepagecontent'	,array('actionForwarding', array('admin', 'ajaxset', 'savepageskin', $params)	)	),
							array('admin.ajaxset.*'					,array('sendMessage', 0)	),
							array('rtcmanager.addcnt.index'			,array('sendMessage', 1)	),
							array('rtcmanager.delcnt.index'			,array('sendMessage', 1)	),
							array('rtcmanager.doact.index'			,array('sendMessage', 1)	),							
							array('rtcmanager.editcnt.index'		,array('sendMessage', 1)	),
							array('admin.index.setsite'				,array('sendMessage', 1)	)
							);
		$excepResources = array(
							'admin.ajaxset.savepageskin'
							);
		foreach($excepResources as $value)
		{
			$exceptions	= explode('.', $value);
			if($exceptions[0]=='*') return true;
			if($exceptions[0]==$module)
				if($exceptions[1]=='*') return true;
				elseif($exceptions[1]==$controller)
					if($exceptions[2]=='*') return true;
					elseif($exceptions[2]==$action) return true;
			
		}
		foreach($restResources as $value)
		{
			$resources	= explode('.', $value[0]);
			if($resources[0]=='*')
			{
				$this->$value[1][0]($value[1][1]);
				return true;
			}
			if($resources[0]==$module)
				if($resources[1]=='*')
				{
					$this->$value[1][0]($value[1][1]);
					return true;
				}
				elseif($resources[1]==$controller)
					if($resources[2]=='*') 
					{
						$this->$value[1][0]($value[1][1]);
						return true;
					}
					elseif($resources[2]==$action) 
					{
						$this->$value[1][0]($value[1][1]);
						return true;
					}
		}
    }
	public function actionForwarding($data)
	{
		//$this->_forward('index', 'index', 'default', array('webpage' => '11'));
		
		$this->getRequest()->setParams($data[3]) 
							->setModuleName($data[0])
							->setControllerName($data[1])
							->setActionName($data[2])
							->setDispatched(false);
		
	}
	public function sendMessage($index)
	{
		$message[0]	= 'شما از نمونه نمایشی استفاده می کنید و دسترسی شما به این قسمت محدود شده است';
		$message[1]	= '<HTML dir="rtl"><HEAD><META http-equiv="Content-Type" content="text/html; charset=UTF-8" /><TITLE>نمونه نمایشی</TITLE>'
					. '</HEAD><BODY><H1>دسترسی محدود</H1>شما از نمونه نمایشی استفاده می کنید و دسترسی شما به این قسمت محدود شده است.<P>'
					. '<HR><H4><A href="'.$_SERVER['HTTP_REFERER'].'"> بازگشت</A></H4></BODY></HTML>';
		if($index == 0)	echo json_encode(array(false, $message[0]));
		if($index == 1) echo $message[1];
		die();
	}
 

}