<?php
/*
	*	
*/
class Db_Model_Database
{
	
		protected $db; //reference to the DB object
		protected $type; //the extension for PHP that handles SQLite
		protected $data;
		protected $lastResult;
		protected $fns;
	
		public function __construct($data)
		{
			$this->data = $data;
			$this->fns = array();
			try
			{
				if(!file_exists($this->data["path"]) && !is_writable(dirname($this->data["path"]))) //make sure the containing directory is writable if the database does not exist
				{
					echo "<div class='confirm' style='margin:20px;'>";
					echo "The database, '".$this->data["path"]."', does not exist and cannot be created because the containing directory, '".dirname($this->data["path"])."', is not writable. The application is unusable until you make it writable.";
					echo "<form action='".PAGE."' method='post'/>";
					echo "<input type='submit' value='Log Out' name='logout' class='btn'/>";
					echo "</form>";
					echo "</div><br/>";
					exit();
				}
	
				$ver = $this->getVersion();
	
				switch(true)
				{
					case (FORCETYPE=="PDO" || ((FORCETYPE==false || $ver!=-1) && class_exists("PDO") && ($ver==-1 || $ver==3))):
						$this->db = new PDO("sqlite:".$this->data['path']);
						if($this->db!=NULL)
						{
							$this->type = "PDO";
							$cfns = unserialize(CUSTOM_FUNCTIONS);
							for($i=0; $i<sizeof($cfns); $i++)
							{
								$this->db->sqliteCreateFunction($cfns[$i], $cfns[$i], 1);
								$this->addUserFunction($cfns[$i]);	
							}
							break;
						}
					case (FORCETYPE=="SQLite3" || ((FORCETYPE==false || $ver!=-1) && class_exists("SQLite3") && ($ver==-1 || $ver==3))):
						$this->db = new SQLite3($this->data['path']);
						if($this->db!=NULL)
						{
							$cfns = unserialize(CUSTOM_FUNCTIONS);
							for($i=0; $i<sizeof($cfns); $i++)
							{
								$this->db->createFunction($cfns[$i], $cfns[$i], 1);
								$this->addUserFunction($cfns[$i]);	
							}
							$this->type = "SQLite3";
							break;
						}
					case (FORCETYPE=="SQLiteDatabase" || ((FORCETYPE==false || $ver!=-1) && class_exists("SQLiteDatabase") && ($ver==-1 || $ver==2))):
						$this->db = new SQLiteDatabase($this->data['path']);
						if($this->db!=NULL)
						{
							$cfns = unserialize(CUSTOM_FUNCTIONS);
							for($i=0; $i<sizeof($cfns); $i++)
							{
								$this->db->createFunction($cfns[$i], $cfns[$i], 1);
								$this->addUserFunction($cfns[$i]);	
							}
							$this->type = "SQLiteDatabase";
							break;
						}
					default:
						$this->showError();
						exit();
				}
			}
			catch(Exception $e)
			{
				$this->showError();
				exit();
			}
		}
		
		public function getUserFunctions()
		{
			return $this->fns;	
		}
		
		public function addUserFunction($name)
		{
			array_push($this->fns, $name);	
		}
		
		public function getError()
		{
			if($this->type=="PDO")
			{
				$e = $this->db->errorInfo();
				return $e[2];	
			}
			else if($this->type=="SQLite3")
			{
				return $this->db->lastErrorMsg();
			}
			else
			{
				return sqlite_error_string($this->db->lastError());
			}
		}
		
