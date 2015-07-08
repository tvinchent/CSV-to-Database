<?php
// Put you own database settings here
// $dbHost="";
// $dbName="test";
// $dbUsername="root";
// $dbPassword="";
// $dbTable="fromCSV";

// control if you have set this variable
if (!isset($dbHost) or !isset($dbName) or !isset($dbUsername) or !isset($dbPassword) or !isset($dbTable) ) {
	echo 'Fill you own database settings in "include/setting.php"';
	exit();
}
?>