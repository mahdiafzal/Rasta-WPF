<?php

class Xal_Extension_Apanet_Smbs
{

	public function	run($argus)
	{
		foreach($argus as $ark=>$argu)
		{
			switch($ark)
			{
				case 'get.dataset'	: return $this->getDataset($argu); break;
				//case 'force.download'	: return $this->_forceDownload($argu); break;
			}
		}
	}
	protected function getDataset($argu)
	{
		$result = array();
		if( !is_object($this->DB) )	$this->DB = Zend_Registry::get('extra_db_data');
        // $sql = "SELECT * FROM `users`";
        $sql1 = "SELECT * FROM `Information`";
        $sql2 = "SELECT * FROM `installations`";
        $sql3 = "SELECT * FROM `rebuildings`";
        $sql4 = "SELECT * FROM `charges`";
        $sql5 = "SELECT * FROM `people`";

        if($temp_result = $this->DB->fetchAll($sql1))
        	$result['part1'] = $temp_result;

    	  if($temp_result = $this->DB->fetchAll($sql2))
    	    $result['part2'] = $temp_result;

    	  if($temp_result = $this->DB->fetchAll($sql3))
        	$result['part3'] = $temp_result;

        if($temp_result = $this->DB->fetchAll($sql4))
        	$result['part4'] = $temp_result;

        if($temp_result = $this->DB->fetchAll($sql5))
        	$result['part5'] = $temp_result;
           
        return array('Result'=>$result);


        	// print_r($_post);
        // $data = json_decode(file_get_contents("php://input"));
        // $nameinput = mysql_real_escape_string($data->option[0]);
        // $nameinput = mysql_real_escape_string($data->option[1]);
        // $nameinput = mysql_real_escape_string($data->option[2]);
        // mysql_query("INSERT INTO Information('title','bdate','adress',)VALUES('".option[0]."','".option[1]."','".option[2]."')");
	}

    protected function AutoRegistration ($argu)
     {

        session_start();

        $username = $POST['username'];
        $password = $POST['password'];

        if ($username&&$password) 
        {
            $sql = "SELECT * FROM `users` WHERE username='$username'";
            // $numrows= $this->DB->fetchRow($sql);
            $stmt = $db->query($sql); 
            $result = $stmt->fetchAll(); 
            $num_rows = count($result);

            if ($num_rows!==0)
             {
                while ($row = $this->DB->fetchAll($sql)) 
                {
                    $dbusername = $row['username'];
                    $dbpassword = $row['password'];
                }
                if ($username==$dbusername&&$password==$dbpassword) 
                {
                    echo "you are logged in!";
                    @$_SESSION['username'] = $username;
                }else{
                    echo "your password is incorreect!";
                }
            }else{
                die("that user dosent exists!");
            }
        }else{
            die("please enter a username and password!");
        }
    //     if (isset($_post["submit"])) {
    //         $user=$_post['user'];
    //         $pass=$_post['pass'];

    //         $result = array();
    //         if( !is_object($this->DB) ) $this->DB = Zend_Registry::get('extra_db_data');
    //         $sql = "SELECT * FROM `users` WHERE username='$user', password='$pass'";

    //         if($temp_result = $this->DB->fetchRow($sql))
    //             $result['part'] = $temp_result;

    //             if ($user == $dbusername && $pass == $password) {
    //                 $_SESSION['apanet']['LoginUser']['sess_user']= $user;
    //                 redirect browser 
    //                 header("location: ")
    //             }
    //         } else {
    //             echo "invalid username or password!;"
    //           }
    //             // $sql="INSERT INTO 'users'(username, password) VALUES ('$user','$pass')";
    //             // $result=$this->DB-> fetchRow($sql);

    //             // if ($result) {
    //             //     echo "account successfully created";
    //             // } else {
    //             //     echo "failure!";
    //             // }
    //         // } else{
    //         //     echo "that username already exists! please try again with another.";
    //         // }
    }
}	
?>