		public function showError()
		{
			$classPDO = class_exists("PDO");
			$classSQLite3 = class_exists("SQLite3");
			$classSQLiteDatabase = class_exists("SQLiteDatabase");
			if($classPDO)
				$strPDO = "installed";
			else
				$strPDO = "not installed";
			if($classSQLite3)
				$strSQLite3 = "installed";
			else
				$strSQLite3 = "not installed";
			if($classSQLiteDatabase)
				$strSQLiteDatabase = "installed";
			else
				$strSQLiteDatabase = "not installed";
			echo "<div class='confirm' style='margin:20px;'>";
			echo "There was a problem setting up your database, ".$this->getPath().". An attempt will be made to find out what's going on so you can fix the problem more easily.<br/><br/>";
			echo "<i>Checking supported SQLite PHP extensions...<br/><br/>";
			echo "<b>PDO</b>: ".$strPDO."<br/>";
			echo "<b>SQLite3</b>: ".$strSQLite3."<br/>";
			echo "<b>SQLiteDatabase</b>: ".$strSQLiteDatabase."<br/><br/>...done.</i><br/><br/>";
			if(!$classPDO && !$classSQLite3 && !$classSQLiteDatabase)
				echo "It appears that none of the supported SQLite library extensions are available in your installation of PHP. You may not use ".PROJECT." until you install at least one of them.";
			else
			{
				if(!$classPDO && !$classSQLite3 && $this->getVersion()==3)
					echo "It appears that your database is of SQLite version 3 but your installation of PHP does not contain the necessary extensions to handle this version. To fix the problem, either delete the database and allow ".PROJECT." to create it automatically or recreate it manually as SQLite version 2.";
				else if(!$classSQLiteDatabase && $this->getVersion()==2)
					echo "It appears that your database is of SQLite version 2 but your installation of PHP does not contain the necessary extensions to handle this version. To fix the problem, either delete the database and allow ".PROJECT." to create it automatically or recreate it manually as SQLite version 3.";
				else
					echo "The problem cannot be diagnosed properly. Please email me at daneiracleous@gmail.com with your database as an attachment and the contents of this error message. It may be that your database is simply not a valid SQLite database, but this is not certain.";
			}
			echo "</div><br/>";
		}
	
		public function __destruct()
		{
			if($this->db)
				$this->close();
		}
	
		//get the exact PHP extension being used for SQLite
		public function getType()
		{
			return $this->type;
		}
	
		//get the name of the database
		public function getName()
		{
			return $this->data["name"];
		}
	
		//get the filename of the database
		public function getPath()
		{
			return $this->data["path"];
		}
	
		//get the version of the database
		public function getVersion()
		{
			if(file_exists($this->data['path'])) //make sure file exists before getting its contents
			{
				$content = strtolower(file_get_contents($this->data['path'], NULL, NULL, 0, 40)); //get the first 40 characters of the database file
				$p = strpos($content, "** this file contains an sqlite 2"); //this text is at the beginning of every SQLite2 database
				if($p!==false) //the text is found - this is version 2
					return 2;
				else
					return 3;
			}
			else //return -1 to indicate that it does not exist and needs to be created
			{
				return -1;
			}
		}
	
		//get the size of the database
		public function getSize()
		{
			return round(filesize($this->data["path"])*0.0009765625, 1)." Kb";
		}
	
		//get the last modified time of database
		public function getDate()
		{
			return date("g:ia \o\\n F j, Y", filemtime($this->data["path"]));
		}
	
		//get number of affected rows from last query
		public function getAffectedRows()
		{
			if($this->type=="PDO")
				return $this->lastResult->rowCount();
			else if($this->type=="SQLite3")
				return $this->db->changes();
			else if($this->type=="SQLiteDatabase")
				return $this->db->changes();
		}
	
		public function close()
		{
			if($this->type=="PDO")
				$this->db = NULL;
			else if($this->type=="SQLite3")
				$this->db->close();
			else if($this->type=="SQLiteDatabase")
				$this->db = NULL;
		}
	
		public function beginTransaction()
		{
			$this->query("BEGIN");
		}
	
		public function commitTransaction()
		{
			$this->query("COMMIT");
		}
	
		public function rollbackTransaction()
		{
			$this->query("ROLLBACK");
		}
	
		//generic query wrapper
		public function query($query, $ignoreAlterCase=false)
		{
			if(strtolower(substr(ltrim($query),0,5))=='alter' && $ignoreAlterCase==false) //this query is an ALTER query - call the necessary function
			{
				$queryparts = preg_split("/[\s]+/", $query, 4, PREG_SPLIT_NO_EMPTY);
				$tablename = $queryparts[2];
				$alterdefs = $queryparts[3];
				//echo $query;
				$result = $this->alterTable($tablename, $alterdefs);
			}
			else //this query is normal - proceed as normal
				$result = $this->db->query($query);
			if(!$result)
				return NULL;
			$this->lastResult = $result;
			return $result;
		}
	
