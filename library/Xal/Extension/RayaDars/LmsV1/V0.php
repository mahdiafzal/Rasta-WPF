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
class Xal_Extension_RayaDars_LmsV1_V0
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

			$sql = 'SELECT `u_id` as `id`, `status`, `username`, `groups`, `force_to_change`, `fname`, `lname`, `email`, `cellphone` FROM `user_with_profile` WHERE status=1 AND username="'.addslashes($request['action-params']['username']).'" AND password="'.md5(addslashes($request['action-params']['password'])).'" ' ;
			// $result['debug'] = $sql;
			// $sql = 'SELECT `u_id` as `id`, `username`, `groups`, `force_to_change` FROM `users` WHERE status=1 AND username="'.addslashes($request['action-params']['username']).'" AND password="'.md5(addslashes($request['action-params']['password'])).'" ' ;

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
		$result['LoginUser'] = $login_user;
		# end of ligin block


		# register user profile
		if($request['namespace']=="Login" & $request['path']=="register" & $login_user['username']==$request['action-params']['username'])
		{
			$sql = 'SELECT * FROM `sabtenam` WHERE `org_id`="'.addslashes($request['action-params']['username']).'"' ;
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
					// $result['debug'] = $sql;
					//return $result;
					$this->DB->query($sql);
					$login_user['force_to_change']=0;
					$this->LoginUser($login_user);
					// return array('CommandParts'=>array('View', 'registerform', '', ''), 'Result'=> $result);

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

		$sql_params = $this->GenrateSqlParams($request, $entity, $login_user);
		$entity = $this->EntityDetailFromPath($request['path']);

		// course dataset
		// Course, l=Lesson, c=Content, q=Question, n=Answer, a=About, h=Help, v=Activity, z=Quiz
		$like_unlike_action = false;
		if($request['action']=="like" | $request['action']=="unlike" )
		{
			$result = $this->DoLikeUnlikeVote($request, $login_user,  $result);
			$like_unlike_action = true;
		}

		$validactions = array('setpublic', 'unsetpublic', 'deny', 'setprivate' );
		if(	($request['namespace']=="Question" | $request['namespace']=="Answer") & in_array($request['action'], $validactions) )
				// ($request['action']=="setpublic" | $request['action']=="unsetpublic" | $request['action']=="deny") )
		{
			$result = $this->QuestionAnswerSetPublicUnpublicDeny($request, $login_user, $sql_params, $result);
		}

		$validactions = array('setpublic', 'unsetpublic', 'deny', 'recycle' );
		if(	($request['namespace']=="Discussion") & in_array($request['action'], $validactions) )
				// ($request['action']=="setpublic" | $request['action']=="unsetpublic" | $request['action']=="deny" | $request['action']=="recycle") )
		{
			$result = $this->DiscussionSetPublicUnpublicDenyRecycle($request, $login_user, $sql_params, $result);
		}

		if($request['namespace']=="Home")
		{
			$result = $this->GetHomeDataset($login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Course")
		{
			// validate course path: is available courses for user groups
			$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-prerequisite'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level=1 AND `path`="'.$entity['root-path'].'" AND '.$sql_params['member-of-user-group'];
			if(!$temp_result = $this->DB->fetchAll($sql)) return $result; // this path not available for user

			// check prerequisite for course
			if($goto = $this->CheckPrerequisite($temp_result[0]['prerequisite'], $login_user))
				return array('CommandParts'=>array('','','',''), 'Result'=> array_merge($result, $goto));

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
			$sql2 = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'.l__" ' ;
			$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-path-asc'];
			$result[$request['namespace']]['Lessons'] = $this->DB->fetchAll($sql);

			// k=ask
			$result = $this->GetAskDataset($entity, $sql_params, $result);

			// a=About
			// $sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.a__" '.$sql_params['order'] ;
			$sql2 = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'.a__" ' ;
			$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-path-asc'];
			$result[$request['namespace']]['About'] = $this->DB->fetchAll($sql);

			// h=Help
			// $sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.h__" '.$sql_params['order'] ;
			$sql2 = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'.h__" ' ;
			$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-path-asc'];
			$result[$request['namespace']]['Helps'] = $this->DB->fetchAll($sql);

			// z=Quiz
			$result = $this->GetQuizDataset($entity, $sql_params, $result);

			// w=Homework
			$result = $this->GetHomeworkDataset($entity, $login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Lesson")
		{
			// validate course path: is available lesson for user groups
			$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-prerequisite'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND level=2 AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'];
			if(!$temp_result = $this->DB->fetchAll($sql)) return $result; // this path not available for user


			// check prerequisite for lesson
			if($goto = $this->CheckPrerequisite($temp_result[0]['prerequisite'], $login_user))
				return array('CommandParts'=>array_values($request), 'Result'=> array_merge($result, $goto));


			$result[$request['namespace']]['Metadata'] = $temp_result[0];

			// reset values
			$result[$request['namespace']]['Discussion'] = '';
			$result[$request['namespace']]['Questions'] = '';
			$result[$request['namespace']]['UnQuestions'] = '';


			// c=Content
			// $sql = 'SELECT '.$sql_params['table-fields-courses-plan-vote'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'c__" '.$sql_params['order'] ;
			$sql2 = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'c__" ';
			$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-path-asc'];
			$result[$request['namespace']]['Contents'] = $this->DB->fetchAll($sql);

			// k=ask
			$result = $this->GetAskDataset($entity, $sql_params, $result);

			// // q=Question
			// $result = $this->GetQuestionDataset($entity, $login_user, $sql_params, $result);

			// a=About
			// $sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'a__" '.$sql_params['order'] ;
			$sql2 = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'a__" ' ;
			$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-path-asc'];
			$result[$request['namespace']]['About'] = $this->DB->fetchAll($sql);

			// h=Help
			// $sql = 'SELECT '.$sql_params['table-fields-courses-with-vote'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'h__" '.$sql_params['order'] ;
			$sql2 = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'h__" ';
			$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-path-asc'];
			$result[$request['namespace']]['Helps'] = $this->DB->fetchAll($sql);

			// z=Quiz
			$result = $this->GetQuizDataset($entity, $sql_params, $result);

		}
		elseif ($request['namespace']=="Content")
		{
			if($request['action']=='show')
			{
				$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ' ;
				$result[$request['namespace']]['Items'] = $this->DB->fetchAll($sql);
			}
			if($request['action']=='download')
			{
				//$sql = 'SELECT `src` FROM `courses` WHERE status=1 AND level=3 AND `src` LIKE "http://%" AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ' ;
				$sql = 'SELECT `src` FROM `course_with_plan` WHERE status=1 AND level=3 AND `src` LIKE "http://%" AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ' ;
				if($temp_result = $this->DB->fetchAll($sql))
				{
					$result[$request['namespace']]['DownloadSrc'] = str_replace( array(':1010', ':1020'), array(':1011', ':1021'), $temp_result[0]['src']);
					$result['status'] = 'success';
				}
				else
						$result['status'] = 'failed';
			}
		}
		elseif ($request['namespace']=="Quiz" & ($request['action']=="start" | $request['action']=="preview" | $request['action']=="check"))
		{
			$result = $this->GetQuizTestsDataset($entity, $login_user, $sql_params, $result, $request['action']);
		}
		elseif ($request['namespace']=="Quiz" & $request['action']=="ended")
		{
			$result = $this->SetQuizEnded($request, $login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Quiz" & $request['action']=="result")
		{
			$result = $this->GetQuizResults($request, $login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Survey" & $request['action']=="start")
		{
			$result = $this->GetSurveyQuestionsDataset($request, $login_user, $sql_params, $result, $request['action']);
		}
		elseif ($request['namespace']=="Survey" & $request['action']=="ended")
		{
			$result = $this->SetSurveyEnded($request, $login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Questions" & ($request['action']=="get" | $request['action']=="push" ) )
		{
			$result = $this->GetQuestionDataset($entity, $login_user, $sql_params, $result, null, $request['action']);
		}
		elseif ($request['namespace']=="Question" & $request['action']=="answer")
		{
				$result = $this->GetAnswerDataset($entity, $sql_params, $result);
		}
		elseif ($request['namespace']=="Question" & $request['action']=="new")
		{

			$result = $this->RegNewQuestion($request, $entity, $login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Answer" & $request['action']=="new")
		{
			$request['action-params'] = trim($request['action-params']);
			if(strlen($request['action-params'])<5 | $request['action-params']=='registered' ) return $result;
				$reg_result = array('registeration' =>  array('status' => false , 'title'=>'ثبت پاسخ', 'message'=>'خطا در ثبت پاسخ!' ) ); // , 'hashcommand'=>''
				$result['status'] = 'error';

				$parent_path = preg_replace("/N__$/",'', $request['path']);
				//$reg_result['registeration']['hashcommand'] = "#/question(".$parent_path.").answer";
				$sql = 'SELECT * FROM `questions` WHERE (status=1 OR '.$sql_params['is-admin'].' OR '.$sql_params['is-owner'].') AND '.$sql_params['member-of-user-group'].' AND `path` = "'.$parent_path.'" ' ;
				if($temp_result = $this->DB->fetchAll($sql))
				{
						$data['prepath'] = $request['path'];
						$data['desc'] =  $request['action-params'];
						//$data['level'] = $temp_result[0]['level']+1;
						$data['user_id'] = $login_user['id'];
						$data['user_groups'] = $temp_result[0]['user_groups'];
						$data['admin_groups'] = $temp_result[0]['admin_groups'];

						if($this->DB->insert('questions', $data))
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
				$data['desc'] = trim($request['action-params']['idea']);
				$data['user_id'] = $login_user['id'];

				if(strlen($data['desc'])<5)	return $result;
				$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$data['path'].'" AND '.$sql_params['member-of-user-group'];
				if(!$parent = $this->DB->fetchRow($sql)) return $result;
				if($this->DB->insert('discussion', $data))
				{
					$result['status'] = 'registered';
					$reg_result['registeration']['status'] = true;
					$reg_result['registeration']['message'] = 'نظر شما با موفقیت ثبت گردید و پس از تأیید مدیران دوره منتشر خواهد شد.';
					//$result = $this->GetDiscussionDataset(array('path'=>$data['path']), $login_user, $sql_params, $result, $parent);
					$result = $this->GetDiscussionDataset($request, $login_user, $sql_params, $result, $parent, 'renew');
				}
				$result = array_merge($result, $reg_result);
		}
		elseif ($request['namespace']=="Discussion" & ($request['action']=="get" | $request['action']=="push" | $request['action']=="renew" ) )
		{
			// Discussion
			$result = $this->GetDiscussionDataset($request, $login_user, $sql_params, $result, null, $request['action']);
		}
		elseif ($request['namespace']=="Activities" & $request['action']=="report")
		{
			$result = $this->GetActivitiesReport($request, $login_user, $sql_params, $result);
		}
		elseif($request['namespace']=="Homework")
		{
			if($request['action']=="upload")
			{
				$result = $this->UploadHomeworkFiles($entity, $login_user, $sql_params, $result);
			}
			elseif ($request['action']=="rm")
			{
				$homework_rm = array( array('id'=>$request['action-params']) );
				$result = $this->RemoveHomeworkFile($entity, $login_user, $sql_params, $result, $homework_rm);
			}
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
		//if(is_array($request['action-params']))
		$request['action-params'] = '';
		return array('CommandParts'=>array_values($request), 'Result'=> $result);
	}
	protected function DiscussionSetPublicUnpublicDenyRecycle($request, $login_user, $sql_params, $result)
	{
		$result['status'] = 'failed';
		if(strpos($request['path'], '$')===false)
			return $result;

		$path_parts = explode("$", $request['path']);
		$record_id = $path_parts[1];
		if(!$parent_path = $this->DB->fetchOne("SELECT `path` FROM `discussion` WHERE `d_id`=".addslashes($record_id))) return $result;
		$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$parent_path.'" AND '.$sql_params['member-of-user-group'];
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
		if($parent['editable']=='y')
			$record_where = "`d_id`=".$record_id;
		elseif($dis_status != 3 & $dis_status != 4)
			return $result;

		$sql = "UPDATE `discussion` SET `status`='$dis_status' WHERE $record_where";
		$this->DB->query($sql);

		$result['status'] = 'done';
		//$result = $this->GetDiscussionDataset(array('path'=> $parent_path), $login_user, $sql_params, $result, $parent);
		return $result;
	}
	protected function QuestionAnswerSetPublicUnpublicDeny($request, $login_user, $sql_params, $result)
	{
		$entity_key =	($request['namespace']=="Question")?'Q':'N'; // | $request['namespace']=="Answer") &

		$data = array();
		switch ($request['action']) {
			case 'setpublic': 	$data['status'] = 1;	break;
			case 'unsetpublic': $data['status'] = 2; break;
			case 'recycle':			$data['status'] = 3;	break;
			case 'deny':				$data['status'] = 4;	break;
			// status = 5 : deleted by owner
			case 'setprivate':	$data['status'] = 6;	break;
			default: 						$data['status'] = 3; break;
		}
		if(strpos($request['path'], '$')===false)
		{
			$record_where = "`path`='".$request['path']."' AND ".$sql_params['member-of-admin-group'];
			$parent_path = preg_replace("/".$entity_key."[0-9a-z]+$/", "", $request['path']);
			$this->DB->update("questions", $data, $record_where);
		}
		else
		{
			$path_parts = explode("$", $request['path']);
			$record_id = $path_parts[1]; //preg_replace("/^[^\$]\$/", "", $request['path']);
			$parent_path = preg_replace("/".$entity_key."$/", "", $path_parts[0]);
			$record_where = "`c_id`=".$record_id." AND ".$sql_params['member-of-admin-group'];
			if($data['status']==1 | $data['status']==6)
			{
				// compute new path
				$sql = "SELECT MAX(`path`) as `last` FROM `questions` WHERE CAST(`path` AS BINARY) RLIKE '^".$parent_path.$entity_key."[0-9a-z]+$'";
				// $result['debug2'] = $sql;

				if($last_entity_path = $this->DB->fetchOne($sql))
				{
					$path_counter = str_replace($parent_path.$entity_key, "", $last_entity_path);
					$new_path = $path_parts[0].(Rasta_Base34_Operation::addOneTo($path_counter));
				}
				else
				{
					if($request['namespace']=='Question')
					{
						$sql = "SELECT * FROM `courses` WHERE `path`='$parent_path' AND ".$sql_params['member-of-admin-group'];
						// $result['debug3'] = $sql;
						if(!$parent = $this->DB->fetchRow($sql)) return $result;
						$ask_setting = json_decode($parent['settings'], true);
						$new_path = $path_parts[0].str_repeat('0',$ask_setting['pathCounterLength']-1).'1';
					}
					else
					{
						$new_path = $path_parts[0].'01';
					}
				}
				if(preg_match("/".$entity_key."z+$/",$new_path))
				{
					$result['status'] = 'exceed';
					return $result;
				}
				$data['path'] = $new_path;
				$data['prepath'] = null;
			}
			$this->DB->update("questions", $data, $record_where);
		}

		$result['status'] = 'done';
		// added on 2015-12-08
		if($request['namespace']=='Question')
			$result = $this->GetQuestionDataset(array('path'=> $parent_path ), $login_user, $sql_params, $result, null, 'get', 'unsorted');
		elseif ($request['namespace']=='Answer')
			$result = $this->GetAnswerDataset(array('path'=> $parent_path ), $sql_params, $result);

		return $result;

	}
	protected function DoLikeUnlikeVote($request, $login_user,  $result)
	{
		$db_table = (preg_match("/Q/",$request['path']))?"questions":"courses";
		$data = array();
		$vote = ($request['action']=='like')?1:2;
		$set_exp = ($vote==1)?"`like` = (`like`+1)" : "`unlike` = (`unlike`+1)";
		$data['vote'] = $vote;


		$sql = "SELECT *  FROM `like_unlike` WHERE `user_id` = ".$login_user['id']." AND `path`='".$request['path']."'";
		if($vote_result = $this->DB->fetchRow($sql))
		{
			if($vote_result['vote']==$data['vote']) $data['vote'] = 0;
			$sql = "UPDATE  `like_unlike` SET  `vote` = ".$data['vote']." WHERE  `lu_id`=".$vote_result['lu_id'];
			$this->DB->query($sql);

			switch ($vote_result['vote'])
			{
				// case 0:
				// 	$set_exp = ($vote==1)?"`like` = (`like`+1)" : "`unlike` = (`unlike`+1)";
				// 	break;
				case 1:
					$set_exp = ($vote==1)?"`like` = (`like`-1)" : "`like` = (`like`-1), `unlike` = (`unlike`+1)";
					break;
				case 2:
					$set_exp = ($vote==1)?"`like` = (`like`+1), `unlike` = (`unlike`-1)" : "`unlike` = (`unlike`-1)";
					break;
			}
		}
		else
		{
			$data['path'] = $request['path'];
			$data['user_id'] = $login_user['id'];
			$this->DB->insert('like_unlike', $data);
		}

		$sql = "UPDATE $db_table SET $set_exp WHERE `path`='".$request['path']."'";
		$this->DB->query($sql);


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

		return $result;
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
		$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` NOT IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') '.$sql_params['order'] ;
		// $result['debug1'] = $sql;
		$result['Home']['AvailableCourses'] = $this->DB->fetchAll($sql);

		// user courses
		// $sql = 'SELECT '.$sql_params['table-fields-courses-plan-vote'].' WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') '.$sql_params['order'] ;
		$sql2 = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') ';
		$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-path-desc'];
		$result['Home']['RegisteredCourses'] = $this->DB->fetchAll($sql);

		// notifications
		if($result['Home']['RegisteredCourses'])
		{
			foreach ($result['Home']['RegisteredCourses'] as $value)
				$RegisteredCoursesPathes[] = $value['path'];

			$sql = 'SELECT * FROM `notifications` WHERE ((`path` RLIKE "^'.implode('(\.[0-9a-z]+)?$") OR (`path` RLIKE "^', $RegisteredCoursesPathes).'(\.[0-9a-z]+)?$")) AND '.$sql_params['member-of-user-group'].' AND (`from_time` < NOW()) AND (`status`=1)';
			//$result['debug'] = $sql;
			if($temp_result= $this->DB->fetchAll($sql))
			{
				$sql_parts1 = array();
				$sql_parts2 = array();
				$path_actions = array();
				foreach ($temp_result as $value)
				{
					if(in_array($value['path'].'--'.$value['action'], $path_actions)) continue;
					$path_actions[] = $value['path'].'--'.$value['action'];
					$sql_parts1[] = '(a.`path`="'.$value['path'].'" AND a.`action`="'.$value['action'].'")';
					// $sql_parts2[] = '(b.`path`="'.$value['path'].'" AND b.`action`="'.$value['action'].'" AND b.`count`'.$value['rule'].')';
					$sql_parts2[] = '(b.`path`="'.$value['path'].'" AND b.`action`="'.$value['action'].'" AND b.`count` BETWEEN n.`min` AND n.`max`)';
				}
				if(count($sql_parts1)==0) return $result;
				//$sql = 'SELECT `message`, `popup` FROM `notifications` WHERE (`status`=1) AND '.$sql_params['member-of-user-group'].' AND (`path`, `action`) IN (SELECT b.`path`, b.`action` FROM ((SELECT a.`path`, a.`action`, COUNT(a.`action`) as `count` FROM `activity_logs` AS a WHERE ('.implode(' OR ',$sql_parts1).') AND '.$sql_params['is-owner'].' GROUP BY a.`path`, a.`action`) as b) WHERE '.implode(' OR ',$sql_parts2).' )';
				$sql = 'SELECT a.`path`, a.`action`, COUNT(a.`action`) as `count` FROM `activity_logs` AS a WHERE ('.implode(' OR ',$sql_parts1).') AND '.$sql_params['is-owner'].' GROUP BY a.`path`, a.`action`';
				$sql = "SELECT `message`, `popup` FROM `notifications` as n LEFT JOIN ($sql) as b ON (n.`path`=b.`path` AND n.`action`=b.`action`) WHERE (`status`=1) AND ".$sql_params['member-of-user-group'].' AND ('.implode(' OR ',$sql_parts2).' )';
				//$result['debug2']=$sql;
				$result['Home']['Notifications'] = $this->DB->fetchAll($sql);

				foreach ($result['Home']['Notifications'] as $key => $value)
				{
					if($value['popup']>0)
						$result['serverMassage'] = $value['message'];
					if($value['popup']==2)
						unset($result['Home']['Notifications'][$key]);
				}
			}
		}

		return $result;

	}
	protected function GetDiscussionDataset($request, $login_user, $sql_params, $result, $parent=null, $mode='get')
	{
		// get Discussion parent data
		if($parent==null)
		{
			$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'];
			if(!$parent = $this->DB->fetchRow($sql)) return $result;
		}
		$request_namespace = ($parent['level']==1)?'Course':'Lesson';

		// Discussions
		if($mode=='get')
		{
			$sql = 'SELECT `d_id` as `id`, `status`, `reg_time` as `datetime`, `path`, `desc`, `lname`, `fname`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, "'.$parent['editable'].'" as `editable`  FROM `user_discussion` WHERE (`status`=1 OR ('.$sql_params['is-owner'].' AND `status` IN (1,3,4)) OR ("'.$parent['editable'].'"="y" AND `status`!=0)) AND `path` = "'.$request['path'].'" ORDER BY `d_id` DESC '.$sql_params['limit'] ;
			$result[$request_namespace]['Discussion'] = $this->DB->fetchAll($sql);
		}
		elseif ($mode=='renew')
		{
			$sql = 'SELECT `d_id` as `id`, `status`, `reg_time` as `datetime`, `path`, `desc`, `lname`, `fname`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, "'.$parent['editable'].'" as `editable`  FROM `user_discussion` WHERE (`status`=1 OR ('.$sql_params['is-owner'].' AND `status` IN (1,3,4)) OR ("'.$parent['editable'].'"="y" AND `status`!=0)) AND `path` = "'.$request['path'].'" AND `d_id`>'.addslashes($request['action-params']['start']).' ORDER BY `d_id` ASC '.$sql_params['limit'] ;
			$result['prepend'][$request_namespace]['Discussion'] = $this->DB->fetchAll($sql);
		}
		elseif ($mode=='push')
		{
			$sql = 'SELECT `d_id` as `id`, `status`, `reg_time` as `datetime`, `path`, `desc`, `lname`, `fname`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, "'.$parent['editable'].'" as `editable`  FROM `user_discussion` WHERE (`status`=1 OR ('.$sql_params['is-owner'].' AND `status` IN (1,3,4)) OR ("'.$parent['editable'].'"="y" AND `status`!=0)) AND `path` = "'.$request['path'].'" AND `d_id`<'.addslashes($request['action-params']['end']).' ORDER BY `d_id` DESC '.$sql_params['limit'] ;
			$result['append'][$request_namespace]['Discussion'] = $this->DB->fetchAll($sql);

		}

		return $result;
	}
	protected function GetAskDataset($entity, $sql_params, $result)
	{
		$request_namespace = (preg_match("/^\d+$/", $entity['path']))?'Course':'Lesson';
		$entity['path'] .= ($request_namespace=='Course')?'.':'';

		// $sql = 'SELECT '.$sql_params['table-fields-courses-plan-vote'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'. $entity['path'].'k__" '.$sql_params['order-by-path-asc'] ;
		$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'. $entity['path'].'k__" '.$sql_params['order-by-path-asc'] ;
		if(!$temp_result = $this->DB->fetchAll($sql)) return $result;
		foreach ($temp_result as $key => $value)
		{
			$temp_result[$key]['Questions'] = '';
			$temp_result[$key]['Answers'] = '';
		}
		$result[$request_namespace]['Asks'] = $temp_result;
		return $result;
	}
	protected function GetQuestionDataset($entity, $login_user, $sql_params, $result, $parent=null, $mode='get', $sets='all')
	{
		$request_namespace = (preg_match("/^\d+\.k[\d\w]{2}$/", $entity['path']))?'Course':'Lesson';
		$parent_path = preg_replace("/k[\d\w]{2}$/", "", $entity['path']);

		// $sql = 'SELECT '.$sql_params['table-fields-courses-plan-vote-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'. $parent_path.'k__" '.$sql_params['order-by-path-asc'] ;
		$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'. $parent_path.'k__" '.$sql_params['order-by-path-asc'] ;
		if(! $asks = $this->DB->fetchAll($sql)) return $result;
		$questions_path_pattern = false;
		foreach ($asks as $key => $value)
		{
			// $asks[$key]['desc'] = json_decode($value['desc'], true);
			$settings = json_decode($value['settings'], true);
			$can_ask = false;
			if(isset($settings['permissions']))
			{
				foreach ($settings['permissions']['new'] as $val)
				{
					switch ($val)
					{
						case 'all': $can_ask=true;	break 1;
						case 'user-groups': $can_ask=true;	break 1;
						case 'admin-groups': $can_ask=($value['editable']=='y')?true:false;	break 1;
					}
					if($can_ask) break 1;
				}
				$settings['permissions']['new'] = $can_ask;
			}
			else
			{
				$settings['permissions'] = array('new' => $can_ask);
			}

			$asks[$key]['settings'] = $settings;

			if($value['path']==$entity['path'])
			{
				$questions_path_pattern = $entity['path']."Q".str_repeat('_', $settings['pathCounterLength']);
				$selected_ask_key = $key;
				$selected_ask_editable = $value['editable'];
			}

		}
		$result[$request_namespace]['Asks'] = $asks;
		if(!$questions_path_pattern)	return $result;



		// if($parent==null)
		// {
		// 	// get Questions parent data
		// 	$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level IN (1,2) AND `path`="'.$entity['path'].'" AND '.$sql_params['member-of-user-group'];
		// 	if(!$parent = $this->DB->fetchRow($sql)) return $result;
		// }
		// $request_namespace = ($parent['level']==1)?'Course':'Lesson';
		// $entity['path'] .= ($entity['level']==1)?'.':'';

		// not editable => new(1), top(1), your(1,2,3,4), search(1)
		// editable => new(1), top(1), your(1,2,3,4), search(1,3,4) + new unsorted questions (3) + new unsorted answers (3) + deleted questions (2) + private questions (4)

		$sql_limit = " LIMIT 0, 500";

		$sql1 = "SELECT `path`, `vote` FROM `like_unlike` WHERE `path` like '".$entity['path']."Q%' AND `user_id`=".$login_user['id'];
		// Discussions
		if($mode=='get')
		{
			if($sets=='all' | $sets=='sorted')
			{
				// q=Question

				$sql2 = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE ('.$sql_params['is-active'].') AND '.$sql_params['member-of-user-group'].' AND (`path` LIKE "'. $questions_path_pattern.'") ' ;
				$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN ($sql1) b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-score-desc'] . $sql_limit;
				// $result['debug']['tops'] = $sql;
				$result[$request_namespace]['Asks'][$selected_ask_key]['Questions']['Tops'] = $this->DB->fetchAll($sql);

				// $sql = 'SELECT '.$sql_params['table-fields-user-questions-with-vote-with-score'].' WHERE ('.$sql_params['is-active'].') AND '.$sql_params['member-of-user-group'].' AND (`path` LIKE "'. $questions_path_pattern.'") '.$sql_params['order-by-start-date-desc']  . $sql_limit ;
				// $result['debug']['lasts'] = $sql;

				// $sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN ($sql1) b ON `a`.`path`=`b`.`path` ORDER BY `id` DESC";
				//
				// $result[$request_namespace]['Asks'][$selected_ask_key]['Questions']['Lasts'] = $this->DB->fetchAll($sql);


				$sql2 = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE ('.$sql_params['is-active'].') AND '.$sql_params['member-of-user-group'].' AND (`path` LIKE "'. $questions_path_pattern.'N__") ';
				$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN ($sql1) b ON `a`.`path`=`b`.`path` ".$sql_params['order-by-score-desc'] . " LIMIT 0, 50";
				// $result['debug']['atops'] = $sql;
				$result[$request_namespace]['Asks'][$selected_ask_key]['Answers']['Tops'] = $this->DB->fetchAll($sql);


				$sql2 = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE ('.$sql_params['is-owner'].') AND (`path` LIKE "'. $entity['path'] .'%" OR `prepath` LIKE "'. $entity['path'] .'%") ';
				$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN ($sql1) b ON `a`.`path`=`b`.`path` ORDER BY `id` DESC";
				// $result['debug']['yours'] = $sql;
				$result[$request_namespace]['Asks'][$selected_ask_key]['Questions']['Yours'] = $this->DB->fetchAll($sql);

				// $sql2 = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE ('.$sql_params['is-active'].' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'. $entity['path'] .'Q%" ) OR ('.$sql_params['is-owner'].' AND (`path` LIKE "'. $entity['path'] .'%" OR `prepath` LIKE "'. $entity['path'] .'%") )';
				// $sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN ($sql1) b ON `a`.`path`=`b`.`path`";
				// $result['debug'] = $sql;

				// return $result;

				// //$sql = 'SELECT '.$sql_params['table-fields-courses-with-vote-with-score'].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'Q__" ';
				// //$sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_questions` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score-desc']; //.$sql_params['limit'] ;
				// $sql = 'SELECT '.$sql_params['table-fields-user-questions-with-vote-with-score'].' WHERE ('.$sql_params['is-active'].') AND '.$sql_params['member-of-user-group'].' AND (`path` LIKE "'. $questions_path_pattern.'") '.$sql_params['order-by-path-asc'] ;
				// // $sql = 'SELECT '.$sql_params['table-fields-user-questions-with-vote-with-score'].' WHERE ('.$sql_params['is-active'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND (`path` LIKE "'. $questions_path_pattern.'" OR `prepath` LIKE "'. $entity['path'].'Q__") '.$sql_params['order-by-path-asc'] ;
				// $result[$request_namespace]['Asks'][$selected_ask_key]['Questions'] = $this->DB->fetchAll($sql);
				//
				// //$result[$request_namespace]['Questions'] = $this->DB->fetchAll($sql);
			}
			if(($sets=='all' | $sets=='unsorted') & $selected_ask_editable=="y")
			{

				// $sql2 = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE `status` NOT IN (0,4) AND '.$sql_params['is-admin'].' AND (`path` LIKE "'. $questions_path_pattern.'" OR `prepath` LIKE "'. $entity['path'].'Q__") ';
				// $sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN ($sql1) b ON `a`.`path`=`b`.`path` ORDER BY `id` DESC";
				// $result['debug']['news'] = $sql;
				$sql_editble_limit = " LIMIT 0, 200";
				$sql = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE `status` NOT IN (0,4) AND '.$sql_params['is-admin'].' AND (`path` LIKE "'. $questions_path_pattern.'" OR `prepath` LIKE "'. $entity['path'].'Q__") ORDER BY `id` DESC'. $sql_editble_limit;
				$result[$request_namespace]['Asks'][$selected_ask_key]['Questions']['News'] = $this->DB->fetchAll($sql);

				// $sql2 = 'SELECT '.$sql_params['table-fields-user-questions-with-vote-with-score'].' WHERE `status` NOT IN (0,4) AND '.$sql_params['is-admin'].' AND (`path` LIKE "'. $questions_path_pattern.'N__" OR `prepath` LIKE "'. $questions_path_pattern.'N__") ';
				// $sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN ($sql1) b ON `a`.`path`=`b`.`path` ORDER BY `id` DESC";
				// $result['debug']['anews'] = $sql;
				$sql = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE `status` NOT IN (0,4) AND '.$sql_params['is-admin'].' AND (`path` LIKE "'. $questions_path_pattern.'N__" OR `prepath` LIKE "'. $questions_path_pattern.'N__") ORDER BY `id` DESC'. $sql_editble_limit;
				$result[$request_namespace]['Asks'][$selected_ask_key]['Answers']['News'] = $this->DB->fetchAll($sql);


				// // Unsorted Course Questions
				// // $sql = 'SELECT '.$sql_params['table-fields-user-unsorted-qu-an'].' WHERE (`status` NOT IN (0,2) OR (`status`!=0 AND '.$sql_params['is-owner'].') ) AND level='.($entity['level']+1).' AND (`reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) OR '.$sql_params['is-owner'].') AND ('.$sql_params['owner-or-admin'].') AND `path` LIKE "'.$entity['path'].'Q__" ORDER BY  `c_id` DESC ';//.$sql_params['order'] ;
				// // $result['debug2'] = $sql;
				// //$result[$request_namespace]['UnQuestions'] = $this->DB->fetchAll($sql);
				// $sql = 'SELECT '.$sql_params['table-fields-user-questions-with-vote-with-score'].' WHERE (`status` NOT IN (0,2) OR (`status`!=0 AND '.$sql_params['is-owner'].') ) AND (`reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) OR '.$sql_params['is-owner'].') AND ('.$sql_params['owner-or-admin'].') AND (`path` LIKE "'. $questions_path_pattern.'" OR `prepath` LIKE "'. $entity['path'].'Q__") ORDER BY  `c_id` DESC';
				// $result[$request_namespace]['Asks'][$selected_ask_key]['Questions'] = $this->DB->fetchAll($sql);

			}
		}
		elseif ($mode=='renew')
		{

		}
		elseif ($mode=='push')
		{
			// $sql = 'SELECT '.$sql_params['table-fields-courses-with-vote-with-score'].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'Q__" ';
			// $sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_questions` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score-desc'].$sql_params['limit'] ;
			// $result['append'][$request_namespace]['Questions'] = $this->DB->fetchAll($sql);
		}
		return $result;
	}
	protected function RegNewQuestion($request, $entity, $login_user, $sql_params, $result)
	{
		$request['action-params'] = trim($request['action-params']);
		if(strlen($request['action-params'])<5 | $request['action-params']=='registered') return $result;
		$reg_result = array('registeration' =>  array('status' => false , 'title'=>'ثبت سوال', 'message'=>'خطا در ثبت سوال!') ); //, 'hashcommand'=>''
		$result['status'] = 'error';

		$parent_path = preg_replace("/\.?Q__/",'', $request['path']);


		// Get Question Parent : Ask Entity
		$sql = 'SELECT * FROM `courses` WHERE status=1 AND level = '.($entity['level']-1).' AND '.$sql_params['member-of-user-group'].' AND `path` = "'.$parent_path.'" ' ;
		if($temp_result = $this->DB->fetchAll($sql))
		{
				$data['prepath'] = $request['path'];
				$data['desc'] = $request['action-params'];
				//$data['level'] = $entity['level'];
				$data['user_id'] = $login_user['id'];
				$data['user_groups'] = $temp_result[0]['user_groups'];
				$data['admin_groups'] = $temp_result[0]['admin_groups'];

				if($this->DB->insert('questions', $data))
				{
					$result['status'] = 'registered';
					$reg_result['registeration']['status'] = true;
					$reg_result['registeration']['message'] = 'سوال مطرح شده با موفقیت ثبت گردید و پس از تأیید مدیران دوره برای دیگران قابل مشاهده و پاسخ دهی خواهد بود.';

					$result = $this->GetQuestionDataset(array('path'=> $parent_path, 'level'=> $entity['level']-1 ), $login_user, $sql_params, $result, null, 'get', 'unsorted');
				}
		}
		$result = array_merge($result, $reg_result);
		$request['action-params'] = '';
		return $result;

	}
	protected function GetAnswerDataset($entity, $sql_params, $result)
	{
		// n=Answer
		$answer_view = 'Course';
		$result['SelectedQuestion'] = '';
		$result['Question'][$answer_view]['Answers'] = '';

		$sql2 = 'SELECT '.$sql_params['table-fields-user-questions-with-score'].' WHERE ('.$sql_params['is-active'].' OR (`status` NOT IN (0,4) AND '.$sql_params['is-admin'].') OR (`status` NOT IN (0,4) AND '.$sql_params['is-owner'].')) AND '.$sql_params['member-of-user-group'].' AND (`path` LIKE "'. $entity['path'].'") ' ;
		$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` " . $sql_params['order-by-score-desc'] . $sql_limit;
		if($selected_question = $this->DB->fetchRow($sql))	$result['SelectedQuestion'] = $selected_question;
		else return $result;


		$sql2 = 'SELECT '.$sql_params['table-fields-user-questions-editable-with-score'].' WHERE ('.$sql_params['is-active'].' OR (`status` NOT IN (0,4) AND '.$sql_params['is-admin'].')) AND '.$sql_params['member-of-user-group'].' AND (`path` LIKE "'.$entity['path'].'N__" OR `prepath` LIKE "'.$entity['path'].'N__") ';// ;
		$sql = "SELECT `a`.*, `b`.`vote` FROM ($sql2) a LEFT JOIN (".$sql_params['vote-query'].") b ON `a`.`path`=`b`.`path` ".$sql_params['order-by-score-desc'];
		$result['Question'][$answer_view]['Answers'] = $this->DB->fetchAll($sql);
		return $result;

		// $result['debug1'] = $sql;
		//$sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_questions` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score-desc'];
		// Unsorted Answers
		//$sql = 'SELECT '.$sql_params['table-fields-user-q'].' WHERE status NOT IN (0,2) AND level='.($entity['level']+1).' AND `reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) AND ('.$sql_params['member-of-admin-group'].') AND `path` LIKE "'.$entity['path'].'N__" ORDER BY  `c_id` DESC ';//.$sql_params['order'] ;
		//$result['Question'][$answer_view]['UnAnswers'] = $this->DB->fetchAll($sql);
		//$result['debug2'] = $sql;
	}
	protected function GetHomeworkDataset($entity, $login_user, $sql_params, $result)
	{
		// $entity is patent entity
		$request_namespace = ($entity['level']==1)?'Course':'Lesson';
		$entity['path'] .= ($entity['level']==1)?'.':'';

		$sql = 'SELECT `settings`, '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE ('.$sql_params['is-active'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND level='.($entity['level']+1).' AND `path` LIKE "'.$entity['path'].'w__" '.$sql_params['order'];
		$temp_result = $this->DB->fetchAll($sql);
		foreach ($temp_result as $key => $value)
		{
			$temp_result[ $key ]['settings'] = json_decode($temp_result[ $key ]['settings']);
		}
		$result[$request_namespace]['Homework'] = $temp_result;
		$result[$request_namespace]['HomeworkSent'] = array();
		$sql = "SELECT * FROM `user_homework_sent` WHERE `user_id`=".$login_user['id']." AND `path` LIKE '".$entity['path']."w__'";

		if($user_homework_sent = $this->DB->fetchAll($sql))
		{
			foreach ($user_homework_sent as $key => $value)
			{
				$user_homework_sent[ $key ]['files'] = json_decode($user_homework_sent[ $key ]['files']);
			}
			$result[$request_namespace]['HomeworkSent'] = $user_homework_sent;
		}
		return $result;
	}
	protected function GetQuizDataset($entity, $sql_params, $result)
	{
		// $entity is patent entity
		$request_namespace = ($entity['level']==1)?'Course':'Lesson';
		$entity['path'] .= ($entity['level']==1)?'.':'';


		//$sql = 'SELECT '.$sql_params['table-fields-courses-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND level='.($entity['level']+1).' AND `path` LIKE "'.$entity['path'].'z__" '.$sql_params['order'];
		$sql = 'SELECT NOW() as now, '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE ('.$sql_params['is-active'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND level='.($entity['level']+1).' AND `path` LIKE "'.$entity['path'].'z__" '.$sql_params['order'];
		$temp_result = $this->DB->fetchAll($sql);
		foreach ($temp_result as $key => $value)
		{
			$temp_result[ $key ]['settings'] = json_decode($temp_result[ $key ]['settings'], true);

			// $result['debug'] = date('Y-m-d H:i:s', strtotime('-10 min', strtotime($value['end'])));
			if(date('Y-m-d H:i:s', strtotime('-'.$temp_result[ $key ]['desc']['maxTime'].' min', strtotime($value['end']))) <  date('Y-m-d H:i:s', strtotime($value['now'])) )
				$value['ended'] = '1';

			// $DateOfRequest = date("Y-m-d H:i:s", strtotime($_REQUEST["DateOfRequest"]));
		}
		$result[$request_namespace]['Quizes'] = $temp_result;
		return $result;
	}

	protected function GetActivitiesReport($request, $login_user, $sql_params, $result)
	{
		$result = $this->GetQuizResults($request, $login_user, $sql_params, $result);
		// // v=Activity
		// $sql = 'SELECT * FROM `activity_report_view` ' ;
		$login_user_id = $login_user['id'];
		$course_path = $request['path'];
		// $sql = "SELECT ual.*, co.name from (select `path`, `action`, count(`id`) AS `count`, max(`datetime`) AS `last`, min(`datetime`) AS `first`, (to_days(max(`datetime`)) - to_days(min(`datetime`))) AS `length`, 'u' AS `whois`
		// from `activity_logs`
		// where (`path` regexp '(^1$)|(^1\.[0-9a-z_]+$)') AND `user_id` = $login_user_id
		// group by `path`, `action`) as ual left join `courses` as `co` on `ual`.`path` = `co`.`path`";
		$sql= "SELECT ual.path, ual.action, ual.cnt, ual.last, ual.first, ual.length, ual.whois, co.name, PathActionFilter(ual.`path`, ual.`action`) as `filter`, SUM(ual.`cnt`) as `count`
				from (select `path`, `action`, count(`id`) AS `cnt`, max(`datetime`) AS `last`, min(`datetime`) AS `first`, (to_days(max(`datetime`)) - to_days(min(`datetime`))) AS `length` , 'u' AS `whois`
					from `activity_logs`
					where (`path` regexp '(^$course_path$)|(^$course_path\.[0-9a-z_]+$)') and `action` in (0,1,2,3,4,5,8) and `user_id` = $login_user_id
					group by `path`, `action`) as ual left join `courses` as `co` on `ual`.`path` = `co`.`path`
					group by `filter`";
		$u_result = $this->DB->fetchAll($sql);
		// $sql = "SELECT ual.*, co.name from (select `path`, `action`, count(`id`) AS `count`, max(`datetime`) AS `last`, min(`datetime`) AS `first`, (to_days(max(`datetime`)) - to_days(min(`datetime`))) AS `length` , 'o' AS `whois`
		// from `activity_logs`
		// where (`path` regexp '(^1$)|(^1\.[0-9a-z_]+$)') and `action` in (select `index` from `termology` where `type`='command.action')
		// group by `path`, `action`) as ual left join `courses` as `co` on `ual`.`path` = `co`.`path`";
		// $sql = "SELECT * FROM `activity_report_o`";
		$sql = "SELECT * FROM `activity_report_o` where (`path` regexp '(^$course_path$)|(^$course_path\.[0-9a-z_]+$)') group by `path`, `filter` ORDER BY `c_id`  DESC";
		$o_result = $this->DB->fetchAll($sql);

		$result['Course']['Activities'] = array_merge($u_result, $o_result);
		return $result;
	}
	protected function GetQuizResults($request, $login_user, $sql_params, $result)
	{
		// // get user quiz strart info
		$sql = "SELECT *  FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `status`=1 AND (`quiz_result`='' OR `quiz_result` IS NULL)";
		//$result['debug1'] = $sql;
		if($notcomputed = $this->DB->fetchAll($sql))
		{
			foreach ($notcomputed as $key => $value)
			{
				$this->ComputeQuizResult($value, $login_user, true, false);
			}
		}
		// get user ended quizes
		// $sql = "SELECT * FROM (SELECT `user_quiz_start`.*,  TIME_TO_SEC(TIMEDIFF(`end_time`,`start_time` )) as `length` FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `status`=1 AND `path` LIKE '".$request['path']."%' ORDER BY  `path`, convert(`quiz_result`, decimal) DESC) as rs GROUP BY `path` ORDER BY  `start_time`";
		$sql = "SELECT `user_quiz_start`.*,  TIME_TO_SEC(TIMEDIFF(`end_time`,`start_time` )) as `length` FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `status`=1 AND `path` LIKE '".$request['path']."%' ORDER BY  `path`, convert(`quiz_result`, decimal) DESC";
		//$result['debug2'] = $sql;
		if(!$userendedquizes = $this->DB->fetchAll($sql)) return $result;
		$quizes_result = array();
		$has_null_value = false;
		foreach ($userendedquizes as $key => $value)
		{
				//if(is_null($value['quiz_result']) | strlen($value['quiz_result'])==0)
				if(empty($value['quiz_result']))
				{
					$has_null_value = ture;
					$this->ComputeQuizResult($value, $login_user, true, false);
				}
				elseif(!$has_null_value)
					$quizes_result[] = $this->ComputeQuizResult($value, $login_user);
		}
		if($has_null_value)
			$quizes_result = $this->GetQuizResults($request, $login_user, $sql_params, $result);

		$result['Reports']['QuizeResults'] = $quizes_result;
		return $result;
	}
	protected function ComputeQuizResult($ended_quiz, $login_user, $need_to_update=false, $with_siblings=false)
	{
		// get selected tests for compare
		$sql = 'SELECT `t_id` as `id`, `status`, `correct`, `refrece` ,  `hints`  FROM `quiz_tests` WHERE `t_id` IN ('.$ended_quiz['tests'].')'; //"AND `path` LIKE "'.$ended_quiz['path'].'"' ; //
		$all_tests = $this->DB->fetchAll($sql);

		$quiz_answers = json_decode($ended_quiz['quiz_answers'], true);
		foreach ($quiz_answers as $ke => $val)		$ta[$ke] = $val['id'];

		$correct_count = 0;
		$incorrect_count = 0;
		$noanswer_count = 0;
		$incorrect_hints = array();
		foreach ($all_tests as $value)
		{
			if($value['status']==2) continue; // remove test from all results
			if($value['status']==3)
			{
				$correct_count ++;
				continue; // test as correct for all results
			}

			$k = array_search($value['id'], $ta);
			if($quiz_answers[$k]['id']!=$value['id']) continue;
			if($quiz_answers[$k]['answer']==0) $noanswer_count ++;
			elseif($value['correct']== $quiz_answers[$k]['answer']) $correct_count ++;
			else
			{
				if(!empty($value['hints']))
					$incorrect_hints_refrences[] = array('hints' => $value['hints'] , 'refrece' => $value['refrece'], 'id'=>  $value['id'] );
				$incorrect_count++;
			}
		}
		$quiz_result_inpercent = round(($correct_count/($correct_count+$incorrect_count+$noanswer_count))*100);

		if($need_to_update)
		{
			$where = " `user_id`=".$login_user['id']." AND `uq_id`=".$ended_quiz['uq_id'];
			$sql = "UPDATE `user_quiz_start` SET `quiz_result` = '$quiz_result_inpercent' WHERE $where";
			$this->DB->query($sql);
		}

		$this_result = array();
		$this_result['path'] = $ended_quiz['path'];
		$this_result['correct'] = $correct_count;
		$this_result['incorrect'] = $incorrect_count;
		$this_result['noanswer'] = $noanswer_count;
		$this_result['totalinpercent'] = $quiz_result_inpercent;
		$this_result['length'] = floor($ended_quiz['length']/60).' دقیقه و '.($ended_quiz['length']%60).' ثانیه';
		$this_result['inhinref'] = $incorrect_hints_refrences;
		$quiz_results = array();
		$quiz_results[] = $this_result;

		if($with_siblings)
		{
			$sql = "SELECT `user_quiz_start`.*,  TIME_TO_SEC(TIMEDIFF(`end_time`,`start_time` )) as `length` FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `status`=1 AND `path`='".$ended_quiz['path']."';";
			if(!$userendedquizes = $this->DB->fetchAll($sql)) return false;
			foreach ($userendedquizes as $key => $value)
			{
					if(empty($value['quiz_result']))
					//if(is_null($value['quiz_result']) | strlen($value['quiz_result'])==0)
					{
						$quiz_results[] =	$this->ComputeQuizResult($value, $login_user, $need_to_update, false);
					}
			}
			return $quiz_results;
		}
		else
			return $this_result;
	}
	protected function SetQuizEnded($request, $login_user, $sql_params, $result)
	{
		$result['serverMassage'] = 'خطا در ثبت نتیجه آزمون!';
		//'پاسخنامه شما ثبت شد. برای شما آرزوی موفقیت داریم.';
		$result['Quiz'] = '';
		$result['status'] = 'info-failed';
		$result['goto'] = '#/home'; // '#/course('.preg_replace("/\.?z[\w\d]{2}$/","",$request['path']).').enter';

		// get quiz info and setting
		$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ';
		if(!$quiz_result = $this->DB->fetchAll($sql)) return $result;
		$quiz_setting = json_decode($quiz_result[ 0 ]['settings'], true);

		$test_ids = array();
		foreach ($request['action-params']['answers'] as $key => $value)
		{
			$test_ids[] = $value['id'];
		}
		if(count($test_ids)<5)	return $result;

		$sql = "SELECT `user_quiz_start`.*,  TIME_TO_SEC(TIMEDIFF(NOW(),`start_time` )) as `length` FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `status`=0 AND `path`='".$request['path']."' AND `renewal`=".$quiz_setting['renewal']." AND tests LIKE '".implode(', ', $test_ids)."' ORDER BY  `start_time` DESC LIMIT 1;";
		if(!$userquizstart = $this->DB->fetchRow($sql)) return $result;




		$sql = 'SELECT `t_id` as `id`, `correct`  FROM `quiz_tests` WHERE `t_id` IN ('.$userquizstart['tests'].')'; //' AND `path` LIKE "'.$request['path'].'"' ; //
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
		$result['serverMassage'] = "پاسخنامه شما ثبت شد. برای شما آرزوی موفقیت داریم.<br /><hr /><b>نتیجه این آزمون:</b><br />پاسخ های درست: $correct_count پاسخ<b> | </b> پاسخ های نادرست: $incorrect_count پاسخ<b> | </b>سوالات بی پاسخ: $noanswer_count سوال<br />نمره آزمون شما: $quiz_result_inpercent درصد";
		if(isset($quiz_setting['endMessage']))
			$result['serverMassage'] = $quiz_setting['endMessage'];
		if(isset($quiz_setting['endGoto']))
			$result['goto'] = $quiz_setting['endGoto'];


		//$sql = "UPDATE `user_quiz_start` SET `status` = '1', `end_time` = NOW(), `quiz_answers` = '".json_encode($request['action-params']['answers'])."', `quiz_result` = '' WHERE `user_id`=".$login_user['id']." AND `status`!=1 AND `path`='".$request['path']."' AND tests LIKE '".implode(', ', $test_ids)."'";
		$sql = "UPDATE `user_quiz_start` SET `status` = '1', `end_time` = NOW(), `quiz_answers` = '".json_encode($request['action-params']['answers'])."', `quiz_result` = '$quiz_result_inpercent', `e_ip`= '".$_SERVER['REMOTE_ADDR']."' WHERE `user_id`=".$login_user['id']." AND `uq_id`=".$userquizstart['uq_id'];
		$this->DB->query($sql);
		$result['status'] = 'answers-registered';

		return $result;
	}
	protected function GetQuizTestsDataset($entity, $login_user, $sql_params, $result, $mode='start')
	{
		$result['serverMassage'] = 'خطای نامشخص! کمی صبر کنید و سپس دوباره تلاش نمایید';
		$result['status'] = 'error-failed';
		$result['goto'] = '#/home';

		// get quiz info and setting
		$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'" ';
		if(!$quiz_result = $this->DB->fetchAll($sql)) return $result;
		//$quiz_setting = json_decode($quiz_result[ 0 ]['desc'], true);
		$quiz_setting = json_decode($quiz_result[ 0 ]['settings'], true);

		// check prerequisite for quiz
		if($mode=="start")
		{
			if($goto = $this->CheckPrerequisite($quiz_result[0]['prerequisite'], $login_user))
				return array_merge($result, $goto);
		}

		if(isset($quiz_setting['maxRenewal']))
		{
			$sql = "SELECT COUNT(*) FROM `user_quiz_start` WHERE `status`=1 AND `user_id`=".$login_user['id']." AND `path`='".$entity['path']."'";
			if($endedcount=$this->DB->fetchOne($sql))
				if($endedcount>=$quiz_setting['maxRenewal'])
				{
					$result['serverMassage'] = "شما قبلا در این آزمون $endedcount مرتبه شرکت نموده و به پایان رسانده اید و دیگر مجوز شرکت در این آزمون را ندارید.";
					$result['status'] = 'error-failed';
					$result['goto'] = '#/home';
					return $result;
				}
		}


		$sql = "SELECT * FROM `user_quiz_start` WHERE `user_id`=".$login_user['id']." AND `path`='".$entity['path']."' AND `renewal`=".$quiz_setting['renewal'];
		if($user_quiz_start = $this->DB->fetchAll($sql))
		{
			if(($mode!='check' & $mode!='preview') | $quiz_result[0]['editable']!='y')
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
				$maxNotEndedCount = 2;
				if(isset($quiz_setting['maxRefresh']))
					$maxNotEndedCount = $quiz_setting['maxRefresh'];

				if($notended_count>=$maxNotEndedCount)
				{
					$result['serverMassage'] = 'شما بیش از چند بار آزمون را شروع کرده ولی به پایان نرسانده اید.';
					$result['status'] = 'error-many-try';
					return $result;
				}
			}
		}

		if(is_array($quiz_setting['testGroups']))
		{
			if($mode=='check' & $quiz_result[0]['editable']=='y')
			{
				$testGroups = array();
				$in_test_groups = array();
				foreach ($quiz_setting['testGroups'] as $value)
				{
					if(!in_array($value['groups'], $testGroups))
					{
						$in_test_group_regex = '(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$value['groups'])) .'\/)';
						$in_test_group =' ( `test_group` RLIKE "'.$in_test_group_regex.'") ';
						$in_test_groups[] = $in_test_group;
						$testGroups[] = $value['groups'];

					}
				}
				$quiz_setting['maxTime'] = 120;
				$sql = 'SELECT `t_id` as `id`, `problem`, `options`, `time`, `correct` as `selected` FROM `quiz_tests` WHERE status=1 AND ('.implode(' OR ', $in_test_groups).')';

			}
			else
			{
				$multi_sql = array();
				foreach ($quiz_setting['testGroups'] as $value)
				{
					//$in_test_group_regex ='(^0$)'. ( (empty($value['groups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$value['groups'])) .'\/)');
					$in_test_group_regex = '(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$value['groups'])) .'\/)';
					$in_test_group =' ( `test_group` RLIKE "'.$in_test_group_regex.'") ';

					if(is_array($value['hardness']))
					{
						$in_test_group .= ' AND `hardness` IN ('.implode(',', $value['hardness']).') ';
					}
					$multi_sql[] = '(SELECT `t_id` as `id`, `problem`, `options`, `time`, "0" as `selected`  FROM `quiz_tests` WHERE status=1 AND '.$in_test_group.' ORDER BY RAND() LIMIT '.$value['count'].')' ;
				}
				$sql = implode(' UNION ALL ', $multi_sql);
			}


			//$result['debug'] = $sql;

		}
		else
		{
			$in_test_group_regex ='(^0$)'. ( (empty($quiz_setting['testGroups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$quiz_setting['testGroups'])) .'\/)');
			$in_test_group =' ( `test_group` RLIKE "'.$in_test_group_regex.'") ';
			$sql = 'SELECT `t_id` as `id`, `problem`, `options`, `time`, "0" as `selected`  FROM `quiz_tests` WHERE status=1 AND '.$in_test_group.' ORDER BY RAND() LIMIT '.$quiz_setting['testCount'].';' ;


			if($mode=='check' & $quiz_result[0]['editable']=='y')
				$sql = 'SELECT `t_id` as `id`, `problem`, `options`, `time`, `correct` as `selected` FROM `quiz_tests` WHERE status=1 AND '.$in_test_group; //'  LIMIT '.$quiz_setting['testCount'].';' ; //ORDER BY RAND()
			elseif($mode=='preview'  & $quiz_result[0]['editable']=='y')
				$sql = 'SELECT `t_id` as `id`, `problem`, `options`, `time`, `correct` as `selected` FROM `quiz_tests` WHERE status=1 AND '.$in_test_group.' LIMIT '.$quiz_setting['testCount'].';' ; //ORDER BY RAND()


		}

		$temp_result = $this->DB->fetchAll($sql);

		$this->_XAL	= new Xal_Servlet('NORMAL_MODE');
		$test_ids = array();
		foreach ($temp_result as $key => $value)
		{
			$options = $this->_XAL->run('<execution>'.$temp_result[ $key ]['options'].'</execution>');
			$temp_result[ $key ]['options'] = $options['var:options'];
			$temp_result[ $key ]['options'][] = array('key' =>0 , 'value'=> 'بدون پاسخ');
			$test_ids[] = $temp_result[ $key ]['id'];
		}
		//	$temp_result[ $key ]['options'] = json_decode($temp_result[ $key ]['options']);
		$result['Quiz']['Setting'] = $quiz_setting;
		$result['Quiz']['Tests'] = $temp_result;
		if(($mode!='check' & $mode!='preview') | $quiz_result[0]['editable']!='y')
		//if ($mode=='start' | $quiz_result[0]['editable']!='y')
		{
			$data = array();
			$data['user_id']=$login_user['id'];
			$data['path'] = $entity['path'];
			$data['renewal'] = $quiz_setting['renewal'];
			$data['tests'] = implode(', ', $test_ids);
			$data['s_ip'] = $_SERVER['REMOTE_ADDR'];
			if(!$this->DB->insert('user_quiz_start', $data))
			{
				$result['Quiz'] = '';
				$result['status'] = 'error-failed';
				return $result;
			}

		}
		$result['serverMassage'] = '';
		$result['status'] = 'started';
		return $result;
	}

	protected function GetSurveyQuestionsDataset($request, $login_user, $sql_params, $result, $mode='start')
	{
		//$result['serverMassage'] = 'خطای نامشخص! کمی صبر کنید و سپس دوباره تلاش نمایید';
		$result['status'] = 'error-failed';
		$result['goto'] = '#/home';

		// get survey info and setting
		$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ';
		if(!$survey_result = $this->DB->fetchAll($sql)) return $result;
		$survey_setting = json_decode($survey_result[ 0 ]['desc'], true);

		$sql = "SELECT * FROM `user_survey_start` WHERE `status`=1 AND `user_id`=".$login_user['id']." AND `path`='".$request['path']."' AND `renewal`=".$survey_setting['renewal'];
		if($user_survey_ended = $this->DB->fetchAll($sql)) return $result;
		$result['goto'] = '';


		$in_question_group_regex ='(^0$)'. ( (empty($survey_setting['questionGroups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$survey_setting['questionGroups'])) .'\/)');
		$in_question_group =' ( `group` RLIKE "'.$in_question_group_regex.'") ';

		$q_order = "`id` ASC";
		if(isset($survey_setting['questionOrder']))
			$q_order = $survey_setting['questionOrder'];

		$sql = 'SELECT `sq_id` as `id`, `statement` , `type`, `options`  FROM `survey_questions` WHERE status=1 AND '.$in_question_group.' ORDER BY '.$q_order.' LIMIT '.$survey_setting['questionCount'].';' ;
		$temp_result = $this->DB->fetchAll($sql);

		$this->_XAL	= new Xal_Servlet('NORMAL_MODE');
		$question_ids = array();
		foreach ($temp_result as $key => $value)
		{
			$options = $this->_XAL->run('<execution>'.$temp_result[ $key ]['options'].'</execution>');
			$temp_result[ $key ]['options'] = $options['var:options'];
			$question_ids[] = $temp_result[ $key ]['id'];
		}

		$result['Survey']['Setting'] = $survey_setting;
		$result['Survey']['Questions'] = $temp_result;

		$data = array();
		$data['user_id']=$login_user['id'];
		$data['path'] = $request['path'];
		$data['renewal'] = $survey_setting['renewal'];
		$data['questions'] = implode(', ', $question_ids);
		$this->DB->insert('user_survey_start', $data);
		$result['debag'][]= '';
		$result['serverMassage'] = '';
		$result['status'] = 'started';
		return $result;
	}
	protected function SetSurveyEnded($request, $login_user, $sql_params, $result)
	{
		// $result['serverMassage'] = 'پاسخنامه شما ثبت شد. برای شما آرزوی موفقیت داریم.';
		$result['Survey'] = array('Setting'=>'', 'Questions'=>'');
		$result['status'] = 'info-failed';
		$result['goto'] = '#/home';

		// get survey info and setting
		$sql = 'SELECT '.$sql_params['table-fields-courses-plan-started-ended-editable'].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" ';
		if(!$survey_result = $this->DB->fetchAll($sql)) return $result;
		$survey_setting = json_decode($survey_result[ 0 ]['desc'], true);
		if(isset($survey_setting['goto'])) 	$result['goto'] = $survey_setting['goto'];


		$sql = "SELECT * FROM `user_survey_start` WHERE `status`=0 AND `user_id`=".$login_user['id']." AND `path`='".$request['path']."' AND `renewal`=".$survey_setting['renewal'] . " ORDER BY  `us_id` DESC LIMIT 1; ";
		if(!$usersurveystart = $this->DB->fetchRow($sql)) return $result;

		$sql = "UPDATE `user_survey_start` SET `status` = '1', `end_time` = NOW(), `survey_answers` = '".json_encode($request['action-params'])."', `survey_result` = '' WHERE `user_id`=".$login_user['id']." AND `us_id`=".$usersurveystart['us_id'];
		$this->DB->query($sql);
		$result['status'] = 'answers-registered';

		$result['serverMassage'] = "پاسخنامه نظرسنجی ثبت شد.";
		return $result;
	}


	protected function CheckPrerequisite($prerequisite, $login_user)
	{
		if(empty($prerequisite)) return false;
		$prerequisite = json_decode($prerequisite, true);
		$table = NULL;
		if(preg_match("/(^\d+\.s[\d\w]{2}$)|(^\d+\.l[\d\w]{2}s[\d\w]{2}$)/", $prerequisite['path']))	$table = ' `user_survey_start` ';
		if(empty($table))	return false;

		$redirection['goto'] = $prerequisite['goto'];
		if(isset($prerequisite['message'])) $redirection['serverMassage'] =  $prerequisite['message'];
		$redirection['status'] = 'prerequisite';

		$where = array();
		foreach ($prerequisite as $key => $value)
		{
			if(in_array($key, array('path', 'goto', 'message'))) continue;
			$where[] = " `$key`$value ";
		}
		$sql_where = '';
		if(count($where)>0) $sql_where = " AND (".implode(" AND ", $where).") ";
		$sql = "SELECT COUNT(*) FROM $table WHERE `user_id`=".$login_user['id']." AND `path` LIKE '".$prerequisite['path']."' $sql_where";
		if(!$count=$this->DB->fetchOne($sql)) return $redirection;
		if($count>0) return false;
		return $redirection;
	}

	protected function GenrateSqlParams($request, $entity, $login_user)
	{

		$sql_params['member-of-group-regex'] ='(^0$)'. ( (empty($login_user['groups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$login_user['groups'])) .'\/)');

		$sql_params['fields-started-ended'] = " if((`start` < NOW()),1,0) AS `started`, if((`end` < NOW()),1,0) AS `ended` ";
		$sql_params['fields-editable'] = ' IF( (`admin_groups` REGEXP "'.$sql_params['member-of-group-regex'].'"), "y","n" ) as `editable` ';
		$sql_params['fields-score'] = " (`like`-`unlike`) as `score` ";
		$sql_params['fields-prerequisite'] = " `prerequisite` ";

		$sql_params['fields-courses'] = " `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, `settings`, `start`, `end`, `like`, `unlike` ";
		// $sql_params['fields-courses-with-vote'] = $sql_params['fields-courses'] .", ".$sql_params['fields-started-ended'].", (SELECT `vote` FROM `like_unlike` WHERE `path`=co.path AND `user_id`=".$login_user['id'].") as vote ";
		$sql_params['fields-user-questions'] = " `fname`, `lname`, `c_id` as `id`, IFNULL(`path`, REPLACE(`prepath`,'__', CONCAT('$',`c_id`)) ) as `path`, `desc`, IF(`user_id`=".$login_user['id'].",'u','o') as `owner`, status , `like`, `unlike` ";
		// $sql_params['fields-user-questions-with-vote'] = $sql_params['fields-user-questions'].", (SELECT `vote` FROM `like_unlike` WHERE `path`=co.path AND `user_id`=".$login_user['id'].") as vote ";
		// $sql_params['fields-courses-with-vote'] = " `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, `settings`, `start`, `end`, `started`, `ended`, Max(`vote`) as vote ";
		//$sql_params['fields-courses-with-vote'] = " `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, `settings`, `start`, `end`, if((`start` < now()),1,0) AS `started`,if((`end` < now()),1,0) AS `ended`, `like`, `unlike`, (SELECT `vote` FROM `like_unlike` WHERE `path`=co.path AND `user_id`=".$login_user['id'].") as vote ";
		//$sql_params['fields-user-questions-with-vote'] = " `fname`, `lname`, `c_id` as `id`, IFNULL(`path`, REPLACE(`prepath`,'__', CONCAT('$',`c_id`)) ) as `path`, `desc`, IF(`user_id`=".$login_user['id'].",'u','o') as `owner`, status , `like`, `unlike`, `vote` ";
		//$sql_params['fields-user-unsorted-qu-an'] = ' `fname`, `lname`, `c_id` as `id`, REPLACE(`path`,"__", CONCAT("$",`c_id`)) as `path`, `desc`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, status ';
		//$sql_params['fields-score'] = " IFNULL( (SELECT score  FROM `path_score` WHERE `path_score`.`path` = `cwv`.`path`), 0) as `score` ";

		$sql_params['table-fields-courses-started-ended'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'] .' FROM `courses`' ;
		$sql_params['table-fields-courses-started-ended-editable'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'] .', '. $sql_params['fields-editable']. ' FROM `courses`' ;
		// $sql_params['table-fields-courses-with-vote'] = $sql_params['fields-courses-with-vote'] .' FROM `courses` as `co` '; //' `course_with_vote` as cwv ' ;
		// $sql_params['table-fields-courses-with-vote-with-score'] = $sql_params['fields-courses-with-vote'] .', '. $sql_params['fields-score'] . ' FROM `courses` as `co` '; //' `course_with_vote` as cwv ' ;

		$sql_params['table-fields-courses-plan-started-ended'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'] .' FROM `course_with_plan`' ;
		$sql_params['table-fields-courses-plan-started-ended-editable'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'] .', '. $sql_params['fields-editable']. ' FROM `course_with_plan`' ;
		$sql_params['table-fields-courses-plan-started-ended-editable-prerequisite'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'] .', '. $sql_params['fields-editable'].', '. $sql_params['fields-prerequisite']. ' FROM `course_with_plan`' ;
		$sql_params['table-fields-courses-plan-started-ended-prerequisite'] = $sql_params['fields-courses'] .', '. $sql_params['fields-started-ended'].', '. $sql_params['fields-prerequisite'] .' FROM `course_with_plan`' ;
		// $sql_params['table-fields-courses-plan-vote'] = $sql_params['fields-courses-with-vote'] .' FROM `course_with_plan` as `co` '; //' `course_plan_vote` as cwv ' ;
		// $sql_params['table-fields-courses-plan-vote-editable'] = $sql_params['fields-courses-with-vote'] .', '. $sql_params['fields-editable'].' FROM `course_with_plan` as `co` '; //' `course_plan_vote` as cwv ' ;
		// $sql_params['table-fields-courses-plan-vote-prerequisite'] = $sql_params['fields-courses-with-vote'] .', '. $sql_params['fields-prerequisite'] .' FROM `course_with_plan` as `co` '; //' `course_plan_vote` as cwv ' ;

		// $sql_params['table-fields-user-questions-with-vote-with-score'] = $sql_params['fields-user-questions-with-vote'] .', '. $sql_params['fields-score'] . ' FROM `user_questions` as `co` ';
		$sql_params['table-fields-user-questions-with-score'] = $sql_params['fields-user-questions'] .', '. $sql_params['fields-score'] . ' FROM `user_questions` as `co` ';
		$sql_params['table-fields-user-questions-editable-with-score'] = $sql_params['fields-user-questions'] .', '. $sql_params['fields-editable'].', '. $sql_params['fields-score'] . ' FROM `user_questions` as `co` ';
		//$sql_params['table-fields-user-questions-with-vote-with-score'] = $sql_params['fields-user-questions-with-vote'] .', '. $sql_params['fields-score'] . ' FROM `user_questions_vote` as `co` ';
		//$sql_params['table-fields-user-unsorted-qu-an'] = $sql_params['fields-user-unsorted-qu-an'] .' FROM `user_questions` ' ;


		$sql_params['is-active-started-notended'] = ' (`status`=1 AND `start` < NOW() AND (`end` IS NULL OR `end` > NOW())) ';
		$sql_params['is-active'] = ' (`status`=1) ';


		// $sql_params['vote-query'] = ""; // " AND `user_id` IN (0,".$login_user['id'].") Group By `path` ";
		$sql_params['vote-query'] = "SELECT `path`, `vote` FROM `like_unlike` WHERE `path` like '".$entity['path']."%' AND `user_id`=".$login_user['id'];

		$sql_params['member-of-user-group'] =' ( user_groups RLIKE "'.$sql_params['member-of-group-regex'] .'") ';
		$sql_params['member-of-admin-group'] =' ( admin_groups RLIKE "'.$sql_params['member-of-group-regex'] .'") ';


		$sql_params['order'] = " ORDER BY `path` ASC";
		$sql_params['order-by-path-asc'] = " ORDER BY `path` ASC";
		$sql_params['order-by-path-desc'] = " ORDER BY `path` DESC";
		$sql_params['order-by-score-desc'] = " ORDER BY `score` DESC";
		$sql_params['order-by-start-date-desc'] = " ORDER BY `start` DESC";
		$sql_params['owner-or-public-or-admin'] = '(`user_id`='.$login_user['id'].') OR ('.$sql_params['member-of-user-group'].' AND `is_public`=1) OR ('.$sql_params['member-of-admin-group'].')';
		$sql_params['owner-or-admin'] = '(`user_id`='.$login_user['id'].') OR ('.$sql_params['member-of-admin-group'].')';
		$sql_params['is-admin'] = $sql_params['member-of-admin-group'];
		$sql_params['is-owner'] = ' (`user_id`='.$login_user['id'].') ';
		$sql_params['limit'] = " LIMIT 0,30 ";
		if(!is_array($request['action-params']))
			if(strpos($request['action-params'], ':'))
			{
				$limit = explode(':', $request['action-params']);
				if(is_numeric($limit[1]))
					$sql_params['limit'] = " LIMIT ".addslashes($limit[0]).",30 "; //.addslashes($limit[1]);
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

	protected function UploadHomeworkFiles($entity, $login_user, $sql_params, $result)
	{
		// $result['debug1'] = $_FILES;
		// $result['debug2'] = $entity;

		$valids['types'] = array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		$valids['size'] = 999999;
		foreach($_FILES as $key=>$file)
		{
			if(empty($key)) continue;
			$destination[$key] = '../public_html/flsimgs/rayadars/lms/'.$entity['root-path'].'/'.$entity['path'].'/';
			if(!file_exists($destination[$key])) mkdir($destination[$key]);
			$destination[$key] .= $key.'.'.$login_user['username'].'.';
		}
		$uploaded = $this->MultiFileUploader($destination, $valids);
		$homework_sent = array();
		foreach ($uploaded as $key => $value)
		{
			if(!empty($value['uri']))
			{
				$data = array();
				$data['id'] = preg_replace("/^homework_/", "", $key);
				$data['uri'] = preg_replace("/^.*\/public_html\//", "/", $uploaded[$key]['uri']);
				$uploaded[$key]['uri'] = $data['uri'];
				$homework_sent[] = $data;
			}
		}
		$result['HomeworkFileUpload'] = $uploaded;
		$result = $this->SetHomeworkFileSent($entity, $login_user, $sql_params, $result, $homework_sent);
		return $result;
	}

	protected function	RemoveHomeworkFile($entity, $login_user, $sql_params, $result, $homework_rm)
	{
		$request_namespace = ($entity['level']==2)?'Course':'Lesson';
		$sql = "SELECT * FROM `user_homework_sent` WHERE `user_id`=".$login_user['id']." AND `path`='".$entity['path']."'";
		if($user_homework_sent = $this->DB->fetchAll($sql))
		{
			if($user_homework_sent[0]['status'] == 1) return $result;
			if(count($homework_rm)==0) return $result;
			$sent_files = array();
			if(!empty($user_homework_sent[ 0 ]['files']))
			{
				$sent_files = json_decode($user_homework_sent[ 0 ]['files'], true);
				foreach ($homework_rm as $k => $v)
				{
					foreach ($sent_files as $key => $value)
					{
						if($value['id']==$v['id'])
						{
							unset($sent_files[$key]);
							unset($homework_rm[$k]);
							$fpath = APPLICATION_PATH . '/../public_html'.$value['uri'];
							// $result['debug'] = $fpath;
							// $result['debug2'] = realpath($fpath);
							if($fpath = realpath(APPLICATION_PATH . '/../public_html'.$value['uri'])) unlink($fpath);
							// if(file_exists($fpath)) unlink($fpath);
						}
					}
				}
			}
			$data = array('files' => json_encode($sent_files), 'sent_count' => count($sent_files) );
			$this->DB->update("user_homework_sent", $data, "`user_id`=".$login_user['id']." AND `path`='".$entity['path']."'");
			$data['files'] = $sent_files;
			$data['user_id'] = $login_user['id'];
			$data['path'] = $entity['path'];
			$result[$request_namespace]['HomeworkSent'][] = $data;
		}

		return $result;
	}
	protected function	SetHomeworkFileSent($entity, $login_user, $sql_params, $result, $homework_sent)
	{
		$request_namespace = ($entity['level']==2)?'Course':'Lesson';
		$sql = "SELECT * FROM `user_homework_sent` WHERE `user_id`=".$login_user['id']." AND `path`='".$entity['path']."'";
		if($user_homework_sent = $this->DB->fetchAll($sql))
		{
			if($user_homework_sent[0]['status'] == 1) return $result;
			if(count($homework_sent)==0) return $result;
			$sent_files = array();
			if(!empty($user_homework_sent[ 0 ]['files']))
			{
				$sent_files = json_decode($user_homework_sent[ 0 ]['files'], true);
				foreach ($sent_files as $key => $value)
				{
					foreach ($homework_sent as $k => $v)
					{
						if($value['id']==$v['id'])
						{
							$sent_files[$key] = $v;
							unset($homework_sent[$k]);
						}
					}
				}
				$homework_sent = array_merge($sent_files, $homework_sent);
			}
			$data = array('files' => json_encode($homework_sent), 'sent_count' => count($homework_sent) );
			$this->DB->update("user_homework_sent", $data, "`user_id`=".$login_user['id']." AND `path`='".$entity['path']."'");
		}
		else
		{
			$data = array('files' => json_encode($homework_sent), 'sent_count' => count($homework_sent) );
			$data['user_id'] = $login_user['id'];
			$data['path'] = $entity['path'];
			$this->DB->insert("user_homework_sent", $data);
		}
		$data['files'] = $homework_sent;
		$data['user_id'] = $login_user['id'];
		$data['path'] = $entity['path'];
		$result[$request_namespace]['HomeworkSent'][] = $data;
		return $result;
	}

	protected function	MultiFileUploader($destinations, $valids)
	{
		if(!is_array($_FILES))	return false;
		$uploaded = array();
		foreach($destinations as $key=>$path)
		{
			if(!isset($_FILES[$key])) continue;
			$uploaded[$key]['uri'] = '';
			$uploaded[$key]['errors'] = array();
			if($_FILES[$key]["error"]!= UPLOAD_ERR_OK)
			{
				$uploaded[$key]['errors'][] = "UploadError";
				continue;
			}
			$valid_size =(isset($valids[$key]))?$valids[$key]['size']:((isset($valids['size']))?$valids['size']:0);
			if ($valid_size>0 and $_FILES[$key]["size"] > $valid_size)		$uploaded[$key]['errors'][] = "FileSizeExceeded";
			$valid_typs =(isset($valids[$key]))?$valids[$key]['types']:((isset($valids['types']))?$valids['types']:array());
			if(count($valid_typs)>0 and !in_array(strtolower($_FILES[$key]['type']), $valid_typs)) $uploaded[$key]['errors'][] = "InvalidFileType";
			// $destinations[$key] = '';
			if(count($uploaded[$key]['errors'])==0)
			{
				$file_rand_name = rand(0, 9999999999) . strtolower( substr($_FILES[$key]['name'], strrpos($_FILES[$key]['name'], '.')) );
				if(move_uploaded_file($_FILES[$key]['tmp_name'], $path.$file_rand_name )) $uploaded[$key]['uri'] = $path.$file_rand_name;
			}
		}
		return $uploaded;
		// return array('destinations'=>$destinations, 'errors'=>$error);
	}


}

?>
