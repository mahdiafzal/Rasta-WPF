<?php
class Application_Model_Localize
{

	static function datetime($timestamp) 
	{
		switch(LANG)
		{
			case 'fa':return self::getPersianTimestamp($timestamp, 'datetime');
			case 'en':return $timestamp;
		}
	}
	
	public function getPersianTimestamp($timestamp, $section=NULL)
	{
		$date	= new Rasta_Pdate;
		$pdate	= $date->gregorian_to_persian(substr($timestamp, 0,4), substr($timestamp, 5,2), substr($timestamp, 8,2));
		$ptimestamp[]	= $pdate[0].'/'.$pdate[1].'/'.$pdate[2];
		$ptimestamp[]	= substr($timestamp, 11,8);
		if(empty($section))return '';
		if($section=='date')return $ptimestamp[0];
		if($section=='time')return $ptimestamp[1];
		if($section=='datetime')return $ptimestamp[0].' '.$ptimestamp[1];
	}
	
}	

?>