		//wrapper for an INSERT and returns the ID of the inserted row
		public function insert($query)
		{
			$result = $this->query($query);
			if($this->type=="PDO")
				return $this->db->lastInsertId();
			else if($this->type=="SQLite3")
				return $this->db->lastInsertRowID();
			else if($this->type=="SQLiteDatabase")
				return $this->db->lastInsertRowid();
		}
	
		//returns an array for SELECT
		public function select($query, $mode="both")
		{
			$result = $this->query($query);
			if(!$result) //make sure the result is valid
				return NULL;
			if($this->type=="PDO")
			{
				if($mode=="assoc")
					$mode = PDO::FETCH_ASSOC;
				else if($mode=="num")
					$mode = PDO::FETCH_NUM;
				else
					$mode = PDO::FETCH_BOTH;
				return $result->fetch($mode);
			}
			else if($this->type=="SQLite3")
			{
				if($mode=="assoc")
					$mode = SQLITE3_ASSOC;
				else if($mode=="num")
					$mode = SQLITE3_NUM;
				else
					$mode = SQLITE3_BOTH;
				return $result->fetchArray($mode);
			}
			else if($this->type=="SQLiteDatabase")
			{
				if($mode=="assoc")
					$mode = SQLITE_ASSOC;
				else if($mode=="num")
					$mode = SQLITE_NUM;
				else
					$mode = SQLITE_BOTH;
				return $result->fetch($mode);
			}
		}
	
		//returns an array of arrays after doing a SELECT
		public function selectArray($query, $mode="both")
		{
			$result = $this->query($query);
			if(!$result) //make sure the result is valid
				return NULL;
			if($this->type=="PDO")
			{
				if($mode=="assoc")
					$mode = PDO::FETCH_ASSOC;
				else if($mode=="num")
					$mode = PDO::FETCH_NUM;
				else
					$mode = PDO::FETCH_BOTH;
				return $result->fetchAll($mode);
			}
			else if($this->type=="SQLite3")
			{
				if($mode=="assoc")
					$mode = SQLITE3_ASSOC;
				else if($mode=="num")
					$mode = SQLITE3_NUM;
				else
					$mode = SQLITE3_BOTH;
				$arr = array();
				$i = 0;
				while($res = $result->fetchArray($mode))
				{
					$arr[$i] = $res;
					$i++;
				}
				return $arr;
			}
			else if($this->type=="SQLiteDatabase")
			{
				if($mode=="assoc")
					$mode = SQLITE_ASSOC;
				else if($mode=="num")
					$mode = SQLITE_NUM;
				else
					$mode = SQLITE_BOTH;
				return $result->fetchAll($mode);
			}
		}
	
