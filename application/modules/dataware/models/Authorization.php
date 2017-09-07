<?php
/*
	*	
*/
class Db_Model_Authorization
{
	
		public function grant($remember)
		{
			if($remember) //user wants to be remembered, so set a cookie
			{
				$expire = time()+60*60*24*30; //set expiration to 1 month from now
				setcookie(COOKIENAME, SYSTEMPASSWORD, $expire);
				setcookie(COOKIENAME."_salt", $_SESSION[COOKIENAME.'_salt'], $expire);
			}
			else
			{
				//user does not want to be remembered, so destroy any potential cookies
				setcookie(COOKIENAME, "", time()-86400);
				setcookie(COOKIENAME."_salt", "", time()-86400);
				unset($_COOKIE[COOKIENAME]);
				unset($_COOKIE[COOKIENAME.'_salt']);
			}
	
			$_SESSION[COOKIENAME.'password'] = SYSTEMPASSWORDENCRYPTED;
		}
		public function revoke()
		{
			//destroy everything - cookies and session vars
			setcookie(COOKIENAME, "", time()-86400);
			setcookie(COOKIENAME."_salt", "", time()-86400);
			unset($_COOKIE[COOKIENAME]);
			unset($_COOKIE[COOKIENAME.'_salt']);
			session_unset();
			session_destroy();
		}
		public function isAuthorized()
		{
			// Is this just session long? (What!?? -DI)
			if((isset($_SESSION[COOKIENAME.'password']) && $_SESSION[COOKIENAME.'password'] == SYSTEMPASSWORDENCRYPTED) || (isset($_COOKIE[COOKIENAME]) && isset($_COOKIE[COOKIENAME.'_salt']) && md5($_COOKIE[COOKIENAME]."_".$_COOKIE[COOKIENAME.'_salt']) == SYSTEMPASSWORDENCRYPTED))
				return true;
			else
			{
				return false;
			}
		}
}

?>