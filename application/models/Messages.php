<?php
class Application_Model_Messages
{
	static function	message($code)
	{
		$pcon_num	= (!defined('WBSgR'))?1010:1110;
		$DB 		= Zend_Registry::get('front_db');
		$sql		= "SELECT `wbs_id`,`message` FROM `wbs_helper_message` WHERE ".Application_Model_Pubcon::get($pcon_num)." AND `lang`='".LANG."' AND `msg_code`='". $code ."'";
		$result		= $DB->fetchAll($sql);
		if(count($result)==1) return $result[0]['message'];
		if(count($result)> 1) foreach($result as $value) if($value['wbs_id']==WBSiD)return $value['message'];
		return false;
	}
}
?>