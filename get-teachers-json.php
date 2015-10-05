<?php

header("Content-type: application/json; charset=utf-8");

// Moodle Homepage Import
require_once($_SERVER['DOCUMENT_ROOT'].'/moodle/config.php');
require_once('config.php');
require_once('setup-db-moodle-json.php');

if (!is_res_admin()) {
	exit('<p>Sorry, this page can be accessed by a site admins only.</p>');
}

$sql = <<<SQL
	SELECT `u`.`id`, `u`.`firstname`, `u`.`lastname` 
	FROM `mdl_cohort_members` AS `cm`, `mdl_user` as `u` 
	WHERE `cm`.`userid` = `u`.`id`
	AND `cm`.`cohortid` = 2
	ORDER BY `u`.`lastname` ASC;
SQL;

if(!$result = $dbMoodle->query($sql)){
	exit(json_encode(array('error' => 'There was an error running the query [' . $dbMoodle->error . ']'), JSON_UNESCAPED_UNICODE));
}

// echo "anzahl der Datensätze ".$result->num_rows;

$json = array();
while($row = $result->fetch_assoc()){
    $json[]= array(
		'id' => $row['id'],
		'firstname' => $row['firstname'],
		'lastname' => $row['lastname']
    );
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);

?>