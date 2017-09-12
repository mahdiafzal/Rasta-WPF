<?php
/*
	view1 = Home
	view2 = Course
	view3 = Lesson
	view4 = Test
	--l1-------------------------l2----------------------l3----------------------l4--
	Course (view1)
		|-------------------- About (view2)
		|-------------------- Lesson (view2)
		|												|-------------------- Content (view3)
		|												|-------------------- Help (view3)
		|												|-------------------- Question (view3)
		|																								|-------------------- Answer (view3)
		|												|-------------------- Quiz (view3)
		|																								|-------------------- Test (view4)
		|-------------------- Question (view2)
		|												|-------------------- Answer (view2)
		|-------------------- Help (view2)
		|-------------------- Quiz (view2)
		|												|-------------------- Test (view4)

*/
class Xal_Extension_RayaDars_LmsUser
{

	public function	run($argus)
	{
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'reset.password'	: return $this->_resetPassword($argu); break;

			}
		}
	}

	protected function	_resetPassword($argus)
	{
		if(!isset($_REQUEST['token'])) return 0;
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_lms');

		// Needle Variables
		$token = $_REQUEST['token'];
		$password = $_REQUEST['new'];

		$sql = "SELECT * FROM `user_temp_password` WHERE `status`=1 AND `token` LIKE '$token' AND (`datetime`> DATE_ADD(NOW(), INTERVAL -1 DAY))";
		if(!$result = $this->DB->fetchAll($sql)) die($this->DB->fetchOne("SELECT `content` FROM `static_pages` WHERE `name`='MESSAGE-RESET-PASSWORD-INVALID-TOKEN'"));
		if($result[0]['password']!=$password) die($this->DB->fetchOne("SELECT `content` FROM `static_pages` WHERE `name`='MESSAGE-RESET-PASSWORD-INVALID-PASSWORD'"));
		$sql = "UPDATE `users` SET `prev_password` = `password`, `password` = MD5('$password'), `force_to_change`=2   WHERE `password`!= MD5('$password') AND `u_id` = ".$result[0]['user_id'];
		if($this->DB->query($sql))
			Zend_OpenId::redirect('http://lms.rayadars.com/#/view(loginform).show');

		//die($this->DB->fetchOne("SELECT `content` FROM `static_pages` WHERE `name`='MESSAGE-RESET-PASSWORD-SUCCESSFULLY-DONE'"));
		die($this->DB->fetchOne("SELECT `content` FROM `static_pages` WHERE `name`='MESSAGE-RESET-PASSWORD-UNKNOWN-ERROR'"));
	}

	protected function LogActivity($request, $login_user, $result_status)
	{
		if(empty($request['path'])) return;
		$action_list = array('enter'=> 1, 'show'=> 2, 'download'=> 3, 'answer'=> 4, 'new'=> 5, 'setpublic'=> 6, 'unsetpublic'=> 7, 'start'=> 8, 'user'=> 9, 'register'=>10);
		$data = array(
			'user_id'=> $login_user['id'],
			//'namespace' =>$request['namespace'] ,
			'path' =>$request['path'] ,
			'action' =>((isset($action_list[$request['action']]))?$action_list[$request['action']]:0)
			//'params' => json_encode($request['action-params']),
			//'result' => $result_status
		  );
		$this->DB->insert('activity_logs', $data);
	}



}

?>
