<?php
//BEGIN USER-DEFINED VARIABLES
//////////////////////////////
//password to gain access
$password = "admin";

//directory relative to this file to search for databases (if false, manually list databases in the $databases variable)

//++++ Rasta ++++
if( ! $directory = realpath(APPLICATION_PATH .'/../data/db/'.WBSiD) )
{
	$directory	= realpath(APPLICATION_PATH .'/../data/db').'/'.WBSiD;
	mkdir($directory, 0700);
}
define("DBROOT", $directory);
//**** Rasta ****

//die( DBROOT );

//whether or not to scan the subdirectories of the above directory infinitely deep
$subdirectories = false;

//if the above $directory variable is set to false, you must specify the databases manually in an array as the next variable
//if any of the databases do not exist as they are referenced by their path, they will be created automatically
$databases = array
(
	array
	(
		"path"=> "database1.sqlite",
		"name"=> "Database 1"
	),
	array
	(
		"path"=> "database2.sqlite",
		"name"=> "Database 2"
	)
);

//a list of custom functions that can be applied to columns in the databases
//make sure to define every function below if it is not a core PHP function
$custom_functions = array('md5', 'md5rev', 'sha1', 'sha1rev', 'time', 'mydate', 'strtotime', 'myreplace');

//define all the non-core custom functions
function md5rev($value)
{
	return strrev(md5($value));
}
function sha1rev($value)
{
	return strrev(sha1($value));
}
function mydate($value)
{
	return date("g:ia n/j/y", intval($value));
}
function myreplace($value)
{
	return ereg_replace("[^A-Za-z0-9]", "", strval($value));	
}

//changing the following variable allows multiple phpLiteAdmin installs to work under the same domain.
$cookie_name = 'pla3412';

//whether or not to put the app in debug mode where errors are outputted
$debug = false;

////////////////////////////
//END USER-DEFINED VARIABLES

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//there is no reason for the average user to edit anything below this comment
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

session_start(); //don't mess with this - required for the login session
date_default_timezone_set(date_default_timezone_get()); //needed to fix STRICT warnings about timezone issues

if($debug==true)
{
	ini_set("display_errors", 1);
	error_reporting(E_STRICT | E_ALL);
}

$startTimeTot = microtime(true); //start the timer to record page load time

//the salt and password encrypting is probably unnecessary protection but is done just for the sake of being very secure
//create a random salt for this session if a cookie doesn't already exist for it
if(!isset($_SESSION[$cookie_name.'_salt']) && !isset($_COOKIE[$cookie_name.'_salt']))
{
	$n = rand(10e16, 10e20);
	$_SESSION[$cookie_name.'_salt'] = base_convert($n, 10, 36);
}
else if(!isset($_SESSION[$cookie_name.'_salt']) && isset($_COOKIE[$cookie_name.'_salt'])) //session doesn't exist, but cookie does so grab it
{
	$_SESSION[$cookie_name.'_salt'] = $_COOKIE[$cookie_name.'_salt'];
}

//build the basename of this file for later reference
	//$info = pathinfo($_SERVER['PHP_SELF']);
	//$thisName = $info['basename'];

//constants
define("PROJECT", "DB Admin");
define("VERSION", "1.9.2");

//define("PAGE", $thisName);
define("PAGE", '/db/admin');

define("COOKIENAME", $cookie_name);
define("SYSTEMPASSWORD", $password); // Makes things easier.
define("SYSTEMPASSWORDENCRYPTED", md5($password."_".$_SESSION[$cookie_name.'_salt'])); //extra security - salted and encrypted password used for checking
define("FORCETYPE", false); //force the extension that will be used (set to false in almost all circumstances except debugging)

//data types array
$types = array("INTEGER", "REAL", "TEXT", "BLOB");
define("DATATYPES", serialize($types));

//accepted db extensions
$exts = array("sqlite", "sqlite3", "db", "db3");
define("EXTENSIONS", serialize($exts));

//available SQLite functions array (don't add anything here or there will be problems)
$functions = array("abs", "hex", "length", "lower", "ltrim", "random", "round", "rtrim", "trim", "typeof", "upper");
define("FUNCTIONS", serialize($functions));
define("CUSTOM_FUNCTIONS", serialize($custom_functions));


