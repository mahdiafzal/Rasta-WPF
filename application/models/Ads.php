<?php

class Application_Model_Ads
{
	
	public function	__construct()
	{
		if(!$this->checkExpiration()) $this->setAds();
	}
	public function checkExpiration()
	{
		$this->site	= Zend_Registry::get('site');
		$today 				= strtotime(date("Y-m-d H:i:s"));
		$expiration_date 	= strtotime($this->site['wb_expirdate']);
		if ($expiration_date < $today) return false;
		return true;	
	}
	public function setAds()
	{
		$this->DB	= Zend_Registry::get('front_db');
		$now		= date("Y-m-d H:i:s");
		$sql		= 'SELECT `id`, `text` FROM `ads` WHERE `start_time`<= "'.$now.'" AND `end_time`>= "'.$now.'" ORDER BY `last_fetch` ASC LIMIT 0 , 1';
		$result		= $this->DB->fetchAll($sql);
		if(!is_array($result) or count($result)==0) return false;
		$sql		= 'UPDATE `ads` SET `visit`= (`visit`+1), `last_fetch`="'.$now.'" WHERE `id`='.$result[0]['id'].' LIMIT 1';
		$this->DB->query($sql);
		$ads	= '<div id="a'.$result[0]['id']
				. '" style="width:122px;height:260px;background-color:white;border: 1px solid #C0C0C0;position:absolute;top:1px;left:1px;z-index: 10;text-align:center;">'
				. '<div dir="rtl" style="width:116px;height:13px;font-family:tahoma;font-size:11px;padding:3px;text-align:center;border-bottom:1px solid #C0C0C0;">'
				. '<div style="width:13px;height:13px;float:right;color:#FF0000;font-weight:bold;cursor:pointer;" onclick="document.getElementById (\'a'.$result[0]['id'].'\').style.display=\'none\'">X</div><span>تبلیغات</span></div>'
				. '<a target="_blank" href="/ads/index/link/case/'.$result[0]['id'].'"> '
				. $result[0]['text']
				. '</a></div>';
		define('ADS', $ads);	
	}
}