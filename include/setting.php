<?php
// Put you own database settings here
// $dbHost="";
// $dbName="";
// $dbUsername="";
// $dbPassword="";
// $dbTable="";

// Put you own scv schema here
// $csvSchema = array('name','age','skill');

// control if you have set this variable
if (!isset($dbHost) or !isset($dbName) or !isset($dbUsername) or !isset($dbPassword) or !isset($dbTable) or !isset($csvSchema) ) {
	echo 'Fill you own settings in "include/setting.php"';
	exit();
}
?>