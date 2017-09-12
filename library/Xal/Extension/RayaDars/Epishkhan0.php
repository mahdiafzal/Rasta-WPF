<?php
/*
	*	
*/



class Xal_Extension_RayaDars_Epishkhan
{
	
	public function	run($argus)
	{
		if(!is_string($argus['ep.ns']))	$argus['ep.ns'] = 'default';
		
	
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'login'		: return $this->_login($argu); break;

			}
			
		}
	}
	
	
	
	
	
	
	protected function	_login($argus)
	{
		
		
		require 'openid.php';
		try {
		    $openid = new LightOpenID;
		    if(!$openid->mode) {
			// test url : index.php?office=71111001
		        if(isset($_GET['office'])) {
				    //identity 
		            $openid->identity = "http://auth.epishkhan.ir/identity/".(int)$_GET['office'];
					$openid->required = array('userkey');
		            header('Location: ' . $openid->authUrl());
		        }
		
		    } elseif($openid->mode == 'cancel') {
		        echo 'User has canceled authentication!';
		    } else {
		        // if validate by openId get 
				if($openid->validate())
				{
					//echo 'User ' .$_GET['office'].' has logged in.<br>';
					//echo 'Here is the provided info: ';
					$res=$openid->getAttributes();
					//echo "userkey=".$res['userkey'];
					print_r($_SESSION); //die();
					return array('office'=> array('userkey'=> $res['userkey'])  );
					//$_SESSION['rayadars'] = array('operator'=> array('type'=>'epishkhan', 'userkey'=> $res['userkey'])  );
					//header('Location: /rtc/خرید_رایادرس_-_قدم_اول');
					//print_r($_SESSION);
				}else{
					echo 'User hos not loggedin.';
				}
		    }
		} catch(ErrorException $e) {
		    echo $e->getMessage();
		}
	}
	
	
	
	
	
	


}

?>