		//function that is called for an alter table statement in a query
		//code borrowed with permission from http://code.jenseng.com/db/
		public function alterTable($table, $alterdefs)
		{
			if($alterdefs != '')
			{
				$tempQuery = "SELECT sql,name,type FROM sqlite_master WHERE tbl_name = '".$table."' ORDER BY type DESC";
				$result = $this->query($tempQuery);
				$resultArr = $this->selectArray($tempQuery);
	
				if(sizeof($resultArr)>0)
				{
					$row = $this->select($tempQuery); //table sql
					$tmpname = 't'.time();
					$origsql = trim(preg_replace("/[\s]+/", " ", str_replace(",", ", ",preg_replace("/[\(]/", "( ", $row['sql'], 1))));
					$createtemptableSQL = 'CREATE TEMPORARY '.substr(trim(preg_replace("'".$table."'", $tmpname, $origsql, 1)), 6);
					$createindexsql = array();
					$i = 0;
					$defs = preg_split("/[,]+/",$alterdefs, -1, PREG_SPLIT_NO_EMPTY);
					$prevword = $table;
					$oldcols = preg_split("/[,]+/", substr(trim($createtemptableSQL), strpos(trim($createtemptableSQL), '(')+1), -1, PREG_SPLIT_NO_EMPTY);
					$newcols = array();
					for($i=0; $i<sizeof($oldcols); $i++)
					{
						$colparts = preg_split("/[\s]+/", $oldcols[$i], -1, PREG_SPLIT_NO_EMPTY);
						$oldcols[$i] = $colparts[0];
						$newcols[$colparts[0]] = $colparts[0];
					}
					$newcolumns = '';
					$oldcolumns = '';
					reset($newcols);
					while(list($key, $val) = each($newcols))
					{
						$newcolumns .= ($newcolumns?', ':'').$val;
						$oldcolumns .= ($oldcolumns?', ':'').$key;
					}
					$copytotempsql = 'INSERT INTO '.$tmpname.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$table;
					$dropoldsql = 'DROP TABLE '.$table;
					$createtesttableSQL = $createtemptableSQL;
					foreach($defs as $def)
					{
						$defparts = preg_split("/[\s]+/", $def,-1, PREG_SPLIT_NO_EMPTY);
						$action = strtolower($defparts[0]);
						switch($action)
						{
							case 'add':
								if(sizeof($defparts) <= 2)
									return false;
								$createtesttableSQL = substr($createtesttableSQL, 0, strlen($createtesttableSQL)-1).',';
								for($i=1;$i<sizeof($defparts);$i++)
									$createtesttableSQL.=' '.$defparts[$i];
								$createtesttableSQL.=')';
								break;
							case 'change':
								if(sizeof($defparts) <= 3)
								{
									return false;
								}
								if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' '))
								{
									if($newcols[$defparts[1]] != $defparts[1])
										return false;
									$newcols[$defparts[1]] = $defparts[2];
									$nextcommapos = strpos($createtesttableSQL,',',$severpos);
									$insertval = '';
									for($i=2;$i<sizeof($defparts);$i++)
										$insertval.=' '.$defparts[$i];
									if($nextcommapos)
										$createtesttableSQL = substr($createtesttableSQL,0,$severpos).$insertval.substr($createtesttableSQL,$nextcommapos);
									else
										$createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1)).$insertval.')';
								}
								else
									return false;
								break;
							case 'drop':
								if(sizeof($defparts) < 2)
									return false;
								if($severpos = strpos($createtesttableSQL,' '.$defparts[1].' '))
								{
									$nextcommapos = strpos($createtesttableSQL,',',$severpos);
									if($nextcommapos)
										$createtesttableSQL = substr($createtesttableSQL,0,$severpos).substr($createtesttableSQL,$nextcommapos + 1);
									else
										$createtesttableSQL = substr($createtesttableSQL,0,$severpos-(strpos($createtesttableSQL,',')?0:1) - 1).')';
									unset($newcols[$defparts[1]]);
								}
								else
									return false;
								break;
							default:
								return false;
						}
						$prevword = $defparts[sizeof($defparts)-1];
					}
					//this block of code generates a test table simply to verify that the columns specifed are valid in an sql statement
					//this ensures that no reserved words are used as columns, for example
					$tempResult = $this->query($createtesttableSQL);
					if(!$tempResult)
						return false;
					$droptempsql = 'DROP TABLE '.$tmpname;
					$tempResult = $this->query($droptempsql);
					//end block
	
					$createnewtableSQL = 'CREATE '.substr(trim(preg_replace("'".$tmpname."'", $table, $createtesttableSQL, 1)), 17);
					$newcolumns = '';
					$oldcolumns = '';
					reset($newcols);
					while(list($key,$val) = each($newcols))
					{
						$newcolumns .= ($newcolumns?', ':'').$val;
						$oldcolumns .= ($oldcolumns?', ':'').$key;
					}
					$copytonewsql = 'INSERT INTO '.$table.'('.$newcolumns.') SELECT '.$oldcolumns.' FROM '.$tmpname;
	
					$this->query($createtemptableSQL); //create temp table
					$this->query($copytotempsql); //copy to table
					$this->query($dropoldsql); //drop old table
	
					$this->query($createnewtableSQL); //recreate original table
					$this->query($copytonewsql); //copy back to original table
					$this->query($droptempsql); //drop temp table
				}
				else
				{
					return false;
				}
				return true;
			}
		}
	
		//multiple query execution
		public function multiQuery($query)
		{
			$error = "Unknown error.";
			if($this->type=="PDO")
			{
				$success = $this->db->exec($query);
			}
			else if($this->type=="SQLite3")
			{
				$success = $this->db->exec($query, $error);
			}
			else
			{
				$success = $this->db->queryExec($query, $error);
			}
			if(!$success)
			{
				return "Error in query: '".$error."'";
			}
			else
			{
				return true;	
			}
		}
	
		//get number of rows in table
		public function numRows($table)
		{
			$result = $this->select("SELECT Count(*) FROM ".$table);
			return $result[0];
		}
	
		//correctly escape a string to be injected into an SQL query
		public function quote($value)
		{
			if($this->type=="PDO")
			{
				// PDO quote() escapes and adds quotes
				return $this->db->quote($value);
			}
			else if($this->type=="SQLite3")
			{
				return "'".$this->db->escapeString($value)."'";
			}
			else
			{
				return "'".sqlite_escape_string($value)."'";
			}
		}
	
		//correctly format a string value from a table before showing it
		public function formatString($value)
		{
			return htmlspecialchars(stripslashes($value));
		}
	
		//import sql
		public function import_sql($query)
		{
			return $this->multiQuery($query);
		}
		
		//import csv
		public function import_csv($filename, $table, $field_terminate, $field_enclosed, $field_escaped, $null, $fields_in_first_row)
		{
			// CSV import implemented by Christopher Kramer - http://www.christosoft.de
			$csv_handle = fopen($filename,'r');
			$csv_insert = "BEGIN;\n";
			$csv_number_of_rows = 0;
			// PHP requires enclosure defined, but has no problem if it was not used
			if($field_enclosed=="") $field_enclosed='"';
			// PHP requires escaper defined
			if($field_escaped=="") $field_escaped='\\';
			while(!feof($csv_handle))
			{
				$csv_data = fgetcsv($csv_handle, 0, $field_terminate, $field_enclosed, $field_escaped); 
				if($csv_data[0] != NULL || count($csv_data)>1)
				{
					$csv_number_of_rows++;
					if($fields_in_first_row && $csv_number_of_rows==1) continue; 
					$csv_col_number = count($csv_data);
					$csv_insert .= "INSERT INTO $table VALUES (";
					foreach($csv_data as $csv_col => $csv_cell)
					{
						if($csv_cell == $null) $csv_insert .= "NULL";
						else
						{
							$csv_insert.= $this->quote($csv_cell);
						}
						if($csv_col == $csv_col_number-2 && $csv_data[$csv_col+1]=='')
						{
							// the CSV row ends with the separator (like old phpliteadmin exported)
							break;
						} 
						if($csv_col < $csv_col_number-1) $csv_insert .= ",";
					}
					$csv_insert .= ");\n";
					
					if($csv_number_of_rows > 5000)
					{
						$csv_insert .= "COMMIT;\nBEGIN;\n";
						$csv_number_of_rows = 0;
					}
				}
			}
			$csv_insert .= "COMMIT;";
			fclose($csv_handle);
			return $this->multiQuery($csv_insert);
	
		}
		
		//export csv
		public function export_csv($tables, $field_terminate, $field_enclosed, $field_escaped, $null, $crlf, $fields_in_first_row)
		{
			$field_enclosed = stripslashes($field_enclosed);
			$query = "SELECT * FROM sqlite_master WHERE type='table' ORDER BY type DESC";
			$result = $this->selectArray($query);
			for($i=0; $i<sizeof($result); $i++)
			{
				$valid = false;
				for($j=0; $j<sizeof($tables); $j++)
				{
					if($result[$i]['tbl_name']==$tables[$j])
						$valid = true;
				}
				if($valid)
				{
					$query = "PRAGMA table_info('".$result[$i]['tbl_name']."')";
					$temp = $this->selectArray($query);
					$cols = array();
					for($z=0; $z<sizeof($temp); $z++)
						$cols[$z] = $temp[$z][1];
					if($fields_in_first_row)
					{
						for($z=0; $z<sizeof($cols); $z++)
						{
							echo $field_enclosed.$cols[$z].$field_enclosed;
							// do not terminate the last column!
							if($z < sizeof($cols)-1)
								echo $field_terminate;
						}
						echo "\r\n";	
					}
					$query = "SELECT * FROM ".$result[$i]['tbl_name'];
					$arr = $this->selectArray($query, "assoc");
					for($z=0; $z<sizeof($arr); $z++)
					{
						for($y=0; $y<sizeof($cols); $y++)
						{
							$cell = $arr[$z][$cols[$y]];
							if($crlf)
							{
								$cell = str_replace("\n","", $cell);
								$cell = str_replace("\r","", $cell);
							}
							$cell = str_replace($field_terminate,$field_escaped.$field_terminate,$cell);
							$cell = str_replace($field_enclosed,$field_escaped.$field_enclosed,$cell);
							// do not enclose NULLs
							if($cell == NULL)
								echo $null;  
							else
								echo $field_enclosed.$cell.$field_enclosed;
							// do not terminate the last column!
							if($y < sizeof($cols)-1)
								echo $field_terminate;
						}
						if($z<sizeof($arr)-1)
							echo "\r\n";	
					}
					if($i<sizeof($result)-1)
						echo "\r\n";
				}
			}
		}
		
		//export sql
		public function export_sql($tables, $drop, $structure, $data, $transaction, $comments)
		{
			if($comments)
			{
				echo "----\r\n";
				//echo "-- phpLiteAdmin database dump (http://phpliteadmin.googlecode.com)\r\n";
				echo "-- phpLiteAdmin database dump \r\n";
				echo "-- phpLiteAdmin version: ".VERSION."\r\n";
				echo "-- Exported on ".date('M jS, Y, h:i:sA')."\r\n";
				//echo "-- Database file: ".$this->getPath()."\r\n";
				echo "----\r\n";
			}
			$query = "SELECT * FROM sqlite_master WHERE type='table' OR type='index' ORDER BY type DESC";
			$result = $this->selectArray($query);
	
			//iterate through each table
			for($i=0; $i<sizeof($result); $i++)
			{
				$valid = false;
				for($j=0; $j<sizeof($tables); $j++)
				{
					if($result[$i]['tbl_name']==$tables[$j])
						$valid = true;
				}
				if($valid)
				{
					if($drop)
					{
						if($comments)
						{
							echo "\r\n----\r\n";
							if($result[$i]['type']=="table")
								echo "-- Drop table for ".$result[$i]['tbl_name']."\r\n";
							else
								echo "-- Drop index for ".$result[$i]['name']."\r\n";
							echo "----\r\n";
						}
						if($result[$i]['type']=="table")
							echo "DROP TABLE '".$result[$i]['tbl_name']."';\r\n";
						else
							echo "DROP INDEX '".$result[$i]['name']."';\r\n";
					}
					if($structure)
					{
						if($comments)
						{
							echo "\r\n----\r\n";
							if($result[$i]['type']=="table")
								echo "-- Table structure for ".$result[$i]['tbl_name']."\r\n";
							else
								echo "-- Structure for index ".$result[$i]['name']." on table ".$result[$i]['tbl_name']."\r\n";
							echo "----\r\n";
						}
						echo $result[$i]['sql'].";\r\n";
					}
					if($data && $result[$i]['type']=="table")
					{
						$query = "SELECT * FROM ".$result[$i]['tbl_name'];
						$arr = $this->selectArray($query, "assoc");
	
						if($comments)
						{
							echo "\r\n----\r\n";
							echo "-- Data dump for ".$result[$i]['tbl_name'].", a total of ".sizeof($arr)." rows\r\n";
							echo "----\r\n";
						}
						$query = "PRAGMA table_info('".$result[$i]['tbl_name']."')";
						$temp = $this->selectArray($query);
						$cols = array();
						$vals = array();
						for($z=0; $z<sizeof($temp); $z++)
							$cols[$z] = $temp[$z][1];
						for($z=0; $z<sizeof($arr); $z++)
						{
							for($y=0; $y<sizeof($cols); $y++)
							{
								if(!isset($vals[$z]))
									$vals[$z] = array();
								$vals[$z][$cols[$y]] = $this->quote($arr[$z][$cols[$y]]);
							}
						}
						if($transaction)
							echo "BEGIN TRANSACTION;\r\n";
						for($j=0; $j<sizeof($vals); $j++)
							echo "INSERT INTO ".$result[$i]['tbl_name']." (".implode(",", $cols).") VALUES (".implode(",", $vals[$j]).");\r\n";
						if($transaction)
							echo "COMMIT;\r\n";
					}
				}
			}
		}	

}

?>