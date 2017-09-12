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
class Xal_Extension_RayaDars_LmsV4
{

	public function	run($argus)
	{
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'get.dataset'	: return $this->_getDataset($argu); break;
				//case 'force.download'	: return $this->_forceDownload($argu); break;
			}
		}
	}
	protected function addslashestoparams($input)
	{
		if(is_string($input))	return addslashes($input);
		if(is_array($input))
				foreach ($input as $key => $value)
					if(is_string($value))
						$input[$key] = addslashes($value);
					elseif(is_array($value))
						foreach ($value as $k => $v)
							if(is_string($v))
								$input[$key][$k] = addslashes($v);
		return $input;
	}
	protected function	_getDataset($argus)
	{


		if(!is_array($_POST['command'])) return 0;
		//$level_to_view = array('1' => 'Course' , '2' => 'Lesson' , '3' => 'Content' );

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_lms');
		$result['IsLogin'] = false;

		// Needle Variables
		$request['namespace'] = $this->addslashestoparams($_POST['command'][0]);
		$request['path'] = $this->addslashestoparams($_POST['command'][1]);
		$request['action'] = $this->addslashestoparams($_POST['command'][2]);
		$request['action-params'] = $this->addslashestoparams($_POST['command'][3]);
		//$result['debug-login'] = $request;

		if($request['namespace']=="Login" & $request['path']=="lost")
		{
			$validator = new Zend_Validate_EmailAddress();
			$result['status'] = 'lost-invalid-email';
			if (!$validator->isValid($request['action-params']['email']))
				return array('CommandParts'=>array('View', 'lostform', 'show', ''), 'Result'=> $result);

	    // email is in user profile list
			$sql = "SELECT * FROM `user_profile` WHERE `email`='".$request['action-params']['email']."'";
			$result['status'] = 'lost-invalid-user';
			if(!$temp_result = $this->DB->fetchAll($sql))
				return array('CommandParts'=>array('View', 'lostform', 'show', ''), 'Result'=> $result);

			$result['status'] = 'lost-error-one-for-many';
			if(count($temp_result)>1)
				return array('CommandParts'=>array('View', 'lostform', 'show', ''), 'Result'=> $result);

			$sql = "SELECT * FROM `user_temp_password` WHERE `user_id`=".$temp_result[0]['user_id']." AND (`datetime`> DATE_ADD(NOW(), INTERVAL -1 DAY))";
			$result['status'] = 'lost-more-than-5';
			if($sooner_tries = $this->DB->fetchAll($sql))
				if(count($sooner_tries)>=5)
					return array('CommandParts'=>array('View', 'lostform', 'show', ''), 'Result'=> $result);

			$new_password = mt_rand(149276, 99999999);
			$sql = "SELECT MAX(`utp_id`) FROM `user_temp_password`";
			$token = $this->DB->fetchOne($sql);
			$token = md5('lms'.$token);
			//$sql = "INSERT INTO `user_temp_password` (`user_id`, `token`, `password`) VALUES (".$temp_result[0]['user_id'].", MD5((SELECT CONCAT('lms', MAX(`utp_id`)) FROM `user_temp_password`))  )"
			$sql = "INSERT INTO `user_temp_password` (`user_id`, `token`, `password`) VALUES (".$temp_result[0]['user_id'].", '$token', '$new_password' )";
			$this->DB->query($sql);
			$sql = "SELECT * FROM `static_pages` WHERE `name`='EMAIL-TEMP-RESET-PASSWORD'";
			$email_body = $this->DB->fetchRow($sql);

			$email_body_vars['#user-first-name#'] = $temp_result[0]['fname'];
			$email_body_vars['#user-last-name#'] = $temp_result[0]['lname'];
			$email_body_vars['#user-new-password#'] = $new_password;
			$email_body_vars['#reset-verify-url#'] = "http://lms.rayadars.com/dandelion?form_id=f7177163c833dff4b38fc8d2872f1ec6&token=$token&new=$new_password";
			$email_body['content'] = str_replace(array_keys($email_body_vars), array_values($email_body_vars), $email_body['content']);
			$email = new Zend_Mail('UTF-8');
			$email->setBodyHtml($email_body['content']);
			$email->setFrom('support@rayadars.com', 'رایادرس');
			$email->setSubject("سامانه مدیریت یادگیری رایادرس ::: بازیابی کلمه عبور");
			//$temp_result[0]['email'] = 'ali.parhamnia@gmail.com';
			$email->addTo( $temp_result[0]['email'] , $temp_result[0]['fname'].' '.$temp_result[0]['lname']);
			try
			{
				$email->send();
				$result['status'] = 'lost-success';
			}
			catch (Zend_Exception $e)
			{
				$result['status'] = 'lost-email-failed';
			}
			return array('CommandParts'=>array('View', 'lostform', 'show', ''), 'Result'=> $result);
		}

		# login block
		if($request['namespace']=="Logout")
		{
			$result['IsLogin'] = false;
			$this->LoginUser(null);
		}
		#end of logout block
		# login block
		if($request['namespace']=="Login" & $request['path']=="in")
		{
			$arabic_numbers = array('۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '۰');
			$english_numbers = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
			$request['action-params']['username'] = str_replace($arabic_numbers, $english_numbers, $request['action-params']['username']);
			$request['action-params']['password'] = str_replace($arabic_numbers, $english_numbers, $request['action-params']['password']);

			$sql = 'SELECT `u_id` as `id`, `username`, `groups`, `force_to_change` FROM `users` WHERE status=1 AND username="'.addslashes($request['action-params']['username']).'" AND password="'.md5(addslashes($request['action-params']['password'])).'" ' ;

			if(!$temp_result = $this->DB->fetchAll($sql))
			{
				//return $sql;
				$result['IsLogin'] = false;
				$result['status'] = 'login-failed';
				$this->LoginUser(null);
				return array('CommandParts'=>array('View', 'loginform', '', ''), 'Result'=> $result);
			}
			else
			{
				$login_user = $temp_result[0];
				$result['IsLogin'] = true;
				$result['status'] = 'success';
				$this->LoginUser($login_user);
				$this->LogActivity($request, $login_user, $result['status']);
				$request['namespace'] = 'Home'; $request['path'] = ''; $request['action'] = ''; $request['action-params'] = '';
				if($login_user['force_to_change']==1)
				{
					return array('CommandParts'=>array('View', 'registerform', '', ''), 'Result'=> $result);
				}
			}
			// $result['IsLogin'] = true;
			// $login_user['id'] = 1;
			// $login_user['groups'] = '/1/';
			// $request['namespace'] = 'Home'; $request['path'] = ''; $request['action'] = ''; $request['action-params'] = '';

		}
		elseif(!$login_user = $this->IsUserLogin())
		{
			$result['IsLogin'] = false;
			return array('CommandParts'=>array('View', 'loginform', '', ''), 'Result'=> $result);
		}
		elseif (is_numeric($login_user['id']))
		{
			$result['IsLogin'] = true;
		}
		# end of ligin block

		# register user profile
		if($request['namespace']=="Login" & $request['path']=="register" & $login_user['username']==$request['action-params']['username'])
		{
			$sql = 'SELECT * FROM `sabtenam` WHERE `org_id`="'.addslashes($request['action-params']['username']).'"' ;
			//$result['debug']= $sql;
			//return $result;
			if(!$temp_result = $this->DB->fetchAll($sql))
			{
				$result['IsLogin'] = false;
				$result['status'] = 'failed';
				$result['message'] = 'فراگیر با مشخصات وارد شده قبلا ثبت نام نشده است.';
				return array('CommandParts'=>array('View', 'registerform', '', ''), 'Result'=> $result);
			}
			else
			{
				if($request['action-params']['password']==$request['action-params']['repassword'])
				{
					//$set1 = '`user_id`="'.$login_user['id'].'", `fname`="'.$temp_result[0]['first_name'].'", `lname`="'.$temp_result[0]['last_name'].'", `email`="'.addslashes($request['action-params']['email']).'", `cellphone`="'.addslashes($request['action-params']['cellphone']).'"';
					$set2 = '`password`="'.md5($request['action-params']['password']).'", `force_to_change`=0';
					$set3 = '`user_id`="'.$login_user['id'].'", `father_rename`="'.addslashes($request['action-params']['fathername']).'", `try`=(`try`+1)';
					// sql TRANSACTION
					$sql = "START TRANSACTION;\n";
					$sql .= "INSERT INTO `user_profile` (`user_id`, `fname`, `lname`, `email`, `cellphone`) VALUES ('".$login_user['id']."', '".$temp_result[0]['first_name']."', '".$temp_result[0]['last_name']."', '"
									.addslashes($request['action-params']['email'])."', '".addslashes($request['action-params']['cellphone'])."');\n";
					$sql .= "UPDATE `users` SET $set2 WHERE `u_id`=".$login_user['id'].";\n";
					$sql .= "UPDATE `sabtenam` SET $set3 WHERE `org_id`=".$login_user['username'].";\n";
					$sql .= "COMMIT;";
					//$result['debug2']= $sql;
					$this->DB->query($sql);
					$login_user['force_to_change']=0;
					$this->LoginUser($login_user);
					$request['namespace'] = 'Home'; $request['path'] = ''; $request['action'] = ''; $request['action-params'] = '';
				}
				//if($temp_result[0]['try']>=10) $result['message'] = '';
				//if($temp_result[0]['user_id']>0) $result['message'] = '';
				// if($request['action-params']['email']) //fathername
				// if($request['action-params']['cellphone'])
				// if($request['action-params']['fathername'])



			}
		}
		# end of register user profile

		if($login_user['force_to_change']==1)
		{
			$result['IsLogin'] = false;
			$result['User'] = $login_user['username'];
			return array('CommandParts'=>array('View', 'registerform', '', ''), 'Result'=> $result);
		}

		$sql_params = $this->GenrateSqlParams($request, $login_user);
		$entity = $this->EntityDetailFromPath($request['path']);

		// course dataset
		// Course, l=Lesson, c=Content, q=Question, n=Answer, a=About, h=Help, v=Activity, z=Quiz
		$like_unlike_action = false;
		if($request['action']=="like" | $request['action']=="unlike" )
		{
			$data = array();
			$data['vote'] = ($request['action']=='like')?1:2;


			$sql = "SELECT *  FROM `like_unlike` WHERE `user_id` = ".$login_user['id']." AND `path`='".$request['path']."'";
			//$result['debug'] = $sql;
			if($vote_result = $this->DB->fetchRow($sql))
			{
				if($vote_result['vote']==$data['vote']) $data['vote'] = 0;
				$sql = "UPDATE  `like_unlike` SET  `vote` = ".$data['vote']." WHERE  `lu_id`=".$vote_result['lu_id'];
				$this->DB->query($sql);
			}
			else
			{
				$data['path'] = $request['path'];
				$data['user_id'] = $login_user['id'];
				$this->DB->insert('like_unlike', $data);
			}


			// try
			// {
			// 	$data['path'] = $request['path'];
			// 	$data['user_id'] = $login_user['id'];
			// 	$this->DB->insert('like_unlike', $data);
			// } catch (Exception $e) {
			// 	$sql = "UPDATE  `like_unlike` SET  `vote` = (CASE WHEN `vote`=".$data['vote']." THEN 0 ELSE ".$data['vote']." END) WHERE  `user_id` =".$login_user['id']." AND  `path` = '".$request['path']."';";
			// 	$this->DB->query($sql);
			// 	//$this->DB->update('like_unlike', $data, '`path`="'.$request['path'].'" AND `user_id`='.$login_user['id']);
			// }
			$result['status'] = 'done';
			//$this->LogActivity($request, $login_user, $result['status']);
			//$this->DB->closeConnection();
			//return array('CommandParts'=>array_values($request), 'Result'=> $result);

			$like_unlike_action = true;
		}

		if(	($request['namespace']=="Question" | $request['namespace']=="Answer") &
				($request['action']=="setpublic" | $request['action']=="unsetpublic" | $request['action']=="deny") )
		{
			$is_public = ($request['action']=='setpublic')?1:0;
			$qn_status = ($is_public==1)?1:(($request['action']=='deny')?2:3);
			//return $entity;

			if(strpos($request['path'], '$')===false)
			{
				$record_where = "`path`='".$request['path']."' AND ".$sql_params['member-of-admin-group'];
				// sql TRANSACTION
				$sql = "START TRANSACTION;\n";
				$sql .= "UPDATE `unsorted_qu_an` SET `status`='".$qn_status."', `is_public`='".$is_public."' WHERE ".$record_where.";\n";
				$sql .= "UPDATE `courses` SET `status`='".$is_public."' WHERE ".$record_where.";\n";
				$sql .= "COMMIT;";
				$this->DB->query($sql);
			}
			else
			{
				$path_parts = explode("$", $request['path']);
				$record_id = $path_parts[1]; //preg_replace("/^[^\$]\$/", "", $request['path']);
				$record_where = "`c_id`=".$record_id." AND ".$sql_params['member-of-admin-group'];
				if($request['action']=="deny")
				{
					$sql = "UPDATE `unsorted_qu_an` SET `status`='".$qn_status."', `is_public`='".$is_public."' WHERE ".$record_where.";\n";
					$this->DB->query($sql);
				}
				else
				{
					// check is question or answer unsorted
					$sql = "SELECT * FROM `unsorted_qu_an` WHERE $record_where AND `path`='".$path_parts[0]."__'";
					if($this->DB->fetchOne($sql))
					{
						// compute new path
						$sql = "SELECT MAX(`path`) as `last` FROM `courses` WHERE `path` LIKE '".$path_parts[0]."__'";
						if($last_entity_path = $this->DB->fetchOne($sql))
							$new_path = $path_parts[0].(Rasta_Base34_Operation::addOneTo( substr($last_entity_path, -2, 2) ));
						else
							$new_path = $path_parts[0].'01';

						// sql TRANSACTION
						$sql = "START TRANSACTION;\n";
						$sql .= "UPDATE `unsorted_qu_an` SET `status`='".$qn_status."', `is_public`='".$is_public."', `path`='".$new_path."' WHERE ".$record_where.";\n";
						$sql .= "INSERT INTO `courses` SELECT NULL, 1, CURRENT_TIMESTAMP, NULL,'پرسش-پاسخ', `desc`, `path`, `level`, 'PT', NULL, NULL, `portal`, `user_groups`, `admin_groups` FROM `unsorted_qu_an` WHERE `path`='".$new_path."';";
						$sql .= "INSERT INTO `like_unlike` (`path`) VALUES ('".$new_path."');";
						$sql .= "COMMIT;";
						$this->DB->query($sql);
					}
				}
			}

			$result['status'] = 'done';

			if($request['namespace']=='Question') //preg_replace("/\.?[\w\d]{3}$/", "", $new_path)
				$result = $this->GetQuestionDataset(array('path'=> $entity['parent-path'], 'level'=> $entity['level']-1 ), $sql_params, $result);
			elseif ($request['namespace']=='Answer')
				$result = $this->GetAnswerDataset(array('path'=> $entity['parent-path'], 'level'=> $entity['level']-1 ), $sql_params, $result);


		}

		if(	($request['namespace']=="Discussion") &
				($request['action']=="setpublic" | $request['action']=="unsetpublic" | $request['action']=="deny" | $request['action']=="recycle") )
		{
			$result['status'] = 'failed';
			if(strpos($request['path'], '$')===false)
				return $result;

			$path_parts = explode("$", $request['path']);
			$record_id = $path_parts[1];
			if(!$parent_path = $this->DB->fetchOne("SELECT `path` FROM `discussion` WHERE `d_id`=".addslashes($record_id))) return $result;
			$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$parent_path.'" AND '.$sql_params['member-of-user-group'];
			//$sql = 'SELECT `rich_courses`.*, '.$sql_params['editable-field'].' FROM `rich_courses` WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$parent_path.'" AND '.$sql_params['member-of-user-group'];
			if(!$parent = $this->DB->fetchRow($sql)) return $result;

			switch ($request['action']) {
				case 'setpublic': $dis_status = 1; break;
				case 'unsetpublic': $dis_status = 2; break;
				case 'recycle':
					$dis_status = 3;
					$record_where = "`d_id`=".$record_id." AND `status`=4 AND user_id=".$login_user['id'];
					break;
				case 'deny':
					$dis_status = 4;
					$record_where = "`d_id`=".$record_id." AND `status`=3 AND user_id=".$login_user['id'];
					if($parent['editable']=='y') $dis_status = 5;
					break;
				default: $dis_status = 0; break;
			}
			//$result['debug1'] = $sql;

			if($parent['editable']=='y')
				$record_where = "`d_id`=".$record_id;
			elseif($dis_status != 3 & $dis_status != 4)
				return $result;

		//$result['debug2'] = $sql;

			$sql = "UPDATE `discussion` SET `status`='$dis_status' WHERE $record_where";
			$this->DB->query($sql);

			$result['status'] = 'done';
			$result = $this->GetDiscussionDataset(array('path'=> $parent_path), $login_user, $sql_params, $result, $parent);
		}

		if($request['namespace']=="Home")
		{
			$result = $this->GetHomeDataset($login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Course")
		{
			// validate course path: is available courses for user groups
			//$sql = 'SELECT * FROM `courses` WHERE status=1 AND level=1 AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'].' AND `path` NOT IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].')' ;
			$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level=1 AND `path`="'.$entity['root-path'].'" AND '.$sql_params['member-of-user-group'];
			//$result['debug'] = $sql;

			//$sql = 'SELECT '.$sql_params['fields'][1].' WHERE '.$sql_params['is-active-started-notended'].'  AND level=1 AND `path`="'.$entity['root-path'].'" AND '.$sql_params['member-of-user-group'].$sql_params['vote-query'];
			if(!$temp_result = $this->DB->fetchAll($sql)) return $result; // this path not available for user
			// $result[$request['namespace']]['debug'] = $sql;
			$result[$request['namespace']]['Metadata'] = $temp_result[0];


			// course register action
			if($request['action']=='register')
			{
				try
				{
					// register course
					$this->DB->insert('user_course_registration', array('user_id'=>$login_user['id'], 'path'=>$request['path']));
				} catch (Exception $e) { }
			}

			// reset values
			$result[$request['namespace']]['Discussion'] = '';
			$result[$request['namespace']]['Questions'] = '';
			$result[$request['namespace']]['UnQuestions'] = '';
			$result[$request['namespace']]['Activities'] = '';


			// l=Lesson
			$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.l__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Lessons'] = $this->DB->fetchAll($sql);

			// // q=question
			// $result = $this->GetQuestionDataset($entity, $sql_params, $result);

			// a=About
			$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.a__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['About'] = $this->DB->fetchAll($sql);

			// h=Help
			$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.h__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Helps'] = $this->DB->fetchAll($sql);

			// z=Quiz
			$result = $this->GetQuizDataset($entity, $sql_params, $result);
		}
		elseif ($request['namespace']=="Lesson")
		{
			// validate course path: is available lesson for user groups
			$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND level=2 AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'];
			//$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND level=2 AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'].$sql_params['vote-query'];
			if(!$temp_result = $this->DB->fetchAll($sql)) return $result; // this path not available for user
			$result[$request['namespace']]['debug'] = $sql;
			$result[$request['namespace']]['Metadata'] = $temp_result[0];

			// reset values
			$result[$request['namespace']]['Discussion'] = '';
			$result[$request['namespace']]['Questions'] = '';
			$result[$request['namespace']]['UnQuestions'] = '';


			// c=Content
			$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'c__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Contents'] = $this->DB->fetchAll($sql);

			// // q=Question
			// $result = $this->GetQuestionDataset($entity, $sql_params, $result);

			// a=About
			$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'a__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['About'] = $this->DB->fetchAll($sql);

			// h=Help
			$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'h__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Helps'] = $this->DB->fetchAll($sql);

			// z=Quiz
			$result = $this->GetQuizDataset($entity, $sql_params, $result);

		}
		elseif ($request['namespace']=="Content")
		{
			if($request['action']=='show')
			{
				$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" '.$sql_params['vote-query'] ;
				$result[$request['namespace']]['Items'] = $this->DB->fetchAll($sql);
			}
			if($request['action']=='download')
			{
				$sql = 'SELECT `src` FROM `courses` WHERE status=1 AND level=3 AND `src` LIKE "http://%" AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ' ;
				if($temp_result = $this->DB->fetchAll($sql))
				{
					$result[$request['namespace']]['DownloadSrc'] = str_replace(':1010', ':1011', $temp_result[0]['src']);
					$result['status'] = 'success';
				}
				else
						$result['status'] = 'failed';
			}
		}
		elseif ($request['namespace']=="Quiz" & $request['action']=="ended")
		{
			$result = $this->SetQuizEnded($request, $login_user, $sql_params, $result);

		}
		elseif ($request['namespace']=="Quiz" & $request['action']=="start")
		{
			$result = $this->GetQuizTestsDataset($entity, $login_user, $sql_params, $result);

		}
		elseif ($request['namespace']=="Questions" & ($request['action']=="get" | $request['action']=="push" ) )
		{
			$result = $this->GetQuestionDataset($entity, $sql_params, $result, null, $request['action']);
		}
		elseif ($request['namespace']=="Question" & $request['action']=="answer")
		{
				$result = $this->GetAnswerDataset($entity, $sql_params, $result);
		}
		elseif ($request['namespace']=="Question" & $request['action']=="new")
		{
				$request['action-params'] = trim($request['action-params']);
				if(strlen($request['action-params'])<5) return $result;
				$reg_result = array('registeration' =>  array('status' => false , 'title'=>'ثبت سوال', 'message'=>'خطا در ثبت سوال!') ); //, 'hashcommand'=>''
				$result['status'] = 'error';
				$parent_path = preg_replace("/\.?q__/",'', $request['path']);
				//$reg_result['registeration']['hashcommand'] = "#/".(($entity['level']==2)?"course":"lesson")."(".$parent_path.").enter";
				$sql = 'SELECT * FROM `courses` WHERE status=1 AND level = '.($entity['level']-1).' AND '.$sql_params['member-of-user-group'].' AND `path` = "'.$parent_path.'" ' ;
				if($temp_result = $this->DB->fetchAll($sql))
				{
						$data['path'] = $request['path'];
						$data['desc'] = $request['action-params'];
						$data['level'] = $entity['level'];
						$data['user_id'] = $login_user['id'];
						$data['user_groups'] = $temp_result[0]['user_groups'];
						$data['admin_groups'] = $temp_result[0]['admin_groups'];

						if($this->DB->insert('unsorted_qu_an', $data))
						{
							$result['status'] = 'registered';
							$reg_result['registeration']['status'] = true;
							$reg_result['registeration']['message'] = 'سوال مطرح شده با موفقیت ثبت گردید و پس از تأیید مدیران دوره برای دیگران قابل مشاهده و پاسخ دهی خواهد بود.';
							$result = $this->GetQuestionDataset(array('path'=> $entity['parent-path'], 'level'=> $entity['level']-1 ), $sql_params, $result);
						}
				}
				$result = array_merge($result, $reg_result);
				$request['action-params'] = '';
		}
		elseif ($request['namespace']=="Answer" & $request['action']=="new")
		{
			$request['action-params'] = trim($request['action-params']);
			if(strlen($request['action-params'])<5) return $result;
				$reg_result = array('registeration' =>  array('status' => false , 'title'=>'ثبت پاسخ', 'message'=>'خطا در ثبت پاسخ!' ) ); // , 'hashcommand'=>''
				$result['status'] = 'error';

				$parent_path = preg_replace("/n__$/",'', $request['path']);
				//$reg_result['registeration']['hashcommand'] = "#/question(".$parent_path.").answer";
				$sql = 'SELECT * FROM `courses` WHERE status=1 AND '.$sql_params['member-of-user-group'].' AND `path` = "'.$parent_path.'" ' ;
				if($temp_result = $this->DB->fetchAll($sql))
				{
						$data['path'] = $request['path'];
						$data['desc'] =  $request['action-params'];
						$data['level'] = $temp_result[0]['level']+1;
						$data['user_id'] = $login_user['id'];
						$data['user_groups'] = $temp_result[0]['user_groups'];
						$data['admin_groups'] = $temp_result[0]['admin_groups'];

						if($this->DB->insert('unsorted_qu_an', $data))
						{
							$result['status'] = 'registered';
							$reg_result['registeration']['status'] = true;
							$reg_result['registeration']['message'] = 'پاسخ سوال با موفقیت ثبت گردید و پس از تأیید مدیران دوره برای دیگران قابل مشاهده خواهد بود.';
							$result = $this->GetAnswerDataset(array('path'=> $entity['parent-path'], 'level'=> $entity['level']-1 ), $sql_params, $result);

						}
				}

				$result = array_merge($result, $reg_result);
				$request['action-params'] = '';
		}
		elseif ($request['namespace']=="Discussion" & $request['action']=="new")
		{
				$reg_result = array('registeration' =>  array('status' => false , 'title'=>'ثبت نظر', 'message'=>'خطا در ثبت نظر!') ); //, 'hashcommand'=>''
				$result['status'] = 'error';
				$data['path'] = array_shift( explode('$', $request['path']) );
				$data['desc'] = $request['action-params'];
				$data['user_id'] = $login_user['id'];
				$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$data['path'].'" AND '.$sql_params['member-of-user-group'];
				//$sql = 'SELECT `rich_courses`.*, '.$sql_params['editable-field'].' FROM `rich_courses` WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$data['path'].'" AND '.$sql_params['member-of-user-group'];
				if(!$parent = $this->DB->fetchRow($sql)) return $result;
				if($this->DB->insert('discussion', $data))
				{
					$result['status'] = 'registered';
					$reg_result['registeration']['status'] = true;
					$reg_result['registeration']['message'] = 'نظر شما با موفقیت ثبت گردید و پس از تأیید مدیران دوره منتشر خواهد شد.';
					$result = $this->GetDiscussionDataset(array('path'=>$data['path']), $login_user, $sql_params, $result, $parent);
				}
				$result = array_merge($result, $reg_result);
		}
		elseif ($request['namespace']=="Discussion" & ($request['action']=="get" | $request['action']=="push" ) )
		{
			// Discussion
			$result = $this->GetDiscussionDataset($request, $login_user, $sql_params, $result, null, $request['action']);
		}

		elseif ($request['namespace']=="Activities" & $request['action']=="report")
		{
			// // // v=Activity
			// // $sql = 'SELECT * FROM `activity_report_view` ' ;
			// $login_user_id = $login_user['id'];
			// // $sql = "SELECT ual.*, co.name from (select `path`, `action`, count(`id`) AS `count`, max(`datetime`) AS `last`, min(`datetime`) AS `first`, (to_days(max(`datetime`)) - to_days(min(`datetime`))) AS `length`, 'u' AS `whois`
			// // from `activity_logs`
			// // where (`path` regexp '(^1$)|(^1\.[0-9a-z_]+$)') AND `user_id` = $login_user_id
			// // group by `path`, `action`) as ual left join `courses` as `co` on `ual`.`path` = `co`.`path`";
			// $sql= "SELECT ual.path, ual.action, ual.cnt, ual.last, ual.first, ual.length, ual.whois, co.name, PathActionFilter(ual.`path`, ual.`action`) as `filter`, SUM(ual.`cnt`) as `count`
			// 		from (select `path`, `action`, count(`id`) AS `cnt`, max(`datetime`) AS `last`, min(`datetime`) AS `first`, (to_days(max(`datetime`)) - to_days(min(`datetime`))) AS `length` , 'u' AS `whois`
			// 			from `activity_logs`
			// 			where (`path` regexp '(^1$)|(^1\.[0-9a-z_]+$)') and `action` in (0,1,2,3,4,5,8) and `user_id` = $login_user_id
			// 			group by `path`, `action`) as ual left join `courses` as `co` on `ual`.`path` = `co`.`path`
			// 			group by `filter`";
			// $u_result = $this->DB->fetchAll($sql);
			// // $sql = "SELECT ual.*, co.name from (select `path`, `action`, count(`id`) AS `count`, max(`datetime`) AS `last`, min(`datetime`) AS `first`, (to_days(max(`datetime`)) - to_days(min(`datetime`))) AS `length` , 'o' AS `whois`
			// // from `activity_logs`
			// // where (`path` regexp '(^1$)|(^1\.[0-9a-z_]+$)') and `action` in (select `index` from `termology` where `type`='command.action')
			// // group by `path`, `action`) as ual left join `courses` as `co` on `ual`.`path` = `co`.`path`";
			// $sql = "SELECT * FROM `activity_report_o`";
			// $o_result = $this->DB->fetchAll($sql);
			//
			// $result['Course']['Activities'] = array_merge($u_result, $o_result);

		}
		elseif (in_array($request['namespace'],array("Help", "Contact")) )
		{
			$sql = 'SELECT * FROM `static_pages` WHERE `name` = "'.$request['namespace'].'" ' ;
			$result['StaticPage'] = $this->DB->fetchRow($sql);
			$request['namespace'] = 'StaticPage';
		}
		if(!$like_unlike_action)
			$this->LogActivity($request, $login_user, ((isset($result['status']))?$result['status']:''));

		$this->DB->closeConnection();
		$request['action-params'] = '';
		return array('CommandParts'=>array_values($request), 'Result'=> $result);
	}
	protected function LogActivity($request, $login_user, $result_status)
	{
		if(empty($request['path'])) return;
		$action_list = array('enter'=> 1, 'show'=> 2, 'download'=> 3, 'answer'=> 4, 'new'=> 5,
		 'setpublic'=> 6, 'unsetpublic'=> 7, 'start'=> 8, 'user'=> 9, 'register'=>10, 'deny'=>11,
	 		'recycle'=>12);
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
	protected function IsUserLogin()
	{
		if(isset($_SESSION['LmsApp']))
			if(isset($_SESSION['LmsApp']['User']))
				if(is_numeric($_SESSION['LmsApp']['User']['id']))
					return $_SESSION['LmsApp']['User'];
		return false;
	}
	protected function LoginUser($user)
	{
		if(!isset($_SESSION['LmsApp']))
			$_SESSION['LmsApp'] = array();
		$_SESSION['LmsApp']['User'] = $user;
	}
	protected function GetHomeDataset($login_user, $sql_params, $result)
	{
		// available courses for user groups
		$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` NOT IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') '.$sql_params['order'] ;
		//$sql = 'SELECT '.$sql_params['fields'][0].' FROM `courses` WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` NOT IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') '.$sql_params['order'] ;
		$result['Home']['AvailableCourses'] = $this->DB->fetchAll($sql);

		// user courses
		$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') '.$sql_params['vote-query'].$sql_params['order'] ;
		//$result['debug'] = $sql;
		//return $result;
		$result['Home']['RegisteredCourses'] = $this->DB->fetchAll($sql);

		// notifications
		if($result['Home']['RegisteredCourses'])
		{
			foreach ($result['Home']['RegisteredCourses'] as $value)
				$RegisteredCoursesPathes[] = $value['path'];

			$sql = 'SELECT * FROM `notifications` WHERE ((`path` RLIKE "^'.implode('(\.[0-9a-z]+)?$") OR (`path` RLIKE "^', $RegisteredCoursesPathes).'(\.[0-9a-z]+)?$")) AND '.$sql_params['member-of-user-group'].' AND (`from_time` < NOW()) AND (`status`=1)';
			//$result['Home']['debug1'] = $sql;
			//return $result;
			if($temp_result= $this->DB->fetchAll($sql))
			{
				$sql_parts1 = array();
				$sql_parts2 = array();
				foreach ($temp_result as $value)
				{
					$sql_parts1[] = '(a.`path`="'.$value['path'].'" AND a.`action`="'.$value['action'].'")';
					$sql_parts2[] = '(b.`path`="'.$value['path'].'" AND b.`action`="'.$value['action'].'" AND b.`count`'.$value['rule'].')';
				}
				if(count($sql_parts1)==0) return $result;
				$sql = 'SELECT `message` FROM `notifications` WHERE (`status`=1) AND (`path`, `action`) IN (SELECT b.`path`, b.`action` FROM ((SELECT a.`path`, a.`action`, COUNT(a.`action`) as `count` FROM `activity_logs` AS a WHERE ('.implode(' OR ',$sql_parts1).') AND '.$sql_params['is-owner'].' GROUP BY a.`path`, a.`action`) as b) WHERE '.implode(' OR ',$sql_parts2).' )';
				$result['Home']['Notifications'] = $this->DB->fetchAll($sql);
				//$result['Home']['debug2'] = $sql;
			}
		}

		return $result;

	}
	protected function GetDiscussionDataset($request, $login_user, $sql_params, $result, $parent=null, $mode='get')
	{
		// get Discussion parent data
		if($parent==null)
		{
			$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'];
			//$sql = 'SELECT `rich_courses`.*, '.$sql_params['editable-field'].' FROM `rich_courses` WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$entity['path'].'" AND '.$sql_params['member-of-user-group'];
			if(!$parent = $this->DB->fetchRow($sql)) return $result;
		}
		//$result['debug'] = $parent;
		$request_namespace = ($parent['level']==1)?'Course':'Lesson';

		// Discussions
		if($mode=='get')
		{
			$sql = 'SELECT `d_id` as `id`, `status`, `reg_time` as `datatime`, `path`, `desc`, `lname`, `fname`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, "'.$parent['editable'].'" as `editable`  FROM `user_discussion` WHERE (`status`=1 OR ('.$sql_params['is-owner'].' AND `status` IN (1,3,4)) OR ("'.$parent['editable'].'"="y")) AND `path` = "'.$request['path'].'" ORDER BY `d_id` DESC '.$sql_params['limit'] ;
			//$result['debug2'] = $sql;
			$result[$request_namespace]['Discussion'] = $this->DB->fetchAll($sql);
		}
		elseif ($mode=='push')
		{
			$sql = 'SELECT `d_id` as `id`, `status`, `reg_time` as `datatime`, `path`, `desc`, `lname`, `fname`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, "'.$parent['editable'].'" as `editable`  FROM `user_discussion` WHERE `path` = "'.$request['path'].'" AND `d_id`>'.addslashes($request['action-params']['start']).' ORDER BY `d_id` DESC '.$sql_params['limit'] ;
			$result['prepend'][$request_namespace]['Discussion'] = $this->DB->fetchAll($sql);
			$sql = 'SELECT `d_id` as `id`, `status`, `reg_time` as `datatime`, `path`, `desc`, `lname`, `fname`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, "'.$parent['editable'].'" as `editable`  FROM `user_discussion` WHERE `path` = "'.$request['path'].'" AND `d_id`<'.addslashes($request['action-params']['end']).' ORDER BY `d_id` DESC '.$sql_params['limit'] ;
			$result['append'][$request_namespace]['Discussion'] = $this->DB->fetchAll($sql);

		}
		//$result['debug2'] = $sql;

		return $result;
	}
	protected function GetQuestionDataset($entity, $sql_params, $result, $parent=null, $mode='get')
	{
		if($parent==null)
		{
			// get Questions parent data
			$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$entity['path'].'" AND '.$sql_params['member-of-user-group'];
			if(!$parent = $this->DB->fetchRow($sql)) return $result;
		}
		$request_namespace = ($parent['level']==1)?'Course':'Lesson';
		$entity['path'] .= ($entity['level']==1)?'.':'';

		// Discussions
		if($mode=='get')
		{
			// q=Question
			$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote-with-score'].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'q__" '.$sql_params['vote-query'];
			$sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_unsorted_qu_an` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score'] ;
			$result[$request_namespace]['Questions'] = $this->DB->fetchAll($sql);
			// Unsorted Course Questions
			$sql = 'SELECT '.$sql_params['table-fields-user-unsorted-qu-an'].' WHERE (`status` NOT IN (0,2) OR (`status`!=0 AND '.$sql_params['is-owner'].') ) AND level='.($entity['level']+1).' AND (`reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) OR '.$sql_params['is-owner'].') AND ('.$sql_params['owner-or-admin'].') AND `path` LIKE "'.$entity['path'].'q__" ORDER BY  `c_id` DESC ';//.$sql_params['order'] ;
			$result[$request_namespace]['UnQuestions'] = $this->DB->fetchAll($sql);
		}
		elseif ($mode=='push')
		{

		}
		return $result;

		// // $entity is patent entity
		// $request_namespace = ($entity['level']==1)?'Course':'Lesson';
		// $entity['path'] .= ($entity['level']==1)?'.':'';
		// // q=Question
		// //$sql = 'SELECT '.$sql_params['fields'][3].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'q__" '.$sql_params['vote-query'].$sql_params['order-by-score'] ;
		// $sql = 'SELECT '.$sql_params['table-fields-courses-with-vote-with-score'].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'q__" '.$sql_params['vote-query'];
		// $sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_unsorted_qu_an` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score'] ;
		// $result[$request_namespace]['Questions'] = $this->DB->fetchAll($sql);
		//
		// // Unsorted Course Questions
		// $sql = 'SELECT '.$sql_params['table-fields-user-unsorted-qu-an'].' WHERE (`status` NOT IN (0,2) OR (`status`!=0 AND '.$sql_params['is-owner'].') ) AND level='.($entity['level']+1).' AND (`reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) OR '.$sql_params['is-owner'].') AND ('.$sql_params['owner-or-admin'].') AND `path` LIKE "'.$entity['path'].'q__" ORDER BY  `c_id` DESC ';//.$sql_params['order'] ;
		// $result[$request_namespace]['UnQuestions'] = $this->DB->fetchAll($sql);
		// return $result;
	}
	protected function GetAnswerDataset($entity, $sql_params, $result)
	{
		//$answer_view = ($entity['level']==2)?'Course':'Lesson';
		$answer_view = 'Course';

		// n=Answer
		$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote-with-score'].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'n__" '.$sql_params['vote-query'] ;
		$sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_unsorted_qu_an` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score'];
		$result['Question'][$answer_view]['Answers'] = $this->DB->fetchAll($sql);

		// Unsorted Answers
		$sql = 'SELECT '.$sql_params['table-fields-user-unsorted-qu-an'].' WHERE status NOT IN (0,2) AND level='.($entity['level']+1).' AND `reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) AND ('.$sql_params['member-of-admin-group'].') AND `path` LIKE "'.$entity['path'].'n__" ORDER BY  `c_id` DESC ';//.$sql_params['order'] ;
		//$result['debug2'] = $sql;

		$result['Question'][$answer_view]['UnAnswers'] = $this->DB->fetchAll($sql);
		return $result;
	}
	protected function GetQuizDataset($entity, $sql_params, $result)
	{
		// $entity is patent entity
		$request_namespace = ($entity['level']==1)?'Course':'Lesson';
		$entity['path'] .= ($entity['level']==1)?'.':'';


		$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND level='.($entity['level']+1).' AND `path` LIKE "'.$entity['path'].'z__" '.$sql_params['order'];
		//$result['debug2']['Quizes'] = $sql;
		//$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'z__" '.$sql_params['vote-query'].$sql_params['order'] ;
		$temp_result = $this->DB->fetchAll($sql);
		foreach ($temp_result as $key => $value)
		{
			$temp_result[ $key ]['desc'] = json_decode($temp_result[ $key ]['desc']);
			// $DateOfRequest = date("Y-m-d H:i:s", strtotime($_REQUEST["DateOfRequest"]));
		}
		$result[$request_namespace]['Quizes'] = $temp_result;
		return $result;
	}

	protected function SetQuizEnded($request, $login_user, $sql_params, $result)
	{
		//$result['registeration'] = array('status' => false , 'title'=>'ثبت پرسشنامه آزمون', 'message'=>'خطا در ثبت پرسشنامه آزمون!') ); //, 'hashcommand'=>''
		//$result['status'] = 'error';
		$result['serverMassage'] = 'پاسخنامه شما ثبت شد. برای شما آرزوی موفقت داریم.';
		$result['Quiz'] = '';
		$result['status'] = 'info-failed';
		$result['goto'] = '#/home'; // '#/course('.preg_replace("/\.?z[\w\d]{2}$/","",$request['path']).').enter';
		$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ';
		//$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" '.$sql_params['vote-query'];
		//$result['debug1'] = $sql;
		if(!$quiz_result = $this->DB->fetchAll($sql)) return $result;
		$test_ids = array();
		foreach ($request['action-params']['answers'] as $key => $value)
		{
			$test_ids[] = $value['id'];
		}
		//$result['debug2'] = $request['action-params'];

		if(count($test_ids)<5)	return $result;

		$sql = "SELECT `user_quiz_start`.*,  TIME_TO_SEC(TIMEDIFF(NOW(),`start_time` )) as `length` FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `status`=0 AND `path`='".$request['path']."' AND tests LIKE '".implode(', ', $test_ids)."' ORDER BY  `start_time` DESC LIMIT 1;";
		if(!$userquizstart = $this->DB->fetchRow($sql)) return $result;

		//$sql = "UPDATE `user_quiz_start` SET `status` = '1', `end_time` = NOW(), `quiz_answers` = '".json_encode($request['action-params']['answers'])."', `quiz_result` = '' WHERE `user_id`=".$login_user['id']." AND `status`!=1 AND `path`='".$request['path']."' AND tests LIKE '".implode(', ', $test_ids)."'";
		$sql = "UPDATE `user_quiz_start` SET `status` = '1', `end_time` = NOW(), `quiz_answers` = '".json_encode($request['action-params']['answers'])."', `quiz_result` = '' WHERE `user_id`=".$login_user['id']." AND `uq_id`=".$userquizstart['uq_id'];
		$this->DB->query($sql);
		$result['status'] = 'answers-registered';

		$sql = 'SELECT `t_id` as `id`, `correct`  FROM `quiz_tests` WHERE `t_id` IN ('.$userquizstart['tests'].') AND `path` LIKE "'.$request['path'].'"' ; //
		$all_tests = $this->DB->fetchAll($sql);

		foreach ($request['action-params']['answers'] as $ke => $val)
		{
			$ta[$ke] = $val['id'];
		}
		//	$ta = array_column($request['action-params']['answers'], 'id');

		$correct_count = 0;
		$incorrect_count = 0;
		$noanswer_count = 0;
		foreach ($all_tests as $value)
		{
			$k = array_search($value['id'], $ta);
			if($request['action-params']['answers'][$k]['id']!=$value['id']) continue;
			if($request['action-params']['answers'][$k]['answer']==0) $noanswer_count ++;
			elseif($value['correct']== $request['action-params']['answers'][$k]['answer']) $correct_count ++;
			else $incorrect_count++;
		}
		$quiz_result_inpercent = round(($correct_count/($correct_count+$incorrect_count+$noanswer_count))*100);

		$result['serverMassage'] = "پاسخنامه شما ثبت شد. برای شما آرزوی موفقت داریم.<br /><hr /><b>نتیجه این آزمون:</b><br />پاسخ های درست: $correct_count پاسخ<b> | </b> پاسخ های نادرست: $incorrect_count پاسخ<b> | </b>سوالات بی پاسخ: $noanswer_count سوال<br />نمره آزمون شما: $quiz_result_inpercent درصد";





		$sql = "SELECT `user_quiz_start`.*,  TIME_TO_SEC(TIMEDIFF(`end_time`,`start_time` )) as `length` FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `status`=1 AND `path`='1.z01' ORDER BY  `start_time` DESC LIMIT 1;";
		if(!$userquizstart = $this->DB->fetchRow($sql)) return $result;

		$sql = 'SELECT `t_id` as `id`, `correct`  FROM `quiz_tests` WHERE `t_id` IN ('.$userquizstart['tests'].') AND `path` LIKE "1.z01"' ; //
		$all_tests = $this->DB->fetchAll($sql);

		$answers = json_decode($userquizstart['quiz_answers'], ture);

		foreach ($answers as $ke => $val)
		{
			$ta[$ke] = $val['id'];
		}

		$correct_count = 0;
		$incorrect_count = 0;
		$noanswer_count = 0;
		foreach ($all_tests as $value)
		{
			$k = array_search($value['id'], $ta);
			if($answers[$k]['id']!=$value['id']) continue;
			if($answers[$k]['answer']==0) $noanswer_count ++;
			elseif($value['correct']== $answers[$k]['answer']) $correct_count ++;
			else $incorrect_count++;
		}
		$quiz_result_inpercent = round(($correct_count/($correct_count+$incorrect_count+$noanswer_count))*100);

		$result['serverMassage'] .= "<hr /><b>نتیجه آزمون قبل:</b><br />پاسخ های درست: $correct_count پاسخ<b> | </b>پاسخ های نادرست: $incorrect_count پاسخ<b> | </b>سوالات بی پاسخ: $noanswer_count سوال<br />نمره آزمون شما: $quiz_result_inpercent درصد";





		return $result;

	}
	protected function GetQuizTestsDataset($entity, $login_user, $sql_params, $result)
	{
		$result['serverMassage'] = 'خطای نامشخص! کمی صبر کنید و سپس دوباره تلاش نمایید';
		$result['status'] = 'error-failed';
		$result['goto'] = '#/home';
		//$sql_params['table-fields-courses-started-ended']
		$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'" ';
		//$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'" '.$sql_params['vote-query'];
		//$result['debug'] = $sql;
		if(!$quiz_result = $this->DB->fetchAll($sql)) return $result;
		$quiz_setting = json_decode($quiz_result[ 0 ]['desc'], true);

		$sql = "SELECT * FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `path`='".$entity['path']."'";
		if($user_quiz_start = $this->DB->fetchAll($sql))
		{
			$started_count = 0;
			$notended_count = 0;
			$ended_count =0;
			foreach ($user_quiz_start as $key => $value)
			{
				$started_count ++;
				if($value['status']==0) $notended_count++;
				elseif ($value['status']==1) $ended_count++;

			}
			if($ended_count>0)
			{
				$result['serverMassage'] = 'شما قبلا در این آزمون شرکت کرده اید و آن را به پایان رسانده اید.';
				$result['status'] = 'error-ended-before';
				return $result;
			}
			if($notended_count>=2)
			{
				$result['serverMassage'] = 'شما بیش از چند بار آزمون را شروع کرده ولی به پایان نرسانده اید.';
				$result['status'] = 'error-many-try';
				return $result;
			}
		}


		$sql = 'SELECT `t_id` as `id`, `problem` ,  `options` ,  `time`  FROM `quiz_tests` WHERE status=1 AND `path` LIKE "'.$entity['path'].'"  LIMIT '.$quiz_setting['testCount'].';' ; //ORDER BY RAND()
		$sql = 'SELECT `t_id` as `id`, `problem` ,  `options` ,  `time`  FROM `quiz_tests` WHERE status=1 AND `path` LIKE "'.$entity['path'].'" ORDER BY RAND() LIMIT '.$quiz_setting['testCount'].';' ;
		$temp_result = $this->DB->fetchAll($sql);

		$this->_XAL	= new Xal_Servlet('NORMAL_MODE');
		$test_ids = array();
		foreach ($temp_result as $key => $value)
		{
			$options = $this->_XAL->run('<execution>'.$temp_result[ $key ]['options'].'</execution>');
			$temp_result[ $key ]['options'] = $options['var:options'];
			$test_ids[] = $temp_result[ $key ]['id'];
		}
		//	$temp_result[ $key ]['options'] = json_decode($temp_result[ $key ]['options']);
		$result['Quiz']['Setting'] = $quiz_setting;
		$result['Quiz']['Tests'] = $temp_result;
		$data = array();
		$data['user_id']=$login_user['id'];
		$data['path'] = $entity['path'];
		$data['tests'] = implode(', ', $test_ids);
		if(!$this->DB->insert('user_quiz_start', $data))
		{
			$result['Quiz'] = '';
			$result['status'] = 'error-failed';
			return $result;
		}
		$result['serverMassage'] = '';
		$result['status'] = 'started';
		return $result;
	}
	protected function GenrateSqlParams($request, $login_user)
	{
		$sql_params['course_with_vote'] = "SELECT `co`.* , if((`co`.`start` < now()),1,0) AS `started`, if((`co`.`end` < now()),1,0) AS `ended`, `lu`.`user_id` AS `user_id`,`lu`.`time` AS `time`,`lu`.`vote` AS `vote` "
																		. " FROM (`courses` `co` LEFT JOIN `like_unlike` `lu` ON((`co`.`path` = `lu`.`path`))) "
																		. " WHERE `lu`.`user_id` IN (0,".$login_user['id'].") Group By `lu`.`path`";

		$sql_params['rich_courses'] = "SELECT `courses`.*, if((`start` < now()),1,0) AS `started`,if((`end` < now()),1,0) AS `ended` FROM `courses`";

		$sql_params['member-of-group-regex'] ='(^0$)'. ( (empty($login_user['groups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$login_user['groups'])) .'\/)');
		$sql_params['fields-courses'] = " `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, `start`, `end` ";
		$sql_params['fields-courses-with-vote'] = " `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, `start`, `end`, `started`, `ended`, Max(`vote`) as vote ";
		$sql_params['fields-started-ended'] = " if((`start` < NOW()),1,0) AS `started`, if((`end` < NOW()),1,0) AS `ended` ";
		$sql_params['fields-editable'] = ' IF( (`admin_groups` REGEXP "'.$sql_params['member-of-group-regex'].'"), "y","n" ) as `editable` ';
		$sql_params['fields-user-unsorted-qu-an'] = ' `fname`, `lname`, `c_id` as `id`, REPLACE(`path`,"__", CONCAT("$",`c_id`)) as `path`, `desc`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, `is_public`, status ';
		$sql_params['fields-score'] = " IFNULL( (SELECT score  FROM `path_score` WHERE `path_score`.`path` = `cwv`.`path`), 0) as `score` ";

		$sql_params['table-fields-courses-started-ended-editable'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'] .', '. $sql_params['fields-editable']. ' FROM `courses`' ;
		$sql_params['table-fields-courses-started-ended'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'] .' FROM `courses`' ;
		$sql_params['table-fields-courses-with-vote'] = $sql_params['fields-courses-with-vote'] .' FROM `course_with_vote` as cwv ' ;
		$sql_params['table-fields-courses-with-vote-with-score'] = $sql_params['fields-courses-with-vote'] .', '. $sql_params['fields-score'] . ' FROM `course_with_vote` as cwv ' ;
		$sql_params['table-fields-user-unsorted-qu-an'] = $sql_params['fields-user-unsorted-qu-an'] .' FROM `user_unsorted_qu_an` ' ;


		$sql_params['is-active-started-notended'] = ' (`status`=1 AND `start` < NOW() AND (`end` IS NULL OR `end` > NOW())) ';

		//$sql_params['fields'][0] = " `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, `start`, `end`, if((`start` < NOW()),1,0) AS `started`,if((`end` < NOW()),1,0) AS `ended` ";
		//$sql_params['fields'][1] = " `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote  FROM (".$sql_params['course_with_vote'].") as cwv ";
		//$sql_params['fields'][1] = " `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote  FROM `course_with_vote` ";
		//$sql_params['fields'][2] = ' `fname`, `lname`, `c_id` as `id`, REPLACE(`path`,"__", CONCAT("$",`c_id`)) as `path`, `desc`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, `is_public`, status FROM `user_unsorted_qu_an` ';
		//$sql_params['fields'][3] = " `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote, IFNULL( (SELECT score  FROM `path_score` WHERE `path_score`.`path` = `cwv`.`path`), 0) as `score` FROM (".$sql_params['course_with_vote'].") as cwv ";
		//$sql_params['fields'][3] = " `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote, IFNULL( (SELECT score  FROM `path_score` WHERE `path_score`.`path` = `course_with_vote`.`path`), 0) as `score` FROM `course_with_vote` ";
		//$sql_params['fields'][4] = " `fname`, `lname`, `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote, IFNULL( (SELECT score  FROM `path_score` WHERE `path_score`.`path` = `course_with_vote`.`path`), 0) as `score` FROM `user_course_with_vote` ";

		$sql_params['vote-query'] = " AND `user_id` IN (0,".$login_user['id'].") Group By `path` ";
		$sql_params['member-of-user-group'] =' ( user_groups RLIKE "'.$sql_params['member-of-group-regex'] .'") ';
		$sql_params['member-of-admin-group'] =' ( admin_groups RLIKE "'.$sql_params['member-of-group-regex'] .'") ';

		//$sql_params['editable-field'] = 'IF( (`admin_groups` REGEXP "'.$sql_params['member-of-group-regex'].'"), "y","n" ) as `editable`';
		//$sql_params['member-of-user-group'] =' ( user_groups RLIKE "(^0$)'. ( (empty($login_user['groups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$login_user['groups'])) .'\/)") ');
		//$sql_params['member-of-admin-group'] =' ( admin_groups RLIKE "(^0$)'. ( (empty($login_user['groups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$login_user['groups'])) .'\/)") ');

		$sql_params['order'] = " ORDER BY `path` ASC";
		$sql_params['order-by-score'] = " ORDER BY `score` DESC";
		$sql_params['owner-or-public-or-admin'] = '(`user_id`='.$login_user['id'].') OR ('.$sql_params['member-of-user-group'].' AND `is_public`=1) OR ('.$sql_params['member-of-admin-group'].')';
		$sql_params['owner-or-admin'] = '(`user_id`='.$login_user['id'].') OR ('.$sql_params['member-of-admin-group'].')';
		$sql_params['is-admin'] = $sql_params['member-of-admin-group'];
		$sql_params['is-owner'] = ' (`user_id`='.$login_user['id'].') ';
		$sql_params['limit'] = "LIMIT 0,30";
		if(!is_array($request['action-params']))
			if(strpos(':',$request['action-params']))
			{
				$limit = explode(':', $request['action-params']);
				if(is_numeric($limit[1]))
					$sql_params['limit'] = "LIMIT ".addslashes($limit[0]).",".addslashes($limit[1]);
			}


		return $sql_params;
	}
	protected function EntityDetailFromPath($path)
	{
		$entity_list = array('root'=>'Course',
			'l'=>'Lesson',
			'c'=>'Content',
			'q'=>'Question',
			'n'=>'Answer',
			'a'=>'About',
			'h'=>'Help',
			'v'=>'Activity',
			'z'=>'Quiz');
		$matches = array();
		$entity['path'] = $path;
		$path = preg_replace("/[\$]\d+/", '__', $path);
		if(!preg_match("/(\d)(\.([\w\d_]*)(\w)[\w\d_]{2})?$/", $path, $matches)) return array();
		$entity['root-path'] = $matches[1];
		if(count($matches)==2)
		{
			$entity['type'] = $entity_list['root'];
			$entity['parents-str'] = '';
			$entity['parent-path'] = '';
			$entity['level'] = 1;
		}
		else
		{
			$entity['type'] = $entity_list[$matches[4]];
			$entity['parents-str'] = $matches[3];
			$entity['parent-path'] = $entity['root-path'].((strlen($entity['parents-str'])>2)?'.'.$entity['parents-str']:'');
			$entity['level'] = (strlen($matches[3])/3)+2;
		}
		return $entity;
	}

}
// SELECT * FROM `courses` as co LEFT JOIN `like_unlike` as lu ON co.path = lu.path
// SELECT `user_id`, Max(user_id) FROM `course_with_vote` where user_id IN (0,1) group by `path`
// SELECT REPLACE(`path`,"__", CONCAT("$",`c_id`)) as `path`, `desc`, IF(`user_id`=1,"u","o") as `owner`, `is_public`, status FROM `unsorted_qu_an` WHERE status NOT IN (0,2) AND level=3 AND (`reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) OR owner="u") AND ('.$sql_params['member-of-admin-group'].') AND `path` LIKE "1.q01n__"
// SELECT `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote  FROM `course_with_vote` WHERE status=1 AND level=2 AND  `path` LIKE "1.q__" AND `user_id` IN (0,".$login_user['id'].") Group By `path`


?>
