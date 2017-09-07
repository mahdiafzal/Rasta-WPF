<?php

//
//  Project: phpLiteAdmin (http://phpliteadmin.googlecode.com)
//  Version: 1.9.2
//  Summary: PHP-based admin tool to manage SQLite2 and SQLite3 databases on the web
//  Last updated: 5/30/12
//  Developers:
//     Dane Iracleous (daneiracleous@gmail.com)
//     Ian Aldrighetti (ian.aldrighetti@gmail.com)
//     George Flanagin & Digital Gaslight, Inc (george@digitalgaslight.com)
//		 Christopher Kramer (crazy4chrissi@gmail.com)
//
//
//  Copyright (C) 2011  phpLiteAdmin
//
//  This program is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////

//please report any bugs you encounter to http://code.google.com/p/phpliteadmin/issues/list







//
// Authorization class
// Maintains user's logged-in state and security of application
//
class Authorization
{
}

//
// Database class
// Generic database abstraction class to manage interaction with database without worrying about SQLite vs. PHP versions
//
class Database
{
}



// here begins the HTML.
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<!-- Copyright 2011 phpLiteAdmin (http://phpliteadmin.googlecode.com) -->
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<title><?php echo PROJECT ?></title>

<?php
if(!file_exists("phpliteadmin.css")) //only use the inline stylesheet if an external one does not exist
{
?>
<!-- begin the customizable stylesheet/theme -->

<!-- end the customizable stylesheet/theme -->
<?php
}
else //an external stylesheet exists - import it
{
	echo 	"<link href='phpliteadmin.css' rel='stylesheet' type='text/css' />";
}
if(isset($_GET['help'])) //this page is used as the popup help section
{
}
?>
<!-- JavaScript Support -->

</head>
<body>
<?php
if(ini_get("register_globals")) //check whether register_globals is turned on - if it is, we need to not continue
{
	echo "<div class='confirm' style='margin:20px;'>";
	echo "It appears that the PHP directive, 'register_globals' is enabled. This is bad. You need to disable it before continuing.";
	echo "</div>";
	exit();
}

if(!$auth->isAuthorized()) //user is not authorized - display the login screen
{
}
else //user is authorized - display the main application
{}
echo "</body>";
echo "</html>";

?>