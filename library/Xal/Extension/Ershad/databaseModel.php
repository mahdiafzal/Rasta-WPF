<?php

	class Xal_Extension_Ershad_databaseModel{

		/* Peroperties */

		public $user 		= false;
		public $base_url	= 'http://sajafarhang.ir';
		public $base_email	= 'support@sajafarhang.ir';

		protected $dbhost 	= 'localhost';
		protected $dbname 	= 'ershad_tehran';
		protected $dbuser 	= 'tehran_ershad_root';
		protected $dbpass 	= 'n$d1H6&5f0';
		protected $database = null;

		protected $PUBLIC_KEY = '6LdBgBITAAAAAKfJrV5IFHK1wwzZpvsm0JOVOAx9';
		protected $PRIVATE_KEY = '6LdBgBITAAAAAA7a8o6dBLFvdcJF3u8EIvEmqCko';

		protected $google_url = "https://www.google.com/recaptcha/api/siteverify";
		protected $secret = '6LdBgBITAAAAAA7a8o6dBLFvdcJF3u8EIvEmqCko';

		/* initialize */

		public function	run($argus) {

			/* preprocess */

			date_default_timezone_set('Asia/Tehran');

			if ($this->database === null) {
				$this->_dbConnect();
			}
				

			/*  */
			if (!is_string($argus['cu.ns'])) {
				$argus['cu.ns'] = 'default';
			}

			/* callbacks */

			foreach ($argus as $arguKey => $argu) {
				switch ($arguKey) {
					case 'controller'		: return $this->controller($argu); break;
					case 'upload'			: return $this->upload($argu); break;
					case 'user.login'		: return $this->loginUser($argu); break;
					case 'user.logout'		: return $this->logoutUser($argu); break;
					case 'user.register'	: return $this->registerUser($argu); break;
					case 'user.resetpass'	: return $this->resetPassword($argu); break;
				}
			}

		}

		/* Database */

		protected function _dbConnect() {
			try {

				$options = array(
					'db' => $this->dbname,
					'username' => $this->dbuser,
					'password' => $this->dbpass
				);

				$connection = new MongoClient('mongodb://localhost', $options);

				$this->database = $connection->{$this->dbname};

			} catch (MongoConnectionException $e){

				die($e->getMessage());
				die('Error: connecting to MongoDB server failed');
				return false;

			} catch (MongoException $e){

				die('Error: ' . $e->getMessage());
				return false;

			}
		}

		protected function _dbDiconnect() {
			$this->database->close();
			$this->database = null;
		}

		protected function _getQuery() {

			require 'queryConfigs.php';

			if (!isset($queryConfig[$_REQUEST['config']])) {
				return array(
					'query' => array(),
					'fields' => array()
				);
			}

			return $queryConfig[$_REQUEST['config']];

		}

		protected function _getFields() {

			$documents = isset($_REQUEST['documents']) && strlen($_REQUEST['documents']) > 0  ? $_REQUEST['documents'] : null;

			if (null === $documents) {
				return array();
			}

			return array_map(function ($item) {
				return trim($item);
			}, explode(',', $documents));
		}

		/* DB Methods */

		public function controller($argus) {

			$checkUser = $this->checkUser();
			if(!$checkUser['status']){
				return $checkUser;
			}
			$collection = (isset($_REQUEST['collection'])) ? $_REQUEST['collection'] : '';
			
			if (isset($_REQUEST['action'])) {
				switch ($_REQUEST['action']) {
					case 'list'	:
						return $this->listData($collection); 
						break;
					case 'view'	:
						return $this->viewData($collection); 
						break;	
					case 'update' :
						return $this->updateData($collection); 
						break;	
					case 'save' :
						return $this->saveData($collection); 
						break;	
					case 'remove' :
						return $this->removeData($collection); 
						break;	
				}				
			} else {
				die('Error: action is not defined');
			}

		}

		public function listData($collection) {

			$query = $this->_getQuery();

			if($this->user['role'] == 'kanoon' && isset($this->user['kanoonId'])){
				if ($collection == 'kanoons') {
					$query['query']['_id'] = new MongoId($this->user['kanoonId']);
				} else {
					$query['query']['Metadata.KanoonId'] = new MongoId($this->user['kanoonId']);
				}
			} else {
				$query = $this->_getQuery();
			}

			try {
				$cursor = $this->database->$collection->find($query['query'], $query['fields']);

				$result = array();

				foreach ($cursor as $row) {
					$result[] = $row;
				}
			} catch (Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
				die;
			}	

			return array(
				'status' 	=> true,
				'data' 		=> $result,
				'user' 		=> $this->user,
				'query'	=> $query,
				'$_SESSION' => $_SESSION
			);


		}		

		public function viewData($collection) {

			$query = $this->_getQuery();

			if($this->user['role'] == 'kanoon' && isset($this->user['kanoonId'])){
				if ($collection == 'kanoons') {
					$query['query']['_id'] = new MongoId($this->user['kanoonId']);
				} else {
					$query['query']['_id'] = new MongoId($_REQUEST['_id']);
				}
			}

			$result = $this->database->$collection->findOne($query['query'], $query['fields']);

			return array(
				'status' 	=> true,
				'data' 		=> $result,
				'user' 		=> $this->user,
				'$_SESSION' => $_SESSION
			);
			
		}

		public function removeData($collection) {

			$query = $this->_getQuery();

			if($this->user['role'] == 'kanoon' && isset($this->user['kanoonId'])){
				if ($collection == 'kanoons') {
					$query['query']['_id'] = new MongoId($this->user['kanoonId']);
				} else {
					$query['query']['_id'] = new MongoId($_REQUEST['_id']);
				}
			}

			try {
				$result = $this->database->$collection->remove($query['query']);
				return array(
					'status' 	=> true,
					'data' 		=> $result,
					'user' 		=> $this->user,
				);
			} catch (Exception $e) {
				return array(
					'status' 	=> false,
					'message ' => $e->getMessage()
				);
			}			


			
		}		

		public function updateData($collection) {

			$query = array();
			$validKeys = array(
				'BaseInfos',
				'OrganizationInfos',
				'PersonalInfos',
				'Files',
				'WorkExperience',
				'ContactInfos',
				'EducationInfos',
				'FormData',
				'Ekran'
			);

			$dataToSave = $_REQUEST['save'];

			foreach ($dataToSave as $key => $value) {
				if (in_array($key, $validKeys)) {
					$query[$key] = $value;
				}
			}

			if($collection == 'kanoons' && $this->user['role'] == 'kanoon' && isset($this->user['kanoonId'])){
				$mongoID = new MongoId($this->user['kanoonId']);
			}

			if (isset($_REQUEST['_id']) && $_REQUEST['_id'] != ''){
				$mongoID = new MongoId($_REQUEST['_id']);
			}

			try {
				$result = $this->database->$collection->update(
					array("_id" => $mongoID),
					array('$set' => $query),
					array("upsert" => true)
				);
				return array(
					'status' 	=> true,
					'result ' => $result,
					'id' => $mongoID
				);
			} catch (Exception $e) {
				return array(
					'status' 	=> false,
					'message ' => $e->getMessage()
				);
			}
			
		}

		public function saveData($collection) {

			$query = $_REQUEST['save'];

			// $query = Rasta_Util_DotNotation::set($query, $dataToSave);

			if($this->user['role'] == 'kanoon' && isset($this->user['kanoonId'])){
				if ($collection == 'persons') {
					$query['Metadata']['KanoonId'] = new MongoId($this->user['kanoonId']);
					$query['Metadata']['RegisterDate'] = date('Y/m/d H:i:s a', time());
				}
			}

			try {
				$result = $this->database->$collection->save($query);
			} catch (Exception $e) {
			    echo 'Caught exception: ',  $e->getMessage(), "\n";
			    print_r($_REQUEST);
			    die;
			}

			return array(
				'status' 	=> true,
				'result ' => $result,
				'$_REQUEST' => $_REQUEST
			);
			
		}		

		/* User */

		public function setUser() {

			if ($this->user = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginUser")) {

				$this->setUserRole('kanoon'); 				
				$this->user['kanoonId'] = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.KanoonId");

			} else if ($this->user = Rasta_Util_DotNotation::GetValue($_SESSION, "KanoonRegApp.LoginStaff")) {

				$this->setUserRole('staff');

			} else {
				$this->user = false;
			}

		}

		public function setUserRole($role) {
			
			if (isset($this->user['Email']) || isset($this->user['CellPhone'])) {
				$this->user['role'] = $role;
			}

		}

		public function checkUser() {


			$this->setUser();
			if ($this->user === false) {
				return array(
					'status' => false,
					'message' => 'شما مجوز لازم برای بازدید از این صفحه را ندارید',
					'redirect' => $this->base_url
				);
			}
			return array(
				'status' => true,
				'data' => $this->user
			);
		}

		public function registerUser(){

			$response = $_POST['g-recaptcha-response'];
			$challenge = $_POST['g-recaptcha-challenge'];
			$verified = $this->verifyCaptchaOldV($response, $challenge);

			if($verified == false){
				return array(
					'status' => false,
					'message' => 'کد امنیتی صحیح نیست'
				);	
			}

			if( !isset($_POST['email']) || !isset($_POST['cellPhone']) || !isset($_POST['password']) ){
				return array(
					'status' => false,
					'message' => 'لطفا اطلاعات لازم را وارد کنید'
				);				
			}

			$checkEmail = $this->checkEmail($_POST['email']);

			if ($checkEmail == true) {
				return array(
					'status' => false,
					'message' => 'ایمیل وارد شده، قبلا استفاده شده است'
				);
			}

			$checkMobaile = $this->checkMobaile($_POST['cellPhone']);

			if ($checkMobaile == true) {
				return array(
					'status' => false,
					'message' => 'موبایل وارد شده، قبلا استفاده شده است'
				);
			}			


			$user = array();
			$user['LoginInfos']['Users'][0]['Email'] = $_POST['email'];
			$user['LoginInfos']['Users'][0]['CellPhone'] = $_POST['cellPhone'];
			$user['LoginInfos']['Users'][0]['Password'] = md5($_POST['password']);
			$user['Metadata']['RegisterDate'] = date('Y/m/d H:i:s a', time());
			
			$collection = 'kanoons';
			$kanoons = $this->database->$collection->save($user);


			if(empty($kanoons)){
				return array(
					'status' => false,
					'message' => 'اطلاعات وارد شده صحیح نیست'
				);					
			}

			$emailTitle = 'ثبت نام با موفقیت انجام شد';
			$emailMessage = '<div>کاربر گرامی, ثبت نام شما با موفقیت انجام شد</div>';
			$emailMessage .= '<div>اطلاعات شما</div>';
			$emailMessage .= '<div><strong>شماره تلفن همراه: </strong>' . $_POST['cellPhone'] . '</div>';
			$emailMessage .= '<div><strong>رمز عبور: </strong>' . $_POST['password'] . '</div>';

			$this->sendEmail($_POST['email'], $emailTitle, $this->emailTemplate($emailMessage));

			$userDataForLogin = array(
				'username' => $_POST['email'],
				'password' => $_POST['password']
			);

			$loginUser = $this->loginUser('', $userDataForLogin);

			if(!$loginUser['status']) {
				return array(
					'status' => false,
					'message' => 'مشکل در ورود'
				);
			}
			
			return array(
				'status' => true,
				'data' => $kanoons,
				'checkEmail' => $checkEmail,
				'loginUser' => $loginUser
			);					

		}	

		public function checkEmail($email){
			$user = array();
			$user['Email'] = $email;
			$collection = 'kanoons';
			$query = array("LoginInfos.Users"=> array('$elemMatch'=> $user));
			try {
				$checkEmail = $this->database->$collection->findOne($query);
			} catch (Exception $e) {
			    echo 'Caught exception: ',  $e->getMessage(), "\n";
			    die;
			}			
			if(empty($checkEmail)){
				return false;					
			}
			return true;

		}

		public function checkMobaile($mobile){
			$user = array();
			$user['CellPhone'] = $mobile;
			$collection = 'kanoons';
			$query = array("LoginInfos.Users"=> array('$elemMatch'=> $user));
			try {
				$checkMobaile = $this->database->$collection->findOne($query);
			} catch (Exception $e) {
			    echo 'Caught exception: ',  $e->getMessage(), "\n";
			    die;
			}			
			if(empty($checkMobaile)){
				return false;					
			}
			return true;

		}		


		public function loginUser($argus, $userDataForLogin) {


			if(!isset($userDataForLogin['username']) || !isset($userDataForLogin['password'])) {
				if(!isset($_POST['username']) || !isset($_POST['password']) || $_POST['username'] == '' || $_POST['password'] == ''){
					return array(
						'status' => false,
						'message' => 'لطفا اطلاعات لازم را وارد کنید'
					);				
				}
			}

			$user = array();

			if(isset($userDataForLogin['username']) || isset($userDataForLogin['password'])) {
				$user['Email'] = $userDataForLogin['username'];
				$user['Password'] = md5($userDataForLogin['password']);
			} else {
				$user['Email'] = $_POST['username'];
				$user['Password'] = md5($_POST['password']);
			}

			/**/
			$query = array("LoginInfos.Users"=> array('$elemMatch'=> $user));
			$fields = array('LoginInfos.Users.$'=>1, 'Progress'=>1, 'BaseInfos'=>1, 'FormData'=>1);

			$collection = 'kanoons';
			$thisUser = $this->database->$collection->findOne($query, $fields);
			/**/

			if(empty($thisUser)){
				$user = array();
				$user['CellPhone'] = $_POST['username'];
				$user['Password'] = md5($_POST['password']);
			}


			/**/
			$query = array("LoginInfos.Users"=> array('$elemMatch'=> $user));
			$fields = array('LoginInfos.Users.$'=>1, 'Progress'=>1, 'BaseInfos'=>1, 'FormData'=>1);


			$collection = 'kanoons';
			$thisUser = $this->database->$collection->findOne($query, $fields);
			/**/


			if(empty($thisUser)){
				return array(
					'status' => false,
					'message' => 'اطلاعات وارد شده صحیح نیست'
				);					
			}


			$result = array();
			$result['user'] 			= $thisUser['LoginInfos']['Users'][0];
			$result['user']['KanoonId'] = $thisUser['_id']->{'$id'};
			$result['progress'] 		= (isset($thisUser['Progress'])) ? $thisUser['Progress'] : array();
			$result['baseinfos'] 		= (isset($thisUser['BaseInfos'])) ? $thisUser['BaseInfos'] : array();
			

			if (isset($thisUser['LoginInfos']['Users'][0]['Role'])) {
				$extra_user_groups = '/'.$thisUser['LoginInfos']['Users'][0]['Role'].'/';
				$_SESSION = Rasta_Util_DotNotation::Set($_SESSION, array("KanoonRegApp.KanoonId"=>$thisUser['_id']->{'$id'}, "KanoonRegApp.LoginStaff"=>$user, "MyApp.extra_user_groups"=>$extra_user_groups));
			} else {
				$extra_user_groups = '/3/';
				$_SESSION = Rasta_Util_DotNotation::Set($_SESSION, array("KanoonRegApp.KanoonId"=>$thisUser['_id']->{'$id'}, "KanoonRegApp.LoginUser"=>$user, "MyApp.extra_user_groups"=>$extra_user_groups));
			}


			$this->setUser();

			return array(
				'status' => true,
				'data' => $thisUser,
				'result' => $result,
				'user' => $this->user,
				'$_SESSION' => $_SESSION
			);

		}

		public function logoutUser($argus) {
			$_SESSION = Rasta_Util_DotNotation::Set($_SESSION, array("KanoonRegApp.LoginUser"=>false, "MyApp.extra_user_groups"=>''));
			header( 'Location: '. $this->base_url ) ;
		}

		public function resetPassword($argus) {
			if(!isset($_POST['userEmail']) || $_POST['userEmail'] == ''){
				return array(
					'status' => false,
					'message' => 'لطفا اطلاعات لازم را وارد کنید'
				);				
			}

			$user = array();
			$user['Email'] = $_POST['userEmail'];

			$query = array("LoginInfos.Users"=> array('$elemMatch'=> $user));
			$fields = array('LoginInfos.Users.$'=>1);

			$collection = 'kanoons';
			$result = $this->database->$collection->findOne($query, $fields);

			if(empty($result)){
				return array(
					'status' => false,
					'message' => 'اطلاعات وارد شده صحیح نیست'
				);	
			}

			$updateUser = array(); 
			$randomPassword = $this->randomPassword();

			$updateUser['LoginInfos.Users.0.Email'] = $result['LoginInfos']['Users'][0]['Email'];
			$updateUser['LoginInfos.Users.0.CellPhone'] = $result['LoginInfos']['Users'][0]['CellPhone'];
			$updateUser['LoginInfos.Users.0.Password'] = md5($randomPassword);

			$message  = '<div style="line-height:2.2; direction:rtl;text-align:right;font-size: 16px; font-family: tahoma;">با سلام</div>';
			$message .= '<div style="line-height:2.2; direction:rtl;text-align:right;font-size: 14px; font-family: tahoma;">رمز عبور شما با موفقیت تغییر یافت، لطفا در حفظ و نگه داری آن کوشا باشید</div>';
			$message .= '<div style="line-height:2.2; direction:rtl;text-align:center;padding: 7px;border-radius: 4px;font-size: 20px;"><strong  style="line-height:2; direction:rtl; margin: 0 20px; background: rgba(100,100,100,0.2);padding: 7px;border-radius: 4px;"><code>'.$randomPassword.'</code></strong></div>';
			$message .= '<div style="line-height:2.2; direction:rtl;text-align:left;font-size: 14px; font-family: tahoma;">با تشکر</div>';
			$message .= '<div style="line-height:2.2; direction:rtl;text-align:right;font-size: 14px; font-family: tahoma;">سامانه جامع کانون های آگهی و تبلیغاتی</div>';
			$message .= '<div style="line-height:2.2; direction:rtl;text-align:right;font-size: 14px; font-family: tahoma;">اداره فرهنگ و ارشاد اسلامی شهر تهران</div>';


			try {
				$updateResult = $this->database->$collection->update(
					array("_id" => $result['_id']),
					array('$set' => $updateUser),
					array("upsert" => true)
				);
				$emailResult = $this->sendEmail($result['LoginInfos']['Users'][0]['Email'], "تغییر رمز عبور", $message);
				return array(
					'status' => true,
					'n' => $randomPassword,
					'emailResult' => $emailResult,
					'message' => 'رمز عبور جدید برای آدرس ایمیل شما ارسال شد'
				);	
			} catch (Exception $e) {
				return array(
					'status' 	=> false,
					'message ' => $e->getMessage()
				);
			}


		}

		public function randomPassword() {
		    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		    $pass = array(); //remember to declare $pass as an array
		    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		    for ($i = 0; $i < 8; $i++) {
		        $n = rand(0, $alphaLength);
		        $pass[] = $alphabet[$n];
		    }
		    return implode($pass); //turn the array into a string
		}

		/* email template */

		public function emailTemplate($content) {
			$html = '<div>';
			$html .= '<table width="90%" border="0" cellpadding="0" cellspacing="0">';
			$html .= '	<tr width="90%" height="200px" bgcolor="#16A085" border="0" cellpadding="0" cellspacing="0">';
			$html .= '		<td border="0" cellpadding="0" cellspacing="0" style="background-repeat: no-repeat;background-position: center center;background-image:url(http://www.raya-web.com/flsimgs/ershad/2/files/skin/img/ershad.png)">';
			$html .= '		</td>';
			$html .= '	</tr>';
			$html .= '	<tr width="90%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1">';
			$html .= '		<td border="0" cellpadding="0" cellspacing="0">';
			$html .= '			<div style="padding:30px 10px;">';
			$html .= '				<div style="color: #303030; text-align: right; font-family: B Nazanin,Tahoma; font-size: 28px; font-weight: bold;">با سلام؛</div>';
			$html .= '			</div>';
			$html .= '		</td>';
			$html .= '	</tr>';				
			$html .= '	<tr width="90%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1">';
			$html .= '		<td border="0" cellpadding="0" cellspacing="0">';
			$html .= '			<div style="padding: 30px; background:#FFFFFF; text-align: right; font-size: 16px; line-height: 2; direction: rtl; border: 5px solid #f1f1f1">';			
			$html .=				$content;
			$html .= '			</div>';
			$html .= '		</td>';
			$html .= '	</tr>';
			$html .= '	<tr width="90%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f1f1">';
			$html .= '		<td border="0" cellpadding="0" cellspacing="0">';
			$html .= '			<div style="padding: 30px 10px;">';
			$html .= '				<div><strong>Web site:</strong>' . $this->base_url . '</div>';
			$html .= '				<div><strong>Mail:</strong>' . $this->base_email . '</div>';
			$html .= '				<div><strong>Tel:</strong>021 88841231</div>';
			$html .= '			</div>';
			$html .= '		</td>';
			$html .= '	</tr>';			
			$html .= '	<tr width="90%" height="150px" border="0" cellpadding="0" cellspacing="0" bgcolor="#16A085">';
			$html .= '		<td border="0" cellpadding="0" cellspacing="0" style="color: rgb(255, 255, 255); text-align: center; vertical-align: middle; font-family: B Titr,B Yekan; font-size: 22px;">';
			$html .= '			اداره فرهنگ و ارشاد اسلامی شهر تهران';
			$html .= '		</td>';
			$html .= '	</tr>';
			$html .= '</table>';
			$html .= '</div>';
			return $html;
		}

		/* Send Email */

		public function sendEmail($emailAddress, $subject, $message) {

			try { 
				$email = new Zend_Mail('UTF-8');
				$email->setBodyHtml($message);
				$email->setFrom($this->base_email, 'سامانه جامع کانون های آگهی و تبلیغاتی');
				$email->setSubject($subject);
				$email->addTo($emailAddress);
				$email->send();
				return array(
					'status' 	=> true
				);
			} catch (Zend_Exception $e) {
				return array(
					'status' 	=> false,
					'message ' => $e->getMessage()
				);
			}
		}

		/* google recaptcha */

		public function verifyCaptcha($response)
		{


			$url = $this->google_url."?secret=".$this->secret."&response=".$response;

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_TIMEOUT, 15);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, TRUE); 
			$curlData = curl_exec($curl);

			curl_close($curl);

			$res = json_decode($curlData, TRUE);
			if($res['success'] == 'true') {
				return true;
			}
			else {
				return false;
			}

		}

		/* verify old version */

		public function verifyCaptchaOldV($response, $challenge)
		{
			require_once('recaptchalib.php');

			// Verify the captcha
			// https://developers.google.com/recaptcha/docs/verify
			$resp = recaptcha_check_answer($this->PRIVATE_KEY,
			                                $_SERVER['REMOTE_ADDR'],
			                                $challenge,
			                                $response
			                            );

			if($resp->is_valid == true) {
				return true;
			}
			else {
				return false;
			}					
		}

		/* upload */

		public function upload($argus) {
			$output = array();
			// $keyName = key($_POST['file']);
			$keyName = 'file';

			$checkUser = $this->checkUser();
			if(!$checkUser['status']){
				return $checkUser;
			}

			$files = $_FILES;
			foreach ($files as $key => $file) {

				$output['status'] = true;

				if ($file["size"] > 300000) {
					$output['message'][] = 'حجم فایل زیاد است';
					$output['status'] = false;
				}

				if ($file["type"] != "image/jpg" && $file["type"] != "image/png" && $file["type"] != "image/jpeg"
				&& $file["type"] != "image/gif" ) {
					$output['message'][] = 'لطفا فقط فایل تصویری انتخاب نمایید';
					$output['status'] = false;
				}

				if ($output['status']) {

					$target_path = '../public_html/flsimgs/ershad/2/images/uploads/' . $this->user['kanoonId'] . '/';
					$target_url = '/flsimgs/ershad/2/images/uploads/' . $this->user['kanoonId'] . '/';

					if (!file_exists($target_path)) {
						if(!mkdir($target_path, 0755 ,true)) {
							$output['message'][] = 'خطا در ایجاد مسیر';
							return $output;
						}
					}

					$target_file = $target_path . preg_replace('/\s+/', '_', $file['name']);
					$target_src = $target_url . preg_replace('/\s+/', '_', $file['name']);

					if (move_uploaded_file($file["tmp_name"], $target_file)) {
						$output['src'] = $target_src;
						return $output;
					} else {
						$output['message'][] = 'خطا در بارگذاری';
						return $output;
					}
				}

			}

			return array(
				'status' => true,
				'data' => $output,
				'kanoonId' => $this->user['kanoonId']
			);
		}	

	}
?>
