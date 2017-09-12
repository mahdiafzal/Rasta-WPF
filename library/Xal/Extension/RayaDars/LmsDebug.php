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
class Xal_Extension_RayaDars_LmsDebug
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
	protected function	_getDataset($argus)
	{
		if(!is_array($_POST['command'])) return 0;
		$level_to_view = array('1' => 'Course' , '2' => 'Lesson' , '3' => 'Content' );
		//$entity_collection_keys = array('Course'=>'Courses','Lesson'=>'Lessons','Content'=>'Contents','Question'=>'Questions','Answer'=>'Answers','About'=>'About','Help'=>'Helps','Activity'=>'Activities','Quiz'=>'Quizes');

		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_lms');

		// Needle Variables
		$request['namespace'] = $_POST['command'][0];
		$request['path'] = $_POST['command'][1];
		$request['action'] = $_POST['command'][2];
		$request['action-params'] = $_POST['command'][3];
		//$activity_log = $request;
		//$request['relation'] = 'childs';
		//$request['str-query'] = addslashes( trim( ( (isset($_POST['query']))?$_POST['query']:"" ) ) );
		//$result['status'] = 'none';
		if($request['namespace']=="Logout")
		{
			$result['IsLogin'] = false;
			$this->LoginUser(null);
		}
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

		//return array($request, $login_user);
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

		if($login_user['force_to_change']==1)
		{
			$result['IsLogin'] = false;
			$result['User'] = $login_user['username'];
			return array('CommandParts'=>array('View', 'registerform', '', ''), 'Result'=> $result);
		}

		//  $login_user['id'] = 1;
		//  $login_user['groups'] = '/1/';

		$sql_params['fields'][0] = " `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, `start`, `end`, if((`start` < NOW()),1,0) AS `started`,if((`end` < NOW()),1,0) AS `ended` ";
		$sql_params['fields'][1] = " `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote  FROM `course_with_vote` ";
		$sql_params['fields'][2] = ' `fname`, `lname`, `c_id` as `id`, REPLACE(`path`,"__", CONCAT("$",`c_id`)) as `path`, `desc`, IF(`user_id`='.$login_user['id'].',"u","o") as `owner`, `is_public`, status FROM `user_unsorted_qu_an` ';
		$sql_params['fields'][3] = " `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote, IFNULL( (SELECT score  FROM `path_score` WHERE `path_score`.`path` = `course_with_vote`.`path`), 0) as `score` FROM `course_with_vote` ";
		$sql_params['fields'][4] = " `fname`, `lname`, `started`, `ended`, `start`, `end`, `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote, IFNULL( (SELECT score  FROM `path_score` WHERE `path_score`.`path` = `course_with_vote`.`path`), 0) as `score` FROM `user_course_with_vote` ";

		$sql_params['vote-query'] = " AND `user_id` IN (0,".$login_user['id'].") Group By `path` ";
		$sql_params['member-of-user-group'] =' ( user_groups RLIKE "(^0$)'. ( (empty($login_user['groups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$login_user['groups'])) .'\/)") ');
		$sql_params['member-of-admin-group'] =' ( admin_groups RLIKE "(^0$)'. ( (empty($login_user['groups']))? '") ' : '|(\/'. str_replace('/','\/)|(\/',preg_replace("/(^\/)|(\/$)/","",$login_user['groups'])) .'\/)") ');
		$sql_params['order'] = " ORDER BY `path` ASC";
		$sql_params['order-by-score'] = " ORDER BY `score` DESC";
		$sql_params['owner-or-public-or-admin'] = '(`user_id`='.$login_user['id'].') OR ('.$sql_params['member-of-user-group'].' AND `is_public`=1) OR ('.$sql_params['member-of-admin-group'].')';
		$sql_params['owner-or-admin'] = '(`user_id`='.$login_user['id'].') OR ('.$sql_params['member-of-admin-group'].')';
		$sql_params['is-admin'] = $sql_params['member-of-admin-group'];
		$sql_params['is-owner'] = ' (`user_id`='.$login_user['id'].') ';
		$sql_params['is-active-started-notended'] = ' (`status`=1 AND `started`=1 AND `ended`=0) ';

		$entity = $this->EntityDetailFromPath($request['path']);
		//return $entity;
		//$entity['root-path'] = array_shift( explode(".", $request['path']) );
		//$result[$request['namespace']] = array();

		// course dataset
		// Course, l=Lesson, c=Content, q=Question, n=Answer, a=About, h=Help, v=Activity, z=Quiz
		$like_unlike_action = false;
		if($request['action']=="like" | $request['action']=="unlike" )
		{
			$data = array();
			$data['vote'] = ($request['action']=='like')?1:2;
			try
			{
				$data['path'] = $request['path'];
				$data['user_id'] = $login_user['id'];
				$this->DB->insert('like_unlike', $data);
			} catch (Exception $e) {
				$sql = "UPDATE  `like_unlike` SET  `vote` = (CASE WHEN `vote`=".$data['vote']." THEN 0 ELSE ".$data['vote']." END) WHERE  `user_id` =".$login_user['id']." AND  `path` = '".$request['path']."';";
				$this->DB->query($sql);
				//$this->DB->update('like_unlike', $data, '`path`="'.$request['path'].'" AND `user_id`='.$login_user['id']);
			}
			$result['status'] = 'done';
			//$this->LogActivity($request, $login_user, $result['status']);
			$like_unlike_action = true;
		}

		if($request['action']=="setpublic" | $request['action']=="unsetpublic" | $request['action']=="deny" )
		{
			$is_public = ($request['action']=='setpublic')?1:0;
			$qn_status = ($is_public==1)?1:(($request['action']=='deny')?2:3);
			//return $entity;
			if(strpos($request['path'], '$'))
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
			else
			{
				$record_where = "`path`='".$request['path']."' AND ".$sql_params['member-of-admin-group'];
				// sql TRANSACTION
				$sql = "START TRANSACTION;\n";
				$sql .= "UPDATE `unsorted_qu_an` SET `status`='".$qn_status."', `is_public`='".$is_public."' WHERE ".$record_where.";\n";
				$sql .= "UPDATE `courses` SET `status`='".$is_public."' WHERE ".$record_where.";\n";
				$sql .= "COMMIT;";
				$this->DB->query($sql);
			}
			$result['status'] = 'done';

			if($request['namespace']=='Question') //preg_replace("/\.?[\w\d]{3}$/", "", $new_path)
				$result = $this->GetQuestionDataset(array('path'=> $entity['parent-path'], 'level'=> $entity['level']-1 ), $sql_params, $result);
			elseif ($request['namespace']=='Answer')
				$result = $this->GetAnswerDataset(array('path'=> $entity['parent-path'], 'level'=> $entity['level']-1 ), $sql_params, $result);


		}

		if($request['namespace']=="Home")
		{
			$result = $this->GetHomeDataset($login_user, $sql_params, $result);
		}
		elseif ($request['namespace']=="Course")
		{
			// validate course path: is available courses for user groups
			//$sql = 'SELECT * FROM `courses` WHERE status=1 AND level=1 AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'].' AND `path` NOT IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].')' ;
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].')  AND level=1 AND `path`="'.$entity['root-path'].'" AND '.$sql_params['member-of-user-group'].$sql_params['vote-query'];
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

			// l=Lesson
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.l__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Lessons'] = $this->DB->fetchAll($sql);

			// q=question
			$result = $this->GetQuestionDataset($entity, $sql_params, $result);

			// a=About
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.a__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['About'] = $this->DB->fetchAll($sql);

			// h=Help
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=2 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['root-path'].'.h__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Helps'] = $this->DB->fetchAll($sql);

			// z=Quiz
			$result = $this->GetQuizDataset($entity, $sql_params, $result);
		}
		elseif ($request['namespace']=="Lesson")
		{
			// validate course path: is available lesson for user groups
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE ('.$sql_params['is-active-started-notended'].' OR '.$sql_params['is-admin'].') AND level=2 AND `path`="'.$request['path'].'" AND '.$sql_params['member-of-user-group'].$sql_params['vote-query'];
			if(!$temp_result = $this->DB->fetchAll($sql)) return $result; // this path not available for user
			$result[$request['namespace']]['debug'] = $sql;
			$result[$request['namespace']]['Metadata'] = $temp_result[0];

			// c=Content
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'c__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Contents'] = $this->DB->fetchAll($sql);

			// q=Question
			$result = $this->GetQuestionDataset($entity, $sql_params, $result);

			// a=About
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'a__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['About'] = $this->DB->fetchAll($sql);

			// h=Help
			$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'h__" '.$sql_params['vote-query'].$sql_params['order'] ;
			$result[$request['namespace']]['Helps'] = $this->DB->fetchAll($sql);

			// z=Quiz
			$result = $this->GetQuizDataset($entity, $sql_params, $result);
		}
		elseif ($request['namespace']=="Content")
		{
			if($request['action']=='show')
			{
				$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=3 AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$request['path'].'" '.$sql_params['vote-query'] ;
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
		elseif ($request['namespace']=="Quiz")
		{
			$result = $this->GetQuizTestsDataset($entity, $sql_params, $result);

		}
		elseif ($request['namespace']=="Question" & $request['action']=="answer")
		{
				$result = $this->GetAnswerDataset($entity, $sql_params, $result);
		}
		elseif ($request['namespace']=="Question" & $request['action']=="new")
		{
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

		}
		elseif ($request['namespace']=="Answer" & $request['action']=="new")
		{
				$reg_result = array('registeration' =>  array('status' => false , 'title'=>'ثبت پاسخ', 'message'=>'خطا در ثبت پاسخ!' ) ); // , 'hashcommand'=>''
				$result['status'] = 'error';

				$parent_path = preg_replace("/n__$/",'', $request['path']);
				//$reg_result['registeration']['hashcommand'] = "#/question(".$parent_path.").answer";
				$sql = 'SELECT * FROM `courses` WHERE status=1 AND '.$sql_params['member-of-user-group'].' AND `path` = "'.$parent_path.'" ' ;
				if($temp_result = $this->DB->fetchAll($sql))
				{
						$data['path'] = $request['path'];
						$data['desc'] = $request['action-params'];
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
		}
		elseif ($request['namespace']=="Activities" & $request['action']=="report")
		{
			// // v=Activity
			// $sql = 'SELECT * FROM `activity_report_view` ' ;
			// $result['Course']['Activities'] = $this->DB->fetchAll($sql);

		}
		elseif (in_array($request['namespace'],array("Help", "Contact")) )
		{
			$sql = 'SELECT * FROM `static_pages` WHERE `name` = "'.$request['namespace'].'" ' ;
			$result['StaticPage'] = $this->DB->fetchRow($sql);
			$request['namespace'] = 'StaticPage';
		}
		if(!$like_unlike_action)
			$this->LogActivity($request, $login_user, ((isset($result['status']))?$result['status']:''));

		return array('CommandParts'=>array_values($request), 'Result'=> $result);
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
		$sql = 'SELECT '.$sql_params['fields'][0].' FROM `courses` WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` NOT IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') '.$sql_params['order'] ;
		$result['Home']['AvailableCourses'] = $this->DB->fetchAll($sql);

		// user courses
		$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level=1 AND '.$sql_params['member-of-user-group'].' AND `path` IN ( SELECT `path` FROM `user_course_registration` WHERE `user_id`='.$login_user['id'].') '.$sql_params['vote-query'].$sql_params['order'] ;
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
	protected function GetQuestionDataset($entity, $sql_params, $result)
	{
		// $entity is patent entity
		$request_namespace = ($entity['level']==1)?'Course':'Lesson';
		$entity['path'] .= ($entity['level']==1)?'.':'';
		// q=Question
		//$sql = 'SELECT '.$sql_params['fields'][3].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'q__" '.$sql_params['vote-query'].$sql_params['order-by-score'] ;
		$sql = 'SELECT '.$sql_params['fields'][3].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'q__" '.$sql_params['vote-query'];
		$sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_unsorted_qu_an` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score'] ;
		$result[$request_namespace]['Questions'] = $this->DB->fetchAll($sql);

		// Unsorted Course Questions
		$sql = 'SELECT '.$sql_params['fields'][2].' WHERE (`status` NOT IN (0,2) OR (`status`!=0 AND '.$sql_params['is-owner'].') ) AND level='.($entity['level']+1).' AND (`reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) OR '.$sql_params['is-owner'].') AND ('.$sql_params['owner-or-admin'].') AND `path` LIKE "'.$entity['path'].'q__" ORDER BY  `c_id` DESC ';//.$sql_params['order'] ;

		$result[$request_namespace]['UnQuestions'] = $this->DB->fetchAll($sql);

		return $result;
	}
	protected function GetAnswerDataset($entity, $sql_params, $result)
	{
		//$answer_view = ($entity['level']==2)?'Course':'Lesson';
		$answer_view = 'Course';

		// n=Answer
		$sql = 'SELECT '.$sql_params['fields'][3].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'n__" '.$sql_params['vote-query'] ;
		$sql = "SELECT  pr.`fname`, pr.`lname`, qn.* FROM (`user_unsorted_qu_an` as pr RIGHT JOIN ($sql) as qn on pr.`path`=qn.`path`) WHERE pr.`lname` IS NOT NULL ".$sql_params['order-by-score'];
		$result['Question'][$answer_view]['Answers'] = $this->DB->fetchAll($sql);

		// Unsorted Answers
		$sql = 'SELECT '.$sql_params['fields'][2].' WHERE status NOT IN (0,2) AND level='.($entity['level']+1).' AND `reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) AND ('.$sql_params['member-of-admin-group'].') AND `path` LIKE "'.$entity['path'].'n__" ORDER BY  `c_id` DESC ';//.$sql_params['order'] ;
		$result['Question'][$answer_view]['UnAnswers'] = $this->DB->fetchAll($sql);
		return $result;
	}
	// protected function _forceDownload($argus)
	// {
	// 	echo "s1";
	// 	if(!isset($_REQUEST['token'])) return false;
	// 	echo "s2";
	// 	if(!isset($_SESSION['LmsApp']['Download'])) return false;
	// 	echo "s3";
	// 	if(!isset($_SESSION['LmsApp']['Download'][$_REQUEST['token']])) return false;
	// 	echo "s4";
	// 	print_r($_SESSION['LmsApp']['Download']);
	// 	$this->force_download($_SESSION['LmsApp']['Download'][$_REQUEST['token']]);
	// 	echo $_SESSION['LmsApp']['Download'][$_REQUEST['token']];
	// }
	protected function force_download($file)
	{
		$ext = explode(".", $file);
		//echo( file_exists($file)."nnnnnn".__FILE__ );
		//die();
		switch($ext[sizeof($ext)-1])
		{
			case 'jar': $mime = "application/java-archive"; break;
			case 'zip': $mime = "application/zip"; break;
			case 'jpeg': $mime = "image/jpeg"; break;
			case 'jpg': $mime = "image/jpg"; break;
			case 'jad': $mime = "text/vnd.sun.j2me.app-descriptor"; break;
			case "gif": $mime = "image/gif"; break;
			case "png": $mime = "image/png"; break;
			case "pdf": $mime = "application/pdf"; break;
			case "txt": $mime = "text/plain"; break;
			case "doc": $mime = "application/msword"; break;
			case "ppt": $mime = "application/vnd.ms-powerpoint"; break;
			case "wbmp": $mime = "image/vnd.wap.wbmp"; break;
			case "wmlc": $mime = "application/vnd.wap.wmlc"; break;
			case "mp4s": $mime = "application/mp4"; break;
			case "ogg": $mime = "application/ogg"; break;
			case "pls": $mime = "application/pls+xml"; break;
			case "asf": $mime = "application/vnd.ms-asf"; break;
			case "swf": $mime = "application/x-shockwave-flash"; break;
			case "mp4": $mime = "video/mp4"; break;
			case "m4a": $mime = "audio/mp4"; break;
			case "m4p": $mime = "audio/mp4"; break;
			case "mp4a": $mime = "audio/mp4"; break;
			case "mp3": $mime = "audio/mpeg"; break;
			case "m3a": $mime = "audio/mpeg"; break;
			case "m2a": $mime = "audio/mpeg"; break;
			case "mp2a": $mime = "audio/mpeg"; break;
			case "mp2": $mime = "audio/mpeg"; break;
			case "mpga": $mime = "audio/mpeg"; break;
			case "wav": $mime = "audio/wav"; break;
			case "m3u": $mime = "audio/x-mpegurl"; break;
			case "bmp": $mime = "image/bmp"; break;
			case "ico": $mime = "image/x-icon"; break;
			case "3gp": $mime = "video/3gpp"; break;
			case "3g2": $mime = "video/3gpp2"; break;
			case "mp4v": $mime = "video/mp4"; break;
			case "mpg4": $mime = "video/mp4"; break;
			case "m2v": $mime = "video/mpeg"; break;
			case "m1v": $mime = "video/mpeg"; break;
			case "mpe": $mime = "video/mpeg"; break;
			case "mpeg": $mime = "video/mpeg"; break;
			case "mpg": $mime = "video/mpeg"; break;
			case "mov": $mime = "video/quicktime"; break;
			case "qt": $mime = "video/quicktime"; break;
			case "avi": $mime = "video/x-msvideo"; break;
			case "midi": $mime = "audio/midi"; break;
			case "mid": $mime = "audio/mid"; break;
			case "amr": $mime = "audio/amr"; break;
			default: $mime = "application/force-download";
		}
		header('Content-Description: File Transfer');
		header('Content-Type: '.$mime);
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		ob_clean();
		flush();

		readfile($file);
	}
	protected function GetQuizDataset($entity, $sql_params, $result)
	{
		// $entity is patent entity
		$request_namespace = ($entity['level']==1)?'Course':'Lesson';
		$entity['path'] .= ($entity['level']==1)?'.':'';


		$sql = 'SELECT '.$sql_params['fields'][1].' WHERE status=1 AND level='.($entity['level']+1).' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'z__" '.$sql_params['vote-query'].$sql_params['order'] ;
		$temp_result = $this->DB->fetchAll($sql);
		// $result['debug']['Quizes'] = $sql;
		foreach ($temp_result as $key => $value)
		{
			$temp_result[ $key ]['desc'] = json_decode($temp_result[ $key ]['desc']);
			// $DateOfRequest = date("Y-m-d H:i:s", strtotime($_REQUEST["DateOfRequest"]));
		}
		$result[$request_namespace]['Quizes'] = $temp_result;
		return $result;
	}
	protected function GetQuizTestsDataset($entity, $sql_params, $result)
	{
		$sql = 'SELECT '.$sql_params['fields'][1].' WHERE '.$sql_params['is-active-started-notended'].' AND '.$sql_params['member-of-user-group'].' AND `path` LIKE "'.$entity['path'].'" '.$sql_params['vote-query'];
		if(!$quiz_result = $this->DB->fetchAll($sql)) return $result;
		$quiz_setting = json_decode($quiz_result[ 0 ]['desc'], true);

		$sql = 'SELECT `problem` ,  `options` ,  `time`  FROM `quiz_tests` WHERE status=1 AND `path` LIKE "'.$entity['path'].'" ORDER BY RAND() LIMIT '.$quiz_setting['testCount'].';' ;
		$temp_result = $this->DB->fetchAll($sql);
		foreach ($temp_result as $key => $value)
			$temp_result[ $key ]['options'] = json_decode($temp_result[ $key ]['options']);
		$result['Quiz']['Setting'] = $quiz_setting;
		$result['Quiz']['Tests'] = $temp_result;
		return $result;
	}


}
// SELECT * FROM `courses` as co LEFT JOIN `like_unlike` as lu ON co.path = lu.path
// SELECT `user_id`, Max(user_id) FROM `course_with_vote` where user_id IN (0,1) group by `path`
// SELECT REPLACE(`path`,"__", CONCAT("$",`c_id`)) as `path`, `desc`, IF(`user_id`=1,"u","o") as `owner`, `is_public`, status FROM `unsorted_qu_an` WHERE status NOT IN (0,2) AND level=3 AND (`reg_time`> DATE_ADD(NOW(), INTERVAL -3 DAY) OR owner="u") AND ('.$sql_params['member-of-admin-group'].') AND `path` LIKE "1.q01n__"
// SELECT `name` , `desc`, `type`, `path`, `level`, `thumb`, `src`, Max(`vote`) as vote  FROM `course_with_vote` WHERE status=1 AND level=2 AND  `path` LIKE "1.q__" AND `user_id` IN (0,".$login_user['id'].") Group By `path`


?>
