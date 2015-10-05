<?php

/* --------------------------
   connect to moodle database
   -------------------------- */

require_once($_SERVER['DOCUMENT_ROOT'].'/moodle/config.php');

$dbMoodle = new mysqli( 
	$CFG->dbhost,
	$CFG->dbuser,
	$CFG->dbpass,
	$CFG->dbname
);

if($dbMoodle->connect_errno > 0){
	exit(json_encode(array('error' => 'Unable to connect to database [' . $dbMoodle->connect_error . ']'), JSON_UNESCAPED_UNICODE));
}

$dbMoodle->query("SET NAMES 'utf8'");
$dbMoodle->query("SET CHARACTER SET 'utf8'");

?>