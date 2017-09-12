<?php
	class Xal_Extension_Ershad_RegisterationPr
	{
		public function	run($argus)
		{
			if(!is_string($argus['cu.ns']))	$argus['cu.ns'] = 'default';
			foreach($argus as $ark=>$argu)
			{
				switch($ark)
				{
					case 'reg.infos'	: return $this->_regInfos($argu); break;
					case 'login.user'	: return $this->_loginUser($argu); break;
					case 'check.login'	: return $this->_checkLogin($argu); break;
					case 'logout.user'	: return $this->_logoutUser($argu); break;
					case 'is.registration.complete'	: return $this->_isRegistrationComplete($argu); break;
					case 'get.dataset'	: return $this->_getDataset($argu); break;
					case 'file.upload'	: return $this->_fileUpload($argu); break;
					case 'delete.person': return $this->_delPerson($argu); break;
					
					
					
					case 'reg.user'	: return $this->_regUser($argu); break;
					case 'reg.proposal'	: return $this->_regProposal($argu); break;
					case 'reg.owner'	: return $this->_regOwner($argu); break;
					case 'reg.extera'	: return $this->_regExtera($argu); break;
					case 'reg.office'	: return $this->_regOffice($argu); break;
					case 'reg.scans'	: return $this->_regScans($argu); break;
					case 'get.proposal'	: return $this->_getProposal($argu); break;
					case 'upload'	: return $this->_upload($argu); break;
					case 'upload.isaargari'	: return $this->_uploadIsaargari($argu); break;
					case 'upload.uploadcertFirstImage'	: return $this->_uploadcertFirstImage($argu); break;
					case 'upload.uploadcertSecondImage'	: return $this->_uploadcertSecondImage($argu); break;
					case 'upload.uploadcertThirdImage'	: return $this->_uploadcertThirdImage($argu); break;
					case 'upload.uploadcardFirstImage'	: return $this->_uploadcardFirstImage($argu); break;
					case 'upload.uploadcardSecondImage'	: return $this->_uploadcardSecondImage($argu); break;
					case 'upload.uploadsolderingImageForw'	: return $this->_uploadsolderingImageForw($argu); break;
					case 'upload.uploadsolderingImageBack'	: return $this->_uploadsolderingImageBack($argu); break;
					case 'upload.eduCardImage'	: return $this->_eduCardImage($argu); break;
					case 'upload.jobHistoryCard'	: return $this->_jobHistoryCard($argu); break;
					case 'get.proposalStatus'	: return $this->_getProposalStatus($argu); break;
				}
			}
		}
		
		
		
		
		
		
		
		
		protected function _delPerson($argus)
		{ 
			if(empty($_POST['PersonId'])) return -1;
			$KanoonId = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginUser.KanoonId");
			$PersonId = $_POST['PersonId'];
			if( !is_object($this->MDB) or  $this->MDB==NULL)	$this->MDB = $this->DbConnect();
			
			try
			{
				$query['_id'] = new MongoId($PersonId);
				$query['Metadata.KanoonId']= new MongoId($KanoonId);
				$this->MDB->persons->remove($query);
				return 1;
			}catch (MongoConnectionException $e){
				return 'Error connecting to MongoDB server';
			}catch (MongoException $e){
				return 'Error: ' . $e->getMessage();
			}
			/*}catch (Exception $e){
				return $e;
				die('Error: ' . $e->getMessage());
			}*/
			
		}
		protected function _fileUpload($argus)
		{ 
			$KanoonId = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginUser.KanoonId");
			if(!$KanoonId)return -1;
			foreach($_FILES as $key=>$file) break;
			if(empty($key)) return -2;
			
			$valids['type'] = array('image/png', 'image/gif', 'image/jpeg', 'image/pjpeg');
			$valids['size'] = 5242880;
			$destination[$key] = '../public_html/flsimgs/ershad/FileServer/'.$KanoonId.'/';
			if(!file_exists($destination[$key])) mkdir($destination[$key]);
			$result = $this->MultiFileUploader($destination, $valids);
			if(count($result['errors'][$key])==0) return $result['destinations'][$key];
			return -10;
		}
		protected function _getDataset($argus)
		{
			if(!$user = $this->IsUserLogin()) return -1;
			$KanoonId = ($user['role']=='kanoon')?$user['KanoonId']:null;
			
			if(!isset($_REQUEST['dataset']))return -2;
			$schemas = $_REQUEST['dataset'];
			if(!is_array($schemas)) return -3;
			if( !is_object($this->MDB) or  $this->MDB==NULL)	$this->MDB = $this->DbConnect();
			$relations['='] = '$eq';
			foreach($schemas as $schema)
			{
				$fields['nodata'] = 1;
				$pattern = "/^kanoons(\[([\w\.]+)([\=\>\<\^\*\$])([\w\.]+)\])?\.([\w\.]+)?\[([\w\.\,\s]+)\]/";
				if( preg_match($pattern, $schema, $matches))
					if(isset($matches[4]))
					{
						if(empty($matches[1]) & empty($matches[2]) & empty($matches[3]) & empty($matches[4])) return -4;
						//if(!in_array($user['role'], array('staff'))) return -5;
						$collection = $this->MDB->kanoons;
						$query[ $matches[2] ] = array($relations[ $matches[3] ]=> $matches[4]);
						if(isset($matches[6]))
						{
							$fields = array_map(trim ,explode(',',$matches[6]));
							if(!empty($matches[5]))
								foreach($fields as $k=>$v)
									$fields[$k] = $matches[5].'.'.$v;
							$fields = array_fill_keys($fields, 1);
						}
						
					}
					
				$pattern = "/^kanoon(\[([\w\.]+)([\=\>\<\^\*\$])([\w\.]+)\])?\.([\w\.]+)?\[([\w\.\,\s]+)\]/";
				if( preg_match($pattern, $schema, $matches))
					if(isset($matches[4]))
					{
						
						//if($KanoonId==null)
						//{
							//if(!in_array($user['role'], array('staff'))) return -5;
							$KanoonId = (isset($_REQUEST['_id']))?$_REQUEST['_id']:null;
							if($KanoonId==null) return -6;
						//}
						$collection = $this->MDB->kanoons;
						
						if(!empty($matches[2]) & !empty($matches[3]) & !empty($matches[4]))
							$query[ $matches[2] ] = array($relations[ $matches[3] ]=> $matches[4]);
						$query['_id'] = new MongoId($KanoonId);
						if(isset($matches[6]))
						{
							$fields = array_map(trim ,explode(',',$matches[6]));
							if(!empty($matches[5]))
								foreach($fields as $k=>$v)
									$fields[$k] = $matches[5].'.'.$v;
							$fields = array_fill_keys($fields, 1);
						}
						//return $fields;
					}
				$pattern = "/^persons(\[([\w\.]+)([\=\>\<\^\*\$])([\w\.]+)\])?\.([\w\.]+)?\[([\w\.\,\s]+)\]/";
				if( preg_match($pattern, $schema, $matches))
				{
					//return $matches;
					if(isset($matches[4]))
					{
						//if($KanoonId==null)
						//{
							//if(!in_array($user['role'], array('staff'))) return -5;
							$KanoonId = (isset($_REQUEST['_id']))?$_REQUEST['_id']:null;
							if($KanoonId==null) return -6;
						//}
						//return $matches;
						$collection = $this->MDB->persons;
						if(!empty($matches[2]) & !empty($matches[3]) & !empty($matches[4]))
							$query[ $matches[2] ] = array($relations[ $matches[3] ]=> $matches[4]);
						$query['Metadata.KanoonId'] = new MongoId($KanoonId);
						if(isset($matches[6]))
						{
							$fields = array_map(trim ,explode(',',$matches[6]));
							if(!empty($matches[5]))
								foreach($fields as $k=>$v)
									$fields[$k] = $matches[5].'.'.$v;
							$fields = array_fill_keys($fields, 1);
						}
						
					}
				}
				$pattern = "/^person(\[([\w\.]+)([\=\>\<\^\*\$])([\w\.]+)\])?\.([\w\.]+)?\[([\w\.\,\s]+)\]/";
				if( preg_match($pattern, $schema, $matches))
					if(isset($matches[4]))
					{
						if($KanoonId==null)
						{
							//if(!in_array($user['role'], array('staff'))) return -5;
						}
						$PersonId = (isset($_REQUEST['_id']))?$_REQUEST['_id']:null;
						if($PersonId==null) return -6;
						$collection = $this->MDB->persons;
						if(!empty($matches[2]) & !empty($matches[3]) & !empty($matches[4]))
							$query[ $matches[2] ] = array($relations[ $matches[3] ]=> $matches[4]);
						
						if($KanoonId!=null)
							$query['Metadata.KanoonId'] = new MongoId($KanoonId);
						$query['_id'] = new MongoId($PersonId);
						if(isset($matches[6]))
						{
							$fields = array_map(trim ,explode(',',$matches[6]));
							if(!empty($matches[5]))
								foreach($fields as $k=>$v)
									$fields[$k] = $matches[5].'.'.$v;
							$fields = array_fill_keys($fields, 1);
						}
					}
				if(!isset($query)) return 0;
				$results = $collection->find($query, $fields);
				if($results->count()<1) return -30;
				return array("schema"=>$schema , "data"=> array_values( iterator_to_array($results) ) );
			}
			

			
			
			switch($_REQUEST['dataset'])
			{
				case 'kanoon.persons.abstract':
					$collection = $this->MDB->persons;
					$query['Metadata.KanoonId'] = new MongoId($KanoonId);
					$fields = array("OrganizationInfos.Roles"=>1, "PersonalInfos.FirstName"=>1, "PersonalInfos.LastName"=>1);
					break;
				case 'kanoon.person.all':
					if(!isset($_REQUEST['PersonId'])) return -11;
					$collection = $this->MDB->persons;
					$query['_id'] = new MongoId($_REQUEST['PersonId']);
					$fields = array();
					break;
				case 'kanoon.baseinfo.all':
					$collection = $this->MDB->kanoons;
					$query['_id'] = new MongoId($KanoonId);
					$fields = array("BaseInfos"=>1, "Metadata"=>1 );
					break;
				case 'kanoon.docscans.all':
					$collection = $this->MDB->kanoons;
					$query['_id'] = new MongoId($KanoonId);
					$fields = array("Files.DocScans"=>1);
					break;
					
			}
			
			if(!isset($query)) return 0;
			$results = $collection->find($query, $fields);
			if($results->count()<1) return -30;
			return array_values( iterator_to_array($results) );
		}
		protected function _isRegistrationComplete($argus)
		{
			if(!$user = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginUser")) return -1;
			return Rasta_Util_DotNotation::GetValue($this->GetKanoonUser($user), "progress.Registration.End.value");
		}
		protected function _checkLogin($argus)
		{
			return $this->IsUserLogin();
		}
		protected function _logoutUser($argus)
		{
			$_SESSION = Rasta_Util_DotNotation::Set($_SESSION, array("KanoonRegApp.LoginUser"=>false, "MyApp.extra_user_groups"=>''));
			return true;
		}
		protected function _loginUser($argus)
		{
			if(!is_array($_POST['user']))	return -1;
			if(!isset($_POST['user']['username']) | !isset($_POST['user']['password'])) return -2;
			if( !is_object($this->MDB) or  $this->MDB==NULL)	$this->MDB = $this->DbConnect();
			
			if(is_numeric($_POST['user']['username']))
				$document['CellPhone'] = $_POST['user']['username'];
			else
				$document['Email'] = $_POST['user']['username'];
			
			$document['Password'] = md5($_POST['user']['password']);
			
			$kanoon = $this->GetKanoonUser($document);
			if(is_array($kanoon['user']))
			{
				$this->SetLoginUserSession($kanoon['user']);
				return $kanoon;
			}
			return -10;
		}	
		protected function _regInfos($argus)
		{
			//return $_POST;
			if( !is_object($this->MDB) or  $this->MDB==NULL)	$this->MDB = $this->DbConnect();
			if($this->IsUserLogin())
			{
				$this->LoginUser = $_SESSION['KanoonRegApp']['LoginUser'];
				//print_r($_SESSION);
				if(is_array($_POST['user']))
				{
				 	$user = $this->RegisterUser();
					$this->DbDiconnect();
					return $user;
				}
				if(is_array($_POST['person']))
				{
					$person = $this->RegisterPerson();
					$this->DbDiconnect();
					return $person;
				}
				if(is_array($_POST['kanoon']))
				{
					$kanoon = $this->InsertEmbededDocument('kanoon');
					$this->DbDiconnect();
					return $kanoon;
				}
			}
			else
			{
				$user['$or'][] = array('Email'=>$_POST['kanoon']['LoginInfos']['Users'][0]['Email']);
				$user['$or'][] = array('CellPhone'=>$_POST['kanoon']['LoginInfos']['Users'][0]['CellPhone']);
				if(!is_array($this->GetKanoonUser($user)))
				{
					$kanoon = $this->RegisterKanoon();
					$this->DbDiconnect();
					return $kanoon;
				}
			}
			$this->DbDiconnect();
			return -40;
		}
		
		protected function SetLoginUserSession($user)
		{
			$_SESSION = Rasta_Util_DotNotation::Set($_SESSION, array("KanoonRegApp.LoginUser"=>$user, "MyApp.extra_user_groups"=>'/3/'));
		}
		protected function GetKanoonProgresses($key='')
		{
			if(!$user = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginUser")) return -1;
			if( !is_object($this->MDB) or  $this->MDB==NULL)	$this->MDB = $this->DbConnect();
			$query = array("_id"=> new MongoId($user['KanoonId']));
			$key = (!empty($key))?'.'.$key:'';
			$fields[ 'Progress'.$key ] = 1;
			$result = $this->MDB->kanoons->find($query, $fields);
			if($result->count()!=1) return -1;
			foreach($result as $doc)
				return $doc['Progress'];
			//return $result->getNext();
		}
		protected function GetKanoonUser($user)
		{
			//if(isset($user['Password'])) $user['Password'] = md5($user['Password']);
			if( !is_object($this->MDB) or  $this->MDB==NULL)	$this->MDB = $this->DbConnect();
			$query = array("LoginInfos.Users"=> array('$elemMatch'=> $user));
			$fields = array('LoginInfos.Users.$'=>1, 'Progress'=>1, 'BaseInfos'=>1);
			$kanoons = $this->MDB->kanoons->find($query, $fields);
			if($kanoons->count()!=1) return -1;
			$kanoon = $kanoons->getNext();
			
			$result['user'] = $kanoon['LoginInfos']['Users'][0];
			$result['user']['KanoonId'] = $kanoon['_id']->{'$id'};
			$result['progress'] = (isset($kanoon['Progress']))?$kanoon['Progress']:array();
			$result['baseinfos'] = (isset($kanoon['BaseInfos']))?$kanoon['BaseInfos']:array();
			return $result;
		}
		
		protected function RegisterKanoon()
		{
			if(!is_array($_POST['kanoon']['LoginInfos'])) return -1;
			if(!is_array($_POST['kanoon']['LoginInfos']['Users'])) return -2;
			if(!is_array($_POST['kanoon']['LoginInfos']['Users'][0])) return -3;
			
			date_default_timezone_set('Asia/Tehran');
			$collection = $this->MDB->kanoons;
			$document = $_POST['kanoon'];
			$document['LoginInfos']['Users'][0]['Password'] = md5($document['LoginInfos']['Users'][0]['Password']);
			$document['Metadata'] = array('RegisterDate'=> date('Y/m/d H:i:s a', time()) );
			if(isset($document['_id']))	unset($document['_id']);
			try
			{
				$collection->insert($document);
				return $document['LoginInfos']['Users'][0];
			}catch (MongoException $e){
				die('Error: ' . $e->getMessage());
			}		
		}
		protected function InsertEmbededDocument($entity)
		{
			if(!$user = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginUser")) return -1;
			$query = array("_id"=> new MongoId($user['KanoonId']));
			$collection = $entity.'s';
			$document['$set'] = $_POST[$entity];
			return $this->UpdateDocument($collection, $query, $document);
		}
		protected function UpdateDocument($collection, $query, $document)
		{
			if( !is_object($this->MDB) or  $this->MDB==NULL)	$this->MDB = $this->DbConnect();
			try
			{
				$this->MDB->$collection->update($query, $document);
				return 1;
			}catch (MongoException $e){
				return -30;
				die('Error: ' . $e->getMessage());
			}
		}
		
		protected function RegisterPerson()
		{
			if( !is_array($this->LoginUser) or count($this->LoginUser)==0) return -1;
			$_return['FirstName'] = Rasta_Util_DotNotation::GetValue($_POST,"person.PersonalInfos.FirstName.value");
			$_return['LastName'] = Rasta_Util_DotNotation::GetValue($_POST,"person.PersonalInfos.LastName.value");
			if(empty($_return['FirstName'])| empty($_return['LastName'])) return -2;
			date_default_timezone_set('Asia/Tehran');
			$collection = $this->MDB->persons;
			$document = $_POST['person'];
			$document['Metadata'] = array('KanoonId'=>new MongoId($this->LoginUser['KanoonId']), 'RegisterDate'=> date('Y/m/d H:i:s a', time()) );
			if(isset($document['_id'])) unset($document['_id']);
			if(isset($document['PersonId']))
			{
				if(strlen($document['PersonId'])==24)	$document['_id']= new MongoId($document['PersonId']);
				unset($document['PersonId']);
			}	
			//if(!empty($kanoon))
			//	$document['Metadata']['KanoonId'] = $kanoon;
			try
			{
				$collection->save($document);
				//$_return['_id']= $document['_id']->{'$id'};
				//$_return['Roles'] = Rasta_Util_DotNotation::GetValue($document, "OrganizationInfos.Roles");
				return $_return;
			}catch (Exception $e){
				return -20;
				die('Error: ' . $e->getMessage());
			}
		}
		protected function IsUserLogin()
		{
			$user = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginUser");
			$user['role'] = 'kanoon';
			if(isset($user['Email'])) return $user;
			
			$user = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginStaff");
			$user['role'] = 'staff';
			if(isset($user['Email'])) return $user;
			
			return false;
		}




		protected function RegisterUser()
		{
			if(!isset($_POST['user']['Password'])) return -1;
			if(strlen($_POST['user']['Password'])<6 ) return -2;
			if(!preg_match("/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/", $_POST['user']['Email'])) return -3;
			if(!preg_match("/^09\d{9}$/", $_POST['user']['CellPhone']) ) return -4;
			
			date_default_timezone_set('Asia/Tehran');
			$collection = $this->MDB->users;
			$document = $_POST['user'];
			$document['Password'] = md5($document['Password']);
			$document['RegisterDate'] = date('Y/m/d H:i:s a', time());
			if(isset($document['_id']))
				if(strlen($document['_id'])<10)	unset($document['_id']);
			try
			{
				$collection->save($document);
				return array('_id'=>$document['_id'], 'email'=>$document['Email']);
			}catch (Exception $e){
				die('Error: ' . $e->getMessage());
			}
		}
		
		
		
		protected function DbDiconnect()
		{
			$this->connection->close();
			$this->MDB=NULL;
		}
		protected function DbConnect()
		{
			try {
					$dbhost = 'localhost';
			        $dbname = 'ershad_tehran';
					$options = array(
					        'db'=>"ershad_tehran",
					        'username'=>"tehran_ershad_root",
					        'password'=>'n$d1H6&5f0'
					);
					$this->connection = new Mongo("mongodb://$dbhost", $options);
					return $this->connection->$dbname;
					
				}catch (MongoConnectionException $e){
					die('Error connecting to MongoDB server');
				}catch (MongoException $e){
					die('Error: ' . $e->getMessage());
				}
		}
		
		protected function	MultiFileUploader($destinations, $valids)
		{
			if(!is_array($_FILES))	return false;
			foreach($destinations as $key=>$path)
			{
				if(!isset($_FILES[$key])) continue;
				if($_FILES[$key]["error"]!= UPLOAD_ERR_OK) continue;
				$error[$key] = array();
				$valid_size =(isset($valids[$key]))?$valids[$key]['size']:((isset($valids['size']))?$valids['size']:0);
				if ($valid_size>0 and $_FILES[$key]["size"] > $valid_size)		$error[$key][] = "File Size Exceeded";
				$valid_typs =(isset($valids[$key]))?$valids[$key]['types']:((isset($valids['types']))?$valids['types']:array());
				if(count($valid_typs)>0 and !in_array(strtolower($_FILES[$key]['type']), $valid_typs)) $error[$key][] = "Invalid File Type";
				if(count($error)>0) continue;
				$file_rand_name = rand(0, 9999999999) . strtolower( substr($_FILES[$key]['name'], strrpos($_FILES[$key]['name'], '.')) );
				$destinations[$key] = '';
				if(move_uploaded_file($_FILES[$key]['tmp_name'], $path.$file_rand_name )) $destinations[$key] = $path.$file_rand_name;
			}
			return array('destinations'=>$destinations, 'errors'=>$error);
		}
	
	
	
	
	
	
	
	
	
	
		
		protected function	_regUser($argus)
		{			
			try {
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$db = $connection->$dbname;
				$collection = $db->kanoons;
				date_default_timezone_set('Asia/Tehran');
				$submitDate = date('Y/m/d H:i:s a', time());
				$email = $_POST['email'];
				$phoneNumber = $_POST['phoneNumber'];
		        $formStep = 0 ;
		        $formNextStep = "/page/14";
				$exteraCount = 0 ;
				$document = array(
					'submitDatePrimery' => $submitDate,
					'email' => $email,
					'phoneNumber' => $phoneNumber,
					'officeStep' => $officeStep,
					'formStep' => $formStep,
					'formNextStep' => $formNextStep,
					'exteraCount' => $exteraCount
				);
				$collection->insert($document);
				$doc_id = $document['_id'];
				$cursor = $collection->find(
				        array(
				            '_id' => new MongoId($doc_id)
				        )
				    );
				foreach ($cursor as $obj) {
					$email = $obj['email'];
					$phoneNumber = $obj['phoneNumber'];
					$officeStep = $obj['officeStep'];
					$formStep = $obj['formStep'];
					$formNextStep = $obj['formNextStep'];
				}
				echo '{ "email": "' . $email . '" , "phoneNumber": "' . $phoneNumber . '" ,"registerId": "' . $doc_id . '" , "officeStep": "' . $obj['officeStep'] . '" , "formStep": "' . $formStep . '" , "formNextStep": "' . $formNextStep . '" }';
				$connection->close();
			}catch (MongoConnectionException $e){
				die('Error connecting to MongoDB server');
			}catch (MongoException $e){
				die('Error: ' . $e->getMessage());
			}
			
		}
		protected function	_regProposal($argus)
		{		
			try {
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$doc_id = $_POST['registerationId'];
				date_default_timezone_set('Asia/Tehran');
				$submitDate = date('Y/m/d H:i:s a', time());
				$firstName = $_POST['firstName'];
		        $lastName= $_POST['lastName'] ;
		        $lastLastName = $_POST['lastLastName'];
		        $aliasName = $_POST['aliasName'];
		        $fatherName = $_POST['fatherName'] ;
		        $birthPlace = $_POST['birthPlace']; 
		        $day = $_POST['day'];
		        $month = $_POST['month'];
		        $year = $_POST['year'];
		        $birthCertificate = $_POST['birthCertificate'];
		        $birthCertificatePlace = $_POST['birthCertificatePlace']; 
		        $nationalId = $_POST['nationalId'] ;
		        $religion = $_POST['religion'];
		        $denomination = $_POST['denomination'];
		        $nationalityStatus = $_POST['nationalityStatus']; 
		        $nationality = $_POST['nationality']; 
		        $gender = $_POST['gender'] ;
		        $mariageStatus = $_POST['mariageStatus'] ;
		        $mariage = $_POST['mariage'] ;
		        $military = $_POST['military'];
		        $isaargari = $_POST['isaargari'] ;
		        $shahidId = $_POST['shahidId'] ;
		        $passportStatus = $_POST['passportStatus']; 
		        $passportId = $_POST['passportId']; 
		        $emergencyCall = $_POST['emergencyCall']; 
		        $homePhone = $_POST['homePhone']; 
		        $address = $_POST['address'];
		        $postalCode = $_POST['postalCode'] ;
		        $managerPersonalImage = $_POST['managerPersonalImage'];
		        $isaargariImage = $_POST['isaargariImage'];
		        $firstFriendsName = $_POST['firstFriendsName'];
		        $firstFriendsJob = $_POST['firstFriendsJob'];
		        $firstFriendsRelationship = $_POST['firstFriendsRelationship'];
		        $firstFriendsRelationshipDuration = $_POST['firstFriendsRelationshipDuration'];
		        $firstFriendsPhone = $_POST['firstFriendsPhone'];
		        $firstFriendsAddress = $_POST['firstFriendsAddress'];
		        $secondtFriendsName = $_POST['secondtFriendsName'];
		        $secondtFriendsJob = $_POST['secondtFriendsJob'];
		        $secondtFriendsRelationship = $_POST['secondtFriendsRelationship'];
		        $secondtFriendsRelationshipDuration = $_POST['secondtFriendsRelationshipDuration'];
		        $secondFriendsPhone = $_POST['secondFriendsPhone'];
		        $secondFriendsAddress = $_POST['secondFriendsAddress'];
		        $thirdFriendsName = $_POST['thirdFriendsName'];
		        $thirdFriendsJob = $_POST['thirdFriendsJob'];
		        $thirdFriendsRelationship = $_POST['thirdFriendsRelationship'];
		        $thirdFriendsRelationshipDuration = $_POST['thirdFriendsRelationshipDuration'];
		        $thirdFriendsPhone = $_POST['thirdFriendsPhone'];
		        $thirdFriendsAddress = $_POST['thirdFriendsAddress'];
		        $educationField = $_POST['educationField'];
		        $educationDate = $_POST['educationDate'];
		        $educationPlace = $_POST['educationPlace'];
		        $firstUniversityLocation = $_POST['firstUniversityLocation'];
		        $firstAcademicDegree = $_POST['firstAcademicDegree'];
		        $secondUniversityLocation = $_POST['secondUniversityLocation'];
		        $secondAcademicDegree = $_POST['secondAcademicDegree'];
		        $otherCertificates = $_POST['otherCertificates'];
		        $currentJob = $_POST['currentJob'];
		        $jobHistory = $_POST['jobHistory'];
		        $briefJobExperience = $_POST['briefJobExperience'];
		        $workAddress = $_POST['workAddress'];
		        $workPostalCode = $_POST['workPostalCode'];
		        $workPhoneNumber = $_POST['workPhoneNumber'];
		        $assuranceNumber = $_POST['assuranceNumber'];
		        $firstLicenseType = $_POST['firstLicenseType'];
		        $firstIssueDate = $_POST['firstIssueDate'];
		        $firstValidityPeriod = $_POST['firstValidityPeriod'];
		        $firstLicenseNumber = $_POST['firstLicenseNumber'];
		        $secondLicenseType = $_POST['secondLicenseType'];
		        $secondIssueDate = $_POST['secondIssueDate'];
		        $secondValidityPeriod = $_POST['secondValidityPeriod'];
		        $secondLicenseNumber = $_POST['secondLicenseNumber'];
		        $thirdLicenseType = $_POST['thirdLicenseType'];
		        $thirdIssueDate = $_POST['thirdIssueDate'];
		        $thirdValidityPeriod = $_POST['thirdValidityPeriod'];
		        $thirdLicenseNumber = $_POST['thirdLicenseNumber'];
		        $firstActivityType = $_POST['firstActivityType'];
		        $firstPosition = $_POST['firstPosition'];
		        $firstOrganizationLocation = $_POST['firstOrganizationLocation'];
		        $firstMonthNumberActivity = $_POST['firstMonthNumberActivity'];
		        $firstFromYearToYear = $_POST['firstFromYearToYear'];
		        $firstLeaveReason = $_POST['firstLeaveReason'];
		        $secondActivityType = $_POST['secondActivityType'];
		        $secondPosition = $_POST['secondPosition'];
		        $secondOrganizationLocation = $_POST['secondOrganizationLocation'];
		        $secondMonthNumberActivity = $_POST['secondMonthNumberActivity'];
		        $secondFromYearToYear = $_POST['secondFromYearToYear'];
		        $secondLeaveReason = $_POST['secondLeaveReason'];
		        $thirdActivityType = $_POST['thirdActivityType'];
		        $thirdPosition = $_POST['thirdPosition'];
		        $thirdOrganizationLocation = $_POST['thirdOrganizationLocation'];
		        $thirdMonthNumberActivity = $_POST['thirdMonthNumberActivity'];
		        $thirdFromYearToYear = $_POST['thirdFromYearToYear'];
		        $thirdLeaveReason = $_POST['thirdLeaveReason'];
		        $fourthActivityType = $_POST['fourthActivityType'];
		        $fourthPosition = $_POST['fourthPosition'];
		        $fourthOrganizationLocation = $_POST['fourthOrganizationLocation'];
		        $fourthMonthNumberActivity = $_POST['fourthMonthNumberActivity'];
		        $fourthFromYearToYear = $_POST['fourthFromYearToYear'];
		        $fourthLeaveReason = $_POST['fourthLeaveReason'];
		        $fifthActivityType = $_POST['fifthActivityType'];
		        $fifthPosition = $_POST['fifthPosition'];
		        $fifthOrganizationLocation = $_POST['fifthOrganizationLocation'];
		        $fifthMonthNumberActivity = $_POST['fifthMonthNumberActivity'];
		        $fifthFromYearToYear = $_POST['fifthFromYearToYear'];
		        $fifthLeaveReason = $_POST['fifthLeaveReason'];
		        $firstArtActivityType = $_POST['firstArtActivityType'];
		        $firstArtActivityPosition = $_POST['firstArtActivityPosition'];
		        $firstArtActivityLocation = $_POST['firstArtActivityLocation'];
		        $secondArtActivityType = $_POST['secondArtActivityType'];
		        $secondArtActivityPosition = $_POST['secondArtActivityPosition'];
		        $secondArtActivityLocation = $_POST['secondArtActivityLocation'];
		        $thirdArtActivityType = $_POST['thirdArtActivityType'];
		        $thirdArtActivityPosition = $_POST['thirdArtActivityPosition'];
		        $thirdArtActivityLocation = $_POST['thirdArtActivityLocation'];
		        $culturalProducts = $_POST['culturalProducts'];
		        $acceptLaws = $_POST['acceptLaws'];
				$formStep = 1 ;
				$document = array(
					'submitDate' => $submitDate,
			        'firstName' => $firstName,
			        'lastName' => $lastName,
			        'lastLastName' => $lastLastName,
			        'aliasName' => $aliasName,
			        'fatherName' => $fatherName,
			        'birthPlace' => $birthPlace,
			        'birthDate' => array('day'=> $day ,
										'month'=> $month ,
										'year'=> $year
										),
			        'birthCertificate' => $birthCertificate ,
			        'birthCertificatePlace' => $birthCertificatePlace,
			        'nationalId' => $nationalId ,
			        'religion' => $religion ,
			        'denomination' => $denomination,
			        'nationalityStatus' => $nationalityStatus,
			        'nationality' => $nationality,
			        'gender' => $gender,
			        'mariageStatus' => $mariageStatus,
			        'mariage' => $mariage,
			        'military' => $military,
			        'isaargari' => $isaargari,
			        'shahidId' => $shahidId,
			        'passportStatus' => $passportStatus,
			        'passportId' => $passportId,
			        'emergencyCall' => $emergencyCall,
			        'homePhone' => $homePhone,
			        'address' => $address,
			        'postalCode' => $postalCode,
			        'managerPersonalImage' => $managerPersonalImage,
			        'isaargariImage' => $isaargariImage,
			        'firstFriends' => array(
											'Name'=> $firstFriendsName ,
											'job'=> $firstFriendsJob ,
											'relationship'=> $firstFriendsRelationship ,
											'relationshipDuration'=> $firstFriendsRelationshipDuration ,
											'phone'=> $firstFriendsPhone ,
											'address'=> $firstFriendsAddress 
											),
			        'secondtFriends' => array(
											'Name'=> $secondtFriendsName ,
											'job'=> $secondtFriendsJob ,
											'relationship'=> $secondtFriendsRelationship ,
											'relationshipDuration'=> $secondtFriendsRelationshipDuration ,
											'phone'=> $secondFriendsPhone ,
											'address'=> $secondFriendsAddress 
											),
			        'thirdFriends' => array(
											'Name'=> $thirdFriendsName ,
											'job'=> $thirdFriendsJob ,
											'relationship'=> $thirdFriendsRelationship ,
											'relationshipDuration'=> $thirdFriendsRelationshipDuration,
											'phone'=> $thirdFriendsPhone ,
											'address'=> $thirdFriendsAddress
											),
			        'educationalInfo' => array(
												'educationField'=> $educationField ,
												'educationDate'=> $educationDate ,
												'educationPlace'=> $educationPlace ,
												'firstUniversityLocation'=> $firstUniversityLocation ,
												'firstAcademicDegree'=> $firstAcademicDegree ,
												'secondUniversityLocation'=> $secondUniversityLocation ,
												'secondAcademicDegree'=> $secondAcademicDegree 
												),
			        'otherCertificates' => array(
												'firstLicenseType'=> $firstLicenseType,
												'firstIssueDate'=> $firstIssueDate,
												'firstValidityPeriod'=> $firstValidityPeriod,
												'firstLicenseNumber'=> $firstLicenseNumber,
												'secondLicenseType'=> $secondLicenseType,
												'secondIssueDate'=> $secondIssueDate,
												'secondValidityPeriod'=> $secondValidityPeriod,
												'secondLicenseNumber'=> $secondLicenseNumber,
												'thirdLicenseType'=> $thirdLicenseType,
												'thirdIssueDate'=> $thirdIssueDate,
												'thirdValidityPeriod'=> $thirdValidityPeriod,
												'thirdLicenseNumber'=> $thirdLicenseNumber
												),
			        'job' => array(
									'currentJob'=> $currentJob ,
									'jobHistory'=> $jobHistory ,
									'briefJobExperience'=> $briefJobExperience ,
									'workAddress'=> $workAddress ,
									'workPostalCode'=> $workPostalCode ,
									'workPhoneNumber'=> $workPhoneNumber ,
									'assuranceNumber'=> $assuranceNumber,
									'firstActivityType'=> $firstActivityType,
									'firstPosition'=> $firstPosition,
									'firstOrganizationLocation'=> $firstOrganizationLocation,
									'firstMonthNumberActivity'=> $firstMonthNumberActivity,
									'firstFromYearToYear'=> $firstFromYearToYear,
									'firstLeaveReason'=> $firstLeaveReason,
									'secondActivityType'=> $secondActivityType,
									'secondPosition'=> $secondPosition,
									'secondOrganizationLocation'=> $secondOrganizationLocation,
									'secondMonthNumberActivity'=> $secondMonthNumberActivity,
									'secondFromYearToYear'=> $secondFromYearToYear,
									'secondLeaveReason'=> $secondLeaveReason,
									'thirdActivityType'=> $thirdActivityType,
									'thirdPosition'=> $thirdPosition,
									'thirdOrganizationLocation'=> $thirdOrganizationLocation,
									'thirdMonthNumberActivity'=> $thirdMonthNumberActivity,
									'thirdFromYearToYear'=> $thirdFromYearToYear,
									'thirdLeaveReason'=> $thirdLeaveReason,
									'fourthActivityType'=> $fourthActivityType,
									'fourthPosition'=> $fourthPosition,
									'fourthOrganizationLocation'=> $fourthOrganizationLocation,
									'fourthMonthNumberActivity'=> $fourthMonthNumberActivity,
									'fourthFromYearToYear'=> $fourthFromYearToYear,
									'fourthLeaveReason'=> $fourthLeaveReason,
									'fifthActivityType'=> $fifthActivityType,
									'fifthPosition'=> $fifthPosition,
									'fifthOrganizationLocation'=> $fifthOrganizationLocation,
									'fifthMonthNumberActivity'=> $fifthMonthNumberActivity,
									'fifthFromYearToYear'=> $fifthFromYearToYear,
									'fifthLeaveReason'=> $fifthLeaveReason 
										),
				'culturalActivity' => array(
											'firstArtActivityType'=> $firstArtActivityType,
											'firstArtActivityPosition'=> $firstArtActivityPosition,
											'firstArtActivityLocation'=> $firstArtActivityLocation,
											'secondArtActivityType'=> $secondArtActivityType,
											'secondArtActivityPosition'=> $secondArtActivityPosition,
											'secondArtActivityLocation'=> $secondArtActivityLocation,
											'thirdArtActivityType'=> $thirdArtActivityType,
											'thirdArtActivityPosition'=> $thirdArtActivityPosition,
											'thirdArtActivityLocation'=> $thirdArtActivityLocation,
											'thirdIssueDate'=> $thirdIssueDate
												),
				'culturalProducts' => $culturalProducts,
				'acceptLaws' => $acceptLaws,
				'formStep' => $formStep 
								);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$collection = $connection->$dbname->kanoons;
				$mongoID = new MongoID($doc_id);
				$collection->update(
		            array("_id" => $mongoID),
		            array('$set' => $document),
		            array("upsert" => true)
				);
				$cursor = $collection->find(
			        array(
			            '_id' => $mongoID
			        )
			    );
			    foreach ($cursor as $obj){
					$formStep = $obj['formStep'];
					switch ($formStep){
					    case 0:
					        $formNextStep = "/page/14";
					        break;
					    case 1:
					        $formNextStep = "/page/15";
					        break;
					    case 2:
					        $formNextStep = "/page/19";
					        break;
					    case 3:
					        $formNextStep = "/page/16";
					        break;
					    case 4:
					        $formNextStep = "/page/17";
					        break;
					    default:
					        $formNextStep = "/page/11";
					}				
					echo '{ "formStep": "' . $obj['formStep'] . '" , "formNextStep": "' . $formNextStep . '" ,"registerId": "' . $obj['_id'] . '" , "firstName": "' . $obj['firstName'] . '" , "lastName": "' . $obj['lastName'] . '" , "officeStep": "' . $obj['officeStep'] . '" , "editPermission": "' . $obj['editPermission'] . '" , "personalImage": "' . $obj['personalImage'] . '" }';
				}
			$connection->close();
			}catch (MongoConnectionException $e){
				die('Error connecting to MongoDB server');
			}catch (MongoException $e){
				die('Error: ' . $e->getMessage());
			}
		}
		protected function	_regOwner($argus)
		{	
			try {
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$doc_id = $_POST['registerationId'];
				date_default_timezone_set('Asia/Tehran');
				$submitDate = date('Y/m/d H:i:s a', time());
				$firstName = $_POST['firstName'];
		        $lastName= $_POST['lastName'] ;
		        $lastLastName = $_POST['lastLastName'];
		        $aliasName = $_POST['aliasName'];
		        $fatherName = $_POST['fatherName'] ;
		        $birthPlace = $_POST['birthPlace']; 
		        $day = $_POST['day'];
		        $month = $_POST['month'];
		        $year = $_POST['year'];
		        $birthCertificate = $_POST['birthCertificate'];
		        $birthCertificatePlace = $_POST['birthCertificatePlace']; 
		        $nationalId = $_POST['nationalId'] ;
		        $religion = $_POST['religion'];
		        $denomination = $_POST['denomination'];
		        $nationalityStatus = $_POST['nationalityStatus']; 
		        $nationality = $_POST['nationality']; 
		        $gender = $_POST['gender'] ;
		        $mariageStatus = $_POST['mariageStatus'] ;
		        $mariage = $_POST['mariage'] ;
		        $military = $_POST['military'];
		        $isaargari = $_POST['isaargari'] ;
		        $shahidId = $_POST['shahidId'] ;
		        $passportStatus = $_POST['passportStatus']; 
		        $passportId = $_POST['passportId']; 
		        $emergencyCall = $_POST['emergencyCall']; 
		        $homePhone = $_POST['homePhone']; 
		        $address = $_POST['address'];
		        $postalCode = $_POST['postalCode'] ;
		        $managerPersonalImage = $_POST['managerPersonalImage'];
		        $isaargariImage = $_POST['isaargariImage'];
		        $firstFriendsName = $_POST['firstFriendsName'];
		        $firstFriendsJob = $_POST['firstFriendsJob'];
		        $firstFriendsRelationship = $_POST['firstFriendsRelationship'];
		        $firstFriendsRelationshipDuration = $_POST['firstFriendsRelationshipDuration'];
		        $firstFriendsPhone = $_POST['firstFriendsPhone'];
		        $firstFriendsAddress = $_POST['firstFriendsAddress'];
		        $secondtFriendsName = $_POST['secondtFriendsName'];
		        $secondtFriendsJob = $_POST['secondtFriendsJob'];
		        $secondtFriendsRelationship = $_POST['secondtFriendsRelationship'];
		        $secondtFriendsRelationshipDuration = $_POST['secondtFriendsRelationshipDuration'];
		        $secondFriendsPhone = $_POST['secondFriendsPhone'];
		        $secondFriendsAddress = $_POST['secondFriendsAddress'];
		        $thirdFriendsName = $_POST['thirdFriendsName'];
		        $thirdFriendsJob = $_POST['thirdFriendsJob'];
		        $thirdFriendsRelationship = $_POST['thirdFriendsRelationship'];
		        $thirdFriendsRelationshipDuration = $_POST['thirdFriendsRelationshipDuration'];
		        $thirdFriendsPhone = $_POST['thirdFriendsPhone'];
		        $thirdFriendsAddress = $_POST['thirdFriendsAddress'];
		        $educationField = $_POST['educationField'];
		        $educationDate = $_POST['educationDate'];
		        $educationPlace = $_POST['educationPlace'];
		        $firstUniversityLocation = $_POST['firstUniversityLocation'];
		        $firstAcademicDegree = $_POST['firstAcademicDegree'];
		        $secondUniversityLocation = $_POST['secondUniversityLocation'];
		        $secondAcademicDegree = $_POST['secondAcademicDegree'];
		        $otherCertificates = $_POST['otherCertificates'];
		        $currentJob = $_POST['currentJob'];
		        $jobHistory = $_POST['jobHistory'];
		        $briefJobExperience = $_POST['briefJobExperience'];
		        $workAddress = $_POST['workAddress'];
		        $workPostalCode = $_POST['workPostalCode'];
		        $workPhoneNumber = $_POST['workPhoneNumber'];
		        $assuranceNumber = $_POST['assuranceNumber'];
		        $firstLicenseType = $_POST['firstLicenseType'];
		        $firstIssueDate = $_POST['firstIssueDate'];
		        $firstValidityPeriod = $_POST['firstValidityPeriod'];
		        $firstLicenseNumber = $_POST['firstLicenseNumber'];
		        $secondLicenseType = $_POST['secondLicenseType'];
		        $secondIssueDate = $_POST['secondIssueDate'];
		        $secondValidityPeriod = $_POST['secondValidityPeriod'];
		        $secondLicenseNumber = $_POST['secondLicenseNumber'];
		        $thirdLicenseType = $_POST['thirdLicenseType'];
		        $thirdIssueDate = $_POST['thirdIssueDate'];
		        $thirdValidityPeriod = $_POST['thirdValidityPeriod'];
		        $thirdLicenseNumber = $_POST['thirdLicenseNumber'];
		        $firstActivityType = $_POST['firstActivityType'];
		        $firstPosition = $_POST['firstPosition'];
		        $firstOrganizationLocation = $_POST['firstOrganizationLocation'];
		        $firstMonthNumberActivity = $_POST['firstMonthNumberActivity'];
		        $firstFromYearToYear = $_POST['firstFromYearToYear'];
		        $firstLeaveReason = $_POST['firstLeaveReason'];
		        $secondActivityType = $_POST['secondActivityType'];
		        $secondPosition = $_POST['secondPosition'];
		        $secondOrganizationLocation = $_POST['secondOrganizationLocation'];
		        $secondMonthNumberActivity = $_POST['secondMonthNumberActivity'];
		        $secondFromYearToYear = $_POST['secondFromYearToYear'];
		        $secondLeaveReason = $_POST['secondLeaveReason'];
		        $thirdActivityType = $_POST['thirdActivityType'];
		        $thirdPosition = $_POST['thirdPosition'];
		        $thirdOrganizationLocation = $_POST['thirdOrganizationLocation'];
		        $thirdMonthNumberActivity = $_POST['thirdMonthNumberActivity'];
		        $thirdFromYearToYear = $_POST['thirdFromYearToYear'];
		        $thirdLeaveReason = $_POST['thirdLeaveReason'];
		        $fourthActivityType = $_POST['fourthActivityType'];
		        $fourthPosition = $_POST['fourthPosition'];
		        $fourthOrganizationLocation = $_POST['fourthOrganizationLocation'];
		        $fourthMonthNumberActivity = $_POST['fourthMonthNumberActivity'];
		        $fourthFromYearToYear = $_POST['fourthFromYearToYear'];
		        $fourthLeaveReason = $_POST['fourthLeaveReason'];
		        $fifthActivityType = $_POST['fifthActivityType'];
		        $fifthPosition = $_POST['fifthPosition'];
		        $fifthOrganizationLocation = $_POST['fifthOrganizationLocation'];
		        $fifthMonthNumberActivity = $_POST['fifthMonthNumberActivity'];
		        $fifthFromYearToYear = $_POST['fifthFromYearToYear'];
		        $fifthLeaveReason = $_POST['fifthLeaveReason'];
		        $firstArtActivityType = $_POST['firstArtActivityType'];
		        $firstArtActivityPosition = $_POST['firstArtActivityPosition'];
		        $firstArtActivityLocation = $_POST['firstArtActivityLocation'];
		        $secondArtActivityType = $_POST['secondArtActivityType'];
		        $secondArtActivityPosition = $_POST['secondArtActivityPosition'];
		        $secondArtActivityLocation = $_POST['secondArtActivityLocation'];
		        $thirdArtActivityType = $_POST['thirdArtActivityType'];
		        $thirdArtActivityPosition = $_POST['thirdArtActivityPosition'];
		        $thirdArtActivityLocation = $_POST['thirdArtActivityLocation'];
		        $culturalProducts = $_POST['culturalProducts'];
		        $acceptLaws = $_POST['acceptLaws'];
				$formStep = 2 ;
				$document = array(
					'owner' => array(
						'submitDate' => $submitDate,
				        'firstName' => $firstName,
				        'lastName' => $lastName,
				        'lastLastName' => $lastLastName,
				        'aliasName' => $aliasName,
				        'fatherName' => $fatherName,
				        'birthPlace' => $birthPlace,
				        'birthDate' => array('day'=> $day ,
											'month'=> $month ,
											'year'=> $year
											),
				        'birthCertificate' => $birthCertificate ,
				        'birthCertificatePlace' => $birthCertificatePlace,
				        'nationalId' => $nationalId ,
				        'religion' => $religion ,
				        'denomination' => $denomination,
				        'nationalityStatus' => $nationalityStatus,
				        'nationality' => $nationality,
				        'gender' => $gender,
				        'mariageStatus' => $mariageStatus,
				        'mariage' => $mariage,
				        'military' => $military,
				        'isaargari' => $isaargari,
				        'shahidId' => $shahidId,
				        'passportStatus' => $passportStatus,
				        'passportId' => $passportId,
				        'emergencyCall' => $emergencyCall,
				        'homePhone' => $homePhone,
				        'address' => $address,
				        'postalCode' => $postalCode,
				        'managerPersonalImage' => $managerPersonalImage,
				        'isaargariImage' => $isaargariImage,
				        'firstFriends' => array(
												'Name'=> $firstFriendsName ,
												'job'=> $firstFriendsJob ,
												'relationship'=> $firstFriendsRelationship ,
												'relationshipDuration'=> $firstFriendsRelationshipDuration ,
												'phone'=> $firstFriendsPhone ,
												'address'=> $firstFriendsAddress 
												),
				        'secondtFriends' => array(
												'Name'=> $secondtFriendsName ,
												'job'=> $secondtFriendsJob ,
												'relationship'=> $secondtFriendsRelationship ,
												'relationshipDuration'=> $secondtFriendsRelationshipDuration ,
												'phone'=> $secondFriendsPhone ,
												'address'=> $secondFriendsAddress 
												),
				        'thirdFriends' => array(
												'Name'=> $thirdFriendsName ,
												'job'=> $thirdFriendsJob ,
												'relationship'=> $thirdFriendsRelationship ,
												'relationshipDuration'=> $thirdFriendsRelationshipDuration,
												'phone'=> $thirdFriendsPhone ,
												'address'=> $thirdFriendsAddress
												),
				        'educationalInfo' => array(
													'educationField'=> $educationField ,
													'educationDate'=> $educationDate ,
													'educationPlace'=> $educationPlace ,
													'firstUniversityLocation'=> $firstUniversityLocation ,
													'firstAcademicDegree'=> $firstAcademicDegree ,
													'secondUniversityLocation'=> $secondUniversityLocation ,
													'secondAcademicDegree'=> $secondAcademicDegree 
													),
				        'otherCertificates' => array(
													'firstLicenseType'=> $firstLicenseType,
													'firstIssueDate'=> $firstIssueDate,
													'firstValidityPeriod'=> $firstValidityPeriod,
													'firstLicenseNumber'=> $firstLicenseNumber,
													'secondLicenseType'=> $secondLicenseType,
													'secondIssueDate'=> $secondIssueDate,
													'secondValidityPeriod'=> $secondValidityPeriod,
													'secondLicenseNumber'=> $secondLicenseNumber,
													'thirdLicenseType'=> $thirdLicenseType,
													'thirdIssueDate'=> $thirdIssueDate,
													'thirdValidityPeriod'=> $thirdValidityPeriod,
													'thirdLicenseNumber'=> $thirdLicenseNumber
													),
				        'job' => array(
										'currentJob'=> $currentJob ,
										'jobHistory'=> $jobHistory ,
										'briefJobExperience'=> $briefJobExperience ,
										'workAddress'=> $workAddress ,
										'workPostalCode'=> $workPostalCode ,
										'workPhoneNumber'=> $workPhoneNumber ,
										'assuranceNumber'=> $assuranceNumber,
										'firstActivityType'=> $firstActivityType,
										'firstPosition'=> $firstPosition,
										'firstOrganizationLocation'=> $firstOrganizationLocation,
										'firstMonthNumberActivity'=> $firstMonthNumberActivity,
										'firstFromYearToYear'=> $firstFromYearToYear,
										'firstLeaveReason'=> $firstLeaveReason,
										'secondActivityType'=> $secondActivityType,
										'secondPosition'=> $secondPosition,
										'secondOrganizationLocation'=> $secondOrganizationLocation,
										'secondMonthNumberActivity'=> $secondMonthNumberActivity,
										'secondFromYearToYear'=> $secondFromYearToYear,
										'secondLeaveReason'=> $secondLeaveReason,
										'thirdActivityType'=> $thirdActivityType,
										'thirdPosition'=> $thirdPosition,
										'thirdOrganizationLocation'=> $thirdOrganizationLocation,
										'thirdMonthNumberActivity'=> $thirdMonthNumberActivity,
										'thirdFromYearToYear'=> $thirdFromYearToYear,
										'thirdLeaveReason'=> $thirdLeaveReason,
										'fourthActivityType'=> $fourthActivityType,
										'fourthPosition'=> $fourthPosition,
										'fourthOrganizationLocation'=> $fourthOrganizationLocation,
										'fourthMonthNumberActivity'=> $fourthMonthNumberActivity,
										'fourthFromYearToYear'=> $fourthFromYearToYear,
										'fourthLeaveReason'=> $fourthLeaveReason,
										'fifthActivityType'=> $fifthActivityType,
										'fifthPosition'=> $fifthPosition,
										'fifthOrganizationLocation'=> $fifthOrganizationLocation,
										'fifthMonthNumberActivity'=> $fifthMonthNumberActivity,
										'fifthFromYearToYear'=> $fifthFromYearToYear,
										'fifthLeaveReason'=> $fifthLeaveReason 
											),
					'culturalActivity' => array(
												'firstArtActivityType'=> $firstArtActivityType,
												'firstArtActivityPosition'=> $firstArtActivityPosition,
												'firstArtActivityLocation'=> $firstArtActivityLocation,
												'secondArtActivityType'=> $secondArtActivityType,
												'secondArtActivityPosition'=> $secondArtActivityPosition,
												'secondArtActivityLocation'=> $secondArtActivityLocation,
												'thirdArtActivityType'=> $thirdArtActivityType,
												'thirdArtActivityPosition'=> $thirdArtActivityPosition,
												'thirdArtActivityLocation'=> $thirdArtActivityLocation,
												'thirdIssueDate'=> $thirdIssueDate
													),
					'culturalProducts' => $culturalProducts,
					'acceptLaws' => $acceptLaws,
					),
					'formStep' => $formStep
				);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$collection = $connection->$dbname->kanoons;
				$mongoID = new MongoID($doc_id);
				$collection->update(
		            array("_id" => $mongoID),
		            array('$set' => $document),
		            array("upsert" => true)
				);
				$cursor = $collection->find(
			        array(
			            '_id' => $mongoID
			        )
			    );
			    foreach ($cursor as $obj) {
					$formStep = $obj['formStep'];
					switch ($formStep) {
					    case 0:
					        $formNextStep = "/page/14";
					        break;
					    case 1:
					        $formNextStep = "/page/15";
					        break;
					    case 2:
					        $formNextStep = "/page/19";
					        break;
					    case 3:
					        $formNextStep = "/page/16";
					        break;
					    case 4:
					        $formNextStep = "/page/17";
					        break;
					    default:
					        $formNextStep = "/page/11";
					}				
					echo '{ "formStep": "' . $obj['formStep'] . '" , "formNextStep": "' . $formNextStep . '" ,"registerId": "' . $obj['_id'] . '" , "firstName": "' . $obj['firstName'] . '" , "lastName": "' . $obj['lastName'] . '" , "officeStep": "' . $obj['officeStep'] . '" , "editPermission": "' . $obj['editPermission'] . '" , "personalImage": "' . $obj['personalImage'] . '" }';
				}
				$connection->close();
			}catch (MongoConnectionException $e){
				die('Error connecting to MongoDB server');
			}catch (MongoException $e){
				die('Error: ' . $e->getMessage());
			}
		}	
		protected function	_regExtera($argus)
		{	
			try {
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$doc_id = $_POST['registerationId'];
				date_default_timezone_set('Asia/Tehran');
				$submitDate = date('Y/m/d H:i:s a', time());
				$firstName = $_POST['firstName'];
		        $lastName= $_POST['lastName'] ;
		        $lastLastName = $_POST['lastLastName'];
		        $aliasName = $_POST['aliasName'];
		        $fatherName = $_POST['fatherName'] ;
		        $birthPlace = $_POST['birthPlace']; 
		        $day = $_POST['day'];
		        $month = $_POST['month'];
		        $year = $_POST['year'];
		        $birthCertificate = $_POST['birthCertificate'];
		        $birthCertificatePlace = $_POST['birthCertificatePlace']; 
		        $nationalId = $_POST['nationalId'] ;
		        $religion = $_POST['religion'];
		        $denomination = $_POST['denomination'];
		        $nationalityStatus = $_POST['nationalityStatus']; 
		        $nationality = $_POST['nationality']; 
		        $gender = $_POST['gender'] ;
		        $mariageStatus = $_POST['mariageStatus'] ;
		        $mariage = $_POST['mariage'] ;
		        $military = $_POST['military'];
		        $isaargari = $_POST['isaargari'] ;
		        $shahidId = $_POST['shahidId'] ;
		        $passportStatus = $_POST['passportStatus']; 
		        $passportId = $_POST['passportId']; 
		        $emergencyCall = $_POST['emergencyCall']; 
		        $homePhone = $_POST['homePhone']; 
		        $address = $_POST['address'];
		        $postalCode = $_POST['postalCode'] ;
		        $managerPersonalImage = $_POST['managerPersonalImage'];
		        $isaargariImage = $_POST['isaargariImage'];
		        $firstFriendsName = $_POST['firstFriendsName'];
		        $firstFriendsJob = $_POST['firstFriendsJob'];
		        $firstFriendsRelationship = $_POST['firstFriendsRelationship'];
		        $firstFriendsRelationshipDuration = $_POST['firstFriendsRelationshipDuration'];
		        $firstFriendsPhone = $_POST['firstFriendsPhone'];
		        $firstFriendsAddress = $_POST['firstFriendsAddress'];
		        $secondtFriendsName = $_POST['secondtFriendsName'];
		        $secondtFriendsJob = $_POST['secondtFriendsJob'];
		        $secondtFriendsRelationship = $_POST['secondtFriendsRelationship'];
		        $secondtFriendsRelationshipDuration = $_POST['secondtFriendsRelationshipDuration'];
		        $secondFriendsPhone = $_POST['secondFriendsPhone'];
		        $secondFriendsAddress = $_POST['secondFriendsAddress'];
		        $thirdFriendsName = $_POST['thirdFriendsName'];
		        $thirdFriendsJob = $_POST['thirdFriendsJob'];
		        $thirdFriendsRelationship = $_POST['thirdFriendsRelationship'];
		        $thirdFriendsRelationshipDuration = $_POST['thirdFriendsRelationshipDuration'];
		        $thirdFriendsPhone = $_POST['thirdFriendsPhone'];
		        $thirdFriendsAddress = $_POST['thirdFriendsAddress'];
		        $educationField = $_POST['educationField'];
		        $educationDate = $_POST['educationDate'];
		        $educationPlace = $_POST['educationPlace'];
		        $firstUniversityLocation = $_POST['firstUniversityLocation'];
		        $firstAcademicDegree = $_POST['firstAcademicDegree'];
		        $secondUniversityLocation = $_POST['secondUniversityLocation'];
		        $secondAcademicDegree = $_POST['secondAcademicDegree'];
		        $otherCertificates = $_POST['otherCertificates'];
		        $currentJob = $_POST['currentJob'];
		        $jobHistory = $_POST['jobHistory'];
		        $briefJobExperience = $_POST['briefJobExperience'];
		        $workAddress = $_POST['workAddress'];
		        $workPostalCode = $_POST['workPostalCode'];
		        $workPhoneNumber = $_POST['workPhoneNumber'];
		        $assuranceNumber = $_POST['assuranceNumber'];
		        $firstLicenseType = $_POST['firstLicenseType'];
		        $firstIssueDate = $_POST['firstIssueDate'];
		        $firstValidityPeriod = $_POST['firstValidityPeriod'];
		        $firstLicenseNumber = $_POST['firstLicenseNumber'];
		        $secondLicenseType = $_POST['secondLicenseType'];
		        $secondIssueDate = $_POST['secondIssueDate'];
		        $secondValidityPeriod = $_POST['secondValidityPeriod'];
		        $secondLicenseNumber = $_POST['secondLicenseNumber'];
		        $thirdLicenseType = $_POST['thirdLicenseType'];
		        $thirdIssueDate = $_POST['thirdIssueDate'];
		        $thirdValidityPeriod = $_POST['thirdValidityPeriod'];
		        $thirdLicenseNumber = $_POST['thirdLicenseNumber'];
		        $firstActivityType = $_POST['firstActivityType'];
		        $firstPosition = $_POST['firstPosition'];
		        $firstOrganizationLocation = $_POST['firstOrganizationLocation'];
		        $firstMonthNumberActivity = $_POST['firstMonthNumberActivity'];
		        $firstFromYearToYear = $_POST['firstFromYearToYear'];
		        $firstLeaveReason = $_POST['firstLeaveReason'];
		        $secondActivityType = $_POST['secondActivityType'];
		        $secondPosition = $_POST['secondPosition'];
		        $secondOrganizationLocation = $_POST['secondOrganizationLocation'];
		        $secondMonthNumberActivity = $_POST['secondMonthNumberActivity'];
		        $secondFromYearToYear = $_POST['secondFromYearToYear'];
		        $secondLeaveReason = $_POST['secondLeaveReason'];
		        $thirdActivityType = $_POST['thirdActivityType'];
		        $thirdPosition = $_POST['thirdPosition'];
		        $thirdOrganizationLocation = $_POST['thirdOrganizationLocation'];
		        $thirdMonthNumberActivity = $_POST['thirdMonthNumberActivity'];
		        $thirdFromYearToYear = $_POST['thirdFromYearToYear'];
		        $thirdLeaveReason = $_POST['thirdLeaveReason'];
		        $fourthActivityType = $_POST['fourthActivityType'];
		        $fourthPosition = $_POST['fourthPosition'];
		        $fourthOrganizationLocation = $_POST['fourthOrganizationLocation'];
		        $fourthMonthNumberActivity = $_POST['fourthMonthNumberActivity'];
		        $fourthFromYearToYear = $_POST['fourthFromYearToYear'];
		        $fourthLeaveReason = $_POST['fourthLeaveReason'];
		        $fifthActivityType = $_POST['fifthActivityType'];
		        $fifthPosition = $_POST['fifthPosition'];
		        $fifthOrganizationLocation = $_POST['fifthOrganizationLocation'];
		        $fifthMonthNumberActivity = $_POST['fifthMonthNumberActivity'];
		        $fifthFromYearToYear = $_POST['fifthFromYearToYear'];
		        $fifthLeaveReason = $_POST['fifthLeaveReason'];
		        $firstArtActivityType = $_POST['firstArtActivityType'];
		        $firstArtActivityPosition = $_POST['firstArtActivityPosition'];
		        $firstArtActivityLocation = $_POST['firstArtActivityLocation'];
		        $secondArtActivityType = $_POST['secondArtActivityType'];
		        $secondArtActivityPosition = $_POST['secondArtActivityPosition'];
		        $secondArtActivityLocation = $_POST['secondArtActivityLocation'];
		        $thirdArtActivityType = $_POST['thirdArtActivityType'];
		        $thirdArtActivityPosition = $_POST['thirdArtActivityPosition'];
		        $thirdArtActivityLocation = $_POST['thirdArtActivityLocation'];
		        $culturalProducts = $_POST['culturalProducts'];
		        $acceptLaws = $_POST['acceptLaws'];
				$formStep = 3 ;
				$exteraPersonDataContent = array(
					'submitDate' => $submitDate,
			        'firstName' => $firstName,
			        'lastName' => $lastName,
			        'lastLastName' => $lastLastName,
			        'aliasName' => $aliasName,
			        'fatherName' => $fatherName,
			        'birthPlace' => $birthPlace,
			        'birthDate' => array('day'=> $day ,
										'month'=> $month ,
										'year'=> $year
										),
			        'birthCertificate' => $birthCertificate ,
			        'birthCertificatePlace' => $birthCertificatePlace,
			        'nationalId' => $nationalId ,
			        'religion' => $religion ,
			        'denomination' => $denomination,
			        'nationalityStatus' => $nationalityStatus,
			        'nationality' => $nationality,
			        'gender' => $gender,
			        'mariageStatus' => $mariageStatus,
			        'mariage' => $mariage,
			        'military' => $military,
			        'isaargari' => $isaargari,
			        'shahidId' => $shahidId,
			        'passportStatus' => $passportStatus,
			        'passportId' => $passportId,
			        'emergencyCall' => $emergencyCall,
			        'homePhone' => $homePhone,
			        'address' => $address,
			        'postalCode' => $postalCode,
			        'managerPersonalImage' => $managerPersonalImage,
			        'isaargariImage' => $isaargariImage,
			        'firstFriends' => array(
											'Name'=> $firstFriendsName ,
											'job'=> $firstFriendsJob ,
											'relationship'=> $firstFriendsRelationship ,
											'relationshipDuration'=> $firstFriendsRelationshipDuration ,
											'phone'=> $firstFriendsPhone ,
											'address'=> $firstFriendsAddress 
											),
			        'secondtFriends' => array(
											'Name'=> $secondtFriendsName ,
											'job'=> $secondtFriendsJob ,
											'relationship'=> $secondtFriendsRelationship ,
											'relationshipDuration'=> $secondtFriendsRelationshipDuration ,
											'phone'=> $secondFriendsPhone ,
											'address'=> $secondFriendsAddress 
											),
			        'thirdFriends' => array(
											'Name'=> $thirdFriendsName ,
											'job'=> $thirdFriendsJob ,
											'relationship'=> $thirdFriendsRelationship ,
											'relationshipDuration'=> $thirdFriendsRelationshipDuration,
											'phone'=> $thirdFriendsPhone ,
											'address'=> $thirdFriendsAddress
											),
			        'educationalInfo' => array(
												'educationField'=> $educationField ,
												'educationDate'=> $educationDate ,
												'educationPlace'=> $educationPlace ,
												'firstUniversityLocation'=> $firstUniversityLocation ,
												'firstAcademicDegree'=> $firstAcademicDegree ,
												'secondUniversityLocation'=> $secondUniversityLocation ,
												'secondAcademicDegree'=> $secondAcademicDegree 
												),
			        'otherCertificates' => array(
												'firstLicenseType'=> $firstLicenseType,
												'firstIssueDate'=> $firstIssueDate,
												'firstValidityPeriod'=> $firstValidityPeriod,
												'firstLicenseNumber'=> $firstLicenseNumber,
												'secondLicenseType'=> $secondLicenseType,
												'secondIssueDate'=> $secondIssueDate,
												'secondValidityPeriod'=> $secondValidityPeriod,
												'secondLicenseNumber'=> $secondLicenseNumber,
												'thirdLicenseType'=> $thirdLicenseType,
												'thirdIssueDate'=> $thirdIssueDate,
												'thirdValidityPeriod'=> $thirdValidityPeriod,
												'thirdLicenseNumber'=> $thirdLicenseNumber
												),
			        'job' => array(
									'currentJob'=> $currentJob ,
									'jobHistory'=> $jobHistory ,
									'briefJobExperience'=> $briefJobExperience ,
									'workAddress'=> $workAddress ,
									'workPostalCode'=> $workPostalCode ,
									'workPhoneNumber'=> $workPhoneNumber ,
									'assuranceNumber'=> $assuranceNumber,
									'firstActivityType'=> $firstActivityType,
									'firstPosition'=> $firstPosition,
									'firstOrganizationLocation'=> $firstOrganizationLocation,
									'firstMonthNumberActivity'=> $firstMonthNumberActivity,
									'firstFromYearToYear'=> $firstFromYearToYear,
									'firstLeaveReason'=> $firstLeaveReason,
									'secondActivityType'=> $secondActivityType,
									'secondPosition'=> $secondPosition,
									'secondOrganizationLocation'=> $secondOrganizationLocation,
									'secondMonthNumberActivity'=> $secondMonthNumberActivity,
									'secondFromYearToYear'=> $secondFromYearToYear,
									'secondLeaveReason'=> $secondLeaveReason,
									'thirdActivityType'=> $thirdActivityType,
									'thirdPosition'=> $thirdPosition,
									'thirdOrganizationLocation'=> $thirdOrganizationLocation,
									'thirdMonthNumberActivity'=> $thirdMonthNumberActivity,
									'thirdFromYearToYear'=> $thirdFromYearToYear,
									'thirdLeaveReason'=> $thirdLeaveReason,
									'fourthActivityType'=> $fourthActivityType,
									'fourthPosition'=> $fourthPosition,
									'fourthOrganizationLocation'=> $fourthOrganizationLocation,
									'fourthMonthNumberActivity'=> $fourthMonthNumberActivity,
									'fourthFromYearToYear'=> $fourthFromYearToYear,
									'fourthLeaveReason'=> $fourthLeaveReason,
									'fifthActivityType'=> $fifthActivityType,
									'fifthPosition'=> $fifthPosition,
									'fifthOrganizationLocation'=> $fifthOrganizationLocation,
									'fifthMonthNumberActivity'=> $fifthMonthNumberActivity,
									'fifthFromYearToYear'=> $fifthFromYearToYear,
									'fifthLeaveReason'=> $fifthLeaveReason 
										),
				'culturalActivity' => array(
											'firstArtActivityType'=> $firstArtActivityType,
											'firstArtActivityPosition'=> $firstArtActivityPosition,
											'firstArtActivityLocation'=> $firstArtActivityLocation,
											'secondArtActivityType'=> $secondArtActivityType,
											'secondArtActivityPosition'=> $secondArtActivityPosition,
											'secondArtActivityLocation'=> $secondArtActivityLocation,
											'thirdArtActivityType'=> $thirdArtActivityType,
											'thirdArtActivityPosition'=> $thirdArtActivityPosition,
											'thirdArtActivityLocation'=> $thirdArtActivityLocation,
											'thirdIssueDate'=> $thirdIssueDate
												),
				'culturalProducts' => $culturalProducts,
				'acceptLaws' => $acceptLaws,
				'formStep' => $formStep 
				);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$collection = $connection->$dbname->kanoons;
				$mongoID = new MongoID($doc_id);
				$collection->update(
		            array("_id" => $mongoID),
		            array('$set' => $document),
		            array("upsert" => true)
				);
				$preCheck = $collection->find(
			        array(
			            '_id' => $mongoID
			        )
			    );
			    foreach ($preCheck as $obj) {
					$exteraCount = $obj['exteraCount'];
					$newExteraCount = $exteraCount + 1 ;
					if( $exteraCount == 0 ){
						$exteraPersonName = "exteraPerson1" ;
					}elseif( $exteraCount > 0 ){
						$exteraPersonName = "exteraPerson".$newExteraCount ;
						}else{
							die("a problem was accure");
							}
				}
				$collection->update(
		            array("_id" => $mongoID),
		            array('$set' => array('formStep' => $formStep,'exteraCount' => $newExteraCount, $exteraPersonName => $exteraPersonDataContent)),
		            array("upsert" => false)
				);
				foreach ($preCheck as $obj) {
					$formStep = $obj['formStep'];
					switch ($formStep) {
					    case 0:
					        $formNextStep = "/page/14";
					        break;
					    case 1:
					        $formNextStep = "/page/15";
					        break;
					    case 2:
					        $formNextStep = "/page/19";
					        break;
					    case 3:
					        $formNextStep = "/page/16";
					        break;
					    case 4:
					        $formNextStep = "/page/17";
					        break;
					    default:
					        $formNextStep = "/page/11";
					}
					echo '{ 
							"formStep": "' . $obj['formStep'] . '" , 
							"formNextStep": "' . $formNextStep . '" ,
							"registerId": "' . $obj['_id'] . '" , 
							"firstName": "' . $obj['firstName'] . '" , 
							"lastName": "' . $obj['lastName'] . '" ,  
							"managerPersonalImage": "' . $obj['managerPersonalImage'] . '" ,
							"extaraCount":"'.$obj['exteraCount'].'",
							"exteraPerson1FirstName":"'.$obj['exteraPerson1']['firstName'].'", 
							"exteraPerson1LastName":"'.$obj['exteraPerson1']['lastName'].'", 
							"exteraPerson1PersonalImage":"'.$obj['exteraPerson1']['managerPersonalImage'].'", 
							"exteraPerson2FirstName":"'.$obj['exteraPerson2']['firstName'].'", 
							"exteraPerson2LastName":"'.$obj['exteraPerson2']['lastName'].'", 
							"exteraPerson2PersonalImage":"'.$obj['exteraPerson2']['managerPersonalImage'].'", 
							"exteraPerson3FirstName":"'.$obj['exteraPerson3']['firstName'].'", 
							"exteraPerson3LastName":"'.$obj['exteraPerson3']['lastName'].'", 
							"exteraPerson3PersonalImage":"'.$obj['exteraPerson3']['managerPersonalImage'].'", 
							"exteraPerson4FirstName":"'.$obj['exteraPerson4']['firstName'].'", 
							"exteraPerson4LastName":"'.$obj['exteraPerson4']['lastName'].'", 
							"exteraPerson4PersonalImage":"'.$obj['exteraPerson4']['managerPersonalImage'].'", 
							"exteraPerson5FirstName":"'.$obj['exteraPerson5']['firstName'].'", 
							"exteraPerson5LastName":"'.$obj['exteraPerson5']['lastName'].'", 
							"exteraPerson5PersonalImage":"'.$obj['exteraPerson5']['managerPersonalImage'].'", 
							"exteraPerson6FirstName":"'.$obj['exteraPerson6']['firstName'].'", 
							"exteraPerson6LastName":"'.$obj['exteraPerson6']['lastName'].'", 
							"exteraPerson6PersonalImage":"'.$obj['exteraPerson6']['managerPersonalImage'].'", 
							"exteraPerson7FirstName":"'.$obj['exteraPerson7']['firstName'].'", 
							"exteraPerson7LastName":"'.$obj['exteraPerson7']['lastName'].'", 
							"exteraPerson7PersonalImage":"'.$obj['exteraPerson7']['managerPersonalImage'].'", 
							"exteraPerson8FirstName":"'.$obj['exteraPerson8']['firstName'].'", 
							"exteraPerson8LastName":"'.$obj['exteraPerson8']['lastName'].'", 
							"exteraPerson8PersonalImage":"'.$obj['exteraPerson8']['managerPersonalImage'].'", 
							"exteraPerson9FirstName":"'.$obj['exteraPerson9']['firstName'].'", 
							"exteraPerson9LastName":"'.$obj['exteraPerson9']['lastName'].'", 
							"exteraPerson9PersonalImage":"'.$obj['exteraPerson9']['managerPersonalImage'].'", 
							"exteraPerson10FirstName":"'.$obj['exteraPerson10']['firstName'].'", 
							"exteraPerson10LastName":"'.$obj['exteraPerson10']['lastName'].'", 
							"exteraPerson10PersonalImage":"'.$obj['exteraPerson10']['managerPersonalImage'].'" 
					}';
				}
				
				
				$connection->close();
				}catch(MongoConnectionException $e){
					die('Error connecting to MongoDB server');
				}catch(MongoException $e){
					die('Error: ' . $e->getMessage());
				}
		}
		protected function	_regOffice($argus)
		{	
			try {
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$doc_id = $_POST['registerationId'];
				date_default_timezone_set('Asia/Tehran');
				$submitDate = date('Y/m/d H:i:s a', time());
		        $activityDomains = $_POST['activityDomains'];
		        $firstRecommendedName = $_POST['firstRecommendedName'];
		        $firstRecommendedNameMeaning = $_POST['firstRecommendedNameMeaning'];
		        $secondRecommendedName = $_POST['secondRecommendedName'];
		        $secondRecommendedNameMeaning = $_POST['secondRecommendedNameMeaning'];
		        $thirdRecommendedName = $_POST['thirdRecommendedName'];
		        $thirdRecommendedNameMeaning = $_POST['thirdRecommendedNameMeaning'];
		        $fourthRecommendedName = $_POST['fourthRecommendedName'];
		        $fourthRecommendedNameMeaning = $_POST['fourthRecommendedNameMeaning'];
		        $fifthRecommendedName = $_POST['fifthRecommendedName'];
		        $fifthRecommendedNameMeaning = $_POST['fifthRecommendedNameMeaning'];
				$formStep = 4 ;
				$document = array(
			        'officeInfos' => array(
											'submitDate' => $submitDate,
											'activityDomains' => $activityDomains,
									        'recommendedNames' => array(
												'firstRecommendedName'=> $firstRecommendedName ,
												'firstRecommendedNameMeaning'=> $firstRecommendedNameMeaning ,
												'secondRecommendedName'=> $secondRecommendedName ,
												'secondRecommendedNameMeaning'=> $secondRecommendedNameMeaning ,
												'thirdRecommendedName'=> $thirdRecommendedName ,
												'thirdRecommendedNameMeaning'=> $thirdRecommendedNameMeaning ,
												'fourthRecommendedName'=> $fourthRecommendedName ,
												'fourthRecommendedNameMeaning'=> $fourthRecommendedNameMeaning ,
												'fifthRecommendedName'=> $fifthRecommendedName ,
												'fifthRecommendedNameMeaning'=> $fifthRecommendedNameMeaning
												)
											),
					'formStep' => $formStep 
				);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$collection = $connection->$dbname->kanoons;
				$mongoID = new MongoID($doc_id);
				$collection->update(
		            array("_id" => $mongoID),
		            array('$set' => $document),
		            array("upsert" => true)
				);
				$cursor = $collection->find(
			        array(
			            '_id' => $mongoID
			        )
			    );
			    foreach ($cursor as $obj){
					$formStep = $obj['formStep'];
					switch ($formStep){
					    case 0:
					        $formNextStep = "/page/14";
					        break;
					    case 1:
					        $formNextStep = "/page/15";
					        break;
					    case 2:
					        $formNextStep = "/page/19";
					        break;
					    case 3:
					        $formNextStep = "/page/16";
					        break;
					    case 4:
					        $formNextStep = "/page/17";
					        break;
					    default:
					        $formNextStep = "/page/11";
					}				
					echo '{ "formStep": "' . $obj['formStep'] . '" , "formNextStep": "' . $formNextStep . '" ,"registerId": "' . $obj['_id'] . '" , "firstName": "' . $obj['firstName'] . '" , "lastName": "' . $obj['lastName'] . '" , "officeStep": "' . $obj['officeStep'] . '" , "editPermission": "' . $obj['editPermission'] . '" , "personalImage": "' . $obj['personalImage'] . '" }';
				}
				
				$connection->close();
				}catch(MongoConnectionException $e){
					die('Error connecting to MongoDB server');
				}catch(MongoException $e){
					die('Error: ' . $e->getMessage());
				}
		}
		protected function	_regScans($argus)
		{	
			try {
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$doc_id = $_POST['registerationId'];
				date_default_timezone_set('Asia/Tehran');
				$submitDate = date('Y/m/d H:i:s a', time());
		        $certFirstImage = $_POST['certFirstImage'];
		        $certSecondImage = $_POST['certSecondImage'];
		        $certThirdImage = $_POST['certThirdImage'];
		        $cardFirstImage = $_POST['cardFirstImage'];
		        $cardSecondImage = $_POST['cardSecondImage'];
		        $solderingImageForw = $_POST['solderingImageForw'];
		        $solderingImageBack = $_POST['solderingImageBack'];
		        $eduCardImage = $_POST['eduCardImage'];
		        $jobHistoryCard = $_POST['jobHistoryCard'];
				$formStep = 5 ;
				$document = array(
			        'certsScans' => array(
						'submitDate' => $submitDate,
				        'nationalCertificate' => array(
												'firstPage'=> $certFirstImage ,
												'secondPage'=> $certSecondImage ,
												'thirdPage'=> $certThirdImage
												),
				        'nationalIDCard' => array(
												'front'=> $cardFirstImage ,
												'back'=> $cardSecondImage 
												),
				        'solderingCard' => array(
												'front'=> $solderingImageForw ,
												'back'=> $solderingImageBack 
												),
				        'educationCert' => $eduCardImage,
				        'jobHistoryCert' => $jobHistoryCard
						),
					'formStep' => $formStep 
				);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$collection = $connection->$dbname->kanoons;
				$mongoID = new MongoID($doc_id);
				$collection->update(
		            array("_id" => $mongoID),
		            array('$set' => $document),
		            array("upsert" => true)
				);
				$cursor = $collection->find(
			        array(
			            '_id' => $mongoID
			        )
			    );
			    foreach ($cursor as $obj){
					$formStep = $obj['formStep'];
					switch ($formStep){
					    case 0:
					        $formNextStep = "/page/14";
					        break;
					    case 1:
					        $formNextStep = "/page/15";
					        break;
					    case 2:
					        $formNextStep = "/page/19";
					        break;
					    case 3:
					        $formNextStep = "/page/16";
					        break;
					    case 4:
					        $formNextStep = "/page/17";
					        break;
					    default:
					        $formNextStep = "/page/11";
					}				
					echo '{ "formStep": "' . $obj['formStep'] . '" , "formNextStep": "' . $formNextStep . '" ,"registerId": "' . $obj['_id'] . '" , "firstName": "' . $obj['firstName'] . '" , "lastName": "' . $obj['lastName'] . '" , "officeStep": "' . $obj['officeStep'] . '" , "editPermission": "' . $obj['editPermission'] . '" , "personalImage": "' . $obj['personalImage'] . '" }';
				}
				
				$connection->close();
				}catch(MongoConnectionException $e){
					die('Error connecting to MongoDB server');
				}catch(MongoException $e){
					die('Error: ' . $e->getMessage());
				}
		}
		protected function	_getProposal($argus)
		{	
			try{
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$db = $connection->$dbname;
				$collection = $db->kanoons;
				//$cursor = $collection->find();
				//$recordCounts = $cursor->count() ;
				$doc_id = $_POST['registerationId'];
				$cursor = $collection->find(
			        array(
			            '_id' => new MongoId($doc_id)
			        )
			    );
				foreach ($cursor as $obj) {
					return $obj ;
				}
				$connection->close();
			}catch (MongoConnectionException $e) {
				die('Error connecting to MongoDB server');
			}catch (MongoException $e) {
				die('Error: ' . $e->getMessage());
			}
		}
		protected function	_upload($argus)
		{
			if(isset($_FILES["managerPersonalImageUpload"]) && $_FILES["managerPersonalImageUpload"]["error"]== UPLOAD_ERR_OK){
				$managerPersonalImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/manager_personal_image/';
				if ($_FILES["managerPersonalImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['managerPersonalImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_managerPersonal          = strtolower($_FILES['managerPersonalImageUpload']['name']);
				$File_Ext_managerPersonal           = substr($File_Name_managerPersonal, strrpos($File_Name_managerPersonal, '.'));
				$Random_Number_managerPersonal      = rand(0, 9999999999);
				$NewFileName_managerPersonal 		= $Random_Number_managerPersonal.$File_Ext_managerPersonal;
				if(move_uploaded_file($_FILES['managerPersonalImageUpload']['tmp_name'], $managerPersonalImageUploadDirectory.$NewFileName_managerPersonal ))
				   {
					echo $NewFileName_managerPersonal ;
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadIsaargari($argus)
		{
			if(isset($_FILES["isaargariImageUpload"]) && $_FILES["isaargariImageUpload"]["error"]== UPLOAD_ERR_OK){
				$managerPersonalImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/isaargari/';
				if ($_FILES["isaargariImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['isaargariImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_managerPersonal          = strtolower($_FILES['isaargariImageUpload']['name']);
				$File_Ext_managerPersonal           = substr($File_Name_managerPersonal, strrpos($File_Name_managerPersonal, '.'));
				$Random_Number_managerPersonal      = rand(0, 9999999999);
				$NewFileName_managerPersonal 		= $Random_Number_managerPersonal.$File_Ext_managerPersonal;
				if(move_uploaded_file($_FILES['isaargariImageUpload']['tmp_name'], $managerPersonalImageUploadDirectory.$NewFileName_managerPersonal ))
				   {
					echo $NewFileName_managerPersonal ;
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadcertFirstImage($argus)
		{
			if(isset($_FILES["certFirstImageUpload"]) && $_FILES["certFirstImageUpload"]["error"]== UPLOAD_ERR_OK){
				$certFirstImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/certFirstImage/';
				if ($_FILES["certFirstImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['certFirstImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certFirst          = strtolower($_FILES['certFirstImageUpload']['name']);
				$File_Ext_certFirst           = substr($File_Name_certFirst, strrpos($File_Name_certFirst, '.'));
				$Random_Number_certFirst      = rand(0, 9999999999);
				$NewFileName_certFirst 		= $Random_Number_certFirst.$File_Ext_certFirst;
				if(move_uploaded_file($_FILES['certFirstImageUpload']['tmp_name'], $certFirstImageUploadDirectory.$NewFileName_certFirst ))
				   {
					//echo $NewFileName_certFirst ;
					echo '{ "certFirstImage": "' . $NewFileName_certFirst . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadcertSecondImage($argus)
		{
			if(isset($_FILES["certSecondImageUpload"]) && $_FILES["certSecondImageUpload"]["error"]== UPLOAD_ERR_OK){
				$certSecondImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/certSecondImage/';
				if ($_FILES["certSecondImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['certSecondImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certSecond          = strtolower($_FILES['certSecondImageUpload']['name']);
				$File_Ext_certSecond           = substr($File_Name_certSecond, strrpos($File_Name_certSecond, '.'));
				$Random_Number_certSecond      = rand(0, 9999999999);
				$NewFileName_certSecond 		= $Random_Number_certSecond.$File_Ext_certSecond;
				if(move_uploaded_file($_FILES['certSecondImageUpload']['tmp_name'], $certSecondImageUploadDirectory.$NewFileName_certSecond ))
				   {
					//echo $NewFileName_certSecond ;
					echo '{ "certSecondImage": "' . $NewFileName_certSecond . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadcertThirdImage($argus)
		{
			if(isset($_FILES["certThirdImageUpload"]) && $_FILES["certThirdImageUpload"]["error"]== UPLOAD_ERR_OK){
				$certThirdImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/certThirdImage/';
				if ($_FILES["certThirdImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['certThirdImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certThird          = strtolower($_FILES['certThirdImageUpload']['name']);
				$File_Ext_certThird           = substr($File_Name_certThird, strrpos($File_Name_certThird, '.'));
				$Random_Number_certThird      = rand(0, 9999999999);
				$NewFileName_certThird 		= $Random_Number_certThird.$File_Ext_certThird;
				if(move_uploaded_file($_FILES['certThirdImageUpload']['tmp_name'], $certThirdImageUploadDirectory.$NewFileName_certThird ))
				   {
					//echo $NewFileName_certThird ;
					echo '{ "certThirdImage": "' . $NewFileName_certThird . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadcardFirstImage($argus)
		{
			if(isset($_FILES["cardFirstImageUpload"]) && $_FILES["cardFirstImageUpload"]["error"]== UPLOAD_ERR_OK){
				$cardFirstImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/cardFirstImage/';
				if ($_FILES["cardFirstImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['cardFirstImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certFirst          = strtolower($_FILES['cardFirstImageUpload']['name']);
				$File_Ext_certFirst           = substr($File_Name_certFirst, strrpos($File_Name_certFirst, '.'));
				$Random_Number_certFirst      = rand(0, 9999999999);
				$NewFileName_certFirst 		= $Random_Number_certFirst.$File_Ext_certFirst;
				if(move_uploaded_file($_FILES['cardFirstImageUpload']['tmp_name'], $cardFirstImageUploadDirectory.$NewFileName_certFirst ))
				   {
					//echo $NewFileName_certFirst ;
					echo '{ "cardFirstImage": "' . $NewFileName_certFirst . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadcardSecondImage($argus)
		{
			if(isset($_FILES["cardSecondImageUpload"]) && $_FILES["cardSecondImageUpload"]["error"]== UPLOAD_ERR_OK){
				$cardSecondImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/cardSecondImage/';
				if ($_FILES["cardSecondImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['cardSecondImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certSecond          = strtolower($_FILES['cardSecondImageUpload']['name']);
				$File_Ext_certSecond           = substr($File_Name_certSecond, strrpos($File_Name_certSecond, '.'));
				$Random_Number_certSecond      = rand(0, 9999999999);
				$NewFileName_certSecond 		= $Random_Number_certSecond.$File_Ext_certSecond;
				if(move_uploaded_file($_FILES['cardSecondImageUpload']['tmp_name'], $cardSecondImageUploadDirectory.$NewFileName_certSecond ))
				   {
					//echo $NewFileName_certSecond ;
					echo '{ "cardSecondImage": "' . $NewFileName_certSecond . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadsolderingImageForw($argus)
		{
			if(isset($_FILES["solderingImageForwUpload"]) && $_FILES["solderingImageForwUpload"]["error"]== UPLOAD_ERR_OK){
				$solderingImageForwUploadDirectory	= '../public_html/flsimgs/ershad/2/images/solderingImageForw/';
				if ($_FILES["solderingImageForwUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['solderingImageForwUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certFirst          = strtolower($_FILES['solderingImageForwUpload']['name']);
				$File_Ext_certFirst           = substr($File_Name_certFirst, strrpos($File_Name_certFirst, '.'));
				$Random_Number_certFirst      = rand(0, 9999999999);
				$NewFileName_certFirst 		= $Random_Number_certFirst.$File_Ext_certFirst;
				if(move_uploaded_file($_FILES['solderingImageForwUpload']['tmp_name'], $solderingImageForwUploadDirectory.$NewFileName_certFirst ))
				   {
					//echo $NewFileName_certFirst ;
					echo '{ "solderingImageForw": "' . $NewFileName_certFirst . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_uploadsolderingImageBack($argus)
		{
			if(isset($_FILES["solderingImageBackUpload"]) && $_FILES["solderingImageBackUpload"]["error"]== UPLOAD_ERR_OK){
				$solderingImageBackUploadDirectory	= '../public_html/flsimgs/ershad/2/images/solderingImageBack/';
				if ($_FILES["solderingImageBackUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['solderingImageBackUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certSecond          = strtolower($_FILES['solderingImageBackUpload']['name']);
				$File_Ext_certSecond           = substr($File_Name_certSecond, strrpos($File_Name_certSecond, '.'));
				$Random_Number_certSecond      = rand(0, 9999999999);
				$NewFileName_certSecond 		= $Random_Number_certSecond.$File_Ext_certSecond;
				if(move_uploaded_file($_FILES['solderingImageBackUpload']['tmp_name'], $solderingImageBackUploadDirectory.$NewFileName_certSecond ))
				   {
					//echo $NewFileName_certSecond ;
					echo '{ "solderingImageBack": "' . $NewFileName_certSecond . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_eduCardImage($argus)
		{
			if(isset($_FILES["eduCardImageUpload"]) && $_FILES["eduCardImageUpload"]["error"]== UPLOAD_ERR_OK){
				$eduCardImageUploadDirectory	= '../public_html/flsimgs/ershad/2/images/eduCardImage/';
				if ($_FILES["eduCardImageUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['eduCardImageUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certSecond          = strtolower($_FILES['eduCardImageUpload']['name']);
				$File_Ext_certSecond           = substr($File_Name_certSecond, strrpos($File_Name_certSecond, '.'));
				$Random_Number_certSecond      = rand(0, 9999999999);
				$NewFileName_certSecond 		= $Random_Number_certSecond.$File_Ext_certSecond;
				if(move_uploaded_file($_FILES['eduCardImageUpload']['tmp_name'], $eduCardImageUploadDirectory.$NewFileName_certSecond ))
				   {
					//echo $NewFileName_certSecond ;
					echo '{ "eduCardImage": "' . $NewFileName_certSecond . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_jobHistoryCard($argus)
		{
			if(isset($_FILES["jobHistoryCardUpload"]) && $_FILES["jobHistoryCardUpload"]["error"]== UPLOAD_ERR_OK){
				$jobHistoryCardUploadDirectory	= '../public_html/flsimgs/ershad/2/images/jobHistoryCard/';
				if ($_FILES["jobHistoryCardUpload"]["size"] > 5242880) {
					die("حجم فایل زیاد است!");
				}
				switch(strtolower($_FILES['jobHistoryCardUpload']['type']))
					{
			            case 'image/png': 
						case 'image/gif': 
						case 'image/jpeg': 
						case 'image/pjpeg':
						break;
						default:
				}
				$File_Name_certSecond          = strtolower($_FILES['jobHistoryCardUpload']['name']);
				$File_Ext_certSecond           = substr($File_Name_certSecond, strrpos($File_Name_certSecond, '.'));
				$Random_Number_certSecond      = rand(0, 9999999999);
				$NewFileName_certSecond 		= $Random_Number_certSecond.$File_Ext_certSecond;
				if(move_uploaded_file($_FILES['jobHistoryCardUpload']['tmp_name'], $jobHistoryCardUploadDirectory.$NewFileName_certSecond ))
				   {
					//echo $NewFileName_certSecond ;
					echo '{ "jobHistoryCard": "' . $NewFileName_certSecond . '" }';
				}else{
					die('اشکالی در ارسال فایل به وجود آمده است.');
				}
			}else{
				die('مشکلی در ارسال فایل به وجود آمده است. آیا حجم مجاز درست تنظیم شده است؟(upload_max_filesize)');
			}
		}
		protected function	_getProposalStatus($argus)
		{	
			try {
				$dbhost = 'localhost';
		        $dbname = 'ershad_tehran';
				$options = array(
				        'db'=>"ershad_tehran",
				        'username'=>"tehran_ershad_root",
				        'password'=>'n$d1H6&5f0'
				);
				$connection = new Mongo("mongodb://$dbhost", $options);
				$db = $connection->$dbname;
				$collection = $db->kanoons;
				$doc_id = $_POST['registerationId'];
				$cursor = $collection->find(
			        array(
			            '_id' => new MongoId($doc_id)
			        )
			    );
				foreach ($cursor as $obj) {
					$formStep = $obj['formStep'];
					switch ($formStep) {
					    case 0:
					        $formNextStep = "/page/14";
					        break;
					    case 1:
					        $formNextStep = "/page/15";
					        break;
					    case 2:
					        $formNextStep = "/page/19";
					        break;
					    case 3:
					        $formNextStep = "/page/16";
					        break;
					    case 4:
					        $formNextStep = "/page/17";
					        break;
					    default:
					        $formNextStep = "/page/11";
					}
					echo '{ 
							"formStep":"'.$obj['formStep'].'", 
							"formNextStep":"'.$formNextStep.'",
							"registerId":"'.$obj['_id'].'" , 
							"firstName":"'.$obj['firstName'].'" , 
							"lastName":"'.$obj['lastName'].'" , 
							"managerPersonalImage":"'.$obj['managerPersonalImage'].'"
						}';
				}
				$connection->close();
				} catch (MongoConnectionException $e){
					die('Error connecting to MongoDB server');
				} catch (MongoException $e){
					die('Error: ' . $e->getMessage());
				}
		}
	}
?>
