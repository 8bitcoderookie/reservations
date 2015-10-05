<?php

/* -------------------------------
   connect to waltergasse database
   ------------------------------- */

require_once($_SERVER['DOCUMENT_ROOT'].'/waltergasse/code/config.php');

$dbHomepage = new mysqli( 
	$waltergasseSite['dbURL'], 
	$waltergasseSite['dbUser'], 
	$waltergasseSite['dbPass'], 
	$waltergasseSite['dbName']
);

if($dbHomepage->connect_errno > 0){
	exit(json_encode(array('error' => 'Konnte Datenbank nicht erreichen [' . $dbHomepage->connect_error . ']'), JSON_UNESCAPED_UNICODE));
}

$dbHomepage->query("SET NAMES 'utf8'");
$dbHomepage->query("SET CHARACTER SET 'utf8'");

?>