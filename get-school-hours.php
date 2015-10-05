<?php

header("Content-type: application/json; charset=utf-8");

require_once($_SERVER['DOCUMENT_ROOT'].'/waltergasse/code/config.php');
require_once('setup-db-waltergasse-json.php');

$sql = <<<SQL
	SELECT `id`, `vonbis` 
	FROM `schulstunde` 
	WHERE 1;
SQL;

if(!$result = $dbHomepage->query($sql)){
	exit(json_encode(array('error' => 'There was an error running the query [' . $dbHomepage->error . ']'), JSON_UNESCAPED_UNICODE));
}

$json = array();
while($row = $result->fetch_assoc()){
    $json[]= array(
		'id' => $row['id'],
		'vonbis' => $row['vonbis']
    );
}

echo json_encode($json, JSON_UNESCAPED_UNICODE);

?>