//function that allows SQL delimiter to be ignored inside comments or strings
function explode_sql($delimiter, $sql)
{
	$ign = array('"' => '"', "'" => "'", "/*" => "*/", "--" => "\n"); // Ignore sequences.
	$out = array();
	$last = 0;
	$slen = strlen($sql);
	$dlen = strlen($delimiter);
	$i = 0;
	while($i < $slen)
	{
		// Split on delimiter
		if($slen - $i >= $dlen && substr($sql, $i, $dlen) == $delimiter)
		{
			array_push($out, substr($sql, $last, $i - $last));
			$last = $i + $dlen;
			$i += $dlen;
			continue;
		}
		// Eat comments and string literals
		foreach($ign as $start => $end)
		{
			$ilen = strlen($start);
			if($slen - $i >= $ilen && substr($sql, $i, $ilen) == $start)
			{
				$i+=strlen($start);
				$elen = strlen($end);
				while($i < $slen)
				{
					if($slen - $i >= $elen && substr($sql, $i, $elen) == $end)
					{
						// SQL comment characters can be escaped by doubling the character. This recognizes and skips those.
						if($start == $end && $slen - $i >= $elen*2 && substr($sql, $i, $elen*2) == $end.$end)
						{
							$i += $elen * 2;
							continue;
						}
						else
						{
							$i += $elen;
							continue 3;
						}
					}
					$i++;
				}
				continue 2;
			}		
		}
		$i++;
	}
	if($last < $slen)
		array_push($out, substr($sql, $last, $slen - $last));
	return $out;
}

//function to scan entire directory tree and subdirectories
function dir_tree($dir)
{
	$path = '';
	$stack[] = $dir;
	while($stack)
	{
		$thisdir = array_pop($stack);
		if($dircont = scandir($thisdir))
		{
			$i=0;
			while(isset($dircont[$i]))
			{
				if($dircont[$i] !== '.' && $dircont[$i] !== '..')
				{
					$current_file = "{$thisdir}/{$dircont[$i]}";
					if(is_file($current_file))
					{
						$path[] = "{$thisdir}/{$dircont[$i]}";
					}
					elseif (is_dir($current_file))
					{
						$path[] = "{$thisdir}/{$dircont[$i]}";
						$stack[] = $current_file;
					}
				}
				$i++;
			}
		}
	}
	return $path;
}

//the function echo the help [?] links to the documentation
function helpLink($name)
{
	return "<a href='javascript:openHelp(\"".$name."\");' class='helpq' title='Help: ".$name."'>[?]</a>";	
}

//user is deleting a database
if(isset($_GET['database_delete']))
{
	$dbpath = $_POST['database_delete'];
	unlink(DBROOT.'/'.$dbpath);
	$_SESSION[COOKIENAME.'currentDB'] = 0;
}

//user is renaming a database
if(isset($_GET['database_rename']))
{
	$oldpath = $_POST['oldname']; //++ Rasta
	$newpath = $_POST['newname']; //++ Rasta
	if(!file_exists(DBROOT.'/'.$newpath))
	{
		copy(DBROOT.'/'.$oldpath, DBROOT.'/'.$newpath);
		unlink(DBROOT.'/'.$oldpath);
		$justrenamed = true;
	}
	else
	{
		$dbexists = true;	
	}
}

//user is creating a new Database
if(isset($_POST['new_dbname']))
{
	$str = preg_replace('@[^\w-.]@','', $_POST['new_dbname']);
	$dbname = $str;
	$dbpath = $str;
	$info = pathinfo($dbpath);
	if(!isset($info['extension']))
		$dbpath = $dbpath.".".$exts[0];
	else
	{
		if(!in_array(strtolower($info['extension']), $exts))
		{
			$dbpath = $dbpath.".".$exts[0];
		}
	}
	$tdata = array();	
	$tdata['name'] = $dbname;
	$tdata['path'] = $directory."/".$dbpath;
	$td = new Db_Model_Database($tdata);
}

