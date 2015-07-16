<?php
// Put you own database settings here
// $dbHost="";
// $dbName="";
// $dbUsername="";
// $dbPassword="";
// $dbTable1="";
// $dbTable2="";

// Put you own scv schema here
// $csvSchema = array('headerColums1','headerColums2','etc..');

// control if you have filled you own settings
if (!isset($dbHost) or !isset($dbName) or !isset($dbUsername) or !isset($dbPassword) or !isset($dbTable) or !isset($csvSchema) ) {
	echo 'Fill you own settings in "include/setting.php"';
	exit();
}
?>