//if the user wants to scan a directory for databases, do so
if($directory!==false)
{
	if($directory[strlen($directory)-1]=="/") //if user has a trailing slash in the directory, remove it
		$directory = substr($directory, 0, strlen($directory)-1);
		
	if(is_dir($directory)) //make sure the directory is valid
	{
		if($subdirectories===true)
			$arr = dir_tree($directory);
		else
			$arr = scandir($directory);
		$databases = array();
		$j = 0;
		for($i=0; $i<sizeof($arr); $i++) //iterate through all the files in the databases
		{
			$file = pathinfo($arr[$i]);
			if(isset($file['extension']))
			{
				$ext = strtolower($file['extension']);
				if(in_array(strtolower($ext), $exts)) //make sure the file is a valid SQLite database by checking its extension
				{
					if($subdirectories===true)
						$databases[$j]['path'] = $arr[$i];
					else
						$databases[$j]['path'] = $directory."/".$arr[$i];
					$databases[$j]['name'] = $arr[$i];
					// 22 August 2011: gkf fixed bug 49.
					$perms = 0;
					$perms += is_readable($databases[$j]['path']) ? 4 : 0;
					$perms += is_writeable($databases[$j]['path']) ? 2 : 0;
					switch($perms)
					{
						case 6: $perms = "[rw] "; break;
						case 4: $perms = "[r ] "; break;
						case 2: $perms = "[ w] "; break; // God forbid, but it might happen.
						default: $perms = "[  ] "; break;
					}
					$databases[$j]['perms'] = $perms;
					$j++;
				}
			}
		}
		// 22 August 2011: gkf fixed bug #50.
		sort($databases);
		if(isset($tdata))
		{
			for($i=0; $i<sizeof($databases); $i++)
			{
				if($tdata['path'] == $databases[$i]['path'])
				{
					$_SESSION[COOKIENAME.'currentDB'] = $i;
					break;
				}
			}
		}
		
		if(isset($justrenamed))
		{
			for($i=0; $i<sizeof($databases); $i++)
			{
				if($newpath == $databases[$i]['path'])
				{
					$_SESSION[COOKIENAME.'currentDB'] = $i;
					break;
				}
			}	
		}
	}
	else //the directory is not valid - display error and exit
	{
		echo "<div class='confirm' style='margin:20px;'>";
		echo "The directory you specified to scan for databases does not exist or is not a directory.";
		echo "</div>";
		exit();
	}
}

// 22 August 2011: gkf added this function to support display of
//                 default values in the form used to INSERT new data.
function deQuoteSQL($s)
{
	return trim(trim($s), "'");
}

//user is downloading the exported database file
if(isset($_POST['export']))
{
	if($_POST['export_type']=="sql")
	{
		header('Content-Type: text/sql');
		header('Content-Disposition: attachment; filename="'.$_POST['filename'].'.'.$_POST['export_type'].'";');
		if(isset($_POST['tables']))
			$tables = $_POST['tables'];
		else
		{
			$tables = array();
			$tables[0] = $_POST['single_table'];
		}
		$drop = isset($_POST['drop']);
		$structure = isset($_POST['structure']);
		$data = isset($_POST['data']);
		$transaction = isset($_POST['transaction']);
		$comments = isset($_POST['comments']);
		$db = new Db_Model_Database($databases[$_SESSION[COOKIENAME.'currentDB']]);
		echo $db->export_sql($tables, $drop, $structure, $data, $transaction, $comments);
	}
	else if($_POST['export_type']=="csv")
	{
		header("Content-type: application/csv");
		header('Content-Disposition: attachment; filename="'.$_POST['filename'].'.'.$_POST['export_type'].'";');
		header("Pragma: no-cache");
		header("Expires: 0");
		if(isset($_POST['tables']))
			$tables = $_POST['tables'];
		else
		{
			$tables = array();
			$tables[0] = $_POST['single_table'];
		}
		$field_terminate = $_POST['export_csv_fieldsterminated'];
		$field_enclosed = $_POST['export_csv_fieldsenclosed'];
		$field_escaped = $_POST['export_csv_fieldsescaped'];
		$null = $_POST['export_csv_replacenull'];
		$crlf = isset($_POST['export_csv_crlf']);
		$fields_in_first_row = isset($_POST['export_csv_fieldnames']);
		$db = new Db_Model_Database($databases[$_SESSION[COOKIENAME.'currentDB']]);
		echo $db->export_csv($tables, $field_terminate, $field_enclosed, $field_escaped, $null, $crlf, $fields_in_first_row);
	}
	exit();
}

//user is importing a file
if(isset($_POST['import']))
{
	$db = new Db_Model_Database($databases[$_SESSION[COOKIENAME.'currentDB']]);
	if($_POST['import_type']=="sql")
	{
		$data = file_get_contents($_FILES["file"]["tmp_name"]);
		$importSuccess = $db->import_sql($data);
	}
	else
	{
		$field_terminate = $_POST['import_csv_fieldsterminated'];
		$field_enclosed = $_POST['import_csv_fieldsenclosed'];
		$field_escaped = $_POST['import_csv_fieldsescaped'];
		$null = $_POST['import_csv_replacenull'];
		$fields_in_first_row = isset($_POST['import_csv_fieldnames']);
		$importSuccess = $db->import_csv($_FILES["file"]["tmp_name"], $_POST['single_table'], $field_terminate, $field_enclosed, $field_escaped, $null, $fields_in_first_row);
	}
}
